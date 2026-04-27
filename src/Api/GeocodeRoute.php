<?php
/**
 * Public REST proxy for the RoxyAPI city-search endpoint.
 *
 * Visitor-facing autocomplete needs lat / lon / IANA-timezone for a typed
 * city name without exposing the API key to the browser. This route accepts
 * `?q=<city>`, calls `Client::searchCities` server-side with a 24-hour
 * response cache, and returns a slim JSON list of `{label, lat, lon, tz}`
 * objects scoped to what the form needs.
 *
 * Public route (`permission_callback => __return_true`) because the use case
 * is logged-out visitors typing into a shortcode form. Abuse is bounded by
 * `RateLimit::check('geocode')` (per-IP transient bucket) and the response
 * cache absorbs repeat queries.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Generated\Client as GeneratedClient;
use RoxyAPI\Support\RateLimit;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class GeocodeRoute {

	public const NAMESPACE = 'roxyapi/v1';
	public const ROUTE     = '/geocode';

	/** Minimum query length before the route accepts a request. */
	private const MIN_LENGTH = 2;

	/** Maximum query length the SaaS will accept. */
	private const MAX_LENGTH = 100;

	/** Default page size for the upstream call. Stripped to top N for the UI. */
	private const UPSTREAM_LIMIT = 8;

	/** Cache TTL for a given query string. City coordinates do not move. */
	private const CACHE_TTL = DAY_IN_SECONDS;

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_route' ) );
	}

	public static function register_route(): void {
		register_rest_route(
			self::NAMESPACE,
			self::ROUTE,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'handle' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'q' => array(
							'type'              => 'string',
							'required'          => true,
							'minLength'         => self::MIN_LENGTH,
							'maxLength'         => self::MAX_LENGTH,
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
			)
		);
	}

	/**
	 * Handle a geocode request. Always returns a `WP_REST_Response`; failures
	 * surface as a sparse error envelope rather than an exception so the
	 * combobox JS can render an empty state.
	 *
	 * @param WP_REST_Request $request Incoming REST request carrying the `q` query.
	 * @return WP_REST_Response
	 */
	public static function handle( WP_REST_Request $request ): WP_REST_Response {
		$q = trim( (string) $request->get_param( 'q' ) );
		if ( strlen( $q ) < self::MIN_LENGTH ) {
			return self::error_response( 'roxyapi_geocode_q_short', 400, __( 'Query is too short.', 'roxyapi' ) );
		}
		if ( strlen( $q ) > self::MAX_LENGTH ) {
			return self::error_response( 'roxyapi_geocode_q_long', 400, __( 'Query is too long.', 'roxyapi' ) );
		}

		if ( ! RateLimit::check( 'geocode' ) ) {
			return self::error_response( 'roxyapi_geocode_rate_limit', 429, __( 'Too many requests. Slow down.', 'roxyapi' ) );
		}

		$result = Cache::remember(
			'location/search',
			array(
				'q'     => strtolower( $q ),
				'limit' => self::UPSTREAM_LIMIT,
			),
			self::CACHE_TTL,
			static function () use ( $q ) {
				return GeneratedClient::searchCities( $q, self::UPSTREAM_LIMIT );
			}
		);

		if ( is_wp_error( $result ) ) {
			return self::error_response( 'roxyapi_geocode_upstream', 502, __( 'Search is temporarily unavailable.', 'roxyapi' ) );
		}

		return new WP_REST_Response(
			array(
				'cities' => self::shape_cities( is_array( $result ) ? $result : array() ),
			),
			200
		);
	}

	/**
	 * Trim the upstream response to the four fields the autocomplete needs.
	 *
	 * @param array<string, mixed> $upstream Decoded RoxyAPI response.
	 * @return array<int, array{label: string, lat: float, lon: float, tz: string}>
	 */
	private static function shape_cities( array $upstream ): array {
		$cities = isset( $upstream['cities'] ) && is_array( $upstream['cities'] ) ? $upstream['cities'] : array();
		$out    = array();
		foreach ( $cities as $city ) {
			if ( ! is_array( $city ) ) {
				continue;
			}
			$lat = isset( $city['latitude'] ) && is_numeric( $city['latitude'] ) ? (float) $city['latitude'] : null;
			$lon = isset( $city['longitude'] ) && is_numeric( $city['longitude'] ) ? (float) $city['longitude'] : null;
			$tz  = isset( $city['timezone'] ) ? (string) $city['timezone'] : '';
			if ( $lat === null || $lon === null || $tz === '' ) {
				continue;
			}
			$parts = array_filter(
				array(
					(string) ( $city['city'] ?? '' ),
					(string) ( $city['province'] ?? '' ),
					(string) ( $city['country'] ?? '' ),
				),
				static fn( $part ) => $part !== ''
			);
			$out[] = array(
				'label' => implode( ', ', $parts ),
				'lat'   => $lat,
				'lon'   => $lon,
				'tz'    => $tz,
			);
		}
		return $out;
	}

	private static function error_response( string $code, int $status, string $message ): WP_REST_Response {
		$response = new WP_REST_Response(
			array(
				'cities' => array(),
				'error'  => array(
					'code'    => $code,
					'message' => $message,
				),
			),
			$status
		);
		// Soft cache hint so a CDN doesn't pin a 429 forever.
		$response->header( 'Cache-Control', 'private, max-age=0' );
		return $response;
	}
}
