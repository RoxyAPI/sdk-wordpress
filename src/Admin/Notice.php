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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		add_action( 'admin_enqueue_scripts', array( self::class, 'maybe_enqueue_dismiss' ) );
		add_action( 'wp_ajax_roxyapi_dismiss_notice', array( self::class, 'handle_dismiss' ) );
	}

	/**
	 * Whether the onboarding notice should render on the current screen.
	 *
	 * @return bool
	 */
	private static function should_show(): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		if ( ApiKey::is_configured() ) {
			return false;
		}
		if ( get_user_meta( get_current_user_id(), self::META_KEY, true ) ) {
			return false;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && $screen->id === 'toplevel_page_' . SettingsPage::PAGE_SLUG ) {
			return false;
		}
		return true;
	}

	/**
	 * Enqueue the dismiss handler script on screens where the notice will render.
	 *
	 * @return void
	 */
	public static function maybe_enqueue_dismiss(): void {
		if ( ! self::should_show() ) {
			return;
		}
		wp_enqueue_script(
			'roxyapi-notice-dismiss',
			plugins_url( 'assets/js/admin-notice-dismiss.js', ROXYAPI_PLUGIN_FILE ),
			array(),
			ROXYAPI_VERSION,
			true
		);
		wp_localize_script(
			'roxyapi-notice-dismiss',
			'RoxyAPINotice',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'roxyapi_dismiss_notice' ),
			)
		);
	}

	/**
	 * Show the setup notice if conditions are met.
	 *
	 * @return void
	 */
	public static function maybe_show(): void {
		if ( ! self::should_show() ) {
			return;
		}

		$message = sprintf(
			'<strong>%s</strong> %s <a href="%s">%s</a>.',
			esc_html__( 'Roxy', 'roxyapi' ),
			esc_html__( 'is installed but not yet connected.', 'roxyapi' ),
			esc_url( admin_url( 'admin.php?page=' . SettingsPage::PAGE_SLUG ) ),
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
	}

	/**
	 * AJAX handler to persist notice dismissal.
	 *
	 * @return void
	 */
	public static function handle_dismiss(): void {
		check_ajax_referer( 'roxyapi_dismiss_notice', '_wpnonce', true );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( null, 403 );
		}
		update_user_meta( get_current_user_id(), self::META_KEY, '1' );
		wp_send_json_success();
	}
}
