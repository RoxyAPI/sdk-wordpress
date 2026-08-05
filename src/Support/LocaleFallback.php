<?php
/**
 * Serves a shipped translation to regional locales we do not ship a file for.
 *
 * WordPress matches translation files by EXACT locale. A site set to `es_AR` looks for
 * `roxyapi-es_AR.mo` and, finding none, renders English even though `roxyapi-es_ES.mo` sits
 * in the same folder and is the same language. WordPress ships dozens of regional Spanish,
 * Portuguese, German and French locales, so shipping one file per variant would mean copying
 * the same catalogue twenty times and remembering to copy it again whenever a new locale is
 * added upstream.
 *
 * This maps by language prefix instead: any `xx_YY` falls back to whichever `xx_*` catalogue
 * is present. One file per language keeps working for every current and future variant.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LocaleFallback {

	/** Text domain this fallback applies to. Matches the wp.org slug. */
	private const DOMAIN = 'roxyapi';

	/**
	 * Resolved paths keyed by requested file, so the directory is scanned at most once per
	 * locale per request.
	 *
	 * @var array<string, string>
	 */
	private static array $cache = array();

	public static function register(): void {
		add_filter( 'load_textdomain_mofile', array( self::class, 'fallback' ), 10, 2 );
	}

	/**
	 * Redirect an unreadable translation path to a catalogue we ship for the same language.
	 *
	 * @param string $mofile Absolute path WordPress intends to load.
	 * @param string $domain Text domain being loaded.
	 * @return string The original path, or a same-language file we actually ship.
	 */
	public static function fallback( $mofile, $domain ) {
		$mofile = (string) $mofile;
		if ( $domain !== self::DOMAIN || is_readable( $mofile ) ) {
			return $mofile;
		}
		if ( isset( self::$cache[ $mofile ] ) ) {
			return self::$cache[ $mofile ];
		}

		/**
		 * A wp.org LANGUAGE PACK, once one exists for the exact locale, lands in
		 * WP_LANG_DIR/plugins and is passed here first. Only an unreadable path reaches this
		 * point, so an official pack always wins and this never shadows one.
		 */
		$resolved               = self::same_language_file( $mofile );
		self::$cache[ $mofile ] = $resolved;
		return $resolved;
	}

	/**
	 * Nearest shipped catalogue for the same language, or the original path when there is none.
	 *
	 * @param string $mofile Absolute path WordPress intended to load.
	 * @return string
	 */
	private static function same_language_file( string $mofile ): string {
		$dir  = dirname( $mofile );
		$base = basename( $mofile, '.mo' );
		// Strip the domain prefix, then keep the two-letter language part of the locale.
		$locale = (string) substr( $base, strlen( self::DOMAIN ) + 1 );
		$prefix = strtolower( substr( $locale, 0, 2 ) );
		if ( strlen( $prefix ) !== 2 ) {
			return $mofile;
		}

		$candidates = glob( $dir . '/' . self::DOMAIN . '-' . $prefix . '*.mo' );
		if ( ! $candidates ) {
			// The plugin's own languages/ folder is the shipped set; WP_LANG_DIR may hold none.
			$candidates = glob( dirname( ROXYAPI_PLUGIN_FILE ) . '/languages/' . self::DOMAIN . '-' . $prefix . '*.mo' );
		}
		if ( ! $candidates ) {
			return $mofile;
		}

		// Deterministic: es_ES before es_MX, so two installs never disagree.
		sort( $candidates );
		return (string) $candidates[0];
	}
}
