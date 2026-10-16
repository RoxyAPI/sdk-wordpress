#!/usr/bin/env node
/**
 * Exercise the bundled shortcode builder without WordPress or API requests.
 * Compare metadata with shortcode contracts before checking the rendered UI,
 * so removing a manifest field cannot also remove the check's expectation.
 * An optional manifest path supports negative tests in /tmp.
 */
import assert from 'node:assert/strict';
import { readFileSync, readdirSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { chromium } from 'playwright';

const root = path.resolve(
	path.dirname( fileURLToPath( import.meta.url ) ),
	'..'
);
const read = ( rel ) => readFileSync( path.join( root, rel ), 'utf8' );
const manifest = JSON.parse(
	process.argv[ 2 ]
		? readFileSync( process.argv[ 2 ], 'utf8' )
		: read( 'builder/manifest.json' )
);
const entries = new Map(
	manifest.entries.map( ( entry ) => [ entry.tag, entry ] )
);
assert.equal(
	entries.size,
	manifest.entries.length,
	'Duplicate reading tags in manifest'
);
// Language belongs to site/Branding settings; mode belongs to builder step 2.
const reserved = new Set( [
	'lang',
	'mode',
	'hide_readings',
	'hide_sections',
] );
for ( const entry of manifest.entries ) {
	for ( const field of entry.attributes ) {
		assert.ok(
			! reserved.has( field.att ),
			`${ entry.tag }: ${ field.att } is not a reading input`
		);
	}
}
assert.ok(
	! ( 'reservedAttributes' in manifest ),
	'Display metadata belongs to the builder UI'
);

// The library and builder share the domain registry's utility exclusion.
const domains = JSON.parse( read( 'bin/domains.json' ) );
const spec = JSON.parse( read( 'specs/openapi.json' ) );
const heroes = JSON.parse( read( 'bin/hero-config.json' ) );
const heroOperations = new Set(
	JSON.parse( read( 'bin/hero-endpoints.json' ) )
);
const expectedTags = new Set(
	Object.entries( heroes )
		.filter( ( [ , hero ] ) => domains[ hero.domain ]?.library !== false )
		.map( ( [ suffix, hero ] ) => hero.tag || `roxy_${ suffix }` )
);
const excludedTags = new Set();
for ( const operations of Object.values( spec.paths ) ) {
	for ( const operation of Object.values( operations ) ) {
		if ( ! operation.operationId ) {
			continue;
		}
		const tag =
			'roxy_' +
			operation.operationId
				.replace( /([a-z])([A-Z])/g, '$1_$2' )
				.replace( /[^a-zA-Z0-9]/g, '_' )
				.toLowerCase();
		if ( domains[ operation.tags?.[ 0 ] ]?.library === false ) {
			excludedTags.add( tag );
		} else if ( ! heroOperations.has( operation.operationId ) ) {
			expectedTags.add( tag );
		}
	}
}
assert.deepEqual(
	[ ...entries.keys() ].sort(),
	[ ...expectedTags ].sort(),
	'Builder must offer the complete canonical reading tag set'
);
for ( const tag of excludedTags ) {
	assert.ok(
		! entries.has( tag ),
		`${ tag }: utility endpoint must stay out of the builder`
	);
}

// The renderer contracts are independent of the builder's generated metadata.
for ( const [ suffix, hero ] of Object.entries( heroes ) ) {
	const tag = hero.tag || `roxy_${ suffix }`;
	const entry = entries.get( tag );
	assert.ok( entry, `${ tag }: hero missing from manifest` );
	for ( const [ att, info ] of Object.entries( hero.attributes || {} ) ) {
		if ( reserved.has( att ) ) {
			continue;
		}
		const field = entry.attributes.find(
			( attribute ) => attribute.att === att
		);
		assert.ok(
			field,
			`${ tag }: attribute ${ att } missing from manifest`
		);
		assert.equal(
			field.required,
			!! info.required,
			`${ tag }: attribute ${ att } required flag drifted`
		);
	}
}
for ( const file of readdirSync(
	path.join( root, 'src/Generated/Shortcodes' )
) ) {
	if ( ! file.endsWith( '.php' ) ) {
		continue;
	}
	const source = read( `src/Generated/Shortcodes/${ file }` );
	const tag = source.match( /public const TAG = '([^']+)'/ )?.[ 1 ];
	assert.ok( tag, `${ file }: shortcode TAG contract not found` );
	// These account/language utilities stay registered for existing placements;
	// the reading library excludes their domains via library: false.
	if ( excludedTags.has( tag ) ) {
		continue;
	}
	const entry = entries.get( tag );
	assert.ok( entry, `${ tag }: shortcode missing from manifest` );
	if ( entry.hero || entry.formOnly ) {
		continue;
	}
	const defaults = source.match(
		/public const DEFAULTS = array\(([\s\S]*?)\n\s*\);/
	);
	assert.ok( defaults, `${ tag }: shortcode DEFAULTS contract not found` );
	for ( const match of defaults[ 1 ].matchAll( /^\s*'([^']+)'\s*=>/gm ) ) {
		const att = match[ 1 ];
		if ( ! reserved.has( att ) ) {
			assert.ok(
				entry.attributes.some( ( field ) => field.att === att ),
				`${ tag }: attribute ${ att } missing from manifest`
			);
		}
	}
}

