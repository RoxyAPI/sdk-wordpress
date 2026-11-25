/**
 * The `x.y` branch of a WordPress version, as a comparable number pair.
 *
 * @param {string} version For example `7.2-RC3`, `7.1.5` or `7.2`.
 * @return {number[]} Major and minor.
 */
export function branch( version ) {
	const match = /^(\d+)\.(\d+)/.exec( version );
	if ( ! match ) {
		throw new Error( `Not a WordPress version: ${ version }` );
	}
	return [ Number( match[ 1 ] ), Number( match[ 2 ] ) ];
}

const compare = ( a, b ) => a[ 0 ] - b[ 0 ] || a[ 1 ] - b[ 1 ];

/**
 * The newest branch WordPress offers when `tested` is behind it, else null.
 *
 * @param {string}   tested The readme `Tested up to` value.
 * @param {string[]} offers Versions offered, release candidates included.
 * @return {string|null} The branch to test against.
 */
export function newerBranch( tested, offers ) {
	if ( offers.length === 0 ) {
		throw new Error( 'WordPress.org offered no version.' );
	}
	const newest = offers.map( branch ).sort( compare ).at( -1 );
	return compare( branch( tested ), newest ) < 0 ? newest.join( '.' ) : null;
}
