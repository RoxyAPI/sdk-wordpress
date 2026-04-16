<?php
/**
 * Generic renderer for auto-generated shortcodes and blocks.
 *
 * Walks the API response and emits a clean <dl> using OpenAPI field names.
 * Hero shortcodes use custom templates in templates/. The generic renderer
 * handles the long tail.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Blocks;

class Renderer {

	/**
	 * @param string $operation_id The OpenAPI operation ID.
	 * @param array<string, mixed> $data API response data.
	 * @return string
	 */
	public static function render_generic( string $operation_id, array $data ): string {
		$class = 'roxyapi-generic roxyapi-' . sanitize_html_class( $operation_id );
		$out   = '<dl class="' . esc_attr( $class ) . '">';
		foreach ( $data as $field => $value ) {
			$label = self::humanize( (string) $field );
			$out  .= '<dt>' . esc_html( $label ) . '</dt>';
			$out  .= '<dd>' . self::render_value( $value ) . '</dd>';
		}
		$out .= '</dl>';
		return $out;
	}

	/**
	 * @param mixed $value Scalar, array, or nested value to render.
	 * @return string
	 */
	private static function render_value( $value ): string {
		if ( is_scalar( $value ) ) {
			return esc_html( (string) $value );
		}
		if ( is_array( $value ) ) {
			if ( self::is_list( $value ) ) {
				$out = '<ul>';
				foreach ( $value as $item ) {
					$out .= '<li>' . self::render_value( $item ) . '</li>';
				}
				return $out . '</ul>';
			}
			$out = '<dl>';
			foreach ( $value as $k => $v ) {
				$out .= '<dt>' . esc_html( self::humanize( (string) $k ) ) . '</dt>';
				$out .= '<dd>' . self::render_value( $v ) . '</dd>';
			}
			return $out . '</dl>';
		}
		return '';
	}

	/**
	 * @param string $field Raw field name (camelCase, snake_case, or kebab-case).
	 * @return string
	 */
	private static function humanize( string $field ): string {
		$with_spaces = preg_replace( '/(?<!^)([A-Z])/', ' $1', $field );
		$with_spaces = str_replace( array( '_', '-' ), ' ', (string) $with_spaces );
		return ucfirst( strtolower( $with_spaces ) );
	}

	/**
	 * @param array<mixed> $arr Array to check.
	 * @return bool
	 */
	private static function is_list( array $arr ): bool {
		if ( empty( $arr ) ) {
			return true;
		}
		return array_keys( $arr ) === range( 0, count( $arr ) - 1 );
	}
}
