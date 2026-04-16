<?php
/**
 * Main plugin bootstrap.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI;

use RoxyAPI\Admin\ActionLinks;
use RoxyAPI\Admin\Notice;
use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Api\PreviewRoute;
use RoxyAPI\Blocks\Bindings;
use RoxyAPI\Blocks\Category;
use RoxyAPI\Blocks\Registrar as BlocksRegistrar;
use RoxyAPI\Shortcodes\Registrar as ShortcodesRegistrar;

class Plugin {

	private static string $plugin_file = '';

	public static function load( string $file ): void {
		self::$plugin_file = $file;

		register_activation_hook( $file, array( Activation::class, 'activate' ) );
		register_deactivation_hook( $file, array( Activation::class, 'deactivate' ) );

		add_action( 'plugins_loaded', array( self::class, 'boot' ) );
	}

	public static function boot(): void {
		SettingsPage::register();
		Notice::register();
		ActionLinks::register();
		PreviewRoute::register();
		Category::register();
		BlocksRegistrar::register();
		ShortcodesRegistrar::register();
		Bindings::register();
	}

	public static function file(): string {
		return self::$plugin_file;
	}
}
