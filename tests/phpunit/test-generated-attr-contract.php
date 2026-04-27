<?php
/**
 * Generated-shortcode example-contract test.
 *
 * The earlier `test-spec-contract.php` only exercises the 10 heroes. This
 * one covers the 120 long-tail generated shortcodes — the surface that ships
 * one shortcode per OpenAPI operation.
 *
 * What it asserts (in plain English):
 *
 * 1. For every generated POST shortcode whose Endpoints.attributes map carries
 *    examples, running `do_shortcode( <sample with examples> )` produces a
 *    JSON body where every `type: number` / `type: integer` field encodes as
 *    a JSON number, NOT a string. Catches the
 *    `latitude: expected number, received string` regression class.
 *
 * 2. For every generated POST shortcode whose body has required scalar fields
 *    AND those fields have spec examples, the example values land in the body.
 *    Catches the `birthDate: expected string, received undefined` regression
 *    that broke the camelCase → lowercase shortcode-attr round-trip.
 *
 * Tests are skipped per-operation when the example set is incomplete (e.g.
 * required body field with no spec example, or nested-object body that flat
 * shortcode attrs cannot express). That is by design: a partial example
 * shouldn't fail the contract.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Generated\Endpoints;
use RoxyAPI\Support\Encryption;

class Test_Generated_Attr_Contract extends \WP_UnitTestCase {

	/** @var array<string,mixed>|null Last captured outgoing request args. */
	private ?array $captured = null;

	/** @var array<string,mixed> Decoded spec snapshot. */
	private static array $spec = array();

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		$path = dirname( __DIR__, 2 ) . '/specs/openapi.json';
		if ( ! file_exists( $path ) ) {
			self::markTestSkipped( 'Spec snapshot not found at ' . $path );
		}
		$decoded = json_decode( (string) file_get_contents( $path ), true );
		self::$spec = is_array( $decoded ) ? $decoded : array();
	}

	public function setUp(): void {
		parent::setUp();
		$test_key = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.0123456789abcdef.test_key_plaintext';
		update_option(
			'roxyapi_settings',
			array( 'api_key_encrypted' => Encryption::encrypt( $test_key ) )
		);
		\RoxyAPI\Api\Cache::flush_all();
		$this->captured = null;
		add_filter( 'pre_http_request', array( $this, 'capture_http' ), 10, 2 );
	}

	public function tearDown(): void {
		remove_all_filters( 'pre_http_request' );
		delete_option( 'roxyapi_settings' );
		\RoxyAPI\Api\Cache::flush_all();
		parent::tearDown();
	}

	/**
	 * Capture outgoing request body, short-circuit with a benign 200.
	 */
	public function capture_http( $pre, $args ) {
		$this->captured = is_array( $args ) ? $args : null;
		return array(
			'headers'  => array(),
			'body'     => '{}',
			'response' => array( 'code' => 200, 'message' => 'OK' ),
			'cookies'  => array(),
			'filename' => null,
		);
	}

	/**
	 * Test: every numeric body field that has a spec example must encode as a
	 * JSON number when sent through the generated shortcode.
	 */
	public function test_numeric_body_fields_encode_as_json_numbers(): void {
		$violations = array();
		$checked    = 0;

		foreach ( Endpoints::all() as $op_id => $ep ) {
			if ( $ep['method'] !== 'POST' ) {
				continue;
			}
			if ( ! empty( $ep['hero'] ) ) {
				// Heroes are covered by test-spec-contract.php.
				continue;
			}
			$attrs = $ep['attributes'] ?? array();
			if ( empty( $attrs ) ) {
				continue;
			}

			$body_schema = self::body_schema_for( (string) $op_id );
			if ( $body_schema === null ) {
				continue;
			}
			$numeric_fields = self::numeric_fields( $body_schema );
			if ( empty( $numeric_fields ) ) {
				continue;
			}

			$shortcode = self::build_shortcode_call( (string) $op_id, $attrs );
			$this->captured = null;
			do_shortcode( $shortcode );

			if ( $this->captured === null || ! isset( $this->captured['body'] ) ) {
				continue;
			}
			$decoded = json_decode( (string) $this->captured['body'], true );
			if ( ! is_array( $decoded ) ) {
				continue;
			}

			++$checked;
			foreach ( $numeric_fields as $field ) {
				if ( ! array_key_exists( $field, $decoded ) ) {
					continue;
				}
				if ( ! is_int( $decoded[ $field ] ) && ! is_float( $decoded[ $field ] ) ) {
					$violations[] = sprintf(
						'%s.%s sent as %s (%s) — expected JSON number',
						$op_id,
						$field,
						gettype( $decoded[ $field ] ),
						var_export( $decoded[ $field ], true )
					);
				}
			}
		}

		$this->assertGreaterThan( 0, $checked, 'No POST endpoints with numeric examples were exercised — coverage gap.' );
		$this->assertSame( array(), $violations, "Numeric-body-field contract violations:\n  " . implode( "\n  ", $violations ) );
	}

	/**
	 * Test: required scalar body fields that have spec examples must arrive in
	 * the request body (proves the shortcode-attr round-trip is intact).
	 */
	public function test_required_scalar_examples_land_in_body(): void {
		$violations = array();
		$checked    = 0;

		foreach ( Endpoints::all() as $op_id => $ep ) {
			if ( $ep['method'] !== 'POST' ) {
				continue;
			}
			if ( ! empty( $ep['hero'] ) ) {
				continue;
			}
			$attrs = $ep['attributes'] ?? array();
			if ( empty( $attrs ) ) {
				continue;
			}

			$body_schema = self::body_schema_for( (string) $op_id );
			if ( $body_schema === null ) {
				continue;
			}

			$required_scalars = self::required_scalar_fields_with_examples( $body_schema );
			if ( empty( $required_scalars ) ) {
				continue;
			}

			$shortcode = self::build_shortcode_call( (string) $op_id, $attrs );
			$this->captured = null;
			do_shortcode( $shortcode );
			if ( $this->captured === null || ! isset( $this->captured['body'] ) ) {
				continue;
			}
			$decoded = json_decode( (string) $this->captured['body'], true );
			if ( ! is_array( $decoded ) ) {
				continue;
			}
			++$checked;

			foreach ( $required_scalars as $field ) {
				if ( ! array_key_exists( $field, $decoded ) || $decoded[ $field ] === '' || $decoded[ $field ] === null ) {
					$violations[] = sprintf(
						'%s: required field "%s" missing from outgoing body (likely camelCase → lowercase shortcode-attr regression)',
						$op_id,
						$field
					);
				}
			}
		}

		$this->assertGreaterThan( 0, $checked, 'No POST endpoints with required scalar examples were exercised.' );
		$this->assertSame( array(), $violations, "Required-field contract violations:\n  " . implode( "\n  ", $violations ) );
	}

	// -------------------------------------------------------------------------
	// helpers
	// -------------------------------------------------------------------------

	/**
	 * Build a shortcode invocation string from the operation id and the
	 * Endpoints.attributes map.
	 *
	 * @param string                $op_id   OperationId.
	 * @param array<string, string> $attrs   Snake_case attribute → example.
	 * @return string
	 */
	private static function build_shortcode_call( string $op_id, array $attrs ): string {
		$snake = preg_replace( '/(?<!^)([A-Z])/', '_$1', $op_id );
		$tag   = 'roxy_' . strtolower( (string) $snake );
		$parts = array( $tag );
		foreach ( $attrs as $name => $value ) {
			if ( ! is_scalar( $value ) || (string) $value === '' ) {
				continue;
			}
			$parts[] = $name . '="' . str_replace( '"', '&quot;', (string) $value ) . '"';
		}
		return '[' . implode( ' ', $parts ) . ']';
	}

	/**
	 * Resolve the request-body JSON schema for an operationId from the spec.
	 *
	 * @return array<string,mixed>|null
	 */
	private static function body_schema_for( string $op_id ): ?array {
		foreach ( ( self::$spec['paths'] ?? array() ) as $methods ) {
			foreach ( (array) $methods as $op ) {
				if ( ! is_array( $op ) ) {
					continue;
				}
				if ( ( $op['operationId'] ?? '' ) !== $op_id ) {
					continue;
				}
				$schema = $op['requestBody']['content']['application/json']['schema'] ?? null;
				if ( ! is_array( $schema ) ) {
					return null;
				}
				return self::resolve_ref( $schema );
			}
		}
		return null;
	}

	/**
	 * Names of body properties whose type is `number` or `integer`.
	 *
	 * @param array<string,mixed> $schema
	 * @return array<int, string>
	 */
	private static function numeric_fields( array $schema ): array {
		$out   = array();
		$props = $schema['properties'] ?? array();
		foreach ( $props as $name => $prop ) {
			$resolved = self::resolve_ref( is_array( $prop ) ? $prop : array() );
			$type     = $resolved['type'] ?? null;
			if ( $type === 'number' || $type === 'integer' ) {
				$out[] = (string) $name;
			}
		}
		return $out;
	}

	/**
	 * Required scalar fields (string/number/integer/boolean) that have a
	 * scalar `example` value in the spec.
	 *
	 * @param array<string,mixed> $schema
	 * @return array<int, string>
	 */
	private static function required_scalar_fields_with_examples( array $schema ): array {
		$required = array_flip( (array) ( $schema['required'] ?? array() ) );
		if ( empty( $required ) ) {
			return array();
		}
		$out   = array();
		$props = $schema['properties'] ?? array();
		foreach ( $props as $name => $prop ) {
			if ( ! isset( $required[ $name ] ) ) {
				continue;
			}
			$resolved = self::resolve_ref( is_array( $prop ) ? $prop : array() );
			$type     = $resolved['type'] ?? null;
			if ( ! in_array( $type, array( 'string', 'number', 'integer', 'boolean' ), true ) ) {
				continue;
			}
			if ( ! isset( $resolved['example'] ) || ! is_scalar( $resolved['example'] ) ) {
				continue;
			}
			$out[] = (string) $name;
		}
		return $out;
	}

	/**
	 * Resolve a single $ref one level deep against the spec's components map.
	 *
	 * @param array<string,mixed> $node
	 * @return array<string,mixed>
	 */
	private static function resolve_ref( array $node ): array {
		if ( ! isset( $node['$ref'] ) || ! is_string( $node['$ref'] ) ) {
			return $node;
		}
		$parts = explode( '/', $node['$ref'] );
		$ref   = self::$spec;
		array_shift( $parts );
		foreach ( $parts as $part ) {
			if ( ! is_array( $ref ) || ! array_key_exists( $part, $ref ) ) {
				return $node;
			}
			$ref = $ref[ $part ];
		}
		return is_array( $ref ) ? $ref : $node;
	}
}
