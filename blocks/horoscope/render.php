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

$sign         = $attributes['sign'] ?? $block->context['roxyapi/sign'] ?? 'aries';
$date         = $attributes['date'] ?? 'today';
$reading_type = $attributes['type'] ?? 'general';

echo wp_kses_post(
	\RoxyAPI\Shortcodes\Horoscope::render(
		array(
			'sign' => $sign,
			'date' => $date,
			'type' => $reading_type,
		),
		'',
		'roxyapi/horoscope'
	)
);
