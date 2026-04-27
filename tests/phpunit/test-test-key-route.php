<?php
/**
 * Tests for the /roxyapi/v1/test-key REST route.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\SettingsFields;
use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Api\TestKeyRoute;
use RoxyAPI\Support\Encryption;
use WP_REST_Request;
use WP_REST_Server;

class Test_Test_Key_Route extends Mock_Http_TestCase {

	/**
	 * @var WP_REST_Server
	 */
	private $server;

	public function setUp(): void {
		parent::setUp();
		delete_option( 'roxyapi_settings' );

		// Spin up a fresh REST server and trigger the proper action so route
		// registration happens through the rest_api_init hook (otherwise WP
		// emits a _doing_it_wrong notice that fails the test).
		global $wp_rest_server;
		$wp_rest_server = new WP_REST_Server();
		$this->server   = $wp_rest_server;
		do_action( 'rest_api_init' );

		// rest_api_init also re-attaches the roxyapi_settings sanitize callback
		// (see SettingsPage::register_setting). When tests update the option
		// directly with api_key_encrypted, that callback strips the value back
		// out. Detach it for the duration of the test.
		remove_filter( 'sanitize_option_' . SettingsPage::OPTION_NAME, array( SettingsFields::class, 'sanitize' ) );
	}

	public function tearDown(): void {
		global $wp_rest_server;
		$wp_rest_server = null;
		wp_set_current_user( 0 );
		parent::tearDown();
	}

	public function test_returns_not_ok_when_no_key_configured(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/roxyapi/v1/test-key' ) );
		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertFalse( $data['ok'] );
		$this->assertStringContainsString( 'No API key configured', (string) $data['message'] );
	}

	public function test_returns_ok_on_successful_mocked_call(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$test_key = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.0123456789abcdef.test_key_value';
		update_option(
			'roxyapi_settings',
			array( 'api_key_encrypted' => Encryption::encrypt( $test_key ) )
		);
		$this->mock_responses['astrology/horoscope/aries/daily'] = array(
			'sign'     => 'aries',
			'overview' => 'A bold day ahead.',
		);

		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/roxyapi/v1/test-key' ) );
		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertTrue( $data['ok'], 'Expected ok=true. Message was: ' . (string) ( $data['message'] ?? '(none)' ) );
	}

	public function test_forbids_non_admin_users(): void {
		$subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber_id );

		$response = $this->server->dispatch( new WP_REST_Request( 'GET', '/roxyapi/v1/test-key' ) );
		$this->assertSame( 403, $response->get_status() );
	}
}
