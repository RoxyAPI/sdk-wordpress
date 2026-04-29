<?php
/**
 * Plugin Name:       Astrology, Horoscope, Tarot, Numerology by Roxy
 * Plugin URI:        https://roxyapi.com
 * Description:       Add astrology, daily horoscopes, tarot card pulls, numerology readings, and Vedic and Western birth charts to any WordPress page. Blocks and shortcodes. Calculations cross-checked against the NASA JPL Horizons ephemeris.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Tested up to:      6.9
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

// Composer's autoloader is preferred when the dev environment installed it
// (composer install). The released zip ships without `vendor/` so we register
// a minimal PSR-4 autoloader covering the plugin's own namespace.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
} else {
	spl_autoload_register(
		static function ( $fqcn ) {
			if ( strpos( $fqcn, 'RoxyAPI\\' ) !== 0 ) {
				return;
			}
			$relative = substr( $fqcn, strlen( 'RoxyAPI\\' ) );
			$path     = __DIR__ . '/src/' . str_replace( '\\', '/', $relative ) . '.php';
			if ( is_readable( $path ) ) {
				require_once $path;
			}
		}
	);
}

RoxyAPI\Plugin::load( __FILE__ );
