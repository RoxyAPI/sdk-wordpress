<?php
/**
 * Tests for the RoxyAPI\Admin\Notice AJAX dismissal handler.
 *
 * Uses the WP_Ajax_UnitTestCase base class to override wp_die so the handler
 * can be invoked synchronously without exiting the PHPUnit process.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\Notice;
use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Support\Encryption;
use ReflectionMethod;

class Test_Notice_Dismiss extends \WP_Ajax_UnitTestCase {

	private const META_KEY = 'roxyapi_dismissed_setup';

	public function set_up(): void {
		parent::set_up();
		delete_option( 'roxyapi_settings' );
		// Ensure the AJAX handler is registered.
		Notice::register();
	}

	public function tear_down(): void {
		wp_set_current_user( 0 );
		parent::tear_down();
	}

	public function test_valid_nonce_admin_sets_dismissed_flag(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$_POST['_wpnonce'] = wp_create_nonce( 'roxyapi_dismiss_notice' );

		try {
			$this->_handleAjax( 'roxyapi_dismiss_notice' );
		} catch ( \WPAjaxDieStopException $e ) {
			// Expected: handler calls wp_die() with no body.
			$this->assertSame( '', $e->getMessage() );
		} catch ( \WPAjaxDieContinueException $e ) {
			// Some WP versions emit content first.
			$this->assertTrue( true );
		}

		$this->assertSame( '1', get_user_meta( $admin_id, self::META_KEY, true ) );
	}

	public function test_missing_nonce_does_not_set_flag(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		// Intentionally NOT setting $_POST['_wpnonce'].

		try {
			$this->_handleAjax( 'roxyapi_dismiss_notice' );
			$this->fail( 'Handler must die when nonce is missing.' );
		} catch ( \WPAjaxDieStopException $e ) {
			$this->assertTrue( true );
		} catch ( \WPAjaxDieContinueException $e ) {
			$this->assertTrue( true );
		}

		$this->assertEmpty( get_user_meta( $admin_id, self::META_KEY, true ) );
	}

	public function test_wrong_nonce_does_not_set_flag(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		$_POST['_wpnonce'] = wp_create_nonce( 'some_other_action' );

		try {
			$this->_handleAjax( 'roxyapi_dismiss_notice' );
			$this->fail( 'Handler must die when nonce action does not match.' );
		} catch ( \WPAjaxDieStopException $e ) {
			$this->assertTrue( true );
		} catch ( \WPAjaxDieContinueException $e ) {
			$this->assertTrue( true );
		}

		$this->assertEmpty( get_user_meta( $admin_id, self::META_KEY, true ) );
	}

	public function test_subscriber_with_valid_nonce_gets_403(): void {
		$sub_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $sub_id );
		$_POST['_wpnonce'] = wp_create_nonce( 'roxyapi_dismiss_notice' );

		try {
			$this->_handleAjax( 'roxyapi_dismiss_notice' );
			$this->fail( 'Handler must die for non admin users.' );
		} catch ( \WPAjaxDieStopException $e ) {
			$this->assertTrue( true );
		} catch ( \WPAjaxDieContinueException $e ) {
			$this->assertTrue( true );
		}

		$this->assertEmpty( get_user_meta( $sub_id, self::META_KEY, true ) );
	}

	public function test_should_show_returns_false_when_meta_set(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		update_user_meta( $admin_id, self::META_KEY, '1' );

		$result = $this->invoke_should_show();
		$this->assertFalse( $result );
	}

	public function test_should_show_returns_false_when_api_key_configured(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		// Detach the sanitize callback so update_option preserves the key.
		remove_filter(
			'sanitize_option_' . SettingsPage::OPTION_NAME,
			array( \RoxyAPI\Admin\SettingsFields::class, 'sanitize' )
		);
		update_option(
			'roxyapi_settings',
			array(
				'api_key_encrypted' => Encryption::encrypt(
					'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.0123456789abcdef.notice_test'
				),
			)
		);

		$result = $this->invoke_should_show();
		$this->assertFalse( $result );
	}

	public function test_should_show_returns_false_for_non_admin(): void {
		$sub_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $sub_id );

		$result = $this->invoke_should_show();
		$this->assertFalse( $result );
	}

	private function invoke_should_show(): bool {
		$ref = new ReflectionMethod( Notice::class, 'should_show' );
		$ref->setAccessible( true );
		return (bool) $ref->invoke( null );
	}

	private function invoke_should_show_exhausted(): bool {
		$ref = new ReflectionMethod( Notice::class, 'should_show_exhausted' );
		$ref->setAccessible( true );
		return (bool) $ref->invoke( null );
	}

	private function set_exhausted_transient(): void {
		set_transient( 'roxyapi_free_tier_exhausted_seen', current_time( 'mysql' ), DAY_IN_SECONDS );
	}

	private function clear_exhausted_state(): void {
		delete_transient( 'roxyapi_free_tier_exhausted_seen' );
	}

	public function test_exhausted_notice_renders_for_admin_with_no_key_when_transient_set(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		delete_option( 'roxyapi_settings' );
		$this->set_exhausted_transient();

		ob_start();
		Notice::maybe_show_exhausted();
		$out = (string) ob_get_clean();

		$this->assertNotEmpty( $out, 'Notice must render when all gates pass.' );
		$this->assertStringContainsString( 'roxyapi-free-tier-exhausted', $out );
		$this->assertStringContainsString( 'free daily allowance', $out );
		$this->assertStringContainsString( 'page=' . \RoxyAPI\Admin\SettingsPage::PAGE_SLUG, $out );
		$this->assertStringContainsString( 'Add key', $out );

		$this->clear_exhausted_state();
	}

	public function test_exhausted_notice_skipped_when_transient_missing(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		delete_option( 'roxyapi_settings' );
		$this->clear_exhausted_state();

		$this->assertFalse( $this->invoke_should_show_exhausted() );

		ob_start();
		Notice::maybe_show_exhausted();
		$this->assertSame( '', (string) ob_get_clean() );
	}

	public function test_exhausted_notice_skipped_when_api_key_configured(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		// Detach the sanitize callback so update_option preserves the key.
		remove_filter(
			'sanitize_option_' . SettingsPage::OPTION_NAME,
			array( \RoxyAPI\Admin\SettingsFields::class, 'sanitize' )
		);
		update_option(
			'roxyapi_settings',
			array(
				'api_key_encrypted' => Encryption::encrypt(
					'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.0123456789abcdef.exhausted_test'
				),
			)
		);
		$this->set_exhausted_transient();

		$this->assertFalse( $this->invoke_should_show_exhausted() );

		ob_start();
		Notice::maybe_show_exhausted();
		$this->assertSame( '', (string) ob_get_clean() );

		$this->clear_exhausted_state();
	}

	public function test_exhausted_notice_skipped_for_non_admin(): void {
		$sub_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $sub_id );
		delete_option( 'roxyapi_settings' );
		$this->set_exhausted_transient();

		$this->assertFalse( $this->invoke_should_show_exhausted() );

		ob_start();
		Notice::maybe_show_exhausted();
		$this->assertSame( '', (string) ob_get_clean() );

		$this->clear_exhausted_state();
	}

	public function test_exhausted_notice_skipped_when_dismissed_today(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		delete_option( 'roxyapi_settings' );
		$this->set_exhausted_transient();
		update_user_meta( $admin_id, 'roxyapi_free_tier_dismissed_' . gmdate( 'Y-m-d' ), '1' );

		$this->assertFalse( $this->invoke_should_show_exhausted() );

		ob_start();
		Notice::maybe_show_exhausted();
		$this->assertSame( '', (string) ob_get_clean() );

		$this->clear_exhausted_state();
	}
}
