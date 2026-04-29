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

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
// Variables in this file are local-scope, not globals: templates receive
// them via Templates::render's `extract( $vars, EXTR_SKIP )` and block
// render.php files receive them by exact unprefixed name from the WP
// block API (core passes $attributes, $content, $block, $sign, $date,
// $period, $wrapper_attributes by contract). PHPCS's static analyzer
// cannot see the extract or the block-API contract; suppress here to
// keep Plugin Check's plugin_repo report clean without per-line ignores.

$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'roxyapi-wrapper' ) );
printf(
	'<div %s>%s</div>',
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built by core get_block_wrapper_attributes(), already escaped.
	wp_kses_post( $content )
);
