<?php
/**
 * Shared template rendering helpers.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

class Templates {

	/**
	 * Render a PHP template with the given variables.
	 *
	 * Parameter is named $vars (not $data) to avoid shadowing any caller-supplied
	 * 'data' key during extract().
	 *
	 * @param string               $template Template name (without .php extension).
	 * @param array<string, mixed> $vars     Variables to extract into the template scope.
	 * @return string
	 */
	public static function render( string $template, array $vars ): string {
		$path = dirname( __DIR__, 2 ) . '/templates/' . $template . '.php';
		if ( ! file_exists( $path ) ) {
			return '';
		}
		ob_start();
		extract( $vars, EXTR_SKIP );
		include $path;
		return (string) ob_get_clean();
	}

	public static function error( string $message ): string {
		return '<div class="roxyapi-error">' . esc_html( $message ) . '</div>';
	}

	/**
	 * Render an API error with audience-aware detail.
	 *
	 * Visitors see the friendly category message attached to the WP_Error.
	 * Logged-in admins additionally see the raw upstream status and message
	 * inside an HTML comment, surfaced as a small italic note. Visitors never
	 * see implementation details (HTTP codes, machine codes, upstream text).
	 *
	 * @param \WP_Error $error Error from the RoxyAPI client.
	 * @return string
	 */
	public static function api_error( \WP_Error $error ): string {
		$message = $error->get_error_message();
		$out     = '<div class="roxyapi-error">' . esc_html( $message );

		if ( current_user_can( 'manage_options' ) ) {
			$data            = $error->get_error_data();
			$status          = is_array( $data ) && isset( $data['status'] ) ? (int) $data['status'] : 0;
			$saas_code       = is_array( $data ) && isset( $data['saas_code'] ) ? (string) $data['saas_code'] : '';
			$saas_msg        = is_array( $data ) && isset( $data['saas_msg'] ) ? (string) $data['saas_msg'] : '';
			$saas_suggestion = is_array( $data ) && isset( $data['saas_suggestion'] ) ? (string) $data['saas_suggestion'] : '';
			$saas_hint       = is_array( $data ) && isset( $data['saas_hint'] ) ? (string) $data['saas_hint'] : '';

			$detail_parts = array();
			if ( $status > 0 ) {
				$detail_parts[] = 'HTTP ' . $status;
			}
			if ( $saas_code !== '' ) {
				$detail_parts[] = $saas_code;
			}
			if ( $saas_msg !== '' ) {
				$detail_parts[] = $saas_msg;
			}
			if ( ! empty( $detail_parts ) ) {
				$out .= ' <small class="roxyapi-error-admin">'
					. esc_html__( 'Admin only:', 'roxyapi' )
					. ' '
					. esc_html( implode( ' / ', $detail_parts ) )
					. '</small>';
			}
			// SaaS 404s carry a fuzzy-match `suggestion` (correct method + path)
			// and `hint` (endpoint summary). Surface them on a second admin-only
			// line so debugging is one screen scroll away. Visitors never see
			// either field.
			if ( $saas_suggestion !== '' ) {
				$suggestion_text = sprintf(
					/* translators: 1: suggested HTTP method + path, 2: optional endpoint hint */
					esc_html__( 'Did you mean: %1$s%2$s', 'roxyapi' ),
					esc_html( $saas_suggestion ),
					$saas_hint !== '' ? esc_html( ' (' . $saas_hint . ')' ) : ''
				);
				$out .= ' <small class="roxyapi-error-admin">' . $suggestion_text . '</small>';
			}
		}

		return $out . '</div>';
	}

	public static function placeholder( string $message ): string {
		return '<div class="roxyapi-placeholder"><p>' . esc_html( $message ) . '</p></div>';
	}
}
