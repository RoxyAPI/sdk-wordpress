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
 * Network failures do not fail the build: a CDN blip should not block a merge.
 * Only a confirmed mismatch (manifest fetched, tag missing) is fatal.
 */
import { readFileSync } from 'node:fs';
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

// Pin-consistency guard (offline, deterministic). The @roxyapi/ui version is
// hand-copied to THREE places that drift independently: ROXYAPI_UI_VERSION in
// roxyapi.php (cache-bust + the version fetch-ui-bundle.mjs vendors), and both
// _meta.ui_version_pinned and the version embedded in _meta.ui_manifest_url
// here. A mismatch makes this very check validate against the wrong build (or
// silently against a stale manifest while the vendored bundle is newer). Fail
// loudly so all three move together on every UI bump.
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

// Adoption signal (informational, never fatal). The map is intentionally a
// subset of the build (helpers + reads that are fine as generic cards stay
// unbound), but a jump in this count after a version bump means new upstream
// components are available to wire up via a component-map row.
const unbound = available.size - referenced.size;
if ( unbound > 0 ) {
	console.log(
		`note: ${ unbound } of ${ available.size } component(s) in the pinned UI build are not bound to any operation. Bind in component-map.json to render them (unbound reads fall back to roxy-data).`
	);
}

console.log(
	`component-map.json OK: ${ referenced.size } of ${
		available.size
	} component tag(s) bound in the pinned UI build, ${
		Object.keys( map.operations || {} ).length
	} operationId(s) present in the live spec`
);
