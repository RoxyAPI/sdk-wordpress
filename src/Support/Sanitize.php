<?php
/**
 * Shared sanitization helpers for shortcode and block attributes.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sanitize {

	public const ZODIAC_SIGNS = array(
		'aries',
		'taurus',
		'gemini',
		'cancer',
		'leo',
		'virgo',
		'libra',
		'scorpio',
		'sagittarius',
		'capricorn',
		'aquarius',
		'pisces',
	);

	/**
	 * Sanitize a value to a valid zodiac sign slug.
	 *
	 * @param mixed  $value   Raw input value.
	 * @param string $fallback Fallback sign if invalid.
	 * @return string
	 */
	public static function zodiac_sign( $value, string $fallback = 'aries' ): string {
		$slug = sanitize_key( (string) $value );
		return in_array( $slug, self::ZODIAC_SIGNS, true ) ? $slug : $fallback;
	}

	/**
	 * Title-case zodiac sign sanitiser. Always returns "Aries", "Taurus",
	 * etc. — uppercase first letter, the rest lowercase. Used by endpoints
	 * whose OpenAPI enum is strict Title Case (e.g. `/crystals/zodiac/{sign}`).
	 *
	 * The horoscope domain uses lowercase enums and stays on `zodiac_sign`.
	 * Per-domain spec authoring inconsistency, not a renderer problem.
	 *
	 * @param mixed  $value    Raw input value.
	 * @param string $fallback Fallback sign if invalid (Title Case expected).
	 * @return string
	 */
	public static function zodiac_sign_title( $value, string $fallback = 'Aries' ): string {
		$slug = self::zodiac_sign( $value, strtolower( $fallback ) );
		return ucfirst( $slug );
	}

	/**
	 * Sanitize a value to a YYYY-MM-DD date string.
	 *
	 * Always returns YYYY-MM-DD on the way OUT, regardless of what came IN.
	 * Accepted inputs: an explicit YYYY-MM-DD, the named days "today",
	 * "tomorrow", "yesterday", or empty (treated as fallback). Named days are
	 * resolved using `wp_date()` so the site's timezone wins.
	 *
	 * The RoxyAPI spec requires every `date` query param to match
	 * `/^\d{4}-\d{2}-\d{2}$/`. Sending bare "today" causes a 400. This helper
	 * is the single guard for every hero shortcode.
	 *
	 * @param mixed  $value    Raw input value.
	 * @param string $fallback Fallback name ("today", "tomorrow", "yesterday")
	 *                         or a literal YYYY-MM-DD string used when input
	 *                         is empty or unparseable.
	 * @return string Always a YYYY-MM-DD string.
	 */
	public static function date_string( $value, string $fallback = 'today' ): string {
		$value = sanitize_text_field( (string) $value );
		if ( $value === '' ) {
			$value = $fallback;
		}

		if ( in_array( $value, array( 'today', 'tomorrow', 'yesterday' ), true ) ) {
			$ts = strtotime( $value );
			return self::iso_date( $ts !== false ? $ts : null );
		}

		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			return $value;
		}

		return self::iso_date( null );
	}

	/**
	 * Return today's date (or the given timestamp) as YYYY-MM-DD in the site's
	 * timezone. Falls back to UTC `gmdate()` if `wp_date()` returns false (rare;
	 * only when the timezone string is invalid). Always returns a real string.
	 *
	 * @param int|null $timestamp Unix timestamp, or null for now.
	 * @return string YYYY-MM-DD
	 */
	private static function iso_date( ?int $timestamp ): string {
		$ts       = null === $timestamp ? time() : $timestamp;
		$resolved = wp_date( 'Y-m-d', $ts );
		return is_string( $resolved ) ? $resolved : gmdate( 'Y-m-d', $ts );
	}

	/**
	 * Sanitize a value to a valid latitude (-90 to 90).
	 *
	 * @param mixed $value Raw input value.
	 * @return float
	 */
	public static function latitude( $value ): float {
		$f = (float) $value;
		return $f >= -90.0 && $f <= 90.0 ? $f : 0.0;
	}

	/**
	 * Sanitize a value to a valid longitude (-180 to 180).
	 *
	 * @param mixed $value Raw input value.
	 * @return float
	 */
	public static function longitude( $value ): float {
		$f = (float) $value;
		return $f >= -180.0 && $f <= 180.0 ? $f : 0.0;
	}

	/**
	 * Sanitize a value to a 24-hour HH:MM:SS time string.
	 *
	 * Accepts either HH:MM or HH:MM:SS on input (humans type "14:30"; the SaaS
	 * spec requires the seconds component) and always returns HH:MM:SS on
	 * output. The RoxyAPI natal chart endpoint validates `time` against
	 * `^\d{2}:\d{2}:\d{2}$`; handing it bare HH:MM yields HTTP 400. Returning
	 * normalised HH:MM:SS from this helper means every hero downstream is
	 * wire-compliant without each one having to remember.
	 *
	 * @param mixed  $value    Raw input value (HH:MM or HH:MM:SS).
	 * @param string $fallback Fallback if input is unparseable. Same shape rules.
	 * @return string Always HH:MM:SS.
	 */
	public static function time_string( $value, string $fallback = '12:00:00' ): string {
		$value = sanitize_text_field( (string) $value );
		if ( preg_match( '/^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $value ) ) {
			return $value;
		}
		if ( preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $value ) ) {
			return $value . ':00';
		}
		// Fallback: accept the same two shapes; default to noon UTC if even the
		// caller-supplied fallback is malformed.
		if ( preg_match( '/^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $fallback ) ) {
			return $fallback;
		}
		if ( preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $fallback ) ) {
			return $fallback . ':00';
		}
		return '12:00:00';
	}

	/**
	 * Sanitize a value to a URL-safe slug.
	 *
	 * @param mixed $value Raw input value.
	 * @return string
	 */
	public static function slug( $value ): string {
		return sanitize_key( (string) $value );
	}

	/**
	 * Sanitize a timezone value into the SaaS-accepted form.
	 *
	 * Returns either:
	 *   - an IANA name string (e.g. "America/New_York", "UTC") matching the
	 *     spec regex `^[A-Za-z_]+(?:\/[A-Za-z0-9_+-]+){0,2}$`, or
	 *   - a decimal offset float in [-14, 14] (e.g. -5.0, 5.5).
	 *
	 * Both forms are accepted by the SaaS via `anyOf` per the OpenAPI spec.
	 * Invalid inputs fall back to 0.0 (UTC) — the safest default for a v1.0
	 * natal chart hero. Users who need accurate charts can pass
	 * `tz="America/New_York"` or `tz="-5"`. v1.1 will add city geocoding to
	 * resolve birth-location timezones automatically.
	 *
	 * @param mixed $value Raw input. Accepts IANA strings, offset strings
	 *                     ("+05:30"), or numeric strings ("-5", "5.5").
	 * @return string|float
	 */
	public static function timezone( $value ) {
		$str = sanitize_text_field( (string) $value );

		// IANA-format strings (the most accurate form).
		if ( preg_match( '/^[A-Za-z_]+(?:\/[A-Za-z0-9_+-]+){0,2}$/', $str ) ) {
			return $str;
		}

		// HH:MM offset (e.g. "+05:30" or "-04:00") — convert to decimal hours.
		if ( preg_match( '/^([+-])(\d{1,2}):(\d{2})$/', $str, $m ) ) {
			$sign  = '-' === $m[1] ? -1.0 : 1.0;
			$hours = (float) $m[2] + ( (float) $m[3] / 60.0 );
			$out   = $sign * $hours;
			if ( $out >= -14.0 && $out <= 14.0 ) {
				return $out;
			}
		}

		// Plain numeric (decimal hours).
		if ( is_numeric( $str ) ) {
			$out = (float) $str;
			if ( $out >= -14.0 && $out <= 14.0 ) {
				return $out;
			}
		}

		return 0.0;
	}

	/**
	 * Sanitize a value to a non-negative integer.
	 *
	 * Negatives clamp to zero (not absolute value). A user passing `year=-1990`
	 * gets `0`, which the SaaS will reject as missing, surfacing a clear error
	 * instead of silently treating the value as `1990`.
	 *
	 * @param mixed $value Raw input value.
	 * @return int
	 */
	public static function non_negative_int( $value ): int {
		return max( 0, (int) $value );
	}

	/**
	 * Sanitize a value to a bounded free-text string.
	 *
	 * @param mixed $value     Raw input value.
	 * @param int   $max_chars Maximum length in characters.
	 * @return string
	 */
	public static function bounded_text( $value, int $max_chars = 100 ): string {
		$text = sanitize_text_field( (string) $value );
		return mb_substr( $text, 0, $max_chars );
	}
}
