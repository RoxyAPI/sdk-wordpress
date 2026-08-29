#!/usr/bin/env node
/**
 * RoxyAPI WordPress plugin code generator.
 *
 * Fetches the live RoxyAPI OpenAPI spec, writes it to specs/openapi.json
 * (the git-tracked baseline for drift detection), and emits the auto-generated
 * PHP client layer plus per-endpoint block.json files into src/Generated/ and
 * blocks/generated/. Hand crafted hero blocks and shortcodes in src/ and blocks/
 * are NEVER overwritten.
 *
 * Generated outputs:
 *   specs/openapi.json                 - committed spec snapshot (drift baseline)
 *   src/Generated/Client.php           - typed PHP method per endpoint
 *   src/Generated/Endpoints.php        - endpoint registry with TTLs
 *   src/Generated/Shortcodes/*.php     - one shortcode class per non-hero endpoint
 *   blocks/generated/{slug}/block.json - one generated block.json per non-hero endpoint
 *
 * Usage:
 *   npm run generate
 *
 * Drift check (CI and pre-push hook):
 *   npm run generate:check
 *
 * See docs/wordpress-plugin.md section 7.5 in the parent project for the full
 * design rationale.
 */

import fs from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname( fileURLToPath( import.meta.url ) );
const ROOT = path.resolve( __dirname, '..' );

const OPENAPI_URL =
	process.env.ROXYAPI_OPENAPI_URL ||
	'https://roxyapi.com/api/v2/openapi.json';
const SPEC_FILE = path.join( ROOT, 'specs', 'openapi.json' );
const OUT_PHP = path.join( ROOT, 'src', 'Generated' );
const OUT_BLOCKS = path.join( ROOT, 'blocks', 'generated' );

const ttlMap = JSON.parse(
	await fs.readFile( path.join( __dirname, 'ttl-map.json' ), 'utf8' )
);
const heroList = JSON.parse(
	await fs.readFile( path.join( __dirname, 'hero-endpoints.json' ), 'utf8' )
);
const heroConfig = JSON.parse(
	await fs.readFile( path.join( __dirname, 'hero-config.json' ), 'utf8' )
);
const exampleOverride = JSON.parse(
	await fs.readFile(
		path.join( __dirname, 'example-overrides.json' ),
		'utf8'
	)
);
const domainRegistry = JSON.parse(
	await fs.readFile( path.join( __dirname, 'domains.json' ), 'utf8' )
);
const componentMap = JSON.parse(
	await fs.readFile( path.join( __dirname, 'component-map.json' ), 'utf8' )
);

/**
 * Apply per-field overrides over a raw spec example. Used when the OpenAPI spec
 * ships a stale or invalid example value (e.g. timezone="-5" that the live
 * validator rejects). Op-specific overrides beat _global.
 * @param operationId
 * @param fieldName
 * @param specExample
 */
function applyExampleOverride( operationId, fieldName, specExample ) {
	const opOverride = exampleOverride[ operationId ];
	if (
		opOverride &&
		Object.prototype.hasOwnProperty.call( opOverride, fieldName )
	) {
		return opOverride[ fieldName ];
	}
	const globalOverride = exampleOverride._global || {};
	if ( Object.prototype.hasOwnProperty.call( globalOverride, fieldName ) ) {
		return globalOverride[ fieldName ];
	}
	return specExample;
}
const heroSet = new Set( heroList );

/**
 * Retry with exponential backoff: a transient upstream error (e.g. a CDN 520)
 * must not fail the release or drift-check run.
 * @param {string} url
 * @param {number} attempts
 */
async function fetchSpec( url, attempts = 5 ) {
	for ( let attempt = 1; ; attempt++ ) {
		try {
			const res = await fetch( url );
			if ( ! res.ok ) {
				throw new Error( `HTTP ${ res.status } ${ res.statusText }` );
			}
			return await res.json();
		} catch ( err ) {
			if ( attempt === attempts ) {
				console.error(
					`[generate] fetch failed after ${ attempts } attempts: ${ err.message }`
				);
				process.exit( 1 );
			}
			const delay = 2 ** attempt;
			console.warn(
				`[generate] fetch attempt ${ attempt }/${ attempts } failed (${ err.message }), retrying in ${ delay }s`
			);
			await new Promise( ( resolve ) =>
				setTimeout( resolve, delay * 1000 )
			);
		}
	}
}

/**
 * Load the spec from disk when `ROXYAPI_SPEC_FILE` is set, from `OPENAPI_URL` otherwise.
 *
 * Orthogonal to `ROXYAPI_OPENAPI_URL`, which points the fetch at a different server. This one
 * skips the network entirely, keeping generation offline and byte-reproducible, which is what
 * the generation drift check in CI relies on.
 */
async function loadSpec() {
	const file = process.env.ROXYAPI_SPEC_FILE;
	if ( file ) {
		console.log(
			`[generate] reading ${ file } (offline, ROXYAPI_SPEC_FILE)`
		);
		return JSON.parse( await fs.readFile( file, 'utf8' ) );
	}
	console.log( `[generate] fetching ${ OPENAPI_URL }` );
	return fetchSpec( OPENAPI_URL );
}

const spec = await loadSpec();
console.log(
	`[generate] loaded spec with ${
		Object.keys( spec.paths || {} ).length
	} paths`
);

// Write the spec snapshot
await fs.mkdir( path.dirname( SPEC_FILE ), { recursive: true } );
await fs.writeFile( SPEC_FILE, JSON.stringify( spec, null, 2 ) + '\n', 'utf8' );
console.log( `[generate] wrote ${ SPEC_FILE }` );

// Prepare output directories (clean them first)
await fs.rm( path.join( OUT_PHP, 'Shortcodes' ), {
	recursive: true,
	force: true,
} );
await fs.mkdir( path.join( OUT_PHP, 'Shortcodes' ), { recursive: true } );
await fs.rm( path.join( OUT_PHP, 'Heroes' ), { recursive: true, force: true } );
await fs.mkdir( path.join( OUT_PHP, 'Heroes' ), { recursive: true } );
await fs.rm( path.join( OUT_PHP, 'Forms' ), { recursive: true, force: true } );
await fs.mkdir( path.join( OUT_PHP, 'Forms' ), { recursive: true } );
// Keep the README in OUT_PHP
const readmePath = path.join( OUT_PHP, 'README.md' );
const readmeExists = await fs.stat( readmePath ).catch( () => null );
const readmeContent = readmeExists
	? await fs.readFile( readmePath, 'utf8' )
	: null;
await fs.rm( OUT_BLOCKS, { recursive: true, force: true } );
await fs.mkdir( OUT_BLOCKS, { recursive: true } );

// ---------------------------------------------------------------------------
// Walk spec.paths and collect operations
// ---------------------------------------------------------------------------

/** @type {Array<{operationId: string, method: string, path: string, tag: string, summary: string, description: string, parameters: any[], requestBody: any}>} */
const operations = [];

for ( const [ apiPath, methods ] of Object.entries( spec.paths || {} ) ) {
	for ( const [ method, op ] of Object.entries( methods ) ) {
		if (
			! [ 'get', 'post', 'put', 'patch', 'delete' ].includes( method )
		) {
			continue;
		}
		if ( ! op.operationId ) {
			continue;
		}
		operations.push( {
			operationId: op.operationId,
			method: method.toUpperCase(),
			path: apiPath,
			tag: ( op.tags || [] )[ 0 ] || 'Other',
			summary: op.summary || '',
			description: op.description || '',
			parameters: op.parameters || [],
			requestBody: op.requestBody || null,
		} );
	}
}

console.log(
	`[generate] found ${ operations.length } operations (${
		heroSet.size
	} hero, ${
		operations.length -
		operations.filter( ( o ) => heroSet.has( o.operationId ) ).length
	} generated)`
);

// Split hero vs generated
const generated = operations.filter( ( o ) => ! heroSet.has( o.operationId ) );

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Resolve a $ref pointer like "#/components/schemas/Foo" against the spec
 * @param obj
 */
function resolveRef( obj ) {
	if ( ! obj || typeof obj !== 'object' ) {
		return obj;
	}
	if ( obj.$ref ) {
		const path = obj.$ref.replace( '#/', '' ).split( '/' );
		let resolved = spec;
		for ( const segment of path ) {
			resolved = resolved?.[ segment ];
		}
		return resolved || obj;
	}
	// Handle allOf: merge all schemas together
	if ( Array.isArray( obj.allOf ) ) {
		let merged = {};
		for ( const part of obj.allOf ) {
			const resolved = resolveRef( part );
			merged = {
				...merged,
				...resolved,
				properties: { ...merged.properties, ...resolved.properties },
				required: [
					...( merged.required || [] ),
					...( resolved.required || [] ),
				],
			};
		}
		return merged;
	}
	return obj;
}

/**
 * Convert operationId to PascalCase PHP class name
 * @param str
 */
function toPascalCase( str ) {
	return str
		.replace( /(^|[^a-zA-Z0-9])([a-z])/g, ( _, _sep, c ) =>
			c.toUpperCase()
		)
		.replace( /^[a-z]/, ( c ) => c.toUpperCase() )
		.replace( /[^a-zA-Z0-9]/g, '' );
}

/**
 * Convert operationId to snake_case shortcode tag
 * @param str
 */
function toSnakeCase( str ) {
	return str
		.replace( /([a-z])([A-Z])/g, '$1_$2' )
		.replace( /[^a-zA-Z0-9]/g, '_' )
		.toLowerCase();
}

/**
 * Convert operationId to kebab-case block slug
 * @param str
 */
function toKebabCase( str ) {
	return str
		.replace( /([a-z])([A-Z])/g, '$1-$2' )
		.replace( /[^a-zA-Z0-9]/g, '-' )
		.toLowerCase();
}

/**
 * Pick a Dashicon deterministically from the tag name via simple hash
 * @param tag
 */
function tagToIcon( tag ) {
	const icons = [
		'star-filled',
		'star-half',
		'heart',
		'cloud',
		'book',
		'chart-line',
		'chart-bar',
		'calculator',
		'format-image',
		'database',
		'admin-customizer',
		'lightbulb',
		'visibility',
		'portfolio',
		'palmtree',
		'universal-access',
		'shield',
		'superhero',
		'tide',
		'games',
	];
	let hash = 0;
	for ( let i = 0; i < tag.length; i++ ) {
		hash = ( ( hash << 5 ) - hash + tag.charCodeAt( i ) ) | 0;
	}
	return icons[ Math.abs( hash ) % icons.length ];
}

/**
 * Get TTL for an operation from the ttl map
 * @param operationId
 */
function getTtl( operationId ) {
	return ttlMap[ operationId ] ?? 0;
}

/**
 * Extract path parameters from an OpenAPI path like /astrology/signs/{id}
 * @param apiPath
 */
function extractPathParams( apiPath ) {
	const matches = apiPath.match( /\{([^}]+)\}/g );
	if ( ! matches ) {
		return [];
	}
	return matches.map( ( m ) => m.slice( 1, -1 ) );
}

/**
 * Build the API path for the Client, with path params as PHP variables
 * @param apiPath
 */
function buildPhpPath( apiPath ) {
	// /astrology/signs/{id} -> astrology/signs/' . rawurlencode( $id ) . '
	const cleaned = apiPath.startsWith( '/' ) ? apiPath.slice( 1 ) : apiPath;
	if ( ! cleaned.includes( '{' ) ) {
		return `'${ cleaned }'`;
	}

	const parts = cleaned.split( /(\{[^}]+\})/ );
	const phpParts = parts.map( ( p ) => {
		if ( p.startsWith( '{' ) && p.endsWith( '}' ) ) {
			const param = p.slice( 1, -1 );
			return `' . rawurlencode( $${ toPhpVar( param ) } ) . '`;
		}
		return p;
	} );
	return `'${ phpParts.join( '' ) }'`;
}

/**
 * Safe PHP variable name
 * @param name
 */
function toPhpVar( name ) {
	return name.replace( /[^a-zA-Z0-9_]/g, '_' );
}

/**
 * Convert an OpenAPI parameter name (camelCase or snake_case) into the snake_case
 * form WordPress will see in $atts. The shortcode parser lowercases attribute
 * keys, so a default of `birthDate` would never match the user's `birthDate=...`
 * input (which arrives as `birthdate`). Snake_case sidesteps the issue and is
 * the conventional WordPress shortcode attribute style anyway.
 * @param name
 */
function toSnakeAttr( name ) {
	return name
		.replace( /([a-z0-9])([A-Z])/g, '$1_$2' )
		.replace( /([A-Z]+)([A-Z][a-z])/g, '$1_$2' )
		.toLowerCase();
}

/**
 * Extract query/path parameters for a GET endpoint
 * @param op
 */
function extractParams( op ) {
	const params = [];
	for ( const p of op.parameters || [] ) {
		const resolvedSchema = resolveRef( p.schema );
		params.push( {
			name: p.name,
			in: p.in,
			required: p.required || false,
			type: resolvedSchema?.type || 'string',
			enum: resolvedSchema?.enum,
			format: resolvedSchema?.format,
			description: p.description || '',
			// Example for the demo defaults emitter. Falls back through both
			// the parameter object and its resolved schema, since either may
			// carry the canonical sample value.
			example: p.example ?? resolvedSchema?.example,
		} );
	}
	return params;
}

/**
 * Extract body fields for a POST endpoint
 * @param op
 */
function extractBodyFields( op ) {
	if ( ! op.requestBody ) {
		return [];
	}
	const content = op.requestBody.content?.[ 'application/json' ];
	if ( ! content?.schema ) {
		return [];
	}
	const schema = resolveRef( content.schema );
	const props = schema.properties || {};
	const required = new Set( schema.required || [] );
	return Object.entries( props ).map( ( [ name, prop ] ) => {
		const resolvedProp = resolveRef( prop );
		// anyOf/oneOf of number + string (e.g. `timezone`: decimal hours OR IANA
		// name). resolvedProp.type is undefined for these, so the body builder
		// would send the shortcode attr as a raw string and the API rejects
		// "5.5" (matches neither the number nor the IANA-string pattern).
		const variants = resolvedProp.anyOf || resolvedProp.oneOf;
		const numericString =
			Array.isArray( variants ) &&
			variants.some( ( v ) => {
				const t = resolveRef( v ).type;
				return t === 'number' || t === 'integer';
			} );
		return {
			name,
			required: required.has( name ),
			type: resolvedProp.type || 'string',
			enum: resolvedProp.enum,
			format: resolvedProp.format,
			numericString,
			description: resolvedProp.description || '',
			example: resolvedProp.example,
		};
	} );
}

/**
 * Whether a field's own documentation says it defaults to the moment the
 * request is made.
 *
 * A copy-paste sample must not freeze one. `year="2026" month="8"` and
 * `year="2026" month="2"` shipped on two ephemeris cards that disagreed with
 * each other, and both were wrong the moment the month turned. Omitting the
 * attribute is the correct sample: the reading then answers for the current
 * month on every page it is pasted onto, which is what the endpoint documents.
 * @param field Parameter or body field carrying `required` and `description`.
 */
function isCurrentDefaulting( field ) {
	return (
		! field.required &&
		/defaults? to (the )?(current|today|now)/i.test(
			String( field.description || '' )
		)
	);
}

/**
 * The flat inputs a generated block exposes: POST body fields, or GET path
 * params (always required) plus query params. The single source both the block
 * attributes (emitBlockJson) and the editor fields (deriveBlockFields) derive
 * from, so a block can never have an attribute without a control or vice versa.
 * @param op
 */
function blockInputs( op ) {
	if ( op.method === 'POST' ) {
		return extractBodyFields( op );
	}
	return [
		...extractPathParams( op.path ).map( ( name ) => ( {
			name,
			required: true,
			type: 'string',
		} ) ),
		...extractParams( op ).filter( ( p ) => p.in === 'query' ),
	];
}

/**
 * True when an operation's request body has at least one REQUIRED nested-object
 * (or array-of-object) field. These shapes cannot be expressed as flat
 * shortcode attributes; the generator emits a visitor-form-mode shortcode
 * (FormRenderer) instead. v1.0 ships forms; v1.1 adds nested-attribute blocks.
 * @param op
 */
