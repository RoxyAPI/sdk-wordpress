<?php
/**
 * Hero shortcode: [roxy_dream]
 *
 * Usage: [roxy_dream q="water"] or [roxy_dream id="water"]
 *
 * Searches dream symbols by query, or looks up a specific symbol by ID.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Shortcodes;

use RoxyAPI\Blocks\Renderer;
use RoxyAPI\Support\Templates;

class Dream {

	/**
	 * Render the dream shortcode.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @param string                       $content Inner content.
	 * @param string                       $tag Shortcode tag.
	 * @return string
	 */
	public static function render( $atts, $content = '', $tag = '' ): string {
		$atts = shortcode_atts(
			array(
				'q'  => '',
				'id' => '',
			),
			is_array( $atts ) ? $atts : array(),
			(string) $tag
		);

		wp_enqueue_style( 'roxyapi-frontend' );

		if ( $atts['q'] === '' && $atts['id'] === '' ) {
			return Templates::error( __( 'Either the q or id attribute is required. Example: [roxy_dream q="water"] or [roxy_dream id="water"]', 'roxyapi' ) );
		}

		// Look up a single symbol by ID when provided.
		if ( $atts['id'] !== '' ) {
			$data = \RoxyAPI\Generated\Client::getDreamSymbol( $atts['id'] );

			if ( is_wp_error( $data ) ) {
				return Templates::error( $data->get_error_message() );
			}

			return Renderer::render_generic( 'getDreamSymbol', is_array( $data ) ? $data : array() );
		}

		// Otherwise search by query string.
		$data = \RoxyAPI\Generated\Client::searchDreamSymbols( $atts['q'] );

		if ( is_wp_error( $data ) ) {
			return Templates::error( $data->get_error_message() );
		}

		return Renderer::render_generic( 'searchDreamSymbols', is_array( $data ) ? $data : array() );
	}
}
