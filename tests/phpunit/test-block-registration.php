<?php
/**
 * Tests that the whole block catalog registers, not just the hero blocks.
 *
 * The spec-generated long-tail blocks ship flat under build/blocks/<name>/ and
 * register through a one-level glob in RoxyAPI\Blocks\Registrar. This guards the
 * regression where the nested build/blocks/generated/<name>/ layout left every
 * long-tail block unregistered while only the hero blocks worked. Skipped when
 * the plugin has not been built, since registration reads the build output;
 * bin/check-block-layout.mjs covers the flat layout in the lint job.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

class Test_Block_Registration extends \WP_UnitTestCase {

	private static function blocks_dir(): string {
		return dirname( ROXYAPI_PLUGIN_FILE ) . '/build/blocks';
	}

	public function setUp(): void {
		parent::setUp();
		if ( ! is_dir( self::blocks_dir() ) ) {
			$this->markTestSkipped( 'Plugin not built; run `npm run build:all` before phpunit to exercise block registration.' );
		}
	}

	public function test_long_tail_blocks_register_not_just_heroes(): void {
		$all = \WP_Block_Type_Registry::get_instance()->get_all_registered();
		$roxy = array_filter(
			array_keys( $all ),
			static function ( $name ) {
				return strpos( $name, 'roxyapi/' ) === 0;
			}
		);
		$this->assertGreaterThan(
			100,
			count( $roxy ),
			'Expected the full long-tail block catalog to register from the manifest, not just the hero blocks.'
		);
	}

	public function test_a_generated_long_tail_block_registers(): void {
		$this->assertTrue(
			\WP_Block_Type_Registry::get_instance()->is_registered( 'roxyapi/generate-bodygraph' ),
			'The spec-generated Human Design bodygraph block must register from the metadata collection.'
		);
	}

	public function test_a_hero_block_still_registers(): void {
		$this->assertTrue(
			\WP_Block_Type_Registry::get_instance()->is_registered( 'roxyapi/horoscope' ),
			'The hand-written hero horoscope block must remain registered.'
		);
	}
}
