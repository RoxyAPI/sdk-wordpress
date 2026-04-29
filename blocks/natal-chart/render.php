<?php
/**
 * Server-side render for the Natal Chart block. Delegates to the hero shortcode.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo wp_kses_post( \RoxyAPI\Generated\Heroes\NatalChart::render( isset( $attributes ) ? $attributes : array(), '', 'roxyapi/natal-chart' ) );
