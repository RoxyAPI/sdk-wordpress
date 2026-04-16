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

echo \RoxyAPI\Shortcodes\NatalChart::render( isset( $attributes ) ? $attributes : array(), '', 'roxyapi/natal-chart' );
