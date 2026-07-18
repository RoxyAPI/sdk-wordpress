<?php
/**
 * Echoes trusted reading markup from a block render.php.
 *
 * @remarks A block render.php is captured via output buffering, so it must echo. The markup comes from {@link \RoxyAPI\Support\ComponentRenderer} (or a hero shortcode): the tag is validated, attributes are escaped, the JSON payload is `JSON_HEX_TAG` encoded, and the fallback is escaped by {@link \RoxyAPI\Support\GenericRenderer}. It is the same string the shortcode path returns and WordPress echoes. `wp_kses_post` strips the custom element and its `<script>` data island, so blocks echo it directly.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BlockOutput {

	/**
	 * Echo trusted, self-escaped reading markup.
	 *
	 * @param string $html Markup from ComponentRenderer or a hero shortcode.
	 */
	public static function render( string $html ): void {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted self-escaped renderer output.
	}

	/**
	 * Map block attribute keys (camelCase, from block.json) to the snake_case keys the generated shortcode `render()` reads.
	 *
	 * @remarks A generated block's render.php hands its `$attributes` straight to the shared `Shortcode::render()`, which resolves inputs with `shortcode_atts()` keyed in snake_case (WordPress lowercases shortcode attribute names, so the generator emits snake_case shortcode attrs while block.json keeps the camelCase API param name). Passing camelCase keys through unchanged means a block's `fullName`, `birthDate`, `nodeType`, etc. never match: required inputs fall back to empty (the reading fails) and optional ones are silently ignored. The generated shortcode key is exactly the snake_case of the block key, so a plain camelCase to snake_case rewrite of the keys lines them up. Single-word keys (`date`, `latitude`, `id`) are unchanged.
	 *
	 * @param array<string, mixed> $attributes Block attributes keyed in camelCase.
	 * @return array<string, mixed> The same values keyed in snake_case for `shortcode_atts()`.
	 */
	public static function to_shortcode_atts( array $attributes ): array {
		$atts = array();
		foreach ( $attributes as $key => $value ) {
			$snake          = strtolower( (string) preg_replace( '/([a-z0-9])([A-Z])/', '$1_$2', (string) $key ) );
			$atts[ $snake ] = $value;
		}
		return $atts;
	}
}
