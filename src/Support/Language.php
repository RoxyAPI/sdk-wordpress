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
			$prefix = strtolower( substr( (string) get_locale(), 0, 2 ) );
			$lang   = in_array( $prefix, self::SUPPORTED, true ) ? $prefix : '';
		}
		return $lang;
	}
}
