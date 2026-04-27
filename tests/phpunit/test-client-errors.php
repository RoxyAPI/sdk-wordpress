<?php
/**
 * Tests for RoxyAPI\Api\Client error categorisation.
 *
 * Mocks pre_http_request directly (rather than going through the parent
 * substring-match harness) so we can return arbitrary HTTP status codes and
 * non-JSON bodies for each scenario.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Api\Cache;
use RoxyAPI\Api\Client;
use RoxyAPI\Support\Encryption;
use WP_Error;

class Test_Client_Errors extends \WP_UnitTestCase {

	private string $test_key = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.0123456789abcdef.test';

	public function setUp(): void {
		parent::setUp();
		Cache::flush_all();
		wp_cache_flush();
		update_option(
			'roxyapi_settings',
			array( 'api_key_encrypted' => Encryption::encrypt( $this->test_key ) )
		);
	}

	public function tearDown(): void {
		remove_all_filters( 'pre_http_request' );
		delete_option( 'roxyapi_settings' );
		Cache::flush_all();
		wp_cache_flush();
		parent::tearDown();
	}

	private function mock_response( int $status, string $body ): void {
		add_filter(
			'pre_http_request',
			static function () use ( $status, $body ) {
				return array(
					'headers'  => array(),
					'body'     => $body,
					'response' => array(
						'code'    => $status,
						'message' => 'Mocked',
					),
					'cookies'  => array(),
					'filename' => null,
				);
			},
			10,
			3
		);
	}

	public function test_status_200_returns_decoded_array(): void {
		$this->mock_response( 200, wp_json_encode( array( 'ok' => true, 'value' => 7 ) ) );
		$out = Client::get( 'astrology/horoscope/aries/daily' );
		$this->assertIsArray( $out );
		$this->assertSame( true, $out['ok'] );
		$this->assertSame( 7, $out['value'] );
	}

	public function test_status_401_returns_roxyapi_auth(): void {
		$this->mock_response( 401, wp_json_encode( array( 'error' => 'Bad key', 'code' => 'unauthorized' ) ) );
		$out = Client::get( 'any' );
		$this->assertInstanceOf( WP_Error::class, $out );
		$this->assertSame( 'roxyapi_auth', $out->get_error_code() );
		$data = $out->get_error_data();
		$this->assertSame( 401, $data['status'] );
		$this->assertSame( 'unauthorized', $data['saas_code'] );
		$this->assertSame( 'Bad key', $data['saas_msg'] );
	}

	public function test_status_403_returns_roxyapi_auth(): void {
		$this->mock_response( 403, wp_json_encode( array( 'error' => 'Forbidden', 'code' => 'forbidden' ) ) );
		$out = Client::get( 'any' );
		$this->assertInstanceOf( WP_Error::class, $out );
		$this->assertSame( 'roxyapi_auth', $out->get_error_code() );
		$data = $out->get_error_data();
		$this->assertSame( 403, $data['status'] );
	}

	public function test_status_429_returns_roxyapi_quota(): void {
		$this->mock_response(
			429,
			wp_json_encode(
				array(
					'error' => 'Rate limit exceeded: 50 requests per month',
					'code'  => 'rate_limit_exceeded',
				)
			)
		);
		$out = Client::get( 'any' );
		$this->assertInstanceOf( WP_Error::class, $out );
		$this->assertSame( 'roxyapi_quota', $out->get_error_code() );
		$data = $out->get_error_data();
		$this->assertSame( 429, $data['status'] );
		$this->assertSame( 'rate_limit_exceeded', $data['saas_code'] );
	}

	public function test_status_500_returns_roxyapi_upstream(): void {
		$this->mock_response( 500, '' );
		$out = Client::get( 'any' );
		$this->assertInstanceOf( WP_Error::class, $out );
		$this->assertSame( 'roxyapi_upstream', $out->get_error_code() );
	}

	public function test_status_502_returns_roxyapi_upstream(): void {
		$this->mock_response( 502, '' );
		$out = Client::get( 'any' );
		$this->assertInstanceOf( WP_Error::class, $out );
		$this->assertSame( 'roxyapi_upstream', $out->get_error_code() );
	}

	public function test_status_503_returns_roxyapi_upstream(): void {
		$this->mock_response( 503, '' );
		$out = Client::get( 'any' );
		$this->assertInstanceOf( WP_Error::class, $out );
		$this->assertSame( 'roxyapi_upstream', $out->get_error_code() );
	}

	public function test_status_404_returns_uncategorised_http_code(): void {
		$this->mock_response( 404, wp_json_encode( array( 'error' => 'Not found' ) ) );
		$out = Client::get( 'unknown/endpoint' );
		$this->assertInstanceOf( WP_Error::class, $out );
		$this->assertSame( 'roxyapi_http_404', $out->get_error_code() );
		$this->assertStringContainsString( 'could not be loaded', $out->get_error_message() );
	}

	public function test_status_200_with_invalid_json_returns_roxyapi_json(): void {
		$this->mock_response( 200, '{not valid json' );
		$out = Client::get( 'any' );
		$this->assertInstanceOf( WP_Error::class, $out );
		$this->assertSame( 'roxyapi_json', $out->get_error_code() );
	}

	public function test_no_api_key_configured_returns_roxyapi_no_key(): void {
		delete_option( 'roxyapi_settings' );
		$out = Client::get( 'any' );
		$this->assertInstanceOf( WP_Error::class, $out );
		$this->assertSame( 'roxyapi_no_key', $out->get_error_code() );
	}

	public function test_friendly_messages_do_not_leak_internals(): void {
		// The friendly message text must NOT leak HTTP status codes, internal
		// machine codes, or upstream error text. Those are confined to
		// $error->get_error_data().
		$this->mock_response(
			401,
			wp_json_encode( array( 'error' => 'Invalid X-API-Key header', 'code' => 'unauthorized' ) )
		);
		$out     = Client::get( 'any' );
		$message = $out->get_error_message();
		$this->assertStringNotContainsString( '401', $message );
		$this->assertStringNotContainsString( 'unauthorized', $message );
		$this->assertStringNotContainsString( 'X-API-Key', $message );
	}

	public function test_post_with_empty_body_sends_json_object_not_array(): void {
		// Empty PHP arrays would JSON-encode to `[]`, but every RoxyAPI POST
		// schema expects an object. Capture the outgoing body and assert it is
		// the JSON object literal `{}` so attribute-less hero/long-tail POST
		// shortcodes do not 400 with `expected object, received array`.
		$captured = null;
		add_filter(
			'pre_http_request',
			static function ( $pre, $args ) use ( &$captured ) {
				$captured = $args['body'] ?? null;
				return array(
					'headers'  => array(),
					'body'     => wp_json_encode( array( 'ok' => true ) ),
					'response' => array(
						'code'    => 200,
						'message' => 'OK',
					),
					'cookies'  => array(),
					'filename' => null,
				);
			},
			10,
			2
		);

		Client::post( 'dreams/daily', array() );

		// Body must be a JSON object, never a JSON array. The exact
		// payload depends on language injection (the Client may add a
		// `lang` based on site locale), so assert the shape rather than
		// the literal — `{` opener and not `[`.
		$this->assertIsString( $captured );
		$this->assertStringStartsWith( '{', $captured, 'Empty POST body must encode as a JSON object, not an array' );
		$this->assertStringEndsWith( '}', $captured );
	}

	public function test_post_with_unencodable_payload_returns_json_encode_error(): void {
		// wp_json_encode returns false for resources or recursive structures.
		// Open a file handle (a resource) and post it as a body field.
		$fh = fopen( 'php://memory', 'r' );
		if ( $fh === false ) {
			$this->markTestSkipped( 'Could not open php://memory.' );
		}
		$out = Client::post( 'any', array( 'handle' => $fh ) );
		fclose( $fh );

		$this->assertInstanceOf( WP_Error::class, $out );
		$this->assertSame( 'roxyapi_json_encode', $out->get_error_code() );
	}
}
