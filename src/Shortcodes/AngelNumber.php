<?php
/**
 * Hero shortcode: [roxy_angel_number]
 *
 * Usage: [roxy_angel_number number="111"]
 *
 * Looks up the meaning of the given angel number via the RoxyAPI client.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Shortcodes;

use RoxyAPI\Blocks\Renderer;
use RoxyAPI\Support\Templates;

class AngelNumber {

	/**
	 * Render the angel number shortcode.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @param string                       $content Inner content.
	 * @param string                       $tag Shortcode tag.
	 * @return string
	 */
	public static function render( $atts, $content = '', $tag = '' ): string {
		$atts = shortcode_atts(
			array( 'number' => '' ),
			is_array( $atts ) ? $atts : array(),
			(string) $tag
		);

		wp_enqueue_style( 'roxyapi-frontend' );

		if ( $atts['number'] === '' ) {
			return Templates::error( __( 'The number attribute is required. Example: [roxy_angel_number number="111"]', 'roxyapi' ) );
		}

		$data = \RoxyAPI\Generated\Client::getAngelNumber( $atts['number'] );

		if ( is_wp_error( $data ) ) {
			return Templates::error( $data->get_error_message() );
		}

		return Renderer::render_generic( 'getAngelNumber', is_array( $data ) ? $data : array() );
	}
}
