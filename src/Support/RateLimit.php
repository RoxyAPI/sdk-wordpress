<?php
/**
 * Rate limiter for visitor submitted shortcode forms.
 *
 * Protects the site owner's RoxyAPI quota from abuse. Every form submission
 * is counted in a transient keyed by IP and shortcode name. Returns false
 * once the per hour limit is reached.
 *
 * The scope string is the bucket, so a visitor typing into the city search
 * cannot exhaust the budget for submitting a form. The ceiling has to clear
 * the city search, which spends several lookups per debounced field entry.
 *
 * The IP comes from `REMOTE_ADDR` only unless the site opts a forwarded header
 * back in. See {@link RateLimit::client_ip} for why that default is not
 * negotiable.
 *
 * `rate_limit_per_hour` in `roxyapi_settings` overrides the default. No admin
 * field writes that key today, so a site sets it through the settings schema
 * filter or not at all.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Admin\SettingsSchema;

class RateLimit {

	public const DEFAULT_LIMIT  = 100;
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

	/**
	 * Resolve the IP this bucket is keyed on.
	 *
	 * `REMOTE_ADDR` is the only value a WordPress site can actually trust. A
	 * forwarded header is supplied by the caller unless a proxy the site owns
	 * overwrote it, and a plugin cannot know the host's proxy topology, so
	 * reading one by default made the limit decorative: changing a single digit
	 * in `CF-Connecting-IP` minted a fresh bucket on every request.
	 *
	 * Behind a reverse proxy with no opt-in every visitor therefore shares one
	 * bucket. That trade is deliberate. A shared bucket is a false positive that
	 * throttles honest visitors during a real attack; a spoofable bucket is not
	 * a limit at all, and this is the only quota control on the public geocode
	 * route and on visitor form submissions. Sites that terminate on a proxy
	 * they control opt back in per header via `roxyapi_trusted_proxy_headers`.
	 *
	 * The same position is taken by WordPress core, which stores `REMOTE_ADDR`
	 * directly as the comment author IP and tells proxied sites to correct it in
	 * `wp-config.php` (`wp-includes/comment.php`, `wp_new_comment()`), and by
	 * Jetpack, which reads a forwarded header only when the site has recorded
	 * one in its `trusted_ip_header` option (`jetpack-ip`, `Utils::get_ip()`).
	 */
	private static function client_ip(): string {
		/**
		 * Filter the detected client IP before rate limiting.
		 *
		 * Lets site owners behind custom proxies (AWS ALB, Fastly, custom CDNs)
		 * inject their own IP detection. Return a non-empty string to override.
		 * Return empty to fall through to the default detection.
		 *
		 * @param string $ip Currently detected IP (empty string before detection).
		 */
		$filtered = apply_filters( 'roxyapi_client_ip', '' );
		if ( is_string( $filtered ) ) {
			$override = self::normalize_ip( $filtered );
			if ( $override !== '' ) {
				return $override;
			}
		}

		$remote = self::header_ip( 'REMOTE_ADDR' );

		/**
		 * Filter which forwarded headers are trusted for rate limiting.
		 *
		 * EMPTY BY DEFAULT, and leaving it empty is the safe choice: anything
		 * listed here can be set by whoever is making the request unless a proxy
		 * you control rewrites it on every hop. Return `$_SERVER` key names,
		 * most trusted first, only for headers your own edge guarantees:
		 *
		 *     add_filter(
		 *         'roxyapi_trusted_proxy_headers',
		 *         function ( $headers, $remote_addr ) {
		 *             return array( 'HTTP_CF_CONNECTING_IP' );
		 *         },
		 *         10,
		 *         2
		 *     );
		 *
		 * `$remote_addr` is passed so a site can trust a header only while the
		 * connection really is arriving from one of its proxies.
		 *
		 * @param string[] $headers     `$_SERVER` keys to trust, in order. Default none.
		 * @param string   $remote_addr Validated connecting IP, empty when unusable.
		 */
		$trusted = apply_filters( 'roxyapi_trusted_proxy_headers', array(), $remote );
		if ( is_array( $trusted ) ) {
			foreach ( $trusted as $header ) {
				$forwarded = self::header_ip( (string) $header );
				if ( $forwarded !== '' ) {
					return $forwarded;
				}
			}
		}

		return $remote !== '' ? $remote : '0.0.0.0';
	}

	/**
	 * Read one `$_SERVER` entry and reduce it to a single validated IP.
	 *
	 * @param string $header `$_SERVER` key, e.g. `REMOTE_ADDR` or `HTTP_CF_CONNECTING_IP`.
	 * @return string Validated IP, or empty string when absent or unusable.
	 */
	private static function header_ip( string $header ): string {
		if ( $header === '' || empty( $_SERVER[ $header ] ) ) {
			return '';
		}
		return self::normalize_ip( sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ) );
	}

	/**
	 * Reduce a raw header value to a validated IP.
	 *
	 * Takes the left-most entry of a comma list, which is the client the first
	 * proxy saw, then strips the port and IPv6 brackets a proxy may append so a
	 * legitimate address is not discarded over formatting.
	 *
	 * @param string $value Raw header value.
	 * @return string Validated IP, or empty string when it is not one.
	 */
	private static function normalize_ip( string $value ): string {
		$value = trim( explode( ',', $value )[0] );
		if ( preg_match( '/^\[(.+)\](?::\d+)?$/', $value, $matches ) ) {
			$value = $matches[1];
		} elseif ( preg_match( '/^(\d{1,3}(?:\.\d{1,3}){3}):\d+$/', $value, $matches ) ) {
			$value = $matches[1];
		}
		return filter_var( $value, FILTER_VALIDATE_IP ) ? $value : '';
	}

	private static function limit(): int {
		$opts = SettingsSchema::get_option();
		if ( isset( $opts['rate_limit_per_hour'] ) ) {
			return max( 1, (int) $opts['rate_limit_per_hour'] );
		}
		return self::DEFAULT_LIMIT;
	}
}
