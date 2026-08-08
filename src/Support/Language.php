<?php
/**
 * Resolves the effective response language for the current request.
 *
 * @remarks A non-empty `display_language` setting wins; otherwise the WordPress locale is mapped to a supported code, or '' when none matches. Used by both the request payload and the cache key so a cached response matches the language it was fetched in.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Language {

	/** Supported `lang` codes. */
	public const SUPPORTED = array( 'en', 'de', 'hi', 'es', 'tr', 'pt', 'fr', 'ru' );

	/**
	 * Effective language code for this request, or '' when none applies.
	 *
	 * @return string
	 */
	public static function resolve(): string {
		$opts = \RoxyAPI\Admin\SettingsSchema::get_option();
		$lang = isset( $opts['display_language'] ) ? (string) $opts['display_language'] : '';
		if ( $lang === '' ) {
			$lang = self::from_locale( (string) get_locale() );
		}
		return $lang;
	}

	/**
	 * Narrow a WordPress locale to a supported code, or '' when the language is
	 * not one we carry.
	 *
	 * @remarks A locale is a language plus a region (`es_AR`, `pt_BR`) and the region is never part of a supported code, so every regional variant of a language resolves to the same one. Callers that need the region for formatting must keep the locale; this answers only "which of our languages is this".
	 *
	 * @param string $locale WordPress locale, for example `es_AR`.
	 * @return string
	 */
	public static function from_locale( string $locale ): string {
		$prefix = strtolower( substr( $locale, 0, 2 ) );
		return in_array( $prefix, self::SUPPORTED, true ) ? $prefix : '';
	}
}
