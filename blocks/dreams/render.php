<?php
/**
 * Server-side render stub for the Dream Symbol block.
 *
 * Delegates to the matching hero shortcode. Full implementation in v1.1.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo wp_kses_post( \RoxyAPI\Shortcodes\Dream::render( isset( $attributes ) ? $attributes : array(), '', 'roxyapi/dreams' ) );
