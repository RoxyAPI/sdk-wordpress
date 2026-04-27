<?php
/**
 * Registers the "Roxy" block category in the inserter.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Category {

	public static function register(): void {
		add_filter( 'block_categories_all', array( self::class, 'add_category' ) );
	}

	/**
	 * Prepend the Roxy category to the block inserter list.
	 *
	 * @param array<int, array<string, string>> $categories Existing block categories.
	 * @return array<int, array<string, string>>
	 */
	public static function add_category( array $categories ): array {
		return array_merge(
			array(
				array(
					'slug'  => 'roxyapi',
					'title' => __( 'Roxy', 'roxyapi' ),
					'icon'  => 'star-filled',
				),
			),
			$categories
		);
	}
}
