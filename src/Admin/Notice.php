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

	private const META_KEY              = 'roxyapi_dismissed_setup';
	private const EXHAUSTED_TRANSIENT   = 'roxyapi_free_tier_exhausted_seen';
	private const EXHAUSTED_META_PREFIX = 'roxyapi_free_tier_dismissed_';

	/**
	 * Register the notice and its dismiss handler.
	 *
	 * @return void
	 */
	public static function register(): void {
		// Onboarding "installed but not yet connected" notice is off by default:
		// keyless installs work out of the box on the free daily allowance, so
		// the only admin notice we show is the free-tier-exhausted one below.
		// Restore it (with its dismiss script) via the
		// roxyapi_show_onboarding_notice filter. handle_dismiss stays registered
		// regardless, so dismissal keeps working whenever the notice is on.
		if ( apply_filters( 'roxyapi_show_onboarding_notice', false ) ) {
			add_action( 'admin_notices', array( self::class, 'maybe_show' ) );
			add_action( 'admin_enqueue_scripts', array( self::class, 'maybe_enqueue_dismiss' ) );
		}
		add_action( 'admin_notices', array( self::class, 'maybe_show_exhausted' ) );
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
			esc_html__( 'RoxyAPI', 'roxyapi' ),
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

	/**
	 * Whether the free-tier exhaustion notice should render on the current
	 * admin page. Surfaced only to admins who have not yet pasted a key, when
	 * the transient set by Client::error_from_response is present, and only
	 * once per UTC date per user. The notice uses the standard
	 * `notice-dismissible` JS to hide for the rest of the page session;
	 * persistence across page loads relies on the per-day user_meta flag,
	 * which the WordPress notice-dismiss handler does not write on its own.
	 * Acceptable: the daily transient will re-trigger the banner next time
	 * the SaaS returns `free_tier_exhausted`.
	 *
	 * @return bool
	 */
	private static function should_show_exhausted(): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		if ( ApiKey::is_configured() ) {
			return false;
		}
		if ( ! get_transient( self::EXHAUSTED_TRANSIENT ) ) {
			return false;
		}
		$today = gmdate( 'Y-m-d' );
		$flag  = get_user_meta( get_current_user_id(), self::EXHAUSTED_META_PREFIX . $today, true );
		if ( $flag === '1' ) {
			return false;
		}
		return true;
	}

	/**
	 * Render the free-tier exhaustion banner when conditions match.
	 *
	 * @return void
	 */
	public static function maybe_show_exhausted(): void {
		if ( ! self::should_show_exhausted() ) {
			return;
		}

		$settings_url = admin_url( 'admin.php?page=' . SettingsPage::PAGE_SLUG );
		$message      = sprintf(
			'%s <a href="%s">%s</a>.',
			esc_html__(
				'RoxyAPI has used up its free daily allowance. It resets each day, so readings return on their own. To remove the daily limit and keep every reading live now,',
				'roxyapi'
			),
			esc_url( $settings_url ),
			esc_html__( 'add your API key', 'roxyapi' )
		);

		if ( function_exists( 'wp_admin_notice' ) ) {
			wp_admin_notice(
				$message,
				array(
					'id'                 => 'roxyapi-free-tier-exhausted',
					'type'               => 'error',
					'dismissible'        => true,
					'paragraph_wrap'     => true,
					'additional_classes' => array( 'roxyapi-notice' ),
				)
			);
		} else {
			printf(
				'<div class="notice notice-error is-dismissible roxyapi-notice"><p>%s</p></div>',
				wp_kses_post( $message )
			);
		}
	}
}
