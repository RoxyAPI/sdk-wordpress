<?php
/**
 * Transient cache wrapper for RoxyAPI responses.
 *
 * Wraps every API call so cached reads cost zero RoxyAPI quota. Object cache
 * backends (Redis, Memcached) are picked up automatically via WordPress
 * transient routing.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Api;

use WP_Error;

class Cache {

	/**
	 * Fetch from cache or call the API and store the result.
	 *
	 * @param string               $endpoint API endpoint path.
	 * @param array<string, mixed> $args     Arguments used to build the cache key.
	 * @param int                  $ttl      Cache TTL in seconds.
	 * @param callable             $fetch    Callback that fetches fresh data.
	 * @return mixed Cached or freshly fetched result.
	 */
	public static function remember( string $endpoint, array $args, int $ttl, callable $fetch ) {
		if ( $ttl <= 0 ) {
			return $fetch();
		}
		$key    = self::key( $endpoint, $args );
		$cached = get_transient( $key );
		if ( $cached !== false ) {
			return $cached;
		}
		$result = $fetch();
		if ( ! is_wp_error( $result ) ) {
			set_transient( $key, $result, $ttl );
		}
		return $result;
	}

	/**
	 * Build a transient cache key from endpoint and arguments.
	 *
	 * @param string               $endpoint API endpoint path.
	 * @param array<string, mixed> $args     Arguments to hash into the cache key.
	 * @return string
	 */
	private static function key( string $endpoint, array $args ): string {
		return 'roxyapi_' . md5( $endpoint . '|' . wp_json_encode( $args ) );
	}

	public static function flush_all(): void {
		global $wpdb;
		$wpdb->query(
			"DELETE FROM {$wpdb->options}
			 WHERE option_name LIKE '_transient_roxyapi_%'
			    OR option_name LIKE '_transient_timeout_roxyapi_%'"
		);
	}
}
