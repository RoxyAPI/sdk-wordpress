<?php
/**
 * Server-side render stub for the Angel Number block.
 *
 * Delegates to the matching hero shortcode. Full implementation in v1.1.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo \RoxyAPI\Shortcodes\AngelNumber::render( isset( $attributes ) ? $attributes : array(), '', 'roxyapi/angel-number' );
