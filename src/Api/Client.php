<?php
/**
 * Runtime HTTP client for RoxyAPI.
 *
 * Wraps wp_remote_request with the X-API-Key header, custom User Agent,
 * and standard error handling. The auto-generated Client in src/Generated/
 * delegates here. Hero shortcodes also delegate here via the generated client.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Api;

use RoxyAPI\Support\ApiKey;
use WP_Error;

class Client {

	private const BASE_URL = 'https://roxyapi.com/api/v2';
	private const TIMEOUT  = 10;

	/**
	 * Send a GET request to the RoxyAPI.
	 *
	 * @param string                $endpoint API path segment.
	 * @param array<string, string> $query    Query parameters.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function get( string $endpoint, array $query = array() ) {
		return self::request( 'GET', $endpoint, $query );
	}

	/**
	 * Send a POST request to the RoxyAPI.
	 *
	 * @param string               $endpoint API path segment.
	 * @param array<string, mixed> $body     Request body.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function post( string $endpoint, array $body = array() ) {
		return self::request( 'POST', $endpoint, $body );
	}

	/**
	 * Execute an HTTP request against the RoxyAPI.
	 *
	 * @param string               $method   HTTP method.
	 * @param string               $endpoint API path segment.
	 * @param array<string, mixed> $payload  Query params for GET, body for POST.
	 * @return array<string, mixed>|WP_Error
	 */
	private static function request( string $method, string $endpoint, array $payload ) {
		$key = ApiKey::get();
		if ( $key === '' ) {
			return new WP_Error(
				'roxyapi_no_key',
				__( 'RoxyAPI key not configured. Visit Settings then RoxyAPI.', 'roxyapi' )
			);
		}

		// Inject the site-owner's preferred display language into the
		// request when the caller hasn't already set one. Empty setting
		// means "match the WP locale", which we resolve here once.
		$payload = self::maybe_inject_language( $payload );

		$url  = self::BASE_URL . '/' . ltrim( $endpoint, '/' );
		$args = array(
			'method'      => $method,
			'timeout'     => self::TIMEOUT,
			'redirection' => 2,
			'headers'     => array(
				'X-API-Key'    => $key,
				'X-SDK-Client' => 'roxy-sdk-wordpress/' . ROXYAPI_VERSION,
				'X-Site-URL'   => home_url( '/' ),
				'Accept'       => 'application/json',
				'User-Agent'   => 'roxy-sdk-wordpress/' . ROXYAPI_VERSION . ' (+' . home_url( '/' ) . ')',
			),
		);

		if ( $method === 'GET' && $payload ) {
			$url = add_query_arg( array_map( 'rawurlencode', $payload ), $url );
		} elseif ( $method === 'POST' ) {
			// Empty PHP arrays encode as JSON `[]`, but every RoxyAPI POST
			// schema expects an object body. Cast to stdClass so an
			// attribute-less call sends `{}` instead of `[]`.
			$encoded = wp_json_encode( empty( $payload ) ? (object) array() : $payload );
			if ( $encoded === false ) {
				return new WP_Error( 'roxyapi_json_encode', __( 'Could not encode request body as JSON.', 'roxyapi' ) );
			}
			$args['headers']['Content-Type'] = 'application/json';
			$args['body']                    = $encoded;
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( $code !== 200 ) {
			return self::error_from_response( $code, $body );
		}

		$decoded = json_decode( $body, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'roxyapi_json', __( 'Invalid JSON from RoxyAPI', 'roxyapi' ) );
		}
		return $decoded;
	}

	/**
	 * Inject the configured display language into a payload when the caller
	 * hasn't supplied one. Empty setting falls back to the WordPress locale,
	 * mapped to the spec's accepted `lang` codes (en/de/hi/es/tr/pt/fr/ru).
	 * Locales outside that set fall through to no `lang` (SaaS default).
	 *
	 * @param array<string, mixed> $payload Outgoing query (GET) or body (POST) payload.
	 * @return array<string, mixed>
	 */
	private static function maybe_inject_language( array $payload ): array {
		if ( isset( $payload['lang'] ) && (string) $payload['lang'] !== '' ) {
			return $payload;
		}
		$opts = \RoxyAPI\Admin\SettingsSchema::get_option();
		$lang = isset( $opts['display_language'] ) ? (string) $opts['display_language'] : '';
		if ( $lang === '' ) {
			$wp_locale = (string) get_locale();
			$prefix    = strtolower( substr( $wp_locale, 0, 2 ) );
			$supported = array( 'en', 'de', 'hi', 'es', 'tr', 'pt', 'fr', 'ru' );
			$lang      = in_array( $prefix, $supported, true ) ? $prefix : '';
		}
		if ( $lang !== '' ) {
			$payload['lang'] = $lang;
		}
		return $payload;
	}

	/**
	 * Translate a non-200 RoxyAPI response into a categorized WP_Error.
	 *
	 * The error data carries the HTTP status, the SaaS-side machine code (when
	 * present), and the raw upstream message so admins can debug from the
	 * `data` property while visitors only see the friendly `get_error_message()`
	 * text. Public-facing renderers must NOT echo `data` fields.
	 *
	 * @param int    $status HTTP status code from RoxyAPI.
	 * @param string $body   Raw response body. May be JSON `{error, code}`.
	 * @return WP_Error
	 */
	private static function error_from_response( int $status, string $body ): WP_Error {
		$decoded         = json_decode( $body, true );
		$saas_code       = '';
		$saas_msg        = '';
		$saas_suggestion = '';
		$saas_hint       = '';
		if ( is_array( $decoded ) ) {
			$saas_code = isset( $decoded['code'] ) ? (string) $decoded['code'] : '';
			$saas_msg  = isset( $decoded['error'] ) ? (string) $decoded['error'] : '';
			// 404 responses from the SaaS carry a fuzzy-match `suggestion`
			// (the correct method + path the caller probably meant) and a
			// `hint` (one-line endpoint summary). Captured here so the
			// admin-only diagnostic line in `Templates::api_error` can surface
			// "Did you mean ..." instead of just the bare 404 message.
			$saas_suggestion = isset( $decoded['suggestion'] ) ? (string) $decoded['suggestion'] : '';
			$saas_hint       = isset( $decoded['hint'] ) ? (string) $decoded['hint'] : '';
		}

		// Map status to a categorized error code that hero shortcodes and the
		// generic renderer can pattern-match on for friendly visitor messages.
		if ( $status === 401 || $status === 403 ) {
			$code    = 'roxyapi_auth';
			$message = __( 'This reading is temporarily unavailable. The site administrator needs to verify the connection.', 'roxyapi' );
		} elseif ( $status === 429 ) {
			$code    = 'roxyapi_quota';
			$message = __( 'Daily readings are temporarily unavailable. Please check back later.', 'roxyapi' );
		} elseif ( $status >= 500 ) {
			$code    = 'roxyapi_upstream';
			$message = __( 'The reading service is temporarily unavailable. Please try again in a few minutes.', 'roxyapi' );
		} else {
			$code    = 'roxyapi_http_' . $status;
			$message = __( 'This reading could not be loaded right now. Please try again.', 'roxyapi' );
		}

		return new WP_Error(
			$code,
			$message,
			array(
				'status'          => $status,
				'saas_code'       => $saas_code,
				'saas_msg'        => $saas_msg,
				'saas_suggestion' => $saas_suggestion,
				'saas_hint'       => $saas_hint,
			)
		);
	}
}
