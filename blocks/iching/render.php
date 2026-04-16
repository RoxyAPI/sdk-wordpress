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

echo \RoxyAPI\Shortcodes\IChing::render( isset( $attributes ) ? $attributes : array(), '', 'roxyapi/iching' );