function hasRequiredObjectBody( op ) {
	if ( op.method !== 'POST' ) {
		return false;
	}
	const content = op.requestBody?.content?.[ 'application/json' ];
	if ( ! content?.schema ) {
		return false;
	}
	const schema = resolveRef( content.schema );
	const required = new Set( schema.required || [] );
	for ( const [ name, prop ] of Object.entries( schema.properties || {} ) ) {
		if ( ! required.has( name ) ) {
			continue;
		}
		const resolved = resolveRef( prop );
		if ( resolved.type === 'object' ) {
			return true;
		}
		if ( resolved.type === 'array' ) {
			const items = resolveRef( resolved.items || {} );
			if ( items.type === 'object' ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Humanise a property name for use as a form label.
 * `birthDate` -> `Birth date`, `house_system` -> `House system`.
 * @param name
 */
function humanLabel( name ) {
	const spaced = name
		.replace( /([a-z])([A-Z])/g, '$1 $2' )
		.replace( /([A-Za-z])([0-9])/g, '$1 $2' )
		.replace( /[_-]+/g, ' ' )
		.trim();
	return spaced.charAt( 0 ).toUpperCase() + spaced.slice( 1 ).toLowerCase();
}

/**
 * Split a spec description into sentences, without splitting on abbreviations
 * like "e.g. 5.5" (which previously truncated timezone help to "Decimal
 * hours (e.g").
 * @param text
 */
function splitSentences( text ) {
	const masked = String( text ).replace(
		/\b(e\.g|i\.e|vs|etc|approx)\./gi,
		( m ) => m.replaceAll( '.', '\u0000' )
	);
	return masked
		.split( /\.\s/ )
		.map( ( sentence ) => sentence.replaceAll( '\u0000', '.' ) );
}

/**
 * First sentence of a spec description.
 * @param text
 */
function firstSentence( text ) {
	return splitSentences( text )[ 0 ];
}

// ---------------------------------------------------------------------------
// Display names
//
// A spec summary is written for a developer reading an endpoint list, so it
// leads with the HTTP verb and carries search keywords: "Get the twelve Arudha
// padas - Arudha Lagna Calculator API". A site owner is placing a reading on a
// page, so a block title, an inserter entry and a visitor-facing form heading
// need the SUBJECT of the operation and nothing else. Everything below derives
// that from the spec, so the name has one home and no list is hand-maintained.
// ---------------------------------------------------------------------------

/** Words a title keeps lowercase unless they lead it. */
const TITLE_SMALL_WORDS = new Set( [
	'a',
	'an',
	'and',
	'as',
	'at',
	'but',
	'by',
	'for',
	'from',
	'in',
	'of',
	'on',
	'or',
	'the',
	'to',
	'with',
	'per',
	'via',
] );

/**
 * Verbs a summary opens with because it describes a call rather than a reading.
 *
 * `search`, `draw` and `cast` are deliberately absent: they name what the
 * reading DOES and are sometimes the only thing separating two operations
 * (`listCrystals` and `searchCrystals` both reduce to "Crystals" without them).
 */
const SUMMARY_LEAD_VERBS =
	/^(get|list|calculate|generate|detect|check|find|analy[sz]e|look\s?up|fetch|retrieve|create)\b[\s:]*/i;
const SUMMARY_LEAD_FILLER = /^(all|the|a|an|any|your|complete|full|and)\s+/i;
/**
 * The subset of those verbs where the noun IS the answer, so the bare noun
 * names the reading on its own. Everything else names what the reading DOES.
 * Read only when two operations in one domain collapse to the same name.
 */
const SUMMARY_RETRIEVAL_VERBS = /^(get|list|look\s?up|fetch|retrieve)\b/i;
/**
 * A leading count, dropped because "the 24 solar terms" is a reading called
 * Solar Terms. Case sensitive on purpose: a spelled-out number CAPITALISED in
 * the summary is part of the technique's name, not a count of anything, so
 * Eight Mansions and Five Elements keep theirs while "the five elements" loses
 * it the same way "the 27 nakshatras" does.
 */
const SUMMARY_LEAD_COUNT =
	/^(\d+|one|two|three|four|five|six|seven|eight|nine|ten|eleven|twelve)\s+/;

/**
 * Where a descriptive tail begins, so a long name can be cut back to its
 * subject. Unordered: the shortest surviving cut wins.
 */
const NAME_TAIL_CONNECTORS = [
	':',
	' (',
	',',
	' for ',
	' with ',
	' from ',
	' including ',
	' at ',
];

/** Above this many characters a name stops being readable at a glance. */
const NAME_LENGTH_BUDGET = 32;

/**
 * Drop "API" and the keyword noun it usually travels with.
 *
 * The word must never reach a block title or a form heading: the site owner is
 * placing a reading, not calling an API. Guarded by `assertNoApiWording`.
 * @param text
 */
function stripApiWords( text ) {
	return String( text )
		.replace( /\s*\(\s*API\s*\)/gi, '' )
		.replace( /\b(calculator|reference|detail|glossary)?\s*API\b/gi, ' ' )
		.replace( /\s{2,}/g, ' ' )
		.trim()
		.replace( /[\s,:;-]+$/, '' );
}

/**
 * Title-case a derived name.
 *
 * A token already carrying a capital past its first letter is left alone, which
 * is what keeps `KP`, `D9`, `I-Ching` and `BG5` intact.
 * @param text
 */
function titleCase( text ) {
	let position = 0;
	return String( text )
		.split( /(\s+|\/)/ )
		.map( ( token ) => {
			if ( /^(\s+|\/)$/.test( token ) ) {
				return token;
			}
			const index = position++;
			if ( /[A-Z]/.test( token.slice( 1 ) ) ) {
				return token;
			}
			const lower = token.toLowerCase();
			if ( lower === 'id' ) {
				return 'ID';
			}
			if (
				index > 0 &&
				TITLE_SMALL_WORDS.has( lower.replace( /[^a-z]/g, '' ) )
			) {
				return lower;
			}
			return lower.replace( /[a-z]/, ( c ) => c.toUpperCase() );
		} )
		.join( '' );
}

/**
 * The subject of an operation, taken from its own summary.
 * @param summary
 * @param operationId
 * @param keepVerb    Keep the lead verb, which `buildDisplayNames` asks for when
 *                    dropping it would make this operation read the same as a peer in its domain.
 */
function deriveDisplayName( summary, operationId, keepVerb = false ) {
	const head = stripApiWords(
		String( summary || operationId ).split( /\s-\s|\.\s/ )[ 0 ]
	);
	let name = head;
	let previous;
	do {
		previous = name;
		if ( ! keepVerb ) {
			name = name.replace( SUMMARY_LEAD_VERBS, '' );
		}
		name = name
			.replace( SUMMARY_LEAD_FILLER, '' )
			.replace( SUMMARY_LEAD_COUNT, '' );
	} while ( name !== previous );
	name = name.replace( /\s+calculator$/i, '' ).trim();
	return titleCase( name || head || operationId );
}

/**
 * Cut a long name back to its subject, but never onto a name another operation
 * in the same domain already answers to.
 *
 * `isTaken` is checked against the FULL names of the peers, so a cut that would
 * read as a shorter spelling of a sibling reading is refused and the next
 * candidate is tried.
 * @param full
 * @param isTaken
 */
function shortenDisplayName( full, isTaken ) {
	if ( full.length <= NAME_LENGTH_BUDGET ) {
		return full;
	}
	const haystack = full.toLowerCase();
	const cuts = [];
	for ( const connector of NAME_TAIL_CONNECTORS ) {
		const at = haystack.indexOf( connector.toLowerCase() );
		if ( at > 0 ) {
			cuts.push(
				full
					.slice( 0, at )
					.trim()
					.replace( /[\s,:;-]+$/, '' )
			);
		}
	}
	cuts.sort( ( a, b ) => a.length - b.length );
	for ( const candidate of cuts ) {
		if ( candidate.split( /\s+/ ).length < 2 && candidate.length < 6 ) {
			continue;
		}
		if ( isTaken( candidate ) ) {
			continue;
		}
		return candidate;
	}
	return full;
}

/**
 * The admin-facing label for an OpenAPI tag, from bin/domains.json.
 * @param tag
 */
function domainLabel( tag ) {
	return domainRegistry[ tag ]?.label || tag;
}

/**
 * Every operation's display name, keyed by operationId. Resolved in one pass so
 * a name can be shortened against the other names in its own domain.
 * @param ops
 */
function buildDisplayNames( ops ) {
	const fullByTag = new Map();
	for ( const op of ops ) {
		const full = deriveDisplayName( op.summary, op.operationId );
		if ( ! fullByTag.has( op.tag ) ) {
			fullByTag.set( op.tag, [] );
		}
		fullByTag
			.get( op.tag )
			.push( { operationId: op.operationId, summary: op.summary, full } );
	}
	// When dropping the lead verb leaves two operations in one domain reading
	// the same, the verb was the only thing separating them, so the one naming
	// an ACTION keeps it and the retrieval keeps the bare noun. That is the
	// split `listCrystals` and `searchCrystals` already ship; deriving it from
	// the collision covers the next pair without a verb added to a list by
	// hand. Two retrievals that still collide are genuinely the same name and
	// the block-title guard rejects them.
	for ( const peers of fullByTag.values() ) {
		const byName = new Map();
		for ( const entry of peers ) {
			if ( ! byName.has( entry.full ) ) {
				byName.set( entry.full, [] );
			}
			byName.get( entry.full ).push( entry );
		}
		for ( const group of byName.values() ) {
			if ( group.length < 2 ) {
				continue;
			}
			for ( const entry of group ) {
				if (
					SUMMARY_RETRIEVAL_VERBS.test(
						String( entry.summary || '' )
					)
				) {
					continue;
				}
				entry.full = deriveDisplayName(
					entry.summary,
					entry.operationId,
					true
				);
			}
		}
	}
	const names = {};
	for ( const op of ops ) {
		const peers = fullByTag.get( op.tag );
		const full = peers.find(
			( entry ) => entry.operationId === op.operationId
		).full;
		const others = peers
			.filter( ( entry ) => entry.operationId !== op.operationId )
			.map( ( entry ) => entry.full );
		names[ op.operationId ] = shortenDisplayName( full, ( candidate ) =>
			others.some(
				( other ) =>
					other === candidate ||
					other.startsWith( candidate + ' ' ) ||
					other.startsWith( candidate + ',' )
			)
		);
	}
	return names;
}

/**
 * The inserter entry for a block: the display name, plus the domain when the
 * name does not already say it.
 *
 * Without the suffix the inserter shows "Monthly Ephemeris" twice and
 * "Compatibility Score" twice with nothing to tell them apart. With it on every
 * entry it repeats the obvious ("Tarot Cards (Tarot)"), so it is added only
 * when it adds information.
 * @param name
 * @param tag
 */
function blockTitleFor( name, tag ) {
	if ( /\)$/.test( name ) ) {
		return name;
	}
	const flatten = ( value ) =>
		String( value )
			.toLowerCase()
			.replace( /[^a-z0-9]/g, '' );
	const target = flatten( name );
	for ( const candidate of [ domainLabel( tag ), tag ] ) {
		const bare = flatten( candidate ).replace( /s$/, '' );
		if ( bare !== '' && target.includes( bare ) ) {
			return name;
		}
	}
	return `${ name } (${ domainLabel( tag ) })`;
}

/**
 * Refuse to ship "API" in anything a site owner or a visitor reads.
 * @param label
 * @param where
 */
function assertNoApiWording( label, where ) {
	if ( /\bAPI\b/i.test( String( label ) ) ) {
		throw new Error(
			`[generate] "${ label }" (${ where }) contains "API". A block title, inserter description or form heading names a reading, never an endpoint.`
		);
	}
	return label;
}

/**
 * Display name per operationId. The single home for what an operation is
 * CALLED, read by the block emitters, the generated form specs and the endpoint
 * registry the admin library renders from.
 */
const displayNames = buildDisplayNames( operations );

/**
 * A one-line description for the inserter, from the summary's own descriptive
 * tail, falling back to the first sentence of the spec description.
 * @param op
 */
function blockDescriptionFor( op ) {
	const name = displayNames[ op.operationId ];
	const parts = String( op.summary || '' ).split( /\s-\s|\.\s/ );
	const tail =
		parts.length > 1 ? stripApiWords( parts.slice( 1 ).join( ' - ' ) ) : '';
	// Many summaries end in a search keyword rather than a description ("Arudha
	// Lagna Calculator API", "Dasha Calculator API"). With the API wording gone
	// what is left restates the title, so the operation's own description does
	// the job better.
	const flatten = ( value ) =>
		String( value )
			.toLowerCase()
			.replace( /[^a-z0-9]/g, '' );
	const usable = ( text ) =>
		text.length >= 24 && ! flatten( name ).includes( flatten( text ) );
	if ( usable( tail ) ) {
		return tail;
	}
	// Several descriptions open with the same keyword line the summary ends in
	// ("Prana dasha API. Returns the 9 Prana periods inside..."), so the first
	// sentence is not always the one that describes anything.
	for ( const sentence of splitSentences( op.description || '' ) ) {
		const cleaned = stripApiWords( sentence );
		if ( usable( cleaned ) ) {
			return cleaned;
		}
	}
	return tail || name;
}

/**
 * Map an OpenAPI property schema + name to a FormRenderer field spec.
 * Heuristics catch lat/lon/timezone by name when the spec lacks min/max.
 * @param name
 * @param schema
 * @param required
 */
function buildFormFieldSpec( name, schema, required ) {
	const lower = name.toLowerCase();
	const field = { name, label: humanLabel( name ), required };
	if ( schema.description ) {
		field.help = firstSentence( String( schema.description ) );
	}
	// The timezone field renders as a wp_timezone_choice() dropdown, so the
	// spec's "decimal hours OR IANA name" help would mislead; use copy that
	// matches the actual control.
	if ( lower === 'tz' || lower === 'timezone' ) {
		field.help = 'Select the timezone of the location.';
	}

	if ( schema.type === 'string' && schema.format === 'date' ) {
		field.type = 'date';
	} else if ( schema.type === 'string' && schema.format === 'time' ) {
		field.type = 'time';
	} else if (
		schema.type === 'string' &&
		( lower === 'time' ||
			lower.endsWith( '_time' ) ||
			lower.endsWith( 'time' ) )
	) {
		// Spec often describes time fields as plain string with help text
		// "HH:MM:SS"; coerce to a time picker so users get the native UX.
		field.type = 'time';
	} else if (
		schema.type === 'string' &&
		( lower === 'date' ||
			lower.endsWith( '_date' ) ||
			lower.endsWith( 'date' ) )
	) {
		// Same for plain-string date fields like `birthDate` / `transitDate`.
		field.type = 'date';
	} else if ( schema.type === 'string' && Array.isArray( schema.enum ) ) {
		field.type = 'enum';
		field.enum = schema.enum;
	} else if ( lower === 'tz' || lower === 'timezone' ) {
		field.type = 'timezone';
	} else if ( schema.type === 'number' || schema.type === 'integer' ) {
		field.type = schema.type === 'integer' ? 'integer' : 'number';
		if ( lower === 'lat' || lower === 'latitude' ) {
			field.min = -90;
			field.max = 90;
		} else if ( lower === 'lon' || lower === 'longitude' ) {
			field.min = -180;
			field.max = 180;
		} else {
			if ( schema.minimum !== undefined ) {
				field.min = schema.minimum;
			}
			if ( schema.maximum !== undefined ) {
				field.max = schema.maximum;
			}
		}
		if ( schema.type === 'number' ) {
			field.step = 'any';
		}
	} else {
		field.type = 'text';
	}
	return field;
}

/**
 * Walk a POST request-body schema and produce a FormRenderer-shaped spec
 * with `sections` for nested objects and `flat_fields` for top-level scalars.
 * @param op
 */
function extractFormSpec( op ) {
	const content = op.requestBody?.content?.[ 'application/json' ];
	if ( ! content?.schema ) {
		return null;
	}
	const schema = resolveRef( content.schema );
	const required = new Set( schema.required || [] );
	const sections = [];
	const flatFields = [];
	for ( const [ name, prop ] of Object.entries( schema.properties || {} ) ) {
		const resolved = resolveRef( prop );
		if ( resolved.type === 'object' ) {
			const subRequired = new Set( resolved.required || [] );
			const fields = [];
			for ( const [ subName, subProp ] of Object.entries(
				resolved.properties || {}
			) ) {
				const subResolved = resolveRef( subProp );
				if (
					subResolved.type === 'object' ||
					subResolved.type === 'array'
				) {
					// v1.0: do not emit deeper-than-one nesting controls.
					continue;
				}
				fields.push(
					buildFormFieldSpec(
						subName,
						subResolved,
						subRequired.has( subName )
					)
				);
			}
			sections.push( { name, label: humanLabel( name ), fields } );
		} else if ( resolved.type === 'array' ) {
			// Array-of-object body fields are deferred to v1.1.
			continue;
		} else {
			flatFields.push(
				buildFormFieldSpec( name, resolved, required.has( name ) )
			);
		}
	}
	return { sections, flatFields };
}

/**
 * Form-spec keys whose value is prose a VISITOR reads, so it has to reach the
 * translators. Everything else in a field spec is machinery: `name` is the
 * request key, `type` picks the control, and `enum` values are the API's own
 * strings submitted verbatim (FormRenderer derives their display text with
 * ucwords(), so translating them here would post a translated value).
 *
 * ONE list, consumed by BOTH form emitters. The long-tail forms and the hero
 * forms build their spec arrays in separate functions, so this must stay a
 * single shared list or a change applies to only half the forms.
 */
const TRANSLATABLE_SPEC_KEYS = new Set( [ 'label', 'help', 'placeholder' ] );

/**
 * A PHP string literal wrapped in `__()` so `wp i18n make-pot` and
 * translate.wordpress.org can both extract it.
 *
 * Extraction is purely STATIC: the tooling reads the literal at the call site
 * and never evaluates anything, so the string must be inlined here rather than
 * passed through a variable. An empty or non-string value falls back to a plain
 * literal, because an empty msgid collides with the PO header.
 * @param v
 */
function translatablePhp( v ) {
	return typeof v === 'string' && v !== ''
		? `__( ${ phpLiteral( v ) }, 'roxyapi' )`
		: phpLiteral( v );
}

/**
 * PHP for one `'key' => value` entry of a form-spec array, translated when the
 * key is visitor-facing.
 * @param key
 * @param value
 */
function specEntryPhp( key, value ) {
	return TRANSLATABLE_SPEC_KEYS.has( key )
		? translatablePhp( value )
		: phpLiteral( value );
}

/**
 * PHP literal for a JS value — strings/numbers/bools/lists. Strict ASCII safe.
 * @param v
 */
function phpLiteral( v ) {
	if ( v === null || v === undefined ) {
		return "''";
	}
	if ( typeof v === 'boolean' ) {
		return v ? 'true' : 'false';
	}
	if ( typeof v === 'number' ) {
		return String( v );
	}
	if ( Array.isArray( v ) ) {
		return 'array( ' + v.map( phpLiteral ).join( ', ' ) + ' )';
	}
	return (
		"'" + String( v ).replace( /\\/g, '\\\\' ).replace( /'/g, "\\'" ) + "'"
	);
}

/**
 * Emit src/Generated/Forms/<Op>Form.php for a nested-object endpoint.
 * @param op
 */
function emitFormPhp( op ) {
	const className = toPascalCase( op.operationId ) + 'Form';
	const formSpec = extractFormSpec( op );
	// The heading a VISITOR reads above the form, so it is the reading's name.
	// The raw summary put "Transit Aspects - Detailed transit-to-natal aspect
	// analysis with interpretations" across three lines at phone width.
	const title = assertNoApiWording(
		displayNames[ op.operationId ] || op.operationId,
		`${ op.operationId } form heading`
	);

	const sectionsPhp = ( formSpec.sections || [] )
		.map( ( s ) => {
			const fieldsPhp = s.fields
				.map( ( f ) => {
					const parts = Object.entries( f )
						.map(
							( [ k, v ] ) =>
								`\t\t\t\t\t\t'${ k }' => ${ specEntryPhp(
									k,
									v
								) },`
						)
						.join( '\n' );
					return `\t\t\t\t\tarray(\n${ parts }\n\t\t\t\t\t),`;
				} )
				.join( '\n' );
			return `\t\t\t\tarray(
					'name'   => ${ phpLiteral( s.name ) },
					'label'  => ${ translatablePhp( s.label ) },
					'fields' => array(
${ fieldsPhp }
					),
				),`;
		} )
		.join( '\n' );

	const flatPhp = ( formSpec.flatFields || [] )
		.map( ( f ) => {
			const parts = Object.entries( f )
				.map(
					( [ k, v ] ) =>
						`\t\t\t\t\t'${ k }' => ${ specEntryPhp( k, v ) },`
				)
				.join( '\n' );
			return `\t\t\t\tarray(\n${ parts }\n\t\t\t\t),`;
		} )
		.join( '\n' );

	return `<?php
/**
 * Auto-generated visitor form for ${ op.operationId }.
 *
 * ${ op.summary || op.operationId }
 *
 * DO NOT EDIT. Generated by bin/generate.mjs. Edit the generator instead.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated\\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ${ className } {

	public static function spec(): array {
		return array(
			'operation_id' => ${ phpLiteral( op.operationId ) },
			'title'        => ${ translatablePhp( title ) },
			'submit_label' => __( 'Get reading', 'roxyapi' ),
			'sections'     => array(
${ sectionsPhp }
			),
			'flat_fields'  => array(
${ flatPhp }
			),
		);
	}

	/**
	 * @param array<string, mixed> $body
	 * @return array<string, mixed>|\\WP_Error
	 */
	public static function call( array $body ) {
		return \\RoxyAPI\\Generated\\Client::${ op.operationId }( $body );
	}
}
`;
}

// ---------------------------------------------------------------------------
// Emit src/Generated/Client.php
// ---------------------------------------------------------------------------

function emitClientPhp() {
	const methods = [];

	for ( const op of operations ) {
		const _className = toPascalCase( op.operationId );
		const phpMethod = op.operationId;
		const ttl = getTtl( op.operationId );
		const pathParams = extractPathParams( op.path );
		const phpPath = buildPhpPath( op.path );

		// Build a guard block that fails fast (with a friendly WP_Error) if any
		// required path param arrived empty. Without this, an unset shortcode
		// attr produces requests like `/horoscope//monthly` that the SaaS 404s
		// or that emit `rawurlencode(null)` deprecation warnings.
		const pathGuard =
			pathParams.length > 0
				? pathParams
						.map( ( p ) => {
							const v = toPhpVar( p );
							return `\t\tif ( $${ v } === '' || $${ v } === null ) {\n\t\t\treturn new \\WP_Error( 'roxyapi_missing_param', sprintf( /* translators: %s: shortcode attribute name. */ __( 'Missing required attribute "%s" for this shortcode.', 'roxyapi' ), '${ p }' ) );\n\t\t}`;
						} )
						.join( '\n' )
				: '';

		if ( op.method === 'GET' ) {
			const params = extractParams( op );
			const allParams = [
				...pathParams.map( ( p ) => `$${ toPhpVar( p ) }` ),
				...params
					.filter( ( p ) => p.in === 'query' )
					.map( ( p ) => `$${ toPhpVar( p.name ) } = null` ),
			];
			const queryArray = params
				.filter( ( p ) => p.in === 'query' )
				.map( ( p ) => {
					const v = toPhpVar( p.name );
					return `\t\t\t'${ p.name }' => $${ v },`;
				} );

			// Build use() clause: always $query, plus any path params
			const useVars = [
				'$query',
				...pathParams.map( ( p ) => `$${ toPhpVar( p ) }` ),
			];
			const useClause = useVars.join( ', ' );

			methods.push( `
	/**
	 * ${ op.summary || op.operationId }
	 *
	 * @return array|\\WP_Error
	 */
	public static function ${ phpMethod }( ${ allParams.join( ', ' ) } ) {
${ pathGuard ? pathGuard + '\n' : '' }		$query = array_filter(
			array(
${ queryArray.join( '\n' ) }
			),
			static function ( $v ) {
				return $v !== null && $v !== '';
			}
		);
		return \\RoxyAPI\\Api\\Cache::remember(
			${ phpPath },
			$query,
			${ ttl },
			static function () use ( ${ useClause } ) {
				return \\RoxyAPI\\Api\\Client::get( ${ phpPath }, $query );
			}
		);
	}` );
		} else {
			const bodyFields = extractBodyFields( op );
			// Same idea as pathGuard, for the request body. A block starts life
			// with every attribute empty, and the shortcodes drop empty
			// attributes while building the body, so an unconfigured reading
			// used to POST `{}` and be rejected. A block editor re-renders on
			// load and after every keystroke, so that became a stream of
			// requests that could never succeed.
			//
			// Driven by the spec's own `required` list, which is what keeps it
			// honest: readings that genuinely take no input (today's card,
			// today's hexagram) declare none and are left alone, and a reading
			// that later gains a required field starts being guarded on the
			// next generate with no edit here.
			const requiredBody = bodyFields
				.filter( ( f ) => f.required )
				.map( ( f ) => `'${ f.name }'` );
			const bodyGuard = requiredBody.length
				? `\t\tif ( ! \\RoxyAPI\\Api\\Client::body_has_all( $body, array( ${ requiredBody.join(
						', '
				  ) } ) ) ) {\n\t\t\treturn \\RoxyAPI\\Api\\Client::not_configured();\n\t\t}`
				: '';
			// A POST operation can still declare QUERY parameters, and many do:
			// the response language on most, plus focus / include / orb /
			// strictOrbs. They belong on the URL, so they travel as their own
			// argument instead of in the body, where the API ignores them. They
			// are folded into the cache args too, because two values of `focus`
			// are two different readings and must not share a cached response.
			const queryParams = extractParams( op ).filter(
				( p ) => p.in === 'query'
			);
			const hasQuery = queryParams.length > 0;
			const allParams = [
				...pathParams.map( ( p ) => `$${ toPhpVar( p ) }` ),
				'$body = array()',
				...( hasQuery ? [ '$query = array()' ] : [] ),
			];

			// Build use() clause: always $body, plus the query and any path params
			const useVars = [
				'$body',
				...( hasQuery ? [ '$query' ] : [] ),
				...pathParams.map( ( p ) => `$${ toPhpVar( p ) }` ),
			];
			const useClause = useVars.join( ', ' );

			methods.push( `
	/**
	 * ${ op.summary || op.operationId }
	 *
	 * @param array $body Request body.${
			hasQuery ? '\n\t * @param array $query Query parameters.' : ''
		}
	 * @return array|\\WP_Error
	 */
	public static function ${ phpMethod }( ${ allParams.join( ', ' ) } ) {
${ pathGuard ? pathGuard + '\n' : '' }${
				bodyGuard ? bodyGuard + '\n' : ''
			}		return \\RoxyAPI\\Api\\Cache::remember(
			${ phpPath },
			${ hasQuery ? 'array_merge( $body, $query )' : '$body' },
			${ ttl },
			static function () use ( ${ useClause } ) {
				return \\RoxyAPI\\Api\\Client::post( ${ phpPath }, $body${
					hasQuery ? ', $query' : ''
				} );
			}
		);
	}` );
		}
	}

	return `<?php
/**
 * Auto-generated typed PHP client for RoxyAPI.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs from the live OpenAPI spec.
 * Edit bin/generate.mjs instead, then run: npm run generate
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Client {
${ methods.join( '\n' ) }
}
`;
}

// ---------------------------------------------------------------------------
// Emit src/Generated/Endpoints.php
// ---------------------------------------------------------------------------

function emitEndpointsPhp() {
	const entries = operations.map( ( op ) => {
		const ttl = getTtl( op.operationId );
		const isHero = heroSet.has( op.operationId );

		// Pluck OpenAPI examples for every parameter/body field so the library
		// and Demo pages can offer a copy-paste sample with sensible inputs.
		// Keys are snake_case to match the shortcode attribute style emitted
		// by emitShortcodePhp (WP lowercases attr names at parse time).
		// `applyExampleOverride` lets us patch fields where the spec example
		// disagrees with the live validator (e.g. timezone "-5" → "UTC").
		//
		// Nothing goes in here that the shortcode would not read back out. An
		// operation whose body needs a nested object renders the visitor form
		// and declares no attributes at all, so a sample carrying any would be
		// advertising inputs WordPress silently drops.
		const attrExamples = {};
		if ( ! hasRequiredObjectBody( op ) ) {
			const params = extractParams( op );
			for ( const p of params ) {
				if ( isCurrentDefaulting( p ) ) {
					continue;
				}
				const ex = applyExampleOverride(
					op.operationId,
					p.name,
					p.example
				);
				if ( ex !== undefined && ex !== null ) {
					attrExamples[ toSnakeAttr( p.name ) ] = ex;
				}
			}
			if ( op.method === 'POST' ) {
				for ( const f of extractBodyFields( op ) ) {
					if ( isCurrentDefaulting( f ) ) {
						continue;
					}
					const ex = applyExampleOverride(
						op.operationId,
						f.name,
						f.example
					);
					if ( ex !== undefined && ex !== null ) {
						attrExamples[ toSnakeAttr( f.name ) ] = ex;
					}
				}
			}
		}
		const attrEntries = Object.entries( attrExamples )
			.map( ( [ k, v ] ) => {
				// Stringify for shortcode-attribute use. Coerce booleans to "1"/"".
				let phpStr;
				if ( typeof v === 'boolean' ) {
					phpStr = v ? '1' : '';
				} else if ( typeof v === 'number' ) {
					phpStr = String( v );
				} else if ( typeof v === 'object' ) {
					// Skip nested object/array examples — shortcode attrs are flat
					// and these belong to a different DX (HTML form / block).
					return null;
				} else {
					phpStr = String( v );
				}
				return `\t\t\t\t'${ k }' => '${ phpStr.replace(
					/'/g,
					"\\'"
				) }',`;
			} )
			.filter( Boolean );
		const attributesPhp =
			attrEntries.length > 0
				? `\n\t\t\t'attributes' => array(\n${ attrEntries.join(
						'\n'
				  ) }\n\t\t\t),`
				: `\n\t\t\t'attributes' => array(),`;

		const blockOnly = hasRequiredObjectBody( op );
		const shortcodeTag = `roxy_${ toSnakeCase( op.operationId ) }`;

		const displayName = assertNoApiWording(
			displayNames[ op.operationId ],
			`${ op.operationId } display name`
		);

		return `		'${ op.operationId }' => array(
			'path'          => '${ op.path }',
			'method'        => '${ op.method }',
			'tag'           => '${ op.tag.replace( /'/g, "\\'" ) }',
			'summary'       => '${ ( op.summary || '' ).replace( /'/g, "\\'" ) }',
			'display_name'  => '${ displayName.replace( /'/g, "\\'" ) }',
			'ttl'           => ${ ttl },
			'hero'          => ${ isHero ? 'true' : 'false' },
			'block_only'    => ${ blockOnly ? 'true' : 'false' },
			'shortcode_tag' => '${ shortcodeTag }',${ attributesPhp }
		),`;
	} );

	return `<?php
/**
 * Auto-generated endpoint registry.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs from the live OpenAPI spec.
 * Edit bin/generate.mjs instead, then run: npm run generate
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Endpoints {

	/**
	 * All registered endpoints keyed by operationId.
	 *
	 * @return array<string, array{path: string, method: string, tag: string, summary: string, display_name: string, ttl: int, hero: bool, block_only: bool, shortcode_tag: string, attributes: array<string, string>}>
	 */
	public static function all(): array {
		return array(
${ entries.join( '\n' ) }
		);
	}

	/**
	 * Get a single endpoint by operationId.
	 *
	 * @return array{path: string, method: string, tag: string, summary: string, display_name: string, ttl: int, hero: bool, block_only: bool, shortcode_tag: string, attributes: array<string, string>}|null
	 */
	public static function get( string $operation_id ): ?array {
		$all = self::all();
		return $all[ $operation_id ] ?? null;
	}

	/**
	 * Only the non-hero (generated) endpoints.
	 *
	 * @return array<string, array{path: string, method: string, tag: string, summary: string, display_name: string, ttl: int, hero: bool, block_only: bool, shortcode_tag: string, attributes: array<string, string>}>
	 */
	public static function generated(): array {
		return array_filter(
			self::all(),
			static function ( $ep ) {
				return ! $ep['hero'];
			}
		);
	}
}
`;
}

// ---------------------------------------------------------------------------
// Emit src/Generated/ComponentMap.php from bin/component-map.json
// ---------------------------------------------------------------------------

/**
 * Emit the operationId -> @roxyapi/ui component rows map. Read at runtime by
 * RoxyAPI\Support\ComponentRenderer to decide whether an operation renders as a
 * web component or falls back to the generic card renderer. Output is sorted by
 * operationId so two runs produce identical bytes.
 */
/**
 * Operations that return the SITE OWNER's account, not a reading.
 *
 * `getUsageStats` answers with the account email, plan name and quota. It takes
 * no inputs, so its block rendered the moment it was inserted and published all
 * of that to the front end: any user who could edit a post could put the
 * owner's email and plan on a public page. It is an account-management endpoint
 * that became a content block.
 *
 * Listed operations get a capability check in the shortcode and are kept out of
 * the block inserter. Add to this list rather than hand-editing generated
 * output, which is overwritten on every run.
 */
const ACCOUNT_SCOPED_OPERATIONS = new Set( [ 'getUsageStats' ] );

/**
 * Operations kept out of the block inserter because the answer is not a reading.
 *
 * The inserter offered one entry per endpoint, and at that size someone looking
 * for a birth chart scrolls past dozens of near-identical rows to find it. The
 * axis is what the response IS, not whether a component is bound to it: an
 * operation stays if a visitor would read the output, including the ones that
 * fall back to the generic card.
 *
 * Two kinds fail that test. A reference lookup answers with a catalogue that
 * exists to fill a dropdown or let a developer discover valid ids (every
 * country, every supported language, the 27 nakshatras), and the random pickers
 * answer differently on every request, so the published page never shows what
 * the editor previewed. An interval dump answers with a month of rows meant for
 * a chart or a spreadsheet; dropped on a page it is a wall of numbers with no
 * reading in it.
 *
 * Hidden, never deregistered, and the shortcode is untouched. A saved post
 * references a block by name, so removing the type would break content that
 * already uses it, and every operation keeps its shortcode for anyone who does
 * want the raw table on a page. Add here rather than hand-editing generated
 * output, which is overwritten on every run.
 */
const NON_READING_OPERATIONS = new Set( [
	// Reference lookups and enumerations: a catalogue for a dropdown or an id
	// list, not something a visitor reads.
	'getCitiesByCountry',
	// Labels for request field names, in one language. A developer-facing map of ~180
	// strings that a site owner would never place on a page, and it arrives with the
	// spec rather than being added deliberately, so it is listed the day it appears.
	'getFieldLabels',
	'getSymbolLetterCounts',
	'listAvasthas',
	'listBaguaSectors',
	'listCountries',
	'listCrystalColors',
	'listCrystalPlanets',
	'listFiveElements',
	'listFlyingStars',
	'listHexagrams',
	'listLanguages',
	'listNakshatras',
	'listNinePeriods',
	'listRashis',
	'listTrigrams',
	'listZodiacAnimals',
	'listZodiacSigns',
	'searchCities',
	// Random pickers: non-deterministic, so a published page never matches the
	// preview the editor showed.
	'getRandomCrystal',
	'getRandomSymbols',
	// Interval and month-range dumps: rows for a chart, not a reading.
	'getEclipticCrossings',
	'getKpPlanetsInterval',
	'getKpRasiChanges',
	'getKpRulingInterval',
	'getKpSublordChanges',
	'getLunarAspects',
	'getMonthlyAlmanac',
	'getMonthlyAspects',
	'getMonthlyEphemeris',
	'getMonthlyParallels',
	'getMonthlyTransits',
	'getMonthlyTropicalEphemeris',
] );

/**
 * Whether a generated block is offered in the inserter. Two lists, one flag:
 * account-scoped operations are hidden because publishing them leaks the site
 * owner's account, non-reading operations because they are noise in a list of
 * 150. Neither is deregistered.
 * @param operationId
 */
function isHiddenFromInserter( operationId ) {
	return (
		ACCOUNT_SCOPED_OPERATIONS.has( operationId ) ||
		NON_READING_OPERATIONS.has( operationId )
	);
}

function emitComponentMapPhp() {
	const ops = componentMap.operations || {};
	const tagPattern = /^roxy-[a-z-]+$/;
	const sq = ( s ) =>
		`'${ String( s ).replace( /\\/g, '\\\\' ).replace( /'/g, "\\'" ) }'`;
	const entries = Object.keys( ops )
		.sort()
		.map( ( opId ) => {
			const rows = Array.isArray( ops[ opId ] ) ? ops[ opId ] : [];
			const rowPhp = rows
				.map( ( row ) => {
					const component = String( row.component || '' );
					if ( ! tagPattern.test( component ) ) {
						throw new Error(
							`[generate] component-map: invalid component tag "${ component }" for ${ opId }`
						);
					}
					// Variant selectors the upstream catalogue declares for this
					// binding (`type="soul-urge"`, `spread="career"`). A component
					// that serves many operations renders its DEFAULT view without
					// them, so dropping these binds the right element to the wrong
					// view, which looks like a working page.
					const attrs = row.attrs || {};
					const attrKeys = Object.keys( attrs ).sort();
					for ( const key of attrKeys ) {
						if ( ! /^[a-z][a-z0-9-]*$/.test( key ) ) {
							throw new Error(
								`[generate] component-map: invalid attribute name "${ key }" for ${ opId }`
							);
						}
					}
					const attrPhp = attrKeys.length
						? `\t\t\t\t\t'attrs'     => array(\n` +
						  attrKeys
								.map(
									( key ) =>
										`\t\t\t\t\t\t${ sq( key ) } => ${ sq(
											attrs[ key ]
										) },`
								)
								.join( '\n' ) +
						  `\n\t\t\t\t\t),\n`
						: '';
					return (
						`\t\t\t\tarray(\n` +
						`\t\t\t\t\t'component' => ${ sq( component ) },\n` +
						attrPhp +
						`\t\t\t\t),`
					);
				} )
				.join( '\n' );
			return `\t\t\t${ sq( opId ) } => array(\n${ rowPhp }\n\t\t\t),`;
		} )
		.join( '\n' );

	// The vocabulary the `hide_sections` setting and attribute accept, carried
	// through from the pinned bundle by bin/fetch-ui-bundle.mjs. Emitted so the
	// admin field can name the legal values instead of leaving a site owner to
	// discover that an unrecognised one is dropped in silence.
	const partNames = ( componentMap._meta?.published_parts || [] ).filter(
		( name ) => /^[a-z][a-z0-9-]*$/.test( name )
	);
	if ( partNames.length === 0 ) {
		throw new Error(
			'[generate] component-map: _meta.published_parts is empty. Run `npm run fetch:ui` to read it from the pinned bundle.'
		);
	}
	const partsPhp = partNames
		.map( ( name ) => `\t\t\t${ sq( name ) },` )
		.join( '\n' );

	return `<?php
/**
 * Auto-generated operationId to web-component map.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs from bin/component-map.json.
 * Edit the JSON map instead, then run: npm run generate
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ComponentMap {

	/**
	 * Component rows for a given operationId, or an empty array when the
	 * operation has no mapped component.
	 *
	 * @return array<int, array{component: string, attrs?: array<string, string>}>
	 */
	public static function for( string $operation_id ): array {
		$map = array(
${ entries }
		);

		return $map[ $operation_id ] ?? array();
	}

	/**
	 * Every \`part\` name the pinned component bundle publishes, sorted.
	 *
	 * The list a \`hide_sections\` value is checked against. A name outside it
	 * reaches no block, so it is worth telling the site owner about rather than
	 * dropping quietly: the setting cannot report its own failure, because a
	 * name that matches nothing and a name that matches a block already hidden
	 * produce the same empty page.
	 *
	 * @return array<int, string>
	 */
	public static function parts(): array {
		return array(
${ partsPhp }
		);
	}
}
`;
}

// ---------------------------------------------------------------------------
// Emit src/Generated/Shortcodes/{ClassName}.php for each non-hero endpoint
// ---------------------------------------------------------------------------

function emitShortcodePhp( op ) {
	const className = toPascalCase( op.operationId );
	const shortcodeTag = 'roxy_' + toSnakeCase( op.operationId );
	const _ttl = getTtl( op.operationId );
	const isPost = op.method === 'POST';
	const pathParams = extractPathParams( op.path );
	const _phpPath = buildPhpPath( op.path );

	// Endpoints whose request body has a required nested object cannot be
	// expressed as flat shortcode attributes. Emit a sentinel that explains
	// the limitation rather than a broken shortcode that always 400s.
	if ( hasRequiredObjectBody( op ) ) {
		const formClass = toPascalCase( op.operationId ) + 'Form';
		return `<?php
/**
 * Auto-generated shortcode: [${ shortcodeTag }]
 *
 * ${ op.summary || op.operationId }
 *
 * Renders a visitor-facing form (FormRenderer) when invoked. Submission is
 * handled by FormRouter at init priority 5 — nonce + rate-limit + sanitize
 * against the generated form spec, then call the typed PHP client and
 * render the result above the form via PRG redirect.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs. Edit the generator instead.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated\\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\\Support\\FormRenderer;

class ${ className } {

	public const TAG = '${ shortcodeTag }';

	/**
	 * Every INPUT is collected by the visitor form, so the only attributes
	 * declared here are the reserved display controls. They have to be
	 * declared: \`shortcode_atts()\` keeps only the keys it is given, so an
	 * undeclared one is dropped in silence rather than refused.
	 *
	 * @var array<string, string>
	 */
	public const DEFAULTS = array(
${ withReservedAtts( [], '\t\t' ).join( '\n' ) }
	);

	public static function register(): void {
		if ( shortcode_exists( self::TAG ) ) {
			return;
		}
		add_shortcode( self::TAG, array( self::class, 'render' ) );
	}

	public static function render( $atts, $content = '', $tag = '' ): string {
		$atts = shortcode_atts( self::DEFAULTS, is_array( $atts ) ? $atts : array(), (string) $tag );
		return FormRenderer::render( \\RoxyAPI\\Generated\\Forms\\${ formClass }::class, $atts );
	}
}
`;
	}

	let attsArray = '';
	// Account-scoped operations refuse to render for anyone who cannot manage the
	// site. Returning empty rather than an error keeps a page that a subscriber
	// visits from advertising that something was hidden there.
	const accountGuard = ACCOUNT_SCOPED_OPERATIONS.has( op.operationId )
		? `
		if ( ! current_user_can( 'manage_options' ) ) {
			return '';
		}
`
		: '';

	let clientCall = '';

	if ( isPost ) {
		const bodyFields = extractBodyFields( op );
		// A POST operation still declares its language as a QUERY parameter, and
		// the GET branch below has always accepted those. Dropping them here is
		// what made `[roxy_x lang="es"]` a silent no-op on most of the
		// catalogue while the library handed out samples that pasted it:
		// shortcode_atts discards any attribute it was not given a default for,
		// and reports nothing. `lang` is the widest case; `focus`, `include`,
		// `orb` and `strictOrbs` were unreachable from WordPress for the same
		// reason.
		const postQueryParams = extractParams( op ).filter(
			( p ) => p.in === 'query'
		);
		// Snake_case-ify every attribute key. WordPress's shortcode parser
		// lowercases attribute names, so a default of `birthDate` would never
		// match the user's `birthDate=...` (which arrives as `birthdate`).
		const allPostAtts = [
			...pathParams.map( toSnakeAttr ),
			...bodyFields.map( ( f ) => toSnakeAttr( f.name ) ),
			...postQueryParams.map( ( p ) => toSnakeAttr( p.name ) ),
		];
		attsArray = withReservedAtts(
			[ ...new Set( allPostAtts ) ].map( ( p ) => `\t\t'${ p }' => '',` ),
			'\t\t'
		).join( '\n' );

		// Body assembly maps API field name (camelCase) to snake_case attr key
		// and casts numeric fields so the JSON body sends 40.71 not "40.71".
		const bodyBuild =
			bodyFields.length > 0
				? `\t\t$body = array_filter(\n\t\t\tarray(\n${ bodyFields
						.map( ( f ) => {
							const attr = toSnakeAttr( f.name );
							if ( f.type === 'integer' ) {
								return `\t\t\t\t'${ f.name }' => $atts['${ attr }'] !== '' ? (int) $atts['${ attr }'] : '',`;
							}
							if ( f.type === 'number' ) {
								return `\t\t\t\t'${ f.name }' => $atts['${ attr }'] !== '' ? (float) $atts['${ attr }'] : '',`;
							}
							if ( f.type === 'boolean' ) {
								return `\t\t\t\t'${ f.name }' => $atts['${ attr }'] !== '' ? filter_var( $atts['${ attr }'], FILTER_VALIDATE_BOOLEAN ) : '',`;
							}
							if ( f.numericString ) {
								return `\t\t\t\t'${ f.name }' => $atts['${ attr }'] !== '' ? ( is_numeric( $atts['${ attr }'] ) ? (float) $atts['${ attr }'] : $atts['${ attr }'] ) : '',`;
							}
							return `\t\t\t\t'${ f.name }' => $atts['${ attr }'],`;
						} )
						.join(
							'\n'
						) }\n\t\t\t),\n\t\t\tstatic function ( $v ) {\n\t\t\t\treturn $v !== '';\n\t\t\t}\n\t\t);`
				: '\t\t$body = array();';

		// Query parameters stay OUT of the body. The API reads them from the
		// URL only, so the generated client keeps the two apart all the way
		// down to Api\Client rather than relying on a rescue at the bottom.
		const queryBuild = postQueryParams.length
			? `\n\t\t$query = array_filter(\n\t\t\tarray(\n${ postQueryParams
					.map(
						( p ) =>
							`\t\t\t\t'${ p.name }' => $atts['${ toSnakeAttr(
								p.name
							) }'],`
					)
					.join(
						'\n'
					) }\n\t\t\t),\n\t\t\tstatic function ( $v ) {\n\t\t\t\treturn $v !== '';\n\t\t\t}\n\t\t);`
			: '';

		clientCall = `${ bodyBuild }${ queryBuild }
		$data = \\RoxyAPI\\Generated\\Client::${ op.operationId }( ${ pathParams
			.map( ( p ) => `$atts['${ toSnakeAttr( p ) }']` )
			.concat( [ '$body' ] )
			.concat( postQueryParams.length ? [ '$query' ] : [] )
			.join( ', ' ) } );`;
	} else {
		const queryParams = extractParams( op ).filter(
			( p ) => p.in === 'query'
		);
		const allAttParams = [
			...pathParams.map( toSnakeAttr ),
			...queryParams.map( ( p ) => toSnakeAttr( p.name ) ),
		];
		attsArray = withReservedAtts(
			allAttParams.map( ( p ) => `\t\t'${ p }' => '',` ),
			'\t\t'
		).join( '\n' );

		const queryArgsList = queryParams.map(
			( p ) => `$atts['${ toSnakeAttr( p.name ) }']`
		);
		const allArgs = [
			...pathParams.map( ( p ) => `$atts['${ toSnakeAttr( p ) }']` ),
			...queryArgsList,
		];

		clientCall = `$data = \\RoxyAPI\\Generated\\Client::${
			op.operationId
		}( ${ allArgs.join( ', ' ) } );`;
	}

	return `<?php
/**
 * Auto-generated shortcode: [${ shortcodeTag }]
 *
 * ${ op.summary || op.operationId }
 *
 * DO NOT EDIT. Generated by bin/generate.mjs. Edit the generator instead.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated\\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\\Support\\ComponentRenderer;

class ${ className } {

	public const TAG = '${ shortcodeTag }';

	/**
	 * Every attribute this shortcode accepts. shortcode_atts() silently
	 * discards anything absent from here, so this is also the contract the
	 * library copy-paste sample is checked against.
	 *
	 * @var array<string, string>
	 */
	public const DEFAULTS = array(
${ attsArray }
	);

	public static function register(): void {
		if ( shortcode_exists( self::TAG ) ) {
			return;
		}
		add_shortcode( self::TAG, array( self::class, 'render' ) );
	}

	public static function render( $atts, $content = '', $tag = '' ): string {
		$atts = shortcode_atts(
			self::DEFAULTS,
			is_array( $atts ) ? $atts : array(),
			(string) $tag
		);

		wp_enqueue_style( 'roxyapi-frontend' );
${ accountGuard }
		${ clientCall }

		if ( is_wp_error( $data ) ) {
			return \\RoxyAPI\\Support\\Templates::api_error( $data );
		}

		return ComponentRenderer::render_atts( '${
			op.operationId
		}', is_array( $data ) ? $data : array(), $atts );
	}
}
`;
}

// ---------------------------------------------------------------------------
// Emit blocks/generated/{slug}/{block.json,render.php,index.js}
//
// Every block in the plugin except the two hand-written ones (horoscope, which
// dispatches by period and reads block context, and the astrology-wrapper that
// provides that context) is emitted from here. The five headline readings used
// to be hand-written placeholders with `"attributes": {}` and a static
// <Placeholder> for an editor, so the flagship blocks were the only ones a site
// owner could not configure. They are described by `block` in
// bin/hero-config.json now and run through the SAME three emitters as the long
// tail, which is what stops the two shapes drifting apart again.
// ---------------------------------------------------------------------------

/**
 * A block descriptor: everything the three emitters need, whether the block
 * came from a spec operation or from a curated hero.
 *
 * @typedef {{slug: string, name: string, title: string, description: string,
 *   icon: string, keywords: string[], attributes: Object, fields: Array,
 *   phpClass: string, hidden: boolean, instructions: (string|null),
 *   sourceLabel: string}} BlockDescriptor
 */

/**
 * Describe the block for one spec operation.
 * @param op
 */
function blockFromOperation( op ) {
	const slug = toKebabCase( op.operationId );
	const family = domainLabel( op.tag );

	const attributes = {};
	for ( const input of blockInputs( op ) ) {
		attributes[ input.name ] = { type: 'string', default: '' };
	}

	return {
		slug,
		name: `roxyapi/${ slug }`,
		title: assertNoApiWording(
			blockTitleFor( displayNames[ op.operationId ], op.tag ),
			`${ op.operationId } block title`
		),
		description: assertNoApiWording(
			blockDescriptionFor( op ),
			`${ op.operationId } block description`
		),
		icon: tagToIcon( op.tag ),
		keywords: [
			...new Set( [
				family.toLowerCase(),
				'roxyapi',
				...op.operationId
					.replace( /([A-Z])/g, ' $1' )
					.toLowerCase()
					.trim()
					.split( /\s+/ )
					.slice( 0, 2 ),
			] ),
		],
		attributes,
		fields: deriveBlockFields( op ),
		phpClass: `\\RoxyAPI\\Generated\\Shortcodes\\${ toPascalCase(
			op.operationId
		) }`,
		hidden: isHiddenFromInserter( op.operationId ),
		instructions: null,
		sourceLabel: op.operationId,
	};
}

/**
 * Map a hero form field type to the editor control that collects it. The
 * timezone picker a VISITOR gets is a `wp_timezone_choice()` dropdown built in
 * PHP; the editor sidebar has no equivalent, so the site owner types the IANA
 * name, exactly as a long-tail timezone input already does.
 */
const HERO_FIELD_CONTROLS = {
	date: 'date',
	time: 'time',
	timezone: 'text',
	number: 'number',
	integer: 'number',
	enum: 'select',
	text: 'text',
};

/**
 * Editor fields for a hero block, from the hero's own form_mode field list.
 *
 * That list is already the curated, translated description of what the reading
 * asks for, so the block sidebar and the visitor form cannot disagree about a
 * label, a control or an enum.
 * @param tagSuffix
 * @param cfg
 */
function heroBlockFields( tagSuffix, cfg ) {
	const declared = Object.keys( cfg.attributes || {} );
	const fields = ( cfg.form_mode?.fields || [] ).map( ( f ) => {
		const name = f.attr || f.name;
		if ( ! declared.includes( name ) ) {
			throw new Error(
				`[generate] hero ${ tagSuffix } form field "${ name }" is not one of its attributes, so the block control would write an attribute the shortcode never reads`
			);
		}
		const field = {
			name,
			control: HERO_FIELD_CONTROLS[ f.type ] || 'text',
			label: f.label || humanLabel( name ),
			// Required as the SHORTCODE sees it, not as the visitor form does.
			// The two differ on purpose: a visitor form asks for a timezone,
			// while the shortcode defaults it, and a block that refused to
			// preview until it was typed would be stricter than the reading.
			required: !! cfg.attributes[ name ].required,
		};
		if ( f.help ) {
			field.help = f.help;
		}
		if ( f.placeholder ) {
			field.placeholder = f.placeholder;
		}
		if ( field.control === 'select' ) {
			field.options = f.enum || [];
		}
		return field;
	} );
	const covered = fields.map( ( f ) => f.name );
	const uncontrolled = declared.filter(
		( name ) => ! covered.includes( name )
	);
	if ( uncontrolled.length > 0 ) {
		throw new Error(
			`[generate] hero ${ tagSuffix } has attribute(s) ${ uncontrolled.join(
				', '
			) } with no form_mode field, so the block would expose an attribute with no control`
		);
	}
	return fields;
}

/**
 * Describe the block for one hero, from its `block` entry in hero-config.
 *
 * `block.name` is the full registered name and is written out rather than
 * derived: a saved post references a block BY NAME, so the five names that
 * shipped have to survive every future rename of the hero key or its
 * operationId. `assertBlockNames` re-checks the emitted files against it.
 * @param tagSuffix
 * @param cfg
 */
function blockFromHero( tagSuffix, cfg ) {
	const name = String( cfg.block.name || '' );
	if ( ! /^roxyapi\/[a-z0-9]+(-[a-z0-9]+)*$/.test( name ) ) {
		throw new Error(
			`[generate] hero ${ tagSuffix } declares block name "${ name }"; expected roxyapi/<kebab-case>`
		);
	}

	const attributes = {};
	for ( const key of Object.keys( cfg.attributes || {} ) ) {
		// Defaults stay empty even where the hero has one (tz "UTC", spread
		// "daily"). An empty attribute is dropped on the way to the shortcode,
		// which then applies its OWN default, so there is one place a default
		// lives. It also keeps "nothing filled in yet" distinguishable in the
		// editor, which is what stops a freshly inserted block calling the API.
		attributes[ key ] = { type: 'string', default: '' };
	}

	const takesInput = Object.values( cfg.attributes || {} ).some(
		( info ) => info.required
	);

	return {
		slug: name.slice( 'roxyapi/'.length ),
		name,
		title: assertNoApiWording(
			titleCase( cfg.title ),
			`${ tagSuffix } block title`
		),
		description: assertNoApiWording(
			cfg.description,
			`${ tagSuffix } block description`
		),
		icon: cfg.block.icon,
		keywords: cfg.block.keywords,
		attributes,
		fields: heroBlockFields( tagSuffix, cfg ),
		phpClass: `\\RoxyAPI\\Generated\\Heroes\\${ toPascalCase(
			tagSuffix
		) }`,
		hidden: false,
		instructions:
			cfg.form_mode && takesInput
				? 'Fill these in to publish a fixed reading, or leave them blank to publish a form your visitors fill in.'
				: 'Choose the options in the sidebar to preview this reading here.',
		sourceLabel: `hero ${ tagSuffix }`,
	};
}

/**
 * @param {BlockDescriptor} block
 */
function emitBlockJson( block ) {
	return (
		JSON.stringify(
			{
				$schema: 'https://schemas.wp.org/trunk/block.json',
				apiVersion: 3,
				name: block.name,
				title: block.title,
				category: 'roxyapi',
				icon: block.icon,
				description: block.description,
				keywords: block.keywords,
				version: '1.0.0',
				textdomain: 'roxyapi',
				supports: {
					html: false,
					align: [ 'wide', 'full' ],
					color: { background: true, text: true },
					spacing: { padding: true, margin: true },
					// Kept out of the inserter, not deregistered: a saved post
					// references a block by name, so removing the type would break
					// content that already uses it.
					...( block.hidden ? { inserter: false } : {} ),
				},
				attributes: block.attributes,
				render: 'file:./render.php',
				editorScript: 'file:./index.js',
			},
			null,
			'\t'
		) + '\n'
	);
}

/**
 * @param {BlockDescriptor} block
 */
function emitBlockRenderPhp( block ) {
	return `<?php
