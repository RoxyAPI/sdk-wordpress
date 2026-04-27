<?php
/**
 * Regression test: every entry in `bin/hero-config.json` references an
 * operationId that exists in `specs/openapi.json` and has a non-empty
 * requestBody or parameters list.
 *
 * Catches accidental config typos before they reach the generator. A typo
 * here would silently emit a hero class that calls a non-existent client
 * method, which would only surface at the next phpunit run as a fatal
 * elsewhere; this test pins the failure to the config itself.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

class Test_Hero_Config_Completeness extends \WP_UnitTestCase {

	/**
	 * Cached spec contents.
	 *
	 * @var array<string, mixed>|null
	 */
	private static $spec = null;

	/**
	 * Cached hero config contents.
	 *
	 * @var array<string, mixed>|null
	 */
	private static $config = null;

	private function plugin_root(): string {
		return dirname( __DIR__, 2 );
	}

	/**
	 * @return array<string, mixed>
	 */
	private function load_spec(): array {
		if ( self::$spec === null ) {
			$path = $this->plugin_root() . '/specs/openapi.json';
			$this->assertFileExists( $path, 'specs/openapi.json must exist for the contract test.' );
			$decoded = json_decode( (string) file_get_contents( $path ), true );
			$this->assertIsArray( $decoded, 'specs/openapi.json must decode to an array.' );
			self::$spec = $decoded;
		}
		return self::$spec;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function load_config(): array {
		if ( self::$config === null ) {
			$path = $this->plugin_root() . '/bin/hero-config.json';
			$this->assertFileExists( $path, 'bin/hero-config.json must exist.' );
			$decoded = json_decode( (string) file_get_contents( $path ), true );
			$this->assertIsArray( $decoded, 'bin/hero-config.json must decode to an array.' );
			self::$config = $decoded;
		}
		return self::$config;
	}

	/**
	 * Walk every hero config entry and assert each operationId it references
	 * (single-target or via dispatch) is registered in the spec with a
	 * non-empty parameter or requestBody set.
	 */
	public function test_every_referenced_operation_id_exists_in_spec_with_inputs(): void {
		$spec   = $this->load_spec();
		$config = $this->load_config();

		$by_op_id = array();
		foreach ( (array) ( $spec['paths'] ?? array() ) as $path => $methods ) {
			if ( ! is_array( $methods ) ) {
				continue;
			}
			foreach ( $methods as $method => $op ) {
				if ( ! is_array( $op ) || empty( $op['operationId'] ) ) {
					continue;
				}
				$op_id              = (string) $op['operationId'];
				$by_op_id[ $op_id ] = array(
					'parameters'  => is_array( $op['parameters'] ?? null ) ? $op['parameters'] : array(),
					'requestBody' => $op['requestBody'] ?? null,
				);
			}
		}

		foreach ( $config as $hero_key => $entry ) {
			if ( ! is_array( $entry ) ) {
				continue;
			}
			if ( ! empty( $entry['hand_written'] ) ) {
				continue;
			}

			$op_ids = array();
			if ( ! empty( $entry['operationId'] ) ) {
				$op_ids[] = (string) $entry['operationId'];
			}
			if ( isset( $entry['dispatch'] ) && is_array( $entry['dispatch'] ) ) {
				foreach ( $entry['dispatch'] as $branch ) {
					if ( is_array( $branch ) && ! empty( $branch['operationId'] ) ) {
						$op_ids[] = (string) $branch['operationId'];
					}
				}
			}

			$this->assertNotEmpty(
				$op_ids,
				"Hero '{$hero_key}' must declare at least one operationId (single or via dispatch)."
			);

			foreach ( $op_ids as $op_id ) {
				$this->assertArrayHasKey(
					$op_id,
					$by_op_id,
					"Hero '{$hero_key}' references operationId '{$op_id}' which is not in specs/openapi.json."
				);
				$slot = $by_op_id[ $op_id ];
				$has_inputs = ( count( $slot['parameters'] ) > 0 ) || ( $slot['requestBody'] !== null );
				$this->assertTrue(
					$has_inputs,
					"OperationId '{$op_id}' (used by hero '{$hero_key}') has no parameters and no requestBody in the spec."
				);
			}
		}
	}

	/**
	 * Every hero config entry's `tag` (or its derived tag) must match a
	 * registered hero shortcode in the registrar.
	 */
	public function test_every_config_tag_is_registered(): void {
		$config   = $this->load_config();
		$registry = \RoxyAPI\Shortcodes\Registrar::HERO_SHORTCODES;

		foreach ( $config as $hero_key => $entry ) {
			if ( ! is_array( $entry ) ) {
				continue;
			}
			$tag = isset( $entry['tag'] ) ? (string) $entry['tag'] : 'roxy_' . (string) $hero_key;
			$this->assertArrayHasKey(
				$tag,
				$registry,
				"Hero config tag '{$tag}' (from '{$hero_key}') must be registered in Registrar::HERO_SHORTCODES."
			);
		}
	}
}
