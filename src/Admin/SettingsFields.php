<?php
/**
 * Settings field renderers and sanitize callback.
 *
 * Encrypts the API key before storing it. Empty input means do not change.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

use RoxyAPI\Support\ApiKey;
use RoxyAPI\Support\Encryption;

class SettingsFields {

	public static function section_intro(): void {
		echo '<p>' . esc_html__( 'Paste the API key from your RoxyAPI dashboard. The key is encrypted at rest before being stored.', 'roxyapi' ) . '</p>';
	}

	public static function field_api_key(): void {
		$is_constant = ApiKey::is_defined_via_constant();
		$masked      = ApiKey::masked();
		?>
		<input
			type="password"
			id="roxyapi_api_key"
			name="roxyapi_settings[api_key]"
			value=""
			autocomplete="off"
			class="regular-text"
			<?php disabled( $is_constant ); ?>
			placeholder="<?php echo $masked === '' ? esc_attr__( 'Paste your RoxyAPI key', 'roxyapi' ) : esc_attr( $masked ); ?>"
		/>
		<p class="description">
			<?php
			if ( $is_constant ) {
				esc_html_e( 'Defined via ROXYAPI_KEY constant in wp-config.php. The settings field is locked.', 'roxyapi' );
			} elseif ( $masked !== '' ) {
				esc_html_e( 'A key is saved. Paste a new one to replace it. Leave blank to keep the current key.', 'roxyapi' );
			} else {
				printf(
					/* translators: %s URL to RoxyAPI pricing page */
					esc_html__( 'No key yet? Get one at %s.', 'roxyapi' ),
					'<a href="https://roxyapi.com/pricing" target="_blank" rel="noopener">roxyapi.com/pricing</a>'
				);
			}
			?>
		</p>
		<?php
	}

	/**
	 * Sanitize settings input.
	 *
	 * @param mixed $input Raw settings input from the form.
	 * @return array<string, string|int>
	 */
	public static function sanitize( $input ): array {
		$existing = get_option( SettingsPage::OPTION_NAME, array() );
		if ( ! is_array( $existing ) ) {
			$existing = array();
		}
		$out = array();

		$raw_key = isset( $input['api_key'] ) ? sanitize_text_field( wp_unslash( $input['api_key'] ) ) : '';

		if ( $raw_key === '' ) {
			$out['api_key_encrypted'] = (string) ( $existing['api_key_encrypted'] ?? '' );
		} elseif ( ! preg_match( '/^[a-f0-9-]{36}\.[a-f0-9]{16}\.[A-Za-z0-9_-]+$/', $raw_key ) ) {
			add_settings_error(
				SettingsPage::OPTION_NAME,
				'invalid_api_key',
				esc_html__( 'API key format is invalid. Get a key at roxyapi.com.', 'roxyapi' )
			);
			$out['api_key_encrypted'] = (string) ( $existing['api_key_encrypted'] ?? '' );
		} else {
			$enc = Encryption::encrypt( $raw_key );
			if ( $enc === false ) {
				add_settings_error(
					SettingsPage::OPTION_NAME,
					'encryption_failed',
					esc_html__( 'Could not encrypt API key. Check that PHP openssl is installed.', 'roxyapi' )
				);
				$out['api_key_encrypted'] = (string) ( $existing['api_key_encrypted'] ?? '' );
			} else {
				$out['api_key_encrypted'] = (string) $enc;
			}
		}

		$out['cache_ttl'] = isset( $input['cache_ttl'] ) ? absint( $input['cache_ttl'] ) : HOUR_IN_SECONDS;
		return $out;
	}
}
