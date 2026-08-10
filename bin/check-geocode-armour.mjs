#!/usr/bin/env node
/**
 * City-search dropdown armour guard.
 *
 * The dropdown floats over the fields below it. If a theme flattens its
 * background, border or padding it does not merely look different: the panel
 * becomes an invisible box, and the city names and the field labels underneath
 * render on top of each other. This is not theoretical: it happens on any theme
 * carrying the ordinary CSS reset that classic themes ship.
 *
 * Two things were true at once and only the first was obvious. The panel kept
 * `position` and `z-index`, so paint order was never wrong and the panel really
 * was the topmost element. It lost `background`, `border`, `padding` and
 * `margin` to a bare `div` selector at 0-0-1, because every rule in
 * frontend.css sits inside `:where()` at 0-0-0. Being on top is worthless when
 * there is nothing to paint.
 *
 * This runs in two layers, because they fail for different reasons:
 *
 *   1. Source layer (always runs, no dependencies). Asserts the integrity
 *      declarations are still weighted to survive a theme. Cheap, deterministic,
 *      and it is the layer that states the rule a future edit must not undo.
 *   2. Cascade layer (skipped when no browser binary is available). Renders the
 *      real stylesheet against a deliberately hostile theme and measures what a
 *      visitor would actually see. This is the only layer that can fail for the
 *      real reason, so it is the one that was verified against the bug: with the
 *      fix reverted it fails on background, border and padding at both widths.
 *
 * Skipping layer 2 mirrors check-component-map.mjs skipping on a network
 * failure: a missing browser should not block a merge, a real regression should.
 */
import { readFileSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(
	path.dirname( fileURLToPath( import.meta.url ) ),
	'..'
);

const read = ( rel ) => readFileSync( path.join( root, rel ), 'utf8' );

const css = read( 'assets/css/frontend.css' );
const comboJs = read( 'assets/js/geocode-combobox.js' );
const formPhp = read( 'src/Support/FormRenderer.php' );

const failures = [];
const fail = ( msg ) => failures.push( msg );

/**
 * Print every collected failure and exit. Never returns.
 *
 * @param {string[]} notes Lines to print on success.
 */
function report( notes = [] ) {
	if ( failures.length > 0 ) {
		console.error( 'Geocode dropdown armour check FAILED:\n' );
		for ( const f of failures ) {
			console.error( `  - ${ f }` );
		}
		console.error(
			'\nThe dropdown floats over the fields below it. When it loses these declarations the city names and the labels underneath render on top of each other.'
		);
		process.exit( 1 );
	}
	for ( const note of notes ) {
		console.log( note );
	}
	process.exit( 0 );
}

/* -------------------------------------------------------------------------
 * Drift guard. The fixture below hand-writes the markup that FormRenderer.php
 * emits and that geocode-combobox.js builds at runtime. Renaming a class in
 * either source without updating the fixture would leave this whole check
 * measuring an element the plugin no longer ships, and it would still pass.
 * ---------------------------------------------------------------------- */
const CONTRACT = [
	[ 'roxyapi-form-geocode', formPhp, 'src/Support/FormRenderer.php' ],
	[ 'roxyapi-geocode-listbox', comboJs, 'assets/js/geocode-combobox.js' ],
	[ 'roxyapi-geocode-option', comboJs, 'assets/js/geocode-combobox.js' ],
];
for ( const [ cls, source, where ] of CONTRACT ) {
	if ( ! source.includes( cls ) ) {
		fail(
			`.${ cls } is no longer emitted by ${ where }. Update this guard's fixture to the current markup, or it is measuring an element that does not ship.`
		);
	}
	if ( ! css.includes( `.${ cls }` ) ) {
		fail( `.${ cls } has no rule in assets/css/frontend.css.` );
	}
}

// The panel's containing block is the geocode <p>, and that only holds because
// the JS inserts it with insertBefore into the input's parent. An HTML parser
// will not put a div inside a p, so the fixture below has to build it the same
// way. If the JS ever appends it somewhere else, `top: 100%` resolves against a
// different box and this fixture stops representing the shipped page.
if ( ! /input\.parentNode\.insertBefore\(\s*listbox/.test( comboJs ) ) {
	fail(
		'geocode-combobox.js no longer inserts the listbox into the input parent. This guard builds its fixture that way; re-check the fixture against the new placement.'
	);
}

/* -------------------------------------------------------------------------
 * Layer 1: weighting.
 *
 * These are the declarations whose loss makes the dropdown unusable rather
 * than merely restyled. Each must be outside :where() (which is 0-0-0 and
 * loses to a bare element selector) AND carry !important (because 0-1-0 and
 * 0-2-0 were both measured losing to an ID-scoped element rule, so specificity
 * alone cannot win this). Decoration is deliberately absent from this list:
 * border-radius, box-shadow, font-size and cursor stay overridable.
 * ---------------------------------------------------------------------- */
const INTEGRITY = {
	'.roxyapi-geocode-listbox': [
		'position',
		'z-index',
		'top',
		'display',
		'margin',
		'padding',
		'background',
		'color',
		'border',
		'overflow-y',
		'max-block-size',
	],
	'.roxyapi-geocode-option': [ 'padding' ],
	'.roxyapi-form-geocode': [ 'position', 'grid-column' ],
};

/**
 * Return the body of the first rule whose selector list contains exactly
 * `selector` as a whole compound, ignoring rules that wrap it in :where().
 *
 * @param {string} selector Selector to find, e.g. `.roxyapi-geocode-option`.
 * @return {string|null} Declaration block, or null when only :where() forms exist.
 */
function armouredBlock( selector ) {
	const escaped = selector.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );
	// Selector list entries that are exactly this compound, optionally with a
	// pseudo-class or attribute suffix, and NOT inside :where(...).
	const re = new RegExp(
		`(^|[},])\\s*([^{}]*?${ escaped }[^{}]*?)\\{([^}]*)\\}`,
		'g'
	);
	let match;
	while ( ( match = re.exec( css ) ) !== null ) {
		const selectorList = match[ 2 ];
		const body = match[ 3 ];
		if ( ! selectorList.includes( selector ) ) {
			continue;
		}
		// Ignore the decoration rules, which are :where()-wrapped on purpose.
		const usesWhere = selectorList
			.split( ',' )
			.filter( ( s ) => s.includes( selector ) )
			.every( ( s ) => s.includes( ':where(' ) );
		if ( usesWhere ) {
			continue;
		}
		// Skip attribute-qualified variants (`[hidden]`) when looking for base.
		if ( /\[[^\]]+\]|:hover/.test( selectorList ) ) {
			continue;
		}
		return body;
	}
	return null;
}

