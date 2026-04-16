<?php
/**
 * Hero shortcode: [roxy_biorhythm]
 *
 * Usage: [roxy_biorhythm birth_date="1990-05-15"] or
 *        [roxy_biorhythm birth_date="1990-05-15" date="2026-04-16"]
 *
 * Computes a biorhythm reading for the given birth date. The target date
 * defaults to today when omitted.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Shortcodes;

use RoxyAPI\Blocks\Renderer;
use RoxyAPI\Support\Templates;

class Biorhythm {

	/**
	 * Render the biorhythm shortcode.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @param string                       $content Inner content.
	 * @param string                       $tag Shortcode tag.
	 * @return string
	 */
	public static function render( $atts, $content = '', $tag = '' ): string {
		$atts = shortcode_atts(
			array(
				'birth_date' => '',
				'date'       => '',
			),
			is_array( $atts ) ? $atts : array(),
			(string) $tag
		);

		wp_enqueue_style( 'roxyapi-frontend' );

		if ( $atts['birth_date'] === '' ) {
			return Templates::error( __( 'The birth_date attribute is required. Example: [roxy_biorhythm birth_date="1990-05-15"]', 'roxyapi' ) );
		}

		$body = array( 'birthDate' => $atts['birth_date'] );
		if ( $atts['date'] !== '' ) {
			$body['date'] = $atts['date'];
		}

		$data = \RoxyAPI\Generated\Client::getReading( $body );

		if ( is_wp_error( $data ) ) {
			return Templates::error( $data->get_error_message() );
		}

		return Renderer::render_generic( 'getReading', is_array( $data ) ? $data : array() );
	}
}
