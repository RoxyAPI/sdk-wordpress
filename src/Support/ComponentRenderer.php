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
	 * Render an API response for the given operation.
	 *
	 * @param string               $operation_id Spec operationId driving the component choice.
	 * @param array<string, mixed> $data         Unwrapped API response.
	 * @return string Rendered HTML.
	 */
	public static function render( string $operation_id, array $data ): string {
		$rows = ComponentMap::for( $operation_id );

		/**
		 * Filter whether the web-component bundle is used for rendering. Return
		 * false to force every reading through the server-side card renderer
		 * (no CDN bundle, no client-side hydration).
		 *
		 * @param bool $enabled Whether to use the web-component renderer.
		 */
		$ui_enabled = (bool) apply_filters( 'roxyapi_enqueue_ui_bundle', true );

		if ( empty( $rows ) || false === $ui_enabled || empty( $data ) ) {
			return GenericRenderer::render( $operation_id, $data );
		}

		UiBundle::enqueue();

		$markup = '';
		foreach ( $rows as $row ) {
			$tag = isset( $row['component'] ) ? (string) $row['component'] : '';
			if ( '' === $tag || ! preg_match( self::TAG_PATTERN, $tag ) ) {
				// Unexpected map entry; skip this row rather than emit an unsafe tag.
				continue;
			}
			$markup .= self::render_element( $tag, $operation_id, $data );
		}

		if ( '' === $markup ) {
			return GenericRenderer::render( $operation_id, $data );
		}

		if ( count( $rows ) > 1 ) {
			$markup = sprintf(
				'<div class="roxyapi-component-stack" data-operation="%s">%s</div>',
				esc_attr( $operation_id ),
				$markup
			);
		}

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
	 * @param string               $tag          Validated `roxy-*` tag name.
	 * @param string               $operation_id Spec operationId.
	 * @param array<string, mixed> $data         Unwrapped API response.
	 * @return string Rendered element HTML.
	 */
	private static function render_element( string $tag, string $operation_id, array $data ): string {
		$payload = wp_json_encode( $data, JSON_HEX_TAG );
		if ( false === $payload ) {
			// Encoding failed; degrade to the server-rendered card.
			return GenericRenderer::render( $operation_id, $data );
		}

		return sprintf(
			'<%1$s class="roxyapi-component" data-operation="%2$s">'
				. '<script type="application/json" class="roxy-data">%3$s</script>'
				. '<div class="roxyapi-component-fallback">%4$s</div>'
				. '</%1$s>',
			$tag,
			esc_attr( $operation_id ),
			$payload,
			// Meta-free fallback: disclaimer and attribution are emitted once by
			// render(), outside the element, so they survive the upgrade.
			GenericRenderer::render( $operation_id, $data, false )
		);
	}
}