/**
 * Server-side render for the auto-generated ${ block.sourceLabel } block.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs.
 *
 * @package RoxyAPI
 * @var array $attributes Block attributes.
 * @var string $content Inner block content.
 * @var WP_Block $block Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

\\RoxyAPI\\Support\\BlockOutput::render( ${ block.phpClass }::render( \\RoxyAPI\\Support\\BlockOutput::to_shortcode_atts( $attributes ) ) );
`;
}

/**
 * Derive the editor field list for a generated block from the spec inputs.
 * Mirrors the inputs emitBlockJson turns into block attributes, so every
 * attribute has a matching sidebar control. Control per field: select for an
 * enum, toggle for a boolean, number for a numeric param, date/time by format
 * or name suffix (a *datetime param stays plain text, since one date or time
 * input cannot express it), text otherwise. name stays the camelCase block
 * attribute key; render.php snake-cases it for the shortcode. Consumed by
 * makeEdit in blocks/_shared/generated-edit.js.
 * @param op
 */
function deriveBlockFields( op ) {
	return blockInputs( op ).map( ( input ) => {
		const options = Array.isArray( input.enum ) ? input.enum : null;
		const lower = input.name.toLowerCase();
		let control = 'text';
		if ( options && options.length ) {
			control = 'select';
		} else if ( input.type === 'boolean' ) {
			control = 'toggle';
		} else if ( input.type === 'integer' || input.type === 'number' ) {
			// A number-or-string param (numericString, e.g. timezone accepting
			// both -5 and "America/New_York") stays text so the IANA name is
			// typable; the shortcode casts a numeric string on the way out.
			control = 'number';
		} else if ( lower.endsWith( 'datetime' ) ) {
			control = 'text';
		} else if ( input.format === 'date' || lower.endsWith( 'date' ) ) {
			control = 'date';
		} else if ( input.format === 'time' || lower.endsWith( 'time' ) ) {
			control = 'time';
		}

		const field = {
			name: input.name,
			control,
			label: humanLabel( input.name ),
			required: !! input.required,
		};
		if ( input.description ) {
			field.help = firstSentence( input.description );
		}
		if ( control === 'select' ) {
			field.options = options;
		}
		return field;
	} );
}