// Use the standalone host's English shim and the builder's actual translations.
const html = read( 'builder/index.html' );
const shim = html.match(
	/<script id="roxyapi-builder-i18n">([\s\S]*?)<\/script>/
)[ 1 ];
const settings = {
	manifest,
	newPage: 'https://example.test/wp-admin/post-new.php?post_type=page',
	reading: '',
	timezones: [ 'UTC', 'America/New_York', 'Asia/Kolkata' ],
	today: '2026-10-15',
};
const adminCss = read( 'assets/css/admin.css' );
const builderCss = read( 'assets/css/builder.css' );
const css = adminCss + builderCss;
const js = read( 'assets/js/builder.js' );
const fixture = `<!doctype html><html lang="en"><head><meta charset="utf-8"><style>${ css }</style></head><body><div class="wrap roxyapi-builder"><h1 class="roxyapi-heading" data-roxyapi-builder-title></h1><div id="roxyapi-builder"></div></div></body></html>`;
let i18n;
const cases = [
	{ tag: 'roxy_synastry', mode: 'visitor', expected: '[roxy_synastry]' },
	{
		tag: 'roxy_natal_chart',
		mode: 'set',
		values: {
			birth_date: '1990-05-15',
			birth_time: '14:30',
			lat: '40.7128',
			lon: '-74.006',
			tz: 'UTC',
		},
		expected:
			'[roxy_natal_chart birth_date="1990-05-15" birth_time="14:30" lat="40.7128" lon="-74.006" tz="UTC"]',
	},
	{ tag: 'roxy_moon_phase', mode: 'none', expected: '[roxy_moon_phase]' },
];

