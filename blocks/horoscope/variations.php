<?php
/**
 * Block variations for the Horoscope block.
 *
 * Each variation is a preset that changes attributes. Users pick a variation
 * from the block inserter or transform menu. Native picker UI since WP 6.2.
 * PHP file variations supported since WP 6.7.
 *
 * @package RoxyAPI
 */

return array(
	array(
		'name'        => 'daily',
		'title'       => __( 'Daily Horoscope', 'roxyapi' ),
		'description' => __( "Today's horoscope for a zodiac sign.", 'roxyapi' ),
		'icon'        => 'star-filled',
		'attributes'  => array(
			'period' => 'daily',
			'type'   => 'general',
		),
		'isDefault'   => true,
		'scope'       => array( 'inserter', 'transform' ),
	),
	array(
		'name'       => 'weekly',
		'title'      => __( 'Weekly Horoscope', 'roxyapi' ),
		'attributes' => array(
			'period' => 'weekly',
			'type'   => 'general',
		),
		'scope'      => array( 'inserter', 'transform' ),
	),
	array(
		'name'       => 'monthly',
		'title'      => __( 'Monthly Horoscope', 'roxyapi' ),
		'attributes' => array(
			'period' => 'monthly',
			'type'   => 'general',
		),
		'scope'      => array( 'inserter', 'transform' ),
	),
	array(
		'name'       => 'love',
		'title'      => __( 'Love Horoscope', 'roxyapi' ),
		'attributes' => array(
			'period' => 'daily',
			'type'   => 'love',
		),
		'scope'      => array( 'inserter', 'transform' ),
	),
	array(
		'name'       => 'career',
		'title'      => __( 'Career Horoscope', 'roxyapi' ),
		'attributes' => array(
			'period' => 'daily',
			'type'   => 'career',
		),
		'scope'      => array( 'inserter', 'transform' ),
	),
	array(
		'name'       => 'chinese',
		'title'      => __( 'Chinese Horoscope', 'roxyapi' ),
		'attributes' => array(
			'period' => 'chinese',
			'type'   => 'general',
		),
		'scope'      => array( 'inserter', 'transform' ),
	),
);
