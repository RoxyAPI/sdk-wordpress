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

	/**
	 * Mirror Cache::key(): the cache folds the current epoch and the effective
	 * display language into the key, so an expected key here must include both.
	 * The epoch is read live because flush_all rewrites it.
	 */
	private function cache_key( string $endpoint, array $args = array() ): string {
		if ( ! isset( $args['lang'] ) || (string) $args['lang'] === '' ) {
			$lang = \RoxyAPI\Support\Language::resolve();
			if ( $lang !== '' ) {
				$args['lang'] = $lang;
			}
		}
		$epoch = (string) get_option( 'roxyapi_cache_epoch', '' );
		return 'roxyapi_' . md5( $epoch . '|' . $endpoint . '|' . wp_json_encode( $args ) );
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

		$key = $this->cache_key( 'astrology/horoscope/leo/daily' );
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

			$key    = $this->cache_key( $endpoint );
			$cached = get_transient( $key );
			$this->assertInstanceOf( WP_Error::class, $cached, "Expected {$code} to be negative cached." );
			$this->assertSame( $code, $cached->get_error_code() );
		}
	}

	/**
	 * A rejected request is rejected identically every time, so repeating it
	 * spends quota to learn nothing. Safe because the cache key hashes the
	 * arguments: correcting the input yields a different key and calls through
	 * at once.
	 */
	public function test_remember_negative_caches_client_errors(): void {
		$codes = array( 'roxyapi_http_400', 'roxyapi_http_404', 'roxyapi_http_422', 'roxyapi_free_tier_exhausted' );
		foreach ( $codes as $code ) {
			$endpoint = 'test/client-error/' . $code;
			$counter  = 0;
			$fetch    = static function () use ( &$counter, $code ) {
				++$counter;
				return new WP_Error( $code, 'rejected' );
			};

			Cache::remember( $endpoint, array(), 3600, $fetch );
			Cache::remember( $endpoint, array(), 3600, $fetch );

			$this->assertSame( 1, $counter, "Client error {$code} must be sent once, not on every render." );
			$this->assertInstanceOf( WP_Error::class, get_transient( $this->cache_key( $endpoint ) ) );
		}
	}

	/**
	 * A TTL of 0 means "repeating the ANSWER would be wrong" (a tarot draw),
	 * never "resend a request the API already refused".
	 */
	public function test_remember_negative_caches_even_when_success_caching_is_off(): void {
		$endpoint = 'test/zero-ttl/rejected';
		$counter  = 0;
		$fetch    = static function () use ( &$counter ) {
			++$counter;
			return new WP_Error( 'roxyapi_http_400', 'rejected' );
		};

		Cache::remember( $endpoint, array(), 0, $fetch );
		Cache::remember( $endpoint, array(), 0, $fetch );

		$this->assertSame( 1, $counter, 'A zero TTL must not defeat negative caching.' );
	}

	public function test_remember_does_not_cache_non_categorised_errors(): void {
		$uncategorized = array( 'roxyapi_json', 'roxyapi_no_key', 'roxyapi_json_encode' );
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

			$key = $this->cache_key( $endpoint );
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

	/**
	 * The customer-visible failure this guards. On a site with a persistent
	 * object cache (Redis, Memcached) transients never reach `wp_options`, so
	 * the LIKE sweep deletes zero rows and flush_all was silently doing
	 * nothing: an owner who spent the free allowance, bought a plan and pasted
	 * the key kept seeing the cached failure until it expired on its own.
	 *
	 * Asserted through the public surface with NO wp_cache_flush() helping,
	 * because that helper is what hid the bug in every other test here.
	 */
	public function test_flush_all_invalidates_entries_behind_an_external_object_cache(): void {
		global $wpdb;
		$previous = wp_using_ext_object_cache( true );

		try {
			$calls = 0;
			$fetch = static function () use ( &$calls ) {
				++$calls;
				return array( 'value' => 'reading-' . $calls );
			};

			Cache::remember( 'test/object-cache/flush', array(), 3600, $fetch );
			Cache::remember( 'test/object-cache/flush', array(), 3600, $fetch );
			$this->assertSame( 1, $calls, 'Sanity: the entry must be cached before the flush.' );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- asserting on raw storage is the point of this test.
			$rows = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_roxyapi_%'" );
			$this->assertSame( 0, $rows, 'Sanity: an object-cached transient must leave nothing for the wp_options sweep to find.' );

			Cache::flush_all();

			$after = Cache::remember( 'test/object-cache/flush', array(), 3600, $fetch );
			$this->assertSame( 2, $calls, 'flush_all must invalidate object-cached entries, which the wp_options sweep cannot reach.' );
			$this->assertSame( array( 'value' => 'reading-2' ), $after );
		} finally {
			// Cast: with no object-cache drop-in the global is null, and passing
			// null back is a read, not a reset, so the flag would leak into
			// every later test and stop their transients reaching wp_options.
			wp_using_ext_object_cache( (bool) $previous );
		}
	}

	public function test_flush_all_rewrites_the_cache_epoch(): void {
		Cache::remember( 'test/epoch/rotation', array(), 3600, static fn() => array( 'v' => 1 ) );
		$before = get_option( 'roxyapi_cache_epoch' );
		$this->assertNotEmpty( $before, 'Using the cache must seed an epoch.' );

		Cache::flush_all();

		$this->assertNotSame( $before, get_option( 'roxyapi_cache_epoch' ), 'Every flush must mint a new epoch, otherwise old keys stay reachable.' );
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
