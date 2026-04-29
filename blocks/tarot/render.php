<?php
/**
 * Server-side render for the Tarot block. Delegates to the hero shortcode.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo wp_kses_post( \RoxyAPI\Generated\Heroes\TarotCard::render( isset( $attributes ) ? $attributes : array(), '', 'roxyapi/tarot' ) );
