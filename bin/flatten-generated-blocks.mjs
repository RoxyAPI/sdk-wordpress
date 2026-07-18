#!/usr/bin/env node
/**
 * Flatten the built long-tail blocks so every block sits directly under
 * build/blocks/<block-name>/, one directory per block.
 *
 * wp-scripts preserves the source tree, so the spec-generated long-tail blocks
 * land nested at build/blocks/generated/<name>/ while the hand-written hero
 * blocks land at build/blocks/<name>/. RoxyAPI\\Blocks\\Registrar registers the
 * catalog with a single one-level glob (build/blocks/[*]/block.json), which the
 * nested blocks miss, so before this step only the hero blocks registered.
 * Moving each generated block up one level puts the whole catalog on that one
 * level, keeping registration to plain register_block_type with no Core
 * functions newer than the declared minimum WordPress version.
 *
 * Runs against build/ only (git-ignored), between `wp-scripts build` and
 * `post-build`. Idempotent: a second run finds no generated/ directory and
 * no-ops. Fails loudly if a generated block name would collide with a hero
 * block, since a silent overwrite would drop one of them.
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(
	path.dirname( fileURLToPath( import.meta.url ) ),
	'..'
);
const BLOCKS_DIR = path.join( ROOT, 'build', 'blocks' );
const GENERATED_DIR = path.join( BLOCKS_DIR, 'generated' );

if ( ! fs.existsSync( GENERATED_DIR ) ) {
	console.log(
		'[flatten-blocks] no build/blocks/generated directory; nothing to flatten'
	);
	process.exit( 0 );
}

const generated = fs
	.readdirSync( GENERATED_DIR, { withFileTypes: true } )
	.filter( ( entry ) => entry.isDirectory() )
	.map( ( entry ) => entry.name );

let moved = 0;
for ( const name of generated ) {
	const from = path.join( GENERATED_DIR, name );
	const to = path.join( BLOCKS_DIR, name );
	if ( fs.existsSync( to ) ) {
		console.error(
			`[flatten-blocks] name collision: build/blocks/${ name } already exists, cannot flatten build/blocks/generated/${ name }. Rename the endpoint or the hero block.`
		);
		process.exit( 1 );
	}
	fs.renameSync( from, to );
	moved++;
}

fs.rmdirSync( GENERATED_DIR );

console.log(
	`[flatten-blocks] moved ${ moved } generated block(s) up to build/blocks/, removed build/blocks/generated/`
);
