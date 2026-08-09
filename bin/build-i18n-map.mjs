/**
 * Write the source map `wp i18n make-json` uses to decide which JavaScript file
 * each translation belongs to.
 *
 * WordPress names a script's Jed catalogue after the md5 of the script path
 * relative to the plugin root, and the path it hashes is the bundle the browser
 * loads: `build/blocks/<slug>/index.js`. The PO files reference the editor
 * SOURCE, because that is what a translator reads and what survives a checkout
 * with no build in it. Without a map, make-json would name every catalogue
 * after a source path and WordPress would never look for one.
 *
 * `_shared/generated-edit.js` is bundled into every generated block, so its
 * strings belong in every one of those catalogues; make-json takes a list of
 * destinations for exactly that case. A reference the map does not list is
 * dropped, which is also how the redundant built-bundle references stay out
 * when a POT happens to have been extracted with build/ present.
 *
 * Written under build/ because it describes what the last build produced and is
 * regenerated with it.
 */

import fs from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname( fileURLToPath( import.meta.url ) );
const ROOT = path.join( __dirname, '..' );
const BUILT_BLOCKS = path.join( ROOT, 'build', 'blocks' );
const SHARED_EDITOR = 'blocks/_shared/generated-edit.js';
const OUT = path.join( ROOT, 'build', 'i18n-map.json' );

/**
 * Whether a path exists, relative to the plugin root.
 * @param relative
 */
async function exists( relative ) {
	try {
		await fs.access( path.join( ROOT, relative ) );
		return true;
	} catch {
		return false;
	}
}

// make-json only ever writes; it never removes. Clearing first is what keeps a
// renamed or deleted block from leaving a catalogue behind that no script reads
// and no run refreshes. Only the md5-named JSON is ours to delete: the PO, MO
// and PHP catalogues beside it are the authored source.
const LANGUAGES = path.join( ROOT, 'languages' );
const STALE = /^roxyapi-[A-Za-z_]+-[0-9a-f]{32}\.json$/;
for ( const file of await fs.readdir( LANGUAGES ) ) {
	if ( STALE.test( file ) ) {
		await fs.rm( path.join( LANGUAGES, file ) );
	}
}

const entries = await fs.readdir( BUILT_BLOCKS, { withFileTypes: true } );
const map = {};
const generatedBundles = [];
let bundles = 0;

for ( const entry of entries ) {
	if ( ! entry.isDirectory() ) {
		continue;
	}
	const bundle = `build/blocks/${ entry.name }/index.js`;
	if ( ! ( await exists( bundle ) ) ) {
		continue;
	}
	bundles++;

	const generated = `blocks/generated/${ entry.name }/index.js`;
	if ( await exists( generated ) ) {
		map[ generated ] = bundle;
		generatedBundles.push( bundle );
		continue;
	}

	// Hero blocks are hand-written and split their editor across two files.
	for ( const file of [ 'index.js', 'edit.js' ] ) {
		const source = `blocks/${ entry.name }/${ file }`;
		if ( await exists( source ) ) {
			map[ source ] = bundle;
		}
	}
}

if ( generatedBundles.length > 0 ) {
	map[ SHARED_EDITOR ] = generatedBundles;
}

if ( bundles === 0 ) {
	await fs.rm( OUT, { force: true } );
	console.error(
		'[i18n-map] no built block bundles found. Run the block build first.'
	);
	process.exit( 1 );
}

await fs.writeFile( OUT, JSON.stringify( map, null, '\t' ) + '\n', 'utf8' );
console.log( `[i18n-map] wrote build/i18n-map.json (${ bundles } bundles)` );
