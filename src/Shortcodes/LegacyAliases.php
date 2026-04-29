<?php
/**
 * Backwards-compat shortcodes for v1.0 heroes that lost hero status in v1.1.
 *
 * Three v1.0 heroes were demoted to long-tail in the v1.1 hero rewrite:
 *   `[roxy_iching]`   - I Ching cast / hexagram lookup
 *   `[roxy_dream]`    - dream symbol search / lookup
 *   `[roxy_crystal]`  - crystal lookup by slug
 *
 * Per `~/per/roxyapi-simple/docs/apis.md` the underlying endpoints are
 * commodity content. The hero slot is better spent on Vedic kundli /
 * panchang / gun-milan and tarot yes-no. The endpoints themselves stay
 * available via auto-generated long-tail shortcodes (e.g.
 * `[roxy_cast_reading]`, `[roxy_get_dream_symbol]`,
 * `[roxy_get_crystal]`).
 *
 * Registering the legacy tags here keeps any v1.0 user page rendering
 * after the upgrade. The dispatch mirrors the v1.0 hero behaviour byte
 * for byte: same attribute names, same routing, same response. Removed
 * in v2.0; documented in MAINTAINER-v1.1-money-heroes.md "Backwards
 * compatibility".
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Generated\Client as GeneratedClient;
use RoxyAPI\Support\GenericRenderer;
use RoxyAPI\Support\Sanitize;
use RoxyAPI\Support\Templates;

class LegacyAliases {

	public static function register(): void {
		add_action( 'init', array( self::class, 'register_aliases' ), 10 );
	}

	public static function register_aliases(): void {
		foreach ( array( 'roxy_iching', 'roxy_dream', 'roxy_crystal' ) as $tag ) {
			if ( shortcode_exists( $tag ) ) {
				continue;
			}
			// Closure wrapper (mirrors Registrar::register_hero) so the
			// callback's exact signature stays opaque to static analysis;
			// add_shortcode's WP-stub typing of `array<string>|''` for $atts
			// otherwise complains about our broader array<string, mixed>.
			add_shortcode(
				$tag,
				static function ( $atts, $content, $shortcode_tag ): string {
					return self::render( $atts, $content ?? '', (string) $shortcode_tag );
				}
			);
		}
	}

	/**
	 * Single render entry-point. Dispatches by shortcode tag because the
	 * three legacy aliases share the same callable but different routing.
	 *
	 * @param array<string, string>|string $atts          Shortcode attributes.
	 * @param string                       $content       Inner content (unused).
	 * @param string                       $shortcode_tag Tag the shortcode was invoked under.
	 * @return string
	 */
	public static function render( $atts, $content = '', $shortcode_tag = '' ): string {
		wp_enqueue_style( 'roxyapi-frontend' );
		$atts = is_array( $atts ) ? $atts : array();

		switch ( (string) $shortcode_tag ) {
			case 'roxy_iching':
				return self::render_iching( $atts );
			case 'roxy_dream':
				return self::render_dream( $atts );
			case 'roxy_crystal':
				return self::render_crystal( $atts );
		}
		return '';
	}

	/**
	 * Render the iching legacy alias. Mirrors v1.0 dispatch:
	 * `number` set → `getHexagram`, else default cast → `castReading`.
	 *
	 * @param array<string, mixed> $atts Shortcode attributes.
	 * @return string
	 */
	private static function render_iching( array $atts ): string {
		$number = isset( $atts['number'] ) ? Sanitize::non_negative_int( (string) $atts['number'] ) : '';
		if ( $number !== '' ) {
			$data = GeneratedClient::getHexagram( $number );
			if ( is_wp_error( $data ) ) {
				return Templates::api_error( $data );
			}
			return GenericRenderer::render( 'getHexagram', is_array( $data ) ? $data : array() );
		}
		$data = GeneratedClient::castReading();
		if ( is_wp_error( $data ) ) {
			return Templates::api_error( $data );
		}
		return GenericRenderer::render( 'castReading', is_array( $data ) ? $data : array() );
	}

	/**
	 * Render the dream legacy alias. Mirrors v1.0 dispatch:
	 * `id` set → `getDreamSymbol`, `symbol` set → `searchDreamSymbols`,
	 * neither → friendly error with the canonical example.
	 *
	 * @param array<string, mixed> $atts Shortcode attributes.
	 * @return string
	 */
	private static function render_dream( array $atts ): string {
		$id     = isset( $atts['id'] ) ? Sanitize::slug( (string) $atts['id'] ) : '';
		$symbol = isset( $atts['symbol'] ) ? Sanitize::bounded_text( (string) $atts['symbol'], 200 ) : '';
		if ( $id !== '' ) {
			$data = GeneratedClient::getDreamSymbol( $id );
			if ( is_wp_error( $data ) ) {
				return Templates::api_error( $data );
			}
			return GenericRenderer::render( 'getDreamSymbol', is_array( $data ) ? $data : array() );
		}
		if ( $symbol !== '' ) {
			$data = GeneratedClient::searchDreamSymbols( $symbol );
			if ( is_wp_error( $data ) ) {
				return Templates::api_error( $data );
			}
			return GenericRenderer::render( 'searchDreamSymbols', is_array( $data ) ? $data : array() );
		}
		return Templates::error(
			esc_html__( 'Either the symbol or id attribute is required. Example: [roxy_dream symbol="water"]', 'roxyapi' )
		);
	}

	/**
	 * Render the crystal legacy alias. Mirrors v1.0 behaviour:
	 * `name` required → `getCrystal`, missing → friendly error.
	 *
	 * @param array<string, mixed> $atts Shortcode attributes.
	 * @return string
	 */
	private static function render_crystal( array $atts ): string {
		$name = isset( $atts['name'] ) ? Sanitize::slug( (string) $atts['name'] ) : '';
		if ( $name === '' ) {
			return Templates::error(
				esc_html__( 'The name attribute is required. Example: [roxy_crystal name="amethyst"]', 'roxyapi' )
			);
		}
		$data = GeneratedClient::getCrystal( $name );
		if ( is_wp_error( $data ) ) {
			return Templates::api_error( $data );
		}
		return GenericRenderer::render( 'getCrystal', is_array( $data ) ? $data : array() );
	}
}
