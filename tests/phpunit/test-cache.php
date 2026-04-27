<?php
/**
 * Tests for the RoxyAPI\Api\Cache transient wrapper.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Api\Cache;
use WP_Error;

class Test_Cache extends \WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		Cache::flush_all();
		wp_cache_flush();
	}

	public function tearDown(): void {
		Cache::flush_all();
		wp_cache_flush();
		parent::tearDown();
	}

	public function test_remember_caches_successful_array_result(): void {
		$counter = 0;
		$fetch   = static function () use ( &$counter ) {
			++$counter;
			return array( 'value' => 'fresh' );
		};

		$first  = Cache::remember( 'astrology/horoscope/aries/daily', array( 'date' => 'today' ), 3600, $fetch );
		$second = Cache::remember( 'astrology/horoscope/aries/daily', array( 'date' => 'today' ), 3600, $fetch );

		$this->assertSame( 1, $counter, 'Fetch closure must run exactly once across two remember calls.' );
		$this->assertSame( array( 'value' => 'fresh' ), $first );
		$this->assertSame( array( 'value' => 'fresh' ), $second );
	}

	public function test_remember_stores_array_with_requested_ttl(): void {
		$fetch = static function () {
			return array( 'overview' => 'test' );
		};
		Cache::remember( 'astrology/horoscope/leo/daily', array(), 1800, $fetch );

		$key = 'roxyapi_' . md5( 'astrology/horoscope/leo/daily|' . wp_json_encode( array() ) );
		$this->assertSame( array( 'overview' => 'test' ), get_transient( $key ) );
	}

	public function test_remember_negative_caches_auth_quota_upstream_errors(): void {
		$codes = array( 'roxyapi_auth', 'roxyapi_quota', 'roxyapi_upstream' );
		foreach ( $codes as $code ) {
			$endpoint = 'test/' . $code;
			$fetch    = static function () use ( $code ) {
				return new WP_Error( $code, 'boom' );
			};
			Cache::remember( $endpoint, array(), 3600, $fetch );

			$key    = 'roxyapi_' . md5( $endpoint . '|' . wp_json_encode( array() ) );
			$cached = get_transient( $key );
			$this->assertInstanceOf( WP_Error::class, $cached, "Expected {$code} to be negative cached." );
			$this->assertSame( $code, $cached->get_error_code() );
		}
	}

	public function test_remember_does_not_cache_non_categorised_errors(): void {
		$uncategorized = array( 'roxyapi_json', 'roxyapi_no_key', 'roxyapi_json_encode', 'roxyapi_http_404' );
		foreach ( $uncategorized as $code ) {
			$endpoint = 'test/no-cache/' . $code;
			$counter  = 0;
			$fetch    = static function () use ( &$counter, $code ) {
				++$counter;
				return new WP_Error( $code, 'transient-issue' );
			};

			Cache::remember( $endpoint, array(), 3600, $fetch );
			Cache::remember( $endpoint, array(), 3600, $fetch );

			$this->assertSame( 2, $counter, "Non categorised error {$code} must not be cached." );

			$key = 'roxyapi_' . md5( $endpoint . '|' . wp_json_encode( array() ) );
			$this->assertFalse( get_transient( $key ) );
		}
	}

	public function test_remember_with_zero_ttl_bypasses_cache(): void {
		$counter = 0;
		$fetch   = static function () use ( &$counter ) {
			++$counter;
			return array( 'x' => 1 );
		};

		Cache::remember( 'astrology/x', array(), 0, $fetch );
		Cache::remember( 'astrology/x', array(), 0, $fetch );

		$this->assertSame( 2, $counter, 'TTL of zero must bypass cache.' );
	}

	public function test_flush_all_deletes_only_roxyapi_transients(): void {
		set_transient( 'roxyapi_one', 'a', 3600 );
		set_transient( 'roxyapi_two', 'b', 3600 );
		set_transient( 'unrelated_plugin_data', 'safe', 3600 );

		Cache::flush_all();
		// flush_all deletes via wpdb directly; the object cache must be
		// invalidated for the next get_transient to reflect reality.
		wp_cache_flush();

		$this->assertFalse( get_transient( 'roxyapi_one' ) );
		$this->assertFalse( get_transient( 'roxyapi_two' ) );
		$this->assertSame( 'safe', get_transient( 'unrelated_plugin_data' ) );
	}

	public function test_flush_all_handles_like_metacharacters_in_other_transients(): void {
		// Sibling transient name that contains LIKE metacharacters but is NOT
		// a roxyapi_ prefix. esc_like must keep flush_all from accidentally
		// matching it.
		set_transient( 'unrelated_transient', 'safe', 3600 );
		set_transient( 'roxyapi_with_underscore', 'targeted', 3600 );

		Cache::flush_all();
		wp_cache_flush();

		$this->assertFalse(
			get_transient( 'roxyapi_with_underscore' ),
			'flush_all must remove roxyapi_ prefixed transients.'
		);
		$this->assertSame(
			'safe',
			get_transient( 'unrelated_transient' ),
			'flush_all must not match transients that lack the roxyapi_ prefix even when they share underscore characters.'
		);
	}

	public function test_cache_key_is_deterministic_for_same_endpoint_and_args(): void {
		$counter = 0;
		$fetch   = static function () use ( &$counter ) {
			++$counter;
			return array( 'n' => $counter );
		};

		$first  = Cache::remember( 'astrology/foo', array( 'a' => 1, 'b' => 2 ), 3600, $fetch );
		$second = Cache::remember( 'astrology/foo', array( 'a' => 1, 'b' => 2 ), 3600, $fetch );

		$this->assertSame( $first, $second );
		$this->assertSame( 1, $counter );
	}

	public function test_different_args_produce_distinct_cache_entries(): void {
		$calls = 0;
		$fetch = static function () use ( &$calls ) {
			++$calls;
			return array( 'call' => $calls );
		};

		Cache::remember( 'astrology/horoscope/aries/daily', array( 'date' => 'today' ), 3600, $fetch );
		Cache::remember( 'astrology/horoscope/aries/daily', array( 'date' => 'tomorrow' ), 3600, $fetch );

		$this->assertSame( 2, $calls, 'Different args must produce different cache keys.' );
	}
}