for ( const [ selector, props ] of Object.entries( INTEGRITY ) ) {
	const body = armouredBlock( selector );
	if ( body === null ) {
		fail(
			`${ selector } has no rule outside :where() in assets/css/frontend.css. At 0-0-0 every declaration on it loses to a theme's bare element reset.`
		);
		continue;
	}
	for ( const prop of props ) {
		const decl = new RegExp( `(^|;|\\s)${ prop }\\s*:([^;]*)`, 'i' ).exec(
			body
		);
		if ( ! decl ) {
			fail( `${ selector } no longer declares \`${ prop }\`.` );
			continue;
		}
		if ( ! /!important/.test( decl[ 2 ] ) ) {
			fail(
				`${ selector } declares \`${ prop }\` without !important. Specificity cannot win this: an ID-scoped element rule outranks any class selector we can write.`
			);
		}
	}
}

// The hidden state is its own rule and must outrank the base `display`, or a
// theme that sets display on a bare div leaves an empty panel permanently open.
if (
	! /\.roxyapi-geocode-listbox\[hidden\]\s*\{[^}]*display\s*:\s*none\s*!important/.test(
		css
	)
) {
	fail(
		'.roxyapi-geocode-listbox[hidden] must set `display: none !important`, or the closed dropdown stays visible under a theme that styles bare divs.'
	);
}

/* -------------------------------------------------------------------------
 * Layer 2: real cascade, real geometry, hostile theme.
 *
 * Deliberately NOT gated on layer 1 passing. Both layers report together, so a
 * run against a regressed stylesheet shows the weighting that slipped AND what
 * a visitor would have seen, rather than stopping at the first complaint.
 * ---------------------------------------------------------------------- */
let chromium;
try {
	( { chromium } = await import( 'playwright' ) );
} catch {
	console.warn(
		'playwright is not resolvable; skipping the rendered cascade check.'
	);
	report();
}

// The adversary. Layer one is the CSS reset that classic themes ship, at 0-0-1.
// Layer two is an ID-scoped element rule at 1-0-1, the shape that was measured
// beating both 0-1-0 and 0-2-0. A fix that relies on specificity fails here.
const HOSTILE_THEME = `
	html, body, div, span, p, ul, li, label, fieldset, legend, form, input {
		margin: 0; padding: 0; border: 0; font-size: 100%;
		vertical-align: baseline; background: transparent;
	}
	#left-area div, #left-area ul, #left-area p, #left-area span {
		background: transparent; border: 0; padding: 0; margin: 0;
		list-style: disc;
	}
	body { background: #ffffff; color: #111111; font-family: sans-serif; }
	/* Canary: anything painted from the fields below the panel is pure red, so
	   "the panel occludes what is under it" is measurable rather than a matter
	   of opinion. */
	#left-area .roxyapi-form-field:not(.roxyapi-form-geocode) label { color: #ff0000; }
`;

