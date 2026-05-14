<?php
/**
 * Block registration. Loops over build/blocks/ and calls register_block_type
 * for each directory holding a block.json.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Registrar {

	public static function register(): void {
		add_action( 'init', array( self::class, 'register_blocks' ) );
		add_action( 'enqueue_block_editor_assets', array( self::class, 'enqueue_editor_globals' ) );
	}

	public static function register_blocks(): void {
		$blocks_dir = plugin_dir_path( ROXYAPI_PLUGIN_FILE ) . 'build/blocks';
		if ( ! is_dir( $blocks_dir ) ) {
			return;
		}
		$entries = glob( $blocks_dir . '/*', GLOB_ONLYDIR );
		if ( ! $entries ) {
			return;
		}
		foreach ( $entries as $block_dir ) {
			if ( file_exists( $block_dir . '/block.json' ) ) {
				register_block_type( $block_dir );
			}
		}
	}

	public static function enqueue_editor_globals(): void {
		wp_enqueue_script(
			'roxyapi-editor-globals',
			plugins_url( 'assets/js/admin.js', ROXYAPI_PLUGIN_FILE ),
			array( 'wp-blocks' ),
			ROXYAPI_VERSION,
			true
		);
		wp_localize_script(
			'roxyapi-editor-globals',
			'RoxyAPIEditor',
			array(
				'hasKey'      => \RoxyAPI\Support\ApiKey::is_configured(),
				'settingsUrl' => admin_url( 'admin.php?page=roxyapi' ),
			)
		);
	}
}
