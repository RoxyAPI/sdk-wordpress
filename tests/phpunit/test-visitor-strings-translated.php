<?php
/**
 * Every string a VISITOR reads at runtime must be translated in all seven shipped catalogues.
 *
 * Extractability is not translation. A string can be correctly wrapped in `__()`, appear in
 * the POT, and still render English on every site, because a catalogue that carries no entry
 * for it falls back to the msgid without erroring. The visible result is one paragraph of
 * Spanish under an English button, which reads as a broken plugin rather than as a missing
 * translation, and nothing in the product reports it.
 *
 * That is what happened to the month paging on the ephemeris: the three controls shipped
 * wrapped and extractable in 1.11.0, the catalogues predated the feature, and Previous month,
 * Next month and Show month rendered English on a fully translated site.
 *
 * Scope is the hand-written runtime files a visitor reads, listed below rather than globbed,
 * because the criterion is who reads the string and no directory expresses that. The admin
 * screens and the block editor are deliberately OUT: both are read by whoever builds the
 * site, who chose the plugin in English, and holding them to this bar would fail the suite
 * over work that changes nothing a visitor sees.
 *
 * The source is scanned rather than the runtime called, for the same reason the generated
 * form spec test does it: by the time `__()` returns, a translated and an untranslated build
 * are the same string.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

class Test_Visitor_Strings_Translated extends \WP_UnitTestCase {

	/**
	 * Files whose gettext calls produce text a site VISITOR reads.
	 *
	 * Generated form classes are covered by their own test and move with the spec, so they
	 * are not repeated here. Add a file when it starts rendering prose on the front end.
	 */
	private const VISITOR_RUNTIME_FILES = array(
		'src/Support/EphemerisNav.php',
		'src/Support/FormRouter.php',
		'src/Support/FormRenderer.php',
		'src/Support/Attribution.php',
		'src/Support/Disclaimer.php',
		'src/Shortcodes/Horoscope.php',
		'templates/horoscope-form.php',
	);

	/** Locales the plugin ships a catalogue for. */
	private const LOCALES = array( 'de_DE', 'es_ES', 'fr_FR', 'hi_IN', 'pt_BR', 'ru_RU', 'tr_TR' );

	/** @return string Absolute path to the plugin root. */
	private function root(): string {
		return dirname( ROXYAPI_PLUGIN_FILE );
	}

	/**
	 * Single-quoted literals passed to a gettext function against our text domain.
	 *
	 * @return array<string, string> msgid => the file it came from.
	 */
	private function visitor_msgids(): array {
		$found = array();
		foreach ( self::VISITOR_RUNTIME_FILES as $relative ) {
			$path = $this->root() . '/' . $relative;
			if ( ! is_readable( $path ) ) {
				continue;
			}
			preg_match_all(
				"/(?:esc_html__|esc_attr__|__)\(\s*'((?:[^'\\\\]|\\\\.)*)'\s*,\s*'roxyapi'\s*\)/",
				(string) file_get_contents( $path ),
				$matches
			);
			foreach ( $matches[1] as $literal ) {
				$msgid = str_replace( array( "\\'", '\\\\' ), array( "'", '\\' ), $literal );
				if ( $msgid !== '' ) {
					$found[ $msgid ] = $relative;
				}
			}
		}
		return $found;
	}

	/**
	 * msgid => msgstr for one catalogue. Only context-free single-line entries, which is
	 * every entry these files produce.
	 *
	 * @param string $locale Locale code.
	 * @return array<string, string>
	 */
	private function catalogue( string $locale ): array {
		$path = $this->root() . '/languages/roxyapi-' . $locale . '.po';
		if ( ! is_readable( $path ) ) {
			return array();
		}
		preg_match_all(
			'/^msgid "((?:[^"\\\\]|\\\\.)*)"\nmsgstr "((?:[^"\\\\]|\\\\.)*)"$/m',
			(string) file_get_contents( $path ),
			$matches,
			PREG_SET_ORDER
		);
		$out = array();
		foreach ( $matches as $entry ) {
			$out[ str_replace( '\\"', '"', $entry[1] ) ] = $entry[2];
		}
		return $out;
	}

	public function test_the_scan_finds_visitor_strings(): void {
		// Non-vacuity. A regex that matches nothing would make every assertion below pass.
		$this->assertGreaterThan(
			15,
			count( $this->visitor_msgids() ),
			'Expected the visitor runtime files to yield gettext literals; a pass over zero strings proves nothing.'
		);
	}

	public function test_every_locale_ships_a_catalogue(): void {
		foreach ( self::LOCALES as $locale ) {
			$this->assertNotEmpty(
				$this->catalogue( $locale ),
				"languages/roxyapi-{$locale}.po parsed to nothing, so its assertions below would be vacuous."
			);
		}
	}

	public function test_every_visitor_string_is_translated_in_every_catalogue(): void {
		$msgids   = $this->visitor_msgids();
		$missing  = array();

		foreach ( self::LOCALES as $locale ) {
			$catalogue = $this->catalogue( $locale );
			foreach ( $msgids as $msgid => $source ) {
				if ( trim( (string) ( $catalogue[ $msgid ] ?? '' ) ) === '' ) {
					$missing[] = sprintf( '%s: "%s" (from %s)', $locale, $msgid, $source );
				}
			}
		}

		$this->assertSame(
			array(),
			$missing,
			"These strings render to a visitor but no catalogue translates them, so they come out English on a translated site:\n"
				. implode( "\n", $missing )
		);
	}
}
