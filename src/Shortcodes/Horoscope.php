<?php
/**
 * Hero shortcode: [roxy_horoscope]
 *
 * Two modes, auto detected:
 *   Static:  [roxy_horoscope sign="aries"]  - site owner picks the sign
 *   Form:    [roxy_horoscope]               - visitors pick their own sign
 *
 * Form mode renders an HTML form. On submit, the plugin validates the nonce,
 * rate limits per IP, calls the API server side, and renders the result
 * above the form. No JavaScript required.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Shortcodes;

use RoxyAPI\Generated\Client as GeneratedClient;
use RoxyAPI\Support\Attribution;
use RoxyAPI\Support\Disclaimer;
use RoxyAPI\Support\RateLimit;
use RoxyAPI\Support\Sanitize;
use RoxyAPI\Support\Templates;

class Horoscope {

	public const ACTION = 'roxy_horoscope';

	/**
	 * Default attributes accepted by this shortcode. Every key listed here is
	 * the canonical attribute name surfaced in the documented examples.
	 *
	 * @var array<string, string>
	 */
	public const DEFAULTS = array(
		'sign' => '',
		'date' => 'today',
		'type' => 'general',
	);

	/**
	 * Render the horoscope shortcode.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @param string                       $content Inner content.
	 * @param string                       $tag Shortcode tag.
	 * @return string
	 */
	public static function render( $atts, $content = '', $tag = '' ): string {
		$atts = shortcode_atts(
			self::DEFAULTS,
			is_array( $atts ) ? $atts : array(),
			(string) $tag
		);

		wp_enqueue_style( 'roxyapi-frontend' );

		// Form submission: check $_POST first. Nonce + rate limit guarded.
		$submitted = self::handle_submission();
		if ( $submitted !== null ) {
			return $submitted;
		}

		// Static mode: site owner passed a sign attribute.
		if ( $atts['sign'] !== '' ) {
			return self::render_result(
				Sanitize::zodiac_sign( $atts['sign'] ),
				Sanitize::date_string( $atts['date'] )
			);
		}

		// Form mode: no sign given, render the picker form.
		return self::render_form();
	}

	private static function handle_submission(): ?string {
		if ( empty( $_POST['roxyapi_action'] ) || $_POST['roxyapi_action'] !== self::ACTION ) {
			return null;
		}
		$nonce = isset( $_POST['roxyapi_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['roxyapi_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::ACTION ) ) {
			return Templates::error( __( 'Security check failed. Please reload the page and try again.', 'roxyapi' ) );
		}
		if ( ! RateLimit::check( self::ACTION ) ) {
			return Templates::error( __( 'Too many requests. Please try again in an hour.', 'roxyapi' ) );
		}

		$raw_sign = isset( $_POST['sign'] ) ? sanitize_text_field( wp_unslash( $_POST['sign'] ) ) : '';
		$sign     = Sanitize::zodiac_sign( $raw_sign );

		return self::render_result( $sign, Sanitize::date_string( 'today' ) ) . self::render_form( $sign );
	}

	private static function render_result( string $sign, string $date ): string {
		$data = GeneratedClient::getDailyHoroscope( $sign, null, $date );

		if ( is_wp_error( $data ) ) {
			return Templates::api_error( $data );
		}

		$body_data = is_array( $data ) ? $data : array();
		return Templates::render(
			'horoscope',
			array(
				'sign' => $sign,
				'date' => $date,
				'data' => $body_data,
			)
		)
			. Disclaimer::render()
			. Attribution::credit_link( 'getDailyHoroscope' )
			. Attribution::jsonld( 'getDailyHoroscope', $body_data );
	}

	private static function render_form( string $selected = '' ): string {
		return Templates::render(
			'horoscope-form',
			array(
				'action'   => self::ACTION,
				'nonce'    => wp_create_nonce( self::ACTION ),
				'selected' => $selected,
				'signs'    => Sanitize::ZODIAC_SIGNS,
			)
		);
	}
}