const CITIES = [
	'Adrogue, Buenos Aires, Argentina',
	'Santiago, Santiago Metropolitan, Chile',
	'Santo Domingo, Nacional, Dominican Republic',
	'San Jose, California, United States',
	'Santa Cruz de la Sierra, Santa Cruz, Bolivia',
	'Santander, Cantabria, Spain',
	'San Salvador, San Salvador, El Salvador',
	'Santa Fe, Santa Fe, Argentina',
];

const field = ( id, label, type ) =>
	`<p class="roxyapi-form-field"><label for="${ id }">${ label } <span class="roxyapi-form-required">*</span></label><input type="${ type }" id="${ id }"><span class="roxyapi-form-help">Help text for ${ label }</span></p>`;

// The geocode <p> carries exactly what FormRenderer::render_city_search emits:
// label, input, help. The listbox and the options are built afterwards with the
// DOM API, because that is how geocode-combobox.js does it and because an HTML
// parser refuses to nest a div inside a p (it would silently close the p, put
// the panel outside its own containing block, and make every geometry
// assertion below meaningless while still reporting green).
const fixture = `<!doctype html><html lang="es"><head><meta charset="utf-8">
<style>${ HOSTILE_THEME }</style>
<style>${ css }</style>
</head><body><div id="page-container"><div id="left-area">
<div class="roxyapi-form-wrap"><form class="roxyapi-form">
<fieldset class="roxyapi-form-section" id="sec-birth">
<legend>Datos de nacimiento</legend>
<p class="roxyapi-form-field roxyapi-form-geocode">
	<label for="geo">Buscar por ciudad</label>
	<input type="text" id="geo" class="roxyapi-form-geocode-input" autocomplete="off" value="San">
	<span class="roxyapi-form-help">Al elegir una ciudad se completan la latitud, la longitud y la zona horaria.</span>
</p>
${ field( 'f-date', 'Fecha de nacimiento', 'date' ) }
${ field( 'f-time', 'Hora de nacimiento', 'time' ) }
${ field( 'f-lat', 'Latitud', 'number' ) }
${ field( 'f-lon', 'Longitud', 'number' ) }
${ field( 'f-tz', 'Zona horaria', 'text' ) }
</fieldset></form></div></div></div>
<script>
	( function () {
		const cities = ${ JSON.stringify( CITIES ) };
		const input = document.getElementById( 'geo' );
		const build = function ( id ) {
			const listbox = document.createElement( 'div' );
			listbox.id = id;
			listbox.className = 'roxyapi-geocode-listbox';
			listbox.setAttribute( 'role', 'listbox' );
			input.parentNode.insertBefore( listbox, input.nextSibling );
			return listbox;
		};
		const open = build( 'lb' );
		cities.forEach( function ( label, index ) {
			const option = document.createElement( 'div' );
			option.id = 'lb-opt-' + index;
			option.setAttribute( 'role', 'option' );
			option.className = 'roxyapi-geocode-option';
			option.textContent = label;
			if ( index === 0 ) {
				option.setAttribute( 'aria-selected', 'true' );
			}
			open.appendChild( option );
		} );
		build( 'closed-probe' ).setAttribute( 'hidden', '' );
	} )();
</script>
</body></html>`;

// Serialized into the page by page.evaluate, so it runs with browser globals
// rather than the Node ones the rest of this file uses.
/* global getComputedStyle */
const measure = () => {
	const panel = document.getElementById( 'lb' );
	const option = panel.querySelector( '[role="option"]' );
	const cs = getComputedStyle( panel );
	const os = getComputedStyle( option );
	const pr = panel.getBoundingClientRect();

	const alpha = ( value ) => {
		const m = /rgba?\(([^)]+)\)/.exec( value );
		if ( ! m ) {
			return 1;
		}
		const parts = m[ 1 ].split( /[,/]/ ).map( ( p ) => parseFloat( p ) );
		return parts.length > 3 ? parts[ 3 ] : 1;
	};

	// Sample the band where the panel overlaps the labels underneath it and
	// record whether the panel is what a visitor would see at each point.
	const labels = [
		...document.querySelectorAll(
			'.roxyapi-form-field:not(.roxyapi-form-geocode) label'
		),
	];
	const leaks = [];
	for ( const label of labels ) {
		const lr = label.getBoundingClientRect();
		const top = Math.max( lr.top, pr.top );
		const bottom = Math.min( lr.bottom, pr.bottom );
		if ( bottom <= top ) {
			continue;
		}
		for ( let f = 0.15; f <= 0.86; f += 0.35 ) {
			const x = Math.max( lr.left, pr.left ) + 8;
			const y = top + ( bottom - top ) * f;
			const hit = document.elementFromPoint( x, y );
			if ( ! hit || ! ( panel === hit || panel.contains( hit ) ) ) {
				leaks.push( {
					label: label.textContent.trim().slice( 0, 26 ),
					x: Math.round( x ),
					y: Math.round( y ),
					hit: hit
						? `${ hit.tagName.toLowerCase() }.${
								hit.className
						  }`.slice( 0, 50 )
						: 'none',
				} );
			}
		}
	}

	return {
		position: cs.position,
		zIndex: cs.zIndex,
		backgroundAlpha: alpha( cs.backgroundColor ),
		backgroundColor: cs.backgroundColor,
		borderTopWidth: parseFloat( cs.borderTopWidth ),
		paddingTop: parseFloat( cs.paddingTop ),
		marginTop: parseFloat( cs.marginTop ),
		optionPaddingTop: parseFloat( os.paddingTop ),
		optionHeight: +option.getBoundingClientRect().height.toFixed( 1 ),
		panelHeight: +pr.height.toFixed( 1 ),
		overlappedLabels: labels.filter( ( l ) => {
			const lr = l.getBoundingClientRect();
			return lr.top < pr.bottom && lr.bottom > pr.top;
		} ).length,
		leaks,
		closedDisplay: getComputedStyle(
			document.getElementById( 'closed-probe' )
		).display,
	};
};

