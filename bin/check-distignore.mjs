#!/usr/bin/env node
/**
 * Match tracked top-level entries across local ZIP and SVN distributions.
 * Keep the generated plugin archive out of both packages.
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
const archive = `${ pkg.name }.zip`;
topLevel.add( archive );

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
	// A directory is shipped when it is listed whole or through a path inside it.
	const inFiles = [ ...filesField ].some(
		( f ) => f === entry || f.startsWith( entry + '/' )
	);
	const inIgnore = matchedByDistignore( entry );
	if ( ( ! inFiles && ! inIgnore ) || ( entry === archive && inFiles ) ) {
		drift.push( entry );
	}
}

if ( drift.length ) {
	console.error( '✗ Distribution drift detected.' );
	console.error( '' );
	for ( const entry of drift.sort() ) {
		console.error(
			entry === archive
				? `  - ${ entry }: exclude in .distignore and keep out of package.json files.`
				: `  - ${ entry }: include in package.json files or exclude in .distignore.`
		);
	}
	process.exit( 1 );
}

console.log(
	`✓ no distribution drift (${ topLevel.size } top-level entries audited)`
);
