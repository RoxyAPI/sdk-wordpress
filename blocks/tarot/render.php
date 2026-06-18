<?php
/**
 * Server-side render for the Tarot block. Delegates to the hero shortcode.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

\RoxyAPI\Support\BlockOutput::render( \RoxyAPI\Generated\Heroes\TarotCard::render( isset( $attributes ) ? $attributes : array(), '', 'roxyapi/tarot' ) );
