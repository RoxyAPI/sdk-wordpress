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
		'sign'          => '',
		'date'          => 'today',
		'period'        => 'daily',
		'hide_readings' => ComponentRenderer::INHERIT,
		'hide_sections' => ComponentRenderer::INHERIT,
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
		'yearly'  => 'getYearlyHoroscope',
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
		$submitted = self::handle_submission( $atts );
		if ( $submitted !== null ) {
			return $submitted;
		}

		// Static mode: site owner passed a sign attribute.
		if ( $atts['sign'] !== '' ) {
			return self::render_result( Sanitize::zodiac_sign( $atts['sign'] ), $atts );
		}

		// Form mode: no sign given, render the picker form.
		return self::render_form();
	}

	/**
	 * Handle the visitor sign-picker POST.
	 *
	 * @param array<string, mixed> $atts Resolved shortcode attributes, carried so the
	 *                                   reserved display attributes survive the POST.
	 * @return string|null Rendered result, or null when this request is not a submission.
	 */
	private static function handle_submission( array $atts ): ?string {
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

		return self::render_result( $sign, $atts ) . self::render_form( $sign );
	}

	/**
	 * Fetch and render one horoscope period.
	 *
	 * @param string               $sign Sanitised zodiac sign slug.
	 * @param array<string, mixed> $atts Resolved shortcode attributes: `period` and
	 *                                   `date` are read here, so the visitor form and
	 *                                   the static placement resolve them the same way,
	 *                                   and the reserved display attributes are read by
	 *                                   `ComponentRenderer::render_atts()`, so a new one
	 *                                   needs no change here.
	 * @return string
	 */
	private static function render_result( string $sign, array $atts ): string {
		$op_id = self::PERIOD_OPS[ (string) $atts['period'] ] ?? 'getDailyHoroscope';
		$date  = self::period_anchor( Sanitize::date_string( (string) $atts['date'] ), $op_id );

		// Each period dispatches to its own operation. All four map to
		// roxy-horoscope-card via the component map, so ComponentRenderer emits
		// the web component (with a server-rendered fallback) and handles the
		// disclaimer and attribution. Unmapped or empty responses degrade to the
		// generic card renderer inside ComponentRenderer.
		if ( $op_id === 'getWeeklyHoroscope' ) {
			$data = GeneratedClient::getWeeklyHoroscope( $sign, null, $date );
		} elseif ( $op_id === 'getMonthlyHoroscope' ) {
			$data = GeneratedClient::getMonthlyHoroscope( $sign, null, $date );
		} elseif ( $op_id === 'getYearlyHoroscope' ) {
			$data = GeneratedClient::getYearlyHoroscope( $sign, null, substr( $date, 0, 4 ) );
		} else {
			$data = GeneratedClient::getDailyHoroscope( $sign, null, $date );
		}

		if ( is_wp_error( $data ) ) {
			return Templates::api_error( $data );
		}

		return ComponentRenderer::render_atts( $op_id, is_array( $data ) ? $data : array(), $atts );
	}

	/**
	 * The first day of the period a date falls in, for the operation that reads it.
	 *
	 * One `date` attribute serves every period, and it is sent on every call:
	 * the day itself, any day of the Monday to Sunday week, any day of the
	 * month, or the year it falls in. Sending it is what makes the current
	 * period roll over on the site clock, since `Sanitize::date_string()`
	 * resolves `today` in the site timezone while the API rolls over at 00:00
	 * UTC when the date is omitted. Anchoring it is what keeps the cache to one
	 * entry, and one metered call, per period: the API returns the same week
	 * for any of its seven days, so a key carrying the day would call through
	 * seven times.
	 *
	 * @param string $date  YYYY-MM-DD.
	 * @param string $op_id The period operation.
	 * @return string YYYY-MM-DD.
	 */
	private static function period_anchor( string $date, string $op_id ): string {
		$day = \DateTimeImmutable::createFromFormat( '!Y-m-d', $date, wp_timezone() );
		if ( $day === false ) {
			return $date;
		}
		switch ( $op_id ) {
			case 'getWeeklyHoroscope':
				return $day->modify( '-' . ( (int) $day->format( 'N' ) - 1 ) . ' days' )->format( 'Y-m-d' );
			case 'getMonthlyHoroscope':
				return $day->format( 'Y-m-01' );
			case 'getYearlyHoroscope':
				return $day->format( 'Y-01-01' );
			default:
				return $date;
		}
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
