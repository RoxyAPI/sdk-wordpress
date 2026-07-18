#!/usr/bin/env node
/**
 * Block layout drift guard.
 *
 * Every built block must sit flat at build/blocks/<name>/block.json so the
 * one-level glob in RoxyAPI\Blocks\Registrar registers the whole catalog. A
 * nested block (the pre-1.6.0 build/blocks/generated/<name>/ layout) is missed
 * by that glob and silently never registers, which is the exact regression this
 * check exists to catch. It also fails if the catalog collapses back to the
 * handful of hero blocks.
 *
 * Run after `npm run build:all` (the CI lint job and the pre-push hook do). It
 * reads only the built output, so it needs a build present and never touches
 * the network.
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(
	path.dirname( fileURLToPath( import.meta.url ) ),
	'..'
);
const BLOCKS_DIR = path.join( ROOT, 'build', 'blocks' );

// A floor well below the real count (~142) but far above the 7 hero blocks, so
// a regression that drops the long-tail catalog fails loudly.
const MIN_BLOCKS = 100;
// A block that only exists as a spec-generated long-tail block. If it sits flat
// with a block.json, the generate to flatten path is intact end to end.
const SENTINEL = 'generate-bodygraph';

const fail = ( msg ) => {
	console.error( `[check-block-layout] ${ msg }` );
	process.exit( 1 );
};

if ( ! fs.existsSync( BLOCKS_DIR ) ) {
	fail( 'build/blocks missing; run `npm run build:all` first' );
}

if ( fs.existsSync( path.join( BLOCKS_DIR, 'generated' ) ) ) {
	fail(
		'build/blocks/generated/ still exists: the long-tail blocks are nested, so the one-level glob in Registrar cannot register them. bin/flatten-generated-blocks.mjs must run in build:all after wp-scripts build.'
	);
}

const blocks = fs
	.readdirSync( BLOCKS_DIR, { withFileTypes: true } )
	.filter( ( entry ) => entry.isDirectory() )
	.filter( ( entry ) =>
		fs.existsSync( path.join( BLOCKS_DIR, entry.name, 'block.json' ) )
	)
	.map( ( entry ) => entry.name );

if ( blocks.length < MIN_BLOCKS ) {
	fail(
		`only ${ blocks.length } flat block(s) with a block.json, expected at least ${ MIN_BLOCKS }. The long-tail catalog is missing.`
	);
}

if ( ! blocks.includes( SENTINEL ) ) {
	fail(
		`the generated block "${ SENTINEL }" is not flat at build/blocks/${ SENTINEL }/block.json; the generate to flatten path is broken`
	);
}

console.log(
	`[check-block-layout] OK: ${ blocks.length } flat blocks, "${ SENTINEL }" present`
);
