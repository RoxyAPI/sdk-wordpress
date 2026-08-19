#!/usr/bin/env node
/**
 * Readable-on-any-section guard.
 *
 * A reading is placed inside whatever container the site owner built, and a
 * section that paints a background without setting a text colour leaves
 * anything of ours that inherits one half drawing dark text on dark. Nothing
 * errors and no test fails; the reading is simply unreadable.
 *
 * The rule: every container emitted at the top level of a shortcode paints
 * BOTH halves, from the token layer the chart components paint from.
 *
 * Two layers, same split as the geocode guard. Layer 1 has no dependencies and
 * states the rule an edit must not undo. Layer 2 renders the real stylesheet
 * inside a hostile section, in both forced modes, and measures what a visitor
 * gets; it is skipped when no browser binary is available.
 *
 * The container set comes from `bin/painted-surfaces.json`, and the phpunit
 * sweep fails on any top-level container the plugin renders that the list does
 * not name, so new chrome cannot ship unmeasured.
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
const tokens = read( 'assets/css/roxy-ui-tokens.css' );

const failures = [];
const fail = ( msg ) => failures.push( msg );

/** Minimum contrast for body-sized text, WCAG 2.2 AA (1.4.3). */
const AA = 4.5;

const listed = JSON.parse( read( 'bin/painted-surfaces.json' ) );

/**
 * The containers that carry text at the top level of a reading, mapped to the
 * renderer that emits each one. Read from `bin/painted-surfaces.json` so this
 * check and `tests/phpunit/test-painted-surfaces.php` cannot hold different
 * ideas of the set: that test renders the plugin and fails on any top-level
 * container the list does not name, which is what stops new chrome shipping
 * unmeasured.
 *
 * @type {Record<string, string>}
 */
const SURFACES = listed.surfaces;

/** Top-level containers that carry no text of their own, so nothing to measure. */
const PASSTHROUGH = listed.passthrough;

for ( const [ cls, where ] of Object.entries( PASSTHROUGH ) ) {
	if ( ! read( where ).includes( cls ) ) {
		fail( `.${ cls } is no longer emitted by ${ where }.` );
	}
}

for ( const [ cls, where ] of Object.entries( SURFACES ) ) {
	if ( ! read( where ).includes( cls ) ) {
		fail(
			`.${ cls } is no longer emitted by ${ where }. Update this guard to the current markup, or it measures an element that does not ship.`
		);
	}
}

/* -------------------------------------------------------------------------
 * Layer 1: both halves declared.
 * ---------------------------------------------------------------------- */

/**
 * Every property declared by any rule whose selector list names this class.
 *
 * @param {string} cls Class name without the dot.
 * @return {Set<string>} Declared property names.
 */
