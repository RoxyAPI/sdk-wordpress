<?php
/**
 * Hero shortcode: [roxy_crystal]
 *
 * Usage: [roxy_crystal slug="amethyst"]
 *
 * Looks up crystal healing properties by slug via the RoxyAPI client.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Shortcodes;

use RoxyAPI\Blocks\Renderer;
use RoxyAPI\Support\Templates;

class Crystal {

	/**
	 * Render the crystal shortcode.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @param string                       $content Inner content.
	 * @param string                       $tag Shortcode tag.
	 * @return string
	 */
	public static function render( $atts, $content = '', $tag = '' ): string {
		$atts = shortcode_atts(
			array( 'slug' => '' ),
			is_array( $atts ) ? $atts : array(),
			(string) $tag
		);

		wp_enqueue_style( 'roxyapi-frontend' );

		if ( $atts['slug'] === '' ) {
			return Templates::error( __( 'The slug attribute is required. Example: [roxy_crystal slug="amethyst"]', 'roxyapi' ) );
		}

		$data = \RoxyAPI\Generated\Client::getCrystal( $atts['slug'] );

		if ( is_wp_error( $data ) ) {
			return Templates::error( $data->get_error_message() );
		}

		return Renderer::render_generic( 'getCrystal', is_array( $data ) ? $data : array() );
	}
}