// Missing Playwright or Chromium is a failed gate, never a successful skip.
const browser = await chromium.launch( { headless: true } );
try {
	for ( const width of [ 1280, 390 ] ) {
		const page = await browser.newPage( {
			viewport: { width, height: 900 },
		} );
		page.setDefaultTimeout( 5000 );
		const errors = [];
		page.on( 'pageerror', ( error ) => errors.push( error.message ) );
		page.on( 'console', ( message ) => {
			if ( message.type() === 'error' ) {
				errors.push( message.text() );
			}
		} );
		try {
			await page.setContent( fixture );
			await page.evaluate( ( localized ) => {
				window.RoxyAPIBuilder = localized;
			}, settings );
			await page.addScriptTag( { content: shim } );
			await page.addScriptTag( { content: js } );
			i18n = await page.evaluate( () => ( {
				back: document.querySelector(
					'.roxyapi-builder-navigation button'
				).textContent,
				continue: document.querySelector(
					'.roxyapi-builder-navigation button:last-child'
				).textContent,
			} ) );
			const groupedTags = await page
				.locator( 'input[name="roxyapi-builder-reading"]' )
				.evaluateAll( ( nodes ) =>
					nodes.map( ( node ) => node.value )
				);
			const orderedEntries = manifest.entries
				.slice()
				.sort( ( a, b ) => a.order - b.order );
			assert.deepEqual(
				groupedTags,
				[
					...orderedEntries
						.filter( ( entry ) => entry.hero )
						.map( ( entry ) => entry.tag ),
					...Object.keys( manifest.domains ).flatMap( ( domain ) =>
						orderedEntries
							.filter(
								( entry ) =>
									! entry.hero && entry.domain === domain
							)
							.map( ( entry ) => entry.tag )
					),
				],
				'Groups must contain each hero once, then the remaining readings in domain order'
			);
			await checkLayout( page, `${ width }px step 1` );
			await checkPicker( page, width );
			const failures = await page.evaluate( () => {
				const issues = [];
				const data = window.RoxyAPIBuilder;
				for ( const entry of data.manifest.entries ) {
					const choices = [
						...document.querySelectorAll(
							'input[name="roxyapi-builder-reading"]'
						),
					].filter( ( input ) => input.value === entry.tag );
					if ( choices.length === 1 ) {
						choices[ 0 ].closest( 'details' ).open = true;
					}
					if (
						choices.length !== 1 ||
						! choices[ 0 ].getClientRects().length
					) {
						issues.push(
							`${ entry.tag }: expected one visible choice in step 1, got ${ choices.length }`
						);
						continue;
					}
					choices[ 0 ].click();
					const panel = document
						.querySelector( '#roxyapi-builder-step-details' )
						.parentElement.querySelector(
							'div[id^="roxyapi-builder-reading-"]:not([hidden])'
						);
					for ( const field of entry.attributes ) {
						const controls = [
							...panel.querySelectorAll( '[name]' ),
						].filter( ( input ) => input.name === field.att );
						if (
							controls.length !== 1 ||
							controls[ 0 ].required !== field.required
						) {
							issues.push(
								`${ entry.tag }: attribute ${ field.att } needs one control with required=${ field.required }, got ${ controls.length }`
							);
						}
					}
				}
				return issues;
			} );
			assert.deepEqual(
				failures,
				[],
				`${ width }px: reading/attribute coverage failed:\n${ failures.join(
					'\n'
				) }`
			);
			for ( const test of cases ) {
				const at = `${ width }px ${ test.tag } (${ test.mode })`;
				assert.ok(
					entries.get( test.tag )?.modes.includes( test.mode ),
					`${ at }: mode missing from manifest`
				);
				const choice = page.locator(
					`input[name="roxyapi-builder-reading"][value="${ test.tag }"]`
				);
				await choice.evaluate( ( node ) => {
					node.closest( 'details' ).open = true;
				} );
				await choice.check();
				await advance( page, 'who', at );
				if ( test.mode === 'set' ) {
					await page
						.locator(
							'input[name="roxyapi-builder-mode"][value="set"]'
						)
						.check();
					await advance( page, 'details', at );
					const panel = page.locator(
						'.roxyapi-builder-step:not([hidden]) > div[id^="roxyapi-builder-reading-"]:not([hidden])'
					);
					for ( const field of entries
						.get( test.tag )
						.attributes.filter(
							( attribute ) => attribute.required
						) ) {
						assert.equal(
							await panel
								.locator( `[name="${ field.att }"]` )
								.count(),
							1,
							`${ at }: required attribute ${ field.att } has no control`
						);
					}
					await panel.locator( 'details' ).evaluateAll( ( nodes ) =>
						nodes.forEach( ( node ) => {
							node.open = true;
						} )
					);
					for ( const [ att, value ] of Object.entries(
						test.values
					) ) {
						const control = panel.locator( `[name="${ att }"]` );
						assert.equal(
							await control.count(),
							1,
							`${ at }: attribute ${ att } has no control`
						);
						if (
							await control.evaluate(
								( node ) => node.tagName === 'SELECT'
							)
						) {
							await control.selectOption( value );
						} else {
							await control.fill( value );
						}
					}
					// The picker shows the browser's date order, so the
					// builder restates the value in words.
					assert.deepEqual(
						(
							await panel
								.locator( '[id$="-said"]' )
								.allTextContents()
						).map( ( text ) => text.replace( /\s/g, ' ' ) ),
						[ 'May 15, 1990', '2:30 PM' ],
						`${ at }: date and time are not spelled out`
					);
					await checkLayout( page, `${ at } filled details` );
				}
				await advance( page, 'display', at );
				await advance( page, 'finish', at );
				assert.equal(
					await page.locator( 'textarea' ).inputValue(),
					test.expected,
					`${ at }: incorrect shortcode`
				);
				await page
					.getByRole( 'button', { name: i18n.back, exact: true } )
					.click();
				await page
					.getByRole( 'button', { name: i18n.back, exact: true } )
					.click();
				if ( test.mode === 'set' ) {
					await page
						.getByRole( 'button', { name: i18n.back, exact: true } )
						.click();
				}
				await page
					.getByRole( 'button', { name: i18n.back, exact: true } )
					.click();
				assert.equal( await choice.isChecked(), true );
				assert.equal(
					await choice.evaluate(
						( node ) => node.closest( 'details' ).open
					),
					true,
					`${ at }: Back must open the selected group`
				);
			}
			assert.deepEqual(
				errors,
				[],
				`${ width }px: builder console/page errors: ${ errors.join(
					'; '
				) }`
			);
			console.log(
				`Builder OK at ${ width }px: ${ entries.size } choices, every attribute control, three shortcodes, no overflow or console errors.`
			);
		} finally {
			await page.close();
		}
	}
	await checkAttributeQuoting( browser );
	await checkStandalone( browser );
} finally {
	await browser.close();
}

