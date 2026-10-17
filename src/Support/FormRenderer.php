<?php
/**
 * Renders a visitor-facing HTML form from a `<Form>::spec()` data structure.
 *
 * Each generated form class lives at `\RoxyAPI\Generated\Forms\<Op>Form` and
 * carries a `spec()` array with sections, fields, and submit copy. The
 * renderer walks the spec, emits a `<form method="post">` with a nonce, the
 * `roxyapi_form` dispatch hidden input, and a control per field whose type
 * comes from the vendored UI model. Submission is handled by FormRouter.
 *
 * If the request follows a PRG redirect carrying `?roxyapi_r=<form id>.<key>`, the
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
	 * @param class-string         $form_class Fully qualified Form class name.
	 * @param array<string, mixed> $atts       The placement's resolved shortcode attributes.
	 *                                         Carried so the reserved display attributes
	 *                                         survive the PRG round trip and a submitted
	 *                                         reading matches what the same placement
	 *                                         renders statically.
	 * @return string HTML.
	 */
	public static function render( string $form_class, array $atts = array() ): string {
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
			if ( ! empty( $stored['expired'] ) ) {
				// A reading is delivered once, so a refresh, a bookmark or a
				// shared link arrives here. Say so: rendering a bare form
				// instead reads as the site having lost the reading that was
				// on screen a moment ago.
				$result_html = '<div class="roxyapi-notice roxyapi-expired">'
					. esc_html__( 'That reading has already been shown. Enter the details below to run it again.', 'roxyapi' )
					. '</div>';
			} elseif ( isset( $stored['error'] ) ) {
				$result_html = '<div class="roxyapi-error">' . esc_html( (string) $stored['error'] ) . '</div>';
			} elseif ( isset( $stored['wp_error'] ) && is_array( $stored['wp_error'] ) ) {
				$err         = new WP_Error(
					(string) ( $stored['wp_error']['code'] ?? 'roxyapi_error' ),
					(string) ( $stored['wp_error']['message'] ?? '' ),
					$stored['wp_error']['data'] ?? null
				);
				$result_html = Templates::api_error( $err );
			} elseif ( isset( $stored['result'] ) && is_array( $stored['result'] ) ) {
				// Render the result through the same component path as the
				// static reading so a submitted form matches a block or
				// shortcode. render_operation_id is the spec operationId for
				// heroes whose form id differs from it. The placement's
				// attributes go with it: for years this call passed none, so
				// `[roxy_natal_chart hide_readings="1"]` was honoured when the
				// site owner supplied birth details and silently ignored when
				// visitors supplied their own.
				$render_op   = (string) ( $spec['render_operation_id'] ?? $form_id );
				$result_html = ComponentRenderer::render_atts( $render_op, $stored['result'], $atts );
			}
			if ( isset( $stored['validation_errors'] ) && is_array( $stored['validation_errors'] ) ) {
				$errors_by_key = $stored['validation_errors'];
			}
		}

		$out  = '<div class="roxyapi-form-wrap">';
		$out .= $result_html;

		$action      = self::current_url();
		$form_dom_id = 'roxyapi-form-' . sanitize_html_class( $form_id );
		$out        .= '<form class="roxyapi-form" id="' . esc_attr( $form_dom_id ) . '" method="post" action="' . esc_url( $action ) . '" novalidate>';
		$out        .= '<input type="hidden" name="roxyapi_form" value="' . esc_attr( $form_id ) . '">';
		$out        .= wp_nonce_field( 'roxyapi_form_' . $form_id, '_wpnonce', true, false );

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

		$city_searches = array();
		foreach ( $spec['locations'] ?? array() as $location ) {
			$scope_id = empty( $location['section'] )
				? $form_dom_id
				: 'roxyapi-section-' . sanitize_html_class( $form_id . '-' . $location['section'] );

			$city_searches[ $location['anchor'] ] = self::render_city_search( $scope_id, $location['fields'], (string) ( $location['id'] ?? '' ) );
		}
		foreach ( ( $spec['sections'] ?? array() ) as $section ) {
			$section_name = (string) $section['name'];
			$section_prev = isset( $prev[ $section_name ] ) && is_array( $prev[ $section_name ] ) ? $prev[ $section_name ] : array();
			$section_errs = isset( $errors_by_key[ $section_name ] ) && is_array( $errors_by_key[ $section_name ] ) ? $errors_by_key[ $section_name ] : array();
			$section_id   = 'roxyapi-section-' . sanitize_html_class( $form_id . '-' . $section_name );
			$fold         = ! empty( $section['fold'] );
			$out         .= self::open_group(
				$section_id,
				(string) $section['label'],
				$fold,
				(bool) ( array_filter( $section_prev, static fn( $v ) => is_scalar( $v ) && (string) $v !== '' ) || $section_errs )
			);
			$out         .= self::render_fields( $section['fields'] ?? array(), $section_name, $section_id, $section_prev, $section_errs, $city_searches, $fold );
			$out         .= $fold ? '</details>' : '</fieldset>';
		}
		$out .= self::render_fields( $spec['flat_fields'] ?? array(), '', $form_dom_id, $prev, $errors_by_key, $city_searches );
		if ( $city_searches ) {
			wp_enqueue_script( 'roxyapi-geocode' );
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
	 * Render controls in model order, folding only fields marked by the model.
	 * Values and validation errors open their containing fold without JavaScript.
	 *
	 * @param array<int, array<string, mixed>> $fields        Field specs.
	 * @param string                           $section       Posted section name, or empty for flat fields.
	 * @param string                           $group_id      DOM id of the containing group.
	 * @param array<string, mixed>             $previous      Submitted values.
	 * @param array<string, mixed>             $errors        Validation errors.
	 * @param array<string, string>            $city_searches City controls keyed by exact posted anchor name.
	 * @param bool                             $group_folded Whether the containing section already folds.
	 * @return string
	 */
	private static function render_fields( array $fields, string $section, string $group_id, array $previous, array $errors, array $city_searches, bool $group_folded = false ): string {
		$shown_html  = '';
		$folded_html = '';
		$folded_open = false;
		foreach ( $fields as $field ) {
			$key   = (string) $field['name'];
			$name  = $section === '' ? $key : $section . '[' . $key . ']';
			$value = $previous[ $key ] ?? '';
			$error = is_string( $errors[ $key ] ?? null ) ? $errors[ $key ] : '';
			$html  = ( $city_searches[ $name ] ?? '' ) . self::render_field( $name, $field, $value, $error );
			if ( $group_folded || empty( $field['fold'] ) ) {
				$shown_html .= $html;
				continue;
			}
			$folded_html .= $html;
			$folded_open  = $folded_open || ( is_scalar( $value ) && (string) $value !== '' ) || $error !== '';
		}
		if ( $folded_html !== '' ) {
			$shown_html .= self::open_group( $group_id . '-advanced', __( 'Advanced', 'roxyapi' ), true, $folded_open )
				. $folded_html . '</details>';
		}
		return $shown_html;
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
				foreach ( $enum as $index => $opt ) {
					$opt_str  = (string) $opt;
					$selected = $opt_str === $value_str ? ' selected' : '';
					$out     .= '<option value="' . esc_attr( $opt_str ) . '"' . $selected . '>'
						. esc_html( (string) ( $field['enum_labels'][ $index ] ?? ucwords( str_replace( array( '-', '_' ), ' ', $opt_str ) ) ) )
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
	 * Open a fieldset, or a `<details>` fold when the model requests it. A
	 * fold opens on its own when it holds a value or an error.
	 *
	 * @param string $id       DOM id of the group.
	 * @param string $label    Legend or summary text.
	 * @param bool   $fold     Whether the model folds this group.
	 * @param bool   $open     Whether a fold starts open.
	 * @return string
	 */
	private static function open_group( string $id, string $label, bool $fold, bool $open ): string {
		if ( $fold ) {
			return '<details class="roxyapi-form-section roxyapi-form-section--optional" id="' . esc_attr( $id ) . '"' . ( $open ? ' open' : '' ) . '>'
				. '<summary>' . esc_html( $label ) . '</summary>';
		}
		return '<fieldset class="roxyapi-form-section" id="' . esc_attr( $id ) . '">'
			. '<legend>' . esc_html( $label ) . '</legend>';
	}

	/**
	 * Render the visible city-search combobox. The matching JS attaches
	 * autocomplete behaviour and writes to the exact posted names inside
	 * the scope on selection. Without JS the field is a labelled hint and the
	 * coordinate inputs remain fully usable.
	 *
	 * @param string                $scope_id DOM id of the element holding the location fields.
	 * @param array<string, string> $fields   Exact posted names keyed by latitude, longitude and timezone.
	 * @param string                $group_id Distinguishes multiple location groups sharing a scope.
	 * @return string
	 */
	private static function render_city_search( string $scope_id, array $fields, string $group_id = '' ): string {
		$input_id = 'roxyapi-geocode-' . sanitize_html_class( $scope_id . ( $group_id !== '' ? '-' . $group_id : '' ) );
		$out      = '<p class="roxyapi-form-field roxyapi-form-geocode">';
		$out     .= '<label for="' . esc_attr( $input_id ) . '">' . esc_html__( 'Find by city', 'roxyapi' ) . '</label>';
		$out     .= '<input type="text" id="' . esc_attr( $input_id ) . '"'
			. ' class="roxyapi-form-geocode-input"'
			. ' placeholder="' . esc_attr__( 'Start typing a city name', 'roxyapi' ) . '"'
			. ' data-roxyapi-geocode="' . esc_attr( '#' . $scope_id ) . '"'
			. ' data-roxyapi-geocode-fields="' . esc_attr( (string) wp_json_encode( $fields ) ) . '"'
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
			: __( 'I consent to sending my inputs to generate this reading. See the Privacy Policy for details.', 'roxyapi' );

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