function declaredProps( cls ) {
	const props = new Set();
	// Comments go first so a brace-free comment cannot be read as a selector.
	// An at-rule wrapper falls out on its own: its own `{` ends the selector run
	// before the rules nested inside it, which are matched normally.
	const re = /([^{}]+)\{([^{}]*)\}/g;
	let m;
	while (
		( m = re.exec( css.replace( /\/\*[\s\S]*?\*\//g, '' ) ) ) !== null
	) {
		if ( ! new RegExp( `\\.${ cls }(?![\\w-])` ).test( m[ 1 ] ) ) {
			continue;
		}
		if ( /:hover|:focus|\[hidden\]/.test( m[ 1 ] ) ) {
			continue;
		}
		for ( const decl of m[ 2 ].split( ';' ) ) {
			const name = decl.split( ':' )[ 0 ].trim();
			if ( name && ! name.startsWith( '--' ) ) {
				props.add( name );
			}
		}
	}
	return props;
}

for ( const cls of Object.keys( SURFACES ) ) {
	const props = declaredProps( cls );
	for ( const half of [ 'background', 'color' ] ) {
		if ( ! props.has( half ) ) {
			fail(
				`.${ cls } does not declare \`${ half }\`. A container that paints one half and inherits the other is readable only while the page happens to agree.`
			);
		}
	}
}

/* -------------------------------------------------------------------------
 * Layer 2: rendered contrast, inside a section that paints only a background.
 * ---------------------------------------------------------------------- */
let chromium;
try {
	( { chromium } = await import( 'playwright' ) );
} catch {
	console.warn(
		'playwright is not resolvable; skipping the rendered contrast check.'
	);
	report();
}

// The adversary: a section with its own dark background whose children keep the
// document text colour. This is what a page builder emits, and it is the shape
// that makes an unpainted container invisible rather than merely off-brand.
const HOSTILE_SECTION = `
	body { background: #ffffff; color: #111111; font-family: sans-serif; margin: 0; }
	#section { background: #080b13; padding: 24px; }
	/* Element rules at 0-0-1 outrank every :where() selector in the stylesheet,
	   and their colours are tuned for the section above. These are the elements
	   a theme reliably styles, so everything of ours they reach is measured. */
	a { color: #bf8515; }
	h1, h2, h3, h4 { color: #f5f5f5; }
	button, select { color: #e8e8e8; }
`;

const FIXTURE = `
<div id="section">
	<div class="roxyapi-card">
		<div class="roxyapi-card-header">
			<h3 class="roxyapi-card-title">Aries</h3>
			<p class="roxyapi-card-meta">19 August 2026</p>
		</div>
		<p class="roxyapi-card-lede">A reading paragraph.</p>
		<div class="roxyapi-section">
			<h4 class="roxyapi-section-title">Positions</h4>
			<dl class="roxyapi-fields">
				<div class="roxyapi-field"><dt>Sun</dt><dd>9 Leo 16</dd></div>
			</dl>
			<span class="roxyapi-badge">Retrograde</span>
		</div>
	</div>
	<div class="roxyapi-form-wrap">
		<form class="roxyapi-form">
			<p class="roxyapi-form-field">
				<label for="d">Date of birth <span class="roxyapi-form-required">*</span></label>
				<input type="date" id="d">
				<span class="roxyapi-form-help">Year, month and day.</span>
			</p>
			<p class="roxyapi-form-actions"><button type="submit" class="roxyapi-form-submit">Calculate</button></p>
		</form>
	</div>
	<nav class="roxyapi-eph-nav">
		<div class="roxyapi-eph-steps">
			<a class="roxyapi-eph-link" href="#">Previous month</a>
			<span class="roxyapi-eph-current">August 2026</span>
			<a class="roxyapi-eph-link" href="#">Next month</a>
		</div>
		<form class="roxyapi-eph-form">
			<label class="roxyapi-eph-field"><span>Month</span><select><option>August</option></select></label>
			<button type="submit" class="roxyapi-eph-submit">Show month</button>
		</form>
	</nav>
	<div class="roxyapi-error">The reading could not be loaded.</div>
	<div class="roxyapi-notice">This reading was already delivered.</div>
	<div class="roxyapi-meta">
		<p class="roxyapi-card-disclaimer">For entertainment purposes only.</p>
		<p class="roxyapi-credit"><a class="roxyapi-credit-link" href="#">Astrology data by RoxyAPI</a></p>
	</div>
</div>`;

// Reads a computed colour of any form the stylesheet can produce, including the
// oklab() and color() results a color-mix() computes to, by letting the canvas
// parse it and sampling the pixel.
const MEASURE = `(() => {
	const minimum = __MINIMUM__;
	const c = document.createElement('canvas');
	c.width = c.height = 1;
	const ctx = c.getContext('2d', { willReadFrequently: true });
	const rgba = (value) => {
		ctx.clearRect(0, 0, 1, 1);
		ctx.fillStyle = '#000000';
		ctx.fillStyle = value;
		ctx.fillRect(0, 0, 1, 1);
		return [...ctx.getImageData(0, 0, 1, 1).data];
	};
	const luminance = ([r, g, b]) => {
		const f = (v) => { v /= 255; return v <= 0.04045 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); };
		return 0.2126 * f(r) + 0.7152 * f(g) + 0.0722 * f(b);
	};
	const contrast = (a, b) => {
		const [hi, lo] = [luminance(a), luminance(b)].sort((x, y) => y - x);
		return (hi + 0.05) / (lo + 0.05);
	};
	// The nearest painted ancestor is what the reader actually sees behind the
	// text; a translucent tint over it is treated as the layer below.
	const behind = (el) => {
		for (let n = el; n; n = n.parentElement) {
			const px = rgba(getComputedStyle(n).backgroundColor);
			if (px[3] > 128) return px;
		}
		return [255, 255, 255, 255];
	};
	const owns = (el) => [...el.childNodes].some((n) => n.nodeType === 3 && n.textContent.trim());
	const worst = [];
	for (const el of document.querySelectorAll('#section *')) {
		// A disabled control is exempt from the contrast minimum, and a
		// decorative node carries no text to read.
		if (!owns(el) || el.matches('.is-disabled, [aria-hidden="true"]')) continue;
		const ratio = contrast(rgba(getComputedStyle(el).color), behind(el));
		if (ratio < minimum) {
			worst.push({ where: el.className || el.tagName.toLowerCase(), ratio: +ratio.toFixed(2) });
		}
	}
	return worst;
})()`;

const browser = await chromium.launch();
try {
	for ( const mode of [ 'light', 'dark' ] ) {
		const page = await browser.newPage();
		await page.setContent(
			`<!doctype html><html data-theme="${ mode }"><head><style>${ tokens }</style><style>${ css }</style><style>${ HOSTILE_SECTION }</style></head><body>${ FIXTURE }</body></html>`
		);
		const measured = await page.evaluate(
			MEASURE.replace( '__MINIMUM__', String( AA ) )
		);
		for ( const hit of measured ) {
			fail(
				`${ mode } mode: .${ hit.where } renders at ${ hit.ratio }:1 inside a section with its own background (AA needs ${ AA }:1).`
			);
		}
		await page.close();
	}
} finally {
	await browser.close();
}

report( [
	`✓ ${
		Object.keys( SURFACES ).length
	} surfaces paint both halves and clear AA in light and dark inside a section with its own background.`,
] );

/**
 * Print every collected failure and exit. Never returns.
 *
 * @param {string[]} notes Lines to print on success.
 */
function report( notes = [] ) {
	if ( failures.length > 0 ) {
		console.error( 'Frontend contrast check FAILED:\n' );
		for ( const f of failures ) {
			console.error( `  - ${ f }` );
		}
		console.error(
			'\nA reading sits inside whatever section the site owner built. Paint both halves from the token layer and it reads on any of them.'
		);
		process.exit( 1 );
	}
	for ( const note of notes ) {
		console.log( note );
	}
	process.exit( 0 );
}
