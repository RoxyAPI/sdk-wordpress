<?php
/**
 * The builder describes every registered shortcode without inventing attributes.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Generated\Builder\Manifest;
use RoxyAPI\Generated\ComponentMap;
use RoxyAPI\Generated\FormLabels;
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

	public function test_every_registered_tag_and_defaults_key_is_described(): void {
		global $shortcode_tags;

		$entries = $this->entries();
		$tags    = array();
		foreach ( $shortcode_tags as $tag => $callback ) {
			if ( strpos( $tag, 'roxy_' ) !== 0 ) {
				continue;
			}
			$tags[] = $tag;
			$this->assertArrayHasKey( $tag, $entries, $tag );
			$class = Registrar::HERO_SHORTCODES[ $tag ] ?? $callback[0];
			$class = is_object( $class ) ? get_class( $class ) : $class;
			$this->assertTrue( defined( $class . '::DEFAULTS' ), $tag );
			$expected = array_diff( array_keys( $class::DEFAULTS ), ComponentRenderer::RESERVED_ATTS );
			$actual   = array_column( $entries[ $tag ]['attributes'], 'att' );
			$this->assertCount( count( array_unique( $actual ) ), $actual, $tag . ' repeats an attribute.' );
			sort( $expected );
			sort( $actual );
			$this->assertSame( $expected, $actual, $tag . ' must describe exactly DEFAULTS minus display attributes.' );
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

	public function test_reserved_display_attributes_are_described_once(): void {
		$manifest = Manifest::all();
		$this->assertSame( ComponentRenderer::RESERVED_ATTS, array_column( $manifest['reservedAttributes'], 'att' ) );
		$this->assertNotEmpty( $manifest['parts'] );
		$this->assertSame( ComponentMap::parts(), $manifest['parts'] );
		foreach ( $manifest['entries'] as $order => $entry ) {
			$this->assertSame( $order, $entry['order'] );
			$this->assertArrayHasKey( 'parts', $entry, $entry['tag'] );
			$this->assertIsArray( $entry['parts'], $entry['tag'] );
			$this->assertNotSame( $manifest['parts'], $entry['parts'], $entry['tag'] . ' must not repeat the global parts vocabulary.' );
			$this->assertSame( array(), array_values( array_diff( $entry['parts'], $manifest['parts'] ) ), $entry['tag'] );
			$this->assertCount( count( array_unique( $entry['parts'] ) ), $entry['parts'], $entry['tag'] );
			$this->assertArrayHasKey( $entry['domain'], $manifest['domains'] );
			$this->assertSame( array(), array_values( array_intersect( ComponentRenderer::RESERVED_ATTS, array_column( $entry['attributes'], 'att' ) ) ) );
		}
	}

	public function test_reading_parts_match_the_vendored_components_including_hand_written_horoscope(): void {
		$entries = $this->entries();
		$map     = wp_json_file_decode( dirname( __DIR__, 2 ) . '/bin/component-map.json', array( 'associative' => true ) );
		$heroes  = \RoxyAPI\Generated\Heroes\Manifest::all();
		foreach ( array( 'roxy_natal_chart', 'roxy_calculate_luck_pillars', 'roxy_horoscope' ) as $tag ) {
			$operation = $entries[ $tag ]['operationId'] ?? $heroes[ $tag ]['operation_id'];
			$expected  = array();
			foreach ( ComponentMap::for( $operation ) as $row ) {
				$this->assertArrayHasKey( $row['component'], $map['_meta']['component_parts'] );
				$expected = array_merge( $expected, $map['_meta']['component_parts'][ $row['component'] ] );
			}
			$expected = array_values( array_unique( $expected ) );
			sort( $expected );
			$this->assertNotEmpty( $expected, $tag );
			$this->assertSame( $expected, $entries[ $tag ]['parts'], $tag );
		}
		$this->assertSame( array(), $entries['roxy_generate_composite_chart']['parts'], 'An unmapped reading must not inherit global parts.' );
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
