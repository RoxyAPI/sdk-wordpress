<?php
/**
 * The Site Health panel has to be right, because it is what a site owner sends instead of a
 * reproduction. A panel that reports a fault on a healthy site is worse than no panel.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\SiteHealth;
use RoxyAPI\Support\LocaleFallback;

class Test_Site_Health extends \WP_UnitTestCase {

	public function tear_down(): void {
		/*
		 * Building the panel calls __() on our domain, so with the locale filters below in place
		 * it can pull the Spanish catalogue into memory. WP_UnitTestCase restores hooks between
		 * tests but NEVER touches the global $l10n, so a catalogue left loaded here would
		 * translate strings in every test file running afterwards. Verified by removing the same
		 * guard from the sibling file: a following test then sees a loaded domain and fails.
		 */
		unload_textdomain( 'roxyapi' );
		parent::tear_down();
	}

	public function force_es_ar(): string {
		return 'es_AR';
	}

	public function test_the_probe_string_is_translated_in_every_shipped_catalogue(): void {
		/*
		 * The panel answers "sample string translates" with this exact string. If a catalogue
		 * ever stops carrying it the panel starts reporting a problem on a working site, and
		 * the string is generated from the API spec, so it can move without anyone noticing.
		 */
		$catalogues = glob( LocaleFallback::languages_dir() . '/roxyapi-*.po' );
		$this->assertNotEmpty( $catalogues, 'Expected shipped .po catalogues in languages/.' );

		foreach ( $catalogues as $catalogue ) {
			$lines     = explode( "\n", (string) file_get_contents( $catalogue ) );
			$translated = false;

			foreach ( $lines as $index => $line ) {
				if ( 'msgid "' . SiteHealth::PROBE_STRING . '"' !== $line ) {
					continue;
				}
				$next       = $lines[ $index + 1 ] ?? '';
				$translated = 'msgstr ""' !== $next && str_starts_with( $next, 'msgstr "' );
				break;
			}

			$this->assertTrue(
				$translated,
				basename( $catalogue ) . ' must translate "' . SiteHealth::PROBE_STRING . '", the string the Site Health panel probes.'
			);
		}
	}

	public function test_the_probe_constant_still_matches_the_literal_it_looks_up(): void {
		// The i18n sniffs demand a string literal inside __(), so the probe text exists twice:
		// once as the literal and once as the constant it is compared against. If they drift the
		// panel reports "not translated" on every site, in every language.
		$source = (string) file_get_contents( dirname( ROXYAPI_PLUGIN_FILE ) . '/src/Admin/SiteHealth.php' );

		$this->assertStringContainsString(
			"__( '" . SiteHealth::PROBE_STRING . "', 'roxyapi' )",
			$source,
			'SiteHealth must look up exactly the string PROBE_STRING holds.'
		);
	}

	public function test_the_section_reports_the_locale_and_the_catalogue(): void {
		$info = SiteHealth::add_section( array() );

		$this->assertArrayHasKey( 'roxyapi-i18n', $info );
		$fields = $info['roxyapi-i18n']['fields'];

		foreach ( array( 'site_language', 'display_language', 'catalogue', 'catalogue_loaded', 'sample_string', 'bundled' ) as $key ) {
			$this->assertArrayHasKey( $key, $fields );
		}

		$this->assertSame( get_locale(), $fields['site_language']['value'] );
		$this->assertStringContainsString( 'es_ES', $fields['bundled']['value'] );
	}

	public function test_a_regional_locale_reports_the_file_it_falls_back_to(): void {
		add_filter( 'pre_determine_locale', array( $this, 'force_es_ar' ) );

		$info = SiteHealth::add_section( array() );

		$this->assertStringContainsString( 'roxyapi-es_ES.mo', $info['roxyapi-i18n']['fields']['catalogue']['value'] );
	}

	public function test_a_locale_mismatch_is_called_out_rather_than_just_printed(): void {
		// Site language and interface language disagreeing is the failure that costs days to
		// find, so the panel has to say so rather than print two codes and leave the reader
		// to spot the difference.
		add_filter( 'pre_determine_locale', array( $this, 'force_es_ar' ) );

		$info  = SiteHealth::add_section( array() );
		$value = $info['roxyapi-i18n']['fields']['display_language']['value'];

		$this->assertStringContainsString( 'es_AR', $value );
		$this->assertNotSame( 'es_AR', $value );
	}

	public function test_other_sections_are_left_alone(): void {
		$info = SiteHealth::add_section( array( 'wp-core' => array( 'label' => 'Core' ) ) );

		$this->assertArrayHasKey( 'wp-core', $info );
		$this->assertSame( 'Core', $info['wp-core']['label'] );
	}
}
