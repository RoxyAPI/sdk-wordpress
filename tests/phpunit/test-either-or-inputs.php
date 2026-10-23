<?php
/**
 * An either-or reading (a Kua, or a birth date and gender) marks nothing
 * required and publishes its input sets instead, carried by the UI form model
 * as `requiredAnyOf`. Placed bare it must show visitors the form without a
 * request, and any one complete set must reach the API.
 *
 * Derived from the vendored model, so a new either-or operation is covered on
 * the next re-vendor with no edit here.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Generated\Client as Generated;
use RoxyAPI\Generated\Endpoints;

class Test_Either_Or_Inputs extends Mock_Http_TestCase {

	/** URLs this test saw requested, in order. */
	private array $requested = array();

	public function setUp(): void {
		parent::setUp();
		$this->requested      = array();
		$this->mock_responses = array( 'roxyapi.com' => array( 'ok' => true ) );
		add_filter( 'pre_http_request', array( $this, 'record' ), 5, 3 );
		wp_set_current_user( 0 );
	}

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'record' ), 5 );
		parent::tearDown();
	}

	public function record( $preempt, $args, $url ) {
		$this->requested[] = $url;
		return $preempt;
	}

	/**
	 * Operation id to its input sets, read from the vendored form model.
	 *
	 * @return array<string, string[][]>
	 */
	private static function either_or_operations(): array {
		$manifest = json_decode( (string) file_get_contents( dirname( __DIR__, 2 ) . '/bin/ui-forms/forms.json' ), true );
		$sets     = array();
		foreach ( $manifest['forms'] as $operation_id => $model ) {
			if ( ! empty( $model['requiredAnyOf'] ) ) {
				$sets[ $operation_id ] = $model['requiredAnyOf'];
			}
		}
		return $sets;
	}

	public function test_bare_placement_shows_visitors_a_form_without_a_request(): void {
		$operations = self::either_or_operations();
		$this->assertGreaterThanOrEqual( 7, count( $operations ), 'The walk found no either-or operations.' );

		$endpoints = Endpoints::all();
		foreach ( array_keys( $operations ) as $operation_id ) {
			$tag = $endpoints[ $operation_id ]['shortcode_tag'];
			$out = do_shortcode( "[$tag]" );
			$this->assertStringContainsString( '<form', $out, "[$tag] placed bare must offer the visitor form." );
			$this->assertStringNotContainsString( 'roxyapi-error', $out, "[$tag] placed bare must not show an error." );
		}
		$this->assertSame( array(), $this->requested, 'A bare either-or reading must not be requested.' );
	}

	public function test_any_complete_set_reaches_the_api_and_a_partial_one_does_not(): void {
		foreach ( self::either_or_operations() as $operation_id => $sets ) {
			foreach ( $sets as $set ) {
				$this->requested = array();
				Generated::$operation_id( array_fill_keys( $set, '1' ) );
				$this->assertCount( 1, $this->requested, "$operation_id with [" . implode( ', ', $set ) . '] must be requested.' );
			}
			$this->requested = array();
			$result          = Generated::$operation_id( array_fill_keys( array_slice( $sets[0], 0, count( $sets[0] ) - 1 ), '1' ) );
			$this->assertSame( 'roxyapi_not_configured', $result->get_error_code(), "$operation_id with an incomplete set is not set up." );
			$this->assertSame( array(), $this->requested );
		}
	}
}
