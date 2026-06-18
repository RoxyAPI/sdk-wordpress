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
}
