<?php
/**
 * Cross-system test: prove RoxyAPI\Api\Cache and RoxyAPI\Support\RateLimit
 * cannot collide on the same transient key.
 *
 * Both helpers prefix keys with `roxyapi_`, but Cache always emits a 32-char
 * MD5 directly after the prefix while RateLimit inserts the literal string
 * `rl_` between the prefix and its MD5. The two namespaces are therefore
 * disjoint and flush_all wipes both with a single LIKE.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Api\Cache;
use RoxyAPI\Support\RateLimit;

class Test_Cache_Key_Isolation extends \WP_UnitTestCase {

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

	public function test_cache_keys_use_distinct_namespace_from_rate_limit(): void {
		// Seed one of each type.
		Cache::remember(
			'astrology/horoscope/aries/daily',
			array( 'date' => 'today' ),
			3600,
			static function () {
				return array( 'overview' => 'present' );
			}
		);
		RateLimit::check( 'isolation_scope' );

		global $wpdb;
		$rows = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
				'_transient_roxyapi_%'
			)
		);

		$this->assertGreaterThanOrEqual( 2, count( $rows ), 'Expected at least one cache entry and one rate-limit entry.' );

		$cache_keys      = array();
		$rate_limit_keys = array();
		foreach ( $rows as $row ) {
			// Strip the standard transient prefix.
			$transient_name = preg_replace( '/^_transient_/', '', (string) $row );
			if ( strpos( (string) $transient_name, 'roxyapi_rl_' ) === 0 ) {
				$rate_limit_keys[] = $transient_name;
			} elseif ( strpos( (string) $transient_name, 'roxyapi_' ) === 0 ) {
				$cache_keys[] = $transient_name;
			}
		}

		$this->assertNotEmpty( $cache_keys, 'Cache::remember must have written at least one entry.' );
		$this->assertNotEmpty( $rate_limit_keys, 'RateLimit::check must have written at least one entry.' );

		// No overlap between the two namespaces.
		$this->assertSame(
			array(),
			array_intersect( $cache_keys, $rate_limit_keys ),
			'Cache and RateLimit namespaces must be disjoint.'
		);

		// Every cache key matches `roxyapi_<32 hex>` exactly, no `rl_` prefix.
		foreach ( $cache_keys as $k ) {
			$this->assertMatchesRegularExpression( '/^roxyapi_[a-f0-9]{32}$/', $k );
		}
		// Every rate-limit key matches `roxyapi_rl_<32 hex>` exactly.
		foreach ( $rate_limit_keys as $k ) {
			$this->assertMatchesRegularExpression( '/^roxyapi_rl_[a-f0-9]{32}$/', $k );
		}
	}

	public function test_flush_all_removes_both_namespaces(): void {
		Cache::remember(
			'astrology/horoscope/leo/daily',
			array(),
			3600,
			static function () {
				return array( 'overview' => 'cached' );
			}
		);
		RateLimit::check( 'flush_isolation' );

		Cache::flush_all();
		wp_cache_flush();

		global $wpdb;
		$remaining = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
				'_transient_roxyapi_%'
			)
		);
		$this->assertSame( 0, $remaining, 'flush_all must remove every roxyapi prefixed transient.' );

		$remaining_timeouts = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
				'_transient_timeout_roxyapi_%'
			)
		);
		$this->assertSame( 0, $remaining_timeouts, 'flush_all must remove every roxyapi timeout entry.' );
	}
}
