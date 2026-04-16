<?php
/**
 * Hero shortcode: [roxy_compatibility]
 *
 * Usage: [roxy_compatibility sign1="aries" sign2="leo"]
 *
 * Calculates zodiac compatibility between two signs via the RoxyAPI client.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Shortcodes;

use RoxyAPI\Blocks\Renderer;
use RoxyAPI\Support\Templates;

class Compatibility {

	/**
	 * Render the compatibility shortcode.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @param string                       $content Inner content.
	 * @param string                       $tag Shortcode tag.
	 * @return string
	 */
	public static function render( $atts, $content = '', $tag = '' ): string {
		$atts = shortcode_atts(
			array(
				'sign1' => '',
				'sign2' => '',
			),
			is_array( $atts ) ? $atts : array(),
			(string) $tag
		);

		wp_enqueue_style( 'roxyapi-frontend' );

		if ( $atts['sign1'] === '' || $atts['sign2'] === '' ) {
			return Templates::error( __( 'Both sign1 and sign2 attributes are required. Example: [roxy_compatibility sign1="aries" sign2="leo"]', 'roxyapi' ) );
		}

		$body = array(
			'sign1' => strtolower( $atts['sign1'] ),
			'sign2' => strtolower( $atts['sign2'] ),
		);

		$data = \RoxyAPI\Generated\Client::calculateCompatibility( $body );

		if ( is_wp_error( $data ) ) {
			return Templates::error( $data->get_error_message() );
		}

		return Renderer::render_generic( 'calculateCompatibility', is_array( $data ) ? $data : array() );
	}
}
