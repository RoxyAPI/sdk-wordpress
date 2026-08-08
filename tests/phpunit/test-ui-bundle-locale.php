<?php
/**
 * The chart labels must arrive in the site language, and only when we ship them.
 *
 * The reading itself comes back translated inside the API response, but the labels the components
 * draw around it live in the bundle in English and are replaced by a catalogue loaded beside it.
 * Without that file a Spanish site renders Spanish planet and sign names under English headings,
 * which is the half-translated result the whole translation effort exists to remove.
 *
 * Measured on WordPress 7.0.2 before this test existed: on a block theme the shortcode renders
 * while `did_action( 'wp_enqueue_scripts' )` is still 0, so neither handle is registered at the
 * moment {@link UiBundle::enqueue} runs. That is why the enqueue must not ask whether the handle
 * is registered. Guarding on registration passed in wp-cli, where the order is reversed, and
 * shipped nothing on every real front-end page.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Support\Language;
use RoxyAPI\Support\UiBundle;

class Test_Ui_Bundle_Locale extends \WP_UnitTestCase {

	/** @var string[] */
	private array $vendored = array();

	public function setUp(): void {
		parent::setUp();
		$found          = glob( dirname( ROXYAPI_PLUGIN_FILE ) . '/assets/js/locales/*.js' );
		$this->vendored = array_map(
			static function ( $path ) {
				return basename( $path, '.js' );
			},
			is_array( $found ) ? $found : array()
		);
	}

	private function enqueue_for( string $locale ): void {
		wp_dequeue_script( UiBundle::HANDLE );
		wp_dequeue_script( UiBundle::LOCALE_HANDLE );
		wp_deregister_script( UiBundle::LOCALE_HANDLE );

		$switch = static function () use ( $locale ) {
			return $locale;
		};
		add_filter( 'determine_locale', $switch );
		// Deliberately enqueued BEFORE registration: that is the order a real front-end render
		// produces, and the order this used to get wrong.
		UiBundle::enqueue();
		UiBundle::register_scripts();
		remove_filter( 'determine_locale', $switch );
	}

	public function test_the_catalogues_we_expect_are_vendored(): void {
		// Non-vacuity. Every per-language assertion below is skipped when nothing is vendored,
		// so without this the suite would go green on a build that ships no catalogue at all.
		$this->assertNotEmpty(
			$this->vendored,
			'Expected vendored catalogues in assets/js/locales. Run `npm run fetch:ui`.'
		);
		$this->assertNotContains( 'en', $this->vendored, 'English ships no catalogue by design.' );
	}

	public function test_every_vendored_language_is_enqueued_after_the_bundle(): void {
		foreach ( $this->vendored as $lang ) {
			$this->enqueue_for( $lang );

			$this->assertTrue(
				wp_script_is( UiBundle::LOCALE_HANDLE, 'enqueued' ),
				"A {$lang} site must enqueue its catalogue."
			);
			$script = wp_scripts()->registered[ UiBundle::LOCALE_HANDLE ];
			$this->assertStringEndsWith( "/assets/js/locales/{$lang}.js", $script->src );
			$this->assertSame(
				array( UiBundle::HANDLE ),
				$script->deps,
				'The catalogue must depend on the bundle so WordPress prints it second.'
			);
		}
	}

	public function test_a_regional_locale_uses_its_language_catalogue(): void {
		if ( ! in_array( 'es', $this->vendored, true ) ) {
			$this->markTestSkipped( 'No Spanish catalogue vendored.' );
		}
		// es_AR is the reporting customer's locale, and the one WordPress writes into <html lang>.
		$this->enqueue_for( 'es_AR' );
		$this->assertTrue( wp_script_is( UiBundle::LOCALE_HANDLE, 'enqueued' ) );
		$this->assertStringEndsWith(
			'/assets/js/locales/es.js',
			wp_scripts()->registered[ UiBundle::LOCALE_HANDLE ]->src
		);
	}

	public function test_an_english_site_loads_no_catalogue(): void {
		$this->enqueue_for( 'en_US' );
		$this->assertTrue( wp_script_is( UiBundle::HANDLE, 'enqueued' ), 'The bundle still loads.' );
		$this->assertFalse( wp_script_is( UiBundle::LOCALE_HANDLE, 'registered' ) );
		$this->assertFalse( wp_script_is( UiBundle::LOCALE_HANDLE, 'enqueued' ) );
	}

	public function test_a_language_we_do_not_ship_degrades_to_english(): void {
		// Japanese is neither a supported API language nor a vendored catalogue. Nothing may be
		// registered, because a registered handle with no file is a 404 on a visitor page.
		$this->enqueue_for( 'ja' );
		$this->assertTrue( wp_script_is( UiBundle::HANDLE, 'enqueued' ) );
		$this->assertFalse( wp_script_is( UiBundle::LOCALE_HANDLE, 'enqueued' ) );
	}

	public function test_the_language_helper_is_the_only_truncation(): void {
		// UiBundle and the API request must narrow a locale the same way, or the labels and the
		// reading they sit around can disagree.
		$this->assertSame( 'es', Language::from_locale( 'es_AR' ) );
		$this->assertSame( 'pt', Language::from_locale( 'pt_BR' ) );
		$this->assertSame( 'en', Language::from_locale( 'en_US' ) );
		$this->assertSame( '', Language::from_locale( 'ja' ) );
		$this->assertSame( '', Language::from_locale( '' ) );
	}
}
