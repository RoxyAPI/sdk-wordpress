<?php
/**
 * Shared sanitization helpers for shortcode and block attributes.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

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
	 * Sanitize a value to a valid date string (YYYY-MM-DD, today, tomorrow, yesterday).
	 *
	 * @param mixed  $value   Raw input value.
	 * @param string $fallback Fallback if invalid.
	 * @return string
	 */
	public static function date_string( $value, string $fallback = 'today' ): string {
		$value = sanitize_text_field( (string) $value );
		if ( $value === '' || $value === 'today' || $value === 'tomorrow' || $value === 'yesterday' ) {
			return $value === '' ? $fallback : $value;
		}
		return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : $fallback;
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
	 * Convert an associative array of attributes to a shortcode attribute string.
	 *
	 * @param array<string, string> $atts Attribute key-value pairs.
	 * @return string Shortcode attribute string like 'key="value" key2="value2"'.
	 */
	public static function attributes_to_string( array $atts ): string {
		$parts = array();
		foreach ( $atts as $key => $value ) {
			if ( $value === '' || $value === null ) {
				continue;
			}
			$parts[] = sanitize_key( $key ) . '="' . esc_attr( (string) $value ) . '"';
		}
		return implode( ' ', $parts );
	}
}
