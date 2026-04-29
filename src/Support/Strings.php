<?php
/**
 * String helpers shared across the renderer and the admin catalog.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Strings {

	/**
	 * Split a camelCase / snake_case / kebab-case identifier into a single
	 * lowercase space-separated string. Letter-to-digit boundaries are
	 * respected so `foo123Bar` becomes `foo 123 bar`.
	 *
	 * Callers re-case to suit their UI: the catalog title-cases each word,
	 * the renderer sentence-cases the whole string.
	 *
	 * @param string $input camelCase / snake_case / kebab-case identifier.
	 * @return string Lowercase, space-separated, trimmed.
	 */
	public static function camel_to_words( string $input ): string {
		$spaced = preg_replace( '/(?<!^)([A-Z])/', ' $1', $input );
		$spaced = preg_replace( '/([A-Za-z])([0-9])/', '$1 $2', (string) $spaced );
		$spaced = str_replace( array( '_', '-' ), ' ', (string) $spaced );
		$spaced = preg_replace( '/\s+/', ' ', (string) $spaced );
		return strtolower( trim( (string) $spaced ) );
	}
}
