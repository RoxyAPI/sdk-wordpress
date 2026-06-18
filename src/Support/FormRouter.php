<?php
/**
 * Visitor-form POST dispatcher.
 *
 * Hooked at `init` priority 5 so it runs before shortcode rendering. When a
 * request carries `$_POST['roxyapi_form']`, the router resolves the matching
 * `\RoxyAPI\Generated\Forms\<Op>Form` class, validates the nonce, applies a
 * per-form rate limit, sanitises the body against the form spec, calls the
 * typed PHP client, stores the result in a short-lived transient, and
 * redirects back to the originating page (Post-Redirect-Get) with a
 * `?roxyapi_r=<token>` query var. The shortcode side reads that token on the
 * follow-up GET and renders the result above the form.
 *
 * The `roxyapi_r` query var is an opaque 32-char random token; the actual
 * transient key is derived server-side via SHA-256 over `form_id|token`, so
 * visitor-controlled input never reaches `get_transient()` / `delete_transient()`
 * as the key parameter.
 *
 * Form classes carry only metadata (`spec()`) and the API call (`call()`).
 * Sanitisation, validation, body building, transient storage, and PRG
 * redirection all live here so 8 generated forms stay tiny and identical.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;

class FormRouter {

	/** Nonce action prefix; per-form-id namespace prevents cross-form leakage. */
	private const PREFIX = 'roxyapi_form_';

	/** Server-derived transient key prefix (constant). */
	private const TRANSIENT_PREFIX = 'roxyapi_r_';

	/** Transient TTL for stored results / errors. */
	private const TTL = 5 * MINUTE_IN_SECONDS;

	public static function register(): void {
		add_action( 'init', array( self::class, 'maybe_handle' ), 5 );
	}

	/**
	 * Inspect the current request and dispatch to the right form handler.
	 *
	 * @return void
	 */
	public static function maybe_handle(): void {
		if ( empty( $_POST['roxyapi_form'] ) ) {
			return;
		}
		// `sanitize_key()` would lowercase the camelCase operationId baked into
		// the form id; use a stricter regex-validated text sanitiser instead.
		$form_id = sanitize_text_field( wp_unslash( $_POST['roxyapi_form'] ) );
		if ( $form_id === '' || preg_match( '/^[A-Za-z][A-Za-z0-9]+$/', $form_id ) !== 1 ) {
			return;
		}
		$form_class = self::resolve_class( $form_id );
		if ( $form_class === null ) {
			return;
		}

		$nonce_action = self::PREFIX . $form_id;
		$nonce        = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
			self::redirect_with(
				$form_id,
				array( 'error' => __( 'Could not verify the form. Reload the page and try again.', 'roxyapi' ) )
			);
			return;
		}

		if ( ! RateLimit::check( 'form_' . $form_id ) ) {
			self::redirect_with(
				$form_id,
				array( 'error' => __( 'Too many submissions. Please wait a minute and try again.', 'roxyapi' ) )
			);
			return;
		}

		// GDPR consent gate. Form-mode endpoints collect special-category
		// data (birth date / time / location); we never call the upstream
		// without explicit visitor consent. Browser-side `required` is a
		// belt-and-braces UX hint; this server check is the actual contract.
		if ( empty( $_POST['roxyapi_consent'] ) ) {
			self::redirect_with(
				$form_id,
				array(
					'validation_errors' => array(
						'_consent' => __( 'Please confirm consent before submitting.', 'roxyapi' ),
					),
				)
			);
			return;
		}

		$spec      = $form_class::spec();
		$post      = wp_unslash( $_POST );
		$sanitized = self::sanitize_against_spec( is_array( $post ) ? $post : array(), $spec );

		$validation_errors = self::validate( $sanitized, $spec );
		if ( ! empty( $validation_errors ) ) {
			self::redirect_with(
				$form_id,
				array(
					'validation_errors' => $validation_errors,
					'previous_input'    => $sanitized,
				)
			);
			return;
		}

		$body   = self::build_body( $sanitized, $spec );
		$result = $form_class::call( $body );

		if ( is_wp_error( $result ) ) {
			self::redirect_with(
				$form_id,
				array(
					'wp_error'       => array(
						'code'    => $result->get_error_code(),
						'message' => $result->get_error_message(),
						'data'    => $result->get_error_data(),
					),
					'previous_input' => $sanitized,
				)
			);
			return;
		}

		self::redirect_with(
			$form_id,
			array(
				'result'         => $result,
				'previous_input' => $sanitized,
			)
		);
	}

	/**
	 * Read the result transient (if any) for a given form on the GET that
	 * follows a PRG redirect. Single-use: deletes the transient after read so
	 * a refresh does not show stale data.
	 *
	 * @param string $form_id Form id (matches `spec()['operation_id']`).
	 * @return array<string,mixed>|null
	 */
	public static function consume_result( string $form_id ): ?array {
		// `$_GET['roxyapi_r']` is an opaque token (no DB semantics on its own);
		// the transient key is derived server-side via SHA-256 over
		// `form_id|token`, so visitor input never reaches the transient API as
		// the key parameter. Nonce verification on the GET would be a false
		// safety; the link is a self-pointing PRG redirect target.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Opaque token; transient key is server-derived.
		if ( empty( $_GET['roxyapi_r'] ) ) {
			return null;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Opaque token; transient key is server-derived.
		$token = sanitize_text_field( wp_unslash( $_GET['roxyapi_r'] ) );
		if ( preg_match( '/^[A-Za-z0-9]{32}$/', $token ) !== 1 ) {
			return null;
		}
		$transient_key = self::transient_key( $form_id, $token );
		$data          = get_transient( $transient_key );
		delete_transient( $transient_key );
		return is_array( $data ) ? $data : null;
	}

	/**
	 * Derive the transient key from a form id and an opaque token.
	 *
	 * SHA-256 keeps the resulting key 100% server-controlled even when the
	 * token came from `$_GET`, so the value passed to `get_transient()` /
	 * `set_transient()` / `delete_transient()` is never user input.
	 *
	 * @param string $form_id Form id (operationId).
	 * @param string $token   Opaque 32-char token.
	 * @return string Transient key.
	 */
	private static function transient_key( string $form_id, string $token ): string {
		return self::TRANSIENT_PREFIX . hash( 'sha256', $form_id . '|' . $token );
	}

	/**
	 * Resolve a form id like `calculateSynastry` to its generated class.
	 *
	 * @param string $form_id Form id.
	 * @return class-string|null
	 */
	private static function resolve_class( string $form_id ): ?string {
		// Form ids are operationIds (camelCase). Generator emits PascalCase
		// classes under \RoxyAPI\Generated\Forms\.
		$pascal = preg_replace_callback(
			'/(^|[^a-zA-Z0-9])([a-z])/',
			static fn( $m ) => strtoupper( $m[2] ),
			$form_id
		);
		$pascal = is_string( $pascal ) ? ucfirst( $pascal ) : ucfirst( $form_id );
		$class  = '\\RoxyAPI\\Generated\\Forms\\' . $pascal . 'Form';
		if ( class_exists( $class ) && method_exists( $class, 'spec' ) && method_exists( $class, 'call' ) ) {
			/**
			 * Narrow the type for static analysers; runtime guards above already
			 * proved the class exists and exposes the expected static methods.
			 *
			 * @var class-string $class
			 */
			return $class;
		}
		return null;
	}

	/**
	 * Sanitise a $_POST array against the form spec.
	 *
	 * @param array<string,mixed> $post Raw POST array (already unslashed).
	 * @param array<string,mixed> $spec Form spec from `<Form>::spec()`.
	 * @return array<string,mixed>
	 */
	private static function sanitize_against_spec( array $post, array $spec ): array {
		$out = array();
		foreach ( ( $spec['sections'] ?? array() ) as $section ) {
			$section_name         = (string) $section['name'];
			$section_post         = isset( $post[ $section_name ] ) && is_array( $post[ $section_name ] ) ? $post[ $section_name ] : array();
			$out[ $section_name ] = array();
			foreach ( ( $section['fields'] ?? array() ) as $field ) {
				$out[ $section_name ][ $field['name'] ] = self::sanitize_field( $section_post[ $field['name'] ] ?? '', $field );
			}
		}
		foreach ( ( $spec['flat_fields'] ?? array() ) as $field ) {
			$out[ $field['name'] ] = self::sanitize_field( $post[ $field['name'] ] ?? '', $field );
		}
		return $out;
	}

	/**
	 * Sanitise one field by its declared type.
	 *
	 * @param mixed               $raw   Raw value from POST.
	 * @param array<string,mixed> $field Field spec.
	 * @return mixed Sanitised value (string, float, int, or '' on invalid input).
	 */
	private static function sanitize_field( $raw, array $field ) {
		$type = (string) ( $field['type'] ?? 'text' );
		if ( ! is_scalar( $raw ) ) {
			return '';
		}
		$raw = (string) $raw;
		switch ( $type ) {
			case 'date':
				$value = sanitize_text_field( $raw );
				return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) === 1 ? $value : '';
			case 'time':
				$value = sanitize_text_field( $raw );
				return preg_match( '/^\d{2}:\d{2}(:\d{2})?$/', $value ) === 1 ? $value : '';
			case 'number':
				if ( $raw === '' || ! is_numeric( $raw ) ) {
					return '';
				}
				$f = (float) $raw;
				if ( isset( $field['min'] ) && $f < (float) $field['min'] ) {
					return '';
				}
				if ( isset( $field['max'] ) && $f > (float) $field['max'] ) {
					return '';
				}
				return $f;
			case 'integer':
				if ( $raw === '' || ! is_numeric( $raw ) ) {
					return '';
				}
				return (int) $raw;
			case 'timezone':
				$value = sanitize_text_field( $raw );
				return in_array( $value, \DateTimeZone::listIdentifiers(), true ) ? $value : '';
			case 'enum':
				$value = sanitize_text_field( $raw );
				$enum  = isset( $field['enum'] ) && is_array( $field['enum'] ) ? $field['enum'] : array();
				return in_array( $value, $enum, true ) ? $value : '';
			case 'text':
			default:
				return sanitize_text_field( $raw );
		}
	}

	/**
	 * Validate sanitised data against required-flags in the spec.
	 *
	 * @param array<string,mixed> $sanitized Sanitised input.
	 * @param array<string,mixed> $spec      Form spec.
	 * @return array<string, array<string,string>|string>
	 */
	private static function validate( array $sanitized, array $spec ): array {
		$errors = array();
		foreach ( ( $spec['sections'] ?? array() ) as $section ) {
			$section_name = (string) $section['name'];
			foreach ( ( $section['fields'] ?? array() ) as $field ) {
				if ( empty( $field['required'] ) ) {
					continue;
				}
				$value = $sanitized[ $section_name ][ $field['name'] ] ?? '';
				if ( $value === '' ) {
					$errors[ $section_name ][ $field['name'] ] = sprintf(
						/* translators: %s: field label */
						__( '%s is required.', 'roxyapi' ),
						(string) $field['label']
					);
				}
			}
		}
		foreach ( ( $spec['flat_fields'] ?? array() ) as $field ) {
			if ( empty( $field['required'] ) ) {
				continue;
			}
			$value = $sanitized[ $field['name'] ] ?? '';
			if ( $value === '' ) {
				$errors[ $field['name'] ] = sprintf(
					/* translators: %s: field label */
					__( '%s is required.', 'roxyapi' ),
					(string) $field['label']
				);
			}
		}
		return $errors;
	}

	/**
	 * Build the API request body from sanitised input. Drops empty values so
	 * the wire payload only carries fields the visitor actually filled.
	 *
	 * @param array<string,mixed> $sanitized Sanitised input keyed by section/field name.
	 * @param array<string,mixed> $spec      Form spec.
	 * @return array<string,mixed>
	 */
	private static function build_body( array $sanitized, array $spec ): array {
		$body = array();
		foreach ( ( $spec['sections'] ?? array() ) as $section ) {
			$section_name = (string) $section['name'];
			$section_data = array();
			foreach ( ( $section['fields'] ?? array() ) as $field ) {
				$value = $sanitized[ $section_name ][ $field['name'] ] ?? '';
				if ( $value === '' ) {
					continue;
				}
				$section_data[ $field['name'] ] = $value;
			}
			if ( ! empty( $section_data ) ) {
				$body[ $section_name ] = $section_data;
			}
		}
		foreach ( ( $spec['flat_fields'] ?? array() ) as $field ) {
			$value = $sanitized[ $field['name'] ] ?? '';
			if ( $value === '' ) {
				continue;
			}
			$body[ $field['name'] ] = $value;
		}
		return $body;
	}

	/**
	 * Store a transient and redirect (PRG) with `?roxyapi_r=<key>`. Append to
	 * the page the visitor came from so a post-cache page reload still picks
	 * the right URL.
	 *
	 * @param string               $form_id Form id (used to namespace the transient key).
	 * @param array<string, mixed> $payload Result, error, or validation_errors map.
	 * @return void
	 */
	private static function redirect_with( string $form_id, array $payload ): void {
		$token         = wp_generate_password( 32, false, false );
		$transient_key = self::transient_key( $form_id, $token );
		set_transient( $transient_key, $payload, self::TTL );
		// wp_get_referer() returns false when the referer equals the current
		// request URI, which is exactly the same-page POST every visitor form
		// makes, so it would send every submission to the site home. Use the
		// raw referer (the form's own _wp_http_referer field, the page the
		// visitor submitted from) and validate it to a local URL.
		$referer = wp_get_raw_referer();
		$target  = is_string( $referer ) && $referer !== ''
			? wp_validate_redirect( $referer, home_url( '/' ) )
			: home_url( '/' );
		// Strip any prior result token so refreshes do not stack.
		$target = remove_query_arg( 'roxyapi_r', $target );
		$target = add_query_arg( 'roxyapi_r', $token, $target );

		/**
		 * Filter the PRG redirect target. Returning a non-null value short-
		 * circuits `wp_safe_redirect()` so tests can capture the target
		 * without terminating the PHPUnit process via `exit`. Production
		 * always sees the default null and the redirect proceeds normally.
		 *
		 * @param string|null $skip  Return any string to skip the redirect.
		 * @param string      $token Opaque token emitted as `?roxyapi_r=`.
		 * @param string      $url   Computed redirect URL.
		 */
		$skip = apply_filters( 'roxyapi_form_router_skip_redirect', null, $token, $target );
		if ( $skip !== null ) {
			return;
		}

		wp_safe_redirect( $target );
		exit;
	}
}
