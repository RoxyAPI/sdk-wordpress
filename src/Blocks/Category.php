<?php
/**
 * Registers the two RoxyAPI block categories in the inserter: the hero blocks
 * under "RoxyAPI", every other reading under "RoxyAPI: every reading", so the
 * curated list is what a browse shows first.
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
	 * Prepend the hero category and append the catalogue category.
	 *
	 * @param array<int, array<string, string>> $categories Existing block categories.
	 * @return array<int, array<string, string>>
	 */
	public static function add_category( array $categories ): array {
		return array_merge(
			array(
				array(
					'slug'  => 'roxyapi',
					'title' => __( 'RoxyAPI', 'roxyapi' ),
					'icon'  => 'star-filled',
				),
			),
			$categories,
			array(
				array(
					'slug'  => 'roxyapi-catalog',
					'title' => __( 'RoxyAPI: every reading', 'roxyapi' ),
					'icon'  => 'star-empty',
				),
			)
		);
	}
}
