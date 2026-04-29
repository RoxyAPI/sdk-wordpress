<?php
/**
 * Tests for the Sanitize helpers.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Support\Sanitize;

class Test_Sanitize extends \WP_UnitTestCase {

	public function test_zodiac_sign_lowercase_kept(): void {
		$this->assertSame( 'aries', Sanitize::zodiac_sign( 'aries' ) );
	}

	public function test_zodiac_sign_mixed_case_normalized(): void {
		$this->assertSame( 'aries', Sanitize::zodiac_sign( 'Aries' ) );
		$this->assertSame( 'capricorn', Sanitize::zodiac_sign( 'CAPRICORN' ) );
	}

	public function test_zodiac_sign_invalid_uses_default_fallback(): void {
		$this->assertSame( 'aries', Sanitize::zodiac_sign( 'dragon' ) );
		$this->assertSame( 'aries', Sanitize::zodiac_sign( '' ) );
	}

	public function test_zodiac_sign_numeric_input_uses_fallback(): void {
		$this->assertSame( 'aries', Sanitize::zodiac_sign( 12345 ) );
	}

	public function test_zodiac_sign_custom_fallback(): void {
		$this->assertSame( 'leo', Sanitize::zodiac_sign( 'dragon', 'leo' ) );
	}

	public function test_date_string_resolves_named_days_to_iso(): void {
		// Named days resolve to a real YYYY-MM-DD via wp_date(). The SaaS
		// rejects literal "today"/"tomorrow"/"yesterday" with HTTP 400; only
		// the resolved form is wire-safe.
		$today_iso = wp_date( 'Y-m-d' );
		$this->assertSame( $today_iso, Sanitize::date_string( 'today' ) );
		$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}$/', Sanitize::date_string( 'tomorrow' ) );
		$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}$/', Sanitize::date_string( 'yesterday' ) );
		$this->assertNotSame( Sanitize::date_string( 'tomorrow' ), Sanitize::date_string( 'yesterday' ) );
	}

	public function test_date_string_keeps_iso_format(): void {
		$this->assertSame( '1990-05-15', Sanitize::date_string( '1990-05-15' ) );
	}

	public function test_date_string_rejects_slash_format(): void {
		$this->assertSame( wp_date( 'Y-m-d' ), Sanitize::date_string( '1990/05/15' ) );
	}

	public function test_date_string_rejects_garbage(): void {
		$this->assertSame( wp_date( 'Y-m-d' ), Sanitize::date_string( 'garbage' ) );
	}

	public function test_date_string_empty_uses_default_fallback(): void {
		// Empty input falls back to "today", which then resolves to YYYY-MM-DD.
		$this->assertSame( wp_date( 'Y-m-d' ), Sanitize::date_string( '' ) );
	}

	public function test_date_string_always_returns_iso_format(): void {
		// The contract: every return value matches the SaaS-required regex.
		$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}$/', Sanitize::date_string( 'today' ) );
		$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}$/', Sanitize::date_string( '' ) );
		$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}$/', Sanitize::date_string( 'garbage' ) );
		$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}$/', Sanitize::date_string( '1990-05-15' ) );
	}

	public function test_latitude_in_range_kept(): void {
		$this->assertSame( 40.7128, Sanitize::latitude( 40.7128 ) );
		$this->assertSame( -34.6037, Sanitize::latitude( -34.6037 ) );
	}

	public function test_latitude_out_of_range_zero(): void {
		$this->assertSame( 0.0, Sanitize::latitude( 91.0 ) );
		$this->assertSame( 0.0, Sanitize::latitude( -91.0 ) );
	}

	public function test_latitude_garbage_zero(): void {
		$this->assertSame( 0.0, Sanitize::latitude( 'garbage' ) );
	}

	public function test_latitude_boundary_values_kept(): void {
		$this->assertSame( 90.0, Sanitize::latitude( 90.0 ) );
		$this->assertSame( -90.0, Sanitize::latitude( -90.0 ) );
	}

	public function test_longitude_in_range_kept(): void {
		$this->assertSame( -74.006, Sanitize::longitude( -74.006 ) );
		$this->assertSame( 180.0, Sanitize::longitude( 180.0 ) );
	}

	public function test_longitude_out_of_range_zero(): void {
		$this->assertSame( 0.0, Sanitize::longitude( 180.1 ) );
		$this->assertSame( 0.0, Sanitize::longitude( -180.1 ) );
	}

	public function test_time_string_normalises_hh_mm_to_hh_mm_ss(): void {
		// HH:MM input gets a `:00` seconds component appended so the SaaS
		// regex `^\d{2}:\d{2}:\d{2}$` accepts it.
		$this->assertSame( '14:30:00', Sanitize::time_string( '14:30' ) );
		$this->assertSame( '00:00:00', Sanitize::time_string( '00:00' ) );
		$this->assertSame( '23:59:00', Sanitize::time_string( '23:59' ) );
	}

	public function test_time_string_passes_through_hh_mm_ss(): void {
		$this->assertSame( '14:30:45', Sanitize::time_string( '14:30:45' ) );
		$this->assertSame( '23:59:59', Sanitize::time_string( '23:59:59' ) );
	}

	public function test_time_string_invalid_hour_uses_fallback(): void {
		$this->assertSame( '12:00:00', Sanitize::time_string( '24:00' ) );
		$this->assertSame( '12:00:00', Sanitize::time_string( '25:00' ) );
	}

	public function test_time_string_single_digit_hour_uses_fallback(): void {
		$this->assertSame( '12:00:00', Sanitize::time_string( '1:30' ) );
	}

	public function test_time_string_invalid_minute_uses_fallback(): void {
		$this->assertSame( '12:00:00', Sanitize::time_string( '14:60' ) );
		$this->assertSame( '12:00:00', Sanitize::time_string( '14:99' ) );
	}

	public function test_time_string_empty_uses_default(): void {
		$this->assertSame( '12:00:00', Sanitize::time_string( '' ) );
	}

	public function test_time_string_custom_fallback_normalised(): void {
		$this->assertSame( '06:00:00', Sanitize::time_string( 'garbage', '06:00' ) );
		$this->assertSame( '06:30:15', Sanitize::time_string( 'garbage', '06:30:15' ) );
	}

	public function test_time_string_always_returns_hh_mm_ss(): void {
		// The SaaS-required contract: every return value matches the spec regex.
		$this->assertMatchesRegularExpression( '/^\d{2}:\d{2}:\d{2}$/', Sanitize::time_string( '14:30' ) );
		$this->assertMatchesRegularExpression( '/^\d{2}:\d{2}:\d{2}$/', Sanitize::time_string( '' ) );
		$this->assertMatchesRegularExpression( '/^\d{2}:\d{2}:\d{2}$/', Sanitize::time_string( 'garbage' ) );
		$this->assertMatchesRegularExpression( '/^\d{2}:\d{2}:\d{2}$/', Sanitize::time_string( 'garbage', 'also garbage' ) );
	}

	public function test_non_negative_int_clamps_negative_to_zero(): void {
		// Negative input clamps to zero (not absolute value). A user passing
		// year=-1990 gets year=0, which the SaaS rejects as invalid, surfacing
		// a clear error rather than silently accepting 1990 as a year.
		$this->assertSame( 0, Sanitize::non_negative_int( -5 ) );
		$this->assertSame( 0, Sanitize::non_negative_int( -1 ) );
		$this->assertSame( 0, Sanitize::non_negative_int( PHP_INT_MIN ) );
	}

	public function test_non_negative_int_string_numeric(): void {
		$this->assertSame( 5, Sanitize::non_negative_int( '5' ) );
	}

	public function test_non_negative_int_float_truncates(): void {
		$this->assertSame( 5, Sanitize::non_negative_int( 5.7 ) );
	}

	public function test_non_negative_int_garbage_zero(): void {
		$this->assertSame( 0, Sanitize::non_negative_int( 'garbage' ) );
	}

	public function test_non_negative_int_php_int_max_kept(): void {
		$this->assertSame( PHP_INT_MAX, Sanitize::non_negative_int( PHP_INT_MAX ) );
	}

	public function test_bounded_text_truncates_to_max_chars(): void {
		$input = str_repeat( 'x', 200 );
		$this->assertSame( 100, mb_strlen( Sanitize::bounded_text( $input, 100 ) ) );
	}

	public function test_bounded_text_default_max_is_100(): void {
		$input = str_repeat( 'a', 200 );
		$this->assertSame( 100, mb_strlen( Sanitize::bounded_text( $input ) ) );
	}

	public function test_bounded_text_handles_multibyte(): void {
		// "café" is 4 chars but 5 bytes (UTF-8 e-acute is 2 bytes).
		$input = str_repeat( 'caf' . "\xC3\xA9", 50 );
		$out   = Sanitize::bounded_text( $input, 10 );
		$this->assertSame( 10, mb_strlen( $out ) );
		// The byte length should be greater than 10 because mb_substr cuts on
		// character boundaries, not byte boundaries.
		$this->assertGreaterThan( 10, strlen( $out ), 'mb_substr must not truncate mid character.' );
	}

	public function test_bounded_text_short_input_unchanged(): void {
		$this->assertSame( 'short', Sanitize::bounded_text( 'short', 100 ) );
	}
}
