#!/usr/bin/env node
/**
 * Vendor the @roxyapi/ui custom-element bundle and design tokens into assets/.
 *
 * The plugin ships both the bundle and its CSS custom-property defaults locally
 * rather than loading them from a CDN. This script reads the pinned version from
 * the `ROXYAPI_UI_VERSION` constant in roxyapi.php and downloads two files from
 * jsDelivr for that version:
 *
 *   - the minified UMD bundle to assets/js/roxy-ui.js (the custom elements)
 *   - the design-token stylesheet to assets/css/roxy-ui-tokens.css (the
 *     `--roxy-*` defaults plus the automatic dark-mode block the elements read)
 *
 * Both vendored files are committed and are the source of truth; this script is
 * run by hand, never wired into the build, so builds stay offline and
 * deterministic.
 *
 * Re-run this whenever ROXYAPI_UI_VERSION in roxyapi.php is bumped.
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(
	path.dirname( fileURLToPath( import.meta.url ) ),
	'..'
);
const PLUGIN_FILE = path.join( ROOT, 'roxyapi.php' );
const JS_OUT_FILE = path.join( ROOT, 'assets', 'js', 'roxy-ui.js' );
const CSS_OUT_FILE = path.join( ROOT, 'assets', 'css', 'roxy-ui-tokens.css' );

const source = fs.readFileSync( PLUGIN_FILE, 'utf8' );
const match = source.match( /const\s+ROXYAPI_UI_VERSION\s*=\s*'([^']+)'/ );
if ( ! match ) {
	console.error(
		'[fetch-ui-bundle] could not find ROXYAPI_UI_VERSION in roxyapi.php'
	);
	process.exit( 1 );
}

const version = match[ 1 ];

/**
 * Download a single file for the pinned version, validate it, and write it.
 *
 * @param {string}                        url      jsDelivr URL to fetch.
 * @param {string}                        outFile  Absolute destination path.
 * @param {(body: string) => string|null} validate Returns an error message if
 *                                                 the payload is not what we expect,
 *                                                 otherwise null.
 * @return {Promise<void>}
 */
async function vendor( url, outFile, validate ) {
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
	fs.writeFileSync( outFile, body );
	console.log(
		`[fetch-ui-bundle] wrote ${ body.length } bytes to ${ path.relative(
			ROOT,
			outFile
		) }`
	);
}

await vendor(
	`https://cdn.jsdelivr.net/npm/@roxyapi/ui@${ version }/dist/cdn/roxy-ui.js`,
	JS_OUT_FILE,
	( body ) =>
		body.includes( 'customElements' )
			? null
			: 'downloaded payload does not look like the UI bundle (no customElements)'
);

await vendor(
	`https://cdn.jsdelivr.net/npm/@roxyapi/ui@${ version }/dist/styles/tokens.css`,
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
