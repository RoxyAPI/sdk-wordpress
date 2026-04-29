<?php
/**
 * Server-side render for the Horoscope block.
 *
 * Delegates to the Horoscope hero shortcode so logic is single sourced.
 * Block attribute wins over inherited context, context wins over default.
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

$sign   = $attributes['sign'] ?? $block->context['roxyapi/sign'] ?? 'aries';
$date   = $attributes['date'] ?? 'today';
$period = $attributes['period'] ?? 'daily';

echo wp_kses_post(
	\RoxyAPI\Shortcodes\Horoscope::render(
		array(
			'sign'   => $sign,
			'date'   => $date,
			'period' => $period,
		),
		'',
		'roxyapi/horoscope'
	)
);
