#!/usr/bin/env node
/**
 * Component-map drift guard.
 *
 * bin/component-map.json maps spec operationIds to @roxyapi/ui custom-element
 * tags. The plugin owns this map so a new endpoint can bind to an existing
 * component without waiting on a @roxyapi/ui release. The risk is the reverse:
 * a component tag in the map that no longer exists in the pinned @roxyapi/ui
 * build. When that happens the generated shortcode emits a tag the bundle never
 * defines, so the element never upgrades and the reader only ever sees the
 * server-rendered fallback.
 *
 * This script fetches the pinned manifest (the `_meta.ui_manifest_url` recorded
 * in component-map.json, which points at a concrete version, not @latest) and
 * fails (exit 1) if any mapped component tag is absent from it. The manifest
 * lists slugs without the `roxy-` prefix, e.g. `natal-chart`.
 *
 * The same manifest lists the interface-label catalogues, so this also fails if
 * the set vendored into assets/js/locales is not the set the pinned version
 * ships. That is the other half of the same failure: a pin moved by hand leaves
 * every translated site reading English labels and nothing errors.
 *
 * Network failures do not fail the build: a CDN blip should not block a merge.
 * Only a confirmed mismatch (manifest fetched, tag missing) is fatal.
 */
import { readdirSync, readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const root = path.resolve(
	path.dirname( fileURLToPath( import.meta.url ) ),
	'..'
);

const map = JSON.parse(
	readFileSync( path.join( root, 'bin', 'component-map.json' ), 'utf8' )
);

const manifestUrl = map?._meta?.ui_manifest_url;
if ( ! manifestUrl ) {
	console.error(
		'component-map.json is missing _meta.ui_manifest_url; cannot verify component tags.'
	);
	process.exit( 1 );
}

// Pin-consistency guard (offline, deterministic). The @roxyapi/ui version lives
// in THREE places: ROXYAPI_UI_VERSION in roxyapi.php (cache-bust + the version
// vendored), and both _meta.ui_version_pinned and the version embedded in
// _meta.ui_manifest_url here. fetch-ui-bundle.mjs writes all three together, so
// they should never disagree; this catches a hand-edit that bypassed it. A
// mismatch makes this very check validate against the wrong build, or silently
// against a stale manifest while the vendored bundle is newer.
const phpSource = readFileSync( path.join( root, 'roxyapi.php' ), 'utf8' );
const phpPin = phpSource.match(
	/const\s+ROXYAPI_UI_VERSION\s*=\s*'([^']+)'/
)?.[ 1 ];
const metaPin = map?._meta?.ui_version_pinned;
const urlPin = String( manifestUrl ).match( /@roxyapi\/ui@([^/]+)\// )?.[ 1 ];
if (
	phpPin &&
	metaPin &&
	urlPin &&
	! ( phpPin === metaPin && metaPin === urlPin )
) {
	console.error(
		'@roxyapi/ui version pins disagree (align all three, then `npm run fetch:ui`):'
	);
	console.error( `  roxyapi.php ROXYAPI_UI_VERSION       = ${ phpPin }` );
	console.error( `  component-map.json ui_version_pinned = ${ metaPin }` );
	console.error( `  component-map.json ui_manifest_url   = ${ urlPin }` );
	process.exit( 1 );
}

// Collect every tag referenced by the map, mapped to its slug form.
const referenced = new Map(); // slug -> tag
for ( const rows of Object.values( map.operations || {} ) ) {
	for ( const row of rows ) {
		const tag = String( row.component || '' );
		if ( ! /^roxy-[a-z-]+$/.test( tag ) ) {
			console.error(
				`Invalid component tag in map: ${ tag || '(empty)' }`
			);
			process.exit( 1 );
		}
		referenced.set( tag.replace( /^roxy-/, '' ), tag );
	}
}

let manifest;
try {
	const res = await fetch( manifestUrl, {
		signal: AbortSignal.timeout( 15000 ),
	} );
	if ( ! res.ok ) {
		throw new Error( `HTTP ${ res.status }` );
	}
	manifest = await res.json();
} catch ( err ) {
	console.warn(
		`Could not fetch ${ manifestUrl } (${ err.message }). Skipping component-map drift check; network issue, not a mismatch.`
	);
	process.exit( 0 );
}

const available = new Set( manifest.components || [] );
const missing = [];
for ( const [ slug, tag ] of referenced ) {
	if ( ! available.has( slug ) ) {
		missing.push( tag );
	}
}

if ( missing.length > 0 ) {
	console.error(
		`component-map.json references ${ missing.length } component tag(s) absent from ${ manifestUrl }:`
	);
	for ( const tag of missing.sort() ) {
		console.error( `  ${ tag }` );
	}
	console.error(
		'Either the @roxyapi/ui pin is wrong or a component was renamed/removed. Fix the map or the pinned version.'
	);
	process.exit( 1 );
}

// Catalogue guard: the pinned build's interface-label catalogues must all be
// vendored, and nothing else may sit alongside them. The plugin enqueues one by
// filename, so a pin moved without re-running `npm run fetch:ui` leaves every
// translated site reading English labels, and a catalogue left over from another
// version is served against a bundle it was not built for. Neither errors.
const expectedLocales = new Set( manifest.locales || [] );
let vendoredLocales;
try {
	vendoredLocales = new Set(
		readdirSync( path.join( root, 'assets', 'js', 'locales' ) )
			.filter( ( file ) => file.endsWith( '.js' ) )
			.map( ( file ) => file.slice( 0, -'.js'.length ) )
	);
} catch {
	vendoredLocales = new Set();
}

const missingLocales = [ ...expectedLocales ].filter(
	( lang ) => ! vendoredLocales.has( lang )
);
const extraLocales = [ ...vendoredLocales ].filter(
	( lang ) => ! expectedLocales.has( lang )
);
if ( missingLocales.length > 0 || extraLocales.length > 0 ) {
	console.error(
		`assets/js/locales does not match the catalogues shipped by ${ manifestUrl }:`
	);
	for ( const lang of missingLocales.sort() ) {
		console.error( `  missing ${ lang }.js` );
	}
	for ( const lang of extraLocales.sort() ) {
		console.error( `  ${ lang }.js is not shipped by the pinned version` );
	}
	console.error( 'Run `npm run fetch:ui` to re-vendor against the pin.' );
	process.exit( 1 );
}

// Coverage guard: every operationId in the map must still exist in the live
// spec. A renamed or removed endpoint leaves a dead map row that silently never
// binds, so the shortcode falls back to the generic card with no warning.
const SPEC_URL = 'https://roxyapi.com/api/v2/openapi.json';
let spec;
try {
	const res = await fetch( SPEC_URL, {
		signal: AbortSignal.timeout( 20000 ),
	} );
	if ( ! res.ok ) {
		throw new Error( `HTTP ${ res.status }` );
	}
	spec = await res.json();
} catch ( err ) {
	console.warn(
		`Could not fetch ${ SPEC_URL } (${ err.message }). Skipping operationId coverage check; network issue.`
	);
	console.log(
		`component-map.json OK: all ${ referenced.size } mapped component tag(s) exist in ${ manifestUrl }`
	);
	process.exit( 0 );
}

const specOps = new Set();
for ( const methods of Object.values( spec.paths || {} ) ) {
	for ( const op of Object.values( methods ) ) {
		if ( op && typeof op === 'object' && op.operationId ) {
			specOps.add( op.operationId );
		}
	}
}

const deadOps = Object.keys( map.operations || {} ).filter(
	( op ) => ! specOps.has( op )
);
if ( deadOps.length > 0 ) {
	console.error(
		`component-map.json maps ${ deadOps.length } operationId(s) that no longer exist in the live spec:`
	);
	for ( const op of deadOps.sort() ) {
		console.error( `  ${ op }` );
	}
	console.error(
		'These rows never bind. The endpoint was renamed or removed: update or drop the map row.'
	);
	process.exit( 1 );
}

// Binding-coverage guard, and the reason it counts OPERATIONS.
//
// This check used to report `available.size - referenced.size`: components in
// the build that no row mentions. That unit cannot see the failure it exists to
// catch. `roxy-numerology-card` is referenced by ONE row, so it counts as bound
// while nine of its eleven operations have no row at all and render as raw
// collapsed JSON. The counter read a clean 0 while 25 shipped shortcodes were
// broken. Measure the thing that breaks: an OPERATION with no binding.
//
// The join is published. `components-catalog.json` is in the @roxyapi/ui npm
// files whitelist and lists, per component, the operationIds it renders and the
// variant attrs each needs. It is fetched at the SAME pinned version as the
// manifest, so this compares against the build actually vendored.
const catalogUrl = manifestUrl.replace(
	/dist\/manifest\.json$/,
	'components-catalog.json'
);
let catalog;
try {
	const res = await fetch( catalogUrl, { signal: AbortSignal.timeout( 15000 ) } );
	if ( ! res.ok ) {
		throw new Error( `HTTP ${ res.status }` );
	}
	catalog = await res.json();
} catch ( err ) {
	console.warn(
		`Could not fetch ${ catalogUrl } (${ err.message }). Skipping binding-coverage check; network issue, not a mismatch.`
	);
	catalog = null;
}

if ( catalog ) {
	const components = Array.isArray( catalog )
		? catalog
		: catalog.components || [];
	const upstream = new Map(); // operationId -> { tag, attrs }
	for ( const component of components ) {
		for ( const endpoint of component.endpoints || [] ) {
			if ( endpoint?.operationId ) {
				upstream.set( endpoint.operationId, {
					tag: component.tag,
					attrs: endpoint.attrs || {},
				} );
			}
		}
	}

	const mapped = map.operations || {};
	const unboundOps = [ ...upstream.keys() ]
		.filter( ( op ) => ! mapped[ op ] )
		.sort();
	if ( unboundOps.length > 0 ) {
		console.error(
			`${ unboundOps.length } operation(s) are bound to a component upstream but have no row in component-map.json.`
		);
		console.error(
			'Each renders as a generic roxy-data dump today, which looks like a working page.'
		);
		for ( const op of unboundOps ) {
			console.error( `  ${ op } -> ${ upstream.get( op ).tag }` );
		}
		console.error( `Add the rows from ${ catalogUrl }.` );
		process.exit( 1 );
	}

	// A row can bind the right element to the wrong VIEW by dropping the
	// variant selector, which renders the component's default and errors
	// nowhere.
	const attrDrift = [];
	for ( const [ op, want ] of upstream ) {
		const rows = mapped[ op ] || [];
		const row = rows.find( ( r ) => r.component === want.tag );
		if ( ! row ) {
			continue;
		}
		for ( const [ key, value ] of Object.entries( want.attrs ) ) {
			if ( ( row.attrs || {} )[ key ] !== value ) {
				attrDrift.push( `${ op }: ${ want.tag } needs ${ key }="${ value }"` );
			}
		}
	}
	if ( attrDrift.length > 0 ) {
		console.error(
			`${ attrDrift.length } binding(s) are missing the variant attribute the catalogue declares:`
		);
		for ( const line of attrDrift.sort() ) {
			console.error( `  ${ line }` );
		}
		console.error(
			'Without it the component renders its default view for every one of these operations.'
		);
		process.exit( 1 );
	}

	console.log(
		`binding coverage OK: all ${ upstream.size } upstream-bound operation(s) have a row, variant attributes match`
	);
}

console.log(
	`component-map.json OK: ${ referenced.size } of ${
		available.size
	} component tag(s) bound in the pinned UI build, ${
		Object.keys( map.operations || {} ).length
	} operationId(s) present in the live spec`
);
