<?php
/**
 * Server-side render for the Numerology block. Delegates to the hero shortcode.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo wp_kses_post( \RoxyAPI\Generated\Heroes\Numerology::render( isset( $attributes ) ? $attributes : array(), '', 'roxyapi/numerology' ) );
