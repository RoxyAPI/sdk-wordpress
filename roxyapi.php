<?php
/**
 * Plugin Name:       RoxyAPI: Astrology, Tarot, Numerology, Horoscope and More
 * Plugin URI:        https://roxyapi.com
 * Description:       Drop daily horoscopes, tarot pulls, numerology readings, I Ching casts, and natal charts onto any WordPress page. Shortcodes and blocks. Verified against NASA JPL Horizons.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Tested up to:      6.8
 * Requires PHP:      7.4
 * Author:            RoxyAPI
 * Author URI:        https://roxyapi.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       roxyapi
 * Domain Path:       /languages
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const ROXYAPI_VERSION     = '1.0.0';
const ROXYAPI_PHP_MIN     = '7.4.0';
const ROXYAPI_PLUGIN_FILE = __FILE__;

if ( version_compare( PHP_VERSION, ROXYAPI_PHP_MIN, '<' ) ) {
	add_action(
		'admin_notices',
		static function () {
			echo '<div class="notice notice-error"><p>RoxyAPI requires PHP 7.4 or higher.</p></div>';
		}
	);
	return;
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

RoxyAPI\Plugin::load( __FILE__ );
