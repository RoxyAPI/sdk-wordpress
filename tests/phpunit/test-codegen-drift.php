<?php
/**
 * Codegen drift guard.
 *
 * Cross-checks the operationIds referenced from `bin/*.json` config files
 * against the live `Endpoints::all()` registry. A spec rename or removal
 * would otherwise silently leave dangling references in
 * `bin/hero-config.json` / `bin/hero-endpoints.json` / `bin/ttl-map.json` /
 * `bin/example-overrides.json` that nothing else in CI catches.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Generated\Endpoints;

class Test_Codegen_Drift extends \WP_UnitTestCase {

	/** @var array<int, string> Cached operationIds for the suite. */
	private static array $live_ops = array();

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::$live_ops = array_keys( Endpoints::all() );
	}

	private function repo_root(): string {
		return dirname( __DIR__, 2 );
	}

	/**
	 * Read a JSON file at $relative path under the repo root and return its
	 * decoded array, or skip the test when the file is missing (running
	 * outside the dev tree).
	 *
	 * @param string $relative
	 * @return array<string, mixed>
	 */
	private function load_json( string $relative ): array {
		$path = $this->repo_root() . '/' . ltrim( $relative, '/' );
		if ( ! file_exists( $path ) ) {
			$this->markTestSkipped( "Config file not found at $relative — likely running outside the dev tree." );
		}
		$raw = (string) file_get_contents( $path );
		$out = json_decode( $raw, true );
		$this->assertIsArray( $out, "$relative must decode to an array" );
		return $out;
	}

	public function test_hero_endpoints_all_exist_in_registry(): void {
		$list = $this->load_json( 'bin/hero-endpoints.json' );
		$this->assertNotEmpty( $list, 'bin/hero-endpoints.json must list at least one operationId' );

		$missing = array_diff( $list, self::$live_ops );
		$this->assertEmpty(
			$missing,
			'bin/hero-endpoints.json references operationId(s) not in the live spec: ' . implode( ', ', (array) $missing )
		);
	}

	public function test_hero_config_operation_ids_all_exist(): void {
		$config  = $this->load_json( 'bin/hero-config.json' );
		$missing = array();
		foreach ( $config as $hero_slug => $entry ) {
			if ( ! is_array( $entry ) ) {
				continue;
			}
			$op_id = (string) ( $entry['operationId'] ?? '' );
			if ( $op_id === '' ) {
				continue; // Hand-written heroes may omit operationId.
			}
			if ( ! in_array( $op_id, self::$live_ops, true ) ) {
				$missing[] = $hero_slug . ' → ' . $op_id;
			}

			// Dispatch blocks are nested under `dispatch.<key>.operationId`.
			$dispatch = isset( $entry['dispatch'] ) && is_array( $entry['dispatch'] ) ? $entry['dispatch'] : array();
			foreach ( $dispatch as $key => $sub ) {
				if ( is_array( $sub ) && isset( $sub['operationId'] ) ) {
					$sub_id = (string) $sub['operationId'];
					if ( $sub_id !== '' && ! in_array( $sub_id, self::$live_ops, true ) ) {
						$missing[] = $hero_slug . '.dispatch.' . $key . ' → ' . $sub_id;
					}
				}
			}
		}
		$this->assertEmpty(
			$missing,
			'bin/hero-config.json references operationId(s) not in the live spec: ' . implode( '; ', $missing )
		);
	}

	public function test_ttl_map_keys_all_exist(): void {
		$ttl = $this->load_json( 'bin/ttl-map.json' );
		$this->assertNotEmpty( $ttl );

		$keys    = array_filter(
			array_keys( $ttl ),
			static function ( $key ) {
				// Skip leading-underscore meta keys (e.g. `_comment`).
				return is_string( $key ) && $key !== '' && $key[0] !== '_';
			}
		);
		$missing = array_diff( $keys, self::$live_ops );
		$this->assertEmpty(
			$missing,
			'bin/ttl-map.json references operationId(s) not in the live spec: ' . implode( ', ', (array) $missing )
		);
	}

	public function test_example_overrides_per_op_keys_all_exist(): void {
		$overrides = $this->load_json( 'bin/example-overrides.json' );
		$missing   = array();
		foreach ( $overrides as $key => $value ) {
			// Skip meta keys like `_global` / `_notes`.
			if ( is_string( $key ) && $key !== '' && $key[0] === '_' ) {
				continue;
			}
			if ( ! in_array( (string) $key, self::$live_ops, true ) ) {
				$missing[] = (string) $key;
			}
		}
		$this->assertEmpty(
			$missing,
			'bin/example-overrides.json references operationId(s) not in the live spec: ' . implode( ', ', $missing )
		);
	}

	public function test_hero_config_and_hero_endpoints_in_sync(): void {
		// hero-endpoints.json is the long-tail skip-list (operationIds the
		// generator must NOT emit because a hero owns them). hero-config.json
		// declares the heroes themselves. Their operationId sets must match —
		// otherwise either a hero would shadow a generated entry, or a
		// generated entry would shadow a hero.
		$config = $this->load_json( 'bin/hero-config.json' );
		$skips  = $this->load_json( 'bin/hero-endpoints.json' );

		$config_ops = array();
		foreach ( $config as $entry ) {
			if ( ! is_array( $entry ) ) {
				continue;
			}
			if ( isset( $entry['operationId'] ) ) {
				$config_ops[] = (string) $entry['operationId'];
			}
			$dispatch = isset( $entry['dispatch'] ) && is_array( $entry['dispatch'] ) ? $entry['dispatch'] : array();
			foreach ( $dispatch as $sub ) {
				if ( is_array( $sub ) && isset( $sub['operationId'] ) ) {
					$config_ops[] = (string) $sub['operationId'];
				}
			}
		}
		$config_ops = array_unique( array_filter( $config_ops ) );
		$skips      = array_unique( $skips );

		// Hand-written `getDailyHoroscope` is in hero-endpoints.json (so the
		// generator skips it) but its hero-config entry is `hand_written: true`.
		// The contract: every entry in hero-endpoints must appear in hero-config.
		$missing_in_config = array_diff( $skips, $config_ops );
		$this->assertEmpty(
			$missing_in_config,
			'hero-endpoints.json lists operationId(s) that hero-config.json does not declare: ' . implode( ', ', $missing_in_config )
		);
	}
}