/**
 * A single-quoted JavaScript string literal.
 *
 * JSON.stringify does the escaping (quotes, backslashes, newlines, control
 * characters), then the outer quotes are swapped for the single quotes the rest
 * of the emitted editor source uses.
 * @param value
 */
function jsLiteral( value ) {
	return `'${ JSON.stringify( String( value ) )
		.slice( 1, -1 )
		.replace( /\\"/g, '"' )
		.replace( /'/g, "\\'" ) }'`;
}

/**
 * A `__()` call around a JavaScript literal, so `wp i18n make-pot` can see it.
 * @param value
 */
function translatableJs( value ) {
	return `__( ${ jsLiteral( value ) }, 'roxyapi' )`;
}

/**
 * Serialise a derived field list as JavaScript source rather than JSON.
 *
 * `label` and `help` are the whole of what the block inspector shows, and as
 * JSON they were bare string literals: invisible to `wp i18n make-pot`, absent
 * from the POT, and untranslatable by any means. Emitting them as `__()` calls
 * puts them in the catalogue with every other editor string. Everything else in
 * a field descriptor is machine data (attribute name, control type, enum
 * values) and stays a literal.
 * @param fields
 */
function fieldsToJsSource( fields ) {
	const js = jsLiteral;
	const t = translatableJs;
	const body = fields
		.map( ( field ) => {
			const lines = [
				`\t\tname: ${ js( field.name ) },`,
				`\t\tcontrol: ${ js( field.control ) },`,
				`\t\tlabel: ${ t( field.label ) },`,
				`\t\trequired: ${ field.required ? 'true' : 'false' },`,
			];
			if ( field.help ) {
				lines.push( `\t\thelp: ${ t( field.help ) },` );
			}
			if ( field.placeholder ) {
				lines.push( `\t\tplaceholder: ${ t( field.placeholder ) },` );
			}
			if ( field.options ) {
				lines.push(
					`\t\toptions: [ ${ field.options
						.map( ( option ) => js( option ) )
						.join( ', ' ) } ],`
				);
			}
			return `\t{\n${ lines.join( '\n' ) }\n\t},`;
		} )
		.join( '\n' );

	return fields.length ? `[\n${ body }\n]` : '[]';
}

