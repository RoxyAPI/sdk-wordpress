<?php
/**
 * Activation, deactivation, and uninstall lifecycle hooks.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI;

use RoxyAPI\Api\Cache;

class Activation {

	public static function activate(): void {
		// Nothing to do on activation. The admin notice in Notice.php handles
		// onboarding by checking if the API key is configured.
	}

	public static function deactivate(): void {
		Cache::flush_all();
	}
}
