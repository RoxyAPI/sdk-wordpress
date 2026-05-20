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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Generated\Client as GeneratedClient;
use RoxyAPI\Support\ComponentRenderer;
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
		'sign'   => '',
		'date'   => 'today',
		'period' => 'daily',
	);

	/**
	 * Map a `period` attribute to the matching API operationId. Anything
	 * the API does not actually expose (legacy `chinese`, `love`, `career`,
	 * etc.) silently falls back to `daily` so a stale shortcode keeps
	 * rendering instead of erroring out.
	 *
	 * @var array<string, string>
	 */
	private const PERIOD_OPS = array(
		'daily'   => 'getDailyHoroscope',
		'weekly'  => 'getWeeklyHoroscope',
		'monthly' => 'getMonthlyHoroscope',
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
				Sanitize::date_string( $atts['date'] ),
				(string) ( $atts['period'] ?? 'daily' )
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

		return self::render_result( $sign, Sanitize::date_string( 'today' ), 'daily' ) . self::render_form( $sign );
	}

	private static function render_result( string $sign, string $date, string $period = 'daily' ): string {
		$op_id = self::PERIOD_OPS[ $period ] ?? 'getDailyHoroscope';

		// Each period dispatches to its own operation. All three map to
		// roxy-horoscope-card via the component map, so ComponentRenderer emits
		// the web component (with a server-rendered fallback) and handles the
		// disclaimer and attribution. Unmapped or empty responses degrade to the
		// generic card renderer inside ComponentRenderer.
		if ( $op_id === 'getWeeklyHoroscope' ) {
			$data = GeneratedClient::getWeeklyHoroscope( $sign );
		} elseif ( $op_id === 'getMonthlyHoroscope' ) {
			$data = GeneratedClient::getMonthlyHoroscope( $sign );
		} else {
			$data = GeneratedClient::getDailyHoroscope( $sign, null, $date );
		}

		if ( is_wp_error( $data ) ) {
			return Templates::api_error( $data );
		}

		return ComponentRenderer::render( $op_id, is_array( $data ) ? $data : array() );
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