/**
 * Emit the editorScript (index.js) for a generated block. Thin and spec-driven:
 * it registers the block on the client with the shared makeEdit editor
 * (blocks/_shared/generated-edit.js) and the block's field list, so one editor
 * component drives every block. save returns null because the block is
 * server-rendered by render.php.
 * @param {BlockDescriptor} block
 */
function emitBlockIndexJs( block ) {
	const fields = fieldsToJsSource( block.fields );
	// A block whose operation takes no inputs has no labels to translate, and an
	// unused import is a lint error.
	const needsI18n = block.fields.length > 0 || block.instructions;
	const i18nImport = needsI18n
		? "import { __ } from '@wordpress/i18n';\n"
		: '';
	// The empty-state copy differs per block because what publishing an
	// unconfigured block DOES differs: a hero with required inputs publishes a
	// visitor form, everything else publishes nothing until it is filled in.
	const options = block.instructions
		? `, {\n\t\tinstructions: ${ translatableJs(
				block.instructions
		  ) },\n\t}`
		: '';
	return `import { registerBlockType } from '@wordpress/blocks';
${ i18nImport }import metadata from './block.json';
import { makeEdit } from '../../_shared/generated-edit';

// Generated from the OpenAPI spec by bin/generate.mjs. DO NOT EDIT.
const fields = ${ fields };

registerBlockType( metadata.name, {
	edit: makeEdit( fields, metadata.name${ options } ),
	save: () => null,
} );
`;
}

// ---------------------------------------------------------------------------
// Emit the Shortcode bootstrapper that registers all generated shortcodes
// ---------------------------------------------------------------------------

function emitBootstrapPhp( generatedOps ) {
	const registerCalls = generatedOps.map( ( op ) => {
		const className = toPascalCase( op.operationId );
		return `\t\t\\RoxyAPI\\Generated\\Shortcodes\\${ className }::register();`;
	} );

	return `<?php
/**
 * Auto-generated shortcode bootstrap.
 *
 * Registers all generated shortcodes. Called from the Shortcodes\\Registrar
 * at priority 20 (after hero shortcodes at priority 10).
 *
 * DO NOT EDIT. Generated by bin/generate.mjs.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ShortcodeBootstrap {

	public static function register(): void {
${ registerCalls.join( '\n' ) }
	}
}
`;
}

// ---------------------------------------------------------------------------
// Hero shortcode emission
// ---------------------------------------------------------------------------

/** Build a quick lookup of operationId -> operation record for hero emission. */
const opsByIdMap = {};
for ( const op of operations ) {
	opsByIdMap[ op.operationId ] = op;
}

/**
 * Classify one hero attribute against a target operation's spec slot.
 * @param attr
 * @param op
 */
function _attrSlotForOperation( attr, op ) {
	// path-param wins if the spec field name matches a path placeholder
	const pathParams = extractPathParams( op.path );
	const targetField = attr.spec_field || attr.name;
	if ( pathParams.includes( targetField ) ) {
		return 'path';
	}
	// query param check
	for ( const p of op.parameters || [] ) {
		if ( p.in === 'query' && p.name === targetField ) {
			return 'query';
		}
	}
	// body fallback for POST endpoints
	if ( op.method === 'POST' ) {
		return 'body';
	}
	// else: query for GET endpoints
	return 'query';
}

/**
 * Attributes every rendering shortcode carries so one placement can override a
 * site-wide display setting. Declared here rather than per hero in
 * bin/hero-config.json because they are not API inputs: they never reach a
 * request body or query string, and a hero or endpoint added later has to pick
 * them up without anyone remembering to declare them.
 *
 * `inherit` follows the setting. `hide_readings` reads any other value as a
 * boolean; `hide_sections` reads it as the same comma-separated name list the
 * setting takes. Both are resolved in `ComponentRenderer`, the one place that
 * knows how to fold a placement value over a site value.
 *
 * **A third one goes in this array, plus the matching constant, resolver and
 * parameter on `ComponentRenderer`, and nowhere else.** Emitted shortcodes never
 * name a reserved attribute individually: they declare the whole set through
 * `withReservedAtts()` and hand the resolved `$atts` ARRAY to
 * `ComponentRenderer::render_atts()` or `FormRenderer::render()`, both of which
 * read the names out of `ComponentRenderer::RESERVED_ATTS`.
 *
 * That indirection is not decoration. The previous shape passed a positional
 * argument list built here, and the two call sites written BY HAND rather than
 * generated each fell behind it: `Horoscope.php` never gained `hide_sections`,
 * and `FormRenderer` passed neither, so every hero ignored both controls in FORM
 * mode from the day `hide_readings` shipped while honouring them in static mode.
 * `tests/phpunit/test-reserved-atts.php` now sweeps every hero and every render
 * path against this array, so a call site cannot fall behind it again.
 */
