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

global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- uninstall cleanup, no persistent caching needed.
$wpdb->query(
	"DELETE FROM {$wpdb->options}
	 WHERE option_name LIKE '_transient_roxyapi_%'
	    OR option_name LIKE '_transient_timeout_roxyapi_%'"
);

$users = get_users(
	array(
		'meta_key' => 'roxyapi_dismissed_setup', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one-time uninstall cleanup.
		'fields'   => 'ID',
	)
);
foreach ( $users as $user_id ) {
	delete_user_meta( $user_id, 'roxyapi_dismissed_setup' );
}
