#!/usr/bin/env node
/**
 * Fails when `Tested up to` in readme.txt is behind the newest WordPress
 * branch, counting a release candidate. The upper bound needs no check here:
 * Plugin Check rejects a version that does not exist yet.
 */
import { readFileSync } from 'node:fs';
import { newerBranch } from './wp-version.mjs';

const readme = readFileSync(
	new URL( '../readme.txt', import.meta.url ),
	'utf8'
);
const tested = /^Tested up to:\s*(\S+)/m.exec( readme )?.[ 1 ] ?? '';

const response = await fetch(
	`https://api.wordpress.org/core/version-check/1.7/?channel=rc&version=${ tested }`,
	{ signal: AbortSignal.timeout( 30000 ) }
);
if ( ! response.ok ) {
	throw new Error( `WordPress.org answered ${ response.status }.` );
}
const { offers } = await response.json();
const due = newerBranch(
	tested,
	offers.map( ( offer ) => offer.version )
);
if ( due ) {
	console.error(
		`readme.txt says "Tested up to: ${ tested }" and WordPress ${ due } is out or in release candidate. Test on it, then set "Tested up to: ${ due }".`
	);
	process.exit( 1 );
}
console.log( `Tested up to ${ tested } is current.` );