const RESERVED_ATTS = [
	{ att: 'hide_readings', default: 'inherit' },
	{ att: 'hide_sections', default: 'inherit' },
];

/**
 * Append every reserved attribute to a DEFAULTS line list. Throws when an
 * endpoint already declares an input of one of those names, since the shortcode
 * would then have two meanings for one key.
 * @param lines  Existing `'key' => value,` lines.
 * @param indent Leading whitespace for one line.
 */
function withReservedAtts( lines, indent ) {
	for ( const { att } of RESERVED_ATTS ) {
		if ( lines.some( ( line ) => line.includes( `'${ att }'` ) ) ) {
			throw new Error(
				`[generate] an operation declares a "${ att }" input, which collides with the reserved shortcode attribute`
			);
		}
	}
	return [
		...lines,
		...RESERVED_ATTS.map(
			( { att, default: value } ) =>
				`${ indent }'${ att }' => '${ value }',`
		),
	];
}

/**
 * Render a Sanitize::name(...) call for the given sanitize directive.
 * @param sanitize
 * @param valueExpr
 */
function renderSanitizeCall( sanitize, valueExpr ) {
	if ( ! sanitize ) {
		return valueExpr;
	}
	if ( typeof sanitize === 'string' ) {
		if ( sanitize === 'key' ) {
			return `sanitize_key( (string) ${ valueExpr } )`;
		}
		return `\\RoxyAPI\\Support\\Sanitize::${ sanitize }( ${ valueExpr } )`;
	}
	if ( sanitize && typeof sanitize === 'object' && sanitize.name ) {
		const args = ( sanitize.args || [] )
			.map( ( a ) => JSON.stringify( a ) )
			.join( ', ' );
		return `\\RoxyAPI\\Support\\Sanitize::${
			sanitize.name
		}( ${ valueExpr }${ args ? ', ' + args : '' } )`;
	}
	return valueExpr;
}

/**
 * Build the PHP array literal contents for a DEFAULTS constant from a hero config.
 * @param attributes
 * @param hasFormMode
 */
function renderDefaultsArray( attributes, hasFormMode = false ) {
	const lines = Object.entries( attributes ).map( ( [ key, info ] ) => {
		const def = info.default ?? '';
		return `\t\t'${ key }' => ${ JSON.stringify( def ) },`;
	} );
	if ( hasFormMode ) {
		// Mode attribute opts in/out of form rendering. `auto` (default) shows
		// the visitor form when required attrs are missing; `form` always shows
		// the form; `static` preserves the legacy missing-attrs error message.
		lines.push( `\t\t'mode' => 'auto',` );
	}
	return withReservedAtts( lines, '\t\t' ).join( '\n' );
}

/**
 * Resolve a transformer name to its PHP call. Currently only one transformer.
 * @param transformName
 * @param valueExpr
 */
function transformPhpCall( transformName, valueExpr ) {
	if ( transformName === 'split_iso_date_into_year_month_day' ) {
		return `\\RoxyAPI\\Support\\HeroTransforms::split_iso_date_into_year_month_day( ${ valueExpr } )`;
	}
	throw new Error( `[generate] unknown hero transform: ${ transformName }` );
}

/**
 * Build the per-attribute "sanitised" PHP block. Returns lines + a map of name -> phpExpression.
 * @param attributes
 */
function renderAttrSanitisation( attributes ) {
	const lines = [];
	const sanitisedExpr = {}; // attr name -> PHP expression
	const transformedFields = {}; // attr name -> { fields: [string], errorTag: string }

	for ( const [ name, info ] of Object.entries( attributes ) ) {
		const raw = `$atts['${ name }']`;
		if ( info.transform ) {
			lines.push(
				`\t\t$${ name }_parts = ${ transformPhpCall(
					info.transform,
					raw
				) };`
			);
			transformedFields[ name ] = info.transform;
			continue;
		}
		if ( info.sanitize ) {
			const expr = renderSanitizeCall( info.sanitize, raw );
			lines.push( `\t\t$${ name }_clean = ${ expr };` );
			sanitisedExpr[ name ] = `$${ name }_clean`;
		} else {
			sanitisedExpr[ name ] = raw;
		}
	}

	return { lines: lines.join( '\n' ), sanitisedExpr, transformedFields };
}

/**
 * Build the "required" check block using the example interpolation for the example.
 * @param attributes
 * @param missingMessage
 * @param example
 * @param formClassName
 */
function renderRequiredCheck(
	attributes,
	missingMessage,
	example,
	formClassName = null
) {
	const requiredKeys = Object.entries( attributes )
		.filter( ( [ _, info ] ) => info.required )
		.map( ( [ k ] ) => k );
	if ( requiredKeys.length === 0 || ! missingMessage ) {
		return '';
	}
	const conditions = requiredKeys
		.map( ( k ) => `$atts['${ k }'] === ''` )
		.join( ' || ' );
	const translatorComment = `/* translators: %s is the canonical example shortcode. */`;
	const messagePhp = `sprintf( ${ translatorComment } __( ${ JSON.stringify(
		missingMessage
	) }, 'roxyapi' ), ${ JSON.stringify( example ) } )`;
	if ( formClassName ) {
		// When form_mode is configured, missing required attrs render the
		// visitor form (unless mode='static' explicitly opts out, which
		// preserves the legacy error message for site owners who do not want
		// a form on the page).
		return `\t\tif ( ${ conditions } ) {
			if ( $atts['mode'] !== 'static' ) {
				return \\RoxyAPI\\Support\\FormRenderer::render( \\RoxyAPI\\Generated\\Forms\\${ formClassName }::class, $atts );
			}
			return \\RoxyAPI\\Support\\Templates::error( ${ messagePhp } );
		}`;
	}
	return `\t\tif ( ${ conditions } ) {\n\t\t\treturn \\RoxyAPI\\Support\\Templates::error( ${ messagePhp } );\n\t\t}`;
}

/**
 * Render the form-mode short-circuit block placed at the top of render().
 * When `mode='form'` is passed, render the visitor form regardless of
 * whether other attrs are present.
 * @param formClassName
 */
function renderFormModeShortCircuit( formClassName ) {
	if ( ! formClassName ) {
		return '';
	}
	return `\t\tif ( $atts['mode'] === 'form' ) {
			return \\RoxyAPI\\Support\\FormRenderer::render( \\RoxyAPI\\Generated\\Forms\\${ formClassName }::class, $atts );
		}`;
}

/**
 * Required-attrs check for fetch_for_form: returns WP_Error on missing.
 * @param attributes
 * @param missingMessage
 * @param example
 */
function renderRequiredCheckFetch( attributes, missingMessage, example ) {
	const requiredKeys = Object.entries( attributes )
		.filter( ( [ _, info ] ) => info.required )
		.map( ( [ k ] ) => k );
	if ( requiredKeys.length === 0 || ! missingMessage ) {
		return '';
	}
	const conditions = requiredKeys
		.map( ( k ) => `$atts['${ k }'] === ''` )
		.join( ' || ' );
	const translatorComment = `/* translators: %s is the canonical example shortcode. */`;
	const messagePhp = `sprintf( ${ translatorComment } __( ${ JSON.stringify(
		missingMessage
	) }, 'roxyapi' ), ${ JSON.stringify( example ) } )`;
	return `\t\tif ( ${ conditions } ) {\n\t\t\treturn new \\WP_Error( 'roxyapi_missing_attrs', ${ messagePhp } );\n\t\t}`;
}

/**
 * Transform error blocks for fetch_for_form: returns WP_Error on null parts.
 * @param transformedFields
 * @param cfg
 */
function renderTransformErrorBlocksFetch( transformedFields, cfg ) {
	const blocks = [];
	for ( const [ name ] of Object.entries( transformedFields ) ) {
		const translatorComment = `/* translators: %s is the canonical example shortcode. */`;
		const errMessage = cfg.transform_error_message
			? `sprintf( ${ translatorComment } __( ${ JSON.stringify(
					cfg.transform_error_message
			  ) }, 'roxyapi' ), ${ JSON.stringify( cfg.example ) } )`
			: `__( ${ JSON.stringify(
					`The ${ name } attribute must be in YYYY-MM-DD format.`
			  ) }, 'roxyapi' )`;
		blocks.push(
			`\t\tif ( $${ name }_parts === null ) {
			return new \\WP_Error( 'roxyapi_invalid_format', ${ errMessage } );
		}`
		);
	}
	return blocks.join( '\n' );
}

/**
 * Build the PHP code that calls a single operation (with the right arg shape).
 * @param opId
 * @param argsByName
 */
function renderClientCall( opId, argsByName ) {
	// argsByName: { fieldName: phpExpression } for body POST OR for path/query GET
	const op = opsByIdMap[ opId ];
	if ( ! op ) {
		throw new Error(
			`[generate] hero references unknown operationId: ${ opId }`
		);
	}
	const pathParams = extractPathParams( op.path );

	if ( op.method === 'POST' ) {
		// For POST: path params come first, then body array
		const pathArgs = pathParams.map( ( p ) =>
			argsByName[ p ] !== undefined ? argsByName[ p ] : "''"
		);
		const bodyEntries = [];
		for ( const [ field, expr ] of Object.entries( argsByName ) ) {
			if ( pathParams.includes( field ) ) {
				continue;
			}
			bodyEntries.push( `\t\t\t'${ field }' => ${ expr },` );
		}
		const bodyArrayPhp =
			bodyEntries.length === 0
				? 'array()'
				: `array(\n${ bodyEntries.join( '\n' ) }\n\t\t)`;
		return `\\RoxyAPI\\Generated\\Client::${ opId }( ${ [
			...pathArgs,
			bodyArrayPhp,
		].join( ', ' ) } )`;
	}
	// GET: ordered args = pathParams (in path order), then query params (in spec order)
	const orderedArgs = [];
	for ( const p of pathParams ) {
		orderedArgs.push(
			argsByName[ p ] !== undefined ? argsByName[ p ] : "''"
		);
	}
	const queryParams = ( op.parameters || [] ).filter(
		( p ) => p.in === 'query'
	);
	for ( const qp of queryParams ) {
		if ( argsByName[ qp.name ] !== undefined ) {
			orderedArgs.push( argsByName[ qp.name ] );
		} else {
			orderedArgs.push( 'null' );
		}
	}
	// trim trailing nulls
	while (
		orderedArgs.length > 0 &&
		orderedArgs[ orderedArgs.length - 1 ] === 'null'
	) {
		orderedArgs.pop();
	}
	// Drop trailing nulls only if the spec method declares them with defaults.
	// The Client.php signatures we generate have `= null` for query params, so
	// we can drop trailing nulls safely.
	return `\\RoxyAPI\\Generated\\Client::${ opId }( ${ orderedArgs.join(
		', '
	) } )`;
}

/**
 * Build the full set of (fieldName -> phpExpr) for a single dispatch branch.
 * @param attributes
 * @param sanitisedExpr
 * @param transformedFields
 * @param op
 * @param branchOpts
 */
function buildArgsForBranch(
	attributes,
	sanitisedExpr,
	transformedFields,
	op,
	branchOpts
) {
	const args = {};
	const passthrough = branchOpts.passthrough || {};
	const pathArgs = branchOpts.path_args || {};
	const queryArgs = branchOpts.query_args || {};

	// Union of all attributes consumed by this branch (passthrough, path_args, query_args).
	const consumed = new Set( [
		...Object.keys( passthrough ),
		...Object.keys( pathArgs ),
		...Object.keys( queryArgs ),
	] );

	for ( const attrName of consumed ) {
		const info = attributes[ attrName ];
		if ( ! info ) {
			throw new Error(
				`[generate] dispatch branch references unknown attr: ${ attrName }`
			);
		}
		// Determine target field name in the spec
		const specName =
			passthrough[ attrName ] ||
			pathArgs[ attrName ] ||
			queryArgs[ attrName ];
		const valueExpr =
			sanitisedExpr[ attrName ] !== undefined
				? sanitisedExpr[ attrName ]
				: `$atts['${ attrName }']`;
		args[ specName ] = valueExpr;
	}

	return args;
}

/**
 * Build the body of the hero method that calls the upstream API.
 *
 * Two output modes:
 *   - "render": emits a PHP fragment that returns rendered HTML via
 *     `ComponentRenderer::render()` on success and `Templates::api_error()` on
 *     failure. ComponentRenderer emits a web component for mapped operationIds
 *     and falls back to the generic card renderer otherwise. Used inside the
 *     hero's `render()` method.
 *   - "fetch": emits a PHP fragment that returns the raw API response array
 *     on success and the original `WP_Error` on failure. Used inside the
 *     hero's `fetch_for_form()` method which the matching `<Hero>Form` class
 *     calls back into.
 *
 * The dispatch / single-target / skip_if_empty branching logic is identical
 * in both modes; only the leaf return statements differ.
 * @param tagSuffix
 * @param cfg
 * @param sanitisedExpr
 * @param transformedFields
 * @param transformedSubExpr
 * @param mode
 */
function buildHeroBodyContent(
	tagSuffix,
	cfg,
	sanitisedExpr,
	transformedFields,
	transformedSubExpr,
	mode
) {
	const returnsHtml = mode === 'render';

	const successReturn = ( opId ) =>
		returnsHtml
			? `return \\RoxyAPI\\Support\\ComponentRenderer::render_atts( '${ opId }', is_array( $data ) ? $data : array(), $atts );`
			: `return is_array( $data ) ? $data : array();`;
	const errorReturn = returnsHtml
		? `return \\RoxyAPI\\Support\\Templates::api_error( $data );`
		: `return $data;`;

	if ( cfg.dispatch ) {
		// Multi-target dispatch hero (TarotCard / IChing / Dream).
		const branches = [];
		for ( let i = 0; i < cfg.dispatch.length; i++ ) {
			const d = cfg.dispatch[ i ];
			const op = opsByIdMap[ d.operationId ];
			if ( ! op ) {
				throw new Error(
					`[generate] dispatch references unknown operationId: ${ d.operationId }`
				);
			}
			let condition = null;
			if ( d.when ) {
				const parts = [];
				for ( const [ attrName, expectedValue ] of Object.entries(
					d.when
				) ) {
					const expr =
						sanitisedExpr[ attrName ] !== undefined
							? sanitisedExpr[ attrName ]
							: `$atts['${ attrName }']`;
					parts.push(
						`${ expr } === ${ JSON.stringify( expectedValue ) }`
					);
				}
				condition = parts.join( ' && ' );
			} else if ( d.when_present ) {
				condition = `$atts['${ d.when_present }'] !== ''`;
			} else if ( d.default ) {
				condition = null;
			}

			const argsByName = buildArgsForBranch(
				cfg.attributes,
				sanitisedExpr,
				transformedFields,
				op,
				d
			);
			const callExpr = renderClientCall( d.operationId, argsByName );

			const branchBody = `\t\t\t$data = ${ callExpr };
			if ( is_wp_error( $data ) ) {
				${ errorReturn }
			}
			${ successReturn( d.operationId ) }`;

			if ( condition === null ) {
				branches.push( `\t\t{\n${ branchBody }\n\t\t}` );
			} else if ( i === 0 ) {
				branches.push(
					`\t\tif ( ${ condition } ) {\n${ branchBody }\n\t\t}`
				);
			} else {
				branches.push(
					`if ( ${ condition } ) {\n${ branchBody }\n\t\t}`
				);
			}
		}

		const stitched = branches.reduce(
			( acc, b, i ) => ( i === 0 ? b : acc + ' else ' + b ),
			''
		);

		// Dispatch fallback for cases where no branch matches and no default
		// branch exists. In render mode this surfaces a friendly Templates::error;
		// in fetch mode a WP_Error so FormRouter can render it via Templates::api_error.
		// Form-mode heroes also offer a re-render of the visitor form when the
		// current site mode is not 'static'.
		let fallback = '';
		const hasDefault = cfg.dispatch.some( ( d ) => d.default );
		if ( ! hasDefault && cfg.missing_message ) {
			const translatorComment = `/* translators: %s is the canonical example shortcode. */`;
			const messagePhp = `sprintf( ${ translatorComment } __( ${ JSON.stringify(
				cfg.missing_message
			) }, 'roxyapi' ), ${ JSON.stringify( cfg.example ) } )`;
			if ( returnsHtml && cfg.form_mode ) {
				const formClassName = `${ toPascalCase( tagSuffix ) }Form`;
				fallback = `\n\n\t\tif ( $atts['mode'] !== 'static' ) {
			return \\RoxyAPI\\Support\\FormRenderer::render( \\RoxyAPI\\Generated\\Forms\\${ formClassName }::class, $atts );
		}
		return \\RoxyAPI\\Support\\Templates::error( ${ messagePhp } );`;
			} else if ( returnsHtml ) {
				fallback = `\n\n\t\treturn \\RoxyAPI\\Support\\Templates::error( ${ messagePhp } );`;
			} else {
				fallback = `\n\n\t\treturn new \\WP_Error( 'roxyapi_missing_attrs', ${ messagePhp } );`;
			}
		}

		return `${ stitched }${ fallback }`;
	}

	// Single-target hero.
	const op = opsByIdMap[ cfg.operationId ];
	if ( ! op ) {
		throw new Error(
			`[generate] hero ${ tagSuffix } references unknown operationId: ${ cfg.operationId }`
		);
	}

	const args = {};
	const skipIfEmptyEntries = [];
	for ( const [ attrName, info ] of Object.entries( cfg.attributes ) ) {
		if ( info.transform ) {
			args.year = transformedSubExpr( attrName, 'year' );
			args.month = transformedSubExpr( attrName, 'month' );
			args.day = transformedSubExpr( attrName, 'day' );
			continue;
		}
		const fieldName = info.spec_field || attrName;
		const valueExpr =
			sanitisedExpr[ attrName ] !== undefined
				? sanitisedExpr[ attrName ]
				: `$atts['${ attrName }']`;
		args[ fieldName ] = valueExpr;
		if ( info.skip_if_empty ) {
			skipIfEmptyEntries.push( { attrName, fieldName } );
		}
	}

	if ( skipIfEmptyEntries.length > 0 && op.method === 'POST' ) {
		const pathParams = extractPathParams( op.path );
		const bodyLines = [];
		const conditional = [];
		for ( const [ field, expr ] of Object.entries( args ) ) {
			if ( pathParams.includes( field ) ) {
				continue;
			}
			const skipEntry = skipIfEmptyEntries.find(
				( s ) => s.fieldName === field
			);
			if ( skipEntry ) {
				conditional.push( {
					field,
					expr,
					attrName: skipEntry.attrName,
				} );
			} else {
				bodyLines.push( `\t\t\t'${ field }' => ${ expr },` );
			}
		}
		const bodyArrayPhp =
			bodyLines.length === 0
				? 'array()'
				: `array(\n${ bodyLines.join( '\n' ) }\n\t\t)`;
		const conditionalAdds = conditional
			.map(
				( c ) =>
					`\t\tif ( $atts['${ c.attrName }'] !== '' ) {\n\t\t\t$body['${ c.field }'] = ${ c.expr };\n\t\t}`
			)
			.join( '\n' );
		const pathArgsList = pathParams
			.map( ( p ) => ( args[ p ] !== undefined ? args[ p ] : "''" ) )
			.join( ', ' );
		const callArgs = pathArgsList ? `${ pathArgsList }, $body` : '$body';

		return `\t\t$body = ${ bodyArrayPhp };
${ conditionalAdds }

		$data = \\RoxyAPI\\Generated\\Client::${ cfg.operationId }( ${ callArgs } );

		if ( is_wp_error( $data ) ) {
			${ errorReturn }
		}

		${ successReturn( cfg.operationId ) }`;
	}

	const callExpr = renderClientCall( cfg.operationId, args );
	return `\t\t$data = ${ callExpr };

		if ( is_wp_error( $data ) ) {
			${ errorReturn }
		}

		${ successReturn( cfg.operationId ) }`;
}

