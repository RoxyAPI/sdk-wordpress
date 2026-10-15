<?php
/**
 * Guided shortcode creation using the bundled reading manifest.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Generated\Builder\Manifest;

class BuilderPage {

	public const PAGE_SLUG = 'roxyapi-builder';

	public static function register(): void {
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap roxyapi-builder"><h1 data-roxyapi-builder-title></h1><div id="roxyapi-builder"></div></div>';
	}

	/**
	 * Load only bundled assets and metadata on the builder screen.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue( string $hook ): void {
		if ( $hook !== get_plugin_page_hookname( self::PAGE_SLUG, SettingsPage::PAGE_SLUG ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		Assets::enqueue_admin_css();
		wp_enqueue_style( 'roxyapi-builder', plugins_url( 'assets/css/builder.css', ROXYAPI_PLUGIN_FILE ), array( 'roxyapi-admin' ), ROXYAPI_VERSION );
		// Build the persistent city inputs before the shared combobox scans the DOM.
		\RoxyAPI\Plugin::register_geocode_script();
		wp_enqueue_script( 'roxyapi-builder', plugins_url( 'assets/js/builder.js', ROXYAPI_PLUGIN_FILE ), array( 'roxyapi-geocode', 'wp-i18n' ), ROXYAPI_VERSION, true );
		wp_set_script_translations( 'roxyapi-builder', 'roxyapi', plugin_dir_path( ROXYAPI_PLUGIN_FILE ) . 'languages' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only selection on a capability-checked page.
		$reading  = isset( $_GET['reading'] ) && is_string( $_GET['reading'] ) ? sanitize_key( wp_unslash( $_GET['reading'] ) ) : '';
		$manifest = Manifest::all();
		wp_localize_script(
			'roxyapi-builder',
			'RoxyAPIBuilder',
			array(
				'manifest'  => $manifest,
				'restUrl'   => esc_url_raw( rest_url( 'roxyapi/v1/geocode' ) ),
				'newPage'   => admin_url( 'post-new.php?post_type=page' ),
				'reading'   => $reading,
				'timezones' => timezone_identifiers_list( \DateTimeZone::ALL_WITH_BC ),
				'today'     => current_time( 'Y-m-d' ),
			)
		);
	}
}
