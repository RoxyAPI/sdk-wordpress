<?php
/**
 * Activation, deactivation, and uninstall lifecycle hooks.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Api\Cache;

class Activation {

	public static function activate(): void {
		// Set a short-lived transient. Plugin::maybe_redirect_after_activation
		// reads it on the next admin_init and one-shot redirects to the
		// settings page so site owners land in the onboarding flow.
		set_transient( 'roxyapi_activated', '1', 30 );
	}

	public static function deactivate(): void {
		Cache::flush_all();
	}
}
