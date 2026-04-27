<?php
/**
 * Renders a visitor-facing HTML form from a `<Form>::spec()` data structure.
 *
 * Each generated form class lives at `\RoxyAPI\Generated\Forms\<Op>Form` and
 * carries a `spec()` array with sections, fields, and submit copy. The
 * renderer walks the spec, emits a `<form method="post">` with a nonce, the
 * `roxyapi_form` dispatch hidden input, and a control per field whose type
 * derives from the OpenAPI body schema. Submission is handled by FormRouter.
 *
 * If the request follows a PRG redirect carrying `?roxyapi_r=<key>`, the
 * stored result (success, validation errors, or upstream WP_Error) renders
 * above the form. Submitted values flow back into the form so the visitor
 * does not retype on a validation miss.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;

class FormRenderer {

	/**
	 * Render the form for the given Form class.
	 *
	 * @param class-string $form_class Fully qualified Form class name.
	 * @return string HTML.
	 */
	public static function render( string $form_class ): string {
		if ( ! class_exists( $form_class ) || ! method_exists( $form_class, 'spec' ) ) {
			return '';
		}
		wp_enqueue_style( 'roxyapi-frontend' );

		$spec    = $form_class::spec();
		$form_id = (string) ( $spec['operation_id'] ?? '' );
		if ( $form_id === '' ) {
			return '';
		}

		$stored        = FormRouter::consume_result( $form_id );
		$result_html   = '';
		$errors_by_key = array();
		$prev          = array();

		if ( is_array( $stored ) ) {
			$prev = isset( $stored['previous_input'] ) && is_array( $stored['previous_input'] ) ? $stored['previous_input'] : array();
			if ( isset( $stored['error'] ) ) {
				$result_html = '<div class="roxyapi-error">' . esc_html( (string) $stored['error'] ) . '</div>';
			} elseif ( isset( $stored['wp_error'] ) && is_array( $stored['wp_error'] ) ) {
				$err         = new WP_Error(
					(string) ( $stored['wp_error']['code'] ?? 'roxyapi_error' ),
					(string) ( $stored['wp_error']['message'] ?? '' ),
					$stored['wp_error']['data'] ?? null
				);
				$result_html = Templates::api_error( $err );
			} elseif ( isset( $stored['result'] ) && is_array( $stored['result'] ) ) {
				$result_html = GenericRenderer::render( $form_id, $stored['result'] );
			}
			if ( isset( $stored['validation_errors'] ) && is_array( $stored['validation_errors'] ) ) {
				$errors_by_key = $stored['validation_errors'];
			}
		}

		$out  = '<div class="roxyapi-form-wrap">';
		$out .= $result_html;

		$action = self::current_url();
		$out   .= '<form class="roxyapi-form" method="post" action="' . esc_url( $action ) . '" novalidate>';
		$out   .= '<input type="hidden" name="roxyapi_form" value="' . esc_attr( $form_id ) . '">';
		$out   .= wp_nonce_field( 'roxyapi_form_' . $form_id, '_wpnonce', true, false );

		// Site-owner override of form title takes priority over the spec
		// default. Empty setting means use the operation's spec title.
		$opts           = \RoxyAPI\Admin\SettingsSchema::get_option();
		$override_title = (string) ( $opts['form_title'] ?? '' );
		$title          = $override_title !== '' ? $override_title : (string) ( $spec['title'] ?? '' );
		if ( $title !== '' ) {
			$out .= '<h3 class="roxyapi-form-title">' . esc_html( $title ) . '</h3>';
		}
		$lede = (string) ( $spec['lede'] ?? '' );
		if ( $lede !== '' ) {
			$out .= '<p class="roxyapi-form-lede">' . esc_html( $lede ) . '</p>';
		}

		$has_geo_section = false;
		foreach ( ( $spec['sections'] ?? array() ) as $section ) {
			$section_name = (string) $section['name'];
			$section_prev = isset( $prev[ $section_name ] ) && is_array( $prev[ $section_name ] ) ? $prev[ $section_name ] : array();
			$section_errs = isset( $errors_by_key[ $section_name ] ) && is_array( $errors_by_key[ $section_name ] ) ? $errors_by_key[ $section_name ] : array();
			$has_geo      = self::section_has_geo_triplet( $section );
			$section_id   = 'roxyapi-section-' . sanitize_html_class( $form_id . '-' . $section_name );
			$out         .= '<fieldset class="roxyapi-form-section" id="' . esc_attr( $section_id ) . '">';
			$out         .= '<legend>' . esc_html( (string) $section['label'] ) . '</legend>';
			if ( $has_geo ) {
				$has_geo_section = true;
				$out            .= self::render_city_search( $section_id, $section_name );
			}
			foreach ( ( $section['fields'] ?? array() ) as $field ) {
				$out .= self::render_field(
					$section_name . '[' . $field['name'] . ']',
					$field,
					$section_prev[ $field['name'] ] ?? '',
					$section_errs[ $field['name'] ] ?? ''
				);
			}
			$out .= '</fieldset>';
		}

		if ( $has_geo_section ) {
			wp_enqueue_script( 'roxyapi-geocode' );
		}

		foreach ( ( $spec['flat_fields'] ?? array() ) as $field ) {
			$out .= self::render_field(
				(string) $field['name'],
				$field,
				$prev[ $field['name'] ] ?? '',
				is_string( $errors_by_key[ $field['name'] ] ?? null ) ? $errors_by_key[ $field['name'] ] : ''
			);
		}

		// Consent checkbox is mandatory on visitor form mode because the
		// body fields collect special-category personal data under GDPR
		// Art. 9 (birth date + time + location are quasi-identifiers).
		// Configurable label via the `consent_label` setting; falls back
		// to a localised default. Required by browser AND server-side
		// (FormRouter rejects on missing consent).
		$out .= self::render_consent_field( $errors_by_key );

		$override_submit = (string) ( $opts['form_submit_label'] ?? '' );
		$submit          = $override_submit !== ''
			? $override_submit
			: (string) ( $spec['submit_label'] ?? __( 'Submit', 'roxyapi' ) );
		$out            .= '<p class="roxyapi-form-actions">';
		$out            .= '<button type="submit" class="roxyapi-form-submit">' . esc_html( $submit ) . '</button>';
		$out            .= '</p>';
		$out            .= '</form>';
		$out            .= '</div>';
		return $out;
	}

	/**
	 * Render a single labelled control.
	 *
	 * @param string              $name  HTML name attribute (may include brackets, e.g. `person1[birthDate]`).
	 * @param array<string,mixed> $field Field spec.
	 * @param mixed               $value Previously-submitted value to restore.
	 * @param string              $error Validation error message, if any.
	 * @return string
	 */
	private static function render_field( string $name, array $field, $value, string $error ): string {
		$type     = (string) ( $field['type'] ?? 'text' );
		$label    = (string) ( $field['label'] ?? $field['name'] );
		$required = ! empty( $field['required'] );
		$id       = 'roxyapi-field-' . sanitize_html_class( str_replace( array( '[', ']' ), array( '-', '' ), $name ) );
		$desc_id  = $error !== '' ? $id . '-err' : '';

		$value_str = is_scalar( $value ) ? (string) $value : '';
		$req_attr  = $required ? ' required' : '';
		$err_attr  = $desc_id !== '' ? ' aria-describedby="' . esc_attr( $desc_id ) . '"' : '';

		$out  = '<p class="roxyapi-form-field' . ( $error !== '' ? ' has-error' : '' ) . '">';
		$out .= '<label for="' . esc_attr( $id ) . '">' . esc_html( $label );
		if ( $required ) {
			$out .= ' <span class="roxyapi-form-required" aria-hidden="true">*</span>';
		}
		$out .= '</label>';

		switch ( $type ) {
			case 'date':
				$min  = isset( $field['min'] ) ? ' min="' . esc_attr( (string) $field['min'] ) . '"' : ' min="1900-01-01"';
				$max  = isset( $field['max'] ) ? ' max="' . esc_attr( (string) $field['max'] ) . '"' : '';
				$out .= '<input type="date" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value_str ) . '"' . $min . $max . $req_attr . $err_attr . '>';
				break;
			case 'time':
				$out .= '<input type="time" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value_str ) . '" step="60"' . $req_attr . $err_attr . '>';
				break;
			case 'number':
				$min  = isset( $field['min'] ) ? ' min="' . esc_attr( (string) $field['min'] ) . '"' : '';
				$max  = isset( $field['max'] ) ? ' max="' . esc_attr( (string) $field['max'] ) . '"' : '';
				$step = isset( $field['step'] ) ? ' step="' . esc_attr( (string) $field['step'] ) . '"' : ' step="any"';
				$out .= '<input type="number" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value_str ) . '"' . $min . $max . $step . ' inputmode="decimal"' . $req_attr . $err_attr . '>';
				break;
			case 'integer':
				$min  = isset( $field['min'] ) ? ' min="' . esc_attr( (string) $field['min'] ) . '"' : '';
				$max  = isset( $field['max'] ) ? ' max="' . esc_attr( (string) $field['max'] ) . '"' : '';
				$out .= '<input type="number" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value_str ) . '"' . $min . $max . ' step="1" inputmode="numeric"' . $req_attr . $err_attr . '>';
				break;
			case 'timezone':
				$out .= '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . $req_attr . $err_attr . '>';
				$out .= wp_kses(
					wp_timezone_choice( $value_str !== '' ? $value_str : 'UTC' ),
					array(
						'optgroup' => array( 'label' => true ),
						'option'   => array(
							'value'    => true,
							'selected' => true,
						),
					)
				);
				$out .= '</select>';
				break;
			case 'enum':
				$enum = isset( $field['enum'] ) && is_array( $field['enum'] ) ? $field['enum'] : array();
				$out .= '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . $req_attr . $err_attr . '>';
				if ( ! $required ) {
					$out .= '<option value=""></option>';
				}
				foreach ( $enum as $opt ) {
					$opt_str  = (string) $opt;
					$selected = $opt_str === $value_str ? ' selected' : '';
					$out     .= '<option value="' . esc_attr( $opt_str ) . '"' . $selected . '>'
						. esc_html( ucwords( str_replace( array( '-', '_' ), ' ', $opt_str ) ) )
						. '</option>';
				}
				$out .= '</select>';
				break;
			case 'text':
			default:
				$placeholder = isset( $field['placeholder'] ) ? ' placeholder="' . esc_attr( (string) $field['placeholder'] ) . '"' : '';
				$out        .= '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value_str ) . '"' . $placeholder . $req_attr . $err_attr . '>';
				break;
		}

		if ( $error !== '' ) {
			$out .= '<span class="roxyapi-form-error" id="' . esc_attr( $desc_id ) . '" aria-live="polite">' . esc_html( $error ) . '</span>';
		}
		$help = isset( $field['help'] ) ? (string) $field['help'] : '';
		if ( $help !== '' ) {
			$out .= '<span class="roxyapi-form-help">' . esc_html( $help ) . '</span>';
		}

		$out .= '</p>';
		return $out;
	}

	/**
	 * True when a section's fields include latitude, longitude, and timezone
	 * controls. Match either short (`lat`/`lon`/`tz`) or long (`latitude`/
	 * `longitude`/`timezone`) names — the spec uses both.
	 *
	 * @param array<string,mixed> $section Section spec entry.
	 * @return bool
	 */
	private static function section_has_geo_triplet( array $section ): bool {
		$names = array();
		foreach ( ( $section['fields'] ?? array() ) as $field ) {
			$names[] = strtolower( (string) ( $field['name'] ?? '' ) );
		}
		$has_lat = in_array( 'lat', $names, true ) || in_array( 'latitude', $names, true );
		$has_lon = in_array( 'lon', $names, true ) || in_array( 'longitude', $names, true );
		$has_tz  = in_array( 'tz', $names, true ) || in_array( 'timezone', $names, true );
		return $has_lat && $has_lon && $has_tz;
	}

	/**
	 * Render the visible city-search combobox. The matching JS attaches
	 * autocomplete behaviour and writes to the section's lat / lon / tz
	 * inputs on selection. Without JS the field is a labelled hint and the
	 * coordinate inputs remain fully usable.
	 *
	 * @param string $section_id   DOM id of the parent fieldset.
	 * @param string $section_name Section attribute name (for unique input id).
	 * @return string
	 */
	private static function render_city_search( string $section_id, string $section_name ): string {
		$input_id = 'roxyapi-geocode-' . sanitize_html_class( $section_id );
		$out      = '<p class="roxyapi-form-field roxyapi-form-geocode">';
		$out     .= '<label for="' . esc_attr( $input_id ) . '">' . esc_html__( 'Find by city', 'roxyapi' ) . '</label>';
		$out     .= '<input type="text" id="' . esc_attr( $input_id ) . '"'
			. ' class="roxyapi-form-geocode-input"'
			. ' placeholder="' . esc_attr__( 'Start typing a city name', 'roxyapi' ) . '"'
			. ' data-roxyapi-geocode="' . esc_attr( '#' . $section_id ) . '"'
			. ' autocomplete="off">';
		$out     .= '<span class="roxyapi-form-help">' . esc_html__( 'Selecting a city auto-fills latitude, longitude, and timezone.', 'roxyapi' ) . '</span>';
		$out     .= '</p>';
		return $out;
	}

	/**
	 * Render the GDPR consent checkbox. Label is the site owner's
	 * `consent_label` setting if set, otherwise a localised default.
	 *
	 * @param array<string, mixed> $errors_by_key Validation errors keyed by field; consent error sits at `_consent`.
	 * @return string
	 */
	private static function render_consent_field( array $errors_by_key ): string {
		$opts  = \RoxyAPI\Admin\SettingsSchema::get_option();
		$label = isset( $opts['consent_label'] ) && (string) $opts['consent_label'] !== ''
			? (string) $opts['consent_label']
			: __( 'I agree to my form input being processed to generate this reading. Birth date, time, and location are sent to the third-party Roxy service. See the site Privacy Policy for details.', 'roxyapi' );

		$error    = isset( $errors_by_key['_consent'] ) && is_string( $errors_by_key['_consent'] )
			? (string) $errors_by_key['_consent']
			: '';
		$desc_id  = $error !== '' ? 'roxyapi-consent-err' : '';
		$err_attr = $desc_id !== '' ? ' aria-describedby="' . esc_attr( $desc_id ) . '"' : '';

		$out  = '<p class="roxyapi-form-consent' . ( $error !== '' ? ' has-error' : '' ) . '">';
		$out .= '<label class="roxyapi-form-consent-label">';
		$out .= '<input type="checkbox" name="roxyapi_consent" value="1" required' . $err_attr . '>';
		$out .= ' <span>' . esc_html( $label ) . '</span>';
		$out .= '</label>';
		if ( $error !== '' ) {
			$out .= '<span class="roxyapi-form-error" id="' . esc_attr( $desc_id ) . '" aria-live="polite">' . esc_html( $error ) . '</span>';
		}
		$out .= '</p>';
		return $out;
	}

	/**
	 * The current request URL minus any prior `roxyapi_r` query var. The form
	 * always submits back to the same page so caching plugins can serve the
	 * GET unchanged.
	 *
	 * @return string
	 */
	private static function current_url(): string {
		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		if ( $host === '' ) {
			return remove_query_arg( 'roxyapi_r', home_url( $uri ) );
		}
		$scheme = is_ssl() ? 'https' : 'http';
		return remove_query_arg( 'roxyapi_r', $scheme . '://' . $host . $uri );
	}
}
