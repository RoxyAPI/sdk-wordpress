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
const reserved = new Set( [ 'mode', 'hide_readings', 'hide_sections' ] );

// The renderer contracts are independent of the builder's generated metadata.
const heroes = JSON.parse( read( 'bin/hero-config.json' ) );
for ( const [ suffix, hero ] of Object.entries( heroes ) ) {
	const tag = hero.tag || `roxy_${ suffix }`;
	const entry = entries.get( tag );
	assert.ok( entry, `${ tag }: hero missing from manifest` );
	for ( const [ att, info ] of Object.entries( hero.attributes || {} ) ) {
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
const css = read( 'assets/css/builder.css' );
const js = read( 'assets/js/builder.js' );
const fixture = `<!doctype html><html lang="en"><head><meta charset="utf-8"><style>${ css }</style></head><body><div class="wrap roxyapi-builder"><h1 data-roxyapi-builder-title></h1><div id="roxyapi-builder"></div></div></body></html>`;
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
			const failures = await page.evaluate( () => {
				const issues = [];
				const data = window.RoxyAPIBuilder;
				const excluded = new Set( [
					'mode',
					...data.manifest.reservedAttributes.map(
						( field ) => field.att
					),
				] );
				for ( const entry of data.manifest.entries ) {
					const choices = [
						...document.querySelectorAll(
							'input[name="roxyapi-builder-reading"]'
						),
					].filter( ( input ) => input.value === entry.tag );
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
						if ( excluded.has( field.att ) ) {
							continue;
						}
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
				await page
					.locator(
						`input[name="roxyapi-builder-reading"][value="${ test.tag }"]`
					)
					.check();
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
	await checkStandalone( browser );
} finally {
	await browser.close();
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
			'/sdk-wordpress/assets/css/builder.css',
			{ contentType: 'text/css', body: css },
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
		await page.emulateMedia( { colorScheme: 'dark' } );
		assert.equal(
			await page
				.locator( '.roxyapi-builder' )
				.evaluate( ( node ) => window.getComputedStyle( node ).color ),
			'rgb(240, 242, 245)'
		);

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
				// Round-trip with the iframe to allow queued postMessages to arrive.
				await frame
					.locator( 'body' )
					.evaluate(
						() =>
							new Promise( ( resolve ) =>
								window.requestAnimationFrame( () =>
									window.requestAnimationFrame( resolve )
								)
							)
					);
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
