<?php
/**
 * Server-side render for the Astrology Section wrapper block.
 *
 * Emits a simple wrapper div and the inner content. Block context is passed
 * to descendants automatically based on providesContext in block.json.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'roxyapi-wrapper' ) );
printf(
	'<div %s>%s</div>',
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built by core get_block_wrapper_attributes(), already escaped.
	wp_kses_post( $content )
);
