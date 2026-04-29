<?php
/**
 * Block variations for the Horoscope block.
 *
 * One variation per real horoscope endpoint exposed by the API:
 * `getDailyHoroscope`, `getWeeklyHoroscope`, `getMonthlyHoroscope`.
 * Variations only differ by the `period` attribute, which the shortcode
 * dispatches on at render time.
 *
 * Each variation is a preset that changes attributes. Users pick one
 * from the block inserter or transform menu. PHP file variations
 * supported since WP 6.7.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	array(
		'name'        => 'daily',
		'title'       => __( 'Daily Horoscope', 'roxyapi' ),
		'description' => __( "Today's horoscope for a zodiac sign.", 'roxyapi' ),
		'icon'        => 'star-filled',
		'attributes'  => array(
			'period' => 'daily',
		),
		'isDefault'   => true,
		'scope'       => array( 'inserter', 'transform' ),
	),
	array(
		'name'        => 'weekly',
		'title'       => __( 'Weekly Horoscope', 'roxyapi' ),
		'description' => __( "This week's horoscope for a zodiac sign.", 'roxyapi' ),
		'attributes'  => array(
			'period' => 'weekly',
		),
		'scope'       => array( 'inserter', 'transform' ),
	),
	array(
		'name'        => 'monthly',
		'title'       => __( 'Monthly Horoscope', 'roxyapi' ),
		'description' => __( "This month's horoscope for a zodiac sign.", 'roxyapi' ),
		'attributes'  => array(
			'period' => 'monthly',
		),
		'scope'       => array( 'inserter', 'transform' ),
	),
);
