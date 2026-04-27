<?php
/**
 * Onboarding helpers shared between the Settings page and the Dashboard widget.
 *
 * Centralises the shortcode samples and hero domain list so the same copy
 * surfaces in both places. Domain ordering follows the brand book: astrology
 * first, then tarot, numerology, I Ching, dream interpretation, biorhythm,
 * angel numbers, crystals.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Onboarding {

	/**
	 * URL to the RoxyAPI pricing page with onboarding UTM parameters.
	 *
	 * @return string
	 */
	public static function signup_url(): string {
		return 'https://roxyapi.com/pricing?utm_source=wp-plugin&utm_medium=onboarding&utm_campaign=v1';
	}

	/**
	 * URL to the RoxyAPI dashboard.
	 *
	 * @return string
	 */
	public static function dashboard_url(): string {
		return 'https://roxyapi.com/account';
	}

	/**
	 * URL to the RoxyAPI Playground (test key pre-filled, no signup required).
	 *
	 * @return string
	 */
	public static function playground_url(): string {
		return 'https://roxyapi.com/api-reference?utm_source=wp-plugin&utm_medium=onboarding';
	}

	/**
	 * URL to the WordPress plugin documentation on roxyapi.com.
	 *
	 * @return string
	 */
	public static function docs_url(): string {
		return 'https://roxyapi.com/docs/integrations/wordpress';
	}

	/**
	 * URL to support.
	 *
	 * @return string
	 */
	public static function support_url(): string {
		return 'https://roxyapi.com/contact';
	}

	/**
	 * Quick-start shortcode samples for the onboarding step 3 and connected state.
	 *
	 * @return array<int, array{label: string, code: string}>
	 */
	public static function quickstart_samples(): array {
		return array(
			array(
				'label' => __( 'Daily horoscope', 'roxyapi' ),
				'code'  => '[roxy_horoscope sign="aries"]',
			),
			array(
				'label' => __( 'Three card tarot', 'roxyapi' ),
				'code'  => '[roxy_tarot_card spread="three"]',
			),
			array(
				'label' => __( 'Numerology reading', 'roxyapi' ),
				'code'  => '[roxy_numerology name="Ada Lovelace" birth_date="1815-12-10"]',
			),
		);
	}

	/**
	 * The ten hero shortcodes in v1.0, in brand-book domain order.
	 *
	 * Astrology first, then tarot, numerology, I Ching, dreams, biorhythm,
	 * angel numbers, crystals. Life path sits under the numerology umbrella
	 * and is kept at the end.
	 *
	 * Reads from the auto-generated `\RoxyAPI\Generated\Heroes\Manifest`
	 * which derives every entry from `bin/hero-config.json` at code-gen
	 * time. The order in `bin/hero-config.json` is the order shown here.
	 *
	 * Every example MUST use only attribute names that the matching hero
	 * class declares in its `DEFAULTS` constant. The
	 * `test-hero-attr-contract.php` regression test enforces this.
	 *
	 * @return array<int, array{title: string, code: string, description: string}>
	 */
	public static function hero_shortcodes(): array {
		if ( ! class_exists( '\\RoxyAPI\\Generated\\Heroes\\Manifest' ) ) {
			return array();
		}
		$out = array();
		foreach ( \RoxyAPI\Generated\Heroes\Manifest::all() as $entry ) {
			$out[] = array(
				'title'       => (string) $entry['title'],
				'code'        => (string) $entry['code'],
				'description' => (string) $entry['description'],
			);
		}
		return $out;
	}

	/**
	 * The five most-used hero shortcodes for the dashboard widget.
	 *
	 * @return array<int, array{title: string, code: string}>
	 */
	public static function widget_top_shortcodes(): array {
		$samples = array();
		foreach ( self::hero_shortcodes() as $hero ) {
			$samples[] = array(
				'title' => $hero['title'],
				'code'  => $hero['code'],
			);
			if ( count( $samples ) >= 5 ) {
				break;
			}
		}
		return $samples;
	}
}
