<?php
/**
 * Hero shortcode: [roxy_natal_chart]
 *
 * Usage: [roxy_natal_chart date="1990-05-15" time="10:30" latitude="40.7128" longitude="-74.0060"]
 *
 * Generates a natal (birth) chart for the given date, time, and location
 * via the RoxyAPI client.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Shortcodes;

use RoxyAPI\Blocks\Renderer;
use RoxyAPI\Support\Templates;

class NatalChart {

	/**
	 * Render the natal chart shortcode.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @param string                       $content Inner content.
	 * @param string                       $tag Shortcode tag.
	 * @return string
	 */
	public static function render( $atts, $content = '', $tag = '' ): string {
		$atts = shortcode_atts(
			array(
				'date'      => '',
				'time'      => '',
				'latitude'  => '',
				'longitude' => '',
			),
			is_array( $atts ) ? $atts : array(),
			(string) $tag
		);

		wp_enqueue_style( 'roxyapi-frontend' );

		if ( $atts['date'] === '' || $atts['time'] === '' || $atts['latitude'] === '' || $atts['longitude'] === '' ) {
			return Templates::error( __( 'The date, time, latitude, and longitude attributes are required. Example: [roxy_natal_chart date="1990-05-15" time="10:30" latitude="40.7128" longitude="-74.0060"]', 'roxyapi' ) );
		}

		$body = array(
			'date'      => $atts['date'],
			'time'      => $atts['time'],
			'latitude'  => (float) $atts['latitude'],
			'longitude' => (float) $atts['longitude'],
		);

		$data = \RoxyAPI\Generated\Client::generateNatalChart( $body );

		if ( is_wp_error( $data ) ) {
			return Templates::error( $data->get_error_message() );
		}

		return Renderer::render_generic( 'generateNatalChart', is_array( $data ) ? $data : array() );
	}
}
