<?php
/**
 * Activation, deactivation, and upgrade lifecycle hooks.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Api\Cache;
use RoxyAPI\Support\Theming;

class Activation {

	public static function activate(): void {
		// Set a short-lived transient. Plugin::maybe_redirect_after_activation
		// reads it on the next admin_init and one-shot redirects to the
		// builder so site owners can create their first reading.
		set_transient( 'roxyapi_activated', '1', 30 );
	}

	public static function deactivate(): void {
		Cache::flush_all();
	}

	/**
	 * Bring settings saved by an earlier version up to this one, once.
	 *
	 * A wp.org update replaces the files without running the activation hook,
	 * so this runs on every load and returns at once when there is nothing to
	 * do. The schema itself is the version gate: every save since the font
	 * setting arrived writes a `font` key, so a saved row without one was
	 * written by an earlier version. Such a site read its palette in its own
	 * font, and keeps it; a fresh install has no row and takes the palette
	 * font. Writing the key is what makes the step run exactly once.
	 *
	 * @return void
	 */
	public static function upgrade(): void {
		$saved = get_option( SettingsPage::OPTION_NAME );
		if ( ! is_array( $saved ) || array_key_exists( 'font', $saved ) ) {
			return;
		}
		$saved['font'] = (string) ( $saved['palette_preset'] ?? '' ) !== '' ? Theming::SITE_FONT : '';
		update_option( SettingsPage::OPTION_NAME, $saved );
	}
}
