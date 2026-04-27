<?php
/**
 * FormRouter tests.
 *
 * Drives the dispatcher with synthetic $_POST payloads and asserts: nonce
 * verification, rate-limit, sanitisation by field type (date / time / number
 * / timezone / enum / text), validation of required fields, body building
 * with empty-value pruning, and PRG redirect with a transient `roxyapi_r` key.
 *
 * No real HTTP — capture via pre_http_request.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Support\Encryption;
use RoxyAPI\Support\FormRouter;

class Test_Form_Router extends \WP_UnitTestCase {

	/** Captured outgoing request body, decoded. */
	private ?array $captured_body = null;

	/** URL passed to wp_safe_redirect. Captured via filter. */
	private ?string $redirect_url = null;

	public function setUp(): void {
		parent::setUp();
		$test_key = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.0123456789abcdef.test_key_plaintext';
		update_option(
			'roxyapi_settings',
			array( 'api_key_encrypted' => Encryption::encrypt( $test_key ) )
		);
		\RoxyAPI\Api\Cache::flush_all();

		$this->captured_body = null;
		$this->redirect_url  = null;

		add_filter(
			'pre_http_request',
			function ( $pre, $args ) {
				$body                = is_string( $args['body'] ?? null ) ? json_decode( $args['body'], true ) : null;
				$this->captured_body = is_array( $body ) ? $body : null;
				return array(
					'headers'  => array(),
					'body'     => wp_json_encode( array( 'ok' => true, 'echo' => $this->captured_body ) ),
					'response' => array( 'code' => 200, 'message' => 'OK' ),
					'cookies'  => array(),
					'filename' => null,
				);
			},
			10,
			2
		);

		// Suppress the redirect+exit pair via the FormRouter test hook and
		// capture the computed target URL.
		add_filter(
			'roxyapi_form_router_skip_redirect',
			function ( $skip, $key, $url ) {
				$this->redirect_url = (string) $url;
				return $url;
			},
			10,
			3
		);
	}

	public function tearDown(): void {
		remove_all_filters( 'pre_http_request' );
		remove_all_filters( 'roxyapi_form_router_skip_redirect' );
		delete_option( 'roxyapi_settings' );
		\RoxyAPI\Api\Cache::flush_all();
		// Wipe transients FormRouter set during the test.
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_roxyapi_form_%' OR option_name LIKE '_transient_timeout_roxyapi_form_%'" );
		parent::tearDown();
	}

	/**
	 * Drive the router with a $_POST payload; catch the exit() that
	 * wp_safe_redirect normally calls.
	 */
	private function dispatch( array $post ): void {
		$_POST   = $post;
		$_SERVER['REQUEST_METHOD'] = 'POST';
		try {
			FormRouter::maybe_handle();
		} catch ( \Throwable $e ) {
			// wp_safe_redirect's exit is suppressed by our wp_redirect filter.
			// Anything thrown is a real bug.
			$this->fail( 'Unexpected throwable: ' . $e->getMessage() );
		}
		$_POST = array();
		unset( $_SERVER['REQUEST_METHOD'] );
	}

	/**
	 * Pull the transient that the most-recent redirect points at.
	 */
	private function consume_transient_for_url( ?string $url ): ?array {
		if ( $url === null ) {
			return null;
		}
		$query = wp_parse_url( $url, PHP_URL_QUERY );
		if ( ! is_string( $query ) ) {
			return null;
		}
		parse_str( $query, $args );
		$key = $args['roxyapi_r'] ?? null;
		if ( ! is_string( $key ) || $key === '' ) {
			return null;
		}
		$data = get_transient( $key );
		return is_array( $data ) ? $data : null;
	}

	public function test_missing_form_id_is_a_noop(): void {
		$this->dispatch( array() );
		$this->assertNull( $this->redirect_url );
	}

	public function test_unknown_form_id_is_a_noop(): void {
		$this->dispatch( array( 'roxyapi_form' => 'thisDoesNotExist' ) );
		$this->assertNull( $this->redirect_url );
	}

	public function test_missing_consent_rejects_submission(): void {
		// GDPR Art. 9 gate: form must reject if visitor did not tick the consent
		// checkbox, even when nonce + body fields are otherwise valid.
		$nonce = wp_create_nonce( 'roxyapi_form_calculateSynastry' );
		$this->dispatch(
			array(
				'roxyapi_form' => 'calculateSynastry',
				'_wpnonce'     => $nonce,
				// roxyapi_consent intentionally omitted
				'person1' => array(
					'name'      => 'Alice',
					'date'      => '1990-05-15',
					'time'      => '14:30',
					'latitude'  => '40.71',
					'longitude' => '-74.0',
					'timezone'  => 'America/New_York',
				),
			)
		);
		$this->assertNull( $this->captured_body, 'API must not be called without consent' );
		$payload = $this->consume_transient_for_url( $this->redirect_url );
		$this->assertIsArray( $payload );
		$this->assertArrayHasKey( 'validation_errors', $payload );
		$this->assertArrayHasKey( '_consent', $payload['validation_errors'] );
	}

	public function test_invalid_nonce_redirects_with_error(): void {
		$this->dispatch(
			array(
				'roxyapi_form' => 'calculateSynastry',
				'_wpnonce'     => 'totally-bogus-nonce',
			)
		);
		$this->assertNotNull( $this->redirect_url, 'expected redirect URL to be captured' );
		$payload = $this->consume_transient_for_url( $this->redirect_url );
		$this->assertIsArray( $payload, 'redirect URL: ' . (string) $this->redirect_url );
		$this->assertArrayHasKey( 'error', $payload );
	}

	public function test_missing_required_field_redirects_with_validation_errors(): void {
		$nonce = wp_create_nonce( 'roxyapi_form_calculateSynastry' );
		$this->dispatch(
			array(
				'roxyapi_form' => 'calculateSynastry',
				'_wpnonce'     => $nonce,
				'roxyapi_consent' => '1',
				// person1.date intentionally missing
				'person1' => array(
					'time'      => '14:30',
					'latitude'  => '40.71',
					'longitude' => '-74.0',
					'timezone'  => 'America/New_York',
				),
			)
		);
		$payload = $this->consume_transient_for_url( $this->redirect_url );
		$this->assertIsArray( $payload );
		$this->assertArrayHasKey( 'validation_errors', $payload );
		$this->assertArrayHasKey( 'person1', $payload['validation_errors'] );
		$this->assertArrayHasKey( 'date', $payload['validation_errors']['person1'] );
	}

	public function test_full_submission_calls_api_with_typed_body_and_redirects_with_result(): void {
		$nonce = wp_create_nonce( 'roxyapi_form_calculateSynastry' );
		$this->dispatch(
			array(
				'roxyapi_form' => 'calculateSynastry',
				'_wpnonce'     => $nonce,
				'roxyapi_consent' => '1',
				'person1'      => array(
					'name'      => 'Alice',
					'date'      => '1990-05-15',
					'time'      => '14:30',
					'latitude'  => '40.7128',
					'longitude' => '-74.006',
					'timezone'  => 'America/New_York',
				),
				'person2'      => array(
					'name'      => 'Bob',
					'date'      => '1992-03-22',
					'time'      => '09:00',
					'latitude'  => '34.0522',
					'longitude' => '-118.2437',
					'timezone'  => 'America/Los_Angeles',
				),
				'houseSystem'  => 'placidus',
			)
		);

		$this->assertNotNull( $this->captured_body, 'API was never called' );
		$this->assertIsFloat( $this->captured_body['person1']['latitude'], 'latitude must encode as JSON number' );
		$this->assertSame( 40.7128, $this->captured_body['person1']['latitude'] );
		$this->assertSame( 'America/New_York', $this->captured_body['person1']['timezone'] );
		$this->assertSame( 'placidus', $this->captured_body['houseSystem'] );

		$payload = $this->consume_transient_for_url( $this->redirect_url );
		$this->assertIsArray( $payload );
		$this->assertArrayHasKey( 'result', $payload );
	}

	public function test_invalid_timezone_string_is_rejected_at_sanitize_step(): void {
		$nonce = wp_create_nonce( 'roxyapi_form_calculateSynastry' );
		$this->dispatch(
			array(
				'roxyapi_form' => 'calculateSynastry',
				'_wpnonce'     => $nonce,
				'roxyapi_consent' => '1',
				'person1'      => array(
					'date'      => '1990-05-15',
					'time'      => '14:30',
					'latitude'  => '40.71',
					'longitude' => '-74.0',
					'timezone'  => 'Not/A/Real/Zone',
				),
				'person2'      => array(
					'date'      => '1992-03-22',
					'time'      => '09:00',
					'latitude'  => '34.05',
					'longitude' => '-118.24',
					'timezone'  => 'America/Los_Angeles',
				),
			)
		);

		$payload = $this->consume_transient_for_url( $this->redirect_url );
		$this->assertIsArray( $payload );
		$this->assertArrayHasKey( 'validation_errors', $payload, 'Invalid IANA must surface as a required-field validation error' );
		$this->assertArrayHasKey( 'timezone', $payload['validation_errors']['person1'] );
	}

	public function test_out_of_range_latitude_rejected(): void {
		$nonce = wp_create_nonce( 'roxyapi_form_calculateSynastry' );
		$this->dispatch(
			array(
				'roxyapi_form' => 'calculateSynastry',
				'_wpnonce'     => $nonce,
				'roxyapi_consent' => '1',
				'person1'      => array(
					'date'      => '1990-05-15',
					'time'      => '14:30',
					'latitude'  => '999',                // way out of -90..90
					'longitude' => '-74.0',
					'timezone'  => 'America/New_York',
				),
				'person2'      => array(
					'date'      => '1992-03-22',
					'time'      => '09:00',
					'latitude'  => '34.05',
					'longitude' => '-118.24',
					'timezone'  => 'America/Los_Angeles',
				),
			)
		);

		$payload = $this->consume_transient_for_url( $this->redirect_url );
		$this->assertIsArray( $payload );
		$this->assertArrayHasKey( 'validation_errors', $payload );
		$this->assertArrayHasKey( 'latitude', $payload['validation_errors']['person1'] );
	}
}
