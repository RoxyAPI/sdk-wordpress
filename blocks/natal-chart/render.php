<?php
/**
 * Server-side render stub for the Natal Chart block.
 *
 * Delegates to the matching hero shortcode. Full implementation in v1.1.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo wp_kses_post( \RoxyAPI\Shortcodes\NatalChart::render( isset( $attributes ) ? $attributes : array(), '', 'roxyapi/natal-chart' ) );
