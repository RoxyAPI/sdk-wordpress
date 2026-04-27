<?php
/**
 * REST route backing the Settings → Test Connection button.
 *
 * Hits a cheap cached endpoint with the saved API key and reports back
 * whether RoxyAPI accepted it. The key itself never appears in the response.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Support\ApiKey;
use RoxyAPI\Support\RateLimit;
use WP_Error;
use WP_REST_Response;

class TestKeyRoute {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		register_rest_route(
			'roxyapi/v1',
			'/test-key',
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'handle' ),
				'permission_callback' => static function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * Verify the configured API key against a cheap cached endpoint.
	 *
	 * @return WP_REST_Response
	 */
	public static function handle(): WP_REST_Response {
		if ( ! ApiKey::is_configured() ) {
			return rest_ensure_response(
				array(
					'ok'      => false,
					'message' => __( 'No API key configured.', 'roxyapi' ),
				)
			);
		}

		// Rate limit before any outbound API call so a stuck-button UI cannot
		// drain the site owner's RoxyAPI quota. Per-IP scope matches RateLimit's
		// default (admin clicks share an IP, so this also bounds a single admin).
		if ( ! RateLimit::check( 'test_key' ) ) {
			return rest_ensure_response(
				array(
					'ok'      => false,
					'message' => __( 'Too many connection tests. Try again in an hour.', 'roxyapi' ),
				)
			);
		}

		$result = Client::get( '/astrology/horoscope/aries/daily' );

		if ( $result instanceof WP_Error ) {
			return rest_ensure_response(
				array(
					'ok'      => false,
					'message' => $result->get_error_message(),
				)
			);
		}

		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => __( 'Connected.', 'roxyapi' ),
			)
		);
	}
}
