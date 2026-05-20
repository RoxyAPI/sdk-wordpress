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
		delete_transient( 'roxyapi_free_tier_exhausted_seen' );
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

	public function test_no_api_key_configured_omits_header_and_passes_through(): void {
		// With no key configured, the plugin no longer short-circuits with a
		// `roxyapi_no_key` WP_Error. Instead, the request goes out with the
		// X-API-Key header absent, so the SaaS free-tier sandbox sees the
		// unauthenticated path and either serves a demo response or returns
		// 429 with `code: free_tier_exhausted`. Here we mock a 200 to assert
		// the request travels through and the header is omitted.
		delete_option( 'roxyapi_settings' );

		$captured_headers = null;
		add_filter(
			'pre_http_request',
			static function ( $pre, $args ) use ( &$captured_headers ) {
				$captured_headers = $args['headers'] ?? array();
				return array(
					'headers'  => array(),
					'body'     => wp_json_encode( array( 'ok' => true ) ),
					'response' => array( 'code' => 200, 'message' => 'OK' ),
					'cookies'  => array(),
					'filename' => null,
				);
			},
			10,
			2
		);

		$out = Client::get( 'astrology/horoscope/aries/daily' );
		$this->assertIsArray( $out );
		$this->assertSame( true, $out['ok'] );
		$this->assertIsArray( $captured_headers );
		$this->assertArrayNotHasKey( 'X-API-Key', $captured_headers );
		// Sanity-check the other identifying headers stay attached.
		$this->assertArrayHasKey( 'X-SDK-Client', $captured_headers );
		$this->assertArrayHasKey( 'X-Site-URL', $captured_headers );
		$this->assertArrayHasKey( 'Accept', $captured_headers );
		$this->assertArrayHasKey( 'User-Agent', $captured_headers );
	}

	public function test_api_key_configured_sends_header(): void {
		// Counterpart to the no-key path: with a real key, X-API-Key MUST be
		// present and equal to the resolved plaintext key.
		$captured_headers = null;
		add_filter(
			'pre_http_request',
			static function ( $pre, $args ) use ( &$captured_headers ) {
				$captured_headers = $args['headers'] ?? array();
				return array(
					'headers'  => array(),
					'body'     => wp_json_encode( array( 'ok' => true ) ),
					'response' => array( 'code' => 200, 'message' => 'OK' ),
					'cookies'  => array(),
					'filename' => null,
				);
			},
			10,
			2
		);

		Client::get( 'astrology/horoscope/aries/daily' );
		$this->assertIsArray( $captured_headers );
		$this->assertArrayHasKey( 'X-API-Key', $captured_headers );
		$this->assertSame( $this->test_key, $captured_headers['X-API-Key'] );
	}

	public function test_free_tier_exhausted_429_returns_distinct_error_code(): void {
		// T-02: 429 with `code: free_tier_exhausted` returns
		// `roxyapi_free_tier_exhausted` (NOT `roxyapi_quota`) so the admin
		// notice path can distinguish demo exhaustion from paid-key exhaustion.
		delete_option( 'roxyapi_settings' );
		delete_transient( 'roxyapi_free_tier_exhausted_seen' );

		$this->mock_response(
			429,
			wp_json_encode(
				array(
					'error' => 'Free playground daily limit reached. Get an API key at https://roxyapi.com/pricing for production use.',
					'code'  => 'free_tier_exhausted',
				)
			)
		);
		$out = Client::get( 'astrology/horoscope/aries/daily' );
		$this->assertInstanceOf( WP_Error::class, $out );
		$this->assertSame( 'roxyapi_free_tier_exhausted', $out->get_error_code() );
		$data = $out->get_error_data();
		$this->assertSame( 429, $data['status'] );
		$this->assertSame( 'free_tier_exhausted', $data['saas_code'] );
		// Transient must be set so the admin notice can trip on next page load.
		$this->assertNotEmpty( get_transient( 'roxyapi_free_tier_exhausted_seen' ) );
		// Visitor copy must NOT leak the SaaS-side machine code or status.
		$message = $out->get_error_message();
		$this->assertStringNotContainsString( '429', $message );
		$this->assertStringNotContainsString( 'free_tier_exhausted', $message );
	}

	public function test_regular_429_still_returns_roxyapi_quota(): void {
		// Regression: ANY 429 that does NOT carry `code: free_tier_exhausted`
		// must continue to surface as `roxyapi_quota` (paid-key quota path).
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
