<?php
/**
 * The builder describes library readings without inventing attributes.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\Catalog;
use RoxyAPI\Generated\Builder\Manifest;
use RoxyAPI\Generated\Domains;
use RoxyAPI\Generated\Endpoints;
use RoxyAPI\Generated\FormLabels;
use RoxyAPI\Plugin;
use RoxyAPI\Shortcodes\Registrar;
use RoxyAPI\Support\ComponentRenderer;
use RoxyAPI\Support\Language;

class Test_Builder_Manifest extends \WP_UnitTestCase {

	/**
	 * Register plugin shortcodes without invoking unrelated init callbacks.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function entries(): array {
		Registrar::register_hero();
		Registrar::register_generated();
		return array_column( Manifest::all()['entries'], null, 'tag' );
	}

	/**
	 * Utilities remain registered for existing placements but are not readings.
	 * Follow Catalog::grouped() and the generated bin/domains.json flags.
	 *
	 * @return array<string, string> Shortcode tags mapped to operation IDs.
	 */
	private function utility_tags(): array {
		$domains = Domains::all();
		$tags    = array();
		foreach ( Endpoints::all() as $operation => $endpoint ) {
			if ( isset( $domains[ $endpoint['tag'] ] ) && empty( $domains[ $endpoint['tag'] ]['library'] ) ) {
				$tags[ $endpoint['shortcode_tag'] ] = $operation;
			}
		}
		return $tags;
	}

	public function test_builder_and_library_offer_the_same_readings(): void {
		$utilities = array_values( $this->utility_tags() );
		sort( $utilities );
		$this->assertSame(
			array( 'getFieldLabels', 'getUsageStats', 'listLanguages' ),
			$utilities,
			'Only account usage and developer language/field-label utilities are exempt from the registered-shortcode sweep.'
		);
		$library_tags = array();
		foreach ( Catalog::grouped() as $group ) {
			$library_tags = array_merge( $library_tags, array_column( $group, 'tag' ) );
		}
		$builder_tags = array_keys( $this->entries() );
		sort( $library_tags );
		sort( $builder_tags );
		$expected = array();
		$domains  = Domains::all();
		foreach ( \RoxyAPI\Generated\Heroes\Manifest::all() as $tag => $hero ) {
			if ( ! empty( $domains[ $hero['domain'] ]['library'] ) ) {
				$expected[] = $tag;
			}
		}
		foreach ( Endpoints::all() as $endpoint ) {
			if ( empty( $endpoint['hero'] ) && ! empty( $domains[ $endpoint['tag'] ]['library'] ) ) {
				$expected[] = $endpoint['shortcode_tag'];
			}
		}
		$expected = array_values( array_unique( $expected ) );
		sort( $expected );
		$this->assertSame( $expected, $library_tags, 'The library must contain the complete canonical reading tag set.' );
		$this->assertSame( $expected, $builder_tags, 'The builder must contain the complete canonical reading tag set.' );
	}

	public function test_shortcode_attribute_quoting_matches_the_shared_contract(): void {
		$fixtures = json_decode( file_get_contents( dirname( __DIR__ ) . '/fixtures/shortcode-attributes.json' ), true );
		$this->assertNotEmpty( $fixtures );
		foreach ( $fixtures as $fixture ) {
			$quoted = Catalog::quote_attribute( $fixture['input'] );
			$this->assertSame( $fixture['expected'], $quoted, $fixture['input'] );
			$this->assertSame( array( 'value' => $fixture['parsed'] ), shortcode_parse_atts( 'value=' . $quoted ) );
		}
	}

	public function test_every_registered_reading_tag_and_defaults_key_is_described(): void {
		global $shortcode_tags;

		// Language belongs to site/Branding settings; mode belongs to builder step 2.
		$excluded  = array( 'lang', 'mode' );
		$entries   = $this->entries();
		$utilities = $this->utility_tags();
		$tags      = array();
		foreach ( $shortcode_tags as $tag => $callback ) {
			if ( strpos( $tag, 'roxy_' ) !== 0 ) {
				continue;
			}
			if ( isset( $utilities[ $tag ] ) ) {
				$this->assertArrayNotHasKey( $tag, $entries, $utilities[ $tag ] . ' is an account or language utility, not a reading.' );
				continue;
			}
			$tags[] = $tag;
			$this->assertArrayHasKey( $tag, $entries, $tag );
			$class = Registrar::HERO_SHORTCODES[ $tag ] ?? $callback[0];
			$class = is_object( $class ) ? get_class( $class ) : $class;
			$this->assertTrue( defined( $class . '::DEFAULTS' ), $tag );
			$expected = array_diff( array_keys( $class::DEFAULTS ), ComponentRenderer::RESERVED_ATTS, $excluded );
			$actual   = array_column( $entries[ $tag ]['attributes'], 'att' );
			$this->assertCount( count( array_unique( $actual ) ), $actual, $tag . ' repeats an attribute.' );
			sort( $expected );
			sort( $actual );
			$this->assertSame( $expected, $actual, $tag . ' must describe exactly DEFAULTS minus display attributes, lang and mode.' );
			foreach ( $entries[ $tag ]['attributes'] as $attribute ) {
				$this->assertSame( $class::DEFAULTS[ $attribute['att'] ], $attribute['default'], $tag . ' default for ' . $attribute['att'] );
			}
		}
		$manifest_tags = array_keys( $entries );
		sort( $tags );
		sort( $manifest_tags );
		$this->assertSame( $tags, $manifest_tags, 'No unregistered tags belong in the manifest.' );
	}

	public function test_every_reading_with_a_generated_form_supports_visitors(): void {
		foreach ( $this->entries() as $tag => $entry ) {
			$class          = Registrar::HERO_SHORTCODES[ $tag ] ?? null;
			$hero_form      = $class ? str_replace( '\\Heroes\\', '\\Forms\\', $class ) . 'Form' : '';
			$operation_form = '\\RoxyAPI\\Generated\\Forms\\' . ucfirst( $entry['operationId'] ?? '' ) . 'Form';
			if ( class_exists( $hero_form ) || class_exists( $operation_form ) ) {
				$this->assertContains( 'visitor', $entry['modes'], $tag );
			}
			if ( $entry['formOnly'] ) {
				$this->assertSame( array( 'visitor' ), $entry['modes'], $tag );
				$this->assertSame( array(), $entry['attributes'], $tag );
			}
		}
		$this->assertSame( array( 'none' ), $this->entries()['roxy_moon_phase']['modes'] );
		$this->assertNull( $this->entries()['roxy_horoscope']['operationId'] );
	}

	public function test_display_and_builder_owned_attributes_are_not_reading_inputs(): void {
		$manifest = Manifest::all();
		$this->assertArrayNotHasKey( 'version', $manifest );
		foreach ( $manifest['domains'] as $domain ) {
			$this->assertSame( array( 'label' ), array_keys( $domain ) );
		}
		$this->assertArrayNotHasKey( 'reservedAttributes', $manifest );
		$this->assertArrayNotHasKey( 'parts', $manifest );
		foreach ( $manifest['entries'] as $order => $entry ) {
			$this->assertSame( $order, $entry['order'] );
			$this->assertArrayNotHasKey( 'parts', $entry, $entry['tag'] );
			foreach ( $entry['attributes'] as $attribute ) {
				$this->assertArrayNotHasKey( 'label_key', $attribute );
				$this->assertArrayNotHasKey( 'enum_key', $attribute );
			}
			$this->assertArrayHasKey( $entry['domain'], $manifest['domains'] );
			$this->assertNotContains( 'lang', array_column( $entry['attributes'], 'att' ), $entry['tag'] );
			$this->assertNotContains( 'mode', array_column( $entry['attributes'], 'att' ), $entry['tag'] );
			$this->assertSame( array(), array_values( array_intersect( ComponentRenderer::RESERVED_ATTS, array_column( $entry['attributes'], 'att' ) ) ) );
		}
	}

	public function test_json_and_php_share_the_english_metadata(): void {
		$locale_filter = static fn() => 'en_US';
		add_filter( 'determine_locale', $locale_filter );
		try {
			$json = wp_json_file_decode( dirname( __DIR__, 2 ) . '/builder/manifest.json', array( 'associative' => true ) );
			$this->assertIsArray( $json );
			$this->assertSame( $json, Manifest::all() );
		} finally {
			remove_filter( 'determine_locale', $locale_filter );
		}
	}

	public function test_spanish_locale_translates_reading_titles_and_existing_domain_labels(): void {
		$locale_filter = static fn() => 'es_ES';
		add_filter( 'pre_determine_locale', $locale_filter );
		unload_textdomain( 'roxyapi' );
		try {
			Plugin::load_textdomain();
			$this->assertTrue( is_textdomain_loaded( 'roxyapi' ) );
			$manifest = Manifest::all();
			$entries  = array_column( $manifest['entries'], null, 'tag' );
			$this->assertSame( 'Horóscopo diario', $entries['roxy_horoscope']['title'] );
			$this->assertSame( 'Numerología', $manifest['domains']['Numerology']['label'] );
			$this->assertSame( 'Biorritmo', $manifest['domains']['Biorhythm']['label'] );
		} finally {
			remove_filter( 'pre_determine_locale', $locale_filter );
			unload_textdomain( 'roxyapi' );
		}
	}

	public function test_labels_follow_the_screen_locale_and_locations_name_attributes(): void {
		$locale_filter = static fn() => 'hi_IN';
		add_filter( 'determine_locale', $locale_filter );
		try {
			$labels = FormLabels::for_language( Language::from_locale( determine_locale() ) );
			$entry  = $this->entries()['roxy_natal_chart'];
			$fields = array_column( $entry['attributes'], null, 'att' );
			$this->assertSame( $labels['fields']['latitude'], $fields['lat']['label'] );
		} finally {
			remove_filter( 'determine_locale', $locale_filter );
		}
		$this->assertSame( 'latitude', $fields['lat']['location']['role'] );
		$this->assertSame( 'longitude', $fields['lon']['location']['role'] );
		$this->assertSame( 'timezone', $fields['tz']['location']['role'] );
		$this->assertSame( $fields['lat']['location']['group'], $fields['lon']['location']['group'] );
		$this->assertSame( $fields['lat']['location']['group'], $fields['tz']['location']['group'] );
		$this->assertFalse( $fields['tz']['required'], 'Static hero defaults supply the timezone.' );
	}
}
