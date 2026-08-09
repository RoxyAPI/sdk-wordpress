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
		remove_all_filters( 'roxyapi_trusted_proxy_headers' );
	}

	public function tearDown(): void {
		Cache::flush_all();
		wp_cache_flush();
		remove_all_filters( 'roxyapi_client_ip' );
		remove_all_filters( 'roxyapi_trusted_proxy_headers' );
		unset(
			$_SERVER['HTTP_CF_CONNECTING_IP'],
			$_SERVER['HTTP_X_FORWARDED_FOR']
		);
		parent::tearDown();
	}

	public function test_check_returns_true_within_limit(): void {
		$this->assertTrue( RateLimit::check( 'horoscope' ) );
		$this->assertTrue( RateLimit::check( 'horoscope' ) );
	}

	public function test_check_returns_false_when_limit_reached(): void {
		update_option( 'roxyapi_settings', array( 'rate_limit_per_hour' => 3 ) );

		$this->assertTrue( RateLimit::check( 'horoscope' ) );
		$this->assertTrue( RateLimit::check( 'horoscope' ) );
		$this->assertTrue( RateLimit::check( 'horoscope' ) );
		$this->assertFalse( RateLimit::check( 'horoscope' ) );
		// One more denial should not flip the counter behaviour.
		$this->assertFalse( RateLimit::check( 'horoscope' ) );
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
		$this->assertTrue( RateLimit::check( 'test_key' ) );
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

	/**
	 * The defect this guards. The limiter used to read CF-Connecting-IP and
	 * X-Forwarded-For with no way to know a proxy had written them, so a
	 * visitor changed one digit per request and never shared a bucket. This is
	 * the only quota control on the public geocode route and on billed form
	 * submissions, so a spoofable bucket meant no limit at all.
	 */
	public function test_spoofed_forwarded_headers_do_not_mint_a_new_bucket(): void {
		update_option( 'roxyapi_settings', array( 'rate_limit_per_hour' => 2 ) );
		$_SERVER['REMOTE_ADDR'] = '198.51.100.10';

		$_SERVER['HTTP_CF_CONNECTING_IP'] = '1.2.3.4';
		$this->assertTrue( RateLimit::check( 'spoof_scope' ) );
		$_SERVER['HTTP_CF_CONNECTING_IP'] = '1.2.3.5';
		$this->assertTrue( RateLimit::check( 'spoof_scope' ) );

		$_SERVER['HTTP_CF_CONNECTING_IP'] = '1.2.3.6';
		$_SERVER['HTTP_X_FORWARDED_FOR']  = '5.6.7.8';
		$this->assertFalse(
			RateLimit::check( 'spoof_scope' ),
			'A forwarded header nobody opted into must not reset the bucket.'
		);
	}

	public function test_trusted_proxy_header_opt_in_keys_its_own_bucket(): void {
		update_option( 'roxyapi_settings', array( 'rate_limit_per_hour' => 2 ) );
		$_SERVER['REMOTE_ADDR'] = '198.51.100.10';
		add_filter( 'roxyapi_trusted_proxy_headers', static fn() => array( 'HTTP_CF_CONNECTING_IP' ) );

		$_SERVER['HTTP_CF_CONNECTING_IP'] = '203.0.113.1';
		$this->assertTrue( RateLimit::check( 'optin_scope' ) );
		$this->assertTrue( RateLimit::check( 'optin_scope' ) );
		$this->assertFalse( RateLimit::check( 'optin_scope' ) );

		$_SERVER['HTTP_CF_CONNECTING_IP'] = '203.0.113.2';
		$this->assertTrue( RateLimit::check( 'optin_scope' ), 'An opted-in header must separate visitors again.' );
	}

	/**
	 * A site can only trust a header conditionally (say, while the connection
	 * really is arriving from its own edge) if the filter is told who connected.
	 */
	public function test_trusted_proxy_filter_receives_the_connecting_ip(): void {
		$_SERVER['REMOTE_ADDR'] = '198.51.100.10';
		$seen                   = null;
		add_filter(
			'roxyapi_trusted_proxy_headers',
			static function ( $headers, $remote_addr ) use ( &$seen ) {
				$seen = $remote_addr;
				return $headers;
			},
			10,
			2
		);

		RateLimit::check( 'remote_arg_scope' );
		$this->assertSame( '198.51.100.10', $seen );
	}

	public function test_trusted_header_normalises_a_port_and_a_proxy_chain(): void {
		update_option( 'roxyapi_settings', array( 'rate_limit_per_hour' => 1 ) );
		$_SERVER['REMOTE_ADDR'] = '198.51.100.10';
		add_filter( 'roxyapi_trusted_proxy_headers', static fn() => array( 'HTTP_X_FORWARDED_FOR' ) );

		$_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.9:51234, 70.41.3.18';
		$this->assertTrue( RateLimit::check( 'normalise_scope' ) );

		// Same client, written the plain way by a different hop. Formatting must
		// not split one visitor across two buckets.
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.9';
		$this->assertFalse( RateLimit::check( 'normalise_scope' ) );
	}

	public function test_default_limit_when_setting_unset(): void {
		delete_option( 'roxyapi_settings' );
		// DEFAULT_LIMIT calls succeed before throttling kicks in.
		for ( $i = 0; $i < RateLimit::DEFAULT_LIMIT; $i++ ) {
			$this->assertTrue( RateLimit::check( 'unset_scope' ), "Call {$i} should succeed." );
		}
		$this->assertFalse( RateLimit::check( 'unset_scope' ), 'Call after default limit must fail.' );
	}

	public function test_custom_limit_override_via_option(): void {
		update_option( 'roxyapi_settings', array( 'rate_limit_per_hour' => 5 ) );
		for ( $i = 0; $i < 5; $i++ ) {
			$this->assertTrue( RateLimit::check( 'override' ), "Call {$i} should succeed." );
		}
		$this->assertFalse( RateLimit::check( 'override' ), 'The 6th call must fail.' );
	}

	public function test_invalid_trusted_header_value_falls_back_to_remote_addr(): void {
		update_option( 'roxyapi_settings', array( 'rate_limit_per_hour' => 2 ) );
		add_filter( 'roxyapi_trusted_proxy_headers', static fn() => array( 'HTTP_CF_CONNECTING_IP' ) );
		$_SERVER['REMOTE_ADDR']           = '198.51.100.10';
		$_SERVER['HTTP_CF_CONNECTING_IP'] = 'garbage';

		// Burn through using REMOTE_ADDR as the resolved IP.
		$this->assertTrue( RateLimit::check( 'fallthrough' ) );
		$this->assertTrue( RateLimit::check( 'fallthrough' ) );
		$this->assertFalse( RateLimit::check( 'fallthrough' ) );

		// A value that is not an IP can never be the bucket, so rewriting it
		// changes nothing and only a different connection mints a new one.
		$_SERVER['HTTP_CF_CONNECTING_IP'] = 'still-garbage';
		$this->assertFalse( RateLimit::check( 'fallthrough' ) );
		$_SERVER['REMOTE_ADDR'] = '198.51.100.11';
		$this->assertTrue( RateLimit::check( 'fallthrough' ) );
	}

	public function test_invalid_client_ip_filter_value_is_rejected(): void {
		update_option( 'roxyapi_settings', array( 'rate_limit_per_hour' => 2 ) );
		$_SERVER['REMOTE_ADDR'] = '198.51.100.10';
		add_filter( 'roxyapi_client_ip', static fn() => '127.0.0.1; rm -rf /' );

		$this->assertTrue( RateLimit::check( 'bad_filter' ) );
		$this->assertTrue( RateLimit::check( 'bad_filter' ) );
		$this->assertFalse( RateLimit::check( 'bad_filter' ) );

		// The override was discarded, so REMOTE_ADDR is what the bucket keys on.
		$_SERVER['REMOTE_ADDR'] = '198.51.100.11';
		$this->assertTrue( RateLimit::check( 'bad_filter' ) );
	}

	public function test_client_ip_filter_override_wins_over_remote_addr(): void {
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

		// The override pins the bucket, so a different connection stays in it.
		$_SERVER['REMOTE_ADDR'] = '198.51.100.11';
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
