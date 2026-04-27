<?php
/**
 * Server-side render stub for the I Ching block.
 *
 * Delegates to the matching hero shortcode. Full implementation in v1.1.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo wp_kses_post( \RoxyAPI\Shortcodes\IChing::render( isset( $attributes ) ? $attributes : array(), '', 'roxyapi/iching' ) );
