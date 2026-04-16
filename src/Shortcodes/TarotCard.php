<?php
/**
 * Hero shortcode: [roxy_tarot_card]
 *
 * Usage: [roxy_tarot_card] or [roxy_tarot_card date="2026-04-16"]
 *
 * Draws a daily tarot card via the RoxyAPI client. The date attribute is
 * optional and defaults to today on the server side.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Shortcodes;

use RoxyAPI\Blocks\Renderer;
use RoxyAPI\Support\Templates;

class TarotCard {

	/**
	 * Render the tarot card shortcode.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @param string                       $content Inner content.
	 * @param string                       $tag Shortcode tag.
	 * @return string
	 */
	public static function render( $atts, $content = '', $tag = '' ): string {
		$atts = shortcode_atts(
			array( 'date' => '' ),
			is_array( $atts ) ? $atts : array(),
			(string) $tag
		);

		wp_enqueue_style( 'roxyapi-frontend' );

		$body = array();
		if ( $atts['date'] !== '' ) {
			$body['date'] = $atts['date'];
		}

		$data = \RoxyAPI\Generated\Client::getDailyCard( $body );

		if ( is_wp_error( $data ) ) {
			return Templates::error( $data->get_error_message() );
		}

		return Renderer::render_generic( 'getDailyCard', is_array( $data ) ? $data : array() );
	}
}