/**
 * Round-trip with the iframe so queued postMessages have arrived.
 *
 * @param frame Builder iframe.
 */
function settle( frame ) {
	return frame
		.locator( 'body' )
		.evaluate(
			() =>
				new Promise( ( resolve ) =>
					window.requestAnimationFrame( () =>
						window.requestAnimationFrame( resolve )
					)
				)
		);
}

/**
 * Visible elements of the builder painted with a light background.
 *
 * @param page Page showing the builder in dark mode.
 */
function lightSurfaces( page ) {
	return page.locator( 'main' ).evaluate( ( main ) =>
		[ main, ...main.querySelectorAll( '*' ) ]
			.filter( ( node ) => {
				const [ r, g, b, a = 1 ] = window
					.getComputedStyle( node )
					.backgroundColor.match( /[\d.]+/g )
					.map( Number );
				return node.checkVisibility() && a > 0 && r + g + b > 600;
			} )
			.map( ( node ) => `${ node.tagName }.${ node.className }` )
	);
}

/** @param browser Browser instance. */
async function checkAttributeQuoting( browser ) {
	const page = await browser.newPage();
	page.setDefaultTimeout( 5000 );
	try {
		await page.setContent( fixture );
		await page.evaluate(
			( localized ) => {
				window.RoxyAPIBuilder = localized;
			},
			{ ...settings, reading: 'roxy_tarot_yes_no' }
		);
		await page.addScriptTag( { content: shim } );
		await page.addScriptTag( { content: js } );
		await page
			.locator( 'input[name="roxyapi-builder-mode"][value="set"]' )
			.check();
		await advance( page, 'details', 'Quoting contract' );
		await page
			.locator( '.roxyapi-builder-step:not([hidden]) details' )
			.evaluateAll( ( nodes ) =>
				nodes.forEach( ( node ) => {
					node.open = true;
				} )
			);
		const control = page.locator( '[name="question"]' );
		for ( const example of JSON.parse(
			read( 'tests/fixtures/shortcode-attributes.json' )
		) ) {
			await control.fill( example.input );
			await advance( page, 'display', 'Quoting contract' );
			await advance( page, 'finish', 'Quoting contract' );
			assert.equal(
				await page.locator( 'textarea' ).inputValue(),
				`[roxy_tarot_yes_no question=${ example.expected }]`,
				example.input
			);
			await page
				.getByRole( 'button', { name: i18n.back, exact: true } )
				.click();
			await page
				.getByRole( 'button', { name: i18n.back, exact: true } )
				.click();
		}
		console.log(
			'Shared shortcode attribute quoting fixtures OK through the real builder.'
		);
	} finally {
		await page.close();
	}
}

/**
 * Check the compact accordion and the flat search without losing coverage.
 *
 * @param page  Browser page.
 * @param width Viewport width.
 */
