#!/usr/bin/env node
/**
 * Vendor the @roxyapi/ui custom-element bundle and design tokens into assets/.
 *
 * The plugin ships both the bundle and its CSS custom-property defaults locally
 * rather than loading them from a CDN, because wordpress.org does not allow a
 * plugin to load executable JavaScript from a third-party host. Two files are
 * downloaded from jsDelivr and committed:
 *
 *   - the minified UMD bundle to assets/js/roxy-ui.js (the custom elements)
 *   - the design-token stylesheet to assets/css/roxy-ui-tokens.css (the
 *     `--roxy-*` defaults plus the automatic dark-mode block the elements read)
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

const PACKAGE = '@roxyapi/ui';
const SEMVER = /^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?$/;

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
 * Rewrite every pin to the version actually vendored, so the cache-buster, the
 * manifest the component-map check validates against, and the vendored bytes can
 * never disagree.
 *
 * @param {string} version The resolved version.
 * @return {void}
 */
function writeBackPins( version ) {
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
	const mapPinned = raw
		.replace( /("ui_version_pinned"\s*:\s*")[^"]+(")/, `$1${ version }$2` )
		.replace(
			/("ui_manifest_url"\s*:\s*")[^"]+(")/,
			`$1https://cdn.jsdelivr.net/npm/${ PACKAGE }@${ version }/dist/manifest.json$2`
		);
	const meta = JSON.parse( mapPinned )._meta;
	if (
		meta?.ui_version_pinned !== version ||
		! String( meta?.ui_manifest_url ).includes( `@${ version }/` )
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

await vendor(
	`https://cdn.jsdelivr.net/npm/${ PACKAGE }@${ version }/dist/cdn/roxy-ui.js`,
	JS_OUT_FILE,
	( body ) =>
		body.includes( 'customElements' )
			? null
			: 'downloaded payload does not look like the UI bundle (no customElements)',
	stripSourcemapRef
);

await vendor(
	`https://cdn.jsdelivr.net/npm/${ PACKAGE }@${ version }/dist/styles/tokens.css`,
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

// Last, so a failed or rejected download can never leave the pins advertising a
// version that is not the one sitting in assets/.
writeBackPins( version );
