<?php
/**
 * Tests for the plugin row meta and action links.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\ActionLinks;

class Test_Action_Links extends \WP_UnitTestCase {

	public function test_row_meta_adds_three_links_for_plugin_row(): void {
		$plugin_basename = plugin_basename( ROXYAPI_PLUGIN_FILE );
		$initial         = array( 'View details' );

		$result = ActionLinks::add_row_meta( $initial, $plugin_basename );

		$this->assertCount( 4, $result, 'Expected three new links plus the original "View details".' );

		$joined = implode( ' ', $result );
		$this->assertStringContainsString( 'Sign up', $joined );
		$this->assertStringContainsString( 'Documentation', $joined );
		$this->assertStringContainsString( 'Support', $joined );

		// UTM params on the sign-up link.
		$this->assertStringContainsString( 'utm_source=wp-plugin', $joined );
		$this->assertStringContainsString( 'utm_medium=plugins-list', $joined );
	}

	public function test_row_meta_unchanged_for_other_plugin_rows(): void {
		$initial = array( 'View details' );
		$result  = ActionLinks::add_row_meta( $initial, 'some-other-plugin/some-other-plugin.php' );
		$this->assertSame( $initial, $result );
	}

	public function test_action_links_includes_settings_link(): void {
		$result = ActionLinks::add_settings_link( array() );
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( 'page=' . \RoxyAPI\Admin\SettingsPage::PAGE_SLUG, (string) $result[0] );
		$this->assertStringContainsString( 'Settings', (string) $result[0] );
	}
}
