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

// Use BuilderPage's actual English strings and the same localization keys.
const php = read( 'src/Admin/BuilderPage.php' );
assert.ok(
	php.includes( "'RoxyAPIBuilder'" ),
	'BuilderPage localization global changed'
);
const i18nSource = php.slice( php.indexOf( "'i18n'" ) );
const i18n = Object.fromEntries(
	[
		...i18nSource.matchAll(
			/'([A-Za-z]+)'\s*=> __\( '([^']*)', 'roxyapi' \)/g
		),
	].map( ( match ) => [ match[ 1 ], match[ 2 ] ] )
);
assert.ok(
	i18n.continue && i18n.finish,
	'BuilderPage localized strings not found'
);
const groups = [
	{
		label: 'Most loved',
		tags: manifest.entries
			.filter( ( entry ) => entry.hero )
			.map( ( entry ) => entry.tag ),
	},
	...Object.entries( manifest.domains ).map( ( [ domain, info ] ) => ( {
		label: info.label,
		tags: manifest.entries
			.filter( ( entry ) => ! entry.hero && entry.domain === domain )
			.map( ( entry ) => entry.tag ),
	} ) ),
];
const settings = {
	manifest,
	groups,
	newPage: 'https://example.test/wp-admin/post-new.php?post_type=page',
	reading: '',
	timezones: [ 'UTC', 'America/New_York', 'Asia/Kolkata' ],
	today: '2026-10-15',
	i18n,
};
const css = read( 'assets/css/builder.css' );
const js = read( 'assets/js/builder.js' );
const fixture = `<!doctype html><html lang="en"><head><meta charset="utf-8"><style>${ css }</style></head><body><div class="wrap roxyapi-builder"><h1>Create a reading</h1><div id="roxyapi-builder"></div></div></body></html>`;
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
			await page.addScriptTag( { content: js } );
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
