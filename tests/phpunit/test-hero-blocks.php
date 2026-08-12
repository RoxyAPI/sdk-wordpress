<?php
/**
 * The five headline blocks are configurable, and their names never move.
 *
 * `natal-chart`, `numerology`, `tarot`, `biorhythm` and `angel-number` shipped
 * as hand-written placeholders: `"attributes": {}` and a static editor with
 * nowhere to type a birth date, while the ~147 generated long-tail blocks had
 * spec-derived controls and a live preview. Nothing regenerated the five, so
 * the quality gradient ran backwards and stayed there. They are described by
 * `block` in bin/hero-config.json now and emitted by the same three emitters as
 * the long tail.
 *
 * The names are the part that cannot be got wrong. A saved post references a
 * block BY NAME, so `roxyapi/natal-chart` staying `roxyapi/natal-chart` is what
 * keeps every page a customer has already built rendering. The list below is a
 * second opinion on purpose: blocks/generated is deleted and rewritten on every
 * `npm run generate`, so without it a slip in the generator or the config would
 * rename a block and nothing would say so.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

class Test_Hero_Blocks extends \WP_UnitTestCase {

	/**
	 * Block name => the hero class its render.php must delegate to.
	 *
	 * Copied from what shipped, never derived, so a renamed hero key or
	 * operationId cannot quietly carry the block name along with it.
	 */
	private const HERO_BLOCKS = array(
		'roxyapi/natal-chart'  => 'NatalChart',
		'roxyapi/numerology'   => 'Numerology',
		'roxyapi/tarot'        => 'TarotCard',
		'roxyapi/biorhythm'    => 'Biorhythm',
		'roxyapi/angel-number' => 'AngelNumber',
	);

	private static function blocks_dir(): string {
		return dirname( __DIR__, 2 ) . '/blocks/generated';
	}

	/**
	 * Decoded block.json for a registered block name.
	 *
	 * @param string $name Block name.
	 * @return array<string, mixed>
	 */
	private function block_json( string $name ): array {
		$slug = substr( $name, strlen( 'roxyapi/' ) );
		$path = self::blocks_dir() . "/{$slug}/block.json";
		$this->assertFileExists( $path, "{$name} must be emitted at blocks/generated/{$slug}/." );
		$decoded = json_decode( (string) file_get_contents( $path ), true );
		$this->assertIsArray( $decoded );
		return $decoded;
	}

	/**
	 * @return array<string, array<int, string>>
	 */
	public function hero_blocks(): array {
		$cases = array();
		foreach ( self::HERO_BLOCKS as $name => $class ) {
			$cases[ $name ] = array( $name, $class );
		}
		return $cases;
	}

	/**
	 * @dataProvider hero_blocks
	 * @param string $name  Registered block name.
	 * @param string $class Hero class the block renders through.
	 */
	public function test_the_shipped_block_name_is_unchanged( string $name, string $class ): void {
		$this->assertSame(
			$name,
			$this->block_json( $name )['name'] ?? '',
			"Renaming {$name} turns every saved post that uses it into invalid content."
		);
	}

	/**
	 * @dataProvider hero_blocks
	 * @param string $name  Registered block name.
	 * @param string $class Hero class the block renders through.
	 */
	public function test_the_block_declares_the_inputs_its_reading_needs( string $name, string $class ): void {
		$attributes = $this->block_json( $name )['attributes'] ?? array();
		$this->assertNotEmpty(
			$attributes,
			"{$name} shipped with an empty attribute map, which is what left it inert."
		);

		$hero = '\\RoxyAPI\\Generated\\Heroes\\' . $class;
		$this->assertTrue( class_exists( $hero ) );
		// `mode` and the two reserved display attributes are shortcode-only
		// controls, not reading inputs, so the block does not expose them: a
		// block placement follows the site setting and a shortcode can override
		// it. Both reserved names sit here for the same reason and must move
		// together, because exposing one in the editor and not the other is the
		// asymmetry `hide_sections` was made per-placement to remove.
		$reading_inputs = array_values(
			array_diff(
				array_keys( $hero::DEFAULTS ),
				array( 'mode', 'hide_readings', 'hide_sections' )
			)
		);
		sort( $reading_inputs );
		$declared = array_keys( $attributes );
		sort( $declared );

		$this->assertSame(
			$reading_inputs,
			$declared,
			"{$name} must expose exactly the inputs its shortcode reads, or the editor collects a value nothing sends."
		);
	}

	/**
	 * @dataProvider hero_blocks
	 * @param string $name  Registered block name.
	 * @param string $class Hero class the block renders through.
	 */
	public function test_the_editor_gets_a_control_for_every_attribute( string $name, string $class ): void {
		$slug   = substr( $name, strlen( 'roxyapi/' ) );
		$source = (string) file_get_contents( self::blocks_dir() . "/{$slug}/index.js" );

		$this->assertStringContainsString(
			"import { makeEdit } from '../../_shared/generated-edit'",
			$source,
			"{$name} must use the one shared editor, not an editor of its own."
		);

		foreach ( array_keys( $this->block_json( $name )['attributes'] ?? array() ) as $attribute ) {
			$this->assertStringContainsString(
				"name: '{$attribute}'",
				$source,
				"{$name} declares the attribute {$attribute} with no sidebar control."
			);
		}
	}

	/**
	 * @dataProvider hero_blocks
	 * @param string $name  Registered block name.
	 * @param string $class Hero class the block renders through.
	 */
	public function test_the_block_renders_through_its_hero( string $name, string $class ): void {
		$slug   = substr( $name, strlen( 'roxyapi/' ) );
		$render = (string) file_get_contents( self::blocks_dir() . "/{$slug}/render.php" );
		$this->assertStringContainsString( "Heroes\\{$class}::render", $render );
		$this->assertStringContainsString(
			'BlockOutput::to_shortcode_atts',
			$render,
			'Block attributes reach the shortcode through the bridge, same as every other block.'
		);
	}

	/**
	 * @dataProvider hero_blocks
	 * @param string $name  Registered block name.
	 * @param string $class Hero class the block renders through.
	 */
	public function test_the_block_is_offered_in_the_inserter( string $name, string $class ): void {
		$this->assertTrue(
			$this->block_json( $name )['supports']['inserter'] ?? true,
			"{$name} is a headline reading and must stay one click away."
		);
	}

	/**
	 * The registry is what the editor actually reads, and it is built from the
	 * BUILT output rather than the source checked above.
	 *
	 * @dataProvider hero_blocks
	 * @param string $name  Registered block name.
	 * @param string $class Hero class the block renders through.
	 */
	public function test_the_block_registers_under_its_shipped_name( string $name, string $class ): void {
		if ( ! is_dir( dirname( ROXYAPI_PLUGIN_FILE ) . '/build/blocks' ) ) {
			$this->markTestSkipped( 'Plugin not built; run `npm run build:all` before phpunit.' );
		}
		$block = \WP_Block_Type_Registry::get_instance()->get_registered( $name );
		$this->assertNotNull( $block, "{$name} must still register." );
		$this->assertNotEmpty(
			$block->editor_script_handles,
			"{$name} must ship an editor script, or its inputs are unreachable in the editor."
		);
		$this->assertNotEmpty( $block->attributes );
	}

	/**
	 * An empty attribute must NOT reach the shortcode, or the hero's own
	 * default is overwritten with nothing. `tz` and `spread` are the two that
	 * carry a real default, and both would be lost.
	 */
	public function test_an_unset_attribute_leaves_the_reading_default_alone(): void {
		$atts = \RoxyAPI\Support\BlockOutput::to_shortcode_atts(
			array(
				'birth_date' => '1990-05-15',
				'tz'         => '',
			)
		);

		$this->assertSame( array( 'birth_date' => '1990-05-15' ), $atts );
		$this->assertSame(
			'UTC',
			shortcode_atts( \RoxyAPI\Generated\Heroes\NatalChart::DEFAULTS, $atts )['tz'],
			'An untouched timezone field must fall through to the shortcode default.'
		);
	}
}
