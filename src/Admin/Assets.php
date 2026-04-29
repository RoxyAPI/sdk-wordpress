<?php
/**
 * Shared enqueue helpers for the admin surface.
 *
 * Centralises the duplicated wp_enqueue_style + wp_enqueue_script + wp_localize_script
 * boilerplate that previously lived inline in every admin page's enqueue() method.
 * The localize call is the canonical site for the RoxyAPIAdmin global; calling
 * wp_localize_script twice with the same handle / global REPLACES, so consolidating
 * here is correctness, not just hygiene.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {

	public static function enqueue_admin_css(): void {
		wp_enqueue_style(
			'roxyapi-admin',
			plugins_url( 'assets/css/admin.css', ROXYAPI_PLUGIN_FILE ),
			array(),
			ROXYAPI_VERSION
		);
	}

	public static function enqueue_admin_script(): void {
		wp_enqueue_script(
			'roxyapi-admin',
			plugins_url( 'assets/js/admin.js', ROXYAPI_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			ROXYAPI_VERSION,
			true
		);
		wp_localize_script(
			'roxyapi-admin',
			'RoxyAPIAdmin',
			array(
				'strings' => array(
					'testing'         => __( 'Testing...', 'roxyapi' ),
					'connected'       => __( 'Connected.', 'roxyapi' ),
					'connectedBanner' => __( 'Connected to Roxy. Paste a shortcode below to render a reading on any page.', 'roxyapi' ),
					'noKey'           => __( 'Paste your API key in the field above and save before testing.', 'roxyapi' ),
					'invalidKey'      => __( 'That key was rejected. Double check it on your roxyapi.com dashboard, paste it again, and save.', 'roxyapi' ),
					'requestFailed'   => __( 'Connection test failed. Try again in a moment.', 'roxyapi' ),
					'copied'          => __( 'Copied', 'roxyapi' ),
					'copyFailed'      => __( 'Copy failed', 'roxyapi' ),
					'copy'            => __( 'Copy', 'roxyapi' ),
				),
			)
		);
	}
}
