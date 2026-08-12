<?php
/**
 * Central dispatch between the web-component renderer and the generic card
 * renderer.
 *
 * For operations mapped in {@link \RoxyAPI\Generated\ComponentMap} this emits
 * one or more @roxyapi/ui custom elements, each carrying the unwrapped API
 * response as a JSON `<script class="roxy-data">` child plus a server-rendered
 * fallback. Each element self-hydrates from that payload on connect, so no
 * mount script is required. For unmapped operations, empty data, or when a site
 * opts out via the `roxyapi_enqueue_ui_bundle` filter, this falls back to
 * {@link GenericRenderer::render()} so behavior is unchanged.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

use RoxyAPI\Generated\ComponentMap;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ComponentRenderer {

	/** Custom-element tag names must match this. Tags come from the trusted map. */
	private const TAG_PATTERN = '/^roxy-[a-z-]+$/';

	/**
	 * Attribute value that defers to the site setting. Every shortcode
	 * declares it as the default for `hide_readings`, so a placement that
	 * says nothing follows the setting.
	 */
	public const INHERIT = 'inherit';

	/**
	 * Render an API response for the given operation.
	 *
	 * @param string               $operation_id  Spec operationId driving the component choice.
	 * @param array<string, mixed> $data          Unwrapped API response.
	 * @param bool|string|null     $hide_readings Per-placement override for written
	 *                                            interpretations. `null`, `''` and
	 *                                            `inherit` follow the site setting;
	 *                                            anything else is read as a boolean.
	 * @param string|null          $hide_sections Per-placement override for whole
	 *                                            blocks, as a comma-separated list of
	 *                                            part names. `null`, `''` and `inherit`
	 *                                            follow the site setting; `none` hides
	 *                                            nothing here regardless of it.
	 * @return string Rendered HTML.
	 */
	public static function render( string $operation_id, array $data, $hide_readings = null, $hide_sections = null ): string {
		$rows     = ComponentMap::for( $operation_id );
		$hide     = self::hide_readings( $hide_readings );
		$sections = self::hide_sections( $hide_sections );

		/**
		 * Filter whether the web-component bundle is used for rendering. Return
		 * false to force every reading through the server-side card renderer
		 * (no CDN bundle, no client-side hydration).
		 *
		 * @param bool $enabled Whether to use the web-component renderer.
		 */
		$ui_enabled = (bool) apply_filters( 'roxyapi_enqueue_ui_bundle', true );

		if ( false === $ui_enabled || empty( $data ) ) {
			return GenericRenderer::render( $operation_id, $data, true, $hide, $sections );
		}

		// Mapped operations use their component; any other shape uses the
		// library's generic `roxy-data` renderer. The server-rendered fallback
		// inside the element covers no-JS, and a shape `roxy-data` cannot
		// handle just leaves that fallback visible.
		if ( empty( $rows ) ) {
			$rows = array(
				array( 'component' => 'roxy-data' ),
			);
		}

		UiBundle::enqueue();

		$markup = '';
		foreach ( $rows as $row ) {
			$tag = isset( $row['component'] ) ? (string) $row['component'] : '';
			if ( '' === $tag || ! preg_match( self::TAG_PATTERN, $tag ) ) {
				// Unexpected map entry; skip this row rather than emit an unsafe tag.
				continue;
			}
			$attrs   = isset( $row['attrs'] ) && is_array( $row['attrs'] ) ? $row['attrs'] : array();
			$markup .= self::render_element( $tag, $operation_id, $data, $hide, $sections, $attrs );
		}

		if ( '' === $markup ) {
			return GenericRenderer::render( $operation_id, $data, true, $hide, $sections );
		}

		// Always wrapped, and the wrapper is load-bearing rather than cosmetic.
		//
		// `wpautop` runs on `the_content` at priority 10 and `do_shortcode` at 11,
		// so a shortcode sitting inline with text is already inside a `<p>` by the
		// time this markup is substituted in. `<div>` may not appear inside `<p>`,
		// so the parser closed the paragraph at the fallback `<div>` and hoisted it
		// OUT of the custom element. Once outside, it matches neither
		// `.roxyapi-component:defined .roxyapi-component-fallback` nor its
		// `:not(:defined)` partner, so it defaulted to visible and the reading
		// rendered twice: once in the upgraded component and once as an unstyled,
		// unbranded copy underneath.
		//
		// A block-level wrapper moves the parser's break point outside the element.
		// The `<p>` still closes, but it closes HERE, and everything below stays
		// nested exactly as written. The fallback keeps its light-DOM home, so the
		// no-JS and failed-bundle views are unchanged. Do not make this conditional.
		$markup = sprintf(
			'<div class="roxyapi-embed" data-operation="%s">%s</div>',
			esc_attr( $operation_id ),
			$markup
		);

		// Disclaimer and attribution render OUTSIDE the custom element. An
		// element hides its light-DOM fallback once it upgrades, so anything
		// nested inside would vanish for JavaScript visitors. Emitting them here
		// keeps them present in both the upgraded and the no-JavaScript view, and
		// once per shortcode rather than once per stacked component.
		return $markup . Meta::block( $operation_id, $data );
	}

	/**
	 * Build a single custom element with its JSON payload and no-JS fallback.
	 *
	 * The payload is encoded with JSON_HEX_TAG so any `<` / `>` inside string
	 * values (for example a literal `</script>` substring) is escaped and
	 * cannot break out of the script element.
	 *
	 * `hide-readings` and `hide-sections` are emitted on every element rather
	 * than on a curated list of components that understand them. An unknown
	 * attribute is inert on a custom element, so uniform emission costs nothing
	 * and cannot go stale when the next component ships.
	 *
	 * `hide-sections` carries the RESOLVED list rather than the raw attribute,
	 * so the element receives the same answer the no-JS fallback below is built
	 * from and the two views can never disagree about which blocks are gone.
	 *
	 * @param string                $tag           Validated `roxy-*` tag name.
	 * @param string                $operation_id  Spec operationId.
	 * @param array<string, mixed>  $data          Unwrapped API response.
	 * @param bool                  $hide_readings Whether written interpretations are suppressed.
	 * @param array<int, string>    $hide_sections Resolved part names to drop, already sanitized.
	 * @param array<string, string> $attrs         Variant selectors from the map, e.g. `type="soul-urge"`.
	 * @return string Rendered element HTML.
	 */
	private static function render_element( string $tag, string $operation_id, array $data, bool $hide_readings, array $hide_sections = array(), array $attrs = array() ): string {
		$payload = wp_json_encode( $data, JSON_HEX_TAG );
		if ( false === $payload ) {
			// Encoding failed; degrade to the server-rendered card.
			return GenericRenderer::render( $operation_id, $data, true, $hide_readings, $hide_sections );
		}

		// Variant selectors from the map (`type="soul-urge"`). Names are
		// constrained here as well as at codegen time, because this method also
		// serves the hand-written fallback row above.
		$attr_markup = '';
		foreach ( $attrs as $name => $value ) {
			if ( ! is_string( $name ) || ! preg_match( '/^[a-z][a-z0-9-]*$/', $name ) ) {
				continue;
			}
			$attr_markup .= sprintf( ' %s="%s"', $name, esc_attr( (string) $value ) );
		}

		return sprintf(
			'<%1$s class="roxyapi-component" data-operation="%2$s"%5$s%7$s%6$s>'
				. '<script type="application/json" class="roxy-data">%3$s</script>'
				. '<div class="roxyapi-component-fallback">%4$s</div>'
				. '</%1$s>',
			$tag,
			esc_attr( $operation_id ),
			$payload,
			// Meta-free fallback: disclaimer and attribution are emitted once by
			// render(), outside the element, so they survive the upgrade. The
			// fallback is what a no-JS visitor reads, so it hides the same text
			// the component hides.
			GenericRenderer::render( $operation_id, $data, false, $hide_readings, $hide_sections ),
			$hide_readings ? ' hide-readings' : '',
			$attr_markup,
			// Names are already narrowed to `[a-z][a-z0-9-]*`, so the joined value
			// carries no character that needs escaping; `esc_attr` runs anyway
			// because an unescaped attribute is not a thing to leave to a reader.
			$hide_sections === array() ? '' : sprintf( ' hide-sections="%s"', esc_attr( implode( ',', $hide_sections ) ) )
		);
	}

	/**
	 * Resolve whether written interpretations are suppressed for this render.
	 *
	 * Precedence: an explicit shortcode attribute wins over the site setting,
	 * the site setting wins over the default, and the default is readings
	 * shown. `inherit` (the shortcode default) and an empty value are not
	 * opinions, so they fall through to the setting.
	 *
	 * @param bool|string|null $override Raw per-placement value.
	 * @return bool
	 */
	private static function hide_readings( $override ): bool {
		if ( is_bool( $override ) ) {
			return $override;
		}
		$raw = is_string( $override ) ? strtolower( trim( $override ) ) : '';
		if ( '' !== $raw && self::INHERIT !== $raw ) {
			$explicit = filter_var( $raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
			if ( null !== $explicit ) {
				return $explicit;
			}
		}

		$opts = \RoxyAPI\Admin\SettingsSchema::get_option();
		return ! empty( $opts['hide_readings'] );
	}

	/**
	 * Resolve which named blocks are hidden for this render.
	 *
	 * Same precedence as {@see hide_readings}, and deliberately the same
	 * sentinel: an explicit shortcode attribute wins over the site setting, and
	 * `inherit` or an empty value are not opinions so they fall through to it.
	 *
	 * The one difference is what "explicit" can say. A boolean has no way to
	 * express "hide nothing here", but a list does, so `hide_sections="none"` is
	 * honoured as an empty list and lets one placement opt OUT of a site-wide
	 * setting. Without it a site hiding `patterns` everywhere could never show
	 * them on a single page, which is most of the reason this is per placement.
	 *
	 * @param string|null $override Raw per-placement value.
	 * @return array<int, string>
	 */
	private static function hide_sections( $override ): array {
		$raw = is_string( $override ) ? strtolower( trim( $override ) ) : '';
		if ( 'none' === $raw ) {
			return array();
		}
		if ( '' !== $raw && self::INHERIT !== $raw ) {
			return Sanitize::section_names( $raw );
		}

		return Theming::hidden_sections();
	}
}
