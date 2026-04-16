<?php
/**
 * Hero shortcode: [roxy_life_path]
 *
 * Usage: [roxy_life_path year="1990" month="5" day="15"]
 *
 * Calculates the Life Path number for the given birth date via the RoxyAPI client.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Shortcodes;

use RoxyAPI\Blocks\Renderer;
use RoxyAPI\Support\Templates;

class LifePath {

	/**
	 * Render the life path shortcode.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @param string                       $content Inner content.
	 * @param string                       $tag Shortcode tag.
	 * @return string
	 */
	public static function render( $atts, $content = '', $tag = '' ): string {
		$atts = shortcode_atts(
			array(
				'year'  => '',
				'month' => '',
				'day'   => '',
			),
			is_array( $atts ) ? $atts : array(),
			(string) $tag
		);

		wp_enqueue_style( 'roxyapi-frontend' );

		if ( $atts['year'] === '' || $atts['month'] === '' || $atts['day'] === '' ) {
			return Templates::error( __( 'The year, month, and day attributes are required. Example: [roxy_life_path year="1990" month="5" day="15"]', 'roxyapi' ) );
		}

		$body = array(
			'year'  => (int) $atts['year'],
			'month' => (int) $atts['month'],
			'day'   => (int) $atts['day'],
		);

		$data = \RoxyAPI\Generated\Client::calculateLifePath( $body );

		if ( is_wp_error( $data ) ) {
			return Templates::error( $data->get_error_message() );
		}

		return Renderer::render_generic( 'calculateLifePath', is_array( $data ) ? $data : array() );
	}
}
