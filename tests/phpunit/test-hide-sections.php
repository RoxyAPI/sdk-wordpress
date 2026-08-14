<?php
/**
 * Tests for the "hide sections" setting.
 *
 * The setting names blocks of a reading to remove, and it has to remove them
 * from BOTH views or a site owner gets a half-hide: the resolved list travels on
 * the element and hides the block inside the upgraded component, and the same
 * names drop the same top-level keys out of the server-rendered fallback a
 * no-JavaScript visitor and a crawler read.
 *
 * The first group is what CANNOT get through. The value is interpolated into a
 * stylesheet, so a stored setting is a CSS injection surface exactly the way the
 * palette is, and the same rule applies: anything that is not a plain part name
 * is dropped rather than repaired.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\SettingsFields;
use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Generated\ComponentMap;
use RoxyAPI\Support\ComponentRenderer;
use RoxyAPI\Support\Sanitize;
use RoxyAPI\Support\Theming;

class Test_Hide_Sections extends \WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		delete_option( SettingsPage::OPTION_NAME );
		// Settings errors are request-global, so a notice raised by one test
		// would otherwise satisfy the next one's assertion.
		$GLOBALS['wp_settings_errors'] = array();
	}

	public function tearDown(): void {
		delete_option( SettingsPage::OPTION_NAME );
		$GLOBALS['wp_settings_errors'] = array();
		parent::tearDown();
	}

	private function set_hide_sections( string $value ): void {
		update_option( SettingsPage::OPTION_NAME, array( 'hide_sections' => $value ) );
	}

	/**
	 * A natal-chart-shaped response: a `patterns` block the component draws as
	 * its own section, beside data that must survive the hide.
	 *
	 * @return array<string, mixed>
	 */
	private function chart_data(): array {
		return array(
			'houseSystem' => 'placidus',
			'planets'     => array(
				array(
					'name'   => 'Sun',
					'sign'   => 'Capricorn',
					'degree' => 21.4,
				),
			),
			'patterns'    => array(
				array(
					'name'    => 'T-Square',
					'planets' => array( 'Sun', 'Moon', 'Mars' ),
					'apex'    => 'Mars',
				),
				array(
					'name'    => 'Stellium',
					'planets' => array( 'Sun', 'Mercury', 'Venus' ),
				),
			),
		);
	}

	private function fallback_of( string $html ): string {
		$start = strpos( $html, '<div class="roxyapi-component-fallback">' );
		$this->assertIsInt( $start, 'The element must carry a server-rendered fallback.' );
		return substr( $html, $start );
	}

	private function payload_of( string $html ): string {
		$end = strpos( $html, '<div class="roxyapi-component-fallback">' );
		$this->assertIsInt( $end, 'The element must carry a server-rendered fallback.' );
		return substr( $html, 0, $end );
	}

	/**
	 * Just the opening tag of the custom element, so an assertion about what the
	 * ELEMENT was told cannot pass or fail on the response payload beside it,
	 * which contains the same words.
	 */
	private function element_attrs_of( string $html ): string {
		$start = strpos( $html, '<roxy-' );
		$this->assertIsInt( $start, 'The render must emit a custom element.' );
		$end = strpos( $html, '>', (int) $start );
		$this->assertIsInt( $end, 'The element tag must close.' );
		return substr( $html, (int) $start, (int) $end - (int) $start + 1 );
	}

	// -------------------------------------------------------------------------
	// The sanitiser
	// -------------------------------------------------------------------------

	/**
	 * Every shape someone would try if they wanted to close our declaration and
	 * open one of their own, or reach the network from a stylesheet. A part name
	 * is a single lowercase word, so all of these are simply not part names.
	 *
	 * @return array<string, array<int, string>>
	 */
	public function injection_payloads(): array {
		return array(
			'closes the rule'         => array( 'patterns){display:block} html{display:none' ),
			'closes the block'        => array( 'patterns}html{display:none' ),
			'closes the style tag'    => array( '</style><script>alert(1)</script>' ),
			'css function'            => array( 'url(https://evil.test/beacon.png)' ),
			'import'                  => array( '@import "https://evil.test/x.css"' ),
			'expression'              => array( 'expression(alert(1))' ),
			'universal selector'      => array( '*' ),
			'escaped paren'           => array( 'patterns\\29 ' ),
			'comment out the closer'  => array( 'patterns/*' ),
			'newline in the name'     => array( "pat\nterns" ),
			'leading digit'           => array( '1patterns' ),
			'attribute selector'      => array( 'patterns[foo="bar"]' ),
		);
	}

	/**
	 * @dataProvider injection_payloads
	 */
	public function test_hostile_values_never_reach_the_stylesheet( string $payload ): void {
		$this->set_hide_sections( $payload );

		$this->assertSame( array(), Theming::hidden_sections() );
		$this->assertStringNotContainsString( '::part(', Theming::inline_css() );
	}

	/**
	 * A hostile value beside a real one loses on its own. The valid name still
	 * works, so a typo cannot silently disable the whole setting.
	 */
	public function test_a_hostile_entry_is_dropped_without_taking_the_valid_one(): void {
		$this->set_hide_sections( 'patterns, }html{display:none' );

		$this->assertSame( array( 'patterns' ), Theming::hidden_sections() );

		$out = ComponentRenderer::render( 'generateNatalChart', $this->chart_data() );
		$this->assertStringContainsString( 'hide-sections="patterns"', $out );
		$this->assertStringNotContainsString( 'html{display:none}', $out );
	}

	public function test_names_are_lowercased_trimmed_and_deduped(): void {
		$this->assertSame(
			array( 'patterns', 'chart-legend' ),
			Sanitize::section_names( '  Patterns , chart-legend,PATTERNS ,, ' )
		);
	}

	public function test_a_non_scalar_setting_is_not_a_list(): void {
		$this->assertSame( array(), Sanitize::section_names( array( 'patterns' ) ) );
		$this->assertSame( array(), Sanitize::section_names( null ) );
	}

	// -------------------------------------------------------------------------
	// The element attribute
	//
	// The resolved list travels on the element rather than in a site-wide
	// stylesheet. A `::part()` rule written at document level sits in the OUTER
	// tree and outranks anything inside the component, so it would win against a
	// placement asking to KEEP a block, which is the one thing per-placement
	// control has to be able to do.
	// -------------------------------------------------------------------------

	public function test_an_empty_setting_emits_no_attribute(): void {
		$out = ComponentRenderer::render( 'generateNatalChart', $this->chart_data() );

		$this->assertStringNotContainsString( 'hide-sections', $out );
	}

	public function test_the_site_setting_reaches_the_element(): void {
		$this->set_hide_sections( 'patterns, aspects' );
		$out = ComponentRenderer::render( 'generateNatalChart', $this->chart_data() );

		$this->assertStringContainsString( 'hide-sections="patterns,aspects"', $out );
	}

	/**
	 * The point of the whole attribute: one placement disagreeing with the site.
	 */
	public function test_a_placement_overrides_the_site_setting(): void {
		$this->set_hide_sections( 'patterns' );
		$out = ComponentRenderer::render( 'generateNatalChart', $this->chart_data(), null, 'legend' );

		$this->assertStringContainsString( 'hide-sections="legend"', $out );
		$this->assertStringNotContainsString( 'patterns', $this->element_attrs_of( $out ) );
	}

	/**
	 * A boolean cannot say "hide nothing here", so the list has a word for it.
	 * Without this a site hiding a block everywhere could never show it once.
	 */
	public function test_none_opts_one_placement_out_of_the_site_setting(): void {
		$this->set_hide_sections( 'patterns' );
		$out = ComponentRenderer::render( 'generateNatalChart', $this->chart_data(), null, 'none' );

		$this->assertStringNotContainsString( 'hide-sections', $out );
		$this->assertStringContainsString( 'Patterns (', $this->fallback_of( $out ), 'none restores the block in the no-JS view too.' );
	}

	public function test_inherit_and_empty_follow_the_site_setting(): void {
		$this->set_hide_sections( 'patterns' );

		foreach ( array( 'inherit', '', null ) as $value ) {
			$out = ComponentRenderer::render( 'generateNatalChart', $this->chart_data(), null, $value );
			$this->assertStringContainsString( 'hide-sections="patterns"', $out );
		}
	}

	/**
	 * A placement value is sanitized exactly like the setting, so a shortcode
	 * cannot put anything in the attribute the settings field could not.
	 */
	public function test_a_placement_value_is_sanitized_like_the_setting(): void {
		$out = ComponentRenderer::render( 'generateNatalChart', $this->chart_data(), null, 'Patterns , }html{display:none' );

		$this->assertStringContainsString( 'hide-sections="patterns"', $out );
		$this->assertStringNotContainsString( 'html{display:none}', $out );
	}

	/**
	 * Guard against the global rule coming back. It is not a harmless extra:
	 * it would silently defeat every override above.
	 */
	public function test_the_stylesheet_carries_no_part_rule_at_all(): void {
		$this->set_hide_sections( 'patterns' );

		$this->assertStringNotContainsString( '::part(', Theming::inline_css() );
		$this->assertStringNotContainsString( '::part(', Theming::inline_css( true ) );
	}

	// -------------------------------------------------------------------------
	// The server-rendered fallback
	// -------------------------------------------------------------------------

	/**
	 * Baseline: with the setting empty the block is in the fallback, which is
	 * what makes the assertion below meaningful.
	 */
	public function test_the_fallback_carries_the_block_by_default(): void {
		$fallback = $this->fallback_of( ComponentRenderer::render( 'generateNatalChart', $this->chart_data() ) );

		$this->assertStringContainsString( 'Patterns (', $fallback );
		$this->assertStringContainsString( 'T-Square', $fallback );
	}

	/**
	 * `::part()` cannot reach the fallback: it is light DOM with no shadow root.
	 * Without this pass the block would stay in the page for a no-JavaScript
	 * visitor and in the HTML a crawler reads.
	 */
	public function test_a_hidden_section_leaves_the_fallback(): void {
		$this->set_hide_sections( 'patterns' );
		$fallback = $this->fallback_of( ComponentRenderer::render( 'generateNatalChart', $this->chart_data() ) );

		$this->assertStringNotContainsString( 'Patterns (', $fallback );
		$this->assertStringNotContainsString( 'T-Square', $fallback );
		$this->assertStringNotContainsString( 'Stellium', $fallback );
		$this->assertStringContainsString( 'Capricorn', $fallback, 'Only the named block goes.' );
	}

	/**
	 * The JSON island is what the component draws the chart FROM. Trimming it
	 * would leave the component unable to draw, and the whole point of the
	 * `::part()` lever is that the component keeps its data and hides one block.
	 */
	public function test_the_data_payload_is_never_trimmed(): void {
		$this->set_hide_sections( 'patterns' );
		$out = ComponentRenderer::render( 'generateNatalChart', $this->chart_data() );

		$this->assertStringContainsString( 'T-Square', $this->payload_of( $out ) );
		$this->assertStringContainsString( '"patterns"', $this->payload_of( $out ) );
	}

	/**
	 * Response keys are camelCase and part names are kebab-case, so the match
	 * collapses both the way every other name heuristic in the renderer does.
	 */
	public function test_a_kebab_case_name_matches_a_camel_case_key(): void {
		$this->set_hide_sections( 'chart-patterns' );
		$data     = array(
			'chartPatterns' => array( array( 'name' => 'T-Square' ) ),
			'houseSystem'   => 'placidus',
		);
		$fallback = $this->fallback_of( ComponentRenderer::render( 'generateNatalChart', $data ) );

		$this->assertStringNotContainsString( 'T-Square', $fallback );
		$this->assertStringContainsString( 'House system', $fallback );
	}

	/**
	 * A part name with no matching response key is correct and costs nothing:
	 * it means the block is drawn by the component and has no fallback of its
	 * own. Nothing else may disappear because of it.
	 */
	public function test_a_name_that_matches_no_key_changes_nothing(): void {
		$this->set_hide_sections( 'legend' );
		$fallback = $this->fallback_of( ComponentRenderer::render( 'generateNatalChart', $this->chart_data() ) );

		$this->assertStringContainsString( 'Patterns (', $fallback );
		$this->assertStringContainsString( 'Capricorn', $fallback );
	}

	/**
	 * Only whole blocks go. A field of that name nested inside another block is
	 * a field, not a section, and a part is never one.
	 */
	public function test_a_nested_key_of_the_same_name_survives(): void {
		$this->set_hide_sections( 'apex' );
		$fallback = $this->fallback_of( ComponentRenderer::render( 'generateNatalChart', $this->chart_data() ) );

		$this->assertStringContainsString( 'Mars', $fallback );
	}

	// -------------------------------------------------------------------------
	// The vocabulary, and saying so
	//
	// The setting cannot report its own failure from the front end: a name that
	// reaches no block and a name whose block that reading never had produce the
	// same page. So the admin field is the only place the difference can be
	// stated, and it can only state it if it knows which names exist.
	// -------------------------------------------------------------------------

	public function test_the_published_vocabulary_is_usable(): void {
		$parts = ComponentMap::parts();

		// Non-vacuity: an empty list would make the checks below pass and would
		// silently turn every saved name into an unrecognised one.
		$this->assertGreaterThan( 20, count( $parts ) );
		$this->assertSame( array_values( array_unique( $parts ) ), $parts, 'Names must be unique.' );
		$sorted = $parts;
		sort( $sorted );
		$this->assertSame( $sorted, $parts, 'Names are recorded sorted, so a re-record is a readable diff.' );

		foreach ( $parts as $name ) {
			$this->assertMatchesRegularExpression( '/^[a-z][a-z0-9-]*$/', $name );
			$this->assertSame( array( $name ), Sanitize::section_names( $name ), 'Every published name must survive the sanitiser the setting runs it through.' );
		}
	}

	/**
	 * The one name the admin copy and the readme both tell a site owner to type.
	 * If the bundle stopped publishing it, that instruction would be wrong.
	 */
	public function test_the_documented_name_is_in_the_vocabulary(): void {
		$this->assertContains( 'patterns', ComponentMap::parts() );
	}

	/**
	 * The suggestion list has to survive `wp_kses()`. The templates pass this
	 * markup through it, and a tag missing from the allowed map is stripped
	 * without a word, which would leave the field looking exactly as unhelpful
	 * as it did before while every test on the HTML itself still passed.
	 */
	public function test_the_suggestion_list_survives_kses(): void {
		$filtered = wp_kses( SettingsFields::hide_sections_html(), SettingsFields::input_kses_allowed() );

		$this->assertStringContainsString( '<datalist id="roxyapi_hide_sections_names">', $filtered );
		$this->assertStringContainsString( 'list="roxyapi_hide_sections_names"', $filtered );
		$this->assertStringContainsString( '<option value="patterns">', $filtered );
		$this->assertSame(
			count( ComponentMap::parts() ),
			substr_count( $filtered, '<option ' ),
			'Every published name should be offered.'
		);
	}

	/**
	 * @return array<string, array<int, string>>
	 */
	public function unrecognised_values(): array {
		return array(
			'a translated panel title' => array( 'Figuras planetarias', 'figuras planetarias' ),
			'a plausible near miss'    => array( 'patrones', 'patrones' ),
			'the wrong separator'      => array( 'patterns;details', 'patterns;details' ),
			'a real name and a typo'   => array( 'patterns, pattrens', 'pattrens' ),
		);
	}

	/**
	 * @dataProvider unrecognised_values
	 */
	public function test_an_unrecognised_name_is_reported( string $value, string $named ): void {
		SettingsFields::sanitize( array( 'hide_sections' => $value ) );

		$codes = wp_list_pluck( get_settings_errors( SettingsPage::OPTION_NAME ), 'code' );
		$this->assertContains( 'unknown_hide_sections', $codes, "Saving \"{$value}\" must say so rather than hide nothing in silence." );

		$messages = implode( ' ', wp_list_pluck( get_settings_errors( SettingsPage::OPTION_NAME ), 'message' ) );
		$this->assertStringContainsString( $named, $messages, 'The message has to name the entry that did nothing.' );
	}

	public function test_a_recognised_name_is_not_reported(): void {
		SettingsFields::sanitize( array( 'hide_sections' => '  Patterns , details ' ) );

		$codes = wp_list_pluck( get_settings_errors( SettingsPage::OPTION_NAME ), 'code' );
		$this->assertNotContains( 'unknown_hide_sections', $codes );
	}

	public function test_an_empty_value_is_not_reported(): void {
		SettingsFields::sanitize( array( 'hide_sections' => '' ) );

		$codes = wp_list_pluck( get_settings_errors( SettingsPage::OPTION_NAME ), 'code' );
		$this->assertNotContains( 'unknown_hide_sections', $codes );
	}

	/**
	 * The value is kept rather than corrected, so the owner can see and repair
	 * what they typed instead of being handed an empty box.
	 */
	public function test_an_unrecognised_value_is_still_stored(): void {
		$out = SettingsFields::sanitize( array( 'hide_sections' => 'Figuras planetarias' ) );

		$this->assertSame( 'Figuras planetarias', $out['hide_sections'] );
	}

	/**
	 * A tab that does not carry the field must not blank it. Every other setting
	 * relies on the same rule, and this one is a text field with no hidden
	 * partner to tell "absent" from "cleared".
	 */
	public function test_saving_another_tab_keeps_the_value(): void {
		$this->set_hide_sections( 'patterns' );
		$out = SettingsFields::sanitize( array( 'theme_mode' => 'dark' ) );

		$this->assertSame( 'patterns', $out['hide_sections'] );
	}
}
