#!/usr/bin/env node
/**
 * Vendor the @roxyapi/ui custom-element bundle and design tokens into assets/.
 *
 * The plugin ships the bundle, its CSS custom-property defaults, and its
 * interface-label catalogues locally rather than loading them from a CDN,
 * because wordpress.org does not allow a plugin to load executable JavaScript
 * from a third-party host. These are downloaded from jsDelivr and committed:
 *
 *   - the minified UMD bundle to assets/js/roxy-ui.js (the custom elements)
 *   - the design-token stylesheet to assets/css/roxy-ui-tokens.css (the
 *     `--roxy-*` defaults plus the automatic dark-mode block the elements read)
 *   - one catalogue per translated language to assets/js/locales/{lang}.js,
 *     which is what puts the labels around a chart into the site language
 *
 * The catalogues are separate payloads rather than part of the bundle, so which
 * languages exist is a property of the version being vendored. The manifest
 * that ships beside them lists it, so this reads the set instead of keeping a
 * copy of it in step by hand, and a version from before the catalogues existed
 * simply reports none.
 *
 * By default this resolves whatever @roxyapi/ui currently tags as `latest` on
 * the npm registry, vendors it, and writes that concrete version back into the
 * three places that pin it: `ROXYAPI_UI_VERSION` in roxyapi.php, and
 * `_meta.ui_version_pinned` + `_meta.ui_manifest_url` in bin/component-map.json.
 * So bumping the pin is not a step anyone can forget: running this script IS the
 * bump. Pass an explicit version to vendor an older one deliberately.
 *
 * The resolved version is written back as a concrete semver rather than left as
 * the literal string `latest`, because it is also the `$ver` cache-buster passed
 * to wp_register_script/wp_register_style. A constant `?ver=latest` never
 * changes, so browsers and proxies would keep serving the previous bundle after
 * a re-vendor. It is also what makes the vendored bytes auditable by a reviewer.
 *
 * @example
 * ```sh
 * npm run fetch:ui              # vendor whatever npm tags latest
 * npm run fetch:ui -- 0.14.0    # vendor an older version on purpose
 * ```
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(
	path.dirname( fileURLToPath( import.meta.url ) ),
	'..'
);
const PLUGIN_FILE = path.join( ROOT, 'roxyapi.php' );
const MAP_FILE = path.join( ROOT, 'bin', 'component-map.json' );
const JS_OUT_FILE = path.join( ROOT, 'assets', 'js', 'roxy-ui.js' );
const CSS_OUT_FILE = path.join( ROOT, 'assets', 'css', 'roxy-ui-tokens.css' );
const LOCALES_OUT_DIR = path.join( ROOT, 'assets', 'js', 'locales' );

const PACKAGE = '@roxyapi/ui';
const SEMVER = /^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?$/;

/**
 * The global a catalogue payload publishes itself on. Its presence is what
 * separates a real catalogue from an error page served with a 200.
 */
const LOCALE_REGISTRY = '__ROXY_UI_I18N__';

/**
 * Base URL for one file inside the pinned package.
 *
 * @param {string} version The resolved version.
 * @param {string} file    Path inside the package, without a leading slash.
 * @return {string} Absolute jsDelivr URL.
 */
function cdnUrl( version, file ) {
	return `https://cdn.jsdelivr.net/npm/${ PACKAGE }@${ version }/${ file }`;
}

/**
 * Resolve the version to vendor: the CLI argument, else npm's `latest` tag.
 *
 * @return {Promise<string>} A concrete semver, never the literal 'latest'.
 */
async function resolveVersion() {
	const requested = process.argv[ 2 ];
	if ( requested && requested !== 'latest' ) {
		if ( ! SEMVER.test( requested ) ) {
			console.error(
				`[fetch-ui-bundle] "${ requested }" is not a semver version; refusing to vendor`
			);
			process.exit( 1 );
		}
		return requested;
	}

	const response = await fetch(
		`https://registry.npmjs.org/${ PACKAGE }/latest`
	);
	if ( ! response.ok ) {
		console.error(
			`[fetch-ui-bundle] could not resolve the latest ${ PACKAGE } from npm: ${ response.status } ${ response.statusText }`
		);
		process.exit( 1 );
	}
	const { version } = await response.json();
	if ( ! SEMVER.test( version ?? '' ) ) {
		console.error(
			`[fetch-ui-bundle] npm returned an unusable latest version: ${ version }`
		);
		process.exit( 1 );
	}
	return version;
}

/**
 * The `part` names the pinned build publishes, across every component, as one
 * sorted list.
 *
 * This is the vocabulary the `hide_sections` setting and its shortcode
 * attribute both accept, and it belongs to the BUNDLE rather than to the API
 * spec, so it moves when the pin moves and cannot be derived at generate time.
 * Reading it here is what keeps it a fact about the build actually vendored
 * instead of a list somebody remembers to update.
 *
 * A name is only usable if it survives the same `[a-z][a-z0-9-]*` narrowing the
 * plugin applies before it reaches a selector, so anything else is dropped here
 * rather than offered to a site owner as a name that cannot work.
 *
 * @param {string} version The resolved version.
 * @return {Promise<string[]>} Sorted, unique part names.
 */
