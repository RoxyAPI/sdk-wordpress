<?php
/**
 * Server-side render stub for the Biorhythm block.
 *
 * Delegates to the matching hero shortcode. Full implementation in v1.1.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo \RoxyAPI\Shortcodes\Biorhythm::render( isset( $attributes ) ? $attributes : array(), '', 'roxyapi/biorhythm' );
