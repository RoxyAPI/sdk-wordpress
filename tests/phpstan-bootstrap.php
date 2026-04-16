<?php
/**
 * PHPStan bootstrap file.
 *
 * Defines plugin constants so PHPStan can resolve them in type analysis.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ROXYAPI_VERSION' ) ) {
	define( 'ROXYAPI_VERSION', '1.0.0' );
}
if ( ! defined( 'ROXYAPI_PHP_MIN' ) ) {
	define( 'ROXYAPI_PHP_MIN', '7.4.0' );
}
if ( ! defined( 'ROXYAPI_PLUGIN_FILE' ) ) {
	define( 'ROXYAPI_PLUGIN_FILE', __DIR__ . '/../roxyapi.php' );
}
