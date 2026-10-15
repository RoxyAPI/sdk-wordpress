<?php
/**
 * Preserve the shortcode and posted form names shipped in 1.20.0.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Shortcodes\Registrar;

class Test_Backwards_Compat extends \WP_UnitTestCase {

	/**
	 * Load the frozen contract rather than deriving expectations from current code.
	 *
	 * @return array<string, mixed>
	 */
	private function contract(): array {
		$contract = json_decode( file_get_contents( __DIR__ . '/fixtures/contract-1.20.0.json' ), true );
		$this->assertIsArray( $contract, 'The 1.20.0 contract must decode successfully.' );
		$this->assertNotEmpty( $contract['shortcodes'], 'The shortcode contract must not be empty.' );
		$this->assertNotEmpty( $contract['forms'], 'The form contract must not be empty.' );
		return $contract;
	}

	/**
	 * Run registration hooks only when a contracted tag is missing.
	 *
	 * @param array<string, array<string>> $shortcodes Contracted shortcode attributes.
	 */
	private function ensure_registered( array $shortcodes ): void {
		foreach ( array_keys( $shortcodes ) as $tag ) {
			if ( ! shortcode_exists( $tag ) ) {
				do_action( 'init' );
				return;
			}
		}
	}

	public function test_every_shipped_shortcode_is_still_registered(): void {
		$shortcodes = $this->contract()['shortcodes'];
		$this->ensure_registered( $shortcodes );
		$missing = array_filter( array_keys( $shortcodes ), static fn( $tag ) => ! shortcode_exists( $tag ) );

		$this->assertSame( array(), $missing, 'Missing 1.20.0 shortcode tags: ' . implode( ', ', $missing ) );
	}

	public function test_every_shipped_shortcode_attribute_is_still_accepted(): void {
		global $shortcode_tags;

		$shortcodes = $this->contract()['shortcodes'];
		$this->ensure_registered( $shortcodes );
		foreach ( $shortcodes as $tag => $attributes ) {
			$this->assertTrue( shortcode_exists( $tag ), 'Missing 1.20.0 shortcode tag: ' . $tag );
			if ( isset( Registrar::HERO_SHORTCODES[ $tag ] ) ) {
				$class = Registrar::HERO_SHORTCODES[ $tag ];
			} else {
				$callback = $shortcode_tags[ $tag ];
				$this->assertIsArray( $callback, $tag . ' must expose its shortcode callback class.' );
				$class = is_object( $callback[0] ) ? get_class( $callback[0] ) : $callback[0];
			}
			$this->assertTrue( defined( $class . '::DEFAULTS' ), $tag . ' is missing DEFAULTS.' );
			$missing = array_values( array_diff( $attributes, array_keys( $class::DEFAULTS ) ) );

			$this->assertSame( array(), $missing, $tag . ' is missing 1.20.0 attributes: ' . implode( ', ', $missing ) );
		}
	}

	public function test_every_shipped_form_and_posted_field_still_exists(): void {
		foreach ( $this->contract()['forms'] as $op_id => $fields ) {
			$form = '\\RoxyAPI\\Generated\\Forms\\' . ucfirst( $op_id ) . 'Form';
			$this->assertTrue( class_exists( $form ), $op_id . ' is missing its 1.20.0 form class: ' . $form );
			$spec  = $form::spec();
			$names = array_column( $spec['flat_fields'] ?? array(), 'name' );
			foreach ( $spec['sections'] ?? array() as $section ) {
				foreach ( $section['fields'] as $field ) {
					$names[] = $section['name'] . '[' . $field['name'] . ']';
				}
			}
			$missing = array_values( array_diff( $fields, $names ) );

			$this->assertSame( array(), $missing, $op_id . ' is missing 1.20.0 posted fields: ' . implode( ', ', $missing ) );
		}
	}
}
