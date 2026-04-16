<?php
/**
 * Abstract base class that mocks wp_remote_* via the pre_http_request filter.
 *
 * Subclasses populate $mock_responses keyed by substring match on the request URL.
 * Any request whose URL contains a mocked substring short-circuits to the canned
 * response. Every other request falls through to the real HTTP API (should never
 * happen in CI).
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

abstract class Mock_Http_TestCase extends \WP_UnitTestCase {

	protected array $mock_responses = array();

	public function setUp(): void {
		parent::setUp();
		add_filter( 'pre_http_request', array( $this, 'mock_http' ), 10, 3 );
	}

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'mock_http' ), 10 );
		parent::tearDown();
	}

	public function mock_http( $preempt, $args, $url ) {
		foreach ( $this->mock_responses as $needle => $response ) {
			if ( strpos( $url, $needle ) !== false ) {
				return array(
					'headers'  => array(),
					'body'     => wp_json_encode( $response ),
					'response' => array( 'code' => 200, 'message' => 'OK' ),
					'cookies'  => array(),
					'filename' => null,
				);
			}
		}
		return $preempt;
	}
}
