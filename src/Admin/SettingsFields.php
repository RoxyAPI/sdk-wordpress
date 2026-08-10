<?php
/**
 * Settings field renderers and sanitize callback.
 *
 * Encrypts the API key before storing it. Empty input means do not change.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Support\ApiKey;
use RoxyAPI\Support\Encryption;
use RoxyAPI\Support\Theming;

class SettingsFields {

	/**
	 * Allowed-tags map for `wp_kses()` covering the form-control HTML this
	 * class emits (`<input>`, `<label>`, `<span>`, `<div>`, `<button>`). Lets
	 * the templates pass pre-built input markup through `wp_kses()` so phpcs
	 * sees the canonical escape rather than needing a `phpcs:ignore`.
	 *
	 * `style` is allowed on the swatch spans only in the sense that kses runs
	 * `safecss_filter_attr` over whatever it finds; the values themselves are
	 * HEX constants from `Theming`, never user input.
	 *
	 * @return array<string, array<string, bool>>
	 */
	public static function input_kses_allowed(): array {
		return array(
			'input'  => array(
				'type'         => true,
				'id'           => true,
				'name'         => true,
				'value'        => true,
				'class'        => true,
				'placeholder'  => true,
				'autocomplete' => true,
				'disabled'     => true,
				'checked'      => true,
				'min'          => true,
				'max'          => true,
				'step'         => true,
				'required'     => true,
			),
			'label'  => array(
				'class' => true,
				'for'   => true,
			),
			'span'   => array(
				'class'       => true,
				'style'       => true,
				'aria-hidden' => true,
			),
			'div'    => array( 'class' => true ),
			'button' => array(
				'type'  => true,
				'name'  => true,
				'value' => true,
				'class' => true,
			),
		);
	}

	/**
	 * Pre-escaped HTML for the API key input control alone.
	 *
	 * Used by both the legacy Settings API field and the new onboarding template.
	 *
	 * @return string
	 */
	public static function api_key_input_html(): string {
		$is_constant = ApiKey::is_defined_via_constant();
		$masked      = ApiKey::masked();
		$placeholder = $masked === '' ? __( 'Paste your RoxyAPI key', 'roxyapi' ) : $masked;

		return sprintf(
			'<input type="password" id="roxyapi_api_key" name="roxyapi_settings[api_key]" value="" autocomplete="off" class="regular-text roxyapi-key-field" placeholder="%s"%s />',
			esc_attr( $placeholder ),
			$is_constant ? ' disabled' : ''
		);
	}

	/**
	 * Pre-escaped HTML for the visitor-consent label textarea. Empty value
	 * means the renderer falls back to the localised default.
	 *
	 * @return string
	 */
	public static function consent_label_textarea_html(): string {
		$opts        = SettingsSchema::get_option();
		$current     = (string) ( $opts['consent_label'] ?? '' );
		$placeholder = __( 'Leave blank to use the localised default.', 'roxyapi' );
		return sprintf(
			'<textarea class="roxyapi-consent-label" name="roxyapi_settings[consent_label]" rows="3" cols="60" placeholder="%s">%s</textarea>',
			esc_attr( $placeholder ),
			esc_textarea( $current )
		);
	}

	/**
	 * Pre-escaped HTML for the palette picker: one card per shipped preset plus
	 * a Custom card, each showing the four colours it would apply.
	 *
	 * Radios rather than a `<select>` because the choice is visual: a
	 * practitioner picks a palette by looking at it, and a list of four names
	 * shows nothing. The swatches read their colours from the same constant the
	 * renderer uses, so a preview can never drift from what saving applies.
	 *
	 * @return string
	 */
	public static function palette_preset_input_html(): string {
		$opts    = SettingsSchema::get_option();
		$current = (string) ( $opts['palette_preset'] ?? '' );
		$labels  = self::preset_labels();

		$out = '<div class="roxyapi-palettes">';
		foreach ( array_merge( array( '' ), Theming::preset_names() ) as $name ) {
			$palette = Theming::preset( $name );
			$out    .= sprintf(
				'<label class="roxyapi-palette%s"><input type="radio" name="roxyapi_settings[palette_preset]" value="%s"%s /><span class="roxyapi-palette-swatches" aria-hidden="true">%s</span><span class="roxyapi-palette-name">%s</span></label>',
				$name === $current ? ' is-active' : '',
				esc_attr( $name ),
				$name === $current ? ' checked' : '',
				self::palette_swatches( $palette ),
				esc_html( $labels[ $name ] ?? $name )
			);
		}
		return $out . '</div>';
	}

	/**
	 * The four-colour strip shown on a palette card. An empty palette is the
	 * Custom card, which previews the site's own saved colours instead.
	 *
	 * @param array<string, array<string, string>> $palette Preset colours, or empty for Custom.
	 * @return string
	 */
	private static function palette_swatches( array $palette ): string {
		$colors = $palette['light'] ?? Theming::palette()['light'];
		$out    = '';
		foreach ( array( 'bg', 'surface', 'accent', 'fg' ) as $token ) {
			$value = isset( $colors[ $token ] ) ? sanitize_hex_color( (string) $colors[ $token ] ) : null;
			// No style attribute when the colour is unset, or the inline
			// declaration would beat the class that paints the "not set yet"
			// hatching and the swatch would read as a solid white choice.
			$out .= is_string( $value ) && $value !== ''
				? '<span class="roxyapi-swatch" style="background:' . esc_attr( $value ) . '"></span>'
				: '<span class="roxyapi-swatch is-unset"></span>';
		}
		return $out;
	}

	/**
	 * Pre-escaped HTML for the seven colour pickers, light and dark side by side.
	 *
	 * Every field is a plain text input at server level so the page still works
	 * with no JavaScript; `wp-color-picker` upgrades them in place. Disabled
	 * while a preset is selected, because the preset answers on its own and an
	 * editable field that has no effect is a lie. A disabled input posts nothing,
	 * which is exactly right: the sanitiser keeps the stored value.
	 *
	 * @return string
	 */
	public static function palette_colors_html(): string {
		$opts     = SettingsSchema::get_option();
		$disabled = (string) ( $opts['palette_preset'] ?? '' ) !== '';
		$labels   = self::token_labels();

		$out  = '<div class="roxyapi-color-grid">';
		$out .= '<span class="roxyapi-color-head"></span>';
		$out .= '<span class="roxyapi-color-head">' . esc_html__( 'Light', 'roxyapi' ) . '</span>';
		$out .= '<span class="roxyapi-color-head">' . esc_html__( 'Dark', 'roxyapi' ) . '</span>';

		foreach ( Theming::tokens() as $token ) {
			$out .= '<span class="roxyapi-color-label">' . esc_html( $labels[ $token ] ?? $token ) . '</span>';
			foreach ( array( 'light', 'dark' ) as $mode ) {
				$key  = Theming::option_key( $token, $mode );
				$out .= sprintf(
					'<input type="text" id="roxyapi_%1$s" name="roxyapi_settings[%1$s]" value="%2$s" class="roxyapi-color-picker" placeholder="%3$s"%4$s />',
					esc_attr( $key ),
					esc_attr( (string) ( $opts[ $key ] ?? '' ) ),
					esc_attr__( 'Default', 'roxyapi' ),
					$disabled ? ' disabled' : ''
				);
			}
		}
		return $out . '</div>';
	}

	/**
	 * Pre-escaped submit button that clears the palette back to the shipped
	 * defaults. A real control rather than "empty every field yourself":
	 * fourteen fields and a preset is not something to undo by hand.
	 *
	 * @return string
	 */
	public static function palette_reset_html(): string {
		return sprintf(
			'<button type="submit" name="roxyapi_settings[reset_palette]" value="1" class="button button-link roxyapi-palette-reset">%s</button>',
			esc_html__( 'Reset colours to defaults', 'roxyapi' )
		);
	}

	/**
	 * Display names for the shipped palettes. Kept beside the picker rather than
	 * in Theming so the renderer stays free of translated strings.
	 *
	 * @return array<string, string>
	 */
	private static function preset_labels(): array {
		return array(
			''             => __( 'Custom', 'roxyapi' ),
			'practitioner' => __( 'Practitioner', 'roxyapi' ),
			'eucalyptus'   => __( 'Eucalyptus', 'roxyapi' ),
			'kiln'         => __( 'Kiln', 'roxyapi' ),
			'moonlit'      => __( 'Moonlit', 'roxyapi' ),
		);
	}

	/**
	 * Display names for the seven colours, in the words a site owner uses.
	 *
	 * @return array<string, string>
	 */
	private static function token_labels(): array {
		return array(
			'accent'  => __( 'Accent', 'roxyapi' ),
			'bg'      => __( 'Page background', 'roxyapi' ),
			'surface' => __( 'Card background', 'roxyapi' ),
			'fg'      => __( 'Text', 'roxyapi' ),
			'muted'   => __( 'Secondary text', 'roxyapi' ),
			'border'  => __( 'Borders', 'roxyapi' ),
			'danger'  => __( 'Warnings', 'roxyapi' ),
		);
	}

	/**
	 * Pre-escaped `<select>` for the chart theme mode (Auto / Light / Dark).
	 * Auto follows the visitor operating system preference; light and dark
	 * force the choice on every rendered reading.
	 *
	 * @return string
	 */
	public static function theme_mode_input_html(): string {
		$opts    = SettingsSchema::get_option();
		$current = (string) ( $opts['theme_mode'] ?? 'light' );
		$choices = array(
			'auto'  => __( 'Auto: follow the visitor device setting', 'roxyapi' ),
			'light' => __( 'Light', 'roxyapi' ),
			'dark'  => __( 'Dark', 'roxyapi' ),
		);
		$out     = '<select id="roxyapi_theme_mode" name="roxyapi_settings[theme_mode]" class="roxyapi-select">';
		foreach ( $choices as $value => $label ) {
			$selected = $value === $current ? ' selected' : '';
			$out     .= '<option value="' . esc_attr( $value ) . '"' . $selected . '>' . esc_html( $label ) . '</option>';
		}
		$out .= '</select>';
		return $out;
	}

	/**
	 * Pre-escaped `<select>` for visitor-display language. Empty value
	 * means "match site language" (resolves via WP locale at request time).
	 *
	 * @return string
	 */
	public static function display_language_input_html(): string {
		$opts    = SettingsSchema::get_option();
		$current = (string) ( $opts['display_language'] ?? '' );
		$choices = array(
			''   => __( 'Match site language', 'roxyapi' ),
			'en' => __( 'English', 'roxyapi' ),
			'de' => __( 'German', 'roxyapi' ),
			'hi' => __( 'Hindi', 'roxyapi' ),
			'es' => __( 'Spanish', 'roxyapi' ),
			'tr' => __( 'Turkish', 'roxyapi' ),
			'pt' => __( 'Portuguese', 'roxyapi' ),
			'fr' => __( 'French', 'roxyapi' ),
			'ru' => __( 'Russian', 'roxyapi' ),
		);
		$out     = '<select id="roxyapi_display_language" name="roxyapi_settings[display_language]" class="roxyapi-select">';
		foreach ( $choices as $value => $label ) {
			$selected = $value === $current ? ' selected' : '';
			$out     .= '<option value="' . esc_attr( $value ) . '"' . $selected . '>' . esc_html( $label ) . '</option>';
		}
		$out .= '</select>';
		return $out;
	}

	/**
	 * Pre-escaped HTML for the "Hide written readings" checkbox.
	 *
	 * @return string
	 */
	public static function hide_readings_html(): string {
		$opts    = SettingsSchema::get_option();
		$current = ! empty( $opts['hide_readings'] );
		return sprintf(
			'%s<label class="roxyapi-attribution-toggle"><input type="checkbox" name="roxyapi_settings[hide_readings]" value="1"%s /> <span>%s</span></label>',
			self::checkbox_presence_field( 'hide_readings' ),
			$current ? ' checked' : '',
			esc_html__( 'Hide the written text on every reading. Charts, tables, and values still show.', 'roxyapi' )
		);
	}

	/**
	 * Pre-escaped HTML for the "Show disclaimer" checkbox.
	 *
	 * @return string
	 */
	public static function disclaimer_show_html(): string {
		$opts    = SettingsSchema::get_option();
		$current = ! empty( $opts['disclaimer_show'] );
		return sprintf(
			'%s<label class="roxyapi-attribution-toggle"><input type="checkbox" name="roxyapi_settings[disclaimer_show]" value="1"%s /> <span>%s</span></label>',
			self::checkbox_presence_field( 'disclaimer_show' ),
			$current ? ' checked' : '',
			esc_html__( 'Show a disclaimer line at the bottom of every reading.', 'roxyapi' )
		);
	}

	/**
	 * Pre-escaped HTML for the disclaimer text textarea.
	 *
	 * @return string
	 */
	public static function disclaimer_text_html(): string {
		$opts        = SettingsSchema::get_option();
		$current     = (string) ( $opts['disclaimer_text'] ?? '' );
		$placeholder = __( 'Leave blank to use the localised default ("For entertainment purposes only…").', 'roxyapi' );
		return sprintf(
			'<textarea class="roxyapi-disclaimer-text" name="roxyapi_settings[disclaimer_text]" rows="2" cols="60" placeholder="%s">%s</textarea>',
			esc_attr( $placeholder ),
			esc_textarea( $current )
		);
	}

	/**
	 * Pre-escaped HTML for the form-title text input.
	 *
	 * @return string
	 */
	public static function form_title_input_html(): string {
		$opts    = SettingsSchema::get_option();
		$current = (string) ( $opts['form_title'] ?? '' );
		return sprintf(
			'<input type="text" id="roxyapi_form_title" name="roxyapi_settings[form_title]" value="%s" class="regular-text" placeholder="%s" />',
			esc_attr( $current ),
			esc_attr__( 'Your reading', 'roxyapi' )
		);
	}

	/**
	 * Pre-escaped HTML for the form-submit-button-label text input.
	 *
	 * @return string
	 */
	public static function form_submit_input_html(): string {
		$opts    = SettingsSchema::get_option();
		$current = (string) ( $opts['form_submit_label'] ?? '' );
		return sprintf(
			'<input type="text" id="roxyapi_form_submit" name="roxyapi_settings[form_submit_label]" value="%s" class="regular-text" placeholder="%s" />',
			esc_attr( $current ),
			esc_attr__( 'Get reading', 'roxyapi' )
		);
	}

	/**
	 * Pre-escaped `<select>` for the cache TTL preset (Fresh / Balanced /
	 * Quota saver).
	 *
	 * @return string
	 */
	public static function cache_preset_input_html(): string {
		$opts    = SettingsSchema::get_option();
		$current = (string) ( $opts['cache_preset'] ?? 'balanced' );
		$choices = array(
			'fresh'       => __( 'Fresh: 15 minutes (most up-to-date, more API calls)', 'roxyapi' ),
			'balanced'    => __( 'Balanced: 1 hour (recommended)', 'roxyapi' ),
			'quota_saver' => __( 'Quota saver: 24 hours (fewer API calls, less fresh)', 'roxyapi' ),
		);
		$out     = '<select id="roxyapi_cache_preset" name="roxyapi_settings[cache_preset]" class="roxyapi-select">';
		foreach ( $choices as $value => $label ) {
			$selected = $value === $current ? ' selected' : '';
			$out     .= '<option value="' . esc_attr( $value ) . '"' . $selected . '>' . esc_html( $label ) . '</option>';
		}
		$out .= '</select>';
		return $out;
	}

	/**
	 * Pre-escaped HTML for the "Show attribution credit" checkbox + label.
	 * Used by the Settings page to give the site owner an explicit opt-in
	 * for the visible "Powered by RoxyAPI" credit, per WP.org guideline #10.
	 *
	 * @return string
	 */
	public static function attribution_checkbox_html(): string {
		$opts    = get_option( SettingsPage::OPTION_NAME, array() );
		$current = is_array( $opts ) && ! empty( $opts['attribution_show'] );
		return sprintf(
			'%s<label class="roxyapi-attribution-toggle">'
				. '<input type="checkbox" name="roxyapi_settings[attribution_show]" value="1"%s />'
				. ' <span>%s</span>'
				. '</label>',
			self::checkbox_presence_field( 'attribution_show' ),
			$current ? ' checked' : '',
			esc_html__( 'Show source line on each reading.', 'roxyapi' )
		);
	}

	/**
	 * The hidden half of a checkbox, emitted immediately before it.
	 *
	 * A browser posts nothing at all for an unticked box, so on its own the
	 * sanitiser cannot tell "the owner unticked this" from "this field is not on
	 * the form being saved". Every tab here is a separate form, so without this
	 * the second case looked like the first and saving Branding silently switched
	 * off the Display toggles. The pair makes presence mean "this form owns the
	 * field" and the value mean what the owner chose; PHP keeps the LAST value
	 * for a repeated key, so a ticked box overrides this `0`.
	 *
	 * Emit this for every new checkbox. A bare checkbox will look like it works
	 * and will quietly clear on an unrelated save.
	 *
	 * @param string $key Option key, which is also the input name.
	 * @return string Pre-escaped HTML.
	 */
	private static function checkbox_presence_field( string $key ): string {
		return sprintf(
			'<input type="hidden" name="roxyapi_settings[%s]" value="0" />',
			esc_attr( $key )
		);
	}

	/**
	 * Help text for the API key input.
	 *
	 * @return string Pre-escaped HTML.
	 */
	public static function api_key_help_html(): string {
		if ( ApiKey::is_defined_via_constant() ) {
			return esc_html__( 'Defined via ROXYAPI_KEY constant in wp-config.php. The settings field is locked.', 'roxyapi' );
		}
		if ( ApiKey::masked() !== '' ) {
			return esc_html__( 'A key is saved. Paste a new one to replace it. Leave blank to keep the current key.', 'roxyapi' );
		}
		return sprintf(
			/* translators: %s URL to RoxyAPI pricing page */
			esc_html__( 'No key yet? Get one at %s.', 'roxyapi' ),
			'<a href="https://roxyapi.com/pricing?utm_source=wp-plugin&utm_medium=onboarding&utm_campaign=v1" target="_blank" rel="noopener noreferrer">roxyapi.com</a>'
		);
	}

	/**
	 * Sanitize settings input.
	 *
	 * Iterates `SettingsSchema::fields()` and dispatches per declared type
	 * (`string` / `multiline` / `int` / `bool` / `encrypted_key`). Adding
	 * a new field is one entry in the schema, no edits here.
	 *
	 * The options.php form processor sanitises a POST twice (once explicitly
	 * via `sanitize_option()`, once inside `update_option()`). Each pass
	 * carries different keys, so every field's input lookup falls back to
	 * the existing stored value when missing — matching the legacy behaviour
	 * for the api_key flow.
	 *
	 * @param mixed $input Raw settings input from the form.
	 * @return array<string, string|int|bool>
	 */
	public static function sanitize( $input ): array {
		if ( ! is_array( $input ) ) {
			$input = array();
		}
		$existing = get_option( SettingsPage::OPTION_NAME, array() );
		if ( ! is_array( $existing ) ) {
			$existing = array();
		}

		$out = array();
		foreach ( SettingsSchema::fields() as $option_key => $field ) {
			$type      = (string) ( $field['type'] ?? 'string' );
			$input_key = (string) ( $field['input_key'] ?? $option_key );

			switch ( $type ) {
				case 'bool':
					// An unticked checkbox posts nothing, so "absent" has to carry
					// two meanings and the FORM is what tells them apart: every
					// checkbox is preceded by a hidden field of the same name, so
					// the key is always present on the tab that owns it (`0`
					// unticked, `1` ticked). Genuinely absent therefore means the
					// submitted form does not carry this field at all, and the
					// stored value is kept.
					//
					// Without that pairing every tab is its own form, so saving
					// Branding or Advanced rebuilt the option with all three
					// toggles false and silently switched off hide written
					// readings, show source and the disclaimer, none of which the
					// site owner had touched.
					if ( isset( $input[ $input_key ] ) ) {
						$out[ $option_key ] = ! empty( $input[ $input_key ] );
					} else {
						$out[ $option_key ] = ! empty( $existing[ $option_key ] );
					}
					break;

				case 'int':
					if ( isset( $input[ $input_key ] ) ) {
						$value = absint( $input[ $input_key ] );
						if ( isset( $field['min'] ) && $value < (int) $field['min'] ) {
							$value = (int) $field['min'];
						}
						if ( isset( $field['max'] ) && $value > (int) $field['max'] ) {
							$value = (int) $field['max'];
						}
						$out[ $option_key ] = $value;
					} else {
						$out[ $option_key ] = isset( $existing[ $option_key ] ) ? (int) $existing[ $option_key ] : (int) ( $field['default'] ?? 0 );
					}
					break;

				case 'multiline':
					if ( isset( $input[ $input_key ] ) ) {
						$out[ $option_key ] = sanitize_textarea_field( wp_unslash( $input[ $input_key ] ) );
					} else {
						$out[ $option_key ] = (string) ( $existing[ $option_key ] ?? $field['default'] ?? '' );
					}
					break;

				case 'enum':
					if ( isset( $input[ $input_key ] ) ) {
						$value              = sanitize_text_field( wp_unslash( $input[ $input_key ] ) );
						$enum               = isset( $field['enum'] ) && is_array( $field['enum'] ) ? $field['enum'] : array();
						$out[ $option_key ] = in_array( $value, $enum, true ) ? $value : (string) ( $field['default'] ?? '' );
					} else {
						$out[ $option_key ] = (string) ( $existing[ $option_key ] ?? $field['default'] ?? '' );
					}
					break;

				case 'color':
					if ( isset( $input[ $input_key ] ) ) {
						$value              = sanitize_hex_color( (string) wp_unslash( $input[ $input_key ] ) );
						$out[ $option_key ] = is_string( $value ) ? $value : '';
					} else {
						$out[ $option_key ] = (string) ( $existing[ $option_key ] ?? $field['default'] ?? '' );
					}
					break;

				case 'encrypted_key':
					$out[ $option_key ] = self::sanitize_encrypted_key( $input, $existing, $option_key, $input_key );
					break;

				case 'string':
				default:
					if ( isset( $input[ $input_key ] ) ) {
						$out[ $option_key ] = sanitize_text_field( wp_unslash( $input[ $input_key ] ) );
					} else {
						$out[ $option_key ] = (string) ( $existing[ $option_key ] ?? $field['default'] ?? '' );
					}
					break;
			}
		}

		return self::apply_palette( $input, $out );
	}

	/**
	 * Resolve the two palette controls the Branding tab posts beside the fields.
	 *
	 * Reset wins over everything and clears the preset plus all fourteen
	 * colours. Otherwise a selected preset copies its own HEX values over them,
	 * every save and not only the one that selected it, so switching back to
	 * Custom later starts from the palette that was on screen rather than from
	 * blank. Nothing here reads a colour out of the request: the values come from
	 * a constant, keyed by a name the schema has already narrowed to one of four.
	 *
	 * Reset is deliberately not a stored field. It is an action, and a stored one
	 * would blank the palette again on every subsequent save.
	 *
	 * @param array<string, mixed> $input Raw settings input from the form.
	 * @param array<string, mixed> $out   Sanitised output so far.
	 * @return array<string, mixed>
	 */
	private static function apply_palette( array $input, array $out ): array {
		if ( ! empty( $input['reset_palette'] ) ) {
			$out['palette_preset'] = '';
			foreach ( Theming::option_keys() as $key ) {
				$out[ $key ] = '';
			}
			return $out;
		}

		$palette = Theming::preset( (string) ( $out['palette_preset'] ?? '' ) );
		if ( $palette === array() ) {
			return $out;
		}

		foreach ( Theming::tokens() as $token ) {
			foreach ( array( 'light', 'dark' ) as $mode ) {
				$out[ Theming::option_key( $token, $mode ) ] = (string) ( $palette[ $mode ][ $token ] ?? '' );
			}
		}
		return $out;
	}

	/**
	 * Sanitise an encrypted-key field. Empty input keeps the existing
	 * encrypted value; bad format surfaces a notice and keeps existing;
	 * good format encrypts and returns the ciphertext. Behaviour matches
	 * the legacy api_key flow including the options.php double-sanitise
	 * second-pass case where `api_key_encrypted` arrives in the input.
	 *
	 * @param array<string,mixed> $input      Raw POST array.
	 * @param array<string,mixed> $existing   Currently stored option array.
	 * @param string              $option_key Stored key (e.g. `api_key_encrypted`).
	 * @param string              $input_key  POST key (e.g. `api_key`).
	 * @return string
	 */
	private static function sanitize_encrypted_key( array $input, array $existing, string $option_key, string $input_key ): string {
		$raw = isset( $input[ $input_key ] ) ? sanitize_text_field( wp_unslash( $input[ $input_key ] ) ) : '';

		if ( $raw === '' ) {
			// On the second sanitise pass the input carries the already-
			// encrypted value under the stored key; honour it before falling
			// back to the DB so we don't clobber a freshly-encrypted secret.
			if ( isset( $input[ $option_key ] ) && (string) $input[ $option_key ] !== '' ) {
				return (string) $input[ $option_key ];
			}
			return (string) ( $existing[ $option_key ] ?? '' );
		}

		// Optional prefix accepts current publishable/secret keys alongside older keys. Unknown prefixes stay rejected.
		if ( ! preg_match( '/^(?:(?:pk|sk)_(?:live|test)_)?[a-f0-9-]{36}\.[a-f0-9]{16}\.[A-Za-z0-9_-]+$/', $raw ) ) {
			self::add_settings_error_once(
				'invalid_api_key',
				esc_html__( 'API key format is invalid. Get a key at roxyapi.com.', 'roxyapi' )
			);
			return (string) ( $existing[ $option_key ] ?? '' );
		}

		$enc = Encryption::encrypt( $raw );
		if ( $enc === false ) {
			self::add_settings_error_once(
				'encryption_failed',
				esc_html__( 'Could not encrypt API key. Check that PHP openssl is installed.', 'roxyapi' )
			);
			return (string) ( $existing[ $option_key ] ?? '' );
		}
		return (string) $enc;
	}

	/**
	 * Register a settings error only if no error with the same code is already
	 * registered for this option in the current request.
	 *
	 * Guards against options.php double-sanitising a POST (once explicitly via
	 * sanitize_option(), once inside update_option()), which would otherwise
	 * surface duplicate notices.
	 *
	 * @param string $code    Unique slug for the error (per option, per request).
	 * @param string $message Pre-escaped message to display.
	 * @param string $type    Error type passed through to WordPress (default 'error').
	 * @return void
	 */
	private static function add_settings_error_once( string $code, string $message, string $type = 'error' ): void {
		foreach ( get_settings_errors( SettingsPage::OPTION_NAME ) as $existing ) {
			if ( isset( $existing['code'] ) && $existing['code'] === $code ) {
				return;
			}
		}
		add_settings_error( SettingsPage::OPTION_NAME, $code, $message, $type );
	}
}
