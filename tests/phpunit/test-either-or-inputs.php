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
	 * Operation id to its input sets and the body keys it requires besides
	 * them, read from the vendored form model.
	 *
	 * @return array<string, array{sets: string[][], required: string[]}>
	 */
	private static function either_or_operations(): array {
		$manifest   = json_decode( (string) file_get_contents( dirname( __DIR__, 2 ) . '/bin/ui-forms/forms.json' ), true );
		$operations = array();
		foreach ( $manifest['forms'] as $operation_id => $model ) {
			if ( empty( $model['requiredAnyOf'] ) ) {
				continue;
			}
			$in_sets  = array_merge( ...$model['requiredAnyOf'] );
			$required = array();
			foreach ( $model['fields'] as $field ) {
				if ( ! empty( $field['required'] ) && ! in_array( $field['key'], $in_sets, true ) ) {
					$required[] = explode( '.', $field['key'] )[0];
				}
			}
			$operations[ $operation_id ] = array(
				'sets'     => $model['requiredAnyOf'],
				'required' => array_values( array_unique( $required ) ),
			);
		}
		return $operations;
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
		foreach ( self::either_or_operations() as $operation_id => $inputs ) {
			$required = array_fill_keys( $inputs['required'], '1' );
			foreach ( $inputs['sets'] as $set ) {
				$this->requested = array();
				Generated::$operation_id( $required + array_fill_keys( $set, '1' ) );
				$this->assertCount( 1, $this->requested, "$operation_id with [" . implode( ', ', $set ) . '] must be requested.' );
			}
			$first    = $inputs['sets'][0];
			$partials = array( $required + array_fill_keys( array_slice( $first, 0, count( $first ) - 1 ), '1' ) );
			if ( $inputs['required'] ) {
				// A complete set without the keys the body also requires.
				$partials[] = array_fill_keys( $first, '1' );
			}
			foreach ( $partials as $body ) {
				$this->requested = array();
				$result          = Generated::$operation_id( $body );
				$label           = "$operation_id with [" . implode( ', ', array_keys( $body ) ) . ']';
				$this->assertInstanceOf( \WP_Error::class, $result, "$label must not be requested." );
				$this->assertSame( 'roxyapi_not_configured', $result->get_error_code(), "$label is not set up." );
				$this->assertSame( array(), $this->requested );
			}
		}
	}
}