async function checkPicker( page, width ) {
	const groups = page.locator( '.roxyapi-builder-group' );
	assert.equal(
		await groups.locator( 'summary' ).count(),
		await groups.count()
	);
	assert.equal(
		await page.locator( '.roxyapi-builder-group[open]' ).count(),
		0
	);
	assert.match(
		await groups.first().locator( 'summary' ).textContent(),
		/^Most loved \(\d+\)$/
	);
	assert.equal(
		await groups.first().evaluate( ( node ) => node.open ),
		false
	);
	const closedHeight = await page.evaluate(
		() => document.documentElement.scrollHeight
	);
	const viewport = page.viewportSize().height;
	assert.ok(
		// A phone stacks the 21 closed groups in one column, so it gets two
		// screens; the guard is against an expanded catalogue (44 screens).
		closedHeight < viewport * ( width === 1280 ? 1.5 : 2 ),
		`${ width }px: closed picker height ${ closedHeight } is too tall`
	);
	console.log(
		`Step 1 at ${ width }px, all closed: ${ closedHeight }px / ${ viewport }px viewport.`
	);
	await assertPickerGap( page, page.locator( '.roxyapi-builder-groups' ) );
	await groups.first().locator( 'summary' ).click();
	await checkExpandedGroup( page, width, groups.first() );
	const vedic = groups.filter( { hasText: /^Vedic Astrology/ } );
	await vedic.locator( 'summary' ).click();
	await checkExpandedGroup( page, width, vedic );
	for ( const index of [ 1, 2 ] ) {
		await groups.nth( index ).locator( 'summary' ).click();
		assert.equal(
			await page.locator( '.roxyapi-builder-group[open]' ).count(),
			1
		);
		assert.equal(
			await groups.nth( index ).evaluate( ( node ) => node.open ),
			true
		);
	}
	const summaries = await groups
		.locator( 'summary' )
		.evaluateAll( ( nodes ) =>
			nodes.every( ( node ) => node.getBoundingClientRect().height >= 44 )
		);
	assert.equal( summaries, true, 'Summaries must be at least 44px tall' );
	// Keyboard activation uses the native summary behavior.
	await groups.nth( 1 ).locator( 'summary' ).focus();
	await page.keyboard.press( 'Enter' );
	assert.equal(
		await groups.nth( 1 ).evaluate( ( node ) => node.open ),
		true
	);
	assert.equal(
		await page.locator( '.roxyapi-builder-group[open]' ).count(),
		1
	);
	const search = page.locator( '#roxyapi-builder-search' );
	const results = page.locator(
		'.roxyapi-builder-step:has(> #roxyapi-builder-step-pick) > .roxyapi-builder-grid'
	);
	for ( const query of [ 'chart', 'BIRTH', 'zzzz-no-reading' ] ) {
		const expected = await groups
			.locator( '[data-search]' )
			.evaluateAll(
				( nodes, text ) =>
					nodes
						.filter( ( node ) =>
							node.textContent
								.toLowerCase()
								.includes( text.toLowerCase() )
						)
						.map( ( node ) => node.querySelector( 'input' ).value ),
				query
			);
		await search.fill( query );
		assert.equal(
			await page.locator( '.roxyapi-builder-groups' ).isVisible(),
			false
		);
		await assertPickerGap( page, results );
		const matches = await results
			.locator( 'input' )
			.evaluateAll( ( nodes ) => nodes.map( ( node ) => node.value ) );
		assert.deepEqual( matches.sort(), expected.sort() );
		if ( query === 'chart' ) {
			assert.ok(
				new Set( matches.map( ( tag ) => entries.get( tag ).domain ) )
					.size > 1,
				'Search must include multiple domains'
			);
		}
		assert.equal(
			await page
				.locator(
					'.roxyapi-builder-step:has(> #roxyapi-builder-step-pick) > [role="status"]'
				)
				.isVisible(),
			! expected.length
		);
		await search.fill( '' );
		assert.equal(
			await groups.nth( 1 ).evaluate( ( node ) => node.open ),
			true,
			'Clearing search must preserve the open group'
		);
		assert.equal( await results.locator( 'input' ).count(), 0 );
	}
}

/**
 * Keep visible choices separated from the search input by one rem.
 *
 * @param page Browser page.
 * @param list Visible group or search result list.
 */
