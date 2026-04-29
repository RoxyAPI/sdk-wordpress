<?php
/**
 * Regression test: every hero shortcode example surfaced in the admin
 * onboarding/connected pages MUST use only attribute names declared in
 * the matching class's `DEFAULTS` constant.
 *
 * The activation cliff this test guards against: a user copy-pastes the
 * canonical example from the WP admin and the shortcode silently drops
 * keys it does not recognise (or fires a required-attribute error). Any
 * future rename or copy edit that drifts from the class defaults goes
 * red here at PHPUnit time, not in the wild.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\Onboarding;
use RoxyAPI\Shortcodes\Registrar;

class Test_Hero_Attr_Contract extends \WP_UnitTestCase {

	/**
	 * Every example in `Onboarding::hero_shortcodes()` must parse to a tag
	 * that exists in `Registrar::HERO_SHORTCODES` and use only attribute
	 * keys present in that class's `DEFAULTS` constant.
	 */
	public function test_every_hero_example_uses_only_declared_attributes(): void {
		$heroes = Onboarding::hero_shortcodes();
		$this->assertNotEmpty( $heroes, 'Onboarding::hero_shortcodes() must not be empty.' );

		foreach ( $heroes as $hero ) {
			$this->assertArrayHasKey( 'code', $hero, 'Each hero entry must carry a code example.' );
			$code = (string) $hero['code'];

			$tag = $this->extract_tag( $code );
			$this->assertNotSame( '', $tag, "Could not extract a shortcode tag from example: {$code}" );

			$registry = Registrar::HERO_SHORTCODES;
			$this->assertArrayHasKey(
				$tag,
				$registry,
				"Hero example uses tag '{$tag}' that is not registered in Registrar::HERO_SHORTCODES."
			);

			$class = $registry[ $tag ];
			$this->assertTrue(
				defined( $class . '::DEFAULTS' ),
				"Hero class {$class} must declare a public const DEFAULTS array so this contract can be enforced."
			);

			/** @var array<string, mixed> $defaults */
			$defaults = $class::DEFAULTS;
			$this->assertIsArray( $defaults, "{$class}::DEFAULTS must be an array." );

			$inside = $this->extract_inside_brackets( $code );
			$parsed = shortcode_parse_atts( $inside );
			if ( ! is_array( $parsed ) ) {
				$parsed = array();
			}
			// shortcode_parse_atts can include numeric-indexed bare values; we
			// only care about the named attributes here.
			$parsed_keys = array_filter(
				array_keys( $parsed ),
				static function ( $k ) {
					return is_string( $k );
				}
			);

			foreach ( $parsed_keys as $key ) {
				$this->assertArrayHasKey(
					$key,
					$defaults,
					"Hero example for '{$tag}' uses attribute '{$key}' that is not in {$class}::DEFAULTS. "
						. 'Either add the key to DEFAULTS (and consume it in render()) or fix the example in Onboarding::hero_shortcodes().'
				);
			}
		}
	}

	/**
	 * Every required attribute (declared in DEFAULTS with an empty-string
	 * default) must be supplied by the canonical example so the user's
	 * copy-paste does not hit the required-attribute error.
	 */
	public function test_every_required_attribute_is_supplied_by_the_example(): void {
		$heroes = Onboarding::hero_shortcodes();

		foreach ( $heroes as $hero ) {
			$code = (string) $hero['code'];
			$tag  = $this->extract_tag( $code );
			$registry = Registrar::HERO_SHORTCODES;
			$this->assertArrayHasKey( $tag, $registry, "Tag {$tag} not in registrar." );

			$class    = $registry[ $tag ];
			$defaults = $class::DEFAULTS;

			$inside = $this->extract_inside_brackets( $code );
			$parsed = shortcode_parse_atts( $inside );
			if ( ! is_array( $parsed ) ) {
				$parsed = array();
			}

			foreach ( $defaults as $key => $default_value ) {
				if ( $default_value !== '' ) {
					// Attribute has a non-empty default, so it is optional.
					continue;
				}
				// IChing is the documented exception: it accepts `question` OR
				// `number` OR neither. The class casts a random reading when
				// neither is supplied. So both are optional even though the
				// default is the empty string.
				if ( $tag === 'roxy_iching' ) {
					continue;
				}
				// Dream accepts `symbol` OR `id`. The example always supplies
				// one; require at least one of them.
				if ( $tag === 'roxy_dream' ) {
					$has_either = isset( $parsed['symbol'] ) || isset( $parsed['id'] );
					$this->assertTrue(
						$has_either,
						"Dream example must supply either 'symbol' or 'id': {$code}"
					);
					break;
				}
				$this->assertArrayHasKey(
					$key,
					$parsed,
					"Hero example for '{$tag}' must supply required attribute '{$key}': {$code}"
				);
			}
		}
	}

	/**
	 * The hero list grew from 10 (v1.0) to 17 (v1.1 money-hero rewrite).
	 * Onboarding (sourced from Manifest) and Registrar (hand-maintained
	 * tag → class map) must agree exactly: any drift means a new hero was
	 * added in only one place and will not register at runtime.
	 */
	public function test_hero_list_onboarding_and_registrar_agree(): void {
		$this->assertCount(
			count( Registrar::HERO_SHORTCODES ),
			Onboarding::hero_shortcodes(),
			'Onboarding hero count must equal Registrar HERO_SHORTCODES count.'
		);
	}

	/**
	 * Extract the bare shortcode tag from a sample like
	 * `[roxy_horoscope sign="aries"]`.
	 */
	private function extract_tag( string $sample ): string {
		if ( preg_match( '/^\[\s*([a-z0-9_]+)/i', $sample, $matches ) === 1 ) {
			return strtolower( $matches[1] );
		}
		return '';
	}

	/**
	 * Pull the attribute portion out of a `[tag attr="value"]` string so it
	 * can be fed to `shortcode_parse_atts`.
	 */
	private function extract_inside_brackets( string $sample ): string {
		if ( preg_match( '/^\[\s*[a-z0-9_]+\s*(.*)\]\s*$/i', $sample, $matches ) === 1 ) {
			return trim( $matches[1] );
		}
		return '';
	}
}
