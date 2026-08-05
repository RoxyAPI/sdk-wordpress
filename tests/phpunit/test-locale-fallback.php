<?php
/**
 * A regional locale must resolve to the catalogue shipped for its language.
 *
 * WordPress matches translation files by EXACT locale, so a site set to `es_AR` looks for
 * `roxyapi-es_AR.mo`, finds none, and renders English even though `roxyapi-es_ES.mo` is sitting
 * in the same folder. WordPress offers dozens of regional Spanish, Portuguese, German and French
 * locales, so the alternative to this filter is copying one catalogue per variant and copying it
 * again every time a locale is added upstream.
 *
 * Verified against a real install before this test existed: `es_AR` rendered the form entirely in
 * English with the Spanish catalogue present.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Support\LocaleFallback;

class Test_Locale_Fallback extends \WP_UnitTestCase {

	private function languages_dir(): string {
		return dirname( ROXYAPI_PLUGIN_FILE ) . '/languages';
	}

	private function path_for( string $locale ): string {
		return $this->languages_dir() . '/roxyapi-' . $locale . '.mo';
	}

	public function test_the_shipped_catalogues_are_present(): void {
		// Non-vacuity: with no shipped files every assertion below would pass trivially.
		$found = glob( $this->languages_dir() . '/roxyapi-*.mo' );
		$this->assertNotEmpty( $found, 'Expected shipped .mo catalogues in languages/.' );
		$this->assertFileExists( $this->path_for( 'es_ES' ) );
	}

	public function test_a_regional_locale_falls_back_to_its_language(): void {
		// es_AR is the locale that exposed this: a real customer site.
		$requested = $this->path_for( 'es_AR' );
		$resolved  = LocaleFallback::fallback( $requested, 'roxyapi' );

		$this->assertNotSame( $requested, $resolved, 'es_AR must not be left unresolved.' );
		$this->assertStringContainsString( 'roxyapi-es_', $resolved );
		$this->assertFileExists( $resolved );
	}

	public function test_every_language_we_ship_covers_its_variants(): void {
		$cases = array(
			'es_MX' => 'es_',
			'es_VE' => 'es_',
			'pt_PT' => 'pt_',
			'de_AT' => 'de_',
			'de_CH' => 'de_',
			'fr_CA' => 'fr_',
			'fr_BE' => 'fr_',
		);
		foreach ( $cases as $locale => $expected_prefix ) {
			$resolved = LocaleFallback::fallback( $this->path_for( $locale ), 'roxyapi' );
			$this->assertStringContainsString(
				'roxyapi-' . $expected_prefix,
				$resolved,
				"{$locale} should resolve to a {$expected_prefix}* catalogue."
			);
		}
	}

	public function test_an_exact_match_is_returned_untouched(): void {
		// The filter must be inert whenever the requested file actually exists, so a wp.org
		// language pack for the exact locale always wins over our shipped fallback.
		$exact = $this->path_for( 'es_ES' );
		$this->assertSame( $exact, LocaleFallback::fallback( $exact, 'roxyapi' ) );
	}

	public function test_other_text_domains_are_never_touched(): void {
		$foreign = WP_LANG_DIR . '/plugins/some-other-plugin-es_AR.mo';
		$this->assertSame( $foreign, LocaleFallback::fallback( $foreign, 'some-other-plugin' ) );
	}

	public function test_an_unsupported_language_is_left_alone(): void {
		// Japanese is not one of the eight; the path must pass through so WordPress falls back
		// to English rather than being handed an unrelated catalogue.
		$ja = $this->path_for( 'ja' );
		$this->assertSame( $ja, LocaleFallback::fallback( $ja, 'roxyapi' ) );
	}
}