async function assertPickerGap( page, list ) {
	const input = await page.locator( '#roxyapi-builder-search' ).boundingBox();
	const bounds = await list.boundingBox();
	assert.ok(
		input && bounds && bounds.y - input.y - input.height >= 16,
		'Picker choices need a 1rem gap below search'
	);
}

/**
 * Bound the accordion chrome while allowing the open reading list to grow.
 *
 * @param page  Browser page.
 * @param width Viewport width.
 * @param group Open reading group.
 */
async function checkExpandedGroup( page, width, group ) {
	const sizes = await group.evaluate( ( node ) => {
		const grid = node.querySelector( '.roxyapi-builder-grid' );
		const bounds = grid.getBoundingClientRect();
		return {
			label: node.querySelector( 'summary' ).textContent,
			height: document.documentElement.scrollHeight,
			gridHeight: bounds.height,
			unclipped:
				grid.scrollHeight <= grid.clientHeight + 1 &&
				[ ...grid.children ].every( ( card ) => {
					const rect = card.getBoundingClientRect();
					return (
						rect.top >= bounds.top && rect.bottom <= bounds.bottom
					);
				} ),
			maxHeight: window.getComputedStyle( grid ).maxHeight,
		};
	} );
	assert.equal( sizes.maxHeight, 'none', 'Open groups must expand fully' );
	assert.equal(
		sizes.unclipped,
		true,
		`${ sizes.label }: all cards must fit without inner scrolling`
	);
	const viewport = page.viewportSize().height;
	assert.ok(
		sizes.height - sizes.gridHeight <
			viewport * ( width === 1280 ? 1.5 : 3 ),
		`${ width }px: accordion chrome exceeds the height limit excluding the fully expanded reading list`
	);
	console.log(
		`Step 1 at ${ width }px, ${ sizes.label } open: ${ sizes.height }px / ${ viewport }px viewport.`
	);
}

/**
 * Assert the current step fits the viewport.
 *
 * @param page Browser page.
 * @param at   Reading and step.
 */
async function checkLayout( page, at ) {
	const sizes = await page.evaluate( () => ( {
		viewport: document.documentElement.clientWidth,
		content: Math.max(
			document.documentElement.scrollWidth,
			document.body.scrollWidth
		),
	} ) );
	assert.ok(
		sizes.content <= sizes.viewport,
		`${ at }: horizontal scroll (${ sizes.content } > ${ sizes.viewport })`
	);
}

/**
 * Advance using the real navigation.
 *
 * @param page Browser page.
 * @param step Expected step.
 * @param at   Reading and width.
 */
async function advance( page, step, at ) {
	await page
		.getByRole( 'button', { name: i18n.continue, exact: true } )
		.click();
	assert.ok(
		await page.locator( `#roxyapi-builder-step-${ step }` ).isVisible(),
		`${ at }: did not reach ${ step }`
	);
	await checkLayout( page, `${ at } ${ step }` );
}

/**
 * Serve the published file layout entirely through browser interception. No
 * request reaches the public site, including the parent and geocode fixtures.
 *
 * @param browser Browser instance.
 */
