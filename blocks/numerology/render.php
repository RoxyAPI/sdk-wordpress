<?php
/**
 * Server-side render stub for the Numerology block.
 *
 * Delegates to the matching hero shortcode. Full implementation in v1.1.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo wp_kses_post( \RoxyAPI\Shortcodes\Numerology::render( isset( $attributes ) ? $attributes : array(), '', 'roxyapi/numerology' ) );
