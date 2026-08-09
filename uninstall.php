<?php
/**
 * Uninstall handler for RoxyAPI.
 *
 * Runs when the user deletes the plugin from the WordPress admin Plugins screen.
 * Removes plugin options and cached transients. Leaves user data alone.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'roxyapi_settings' );
// The cache epoch is the token folded into every response-cache key. Dropping
// it with the rest means a reinstall starts on a fresh namespace instead of
// adopting entries the sweep below cannot reach, such as those held by a
// persistent object cache.
delete_option( 'roxyapi_cache_epoch' );

global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- uninstall cleanup, no persistent caching needed.
$wpdb->query(
	"DELETE FROM {$wpdb->options}
	 WHERE option_name LIKE '_transient_roxyapi_%'
	    OR option_name LIKE '_transient_timeout_roxyapi_%'"
);

// Bulk delete the dismiss-notice user meta in one query without enumerating
// users first. The earlier get_users + foreach approach OOMs on sites with
// many users; delete_metadata( ..., true ) issues a single DELETE.
delete_metadata( 'user', 0, 'roxyapi_dismissed_setup', '', true );
