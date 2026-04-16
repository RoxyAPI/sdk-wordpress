<?php
/**
 * Adds a "Settings" link to the plugin row on the Plugins screen.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

class ActionLinks {

	public static function register(): void {
		add_filter(
			'plugin_action_links_' . plugin_basename( ROXYAPI_PLUGIN_FILE ),
			array( self::class, 'add_settings_link' )
		);
	}

	/**
	 * Add settings link to the plugins list.
	 *
	 * @param array<string, string> $links Existing action links.
	 * @return array<string, string>
	 */
	public static function add_settings_link( array $links ): array {
		$settings = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=' . SettingsPage::PAGE_SLUG ) ),
			esc_html__( 'Settings', 'roxyapi' )
		);
		array_unshift( $links, $settings );
		return $links;
	}
}
