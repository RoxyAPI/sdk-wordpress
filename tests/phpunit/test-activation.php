<?php
/**
 * Tests for plugin activation, deactivation, and the post-activation redirect.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Activation;
use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Api\Cache;
use RoxyAPI\Plugin;
use RoxyAPI\Support\Encryption;

class Test_Activation extends \WP_UnitTestCase {

	private ?string $captured_redirect;

	public function setUp(): void {
		parent::setUp();
		Cache::flush_all();
		wp_cache_flush();
		delete_transient( 'roxyapi_activated' );
		$this->captured_redirect = null;
	}

	public function tearDown(): void {
		Cache::flush_all();
		wp_cache_flush();
		delete_transient( 'roxyapi_activated' );
		remove_all_filters( 'wp_redirect' );
		remove_all_filters( 'wp_doing_ajax' );
		unset( $_GET['activate-multi'] );
		wp_set_current_user( 0 );
		parent::tearDown();
	}

	public function test_activate_sets_short_lived_transient(): void {
		Activation::activate();
		$this->assertSame( '1', get_transient( 'roxyapi_activated' ) );
	}

	public function test_deactivate_does_not_delete_user_data(): void {
		// Detach the sanitize callback so update_option preserves the value.
		remove_filter(
			'sanitize_option_' . SettingsPage::OPTION_NAME,
			array( \RoxyAPI\Admin\SettingsFields::class, 'sanitize' )
		);
		$enc = Encryption::encrypt( 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.0123456789abcdef.deact' );
		update_option(
			'roxyapi_settings',
			array( 'api_key_encrypted' => $enc )
		);
		// Also set a user meta to confirm it survives.
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		update_user_meta( $user_id, 'roxyapi_dismissed_setup', '1' );

		Activation::deactivate();
		wp_cache_flush();

		$opts = get_option( 'roxyapi_settings' );
		$this->assertIsArray( $opts );
		$this->assertSame( $enc, $opts['api_key_encrypted'] );
		$this->assertSame( '1', get_user_meta( $user_id, 'roxyapi_dismissed_setup', true ) );
	}

	public function test_deactivate_flushes_cache(): void {
		set_transient( 'roxyapi_test_cache_entry', 'present', 3600 );
		Activation::deactivate();
		wp_cache_flush();
		$this->assertFalse( get_transient( 'roxyapi_test_cache_entry' ) );
	}

	private function capture_redirect(): void {
		add_filter(
			'wp_redirect',
			function ( $location ) {
				$this->captured_redirect = (string) $location;
				return false; // Prevent the redirect, which would call exit.
			}
		);
	}

	private function exit_on_redirect_block(): callable {
		// wp_safe_redirect calls exit() unconditionally after wp_redirect
		// returns. We have to intercept by throwing inside the filter.
		return function ( $location ) {
			$this->captured_redirect = (string) $location;
			throw new \RuntimeException( 'redirect_intercepted' );
		};
	}

	public function test_redirect_happy_path_for_admin(): void {
		set_transient( 'roxyapi_activated', '1', 30 );
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		add_filter( 'wp_redirect', $this->exit_on_redirect_block(), 10, 2 );

		try {
			Plugin::maybe_redirect_after_activation();
			$this->fail( 'Redirect must have fired.' );
		} catch ( \RuntimeException $e ) {
			$this->assertSame( 'redirect_intercepted', $e->getMessage() );
		}

		$this->assertNotNull( $this->captured_redirect );
		$this->assertStringContainsString( 'admin.php?page=' . SettingsPage::PAGE_SLUG, (string) $this->captured_redirect );

		// The transient must have been consumed even though redirect was
		// intercepted; the source deletes it before the redirect call.
		$this->assertFalse( get_transient( 'roxyapi_activated' ) );
	}

	public function test_no_redirect_when_transient_missing(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		delete_transient( 'roxyapi_activated' );

		$this->capture_redirect();
		Plugin::maybe_redirect_after_activation();
		$this->assertNull( $this->captured_redirect );
	}

	public function test_no_redirect_during_ajax(): void {
		set_transient( 'roxyapi_activated', '1', 30 );
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		add_filter( 'wp_doing_ajax', '__return_true' );
		$this->capture_redirect();

		Plugin::maybe_redirect_after_activation();
		$this->assertNull( $this->captured_redirect );

		// Guards run BEFORE the transient is consumed, so an AJAX request that
		// happens to land on admin_init right after activation must NOT eat the
		// redirect intended for the next real admin page load.
		$this->assertSame( '1', get_transient( 'roxyapi_activated' ) );
	}

	public function test_no_redirect_for_subscriber(): void {
		set_transient( 'roxyapi_activated', '1', 30 );
		$sub_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $sub_id );

		$this->capture_redirect();
		Plugin::maybe_redirect_after_activation();
		$this->assertNull( $this->captured_redirect );
	}

	public function test_no_redirect_when_activate_multi_is_set(): void {
		set_transient( 'roxyapi_activated', '1', 30 );
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		$_GET['activate-multi'] = '1';

		$this->capture_redirect();
		Plugin::maybe_redirect_after_activation();
		$this->assertNull( $this->captured_redirect );
	}

	public function test_no_redirect_with_rest_request_constant(): void {
		// REST_REQUEST cannot be safely defined in this single test process
		// because it persists into other tests. We instead document that the
		// guard exists in source.
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/src/Plugin.php' );
		$this->assertIsString( $source );
		$this->assertStringContainsString( "defined( 'REST_REQUEST' ) && REST_REQUEST", $source );
	}

	public function test_no_redirect_with_wp_cli_constant(): void {
		// WP_CLI cannot be defined here without affecting later tests.
		// Instead verify the guard is present in source.
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/src/Plugin.php' );
		$this->assertIsString( $source );
		$this->assertStringContainsString( "defined( 'WP_CLI' ) && WP_CLI", $source );
	}
}
