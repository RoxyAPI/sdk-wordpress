<?php
/**
 * REST route for editor preview rendering of blocks.
 *
 * Future replacement for ServerSideRender. Hits a permission-gated route
 * that runs the block render server side and returns HTML, so the API key
 * never reaches the browser.
 *
 * Implementation deferred. See docs/wordpress-plugin.md section 9.7.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Api;

class PreviewRoute {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		register_rest_route(
			'roxyapi/v1',
			'/preview/(?P<block>[a-z0-9_-]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'render' ),
				'permission_callback' => static function () {
					return current_user_can( 'edit_posts' );
				},
				'args'                => array(
					'block' => array(
						'sanitize_callback' => 'sanitize_key',
					),
				),
			)
		);
	}

	/**
	 * Render a block preview via REST.
	 *
	 * @param \WP_REST_Request $request REST request instance.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function render( \WP_REST_Request $request ) {
		// TODO: dispatch on block name to the matching render callback.
		return rest_ensure_response( array( 'html' => '' ) );
	}
}
