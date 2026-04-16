<?php
/**
 * Onboarding admin notice using wp_admin_notice() (WP 6.4+).
 *
 * Shows once per user when no API key is configured. Dismissible via
 * a per-user user_meta flag. The dismiss is persisted via an AJAX handler
 * so the notice stays hidden across page loads.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

use RoxyAPI\Support\ApiKey;

class Notice {

	private const META_KEY = 'roxyapi_dismissed_setup';

	/**
	 * Register the notice and its dismiss handler.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'admin_notices', array( self::class, 'maybe_show' ) );
		add_action( 'wp_ajax_roxyapi_dismiss_notice', array( self::class, 'handle_dismiss' ) );
	}

	/**
	 * Show the setup notice if conditions are met.
	 *
	 * @return void
	 */
	public static function maybe_show(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ApiKey::is_configured() ) {
			return;
		}
		$user_id = get_current_user_id();
		if ( get_user_meta( $user_id, self::META_KEY, true ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && $screen->id === 'settings_page_' . SettingsPage::PAGE_SLUG ) {
			return;
		}

		$message = sprintf(
			'<strong>%s</strong> %s <a href="%s">%s</a>.',
			esc_html__( 'RoxyAPI', 'roxyapi' ),
			esc_html__( 'is installed but not yet connected.', 'roxyapi' ),
			esc_url( admin_url( 'options-general.php?page=' . SettingsPage::PAGE_SLUG ) ),
			esc_html__( 'Add your API key', 'roxyapi' )
		);

		if ( function_exists( 'wp_admin_notice' ) ) {
			wp_admin_notice(
				$message,
				array(
					'id'                 => 'roxyapi-setup-notice',
					'type'               => 'info',
					'dismissible'        => true,
					'paragraph_wrap'     => true,
					'additional_classes' => array( 'roxyapi-notice' ),
				)
			);
		} else {
			printf(
				'<div class="notice notice-info is-dismissible roxyapi-notice"><p>%s</p></div>',
				wp_kses_post( $message )
			);
		}

		// Inline JS to persist the dismiss via AJAX.
		?>
		<script>
		jQuery( function( $ ) {
			$( document ).on( 'click', '#roxyapi-setup-notice .notice-dismiss', function() {
				$.post( ajaxurl, { action: 'roxyapi_dismiss_notice', _wpnonce: '<?php echo esc_js( wp_create_nonce( 'roxyapi_dismiss_notice' ) ); ?>' } );
			} );
		} );
		</script>
		<?php
	}

	/**
	 * AJAX handler to persist notice dismissal.
	 *
	 * @return void
	 */
	public static function handle_dismiss(): void {
		check_ajax_referer( 'roxyapi_dismiss_notice' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( '', '', array( 'response' => 403 ) );
		}
		update_user_meta( get_current_user_id(), self::META_KEY, '1' );
		wp_die();
	}
}
