<?php
/**
 * Single source of truth for resolving the RoxyAPI key.
 *
 * Resolution order:
 *   1. ROXYAPI_KEY constant (wp-config.php override)
 *   2. Encrypted value in roxyapi_settings option, decrypted via Encryption helper
 *   3. Empty string (caller renders a friendly placeholder)
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

class ApiKey {

	/**
	 * Resolve the API key from constant or encrypted option.
	 *
	 * @return string The API key, or empty string if not configured.
	 */
	public static function get(): string {
		if ( defined( 'ROXYAPI_KEY' ) && ROXYAPI_KEY ) {
			return (string) ROXYAPI_KEY;
		}
		$opts = get_option( 'roxyapi_settings', array() );
		$enc  = is_array( $opts ) ? (string) ( $opts['api_key_encrypted'] ?? '' ) : '';
		if ( $enc === '' ) {
			return '';
		}
		$plain = Encryption::decrypt( $enc );
		return $plain === false ? '' : (string) $plain;
	}

	/**
	 * Whether a valid API key is available.
	 *
	 * @return bool
	 */
	public static function is_configured(): bool {
		return self::get() !== '';
	}

	/**
	 * Whether the API key is defined via the ROXYAPI_KEY constant.
	 *
	 * @return bool
	 */
	public static function is_defined_via_constant(): bool {
		return defined( 'ROXYAPI_KEY' ) && ROXYAPI_KEY;
	}

	/**
	 * Return a masked version of the API key for display.
	 *
	 * @return string Masked key (e.g. "********abcd"), or empty if too short.
	 */
	public static function masked(): string {
		$key = self::get();
		if ( strlen( $key ) < 8 ) {
			return '';
		}
		return str_repeat( '*', 8 ) . substr( $key, -4 );
	}
}