async function fetchPublishedParts( version ) {
	const url = cdnUrl( version, 'components-catalog.json' );
	console.log( `[fetch-ui-bundle] fetching ${ url }` );
	const response = await fetch( url );
	if ( ! response.ok ) {
		console.error(
			`[fetch-ui-bundle] could not read the component catalogue: ${ response.status } ${ response.statusText }; refusing to write`
		);
		process.exit( 1 );
	}
	const catalog = await response.json();
	const components = Array.isArray( catalog )
		? catalog
		: catalog.components || [];
	const parts = new Set();
	for ( const component of components ) {
		for ( const part of component.parts || [] ) {
			if ( /^[a-z][a-z0-9-]*$/.test( part ) ) {
				parts.add( part );
			}
		}
	}
	if ( parts.size === 0 ) {
		console.error(
			`[fetch-ui-bundle] ${ PACKAGE }@${ version } publishes no part names, so the Hide sections setting would have nothing to validate against; refusing to write`
		);
		process.exit( 1 );
	}
	return [ ...parts ].sort();
}

/**
 * Rewrite every pin to the version actually vendored, so the cache-buster, the
 * manifest the component-map check validates against, and the vendored bytes can
 * never disagree.
 *
 * @param {string}   version The resolved version.
 * @param {string[]} parts   Part names the pinned build publishes.
 * @return {void}
 */
