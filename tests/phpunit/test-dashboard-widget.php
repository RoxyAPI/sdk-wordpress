<?php
/**
 * Tests for the Roxy connection dashboard widget.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\DashboardWidget;
use RoxyAPI\Support\Encryption;

class Test_Dashboard_Widget extends \WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		delete_option( 'roxyapi_settings' );
		global $wp_meta_boxes;
		$wp_meta_boxes = array();

		// wp_add_dashboard_widget lives in wp-admin/includes/dashboard.php which
		// is not autoloaded outside admin requests. The dashboard widget hook
		// only fires when WordPress has already loaded it, so registering inside
		// a unit test means we have to require the file ourselves.
		if ( ! function_exists( 'wp_add_dashboard_widget' ) ) {
			require_once ABSPATH . 'wp-admin/includes/dashboard.php';
		}
	}

	public function tearDown(): void {
		wp_set_current_user( 0 );
		global $wp_meta_boxes;
		$wp_meta_boxes = array();
		parent::tearDown();
	}

	public function test_widget_registers_for_admins(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		set_current_screen( 'dashboard' );
		DashboardWidget::maybe_add_widget();

		global $wp_meta_boxes;
		$this->assertArrayHasKey( 'dashboard', (array) $wp_meta_boxes );
		$found = false;
		foreach ( (array) $wp_meta_boxes['dashboard'] as $context ) {
			foreach ( (array) $context as $priority ) {
				if ( isset( $priority['roxyapi_connection_widget'] ) ) {
					$found = true;
				}
			}
		}
		$this->assertTrue( $found, 'Expected the Roxy connection widget to be registered for admins.' );
	}

	public function test_widget_does_not_register_for_subscribers(): void {
		$subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber_id );

		set_current_screen( 'dashboard' );
		DashboardWidget::maybe_add_widget();

		global $wp_meta_boxes;
		$found = false;
		foreach ( (array) $wp_meta_boxes as $screen ) {
			foreach ( (array) $screen as $context ) {
				foreach ( (array) $context as $priority ) {
					if ( isset( $priority['roxyapi_connection_widget'] ) ) {
						$found = true;
					}
				}
			}
		}
		$this->assertFalse( $found, 'Subscribers should not see the Roxy widget.' );
	}

	public function test_render_empty_state_when_no_key(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		ob_start();
		DashboardWidget::render();
		$out = (string) ob_get_clean();

		$this->assertStringContainsString( 'roxyapi-widget-empty', $out );
		$this->assertStringContainsString( 'Connect Roxy', $out );
		$this->assertStringNotContainsString( 'roxyapi-widget-connected', $out );
	}

	public function test_render_connected_state_when_key_present(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$test_key = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.0123456789abcdef.test_key_value';
		update_option(
			'roxyapi_settings',
			array( 'api_key_encrypted' => Encryption::encrypt( $test_key ) )
		);

		ob_start();
		DashboardWidget::render();
		$out = (string) ob_get_clean();

		$this->assertStringContainsString( 'roxyapi-widget-connected', $out );
		$this->assertStringContainsString( 'Connected to Roxy', $out );
		$this->assertStringContainsString( '[roxy_horoscope', $out );
		$this->assertStringNotContainsString( 'roxyapi-widget-empty', $out );
	}

	public function test_render_returns_early_for_non_admin(): void {
		$subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber_id );

		ob_start();
		DashboardWidget::render();
		$out = (string) ob_get_clean();

		$this->assertSame( '', $out );
	}
}
