<?php
/**
 * A reading placed inline with text must render once, not twice.
 *
 * `wpautop` runs on `the_content` at priority 10 and `do_shortcode` at 11, so a
 * shortcode sitting in the middle of a sentence is already inside a `<p>` by the
 * time the component markup replaces it. `<div>` may not appear inside `<p>`, so
 * the HTML parser closed the paragraph at the fallback `<div>` and hoisted it OUT
 * of the custom element. Outside, it matched neither
 * `.roxyapi-component:defined .roxyapi-component-fallback` nor its
 * `:not(:defined)` partner, so it defaulted to visible: the reading rendered
 * twice, the second copy unstyled, unbranded and light-mode under a dark card.
 *
 * The fix is the always-present block-level `.roxyapi-embed` wrapper, which moves
 * the parser's break point outside the element so everything below stays nested.
 * Core cannot cover this case: `shortcode_unautop()` only unwraps a `<p>` whose
 * entire contents are the shortcode, which is why a STANDALONE shortcode was
 * always fine and only the inline one broke.
 *
 * These assertions are structural, on the markup the server sends. The visible
 * symptom needs a browser (an ejected node only becomes visible once a real
 * parser has moved it), so what is pinned here is the property that prevents it.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Support\ComponentRenderer;

class Test_Inline_Shortcode_Wrapper extends Mock_Http_TestCase {

	/** A payload `roxy-horoscope-card` renders through the component path. */
	private function markup(): string {
		return ComponentRenderer::render(
			'getDailyHoroscope',
			array(
				'sign'      => 'Aries',
				'date'      => '2026-08-10',
				'horoscope' => 'A test reading long enough to be a real fallback body.',
			)
		);
	}

	public function test_component_markup_is_wrapped_in_a_block_level_element(): void {
		$this->assertStringStartsWith(
			'<div class="roxyapi-embed"',
			$this->markup(),
			'Component output must open with the block-level wrapper, or wpautop nests it inside a <p>.'
		);
	}

	/**
	 * The wrapper is only worth anything if it sits OUTSIDE the custom element.
	 * Inside, the parser would still break at it and take the element with it.
	 */
	public function test_the_wrapper_encloses_the_custom_element(): void {
		$html = $this->markup();

		$wrapper = strpos( $html, 'roxyapi-embed' );
		$element = strpos( $html, '<roxy-' );

		$this->assertNotFalse( $element, 'Expected a custom element in the component markup.' );
		$this->assertLessThan( $element, $wrapper, 'The wrapper must open before the custom element.' );
	}

	/**
	 * The whole point of the wrapper: the fallback keeps its light-DOM home, so
	 * the no-JS and failed-bundle views are unchanged. If a future change moves
	 * the fallback out of the element, the CSS that hides it stops matching and
	 * the duplicate comes back.
	 */
	public function test_the_fallback_stays_inside_the_custom_element(): void {
		$html = $this->markup();

		$open     = strpos( $html, '<roxy-' );
		$fallback = strpos( $html, 'roxyapi-component-fallback' );
		$close    = strpos( $html, '</roxy-' );

		$this->assertNotFalse( $fallback, 'The no-JS fallback must still be emitted.' );
		$this->assertLessThan( $fallback, $open, 'The fallback must open after the custom element.' );
		$this->assertLessThan( $close, $fallback, 'The fallback must close before the custom element does.' );
	}

	/** And it must still carry readable content, not an empty shell. */
	public function test_the_fallback_still_carries_the_reading(): void {
		$this->assertStringContainsString( 'A test reading long enough', $this->markup() );
	}

	/**
	 * The end-to-end shape, through the real content pipeline: `wpautop` then
	 * `do_shortcode`, exactly as `the_content` orders them.
	 *
	 * **The served HTML really does read `<p>Your reading: <div class="roxyapi-embed">`,
	 * and that is fine.** WordPress cannot avoid it, and asserting its absence was
	 * this test's first, wrong shape. `<div>` inside `<p>` is invalid, so every
	 * browser closes the paragraph at that point; what decides whether the bug
	 * appears is WHERE that break lands. With the wrapper outermost it lands
	 * above the custom element and the whole subtree moves together, fallback
	 * included. Without it, the break landed at the fallback `<div>` instead and
	 * tore it out of the element.
	 *
	 * So what is asserted is the property that fixes the break point: between the
	 * wrapper and the custom element nothing re-opens a paragraph, and no second
	 * block-level tag offers the parser an earlier place to split.
	 */
	public function test_nothing_can_split_the_markup_before_the_custom_element(): void {
		$this->mock_responses['horoscope'] = array(
			'sign'      => 'Aries',
			'date'      => '2026-08-10',
			'horoscope' => 'A test reading long enough to be a real fallback body.',
		);

		$rendered = do_shortcode( wpautop( 'Your reading for today: [roxy_horoscope sign="aries"] Enjoy.' ) );
		$this->assertStringContainsString( 'roxyapi-embed', $rendered, 'Fixture failed: the shortcode did not render.' );

		$wrapper = strpos( $rendered, '<div class="roxyapi-embed"' );
		$element = strpos( $rendered, '<roxy-' );
		$between = substr( $rendered, $wrapper, $element - $wrapper );

		$this->assertStringNotContainsString( '<p', $between, 'Nothing may open a paragraph between the wrapper and the element.' );
		$this->assertSame(
			1,
			substr_count( $between, '<div' ),
			'The wrapper must be the only block-level tag before the custom element.'
		);
	}
}
