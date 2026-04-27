<?php
/**
 * Adds a "Settings" link to the plugin row on the Plugins screen.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ActionLinks {

	public static function register(): void {
		add_filter(
			'plugin_action_links_' . plugin_basename( ROXYAPI_PLUGIN_FILE ),
			array( self::class, 'add_settings_link' )
		);
		add_filter( 'plugin_row_meta', array( self::class, 'add_row_meta' ), 10, 2 );
	}

	/**
	 * Add settings link to the plugins list.
	 *
	 * @param array<int|string, string> $links Existing action links.
	 * @return array<int|string, string>
	 */
	public static function add_settings_link( array $links ): array {
		$settings = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=' . SettingsPage::PAGE_SLUG ) ),
			esc_html__( 'Settings', 'roxyapi' )
		);
		array_unshift( $links, $settings );
		return $links;
	}

	/**
	 * Append Sign up, Documentation, and Support links to the plugin row meta.
	 *
	 * Three links surface beneath the plugin description on the Plugins screen
	 * for the RoxyAPI plugin row only. The `Sign up` link carries onboarding
	 * UTM params so attribution flows back from the plugins list.
	 *
	 * @param array<int, string> $meta        Existing plugin row meta links.
	 * @param string             $plugin_file Plugin file path being filtered.
	 * @return array<int, string>
	 */
	public static function add_row_meta( array $meta, string $plugin_file ): array {
		if ( $plugin_file !== plugin_basename( ROXYAPI_PLUGIN_FILE ) ) {
			return $meta;
		}

		$meta[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( 'https://roxyapi.com/pricing?utm_source=wp-plugin&utm_medium=plugins-list' ),
			esc_html__( 'Sign up', 'roxyapi' )
		);
		$meta[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( 'https://roxyapi.com/docs/integrations/wordpress' ),
			esc_html__( 'Documentation', 'roxyapi' )
		);
		$meta[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( 'https://roxyapi.com/contact' ),
			esc_html__( 'Support', 'roxyapi' )
		);

		return $meta;
	}
}
