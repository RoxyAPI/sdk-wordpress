<?php
/**
 * Forced light or dark theme for the @roxyapi/ui chart components.
 *
 * The components read their light and dark tokens from the vendored token
 * stylesheet, which only flips to dark via the visitor `prefers-color-scheme`
 * or a `data-theme` attribute on the document root. A `data-theme` on an inner
 * wrapper does not reach the tokens, so a forced choice is applied to the page
 * `<html>` tag through the `language_attributes` filter. That attribute only
 * declares `--roxy-*` custom properties, which nothing but our components read,
 * so it does not restyle the surrounding site. The default `auto` emits nothing
 * and the components follow the visitor device automatically.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

use RoxyAPI\Admin\SettingsSchema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Theming {

	/**
	 * Hook the front-end `<html>` filter.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_filter( 'language_attributes', array( self::class, 'html_theme_attr' ) );
	}

	/**
	 * Append `data-theme="dark"` or `data-theme="light"` to the front-end
	 * `<html>` tag when the site owner forces a mode. `auto` returns the
	 * attributes unchanged so the components follow `prefers-color-scheme`.
	 *
	 * The schema default is `light`, not `auto`: most WordPress themes are light,
	 * and a dark reading card dropped into a light page reads as broken. A site
	 * owner who wants the components to follow the visitor picks `auto`.
	 *
	 * @param string $output The language attributes string for the `<html>` tag.
	 * @return string
	 */
	public static function html_theme_attr( string $output ): string {
		if ( is_admin() ) {
			return $output;
		}
		$opts = SettingsSchema::get_option();
		$mode = (string) ( $opts['theme_mode'] ?? 'auto' );
		if ( $mode === 'dark' || $mode === 'light' ) {
			$output .= ' data-theme="' . $mode . '"';
		}
		return $output;
	}
}
