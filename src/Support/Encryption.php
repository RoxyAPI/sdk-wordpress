<?php
/**
 * AES-256-CTR encryption for storing third-party API credentials at rest.
 *
 * Ports the Google Site Kit Data_Encryption pattern. OpenSSL only, zero
 * Composer dependencies. Random IV per encrypt call. Returns base64(iv . cipher).
 *
 * Key derivation precedence:
 *   1. ROXYAPI_ENCRYPTION_KEY constant (recommended for production hosts)
 *   2. LOGGED_IN_KEY (always defined by WordPress core)
 *
 * Salt derivation precedence:
 *   1. ROXYAPI_ENCRYPTION_SALT constant
 *   2. LOGGED_IN_SALT
 *
 * NEVER store the encryption key inside wp_options. It must live outside
 * the database for encryption at rest to provide any value.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Encryption {

	private const METHOD = 'aes-256-ctr';

	/**
	 * Encrypt a string value using AES-256-CTR.
	 *
	 * @param string $value Plaintext to encrypt.
	 * @return string|false Base64-encoded ciphertext, or false on failure.
	 */
	public static function encrypt( string $value ) {
		if ( $value === '' ) {
			return '';
		}
		if ( ! extension_loaded( 'openssl' ) ) {
			return false;
		}
		$key  = self::key();
		$salt = self::salt();
		if ( $key === false || $salt === false ) {
			return false;
		}
		$ivlen = openssl_cipher_iv_length( self::METHOD );
		if ( $ivlen === false ) {
			return false;
		}
		$iv  = openssl_random_pseudo_bytes( $ivlen );
		$raw = openssl_encrypt(
			$value . $salt,
			self::METHOD,
			$key,
			OPENSSL_RAW_DATA,
			$iv
		);
		if ( $raw === false ) {
			return false;
		}
		return base64_encode( $iv . $raw );
	}

	/**
	 * Decrypt a previously encrypted value.
	 *
	 * @param string $value Base64-encoded ciphertext.
	 * @return string|false Decrypted plaintext, or false on failure.
	 */
	public static function decrypt( string $value ) {
		if ( $value === '' ) {
			return '';
		}
		if ( ! extension_loaded( 'openssl' ) ) {
			return false;
		}
		$key  = self::key();
		$salt = self::salt();
		if ( $key === false || $salt === false ) {
			return false;
		}
		$decoded = base64_decode( $value, true );
		if ( $decoded === false ) {
			return false;
		}
		$ivlen = openssl_cipher_iv_length( self::METHOD );
		if ( $ivlen === false || strlen( $decoded ) <= $ivlen ) {
			return false;
		}
		$iv     = substr( $decoded, 0, $ivlen );
		$cipher = substr( $decoded, $ivlen );
		$raw    = openssl_decrypt(
			$cipher,
			self::METHOD,
			$key,
			OPENSSL_RAW_DATA,
			$iv
		);
		if ( $raw === false ) {
			return false;
		}
		if ( substr( $raw, -strlen( $salt ) ) !== $salt ) {
			return false;
		}
		return substr( $raw, 0, -strlen( $salt ) );
	}

	/**
	 * Resolve the encryption key.
	 *
	 * Returns false (rather than a hardcoded fallback) when no real key is
	 * available. Callers must surface the failure so the user can configure
	 * `ROXYAPI_ENCRYPTION_KEY` or fix their WordPress install. Encryption
	 * with a hardcoded key would be encryption-at-rest in name only.
	 *
	 * @return string|false
	 */
	private static function key() {
		if ( defined( 'ROXYAPI_ENCRYPTION_KEY' ) && ROXYAPI_ENCRYPTION_KEY ) {
			return (string) ROXYAPI_ENCRYPTION_KEY;
		}
		if ( defined( 'LOGGED_IN_KEY' ) && LOGGED_IN_KEY && LOGGED_IN_KEY !== 'put your unique phrase here' ) {
			return (string) LOGGED_IN_KEY;
		}
		return false;
	}

	/**
	 * Resolve the encryption salt. See key() for the no-fallback rationale.
	 *
	 * @return string|false
	 */
	private static function salt() {
		if ( defined( 'ROXYAPI_ENCRYPTION_SALT' ) && ROXYAPI_ENCRYPTION_SALT ) {
			return (string) ROXYAPI_ENCRYPTION_SALT;
		}
		if ( defined( 'LOGGED_IN_SALT' ) && LOGGED_IN_SALT && LOGGED_IN_SALT !== 'put your unique phrase here' ) {
			return (string) LOGGED_IN_SALT;
		}
		return false;
	}
}
