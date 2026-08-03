<?php
/**
 * A reading that has not been set up yet must not reach the network.
 *
 * A block is inserted with every attribute empty, and the generated shortcodes
 * drop empty attributes while assembling the body, so an unconfigured reading
 * used to POST an empty object and be rejected. The block editor re-renders on
 * load and after every keystroke, so one unconfigured block produced a stream
 * of requests that could never succeed.
 *
 * The guard is emitted by bin/generate.mjs from the spec's `required` list.
 * These tests pin both halves of that: readings that need input are guarded,
 * and readings that legitimately take none are left alone.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Generated\Client as Generated;

class Test_Not_Configured_Guard extends Mock_Http_TestCase {

	/** URLs this test saw requested, in order. */
	private array $requested = array();

	public function setUp(): void {
		parent::setUp();
		$this->requested = array();
		add_filter( 'pre_http_request', array( $this, 'record' ), 5, 3 );
	}

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'record' ), 5 );
		parent::tearDown();
	}

	public function record( $preempt, $args, $url ) {
		$this->requested[] = $url;
		return $preempt;
	}

	public function test_reading_with_no_inputs_is_not_requested(): void {
		$result = Generated::calculateProfile( array() );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'roxyapi_not_configured', $result->get_error_code() );
		$this->assertSame( array(), $this->requested, 'An unconfigured reading must not be requested at all.' );
	}

	public function test_partially_configured_reading_is_not_requested(): void {
		$result = Generated::calculateProfile( array( 'date' => '1990-07-15' ) );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'roxyapi_not_configured', $result->get_error_code() );
		$this->assertSame( array(), $this->requested );
	}

	/**
	 * The counterpart that stops the guard being over-eager: today's card takes
	 * no input by design, so an empty body is a complete request.
	 */
	public function test_reading_that_takes_no_input_still_runs(): void {
		$this->mock_responses = array( 'tarot/daily' => array( 'card' => 'The Star' ) );

		$result = Generated::getDailyCard( array() );

		$this->assertIsArray( $result );
		$this->assertCount( 1, $this->requested, 'An input-free reading must still be requested.' );
	}

	/** Nothing is cached, so filling the setting in renders immediately rather than after a wait. */
	public function test_guard_result_is_not_cached(): void {
		Generated::calculateProfile( array() );
		$this->mock_responses = array( 'human-design/profile' => array( 'profile' => '1/3' ) );

		$result = Generated::calculateProfile(
			array(
				'date'     => '1990-07-15',
				'time'     => '14:30:00',
				'timezone' => 'UTC',
			)
		);

		$this->assertIsArray( $result );
		$this->assertCount( 1, $this->requested );
	}

	/** Visitors never see setup instructions written for the site owner. */
	public function test_message_is_hidden_from_visitors(): void {
		wp_set_current_user( 0 );
		$this->assertSame( '', \RoxyAPI\Support\Templates::api_error( \RoxyAPI\Api\Client::not_configured() ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$this->assertStringContainsString(
			'not been set up',
			\RoxyAPI\Support\Templates::api_error( \RoxyAPI\Api\Client::not_configured() )
		);
	}
}