/**
 * Emit the PHP class for a single hero.
 * @param tagSuffix
 * @param cfg
 */
function emitHeroPhp( tagSuffix, cfg ) {
	const className = toPascalCase( tagSuffix );
	const shortcodeTag = 'roxy_' + tagSuffix;

	// Heroes that delegate to an existing long-tail Form (synastry, gun_milan,
	// compatibility) are essentially a clean alias for the form-mode shortcode.
	// Static mode is impractical (10+ attributes) so the hero just renders the
	// Form class on every invocation. No DEFAULTS beyond the tag, no
	// fetch_for_form, no companion <Hero>Form class — we reuse the long-tail one.
	if ( cfg.delegate_to_form ) {
		return `<?php
/**
 * Auto-generated hero shortcode: [${ shortcodeTag }]
 *
 * ${ cfg.description }
 *
 * Form-only hero. Delegates rendering to the existing long-tail Form
 * class \\RoxyAPI\\Generated\\Forms\\${ cfg.delegate_to_form }, which already
 * carries the right multi-section visitor form (two birth charts) and
 * call() implementation. FormRouter handles the POST cycle.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs from bin/hero-config.json.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated\\Heroes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ${ className } {

	/**
	 * Every INPUT flows through the form, so the only attributes declared
	 * here are the reserved display controls. They have to be declared:
	 * \`shortcode_atts()\` keeps only the keys it is given, so an undeclared
	 * one is dropped in silence rather than refused.
	 *
	 * @var array<string, string>
	 */
	public const DEFAULTS = array(
${ withReservedAtts( [], '\t\t' ).join( '\n' ) }
	);

	/**
	 * Render the shortcode. Always shows the visitor form because static
	 * mode would require 10+ attributes to be passed inline.
	 *
	 * @param array<string, string>|string $atts    Shortcode attributes. Inputs are
	 *                                              ignored; the reserved display
	 *                                              attributes are honoured.
	 * @param string                       $content Inner content (ignored).
	 * @param string                       $tag     Shortcode tag.
	 * @return string
	 */
	public static function render( $atts, $content = '', $tag = '' ): string {
		$atts = shortcode_atts( self::DEFAULTS, is_array( $atts ) ? $atts : array(), (string) $tag );
		wp_enqueue_style( 'roxyapi-frontend' );
		return \\RoxyAPI\\Support\\FormRenderer::render( \\RoxyAPI\\Generated\\Forms\\${
			cfg.delegate_to_form
		}::class, $atts );
	}
}
`;
	}

	const hasFormMode = !! cfg.form_mode;
	const formClassName = hasFormMode ? `${ className }Form` : null;

	const defaultsArray = renderDefaultsArray( cfg.attributes, hasFormMode );
	const {
		lines: sanitiseLines,
		sanitisedExpr,
		transformedFields,
	} = renderAttrSanitisation( cfg.attributes );
	const requiredCheck = renderRequiredCheck(
		cfg.attributes,
		cfg.missing_message,
		cfg.example,
		formClassName
	);
	const formModeShortCircuit = renderFormModeShortCircuit( formClassName );

	// Transform error-handling block: for every transformed field, emit a null-check.
	const transformErrorBlocks = [];
	for ( const [ name ] of Object.entries( transformedFields ) ) {
		const translatorComment = `/* translators: %s is the canonical example shortcode. */`;
		const errMessage = cfg.transform_error_message
			? `sprintf( ${ translatorComment } __( ${ JSON.stringify(
					cfg.transform_error_message
			  ) }, 'roxyapi' ), ${ JSON.stringify( cfg.example ) } )`
			: `__( ${ JSON.stringify(
					`The ${ name } attribute must be in YYYY-MM-DD format.`
			  ) }, 'roxyapi' )`;
		transformErrorBlocks.push(
			`\t\tif ( $${ name }_parts === null ) {
			return \\RoxyAPI\\Support\\Templates::error( ${ errMessage } );
		}`
		);
	}

	// Build expressions that include transformed parts injected into argsByName.
	// Transformed fields produce a map of $name_parts['year'|'month'|'day'].
	function transformedSubExpr( attrName, fieldName ) {
		return `$${ attrName }_parts['${ fieldName }']`;
	}

	const bodyContent = buildHeroBodyContent(
		tagSuffix,
		cfg,
		sanitisedExpr,
		transformedFields,
		transformedSubExpr,
		'render'
	);

	const sections = [
		`\t\t$atts = shortcode_atts(\n\t\t\tself::DEFAULTS,\n\t\t\tis_array( $atts ) ? $atts : array(),\n\t\t\t(string) $tag\n\t\t);`,
		`\t\twp_enqueue_style( 'roxyapi-frontend' );`,
	];
	if ( formModeShortCircuit ) {
		sections.push( formModeShortCircuit );
	}
	if ( requiredCheck ) {
		sections.push( requiredCheck );
	}
	if ( sanitiseLines ) {
		sections.push( sanitiseLines );
	}
	if ( transformErrorBlocks.length > 0 ) {
		sections.push( transformErrorBlocks.join( '\n' ) );
	}
	sections.push( bodyContent );

	// fetch_for_form parallel method (only when form_mode is configured).
	// Same dispatch + sanitisation as render(), but returns array | WP_Error
	// so the matching <Hero>Form::call() can hand the raw response back to
	// FormRouter for a Post-Redirect-Get cycle.
	let fetchMethod = '';
	if ( hasFormMode ) {
		const fetchSections = [
			`\t\t$atts = array_merge( self::DEFAULTS, $atts );`,
		];
		const reqFetch = renderRequiredCheckFetch(
			cfg.attributes,
			cfg.missing_message,
			cfg.example
		);
		if ( reqFetch ) {
			fetchSections.push( reqFetch );
		}
		if ( sanitiseLines ) {
			fetchSections.push( sanitiseLines );
		}
		const transformFetch = renderTransformErrorBlocksFetch(
			transformedFields,
			cfg
		);
		if ( transformFetch ) {
			fetchSections.push( transformFetch );
		}
		const bodyFetch = buildHeroBodyContent(
			tagSuffix,
			cfg,
			sanitisedExpr,
			transformedFields,
			transformedSubExpr,
			'fetch'
		);
		fetchSections.push( bodyFetch );

		fetchMethod = `

	/**
	 * Visitor-form data path. Same dispatch as render() but returns the raw
	 * API response (or a WP_Error) so the matching <Hero>Form::call() can
	 * surface it via the FormRouter PRG cycle. Caller must pass the form
	 * body keyed by the same attribute names as the shortcode accepts.
	 *
	 * @param array<string, mixed> $atts Form-body attributes.
	 * @return array<string, mixed>|\\WP_Error
	 */
	public static function fetch_for_form( array $atts ) {
${ fetchSections.join( '\n\n' ) }
	}`;
	}

	return `<?php
/**
 * Auto-generated hero shortcode: [${ shortcodeTag }]
 *
 * ${ cfg.description }
 *
 * Example: ${ cfg.example }
 *
 * DO NOT EDIT. Generated by bin/generate.mjs from bin/hero-config.json.
 * To change this hero, edit the config and run: npm run generate
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated\\Heroes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ${ className } {

	/**
	 * Default attributes accepted by this shortcode. Single source of truth
	 * for the hero attribute contract test.
	 *
	 * @var array<string, string>
	 */
	public const DEFAULTS = array(
${ defaultsArray }
	);

	/**
	 * Render the shortcode.
	 *
	 * @param array<string, string>|string $atts    Shortcode attributes.
	 * @param string                       $content Inner content.
	 * @param string                       $tag     Shortcode tag.
	 * @return string
	 */
	public static function render( $atts, $content = '', $tag = '' ): string {
${ sections.join( '\n\n' ) }
	}${ fetchMethod }
}
`;
}

/**
 * Convert a hero-config form_mode field declaration to a PHP form-field
 * literal string (one entry in the spec sections/flat_fields array).
 * Only emits keys the FormRenderer recognises so a typo in the config
 * surfaces at PHP rendering rather than silently passing through.
 * @param field
 * @param indent
 */
function heroFormFieldToPhp( field, indent ) {
	const allowed = [
		'name',
		'label',
		'required',
		'type',
		'help',
		'placeholder',
		'min',
		'max',
		'step',
		'enum',
	];
	const lines = [];
	for ( const key of allowed ) {
		if ( field[ key ] === undefined ) {
			continue;
		}
		lines.push(
			`${ indent }\t'${ key }' => ${ specEntryPhp( key, field[ key ] ) },`
		);
	}
	return `${ indent }array(\n${ lines.join( '\n' ) }\n${ indent }),`;
}

/**
 * Emit src/Generated/Forms/<Hero>Form.php for a form-mode hero. The form
 * spec mirrors the hero-config form_mode block; the call() implementation
 * delegates back to <Hero>::fetch_for_form() so dispatch / sanitisation /
 * client-call logic stays in one place.
 * @param tagSuffix
 * @param cfg
 */
function emitHeroFormPhp( tagSuffix, cfg ) {
	if ( ! cfg.form_mode ) {
		return null;
	}
	const heroClass = toPascalCase( tagSuffix );
	const formClassName = `${ heroClass }Form`;
	const formId = lowerCamelCase( tagSuffix );
	const fm = cfg.form_mode;
	const submitLabel = fm.submit_label || 'Submit';
	const title = assertNoApiWording(
		fm.title || cfg.title || tagSuffix,
		`${ tagSuffix } form heading`
	);

	// Build PHP fragments for fields. A single-section spec keeps the
	// FormRenderer geo autocomplete heuristic working (lat / lon / tz in
	// the same fieldset). Without a section, all fields go into flat_fields.
	const fields = ( fm.fields || [] ).map( ( f ) => ( {
		...f,
		name: f.attr || f.name,
	} ) );

	let sectionsPhp = '';
	let flatPhp = '';
	let callBodyMap = '$body';
	if ( fm.section ) {
		const sectionName = fm.section.name;
		const sectionLabel = fm.section.label || humanLabel( sectionName );
		const fieldEntries = fields
			.map( ( f ) => heroFormFieldToPhp( f, '\t\t\t\t\t' ) )
			.join( '\n' );
		sectionsPhp = `\t\t\tarray(
				'name'   => ${ phpLiteral( sectionName ) },
				'label'  => ${ translatablePhp( sectionLabel ) },
				'fields' => array(
${ fieldEntries }
				),
			),`;
		// FormRouter sanitises section data into $body[<section>] sub-array;
		// flatten it for the hero method which expects top-level attr keys.
		callBodyMap = `isset( $body['${ sectionName }'] ) && is_array( $body['${ sectionName }'] ) ? $body['${ sectionName }'] : array()`;
	} else {
		flatPhp = fields
			.map( ( f ) => heroFormFieldToPhp( f, '\t\t\t' ) )
			.join( '\n' );
	}

	return `<?php
/**
 * Auto-generated visitor form for the [${
		cfg.tag || 'roxy_' + tagSuffix
 }] hero shortcode.
 *
 * Surfaced when the shortcode is invoked without required attributes (or
 * with mode="form"). FormRouter sanitises and validates the POST body
 * against this spec, then calls call() which delegates to the hero's
 * fetch_for_form() so the dispatch / sanitisation / client-call logic
 * stays in one place.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs from bin/hero-config.json.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated\\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ${ formClassName } {

	/**
	 * @return array<string, mixed>
	 */
	public static function spec(): array {
		return array(
			'operation_id' => ${ phpLiteral( formId ) },
			'render_operation_id' => ${ phpLiteral( cfg.operationId ) },
			'title'        => ${ translatablePhp( title ) },
			'submit_label' => __( ${ phpLiteral( submitLabel ) }, 'roxyapi' ),
			'sections'     => array(
${ sectionsPhp }
			),
			'flat_fields'  => array(
${ flatPhp }
			),
		);
	}

	/**
	 * @param array<string, mixed> $body
	 * @return array<string, mixed>|\\WP_Error
	 */
	public static function call( array $body ) {
		$atts = ${ callBodyMap };
		return \\RoxyAPI\\Generated\\Heroes\\${ heroClass }::fetch_for_form( $atts );
	}
}
`;
}

/**
 * Convert snake_case_or_kebab-case to lowerCamelCase. Matches the form-id
 * grammar FormRouter validates with `/^[A-Za-z][A-Za-z0-9]+$/`.
 * @param str
 */
function lowerCamelCase( str ) {
	const pascal = toPascalCase( str );
	return pascal.charAt( 0 ).toLowerCase() + pascal.slice( 1 );
}

/**
 * Emit the Heroes Manifest exposing display metadata for every hero.
 * @param heroConfig
 */
