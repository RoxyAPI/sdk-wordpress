<?php
/**
 * The block inspector labels a translated site actually sees.
 *
 * Every generated block draws its sidebar from a field list the generator emits
 * into the block's own index.js. `label` and `help` are the whole of what that
 * sidebar says, and they were emitted as bare string literals: nothing in them
 * looked like a translatable string to `wp i18n make-pot`, so they never entered
 * the POT and no catalogue, hand-written or from wordpress.org, could reach
 * them. A Spanish admin read Spanish everywhere on the screen except the panel
 * they were editing.
 *
 * Three things have to hold together or the labels stay English, and each fails
 * silently on its own:
 *
 *   1. the generator wraps them in `__()`, so the extractor can see them;
 *   2. `npm run i18n:json` turns the PO files into the Jed catalogue WordPress
 *      loads, named after the md5 of the SCRIPT path, not the source path;
 *   3. `wp_set_script_translations()` is told where those catalogues live,
 *      because core looks in `WP_LANG_DIR/plugins` otherwise and a bundled
 *      catalogue is not there.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

class Test_Editor_Translations extends \WP_UnitTestCase {

	/** A generated block with several inspector fields, used as the worked example. */
	private const SAMPLE_SLUG = 'get-weekly-horoscope';

	private static function root(): string {
		return dirname( __DIR__, 2 );
	}

	public function test_generated_inspector_labels_are_wrapped_for_translation(): void {
		$source = (string) file_get_contents(
			self::root() . '/blocks/generated/' . self::SAMPLE_SLUG . '/index.js'
		);

		$this->assertStringContainsString(
			"import { __ } from '@wordpress/i18n';",
			$source,
			'A generated editor that has labels must import the translate function.'
		);
		$this->assertMatchesRegularExpression(
			"/label: __\( '[^']+', 'roxyapi' \)/",
			$source,
			'Inspector labels must be __() calls, not bare literals the extractor cannot see.'
		);
		$this->assertMatchesRegularExpression(
			"/help: __\( '[^']+', 'roxyapi' \)/",
			$source,
			'Inspector help text must be __() calls too.'
		);
	}

	public function test_no_generated_editor_leaves_a_bare_label(): void {
		$bare = array();
		foreach ( (array) glob( self::root() . '/blocks/generated/*/index.js' ) as $path ) {
			$source = (string) file_get_contents( (string) $path );
			if ( preg_match( "/(label|help): '/", $source ) === 1 ) {
				$bare[] = basename( dirname( (string) $path ) );
			}
		}
		$this->assertSame(
			array(),
			array_slice( $bare, 0, 10 ),
			'Every generated block inspector string must go through __().'
		);
	}

	public function test_the_labels_reach_the_translation_template(): void {
		$pot = (string) file_get_contents( self::root() . '/languages/roxyapi.pot' );

		$this->assertStringContainsString(
			'#: blocks/generated/' . self::SAMPLE_SLUG . '/index.js',
			$pot,
			'make-pot must now see the generated editor scripts as a source of strings.'
		);
		$this->assertStringContainsString(
			'msgid "Any date inside the target week, in YYYY-MM-DD format"',
			$pot,
			'An inspector help string must be extractable into the POT.'
		);
	}

	/**
	 * The catalogue file name is the contract. Core hashes the script path
	 * RELATIVE to the plugin, which is the built bundle, while the PO files
	 * reference the editor source; `bin/build-i18n-map.mjs` is what bridges the
	 * two. Assert the exact file core will ask for, rather than that some JSON
	 * exists, because a catalogue under any other name is never read and the
	 * screen just stays English.
	 */
	public function test_the_jed_catalogue_is_named_the_way_core_looks_it_up(): void {
		$languages = self::root() . '/languages';
		if ( glob( $languages . '/roxyapi-es_ES-*.json' ) === array() ) {
			$this->markTestSkipped( 'Plugin not built; run `npm run build:all` to generate the script catalogues.' );
		}

		$expected = sprintf(
			'%s/roxyapi-es_ES-%s.json',
			$languages,
			md5( 'build/blocks/' . self::SAMPLE_SLUG . '/index.js' )
		);
		$this->assertFileExists(
			$expected,
			'The Jed catalogue must be named after the md5 of the BUILT bundle path, or core never loads it.'
		);

		$catalogue = json_decode( (string) file_get_contents( $expected ), true );
		$messages  = $catalogue['locale_data']['messages'] ?? array();

		$this->assertArrayHasKey(
			'Date',
			$messages,
			'An inspector label already carried by the Spanish catalogue must land in the block bundle.'
		);
		$this->assertSame(
			array( 'Fecha' ),
			$messages['Date'],
			'The Spanish translation must travel with the catalogue, not just the msgid.'
		);
		$this->assertArrayHasKey(
			'Reading Settings',
			$messages,
			'Strings from the shared editor are bundled into every block, so they belong in every catalogue.'
		);
	}

	/**
	 * Without the path argument core looks only in `WP_LANG_DIR/plugins`, which
	 * holds what wordpress.org built and never what the plugin ships beside its
	 * MO files.
	 */
	public function test_editor_scripts_are_pointed_at_the_bundled_catalogues(): void {
		if ( ! is_dir( dirname( ROXYAPI_PLUGIN_FILE ) . '/build/blocks' ) ) {
			$this->markTestSkipped( 'Plugin not built; run `npm run build:all` before phpunit.' );
		}

		$block = \WP_Block_Type_Registry::get_instance()->get_registered( 'roxyapi/' . self::SAMPLE_SLUG );
		$this->assertNotNull( $block, 'The sample block must be registered.' );

		$scripts  = wp_scripts();
		$expected = untrailingslashit( plugin_dir_path( ROXYAPI_PLUGIN_FILE ) . 'languages' );
		foreach ( $block->editor_script_handles as $handle ) {
			$registered = $scripts->registered[ $handle ] ?? null;
			$this->assertNotNull( $registered, "Editor script {$handle} must be registered." );
			$this->assertSame(
				$expected,
				untrailingslashit( (string) $registered->translations_path ),
				'Editor scripts must be pointed at the plugin languages directory.'
			);
		}
	}
}
