<?php
/**
 * Named transformer registry used by the auto-generated hero shortcodes.
 *
 * Hero shortcodes occasionally need to map a single user-facing attribute to
 * multiple SaaS request fields. The classic example is `birth_date="1990-05-15"`
 * splitting into integer `year`, `month`, and `day`. Rather than encoding that
 * logic in JSON, the generator emits a call to a named transformer here. Each
 * transformer is a pure function taking the raw value and returning either an
 * associative array of field-name to value pairs (success) or null (validation
 * failure that the caller renders as a Templates::error).
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HeroTransforms {

	/**
	 * Split a YYYY-MM-DD birth date into integer year/month/day fields.
	 *
	 * Used by the Numerology and Life Path heroes whose SaaS endpoints take
	 * three integer parts rather than a date string. Returns null if the input
	 * does not match the expected pattern after Sanitize::date_string runs;
	 * the caller surfaces a clear "must be YYYY-MM-DD" error in that case.
	 *
	 * @param string $birth_date Raw input.
	 * @return array{year:int,month:int,day:int}|null
	 */
	public static function split_iso_date_into_year_month_day( string $birth_date ): ?array {
		$value = Sanitize::date_string( $birth_date, '' );
		if ( $value === '' || ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m ) ) {
			return null;
		}
		return array(
			'year'  => (int) $m[1],
			'month' => (int) $m[2],
			'day'   => (int) $m[3],
		);
	}
}
