<?php
/**
 * Hero shortcode: [roxy_numerology]
 *
 * Usage: [roxy_numerology name="John Doe" year="1990" month="5" day="15"]
 *
 * Generates a complete numerology chart for the given name and birth date
 * via the RoxyAPI client.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Shortcodes;

use RoxyAPI\Blocks\Renderer;
use RoxyAPI\Support\Templates;

class Numerology {

	/**
	 * Render the numerology shortcode.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @param string                       $content Inner content.
	 * @param string                       $tag Shortcode tag.
	 * @return string
	 */
	public static function render( $atts, $content = '', $tag = '' ): string {
		$atts = shortcode_atts(
			array(
				'name'  => '',
				'year'  => '',
				'month' => '',
				'day'   => '',
			),
			is_array( $atts ) ? $atts : array(),
			(string) $tag
		);

		wp_enqueue_style( 'roxyapi-frontend' );

		if ( $atts['name'] === '' || $atts['year'] === '' || $atts['month'] === '' || $atts['day'] === '' ) {
			return Templates::error( __( 'The name, year, month, and day attributes are required. Example: [roxy_numerology name="John Doe" year="1990" month="5" day="15"]', 'roxyapi' ) );
		}

		$body = array(
			'name'  => $atts['name'],
			'year'  => (int) $atts['year'],
			'month' => (int) $atts['month'],
			'day'   => (int) $atts['day'],
		);

		$data = \RoxyAPI\Generated\Client::generateNumerologyChart( $body );

		if ( is_wp_error( $data ) ) {
			return Templates::error( $data->get_error_message() );
		}

		return Renderer::render_generic( 'generateNumerologyChart', is_array( $data ) ? $data : array() );
	}
}
