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
				// Cap the 24x boost at one day, but never below the base TTL:
				// long-lived entries (natal chart 30d) must not shrink.
				return (int) max( $ttl, min( DAY_IN_SECONDS, $ttl * 24 ) );
			case 'balanced':
			default:
				return $ttl;
		}
	}

	/**
	 * Short TTL for negative-caching a failure that may clear on its own.
	 *
	 * Auth, rate-limit and 5xx responses are cached for this many seconds so the
	 * next page load does not round-trip again, while still recovering quickly
	 * once the condition passes.
	 */
	private const NEGATIVE_TTL = 60;

	/**
	 * Longer TTL for a failure that repeating the same request cannot fix.
	 *
	 * A rejected request (a shortcode missing a required attribute, a bad sign
	 * name, an unparseable date) is rejected identically every time, so sending
	 * it again on the next page view costs a request and changes nothing. A
	 * misconfigured block on a busy page is the case this exists for.
	 *
	 * Holding it this long is safe because the cache key hashes the arguments.
	 * The moment an admin corrects the attribute the key changes and the next
	 * render calls through immediately, so a fix is never masked by a stale
	 * error.
	 */
	private const CLIENT_ERROR_TTL = HOUR_IN_SECONDS;

	/**
	 * Fetch from cache or call the API and store the result.
	 *
	 * Successful responses cache for the full $ttl, and a $ttl of 0 means "never
	 * cache a success" for the endpoints where a repeat answer would be a bug
	 * (a tarot draw, a yes/no cast, anything with `random` in the name).
	 *
	 * FAILURES are cached independently of $ttl. A TTL of 0 describes how fresh
	 * a SUCCESSFUL reading has to be; it says nothing about how often a request
	 * that the API already rejected deserves to be sent again. Before this split
	 * a $ttl of 0 returned early and skipped the cache entirely, so a random-draw
	 * endpoint called with a bad argument re-POSTed a guaranteed rejection on
	 * every single page view.
	 *
	 * @param string               $endpoint API endpoint path.
	 * @param array<string, mixed> $args     Arguments used to build the cache key.
	 * @param int                  $ttl      Cache TTL in seconds for successful responses. 0 disables success caching only.
	 * @param callable             $fetch    Callback that fetches fresh data.
	 * @return mixed Cached or freshly fetched result. May be a WP_Error from a recently failed call.
	 */
	public static function remember( string $endpoint, array $args, int $ttl, callable $fetch ) {
		$ttl    = self::apply_preset_multiplier( $ttl );
		$key    = self::key( $endpoint, $args );
		$cached = get_transient( $key );
		if ( $cached !== false ) {
			return $cached;
		}
		$result = $fetch();
		if ( is_wp_error( $result ) ) {
			$negative = self::negative_ttl( $result );
			if ( $negative > 0 ) {
				set_transient( $key, $result, $negative );
			}
		} elseif ( $ttl > 0 ) {
			set_transient( $key, $result, $ttl );
		}
		return $result;
	}

	/**
	 * How long to suppress repeat calls after a failure, in seconds. 0 means
	 * do not cache the failure at all.
	 *
	 * Three tiers, split by whether retrying could plausibly succeed:
	 *
	 * - A failure that repeating cannot fix (4xx other than 408 and 429, and a
	 *   spent allowance) is held for {@link Cache::CLIENT_ERROR_TTL}.
	 * - A failure that may clear on its own (auth, rate limit, 5xx, request
	 *   timeout) is held only for {@link Cache::NEGATIVE_TTL}.
	 * - Anything else (malformed JSON, a local encoding failure) is not cached,
	 *   so an admin sees it fresh on every render.
	 *
	 * Saving an API key flushes every cached entry, so an owner who connects or
	 * rotates a key never waits out one of these windows.
	 *
	 * @param \WP_Error $error Error returned by the API client.
	 * @return int
	 */
	private static function negative_ttl( \WP_Error $error ): int {
		$code = (string) $error->get_error_code();

		if ( $code === 'roxyapi_auth'
			|| $code === 'roxyapi_quota'
			|| $code === 'roxyapi_upstream'
			|| $code === 'roxyapi_http_408' ) {
			return self::NEGATIVE_TTL;
		}

		if ( $code === 'roxyapi_free_tier_exhausted'
			|| strpos( $code, 'roxyapi_http_4' ) === 0 ) {
			return self::CLIENT_ERROR_TTL;
		}

		return 0;
	}

	/**
	 * Build a transient cache key from endpoint and arguments.
	 *
	 * @remarks The display language is added to the request below this layer (in {@link \RoxyAPI\Api\Client}), so it is absent from $args. Fold the resolved language into the key here too, otherwise a cached response can be served in the wrong language. An explicit `lang` already in $args is left untouched. Mutates a local copy only.
	 *
	 * @param string               $endpoint API endpoint path.
	 * @param array<string, mixed> $args     Arguments to hash into the cache key.
	 * @return string
	 */
	private static function key( string $endpoint, array $args ): string {
		if ( ! isset( $args['lang'] ) || (string) $args['lang'] === '' ) {
			$lang = \RoxyAPI\Support\Language::resolve();
			if ( $lang !== '' ) {
				$args['lang'] = $lang;
			}
		}
		return 'roxyapi_' . md5( $endpoint . '|' . wp_json_encode( $args ) );
	}

	public static function flush_all(): void {
		global $wpdb;
		$value_like   = '_transient_' . $wpdb->esc_like( 'roxyapi_' ) . '%';
		$timeout_like = '_transient_timeout_' . $wpdb->esc_like( 'roxyapi_' ) . '%';
		// Direct DELETE is the only way to drop matching `_transient_*`
		// rows in a single query — there is no core API for "flush every
		// transient whose name matches this prefix". WordPress's own
		// `delete_expired_transients` and `wp_cache_flush_group` work
		// the same way. Caching the DELETE itself makes no sense (it is
		// a write; there is nothing to cache). $wpdb->prepare + esc_like
		// guard the LIKE pattern.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				$value_like,
				$timeout_like
			)
		);
	}
}
