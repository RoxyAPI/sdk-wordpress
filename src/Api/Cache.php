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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;

class Cache {

	/**
	 * Apply the site-owner's cache-preset multiplier on top of the
	 * generator-emitted per-endpoint TTL. `fresh` quarters TTLs to keep
	 * readings current; `quota_saver` 24x's them to slash quota burn.
	 * `balanced` (default) is unchanged.
	 *
	 * @param int $ttl Base TTL from the generator's ttl-map.
	 * @return int Adjusted TTL.
	 */
	private static function apply_preset_multiplier( int $ttl ): int {
		if ( $ttl <= 0 ) {
			return $ttl;
		}
		$opts   = \RoxyAPI\Admin\SettingsSchema::get_option();
		$preset = (string) ( $opts['cache_preset'] ?? 'balanced' );
		switch ( $preset ) {
			case 'fresh':
				return (int) max( 60, $ttl / 4 );
			case 'quota_saver':
				return (int) min( DAY_IN_SECONDS, $ttl * 24 );
			case 'balanced':
			default:
				return $ttl;
		}
	}

	/**
	 * Short TTL for negative-caching upstream failures.
	 *
	 * When RoxyAPI returns 429 (quota), 401/403 (auth), or 5xx (upstream), we
	 * cache the WP_Error for this many seconds so the next page load does not
	 * round-trip again. The error counter on the SaaS side keeps ticking
	 * regardless of useful work, so blasting through it would only push the
	 * recovery window further out.
	 */
	private const NEGATIVE_TTL = 60;

	/**
	 * Fetch from cache or call the API and store the result.
	 *
	 * Successful responses cache for the full $ttl. Categorised upstream
	 * failures (auth, quota, 5xx) cache for self::NEGATIVE_TTL seconds to avoid
	 * hammering RoxyAPI while the user-visible message is the same.
	 *
	 * @param string               $endpoint API endpoint path.
	 * @param array<string, mixed> $args     Arguments used to build the cache key.
	 * @param int                  $ttl      Cache TTL in seconds for successful responses.
	 * @param callable             $fetch    Callback that fetches fresh data.
	 * @return mixed Cached or freshly fetched result. May be a WP_Error from a recently failed call.
	 */
	public static function remember( string $endpoint, array $args, int $ttl, callable $fetch ) {
		$ttl = self::apply_preset_multiplier( $ttl );
		if ( $ttl <= 0 ) {
			return $fetch();
		}
		$key    = self::key( $endpoint, $args );
		$cached = get_transient( $key );
		if ( $cached !== false ) {
			return $cached;
		}
		$result = $fetch();
		if ( is_wp_error( $result ) && self::is_transient_failure( $result ) ) {
			set_transient( $key, $result, self::NEGATIVE_TTL );
		} elseif ( ! is_wp_error( $result ) ) {
			set_transient( $key, $result, $ttl );
		}
		return $result;
	}

	/**
	 * Whether an error should be negative-cached briefly to avoid hammering.
	 *
	 * Only categorised upstream failures (auth, quota, 5xx) qualify. Other
	 * errors (malformed JSON, encoding failures, missing key) either resolve
	 * on user action or are best surfaced fresh on every render so admins
	 * notice them quickly.
	 *
	 * @param \WP_Error $error Error returned by the API client.
	 * @return bool
	 */
	private static function is_transient_failure( \WP_Error $error ): bool {
		$code = (string) $error->get_error_code();
		return $code === 'roxyapi_auth'
			|| $code === 'roxyapi_quota'
			|| $code === 'roxyapi_upstream';
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
		$value_like   = '_transient_' . $wpdb->esc_like( 'roxyapi_' ) . '%';
		$timeout_like = '_transient_timeout_' . $wpdb->esc_like( 'roxyapi_' ) . '%';
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				$value_like,
				$timeout_like
			)
		);
	}
}