async function checkStandalone( browser ) {
	const base = 'https://builder.example.test/sdk-wordpress/';
	const page = await browser.newPage( {
		viewport: { width: 390, height: 900 },
	} );
	page.setDefaultTimeout( 5000 );
	const errors = [];
	page.on( 'pageerror', ( error ) => errors.push( error.message ) );
	const files = new Map( [
		[ '/sdk-wordpress/', { contentType: 'text/html', body: html } ],
		[
			'/sdk-wordpress/manifest.json',
			{
				contentType: 'application/json',
				body: JSON.stringify( manifest ),
			},
		],
		[
			'/sdk-wordpress/assets/js/builder.js',
			{ contentType: 'application/javascript', body: js },
		],
		[
			'/sdk-wordpress/assets/js/geocode-combobox.js',
			{
				contentType: 'application/javascript',
				body: read( 'assets/js/geocode-combobox.js' ),
			},
		],
		[
			'/sdk-wordpress/assets/css/admin.css',
			{ contentType: 'text/css', body: adminCss },
		],
		[
			'/sdk-wordpress/assets/css/builder.css',
			{ contentType: 'text/css', body: builderCss },
		],
	] );
	let geocodeQueries = 0;
	await page.route( '**/*', ( route ) => {
		const url = new URL( route.request().url() );
		if (
			url.origin === 'https://builder.example.test' &&
			files.has( url.pathname )
		) {
			return route.fulfill( files.get( url.pathname ) );
		}
		if (
			url.origin === 'https://roxyapi.com' &&
			url.pathname === '/tools/location-search'
		) {
			assert.equal( url.searchParams.get( 'q' ), 'Mumbai' );
			geocodeQueries++;
			return route.fulfill( {
				json: {
					results: [
						{
							label: 'Mumbai, Maharashtra, India',
							latitude: 19.076,
							longitude: 72.8777,
							timezone: 'Asia/Kolkata',
						},
					],
				},
				headers: { 'Access-Control-Allow-Origin': '*' },
			} );
		}
		if ( url.pathname === '/builder-test' ) {
			return route.fulfill( {
				contentType: 'text/html',
				body: `<!doctype html><script>window.messages = []; window.addEventListener('message', event => window.messages.push(event.data));</script><iframe style="width:100%;border:0" src="${ base }?embed=1&reading=roxy_moon_phase"></iframe>`,
			} );
		}
		return route.fulfill( { status: 404, body: '' } );
	} );
	await page.addInitScript( () => {
		Object.defineProperty( window.navigator, 'clipboard', {
			value: {
				writeText: async ( value ) => {
					if ( window.blockClipboard ) {
						throw new Error( 'Clipboard permission denied' );
					}
					window.copiedShortcode = value;
				},
			},
		} );
	} );
	try {
		for ( const width of [ 1280, 390 ] ) {
			await page.setViewportSize( { width, height: 900 } );
			await page.goto( base );
			await page.locator( '.roxyapi-builder-group' ).first().waitFor();
			console.log( 'Standalone picker:' );
			await checkPicker( page, width );
		}
		const longTail = manifest.entries.find( ( entry ) => ! entry.hero );
		await page.goto( base + '?reading=' + longTail.tag );
		await page
			.locator( '#roxyapi-builder-step-who' )
			.waitFor( { state: 'visible' } );
		const preselected = page.locator(
			`input[name="roxyapi-builder-reading"][value="${ longTail.tag }"]`
		);
		assert.equal( await preselected.isChecked(), true );
		assert.equal(
			await preselected.evaluate(
				( node ) => node.closest( 'details' ).open
			),
			true
		);
		await page
			.getByRole( 'button', { name: i18n.back, exact: true } )
			.click();
		assert.equal( await preselected.isVisible(), true );
		assert.equal( await preselected.isChecked(), true );
		await page.goto( base + '?reading=roxy_natal_chart' );
		await page
			.locator( '#roxyapi-builder-step-who' )
			.waitFor( { state: 'visible' } );
		assert.equal( await page.locator( 'header' ).isVisible(), true );
		assert.equal( await page.locator( 'footer' ).isVisible(), true );
		assert.equal(
			await page.locator( 'a[href*="post-new.php"]' ).count(),
			0
		);
		assert.equal(
			await page
				.locator( 'meta[name="robots"]' )
				.getAttribute( 'content' ),
			'noindex'
		);
		assert.equal(
			await page
				.locator( 'link[rel="canonical"]' )
				.getAttribute( 'href' ),
			'https://roxyapi.com/docs/integrations/wordpress'
		);
		assert.equal(
			await page.evaluate( () =>
				window.wp.i18n.sprintf( '%2$s: %1$d %%', 3, 'Step' )
			),
			'Step: 3 %'
		);
		await page
			.locator( 'input[name="roxyapi-builder-mode"][value="set"]' )
			.check();
		await advance( page, 'details', 'Standalone' );
		const panel = page.locator(
			'.roxyapi-builder-step:not([hidden]) > div:not([hidden])'
		);
		await panel.locator( '[data-roxyapi-geocode]' ).fill( 'Mumbai' );
		await panel
			.getByRole( 'option', { name: 'Mumbai, Maharashtra, India' } )
			.click();
		assert.equal( geocodeQueries, 1 );
		assert.equal(
			await panel.locator( '[name="lat"]' ).inputValue(),
			'19.076'
		);
		assert.equal(
			await panel.locator( '[name="lon"]' ).inputValue(),
			'72.8777'
		);
		assert.equal(
			await panel.locator( '[name="tz"]' ).inputValue(),
			'Asia/Kolkata'
		);
		await checkLayout( page, 'Standalone city selection' );
		// Dark follows the OS until the embedding page passes a theme, and
		// every painted surface follows it, open city list included.
		await page.emulateMedia( { colorScheme: 'dark' } );
		await panel.locator( '[data-roxyapi-geocode]' ).fill( 'Mumbai' );
		await panel.getByRole( 'option' ).first().waitFor();
		assert.deepEqual( await lightSurfaces( page ), [] );
		await page.evaluate( () => {
			document.documentElement.dataset.theme = 'light';
		} );
		assert.equal(
			await page
				.locator( '.roxyapi-builder' )
				.evaluate( ( node ) => window.getComputedStyle( node ).color ),
			'rgb(29, 35, 39)'
		);
		await page.emulateMedia( { colorScheme: 'light' } );

		// Trust the exact parent origin, rejecting a matching hostname prefix.
		for ( const origin of [
			'https://roxyapi.com',
			'https://roxyapi.com.example.test',
		] ) {
			await page.goto( origin + '/builder-test' );
			const frame = page.frameLocator( 'iframe' );
			await frame
				.locator( '#roxyapi-builder-step-who' )
				.waitFor( { state: 'visible' } );
			assert.equal( await frame.locator( 'header' ).isVisible(), false );
			assert.equal( await frame.locator( 'footer' ).isVisible(), false );
			const before = await page.evaluate( () => window.messages.length );
			await frame
				.locator( '.roxyapi-builder-navigation button:last-child' )
				.click();
			await frame
				.locator( '.roxyapi-builder-navigation button:last-child' )
				.click();
			await frame
				.locator( '.roxyapi-builder-step:not([hidden]) button' )
				.click();
			await frame
				.getByRole( 'status' )
				.filter( { hasText: /./ } )
				.waitFor();
			assert.equal(
				await frame.locator( 'textarea' ).inputValue(),
				'[roxy_moon_phase]'
			);
			assert.equal(
				await frame
					.locator( 'body' )
					.evaluate( () => window.copiedShortcode ),
				'[roxy_moon_phase]'
			);
			await page.evaluate( () =>
				document
					.querySelector( 'iframe' )
					.contentWindow.postMessage(
						{ type: 'roxy-builder:theme', theme: 'dark' },
						'*'
					)
			);
			await settle( frame );
			assert.equal(
				await frame
					.locator( 'html' )
					.evaluate( ( node ) => node.dataset.theme ?? null ),
				origin === 'https://roxyapi.com' ? 'dark' : null,
				`${ origin }: only the trusted parent sets the theme`
			);
			if ( origin === 'https://roxyapi.com' ) {
				await page.waitForFunction( () =>
					window.messages.some(
						( message ) => message.type === 'roxy-builder:copied'
					)
				);
				const messages = await page.evaluate( () => window.messages );
				assert.ok(
					messages.some(
						( message ) =>
							message.type === 'roxy-builder:height' &&
							message.height > 0
					)
				);
				assert.deepEqual(
					messages.find(
						( message ) => message.type === 'roxy-builder:copied'
					),
					{ type: 'roxy-builder:copied', tag: 'roxy_moon_phase' }
				);
				const copies = messages.filter(
					( message ) => message.type === 'roxy-builder:copied'
				).length;
				await frame.locator( 'body' ).evaluate( () => {
					window.blockClipboard = true;
				} );
				await frame
					.locator( '.roxyapi-builder-step:not([hidden]) button' )
					.click();
				await page.waitForFunction(
					( count ) =>
						window.messages.filter(
							( message ) =>
								message.type === 'roxy-builder:copied'
						).length > count,
					copies
				);
			} else {
				await settle( frame );
				assert.equal( before, 0 );
				assert.deepEqual(
					await page.evaluate( () => window.messages ),
					[]
				);
			}
		}
		assert.deepEqual( errors, [] );
		console.log(
			'Standalone OK: published paths, preselection, city mapping, dark theme, trusted embed height/copy messages and rejected lookalike origin (all requests intercepted).'
		);
	} finally {
		await page.close();
	}
}
