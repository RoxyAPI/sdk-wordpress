<?php
/**
 * Block registration.
 *
 * @remarks
 * The build ships every block flat at build/blocks/<name>/block.json: the hero blocks plus every spec-generated long-tail block, moved up from the nested generated/ output by bin/flatten-generated-blocks.mjs. A single one-level glob registers the whole catalog on every supported WordPress version (6.5 and up) using only register_block_type, which keeps the plugin compatible with the declared minimum with no version-gated Core functions. Before 1.6.0 the long-tail blocks sat nested at build/blocks/generated/<name>/ and the one-level scan missed them, so only the hero blocks registered. Keep the layout flat (bin/check-block-layout.mjs enforces it) or the glob misses the long-tail again.
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
		$block_files = glob( $blocks_dir . '/*/block.json' );
		if ( $block_files ) {
			foreach ( $block_files as $block_json ) {
				register_block_type( dirname( $block_json ) );
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
