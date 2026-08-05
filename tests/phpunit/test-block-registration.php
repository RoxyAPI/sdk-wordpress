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

	public function test_generated_long_tail_blocks_carry_an_editor_script(): void {
		$block = \WP_Block_Type_Registry::get_instance()->get_registered( 'roxyapi/calculate-expression' );
		$this->assertNotNull( $block, 'The generated block must be registered.' );
		$this->assertNotEmpty(
			$block->editor_script_handles,
			'A generated long-tail block must ship an editorScript so its inputs are editable in the block editor, not just render read-only.'
		);
	}

	/**
	 * Editor panels are built in JavaScript and translated by `@wordpress/i18n`, which only
	 * resolves once the script HANDLE is bound to the text domain. Declaring `wp-i18n` as a
	 * script dependency is not enough on its own, and the failure is silent: the editor panel
	 * stays English while every PHP string on the same screen translates correctly.
	 */
	public function test_every_block_editor_script_is_bound_to_the_text_domain(): void {
		$blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();
		$ours   = array_filter(
			$blocks,
			static fn( $name ) => strpos( (string) $name, 'roxyapi/' ) === 0,
			ARRAY_FILTER_USE_KEY
		);
		// Non-vacuity: an empty set would make the loop below assert nothing.
		$this->assertGreaterThan( 100, count( $ours ), 'Expected the block catalog to be registered.' );

		$scripts = wp_scripts();
		$unbound = array();
		foreach ( $ours as $name => $block ) {
			foreach ( $block->editor_script_handles as $handle ) {
				$registered = isset( $scripts->registered[ $handle ] ) ? $scripts->registered[ $handle ] : null;
				if ( ! $registered || empty( $registered->textdomain ) ) {
					$unbound[] = $name;
				}
			}
		}

		$this->assertSame(
			array(),
			array_slice( $unbound, 0, 10 ),
			'Every block editor script must be bound with wp_set_script_translations( $handle, "roxyapi" ) in Blocks\\Registrar.'
		);
	}
}
