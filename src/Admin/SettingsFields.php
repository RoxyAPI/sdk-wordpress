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

class SettingsFields {

	public static function section_intro(): void {
		echo '<p>' . esc_html__( 'Paste the API key from your Roxy dashboard. The key is encrypted at rest before being stored.', 'roxyapi' ) . '</p>';
	}

	/**
	 * Render the API key field for the classic Settings API screen.
	 *
	 * @return void
	 */
	public static function field_api_key(): void {
		echo wp_kses( self::api_key_input_html(), self::input_kses_allowed() );
		?>
		<p class="description">
			<?php echo wp_kses_post( self::api_key_help_html() ); ?>
		</p>
		<p>
			<button type="button" class="button" data-roxyapi-test-connection<?php echo ApiKey::is_configured() ? '' : ' disabled'; ?>>
				<?php esc_html_e( 'Test Connection', 'roxyapi' ); ?>
			</button>
			<span class="roxyapi-test-connection-result" aria-live="polite"></span>
		</p>
		<?php
	}

	/**
	 * Allowed-tags map for `wp_kses()` covering the form-input HTML this
	 * class emits (`<input>`, `<label>`, `<span>`). Lets the templates pass
	 * pre-built input markup through `wp_kses()` so phpcs sees the canonical
	 * escape rather than needing a `phpcs:ignore`.
	 *
	 * @return array<string, array<string, bool>>
	 */
	public static function input_kses_allowed(): array {
		return array(
			'input' => array(
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
			'label' => array(
				'class' => true,
				'for'   => true,
			),
			'span'  => array( 'class' => true ),
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
		$placeholder = $masked === '' ? __( 'Paste your Roxy API key', 'roxyapi' ) : $masked;

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
	 * Pre-escaped HTML for the brand-accent colour input. Uses
	 * `wp-color-picker` for a native admin-friendly picker; the input is a
	 * plain text field at server level so missing JS gracefully degrades.
	 *
	 * @return string
	 */
	public static function accent_color_input_html(): string {
		$opts    = SettingsSchema::get_option();
		$current = (string) ( $opts['accent_color'] ?? '' );
		return sprintf(
			'<input type="text" id="roxyapi_accent_color" name="roxyapi_settings[accent_color]" value="%s" class="roxyapi-color-picker" placeholder="#000000" />',
			esc_attr( $current )
		);
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
	 * Pre-escaped HTML for the "Show disclaimer" checkbox.
	 *
	 * @return string
	 */
	public static function disclaimer_show_html(): string {
		$opts    = SettingsSchema::get_option();
		$current = ! empty( $opts['disclaimer_show'] );
		return sprintf(
			'<label class="roxyapi-attribution-toggle"><input type="checkbox" name="roxyapi_settings[disclaimer_show]" value="1"%s /> <span>%s</span></label>',
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
	 * for the visible "Powered by Roxy" credit, per WP.org guideline #10.
	 *
	 * @return string
	 */
	public static function attribution_checkbox_html(): string {
		$opts    = get_option( SettingsPage::OPTION_NAME, array() );
		$current = is_array( $opts ) && ! empty( $opts['attribution_show'] );
		return sprintf(
			'<label class="roxyapi-attribution-toggle">'
				. '<input type="checkbox" name="roxyapi_settings[attribution_show]" value="1"%s />'
				. ' <span>%s</span>'
				. '</label>',
			$current ? ' checked' : '',
			esc_html__( 'Show source line on each reading.', 'roxyapi' )
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
					$out[ $option_key ] = ! empty( $input[ $input_key ] );
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

		if ( ! preg_match( '/^[a-f0-9-]{36}\.[a-f0-9]{16}\.[A-Za-z0-9_-]+$/', $raw ) ) {
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
