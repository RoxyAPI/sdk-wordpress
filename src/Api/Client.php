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
			$args['headers']['Content-Type'] = 'application/json';
			$args['body']                    = wp_json_encode( $payload );
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( (int) $code !== 200 ) {
			return new WP_Error(
				'roxyapi_http_' . $code,
				sprintf(
					/* translators: %d HTTP status code */
					__( 'RoxyAPI returned HTTP %d', 'roxyapi' ),
					(int) $code
				)
			);
		}

		$decoded = json_decode( $body, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'roxyapi_json', __( 'Invalid JSON from RoxyAPI', 'roxyapi' ) );
		}
		return $decoded;
	}
}
