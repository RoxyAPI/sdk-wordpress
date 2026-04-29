<?php
/**
 * Server-side render for the Biorhythm block. Delegates to the hero shortcode.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo wp_kses_post( \RoxyAPI\Generated\Heroes\Biorhythm::render( isset( $attributes ) ? $attributes : array(), '', 'roxyapi/biorhythm' ) );