function emitHeroManifest( heroConfig ) {
	const lines = [];
	for ( const [ tagSuffix, cfg ] of Object.entries( heroConfig ) ) {
		const tag = cfg.tag || 'roxy_' + tagSuffix;
		const handWritten = cfg.hand_written ? 'true' : 'false';
		const className = cfg.hand_written
			? `\\RoxyAPI\\Shortcodes\\${ toPascalCase( tagSuffix ) }`
			: `\\RoxyAPI\\Generated\\Heroes\\${ toPascalCase( tagSuffix ) }`;
		// Pick the most-representative operationId for this hero so callers
		// like Catalog::all() (which looks up TTL via Endpoints::get) get a
		// hit instead of a silent null:
		//   - Single-target heroes: use cfg.operationId directly.
		//   - delegate_to_form heroes (synastry / gun_milan / compatibility):
		//     use the underlying form-class operation (e.g. CalculateSynastryForm
		//     → calculateSynastry).
		//   - dispatch heroes (tarot_card / iching / dream): use the default
		//     branch's op, falling back to the first branch if no default.
		let opId = cfg.operationId || '';
		if ( ! opId && cfg.delegate_to_form ) {
			// `CalculateSynastryForm` → `calculateSynastry`
			const formBase = cfg.delegate_to_form.replace( /Form$/, '' );
			opId = formBase.charAt( 0 ).toLowerCase() + formBase.slice( 1 );
		}
		if ( ! opId && Array.isArray( cfg.dispatch ) ) {
			const def =
				cfg.dispatch.find( ( d ) => d.default ) || cfg.dispatch[ 0 ];
			opId = def && def.operationId ? def.operationId : '';
		}
		lines.push(
			`\t\t\t'${ tag }' => array(
				'tag'           => '${ tag }',
				'operation_id'  => ${ JSON.stringify( opId ) },
				'title'         => __( ${ JSON.stringify( cfg.title ) }, 'roxyapi' ),
				'description'   => __( ${ JSON.stringify( cfg.description ) }, 'roxyapi' ),
				'code'          => ${ JSON.stringify( cfg.example ) },
				'domain'        => ${ JSON.stringify( cfg.domain ) },
				'class'         => '${ className.replace( /\\/g, '\\\\' ) }',
				'hand_written'  => ${ handWritten },
			),`
		);
	}

	return `<?php
/**
 * Auto-generated hero manifest. Lists every hero shortcode (the one
 * hand-written Horoscope plus every absorbed hero) with its display metadata.
 *
 * Onboarding and Catalog read from this manifest so the docs, the registrar
 * and the runtime stay in lockstep with bin/hero-config.json automatically.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs from bin/hero-config.json.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated\\Heroes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Manifest {

	/**
	 * Hero shortcode display metadata, keyed by shortcode tag.
	 *
	 * @return array<string, array{tag:string,operation_id:string,title:string,description:string,code:string,domain:string,class:string,hand_written:bool}>
	 */
	public static function all(): array {
		return array(
${ lines.join( '\n' ) }
		);
	}
}
`;
}

/**
 * Emit the Heroes Bootstrap that registers every absorbed hero shortcode.
 * @param heroConfig
 */
function _emitHeroBootstrap( heroConfig ) {
	const calls = [];
	for ( const [ tagSuffix, cfg ] of Object.entries( heroConfig ) ) {
		if ( cfg.hand_written ) {
			continue;
		} // hand-written heroes are registered by Shortcodes\Registrar
		const tag = cfg.tag || 'roxy_' + tagSuffix;
		const className = toPascalCase( tagSuffix );
		calls.push(
			`\t\tif ( ! shortcode_exists( '${ tag }' ) ) {
			add_shortcode(
				'${ tag }',
				static function ( $atts, $content, $shortcode_tag ) {
					return \\RoxyAPI\\Generated\\Heroes\\${ className }::render( $atts, $content ?? '', (string) $shortcode_tag );
				}
			);
		}`
		);
	}

	return `<?php
/**
 * Auto-generated hero bootstrap. Registers every absorbed hero shortcode.
 *
 * Called from the Shortcodes\\Registrar at priority 10 alongside the
 * hand-written Horoscope. Hero registration always runs before the generated
 * (long-tail) shortcode bootstrap at priority 20.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs from bin/hero-config.json.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated\\Heroes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bootstrap {

	public static function register(): void {
${ calls.join( '\n' ) }
	}
}
`;
}

// ---------------------------------------------------------------------------
// Write everything
// ---------------------------------------------------------------------------

// 1. Client.php
const clientPhp = emitClientPhp();
await fs.writeFile( path.join( OUT_PHP, 'Client.php' ), clientPhp, 'utf8' );
console.log(
	`[generate] wrote src/Generated/Client.php (${ operations.length } methods)`
);

// 2. Endpoints.php
const endpointsPhp = emitEndpointsPhp();
await fs.writeFile(
	path.join( OUT_PHP, 'Endpoints.php' ),
	endpointsPhp,
	'utf8'
);
console.log(
	`[generate] wrote src/Generated/Endpoints.php (${ operations.length } entries)`
);

// 2b. ComponentMap.php
const componentMapPhp = emitComponentMapPhp();
await fs.writeFile(
	path.join( OUT_PHP, 'ComponentMap.php' ),
	componentMapPhp,
	'utf8'
);
console.log(
	`[generate] wrote src/Generated/ComponentMap.php (${
		Object.keys( componentMap.operations || {} ).length
	} mapped operations)`
);

// 3. Generated shortcodes
for ( const op of generated ) {
	const className = toPascalCase( op.operationId );
	const phpContent = emitShortcodePhp( op );
	await fs.writeFile(
		path.join( OUT_PHP, 'Shortcodes', `${ className }.php` ),
		phpContent,
		'utf8'
	);
}
console.log(
	`[generate] wrote ${ generated.length } shortcode classes to src/Generated/Shortcodes/`
);

// 3b. Generated form classes (visitor-form mode for nested-object endpoints)
let formCount = 0;
for ( const op of generated ) {
	if ( ! hasRequiredObjectBody( op ) ) {
		continue;
	}
	const className = toPascalCase( op.operationId ) + 'Form';
	await fs.writeFile(
		path.join( OUT_PHP, 'Forms', `${ className }.php` ),
		emitFormPhp( op ),
		'utf8'
	);
	formCount++;
}
console.log(
	`[generate] wrote ${ formCount } form classes to src/Generated/Forms/`
);

// 4. ShortcodeBootstrap.php
const bootstrapPhp = emitBootstrapPhp( generated );
await fs.writeFile(
	path.join( OUT_PHP, 'ShortcodeBootstrap.php' ),
	bootstrapPhp,
	'utf8'
);
console.log( `[generate] wrote src/Generated/ShortcodeBootstrap.php` );

// 5. Blocks. Endpoints whose request body needs a nested object (person1,
// natalChart, etc.) are skipped: block attributes can hold objects but the
// editor lacks a nested-attribute UI, so a block here would only show the same
// "structured input not supported" notice the shortcode does. Better to keep
// them out of the inserter entirely.
//
// The heroes that declare a `block` in bin/hero-config.json are written from
// the same descriptors, into the same directory. That directory is wiped and
// rebuilt on every run, which is the point: the five headline blocks used to be
// hand-written, so nothing regenerated them and they sat at `"attributes": {}`
// with a static placeholder for an editor while the long tail grew real
// controls and a live preview.
const blockDescriptors = [];
let blocksSkippedStructured = 0;
for ( const op of generated ) {
	if ( hasRequiredObjectBody( op ) ) {
		blocksSkippedStructured++;
		continue;
	}
	blockDescriptors.push( blockFromOperation( op ) );
}
let heroBlockCount = 0;
for ( const [ tagSuffix, cfg ] of Object.entries( heroConfig ) ) {
	if ( ! cfg.block ) {
		continue;
	}
	if ( cfg.hand_written ) {
		throw new Error(
			`[generate] hero ${ tagSuffix } is hand_written, so its block is hand-written too; remove the "block" entry or the generator will overwrite nothing`
		);
	}
	blockDescriptors.push( blockFromHero( tagSuffix, cfg ) );
	heroBlockCount++;
}

// A duplicate name would silently overwrite one block with another; a duplicate
// TITLE ships two identical-looking rows in the inserter with no way to tell
// them apart, which is the failure the display names exist to prevent.
const seenBlockNames = new Map();
const seenBlockTitles = new Map();
for ( const block of blockDescriptors ) {
	if ( seenBlockNames.has( block.name ) ) {
		throw new Error(
			`[generate] two blocks claim the name ${
				block.name
			}: ${ seenBlockNames.get( block.name ) } and ${ block.sourceLabel }`
		);
	}
	seenBlockNames.set( block.name, block.sourceLabel );
	if ( seenBlockTitles.has( block.title ) ) {
		throw new Error(
			`[generate] two blocks are titled "${
				block.title
			}" (${ seenBlockTitles.get( block.title ) } and ${
				block.sourceLabel
			}); the inserter would show the same row twice`
		);
	}
	seenBlockTitles.set( block.title, block.sourceLabel );
}

for ( const block of blockDescriptors ) {
	const blockDir = path.join( OUT_BLOCKS, block.slug );
	await fs.mkdir( blockDir, { recursive: true } );
	await fs.writeFile(
		path.join( blockDir, 'block.json' ),
		emitBlockJson( block ),
		'utf8'
	);
	await fs.writeFile(
		path.join( blockDir, 'render.php' ),
		emitBlockRenderPhp( block ),
		'utf8'
	);
	await fs.writeFile(
		path.join( blockDir, 'index.js' ),
		emitBlockIndexJs( block ),
		'utf8'
	);
}
console.log(
	`[generate] wrote ${ blockDescriptors.length } blocks to blocks/generated/ (${ heroBlockCount } hero, skipped ${ blocksSkippedStructured } that need nested-object input)`
);

// 6. Hero shortcode classes (one per absorbed hero)
let heroClassCount = 0;
let heroFormCount = 0;
for ( const [ tagSuffix, cfg ] of Object.entries( heroConfig ) ) {
	if ( cfg.hand_written ) {
		continue;
	}
	const className = toPascalCase( tagSuffix );
	const phpContent = emitHeroPhp( tagSuffix, cfg );
	await fs.writeFile(
		path.join( OUT_PHP, 'Heroes', `${ className }.php` ),
		phpContent,
		'utf8'
	);
	heroClassCount++;

	// Companion <Hero>Form class for form-mode heroes.
	const formPhp = emitHeroFormPhp( tagSuffix, cfg );
	if ( formPhp ) {
		await fs.writeFile(
			path.join( OUT_PHP, 'Forms', `${ className }Form.php` ),
			formPhp,
			'utf8'
		);
		heroFormCount++;
	}
}
console.log(
	`[generate] wrote ${ heroClassCount } hero shortcode classes to src/Generated/Heroes/ (+${ heroFormCount } hero form classes)`
);

// 7. Heroes Manifest.php (display metadata for all heroes including hand-written Horoscope)
await fs.writeFile(
	path.join( OUT_PHP, 'Heroes', 'Manifest.php' ),
	emitHeroManifest( heroConfig ),
	'utf8'
);
console.log( `[generate] wrote src/Generated/Heroes/Manifest.php` );

// 8. Heroes Bootstrap.php — INTENTIONALLY NOT EMITTED.
// Hero registration runs through `src/Shortcodes/Registrar::HERO_SHORTCODES`
// (hand-maintained class → tag map). The previously-emitted
// `Generated\Heroes\Bootstrap` was never invoked anywhere in the plugin and
// shipped 5 KB of dead code per install. Removed 2026-04-28 per audit.
// `emitHeroBootstrap` is still defined below for reference but has no
// caller; safe to delete in v1.0.1 along with the function itself.

// 8b. Domains.php — admin-UI registry of OpenAPI tags ordered for the brand book.
const domainEntries = Object.entries( domainRegistry )
	.filter( ( [ k ] ) => ! k.startsWith( '_' ) )
	.map(
		( [ tag, info ] ) =>
			`\t\t\t${ JSON.stringify( tag ) } => array(\n` +
			`\t\t\t\t'label'  => ${ JSON.stringify( info.label ) },\n` +
			`\t\t\t\t'slug'   => ${ JSON.stringify( info.slug ) },\n` +
			`\t\t\t\t'accent' => ${ JSON.stringify( info.accent ) },\n` +
			`\t\t\t),`
	)
	.join( '\n' );
const domainsPhp = `<?php
/**
 * Auto-generated brand-domain registry. Maps OpenAPI tag strings to admin-UI
 * metadata (label, slug, accent). Order matches the brand-book domain order.
 *
 * DO NOT EDIT. Generated by bin/generate.mjs from bin/domains.json.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\\Generated;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Domains {

	/**
	 * Brand-ordered map of OpenAPI tag → label/slug/accent.
	 *
	 * @return array<string, array{label:string,slug:string,accent:string}>
	 */
	public static function all(): array {
		return array(
${ domainEntries }
		);
	}
}
`;
await fs.writeFile( path.join( OUT_PHP, 'Domains.php' ), domainsPhp, 'utf8' );
console.log(
	`[generate] wrote src/Generated/Domains.php (${
		Object.keys( domainRegistry ).filter( ( k ) => ! k.startsWith( '_' ) )
			.length
	} domains)`
);

// 9. Restore README
if ( readmeContent ) {
	await fs.writeFile( readmePath, readmeContent, 'utf8' );
}

// ---------------------------------------------------------------------------
// 10. Validate every generated POST endpoint's example body against the spec
// schema using ajv. Catches type/required/enum/format drift between the
// OpenAPI spec and our example values before the plugin ever hits the SaaS.
// Failures are fatal so codegen cannot land on main without a deliberate
// fix in the spec or an entry in bin/example-overrides.json.
// ---------------------------------------------------------------------------

const { default: Ajv } = await import( 'ajv' );
const { default: addFormats } = await import( 'ajv-formats' );
const ajv = new Ajv( {
	allErrors: true,
	strict: false,
	useDefaults: false,
	// Pre-register every component schema so $ref resolution works without
	// loading remote URIs. ajv treats #/components/schemas/Foo refs natively
	// when the root schema is the spec itself.
} );
addFormats( ajv );
// The API uses `format: time` for local wall-clock birth times (HH:MM:SS, no
// zone; the offset is a separate `timezone` field). ajv-formats follows RFC3339
// strict full-time and rejects any time without a zone, so every birth-time
// example would fail validation. Override with a local-time matcher (seconds
// optional) that still rejects out-of-range values like 25:00:00.
ajv.addFormat( 'time', /^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/ );

let validateFailures = 0;
for ( const op of operations ) {
	if ( op.method !== 'POST' ) {
		continue;
	}
	if ( heroSet.has( op.operationId ) ) {
		continue;
	} // heroes have their own contract test
	if ( hasRequiredObjectBody( op ) ) {
		continue;
	} // block-only by design

	const schemaNode = op.requestBody?.content?.[ 'application/json' ]?.schema;
	if ( ! schemaNode ) {
		continue;
	}

	// Build the body the generated shortcode would build, using the (possibly
	// overridden) spec examples and the same numeric coercion rules.
	const body = {};
	for ( const f of extractBodyFields( op ) ) {
		const ex = applyExampleOverride( op.operationId, f.name, f.example );
		if ( ex === undefined || ex === null || ex === '' ) {
			continue;
		}
		if ( f.type === 'integer' ) {
			body[ f.name ] = typeof ex === 'string' ? parseInt( ex, 10 ) : ex;
		} else if ( f.type === 'number' ) {
			body[ f.name ] = typeof ex === 'string' ? parseFloat( ex ) : ex;
		} else if ( f.type === 'boolean' ) {
			body[ f.name ] = ex === '1' || ex === 'true' || ex === true;
		} else {
			body[ f.name ] = ex;
		}
	}

	// Use the spec as the root schema so #/components/schemas/* refs resolve.
	const validate = ajv.compile( {
		...schemaNode,
		components: spec.components,
	} );
	const ok = validate( body );
	if ( ! ok ) {
		validateFailures++;
		console.error(
			`[generate] FAIL ${ op.operationId } (${ op.method } ${ op.path })`
		);
		for ( const err of validate.errors || [] ) {
			console.error(
				`  ${ err.instancePath || '/' } ${
					err.message
				} (${ JSON.stringify( err.params ) })`
			);
		}
	}
}

if ( validateFailures > 0 ) {
	console.error(
		`[generate] ${ validateFailures } endpoint(s) failed schema validation.`
	);
	console.error(
		`[generate] Either fix the example in the spec, or override it in bin/example-overrides.json.`
	);
	process.exit( 1 );
}

console.log(
	`[generate] schema validation: every example body validates against its spec schema`
);
console.log(
	`[generate] done. ${ operations.length } total, ${ heroSet.size } hero, ${ generated.length } generated, ${ heroClassCount } absorbed-hero classes.`
);
