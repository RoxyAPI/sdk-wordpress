<?php
/**
 * Translation state, reported under Tools > Site Health > Info.
 *
 * "The plugin is not translated" is otherwise invisible from the outside: nothing errors, the
 * page renders, and only the wording is wrong. Every earlier report of it cost days of remote
 * guesswork. This section is the copy-paste answer, and it names the two locales separately
 * because the common cause is those two disagreeing: readings come back in the site language
 * while the interface stays English.
 *
 * Locale and translation facts only. Nothing about keys, accounts or usage belongs on a screen
 * that site owners paste into public support threads.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Support\LocaleFallback;

class SiteHealth {

	/**
	 * String used to prove a lookup really resolves.
	 *
	 * A loaded catalogue is not a working one: an empty or truncated file loads fine and
	 * translates nothing. This label ships in every catalogue and appears on nearly every
	 * reading form. `test-site-health.php` fails if it ever stops being translated, so the
	 * panel cannot start reporting a false problem on a healthy site.
	 */
	public const PROBE_STRING = 'Birth date';

	public static function register(): void {
		add_filter( 'debug_information', array( self::class, 'add_section' ) );
	}

	/**
	 * Append the RoxyAPI translation section.
	 *
	 * @param array<string, mixed> $info Sections already gathered by core.
	 * @return array<string, mixed>
	 */
	public static function add_section( $info ) {
		if ( ! is_array( $info ) ) {
			return $info;
		}

		$site_locale   = get_locale();
		$render_locale = determine_locale();
		$catalogue     = LocaleFallback::catalogue_for( $render_locale );
		$loaded        = is_textdomain_loaded( 'roxyapi' );

		$fields = array(
			'site_language'    => array(
				'label' => __( 'Site language', 'roxyapi' ),
				'value' => $site_locale,
			),
			'display_language' => array(
				'label' => __( 'Language used for the interface', 'roxyapi' ),
				'value' => $site_locale === $render_locale
					? $render_locale
					/* translators: %s: WordPress locale code. */
					: sprintf( __( '%s (does not match the site language, so something on this site is overriding it)', 'roxyapi' ), $render_locale ),
			),
			'catalogue'        => array(
				'label' => __( 'Translation file', 'roxyapi' ),
				'value' => '' === $catalogue
					? __( 'None. This plugin ships no translation for that language.', 'roxyapi' )
					: $catalogue,
			),
			'catalogue_loaded' => array(
				'label' => __( 'Translation file loaded', 'roxyapi' ),
				'value' => $loaded ? __( 'Yes', 'roxyapi' ) : __( 'No', 'roxyapi' ),
			),
			'sample_string'    => array(
				'label' => __( 'Sample string translates', 'roxyapi' ),
				'value' => self::probe_result(),
			),
			'bundled'          => array(
				'label' => __( 'Languages included with the plugin', 'roxyapi' ),
				'value' => self::bundled_locales(),
			),
		);

		$info['roxyapi-i18n'] = array(
			'label'       => __( 'RoxyAPI translations', 'roxyapi' ),
			'description' => __( 'How this plugin is resolving the site language. Copy this section into any report about wording appearing in the wrong language.', 'roxyapi' ),
			'fields'      => $fields,
		);

		return $info;
	}

	/**
	 * Whether a known string actually comes back translated.
	 *
	 * @return string
	 */
	private static function probe_result(): string {
		/*
		 * A plain __() lookup, NOT has_translation(): that needs WordPress 6.7 while the plugin
		 * supports 6.5, and Plugin Check reads the call site statically, so a function_exists()
		 * guard does not stop it failing review at the severity wp.org rejects on. translate()
		 * is no better, it trips the low-level and literal-argument i18n sniffs.
		 *
		 * The literal is duplicated into PROBE_STRING because the i18n sniffs require a literal
		 * here and the test needs a readable handle. `test-site-health.php` asserts the two
		 * still match.
		 *
		 * On English, and on any language we ship no catalogue for, this reports "No". That is
		 * the correct answer rather than a false alarm: the interface really does render in
		 * English, and the row above says whether a file existed to load.
		 */
		if ( __( 'Birth date', 'roxyapi' ) !== self::PROBE_STRING ) {
			return __( 'Yes', 'roxyapi' );
		}

		return __( 'No. The interface will render in English.', 'roxyapi' );
	}

	/**
	 * Locale codes with a catalogue in the shipped `languages` directory.
	 *
	 * @return string
	 */
	private static function bundled_locales(): string {
		$files = glob( LocaleFallback::languages_dir() . '/roxyapi-*.mo' );
		if ( ! is_array( $files ) || array() === $files ) {
			return __( 'None found.', 'roxyapi' );
		}

		$locales = array();
		foreach ( $files as $file ) {
			$locales[] = substr( basename( $file, '.mo' ), strlen( 'roxyapi-' ) );
		}
		sort( $locales );

		return implode( ', ', $locales );
	}
}
