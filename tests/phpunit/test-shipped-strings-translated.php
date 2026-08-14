<?php
/**
 * Every hand-written string this plugin shows a human must be translated in all seven catalogues.
 *
 * Extractability is not translation. A string can be correctly wrapped in `__()`, appear in the
 * POT, and still render English on every site, because a catalogue that carries no entry for it
 * falls back to the msgid without erroring. The visible result is one paragraph of Spanish under
 * an English button, which reads as a broken plugin rather than as a missing translation, and
 * nothing in the product reports it.
 *
 * That is what happened to the month paging on the ephemeris, and the guard written for it then
 * curated a list of seven visitor files. The list was the defect: `src/Api/Client.php` renders
 * "This reading is temporarily unavailable" straight onto the page and was never on it, so twelve
 * more strings sat English in all seven locales behind a passing test. A curated list is only ever
 * as complete as the last person to remember it.
 *
 * So scope is DERIVED. Every `.php` under `src/`, `templates/` and the plugin root is in, and the
 * only exemptions are the two directories below. Both are exempt for what the string IS, never for
 * who reads it: their text is generated from the OpenAPI spec, which is the source of truth for it,
 * and re-stating spec prose in seven catalogues would fork it. A new admin screen, a new REST
 * route, a new template is covered the day it is added, with nothing to remember.
 *
 * The trade is real and deliberate: adding a user-facing string now obliges you to translate it
 * before the suite passes. That obligation is the entire point. Coverage that depends on goodwill
 * decays, and this one already did.
 *
 * The source is scanned rather than the runtime called, for the same reason the generated form
 * spec test does it: by the time `__()` returns, a translated and an untranslated build are the
 * same string.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

class Test_Shipped_Strings_Translated extends \WP_UnitTestCase {

	/**
	 * Path fragments exempt from the bar.
	 *
	 * Each is exempt because its text is GENERATED FROM THE SPEC, not because of its audience.
	 * `src/Generated/` is rebuilt by `bin/generate.mjs` on every release and its wrapping is
	 * covered by `test-form-strings-translatable.php`; `blocks/` carries the same spec prose into
	 * the editor. Holding either to this bar would fail the suite whenever an endpoint description
	 * is reworded upstream, which is not a defect in this repository.
	 *
	 * Adding anything here needs a reason of that kind. "It is only the admin" is not one.
	 */
	private const EXEMPT = array( 'src/Generated/', 'blocks/' );

	/** Locales the plugin ships a catalogue for. */
	private const LOCALES = array( 'de_DE', 'es_ES', 'fr_FR', 'hi_IN', 'pt_BR', 'ru_RU', 'tr_TR' );

	/** @return string Absolute path to the plugin root. */
	private function root(): string {
		return dirname( ROXYAPI_PLUGIN_FILE );
	}

	/**
	 * Every non-exempt PHP file that can hold a gettext call, relative to the plugin root.
	 *
	 * @return string[]
	 */
	private function scanned_files(): array {
		$found = array();

		foreach ( array( 'src', 'templates' ) as $dir ) {
			$base = $this->root() . '/' . $dir;
			if ( ! is_dir( $base ) ) {
				continue;
			}
			$walk = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $base, \FilesystemIterator::SKIP_DOTS ) );
			foreach ( $walk as $file ) {
				if ( $file->isFile() && $file->getExtension() === 'php' ) {
					$found[] = $file->getPathname();
				}
			}
		}
		foreach ( (array) glob( $this->root() . '/*.php' ) as $file ) {
			$found[] = $file;
		}

		$relative = array();
		foreach ( $found as $path ) {
			$rel = ltrim( str_replace( $this->root(), '', $path ), '/' );
			foreach ( self::EXEMPT as $exempt ) {
				if ( str_starts_with( $rel, $exempt ) ) {
					continue 2;
				}
			}
			$relative[] = $rel;
		}
		sort( $relative );

		return $relative;
	}

	/**
	 * Single-quoted literals passed to a gettext function against our text domain.
	 *
	 * Covers the four forms this plugin actually uses. `_x()` and `_nx()` are absent on purpose:
	 * nothing here takes a context, so the POT holds no `msgctxt` and the parser below need not
	 * key on one. Should a context string ever be introduced, this scan silently skips it, so add
	 * it here and to `catalogue()` together.
	 *
	 * @return array<string, string> msgid => the first file it was seen in.
	 */
	private function shipped_msgids(): array {
		$found = array();

		foreach ( $this->scanned_files() as $relative ) {
			$source = (string) file_get_contents( $this->root() . '/' . $relative );

			$patterns = array(
				// __(), esc_html__(), esc_attr__() and their one-literal shape.
				"/(?:esc_html__|esc_attr__|__)\(\s*'((?:[^'\\\\]|\\\\.)*)'\s*,\s*'roxyapi'\s*\)/" => array( 1 ),
				// _n() only ever contributes its SINGULAR as a catalogue key. The plural literal is
				// stored as `msgid_plural` under that same entry, never as a lookup key of its own,
				// so requiring it here would report a fully translated plural as missing forever.
				// Its forms are still checked: catalogue() returns every msgstr[n] and the
				// assertion below fails if any one of them is blank.
				"/\b_n\(\s*'((?:[^'\\\\]|\\\\.)*)'\s*,/"                                          => array( 1 ),
			);

			foreach ( $patterns as $pattern => $groups ) {
				preg_match_all( $pattern, $source, $matches );
				foreach ( $groups as $group ) {
					foreach ( $matches[ $group ] as $literal ) {
						$msgid = str_replace( array( "\\'", '\\\\' ), array( "'", '\\' ), $literal );
						if ( $msgid !== '' && ! isset( $found[ $msgid ] ) ) {
							$found[ $msgid ] = $relative;
						}
					}
				}
			}
		}

		return $found;
	}

	/**
	 * One catalogue as msgid => every msgstr form it carries.
	 *
	 * Parsed line by line rather than by one regex so that multi-line entries and the plural
	 * forms are read correctly; a single-line regex silently treats both as absent, which would
	 * report a translated string as missing and, worse, the reverse.
	 *
	 * @param string $locale Locale code.
	 * @return array<string, string[]>
	 */
	private function catalogue( string $locale ): array {
		$path = $this->root() . '/languages/roxyapi-' . $locale . '.po';
		if ( ! is_readable( $path ) ) {
			return array();
		}

		$out   = array();
		$id    = null;
		$forms = array();
		$mode  = null;

		$flush = static function () use ( &$out, &$id, &$forms ) {
			if ( is_string( $id ) && '' !== $id ) {
				$out[ $id ] = $forms;
			}
			$id    = null;
			$forms = array();
		};

		foreach ( explode( "\n", (string) file_get_contents( $path ) ) as $line ) {
			$line = trim( $line );

			if ( '' === $line ) {
				$flush();
				$mode = null;
				continue;
			}
			if ( '#' === $line[0] ) {
				continue;
			}
			if ( preg_match( '/^msgid\s+"(.*)"$/', $line, $matched ) ) {
				$flush();
				$id   = stripcslashes( $matched[1] );
				$mode = 'id';
				continue;
			}
			if ( str_starts_with( $line, 'msgid_plural' ) ) {
				// The plural msgid is never looked up directly; only its msgstr forms matter.
				$mode = 'ignore';
				continue;
			}
			if ( preg_match( '/^msgstr(?:\[\d+\])?\s+"(.*)"$/', $line, $matched ) ) {
				$forms[] = stripcslashes( $matched[1] );
				$mode    = 'str';
				continue;
			}
			if ( preg_match( '/^"(.*)"$/', $line, $matched ) ) {
				if ( 'id' === $mode ) {
					$id .= stripcslashes( $matched[1] );
				} elseif ( 'str' === $mode && array() !== $forms ) {
					$forms[ count( $forms ) - 1 ] .= stripcslashes( $matched[1] );
				}
			}
		}
		$flush();

		return $out;
	}

	public function test_the_scan_finds_shipped_strings(): void {
		// Non-vacuity. A regex or a walk that matches nothing would make every assertion below pass.
		$this->assertGreaterThan(
			150,
			count( $this->shipped_msgids() ),
			'Expected the scanned files to yield gettext literals; a pass over zero strings proves nothing.'
		);
	}

	public function test_the_walk_reaches_the_surfaces_a_curated_list_missed(): void {
		$sources = implode( "\n", array_values( $this->shipped_msgids() ) );

		// src/Api/ rendered visitor-facing failure text and was absent from the old curated list.
		$this->assertMatchesRegularExpression( '#^src/Api/#m', $sources, 'The walk no longer reaches src/Api/, which is the omission this guard was rewritten for.' );
		$this->assertMatchesRegularExpression( '#^src/Admin/#m', $sources, 'The walk no longer reaches src/Admin/.' );
		$this->assertMatchesRegularExpression( '#^templates/#m', $sources, 'The walk no longer reaches templates/.' );
	}

	public function test_exempt_paths_are_actually_skipped(): void {
		foreach ( $this->scanned_files() as $relative ) {
			foreach ( self::EXEMPT as $exempt ) {
				$this->assertStringStartsNotWith( $exempt, $relative );
			}
		}
	}

	public function test_every_locale_ships_a_catalogue(): void {
		foreach ( self::LOCALES as $locale ) {
			$this->assertNotEmpty(
				$this->catalogue( $locale ),
				"languages/roxyapi-{$locale}.po parsed to nothing, so its assertions below would be vacuous."
			);
		}
	}

	public function test_every_shipped_string_is_translated_in_every_catalogue(): void {
		$msgids  = $this->shipped_msgids();
		$missing = array();

		foreach ( self::LOCALES as $locale ) {
			$catalogue = $this->catalogue( $locale );

			foreach ( $msgids as $msgid => $source ) {
				$forms   = $catalogue[ $msgid ] ?? array();
				$blank   = array() === $forms;
				foreach ( $forms as $form ) {
					if ( '' === trim( $form ) ) {
						$blank = true;
					}
				}
				if ( $blank ) {
					$missing[] = sprintf( '%s: "%s" (%s)', $locale, $msgid, $source );
				}
			}
		}

		$this->assertSame(
			array(),
			$missing,
			"These strings ship to a human but no catalogue translates them, so they come out English on a translated site:\n"
				. implode( "\n", $missing )
		);
	}
}
