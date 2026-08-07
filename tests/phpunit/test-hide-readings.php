<?php
/**
 * Tests for the "hide written readings" setting and its per-placement
 * `hide_readings` shortcode attribute.
 *
 * Three things have to hold together or a site owner gets a half-hidden page:
 * the precedence (attribute, then setting, then shown), the `hide-readings`
 * attribute on the emitted custom element, and the same suppression inside the
 * server-rendered fallback that a visitor without JavaScript reads.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Api\Cache;
use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Support\ComponentRenderer;
use RoxyAPI\Support\GenericRenderer;

class Test_Hide_Readings extends Mock_Http_TestCase {

	/**
	 * Response shaped like a natal chart: a written interpretation per planet,
	 * a prose block under an `*Interpretation` key, and a `summary` OBJECT that
	 * carries chart data rather than prose.
	 *
	 * @return array<string, mixed>
	 */
	private function chart_data(): array {
		return array(
			'houseSystem' => 'placidus',
			'planets'     => array(
				array(
					'name'           => 'Sun',
					'sign'           => 'Taurus',
					'degree'         => 24.65,
					'interpretation' => array(
						'summary'  => 'Your Sun in Taurus reveals dependable energy.',
						'detailed' => 'Sun represents self-awareness and ego.',
					),
				),
				array(
					'name'           => 'Moon',
					'sign'           => 'Pisces',
					'degree'         => 3.21,
					'interpretation' => array(
						'summary'  => 'Your Moon in Pisces reveals a receptive inner life.',
						'detailed' => 'Moon represents instinct and memory.',
					),
				),
			),
			'aspectsInterpretation' => array(
				'summary'  => 'Your chart contains 42 aspects and reads as balanced.',
				'dominant' => 'balanced',
			),
			'summary'     => array(
				'dominantElement'  => 'Earth',
				'dominantModality' => 'Fixed',
			),
		);
	}

	public function setUp(): void {
		parent::setUp();
		delete_option( SettingsPage::OPTION_NAME );
		Cache::flush_all();
	}

	public function tearDown(): void {
		delete_option( SettingsPage::OPTION_NAME );
		Cache::flush_all();
		parent::tearDown();
	}

	/**
	 * Turn the setting on or off without going through the admin form.
	 */
	private function set_hide_readings( bool $on ): void {
		update_option( SettingsPage::OPTION_NAME, array( 'hide_readings' => $on ) );
	}

	/**
	 * The server-rendered card inside the element. This is the part a visitor
	 * reads, so it is the part a reading assertion belongs against. The JSON
	 * payload beside it is machine input for the component and keeps the whole
	 * response either way.
	 */
	private function fallback_of( string $html ): string {
		$start = strpos( $html, '<div class="roxyapi-component-fallback">' );
		$this->assertIsInt( $start, 'The element must carry a server-rendered fallback.' );
		return substr( $html, $start );
	}

	// -------------------------------------------------------------------------
	// Precedence
	// -------------------------------------------------------------------------

	/**
	 * Default path: no setting, no attribute, readings shown and no attribute
	 * on the element. This is the render every existing page already gets.
	 */
	public function test_default_shows_readings_and_emits_no_attribute(): void {
		$out = ComponentRenderer::render( 'generateNatalChart', $this->chart_data() );

		$this->assertStringNotContainsString( 'hide-readings', $out );
		$this->assertStringContainsString( 'dependable energy', $this->fallback_of( $out ) );
	}

	/**
	 * Setting on, attribute silent: the setting decides.
	 */
	public function test_setting_hides_readings_when_attribute_is_absent(): void {
		$this->set_hide_readings( true );
		$out = ComponentRenderer::render( 'generateNatalChart', $this->chart_data() );

		$this->assertStringContainsString( ' hide-readings>', $out );
		$this->assertStringNotContainsString( 'dependable energy', $this->fallback_of( $out ) );
	}

	/**
	 * The JSON payload is what the component renders the chart FROM, so it
	 * stays whole even when the readings are hidden. Trimming it would leave
	 * the component unable to draw, and it is machine input, never displayed
	 * text. Pinned so a later "leak" fix does not break the chart.
	 */
	public function test_the_data_payload_is_never_trimmed(): void {
		$this->set_hide_readings( true );
		$out     = ComponentRenderer::render( 'generateNatalChart', $this->chart_data() );
		$payload = substr( $out, 0, strpos( $out, '<div class="roxyapi-component-fallback">' ) );

		$this->assertStringContainsString( 'dependable energy', $payload );
	}

	/**
	 * The shortcode default is `inherit`, which is not an opinion. It has to
	 * behave exactly as if the attribute were absent, in both directions.
	 */
	public function test_inherit_follows_the_setting_both_ways(): void {
		$this->set_hide_readings( true );
		$this->assertStringContainsString(
			' hide-readings>',
			ComponentRenderer::render( 'generateNatalChart', $this->chart_data(), ComponentRenderer::INHERIT )
		);

		$this->set_hide_readings( false );
		$this->assertStringNotContainsString(
			'hide-readings',
			ComponentRenderer::render( 'generateNatalChart', $this->chart_data(), ComponentRenderer::INHERIT )
		);
	}

	/**
	 * An explicit attribute wins over the setting, in both directions. This is
	 * the per-placement override the shortcode exists for.
	 */
	public function test_attribute_overrides_the_setting_in_both_directions(): void {
		$this->set_hide_readings( false );
		$forced_on = ComponentRenderer::render( 'generateNatalChart', $this->chart_data(), '1' );
		$this->assertStringContainsString( ' hide-readings>', $forced_on );
		$this->assertStringNotContainsString( 'dependable energy', $this->fallback_of( $forced_on ) );

		$this->set_hide_readings( true );
		$forced_off = ComponentRenderer::render( 'generateNatalChart', $this->chart_data(), '0' );
		$this->assertStringNotContainsString( 'hide-readings', $forced_off );
		$this->assertStringContainsString( 'dependable energy', $this->fallback_of( $forced_off ) );
	}

	/**
	 * Site owners type words, not booleans. Every value WordPress and PHP treat
	 * as a boolean has to resolve, and an unparseable one has to fall through
	 * to the setting rather than guess.
	 *
	 * @dataProvider provide_attribute_values
	 */
	public function test_attribute_values_resolve( string $value, bool $setting, bool $expected_hidden ): void {
		$this->set_hide_readings( $setting );
		$out    = ComponentRenderer::render( 'generateNatalChart', $this->chart_data(), $value );
		$hidden = strpos( $out, 'hide-readings' ) !== false;

		$this->assertSame( $expected_hidden, $hidden, "Value '{$value}' resolved wrong." );
	}

	/**
	 * @return array<string, array{0: string, 1: bool, 2: bool}>
	 */
	public function provide_attribute_values(): array {
		return array(
			'1 hides'                    => array( '1', false, true ),
			'true hides'                 => array( 'true', false, true ),
			'yes hides'                  => array( 'yes', false, true ),
			'on hides'                   => array( 'on', false, true ),
			'uppercase TRUE hides'       => array( 'TRUE', false, true ),
			'padded value hides'         => array( '  1  ', false, true ),
			'0 shows'                    => array( '0', true, false ),
			'false shows'                => array( 'false', true, false ),
			'no shows'                   => array( 'no', true, false ),
			'off shows'                  => array( 'off', true, false ),
			'empty falls back to on'     => array( '', true, true ),
			'empty falls back to off'    => array( '', false, false ),
			'gibberish falls back to on' => array( 'maybe', true, true ),
			'inherit falls back to off'  => array( 'inherit', false, false ),
		);
	}

	// -------------------------------------------------------------------------
	// The no-JavaScript fallback
	// -------------------------------------------------------------------------

	/**
	 * The fallback inside the element is what a visitor without JavaScript
	 * reads. If it kept the written text, hiding it in the component would be
	 * cosmetic.
	 */
	public function test_fallback_inside_the_element_drops_the_written_text(): void {
		$this->set_hide_readings( true );
		$out = ComponentRenderer::render( 'generateNatalChart', $this->chart_data() );

		$fallback = strstr( $out, '<div class="roxyapi-component-fallback">' );
		$this->assertIsString( $fallback, 'The element must carry a server-rendered fallback.' );

		$this->assertStringNotContainsString( 'dependable energy', $fallback );
		$this->assertStringNotContainsString( 'reads as balanced', $fallback );
		$this->assertStringNotContainsString( 'Aspects interpretation', $fallback );
	}

	/**
	 * Hiding the readings must not take the chart with it. Positions, values
	 * and the derived summary block are what the site owner is keeping.
	 */
	public function test_fallback_keeps_the_chart_data(): void {
		$out = GenericRenderer::render( 'generateNatalChart', $this->chart_data(), false, true );

		$this->assertStringContainsString( 'Taurus', $out );
		$this->assertStringContainsString( 'Pisces', $out );
		$this->assertStringContainsString( 'placidus', $out );
		// `summary` names a prose slot, but here it holds an object of chart
		// data. Dropping it by name alone would delete the dominant element.
		$this->assertStringContainsString( 'Earth', $out );
		$this->assertStringContainsString( 'Fixed', $out );
	}

	/**
	 * A prose field that holds a STRING is written text and goes; the same
	 * field name holding an object is structure and stays. Both directions in
	 * one response, so a future edit cannot satisfy one and break the other.
	 */
	public function test_prose_strings_go_and_structure_stays(): void {
		$data = array(
			'name'    => 'Three of Cups',
			'meaning' => 'The Three of Cups extends the loving exchange outward into community.',
			'detail'  => array(
				'summary'  => 'A written summary that must not survive.',
				'keywords' => array( 'celebration', 'friendship' ),
			),
		);

		$out = GenericRenderer::render( 'castDailyCard', $data, false, true );

		$this->assertStringNotContainsString( 'loving exchange', $out );
		$this->assertStringNotContainsString( 'must not survive', $out );
		$this->assertStringContainsString( 'celebration', $out );
		$this->assertStringContainsString( 'Three of Cups', $out );
	}

	/**
	 * An interpretation object emptied by the pass must not leave a bare
	 * heading behind, and a list that lost an entry must still render as a
	 * list rather than collapsing into a key/value object.
	 */
	public function test_emptied_containers_are_dropped_and_lists_stay_lists(): void {
		$data = array(
			'title' => 'Reading',
			'notes' => array(
				array( 'meaning' => 'gone' ),
				array( 'label' => 'kept', 'value' => 7 ),
			),
		);

		$out = GenericRenderer::render( 'getX', $data, false, true );

		$this->assertStringNotContainsString( 'gone', $out );
		$this->assertStringContainsString( 'kept', $out );
		// One surviving entry, rendered through the list path.
		$this->assertStringContainsString( 'roxyapi-list', $out );
	}

	/**
	 * With readings off, the default render must be byte-identical to what the
	 * plugin produced before the setting existed. Anything else is a silent
	 * change on every live page.
	 */
	public function test_showing_readings_matches_the_untouched_render(): void {
		$data = $this->chart_data();

		$this->assertSame(
			GenericRenderer::render( 'generateNatalChart', $data, false ),
			GenericRenderer::render( 'generateNatalChart', $data, false, false )
		);
	}

	// -------------------------------------------------------------------------
	// Shortcode surface
	// -------------------------------------------------------------------------

	/**
	 * Every hero that renders a reading has to accept the attribute, or the
	 * setting becomes an all-or-nothing switch with no way out on one page.
	 * Covers the hand-written hero and the generated ones, including the hero
	 * that takes no inputs of its own.
	 */
	public function test_every_rendering_hero_declares_the_attribute(): void {
		$skipped = 0;
		foreach ( \RoxyAPI\Shortcodes\Registrar::HERO_SHORTCODES as $tag => $class ) {
			$defaults = $class::DEFAULTS;
			if ( $defaults === array() ) {
				// Form-only heroes render a visitor form, never a reading.
				++$skipped;
				continue;
			}
			$this->assertArrayHasKey(
				'hide_readings',
				$defaults,
				"[{$tag}] must declare a hide_readings attribute."
			);
			$this->assertSame(
				ComponentRenderer::INHERIT,
				$defaults['hide_readings'],
				"[{$tag}] must default hide_readings to inherit so the setting decides."
			);
		}
		$this->assertGreaterThan( 0, count( \RoxyAPI\Shortcodes\Registrar::HERO_SHORTCODES ) - $skipped );
	}

	/**
	 * End to end through a hero shortcode: the attribute a site owner types
	 * has to survive shortcode_atts and land on the element.
	 */
	public function test_hero_shortcode_attribute_reaches_the_element(): void {
		$this->mock_responses = array( '/astrology/natal-chart' => $this->chart_data() );
		$call                 = '[roxy_natal_chart birth_date="1990-05-15" birth_time="14:30" lat="40.7128" lon="-74.0060" tz="UTC"%s]';

		$hidden = do_shortcode( sprintf( $call, ' hide_readings="1"' ) );
		$this->assertStringContainsString( ' hide-readings>', $hidden );
		$this->assertStringNotContainsString( 'dependable energy', $this->fallback_of( $hidden ) );

		Cache::flush_all();
		$shown = do_shortcode( sprintf( $call, '' ) );
		$this->assertStringNotContainsString( 'hide-readings', $shown );
		$this->assertStringContainsString( 'dependable energy', $this->fallback_of( $shown ) );
	}

	/**
	 * Same for a generated long-tail shortcode, which is the surface the
	 * generator emits 161 times. It has no DEFAULTS const to assert against,
	 * so drive it the way a site owner does.
	 */
	public function test_long_tail_shortcode_attribute_reaches_the_element(): void {
		$this->mock_responses = array( '/astrology/signs/' => array( 'name' => 'Aries', 'meaning' => 'The written meaning.' ) );

		$hidden = do_shortcode( '[roxy_get_zodiac_sign id="aries" hide_readings="1"]' );
		$this->assertStringContainsString( ' hide-readings>', $hidden );
		$this->assertStringNotContainsString( 'written meaning', $this->fallback_of( $hidden ) );

		Cache::flush_all();
		$shown = do_shortcode( '[roxy_get_zodiac_sign id="aries"]' );
		$this->assertStringNotContainsString( 'hide-readings', $shown );
		$this->assertStringContainsString( 'written meaning', $this->fallback_of( $shown ) );
	}
}
