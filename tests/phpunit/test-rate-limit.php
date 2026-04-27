<?php
/**
 * Tests for the RoxyAPI\Support\RateLimit helper.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Api\Cache;
use RoxyAPI\Support\RateLimit;

class Test_Rate_Limit extends \WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		Cache::flush_all();
		wp_cache_flush();
		delete_option( 'roxyapi_settings' );
		// Clear ip headers so tests start with a known client IP.
		unset(
			$_SERVER['HTTP_CF_CONNECTING_IP'],
			$_SERVER['HTTP_X_FORWARDED_FOR'],
			$_SERVER['REMOTE_ADDR']
		);
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		remove_all_filters( 'roxyapi_client_ip' );
	}

	public function tearDown(): void {
		Cache::flush_all();
		wp_cache_flush();
		remove_all_filters( 'roxyapi_client_ip' );
		unset(
			$_SERVER['HTTP_CF_CONNECTING_IP'],
			$_SERVER['HTTP_X_FORWARDED_FOR']
		);
		parent::tearDown();
	}

	public function test_check_returns_true_within_limit(): void {
		$this->assertTrue( RateLimit::check( 'horoscope' ) );
		$this->assertTrue( RateLimit::check( 'horoscope' ) );
		$this->assertSame( RateLimit::DEFAULT_LIMIT - 2, RateLimit::remaining( 'horoscope' ) );
	}

	public function test_check_returns_false_when_limit_reached(): void {
		update_option( 'roxyapi_settings', array( 'rate_limit_per_hour' => 3 ) );

		$this->assertTrue( RateLimit::check( 'horoscope' ) );
		$this->assertTrue( RateLimit::check( 'horoscope' ) );
		$this->assertTrue( RateLimit::check( 'horoscope' ) );
		$this->assertFalse( RateLimit::check( 'horoscope' ) );
		// Counter must not creep above the limit.
		$this->assertSame( 0, RateLimit::remaining( 'horoscope' ) );
		// One more denial should not push remaining negative.
		$this->assertFalse( RateLimit::check( 'horoscope' ) );
		$this->assertSame( 0, RateLimit::remaining( 'horoscope' ) );
	}

	public function test_window_resets_when_transient_expires(): void {
		update_option( 'roxyapi_settings', array( 'rate_limit_per_hour' => 2 ) );
		$this->assertTrue( RateLimit::check( 'reset_scope' ) );
		$this->assertTrue( RateLimit::check( 'reset_scope' ) );
		$this->assertFalse( RateLimit::check( 'reset_scope' ) );

		Cache::flush_all();
		// flush_all bypasses the object cache via direct wpdb DELETE, so we
		// must invalidate the cache manually for the next read to see fresh
		// state.
		wp_cache_flush();
		$this->assertTrue( RateLimit::check( 'reset_scope' ) );
	}

	public function test_different_scopes_are_isolated(): void {
		update_option( 'roxyapi_settings', array( 'rate_limit_per_hour' => 2 ) );
		$this->assertTrue( RateLimit::check( 'horoscope' ) );
		$this->assertTrue( RateLimit::check( 'horoscope' ) );
		$this->assertFalse( RateLimit::check( 'horoscope' ) );

		// A different scope starts with full quota.
		$this->assertTrue( RateLimit::check( 'test_key' ) );
		$this->assertSame( 1, RateLimit::remaining( 'test_key' ) );
	}

	public function test_different_ips_have_isolated_counters(): void {
		update_option( 'roxyapi_settings', array( 'rate_limit_per_hour' => 2 ) );

		$current_ip = '10.0.0.1';
		add_filter(
			'roxyapi_client_ip',
			static function () use ( &$current_ip ) {
				return $current_ip;
			}
		);

		$this->assertTrue( RateLimit::check( 'horoscope' ) );
		$this->assertTrue( RateLimit::check( 'horoscope' ) );
		$this->assertFalse( RateLimit::check( 'horoscope' ) );

		$current_ip = '10.0.0.2';
		$this->assertTrue( RateLimit::check( 'horoscope' ), 'A second IP must have its own counter.' );
	}

	public function test_x_forwarded_for_uses_first_ip(): void {
		update_option( 'roxyapi_settings', array( 'rate_limit_per_hour' => 2 ) );
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4, 5.6.7.8';

		$this->assertTrue( RateLimit::check( 'xff_scope' ) );
		$this->assertTrue( RateLimit::check( 'xff_scope' ) );
		$this->assertFalse( RateLimit::check( 'xff_scope' ) );

		// Switch to a request originating from the second IP only. The first
		// should now be empty so the same-named scope must restart counting.
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '5.6.7.8';
		$this->assertTrue( RateLimit::check( 'xff_scope' ) );
	}

	public function test_default_limit_when_setting_unset(): void {
		delete_option( 'roxyapi_settings' );
		// Use a fresh scope so we are at zero.
		$remaining_at_start = RateLimit::remaining( 'unset_scope' );
		$this->assertSame( RateLimit::DEFAULT_LIMIT, $remaining_at_start );
	}

	public function test_custom_limit_override_via_option(): void {
		update_option( 'roxyapi_settings', array( 'rate_limit_per_hour' => 5 ) );
		for ( $i = 0; $i < 5; $i++ ) {
			$this->assertTrue( RateLimit::check( 'override' ), "Call {$i} should succeed." );
		}
		$this->assertFalse( RateLimit::check( 'override' ), 'The 6th call must fail.' );
	}

	public function test_invalid_ip_falls_through_to_next_header(): void {
		update_option( 'roxyapi_settings', array( 'rate_limit_per_hour' => 2 ) );
		$_SERVER['HTTP_CF_CONNECTING_IP'] = 'garbage';
		$_SERVER['REMOTE_ADDR']           = '127.0.0.1';

		// Burn through using REMOTE_ADDR as the resolved IP.
		$this->assertTrue( RateLimit::check( 'fallthrough' ) );
		$this->assertTrue( RateLimit::check( 'fallthrough' ) );
		$this->assertFalse( RateLimit::check( 'fallthrough' ) );

		// Same scope, different REMOTE_ADDR, garbage CF header still present:
		// the bucket should reset because the per-IP key now differs.
		$_SERVER['REMOTE_ADDR'] = '127.0.0.2';
		$this->assertTrue( RateLimit::check( 'fallthrough' ) );
	}

	public function test_filter_override_wins_over_headers(): void {
		update_option( 'roxyapi_settings', array( 'rate_limit_per_hour' => 1 ) );
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';

		add_filter(
			'roxyapi_client_ip',
			static function () {
				return '203.0.113.7';
			}
		);

		$this->assertTrue( RateLimit::check( 'filter_scope' ) );
		$this->assertFalse( RateLimit::check( 'filter_scope' ) );
		// remove_all_filters in tearDown restores baseline.
	}

	public function test_zero_or_negative_setting_clamps_to_minimum_one(): void {
		update_option( 'roxyapi_settings', array( 'rate_limit_per_hour' => 0 ) );
		// Limit is clamped to max(1, 0) = 1, so the first call passes and the
		// second is denied.
		$this->assertTrue( RateLimit::check( 'clamp_scope' ) );
		$this->assertFalse( RateLimit::check( 'clamp_scope' ) );
	}
}