// Both widths. A desktop-only measurement would have missed that the rows fall
// under the minimum target size, which is what makes this worse on a phone.
const VIEWPORTS = [
	{ tag: 'desktop', width: 1280, height: 900 },
	{ tag: 'mobile', width: 390, height: 844 },
];

let browser;
try {
	browser = await chromium.launch();
} catch ( err ) {
	console.warn(
		`Could not launch chromium (${
			err.message.split( '\n' )[ 0 ]
		}). Skipping the rendered cascade check; run \`npx playwright install chromium\` to enable it.`
	);
	report();
}

const rendered = [];
for ( const vp of VIEWPORTS ) {
	const context = await browser.newContext( {
		viewport: { width: vp.width, height: vp.height },
	} );
	const page = await context.newPage();
	await page.setContent( fixture, { waitUntil: 'load' } );
	const m = await page.evaluate( measure );
	await context.close();
	rendered.push( [ vp, m ] );

	const at = `${ vp.tag } ${ vp.width }px`;
	if ( m.position !== 'absolute' ) {
		fail(
			`[${ at }] panel position is \`${ m.position }\`, expected absolute.`
		);
	}
	if ( m.zIndex === 'auto' ) {
		fail(
			`[${ at }] panel z-index is auto, so later positioned content can paint over it.`
		);
	}
	if ( m.backgroundAlpha !== 1 ) {
		fail(
			`[${ at }] panel background is ${ m.backgroundColor } (alpha ${ m.backgroundAlpha }). A transparent panel does not hide the ${ m.overlappedLabels } field label(s) it covers, which is the reported bug.`
		);
	}
	if ( ! ( m.borderTopWidth > 0 ) ) {
		fail(
			`[${ at }] panel has no border, so it has no visible edge against the page.`
		);
	}
	if ( ! ( m.paddingTop > 0 ) ) {
		fail( `[${ at }] panel padding collapsed to ${ m.paddingTop }px.` );
	}
	if ( ! ( m.optionPaddingTop > 0 ) ) {
		fail(
			`[${ at }] option padding collapsed to ${ m.optionPaddingTop }px.`
		);
	}
	// WCAG 2.5.8 Target Size (Minimum) is 24x24 CSS px. Unpadded rows measured
	// 23.8px with no separation, which is why this is worse on a touch screen.
	if ( m.optionHeight < 24 ) {
		fail(
			`[${ at }] option row is ${ m.optionHeight }px tall, under the 24px minimum target size.`
		);
	}
	if ( m.closedDisplay !== 'none' ) {
		fail(
			`[${ at }] a listbox with [hidden] computes display:${ m.closedDisplay }, so the closed dropdown stays on screen.`
		);
	}
	if ( m.leaks.length > 0 ) {
		fail(
			`[${ at }] ${
				m.leaks.length
			} sample point(s) inside the panel resolve to content behind it, e.g. ${ JSON.stringify(
				m.leaks[ 0 ]
			) }. The panel is not occluding what it covers.`
		);
	}
}
await browser.close();

report(
	rendered.map(
		( [ vp, m ] ) =>
			`geocode dropdown OK at ${ vp.width }px: panel ${ m.panelHeight }px, background ${ m.backgroundColor }, border ${ m.borderTopWidth }px, rows ${ m.optionHeight }px, covering ${ m.overlappedLabels } field label(s) with no leaks`
	)
);
