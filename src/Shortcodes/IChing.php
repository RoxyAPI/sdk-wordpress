<?php
/**
 * Hero shortcode: [roxy_iching]
 *
 * Usage: [roxy_iching] or [roxy_iching number="1"]
 *
 * Casts a random I Ching reading, or looks up a specific hexagram by number.
 * Both attributes are optional. When no number is given, a random cast is
 * performed.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Shortcodes;

use RoxyAPI\Blocks\Renderer;
use RoxyAPI\Support\Templates;

class IChing {

	/**
	 * Render the I Ching shortcode.
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

		// Look up a specific hexagram when number is provided.
		if ( $atts['number'] !== '' ) {
			$data = \RoxyAPI\Generated\Client::getHexagram( $atts['number'] );

			if ( is_wp_error( $data ) ) {
				return Templates::error( $data->get_error_message() );
			}

			return Renderer::render_generic( 'getHexagram', is_array( $data ) ? $data : array() );
		}

		// Otherwise cast a random reading.
		$data = \RoxyAPI\Generated\Client::castReading();

		if ( is_wp_error( $data ) ) {
			return Templates::error( $data->get_error_message() );
		}

		return Renderer::render_generic( 'castReading', is_array( $data ) ? $data : array() );
	}
}
