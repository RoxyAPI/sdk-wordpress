<?php
/**
 * Shortcodes Library submenu page.
 *
 * Browse, search, copy. The full library of every hero plus generated
 * shortcode shipped with the plugin, grouped by domain in brand order.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Support\Templates;

class ShortcodesPage {

	public const PAGE_SLUG = 'roxyapi-shortcodes';

	/**
	 * Hook the page-specific asset enqueue. The menu entry itself is
	 * registered by SettingsPage::add_menu so the parent slug stays under
	 * the same top-level menu.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	/**
	 * Render the library. All output goes through the shared Templates
	 * helper which already handles escaping per-row.
	 *
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$domains     = Catalog::domains();
		$groups      = Catalog::grouped();
		$total_count = 0;
		foreach ( $groups as $items ) {
			$total_count += count( $items );
		}

		echo '<div class="wrap roxyapi-shortcodes">';

		$html = Templates::render(
			'admin-shortcodes',
			array(
				'domains'     => $domains,
				'groups'      => $groups,
				'total_count' => $total_count,
			)
		);

		echo wp_kses_post( $html );

		echo '</div>';
	}

	/**
	 * Enqueue page-specific styles and scripts. Only fires on the library
	 * page hook so we never leak assets onto unrelated screens.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue( string $hook ): void {
		// The submenu hookname is `{sanitize_title(parent menu title)}_page_{slug}`.
		// Renaming the parent menu would silently break a hardcoded prefix, so
		// resolve it through WP's own helper instead.
		if ( $hook !== get_plugin_page_hookname( self::PAGE_SLUG, SettingsPage::PAGE_SLUG ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_enqueue_style(
			'roxyapi-admin',
			plugins_url( 'assets/css/admin.css', ROXYAPI_PLUGIN_FILE ),
			array(),
			ROXYAPI_VERSION
		);
		wp_enqueue_script(
			'roxyapi-admin-shortcodes',
			plugins_url( 'assets/js/admin-shortcodes.js', ROXYAPI_PLUGIN_FILE ),
			array(),
			ROXYAPI_VERSION,
			true
		);

		$domains = Catalog::domains();
		$total   = 0;
		foreach ( $domains as $domain ) {
			$total += $domain['count'];
		}

		wp_localize_script(
			'roxyapi-admin-shortcodes',
			'RoxyAPIShortcodes',
			array(
				'domains'    => $domains,
				'totalCount' => $total,
				'i18n'       => array(
					'copied'        => __( 'Copied', 'roxyapi' ),
					'copyFailed'    => __( 'Copy failed', 'roxyapi' ),
					'copy'          => __( 'Copy', 'roxyapi' ),
					'noResults'     => __( 'No shortcodes match. Try a different domain or clear the search.', 'roxyapi' ),
					'searchAriaHit' => __( 'Search shortcodes', 'roxyapi' ),
				),
			)
		);
	}
}
