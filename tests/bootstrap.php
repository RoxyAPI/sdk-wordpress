<?php
/**
 * PHPUnit bootstrap for the RoxyAPI plugin.
 *
 * Loads the WordPress test suite and activates the plugin.
 *
 * @package RoxyAPI
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// install-wp-tests.sh ships a wp-tests-config.php with placeholder LOGGED_IN_KEY
// / LOGGED_IN_SALT, which Encryption::key()/salt() reject by design. Define test
// constants so the encryption layer has real values during phpunit. Not shipped:
// /tests is excluded from the published zip via .distignore.
if ( ! defined( 'ROXYAPI_ENCRYPTION_KEY' ) ) {
	define( 'ROXYAPI_ENCRYPTION_KEY', 'phpunit-test-encryption-key-not-for-production-use' );
}
if ( ! defined( 'ROXYAPI_ENCRYPTION_SALT' ) ) {
	define( 'ROXYAPI_ENCRYPTION_SALT', 'phpunit-test-encryption-salt-not-for-production-use' );
}

// Unit tests must never reach the network. Core evaluates this AFTER the
// pre_http_request filter, so a mocked request still short-circuits normally
// while an unmocked one fails with the offending URL named in the WP_Error.
if ( ! defined( 'WP_HTTP_BLOCK_EXTERNAL' ) ) {
	define( 'WP_HTTP_BLOCK_EXTERNAL', true );
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
	exit( 1 );
}

require_once "{$_tests_dir}/includes/functions.php";

function _manually_load_plugin() {
	require dirname( __DIR__ ) . '/roxyapi.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require "{$_tests_dir}/includes/bootstrap.php";

// WP_Ajax_UnitTestCase fires admin_init, where core pings api.wordpress.org for
// core, plugin and theme updates. Those pings are refused by the block above and
// raise a warning that phpunit converts into an exception, so the ajax tests error
// on a fresh database. Nothing here covers core update behaviour, so drop the
// checks rather than let the suite depend on wordpress.org being reachable.
remove_action( 'admin_init', '_maybe_update_core' );
remove_action( 'admin_init', '_maybe_update_plugins' );
remove_action( 'admin_init', '_maybe_update_themes' );
