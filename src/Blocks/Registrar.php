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

	/**
	 * Hook block registration.
	 *
	 * @remarks No editor globals are published. Block editors used to read a `RoxyAPIEditor.hasKey` flag and refuse to preview without a key, which hid blocks that render perfectly well without one. Dropping the gate also stops broadcasting whether a key is configured to every editor session.
	 */
	public static function register(): void {
		add_action( 'init', array( self::class, 'register_blocks' ) );
	}

	public static function register_blocks(): void {
		$blocks_dir = plugin_dir_path( ROXYAPI_PLUGIN_FILE ) . 'build/blocks';
		if ( ! is_dir( $blocks_dir ) ) {
			return;
		}
		$block_files = glob( $blocks_dir . '/*/block.json' );
		if ( $block_files ) {
			foreach ( $block_files as $block_json ) {
				$block_type = register_block_type( dirname( $block_json ) );
				/**
				 * Editor-side strings are translated by `@wordpress/i18n` in JavaScript, and those
				 * only resolve once the script HANDLE is bound to the text domain. `wp-i18n` being
				 * a declared dependency is not enough on its own: without this call the editor
				 * panel stays English even when a language pack is installed and every PHP string
				 * on the same screen is translated.
				 *
				 * The path argument is what reaches the catalogues this plugin ships. Core looks in
				 * `WP_LANG_DIR/plugins` for a JSON named after the md5 of the script path, and that
				 * directory only ever holds what wordpress.org built; a bundled catalogue lives
				 * beside the MO files and is never found without being pointed at. Core does fall
				 * back to the text-domain registry when no path is given, but not on every version
				 * this plugin supports, and the failure is silent. Passing it costs nothing: the
				 * wordpress.org copy is still checked afterwards, so a translated site keeps
				 * whichever catalogue is present.
				 */
				if ( $block_type instanceof \WP_Block_Type ) {
					foreach ( $block_type->editor_script_handles as $handle ) {
						wp_set_script_translations( $handle, 'roxyapi', self::languages_dir() );
					}
				}
			}
		}
	}

	/**
	 * Absolute path to the bundled translation catalogues.
	 *
	 * @return string
	 */
	private static function languages_dir(): string {
		return plugin_dir_path( ROXYAPI_PLUGIN_FILE ) . 'languages';
	}
}
