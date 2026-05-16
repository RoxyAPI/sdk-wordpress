#!/usr/bin/env node
/**
 * Distribution drift guard.
 *
 * Two release paths produce the published zip and they use opposite rules:
 *
 *   - Local: `npm run plugin-zip` (wp-scripts plugin-zip → npm-packlist) ships
 *     only entries listed in package.json `files`. Include-list semantics.
 *   - CI: 10up/action-wordpress-plugin-deploy ships every top-level entry
 *     except those matched by .distignore. Exclude-list semantics.
 *
 * Any top-level repo entry that is in neither list ships via 10up but is
 * absent from the local zip — silent drift that only surfaces after SVN
 * deploy. v1.0.3 leaked CITATION.cff, eslint.config.cjs, and patterns/
 * exactly this way.
 *
 * This script fails (exit 1) if any tracked top-level entry is neither
 * a member of the package.json `files` include-list nor matched by a
 * .distignore rule. Run before push via lefthook.
 */
import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const root = path.resolve(
	path.dirname( fileURLToPath( import.meta.url ) ),
	'..'
);

const tracked = execFileSync( 'git', [ 'ls-files' ], {
	cwd: root,
	encoding: 'utf8',
} )
	.split( '\n' )
	.filter( Boolean );

const topLevel = new Set();
for ( const f of tracked ) {
	const slash = f.indexOf( '/' );
	topLevel.add( slash === -1 ? f : f.slice( 0, slash ) );
}

const pkg = JSON.parse(
	readFileSync( path.join( root, 'package.json' ), 'utf8' )
);
const filesField = new Set(
	( pkg.files || [] ).map( ( e ) => e.replace( /\/$/, '' ) )
);

const distignore = readFileSync( path.join( root, '.distignore' ), 'utf8' )
	.split( '\n' )
	.map( ( l ) => l.trim() )
	.filter( ( l ) => l && ! l.startsWith( '#' ) );

const distignoreLeading = new Set(
	distignore
		.filter( ( l ) => l.startsWith( '/' ) )
		.map( ( l ) => l.slice( 1 ) )
);
const distignoreGlobs = distignore.filter( ( l ) => l.includes( '*' ) );

function matchedByDistignore( entry ) {
	if ( distignoreLeading.has( entry ) ) {
		return true;
	}
	for ( const glob of distignoreGlobs ) {
		const pattern =
			'^' + glob.replace( /\./g, '\\.' ).replace( /\*/g, '.*' ) + '$';
		if ( new RegExp( pattern ).test( entry ) ) {
			return true;
		}
	}
	return false;
}

const drift = [];
for ( const entry of topLevel ) {
	const inFiles = filesField.has( entry ) || filesField.has( entry + '/' );
	const inIgnore = matchedByDistignore( entry );
	if ( ! inFiles && ! inIgnore ) {
		drift.push( entry );
	}
}

if ( drift.length ) {
	console.error( '✗ Distribution drift detected.' );
	console.error( '' );
	console.error(
		'The following top-level entries are tracked in git but are:'
	);
	console.error(
		'  - NOT listed in package.json `files` (so npm-packlist excludes them locally)'
	);
	console.error(
		'  - NOT matched by any .distignore rule (so 10up rsync ships them to SVN)'
	);
	console.error( '' );
	console.error(
		'This produces a silent drift between `npm run plugin-zip` and the SVN deploy.'
	);
	console.error( '' );
	for ( const entry of drift.sort() ) {
		console.error( `  - ${ entry }` );
	}
	console.error( '' );
	console.error(
		'Fix by adding each to .distignore (if dev-only) or to package.json `files` (if shippable).'
	);
	process.exit( 1 );
}

console.log(
	`✓ no distribution drift (${ topLevel.size } top-level entries audited)`
);
