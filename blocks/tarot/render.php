<?php
/**
 * Server-side render stub for the Tarot block.
 *
 * Delegates to the matching hero shortcode. Full implementation in v1.1.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo wp_kses_post( \RoxyAPI\Shortcodes\TarotCard::render( isset( $attributes ) ? $attributes : array(), '', 'roxyapi/tarot' ) );
