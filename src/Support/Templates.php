<?php
/**
 * Shared template rendering helpers.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

class Templates {

	/**
	 * Render a PHP template with the given data.
	 *
	 * @param string               $template Template name (without .php extension).
	 * @param array<string, mixed> $data     Variables to extract into the template scope.
	 * @return string
	 */
	public static function render( string $template, array $data ): string {
		$path = dirname( __DIR__, 2 ) . '/templates/' . $template . '.php';
		if ( ! file_exists( $path ) ) {
			return '';
		}
		ob_start();
		extract( $data, EXTR_SKIP );
		include $path;
		return (string) ob_get_clean();
	}

	public static function error( string $message ): string {
		return '<div class="roxyapi-error">' . esc_html( $message ) . '</div>';
	}

	public static function placeholder( string $message ): string {
		return '<div class="roxyapi-placeholder"><p>' . esc_html( $message ) . '</p></div>';
	}
}