function writeBackPins( version, parts ) {
	const php = fs.readFileSync( PLUGIN_FILE, 'utf8' );
	const phpPinned = php.replace(
		/(const\s+ROXYAPI_UI_VERSION\s*=\s*')[^']+(')/,
		`$1${ version }$2`
	);
	if ( ! /const\s+ROXYAPI_UI_VERSION\s*=\s*'/.test( php ) ) {
		console.error(
			'[fetch-ui-bundle] could not find ROXYAPI_UI_VERSION in roxyapi.php'
		);
		process.exit( 1 );
	}
	fs.writeFileSync( PLUGIN_FILE, phpPinned );

	// Rewritten as text, not round-tripped through JSON.stringify: the map is
	// hand-maintained and re-serializing it reformats all 500 lines, burying the
	// two-line pin bump in noise no reviewer can scan.
	const raw = fs.readFileSync( MAP_FILE, 'utf8' );
	if ( ! /"published_parts"\s*:\s*\[/.test( raw ) ) {
		console.error(
			'[fetch-ui-bundle] could not find _meta.published_parts in component-map.json'
		);
		process.exit( 1 );
	}
	// Indented by hand to the surrounding tab style. This file is excluded from
	// the formatter, so nothing downstream will normalise it back.
	const partsJson =
		'[\n' +
		parts
			.map( ( part ) => `\t\t\t${ JSON.stringify( part ) }` )
			.join( ',\n' ) +
		'\n\t\t]';
	const mapPinned = raw
		.replace( /("ui_version_pinned"\s*:\s*")[^"]+(")/, `$1${ version }$2` )
		.replace(
			/("ui_manifest_url"\s*:\s*")[^"]+(")/,
			`$1${ cdnUrl( version, 'dist/manifest.json' ) }$2`
		)
		.replace( /("published_parts"\s*:\s*)\[[^\]]*\]/, `$1${ partsJson }` );
	const meta = JSON.parse( mapPinned )._meta;
	if (
		meta?.ui_version_pinned !== version ||
		! String( meta?.ui_manifest_url ).includes( `@${ version }/` ) ||
		meta?.published_parts?.join( ',' ) !== parts.join( ',' )
	) {
		console.error(
			'[fetch-ui-bundle] failed to rewrite the component-map pins; refusing to write'
		);
		process.exit( 1 );
	}
	fs.writeFileSync( MAP_FILE, mapPinned );

	console.log(
		`[fetch-ui-bundle] pinned roxyapi.php + component-map.json to ${ version }`
	);
}

const version = await resolveVersion();
console.log( `[fetch-ui-bundle] vendoring ${ PACKAGE }@${ version }` );

/**
 * Strip a trailing sourcemap reference.
 *
 * We vendor the runtime bundle but not its .map, so leaving the annotation in
 * makes every browser with devtools open request a file the plugin does not
 * ship and log a 404.
 *
 * @param {string} body File contents.
 * @return {string} Contents with any trailing sourcemap annotation removed.
 */
function stripSourcemapRef( body ) {
	const stripped = body.replace(
		/\n?\/\/#\s*sourceMappingURL=[^\n]*\s*$/,
		'\n'
	);
	if ( stripped.includes( 'sourceMappingURL' ) ) {
		console.error(
			'[fetch-ui-bundle] sourcemap annotation survived the strip; refusing to write'
		);
		process.exit( 1 );
	}
	return stripped;
}

/**
 * Download a single file for the pinned version, validate it, and write it.
 *
 * @param {string}                        url         jsDelivr URL to fetch.
 * @param {string}                        outFile     Absolute destination path.
 * @param {(body: string) => string|null} validate    Returns an error message if
 *                                                    the payload is not what we expect,
 *                                                    otherwise null.
 * @param {(body: string) => string}      [transform] Applied after validation.
 * @return {Promise<void>}
 */
async function vendor( url, outFile, validate, transform = ( body ) => body ) {
	console.log( `[fetch-ui-bundle] fetching ${ url }` );
	const response = await fetch( url );
	if ( ! response.ok ) {
		console.error(
			`[fetch-ui-bundle] download failed: ${ response.status } ${ response.statusText }`
		);
		process.exit( 1 );
	}
	const body = await response.text();
	const error = validate( body );
	if ( error ) {
		console.error( `[fetch-ui-bundle] ${ error }; refusing to write` );
		process.exit( 1 );
	}
	const output = transform( body );
	fs.writeFileSync( outFile, output );
	console.log(
		`[fetch-ui-bundle] wrote ${ output.length } bytes to ${ path.relative(
			ROOT,
			outFile
		) }`
	);
}

/**
 * Vendor one catalogue per language the pinned build ships, and delete any that
 * it does not.
 *
 * The prune half is the load-bearing one. Vendoring an older version on purpose
 * would otherwise leave catalogues from the newer one in place, and the plugin
 * enqueues whichever file it finds, so a site would be served labels built
 * against a bundle it is not running.
 *
 * @param {string} version The resolved version.
 * @return {Promise<void>}
 */
async function vendorLocales( version ) {
	const manifestUrl = cdnUrl( version, 'dist/manifest.json' );
	console.log( `[fetch-ui-bundle] fetching ${ manifestUrl }` );
	const response = await fetch( manifestUrl );
	if ( ! response.ok ) {
		console.error(
			`[fetch-ui-bundle] could not read the manifest: ${ response.status } ${ response.statusText }; refusing to write`
		);
		process.exit( 1 );
	}
	const { locales } = await response.json();
	const wanted = Array.isArray( locales ) ? locales : [];
	const unusable = wanted.filter( ( lang ) => ! /^[a-z]{2}$/.test( lang ) );
	if ( unusable.length > 0 ) {
		console.error(
			`[fetch-ui-bundle] manifest lists a locale that is not a two-letter code: ${ unusable.join(
				', '
			) }; refusing to write`
		);
		process.exit( 1 );
	}

	fs.mkdirSync( LOCALES_OUT_DIR, { recursive: true } );
	const stale = fs
		.readdirSync( LOCALES_OUT_DIR )
		.filter(
			( file ) =>
				file.endsWith( '.js' ) &&
				! wanted.includes( file.slice( 0, -'.js'.length ) )
		);
	for ( const file of stale ) {
		fs.rmSync( path.join( LOCALES_OUT_DIR, file ) );
		console.log(
			`[fetch-ui-bundle] removed assets/js/locales/${ file }, which ${ version } does not ship`
		);
	}

	if ( wanted.length === 0 ) {
		console.warn(
			`[fetch-ui-bundle] ${ PACKAGE }@${ version } ships no interface-label catalogues, so chart labels will read English on every site`
		);
		return;
	}

	for ( const lang of wanted ) {
		await vendor(
			cdnUrl( version, `dist/cdn/locales/${ lang }.js` ),
			path.join( LOCALES_OUT_DIR, `${ lang }.js` ),
			( body ) =>
				body.includes( LOCALE_REGISTRY )
					? null
					: `downloaded ${ lang } payload does not publish a catalogue (no ${ LOCALE_REGISTRY })`,
			stripSourcemapRef
		);
	}
}

await vendor(
	cdnUrl( version, 'dist/cdn/roxy-ui.js' ),
	JS_OUT_FILE,
	( body ) =>
		body.includes( 'customElements' )
			? null
			: 'downloaded payload does not look like the UI bundle (no customElements)',
	stripSourcemapRef
);

await vendor(
	cdnUrl( version, 'dist/styles/tokens.css' ),
	CSS_OUT_FILE,
	( body ) => {
		if ( ! body.includes( '--roxy-' ) ) {
			return 'downloaded tokens stylesheet has no --roxy-* custom properties';
		}
		if (
			! body.includes( 'prefers-color-scheme' ) &&
			! body.includes( '[data-theme' )
		) {
			return 'downloaded tokens stylesheet has no dark-mode block (prefers-color-scheme or [data-theme)';
		}
		return null;
	}
);

await vendorLocales( version );

// Last, so a failed or rejected download can never leave the pins advertising a
// version that is not the one sitting in assets/.
writeBackPins( version, await fetchPublishedParts( version ) );
