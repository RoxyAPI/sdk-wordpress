<?php
/**
 * Public REST search over a collection a visitor form searches.
 *
 * A form field whose collection is too large to list (the dream dictionary)
 * renders as a search box. This route answers it: `?form=<form id>&field=<posted
 * name>&q=<typed word>` reads the matches server-side, through the response
 * cache, and returns `{label, value}` pairs. It reads ONLY the collection the
 * named generated form declares for that field, so it can never reach any
 * other API path, and it is rate limited per IP like the city search.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Support\FormRouter;
use RoxyAPI\Support\OptionSource;
use RoxyAPI\Support\RateLimit;
use WP_REST_Request;
use WP_REST_Response;

class SearchRoute {

	public const ROUTE = '/search';

	/** Shortest word worth a search; the combobox waits for as many characters. */
	private const MIN_LENGTH = 2;

	/** Longest word the API accepts. */
	private const MAX_LENGTH = 100;

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_route' ) );
	}

	public static function register_route(): void {
		register_rest_route(
			GeocodeRoute::NAMESPACE,
			self::ROUTE,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'handle' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'form'  => array(
							'type'     => 'string',
							'required' => true,
							'pattern'  => '^[a-zA-Z][a-zA-Z0-9]*$',
						),
						'field' => array(
							'type'      => 'string',
							'required'  => true,
							'maxLength' => self::MAX_LENGTH,
							'pattern'   => '^[a-zA-Z0-9_\[\]]+$',
						),
						'q'     => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
			)
		);
	}

	/**
	 * The URL a search box queries.
	 *
	 * @param string $form  Form id.
	 * @param string $field Posted name of the searched field.
	 * @return string
	 */
	public static function url( string $form, string $field ): string {
		return add_query_arg(
			array(
				'form'  => $form,
				'field' => $field,
			),
			rest_url( GeocodeRoute::NAMESPACE . self::ROUTE )
		);
	}

	/**
	 * Answer one typed word. Failures come back as the shared refusal, already
	 * translated, so the search box can say why it shows nothing.
	 *
	 * @param WP_REST_Request $request Incoming request carrying `form`, `field` and `q`.
	 * @return WP_REST_Response
	 */
	public static function handle( WP_REST_Request $request ): WP_REST_Response {
		$q      = trim( (string) $request->get_param( 'q' ) );
		$length = mb_strlen( $q, 'UTF-8' );
		if ( $length < self::MIN_LENGTH ) {
			return GeocodeRoute::refusal( 'results', 'roxyapi_search_q_short', 400, __( 'Query is too short.', 'roxyapi' ) );
		}
		if ( $length > self::MAX_LENGTH ) {
			return GeocodeRoute::refusal( 'results', 'roxyapi_search_q_long', 400, __( 'Query is too long.', 'roxyapi' ) );
		}
		if ( ! RateLimit::check( 'search' ) ) {
			return GeocodeRoute::refusal( 'results', 'roxyapi_search_rate_limit', 429, __( 'Too many searches. Try again shortly.', 'roxyapi' ) );
		}

		$field  = (string) $request->get_param( 'field' );
		$source = self::source( (string) $request->get_param( 'form' ), $field );
		if ( $source === null ) {
			return GeocodeRoute::refusal( 'results', 'roxyapi_search_unknown', 404, __( 'Search is temporarily unavailable.', 'roxyapi' ) );
		}

		// A record without an `id` carries its value under the field's own name, the last segment of a posted `section[field]`.
		$matches = OptionSource::search( $source, (string) preg_replace( '/^.*\[([^\]]+)\]$/', '$1', $field ), $q );
		if ( is_wp_error( $matches ) ) {
			return GeocodeRoute::refusal( 'results', 'roxyapi_search_upstream', 502, __( 'Search is temporarily unavailable.', 'roxyapi' ) );
		}

		$results = array();
		foreach ( $matches as $value => $label ) {
			$results[] = array(
				'label' => $label,
				'value' => (string) $value,
			);
		}
		return new WP_REST_Response( array( 'results' => $results ), 200 );
	}

	/**
	 * The collection a generated form searches under this posted name, read
	 * from its `SEARCHED` constant so no other collection of the form is read.
	 *
	 * @param string $form  Form id.
	 * @param string $field Posted name.
	 * @return array<string, mixed>|null Searched source, or null when the form has no such search.
	 */
	private static function source( string $form, string $field ): ?array {
		$class = FormRouter::form_class( $form );
		if ( $class === null || ! defined( $class . '::SEARCHED' ) ) {
			return null;
		}
		$searched = constant( $class . '::SEARCHED' );
		return is_array( $searched[ $field ] ?? null ) ? $searched[ $field ] : null;
	}
}
