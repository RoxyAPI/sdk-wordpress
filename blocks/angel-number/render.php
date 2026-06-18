<?php
/**
 * Server-side render for the Angel Number block. Delegates to the hero shortcode.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

\RoxyAPI\Support\BlockOutput::render( \RoxyAPI\Generated\Heroes\AngelNumber::render( isset( $attributes ) ? $attributes : array(), '', 'roxyapi/angel-number' ) );
