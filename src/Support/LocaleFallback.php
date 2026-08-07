<?php
/**
 * Resolves which shipped catalogue serves a locale, and serves it to regional locales
 * we do not ship a file for.
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
 * Two entry points, same resolution. `fallback()` is the `load_textdomain_mofile` filter, which
 * covers loads WordPress starts itself. `catalogue_for()` answers the same question without a
 * filter, so the plugin can load its catalogue directly instead of waiting to be asked.
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
	 * Resolved catalogue keyed by locale, so the directory is scanned at most once per
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

		$locale = (string) substr( basename( $mofile, '.mo' ), strlen( self::DOMAIN ) + 1 );

		/**
		 * A wp.org LANGUAGE PACK, once one exists, lands in WP_LANG_DIR/plugins and is passed
		 * here first. Only an unreadable path reaches this point, so an official pack for the
		 * exact locale always wins; look beside it for a same-language pack before reaching
		 * for the catalogue we bundle.
		 */
		$resolved = self::same_language_file( dirname( $mofile ), $locale );
		if ( '' === $resolved ) {
			$resolved = self::catalogue_for( $locale );
		}

		return '' === $resolved ? $mofile : $resolved;
	}

	/**
	 * Absolute path of the catalogue this plugin ships for a locale.
	 *
	 * Exact locale first, then any catalogue for the same language. Empty string when we ship
	 * nothing that language can use, which is the normal answer on an English site.
	 *
	 * @param string $locale WordPress locale, for example `es_AR`.
	 * @return string Absolute path, or '' when there is nothing to load.
	 */
	public static function catalogue_for( string $locale ): string {
		if ( ! isset( self::$cache[ $locale ] ) ) {
			self::$cache[ $locale ] = self::same_language_file( self::languages_dir(), $locale );
		}

		return self::$cache[ $locale ];
	}

	/** Directory holding the catalogues shipped inside the plugin. */
	public static function languages_dir(): string {
		return dirname( ROXYAPI_PLUGIN_FILE ) . '/languages';
	}

	/**
	 * Nearest catalogue for the same language inside one directory.
	 *
	 * @param string $dir    Directory to search.
	 * @param string $locale WordPress locale, for example `es_AR`.
	 * @return string Absolute path, or '' when the directory holds nothing for that language.
	 */
	private static function same_language_file( string $dir, string $locale ): string {
		$exact = $dir . '/' . self::DOMAIN . '-' . $locale . '.mo';
		if ( is_readable( $exact ) ) {
			return $exact;
		}

		$prefix = strtolower( substr( $locale, 0, 2 ) );
		if ( strlen( $prefix ) !== 2 ) {
			return '';
		}

		// glob() returns false on an unreadable directory, so the result is checked rather than
		// trusted. Guarding the CALL would be dead code: a host that disables glob() outright
		// takes WordPress down in wp-settings.php long before any plugin runs, measured on 6.7.2
		// and 7.0.3 alike.
		$candidates = glob( $dir . '/' . self::DOMAIN . '-' . $prefix . '*.mo' );
		if ( ! is_array( $candidates ) || array() === $candidates ) {
			return '';
		}

		// Deterministic: es_ES before es_MX, so two installs never disagree.
		sort( $candidates );
		return (string) $candidates[0];
	}
}
