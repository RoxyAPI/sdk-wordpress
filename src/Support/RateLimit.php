<?php
/**
 * Rate limiter for visitor submitted shortcode forms.
 *
 * Protects the site owner's RoxyAPI quota from abuse. Every form submission
 * is counted in a transient keyed by IP and shortcode name. Returns false
 * once the per hour limit is reached.
 *
 * Configurable via Settings, RoxyAPI, Cache tab.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Admin\SettingsSchema;

class RateLimit {

	public const DEFAULT_LIMIT  = 20;
	public const DEFAULT_WINDOW = HOUR_IN_SECONDS;

	/**
	 * Check (and increment) the rate limit for a shortcode submission.
	 *
	 * @param string $shortcode Shortcode name used as the rate limit scope.
	 * @return bool True if within limit, false if limit reached.
	 */
	public static function check( string $shortcode ): bool {
		$key     = self::key( $shortcode );
		$current = (int) get_transient( $key );
		$limit   = self::limit();

		if ( $current >= $limit ) {
			return false;
		}
		set_transient( $key, $current + 1, self::DEFAULT_WINDOW );
		return true;
	}

	private static function key( string $shortcode ): string {
		$ip = self::client_ip();
		return 'roxyapi_rl_' . md5( $ip . '|' . $shortcode );
	}

	private static function client_ip(): string {
		/**
		 * Filter the detected client IP before rate limiting.
		 *
		 * Lets site owners behind custom proxies (AWS ALB, Fastly, custom CDNs)
		 * inject their own IP detection. Return a non-empty string to override.
		 * Return empty to fall through to the default header walk.
		 *
		 * @param string $ip Currently detected IP (empty string before detection).
		 */
		$filtered = apply_filters( 'roxyapi_client_ip', '' );
		if ( is_string( $filtered ) && $filtered !== '' && filter_var( $filtered, FILTER_VALIDATE_IP ) ) {
			return $filtered;
		}

		$candidates = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
		foreach ( $candidates as $header ) {
			if ( empty( $_SERVER[ $header ] ) ) {
				continue;
			}
			$value = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
			if ( $header === 'HTTP_X_FORWARDED_FOR' ) {
				$parts = explode( ',', $value );
				$value = trim( $parts[0] );
			}
			if ( filter_var( $value, FILTER_VALIDATE_IP ) ) {
				return $value;
			}
		}
		return '0.0.0.0';
	}

	private static function limit(): int {
		$opts = SettingsSchema::get_option();
		if ( isset( $opts['rate_limit_per_hour'] ) ) {
			return max( 1, (int) $opts['rate_limit_per_hour'] );
		}
		return self::DEFAULT_LIMIT;
	}
}
