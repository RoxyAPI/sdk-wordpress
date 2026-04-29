<?php
/**
 * Settings submenu page under Settings, RoxyAPI.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Support\ApiKey;
use RoxyAPI\Support\Templates;

class SettingsPage {

	public const PAGE_SLUG    = 'roxyapi';
	public const OPTION_NAME  = 'roxyapi_settings';
	public const OPTION_GROUP = 'roxyapi';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'add_menu' ) );
		// register_setting must fire on both admin_init and rest_api_init so the
		// option is recognised in both contexts. add_settings_section / field()
		// are admin-only and must NOT run during REST requests; calling them
		// there fatals because wp-admin/includes/template.php is not loaded.
		add_action( 'admin_init', array( self::class, 'register_setting' ) );
		add_action( 'rest_api_init', array( self::class, 'register_setting' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	public static function add_menu(): void {
		add_menu_page(
			esc_html__( 'Roxy', 'roxyapi' ),
			esc_html__( 'Roxy', 'roxyapi' ),
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render' ),
			'dashicons-star-filled',
			30
		);

		// Explicitly register the first submenu so we can rename it from the
		// auto-generated duplicate to "Connect" without losing the default
		// landing slug used by the post-activation redirect.
		add_submenu_page(
			self::PAGE_SLUG,
			esc_html__( 'Connect', 'roxyapi' ),
			esc_html__( 'Connect', 'roxyapi' ),
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render' )
		);

		add_submenu_page(
			self::PAGE_SLUG,
			esc_html__( 'Shortcodes', 'roxyapi' ),
			esc_html__( 'Shortcodes', 'roxyapi' ),
			'manage_options',
			ShortcodesPage::PAGE_SLUG,
			array( ShortcodesPage::class, 'render' )
		);

		// The Demo page renders live shortcode output for QA. It only ships in
		// non-production environments because (a) it burns API quota when run
		// across all 130 endpoints, (b) it exposes the full surface to anyone
		// with manage_options, and (c) the WordPress.org review guidelines
		// discourage admin pages that have no production purpose.
		if ( DemoPage::is_available() ) {
			add_submenu_page(
				self::PAGE_SLUG,
				esc_html__( 'Demo', 'roxyapi' ),
				esc_html__( 'Demo', 'roxyapi' ),
				'manage_options',
				DemoPage::PAGE_SLUG,
				array( DemoPage::class, 'render' )
			);
		}
	}

	/**
	 * Register the option with WordPress.
	 *
	 * Safe to run on both admin_init and rest_api_init: register_setting() is
	 * defined in core, not wp-admin.
	 *
	 * @return void
	 */
	public static function register_setting(): void {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			array(
				'type'              => 'object',
				'sanitize_callback' => array( SettingsFields::class, 'sanitize' ),
				'default'           => SettingsSchema::defaults(),
				'show_in_rest'      => false,
				// Hot, small option read on every API call via ApiKey::get();
				// autoload to avoid the per-request DB hit.
				'autoload'          => true,
			)
		);
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$is_configured = ApiKey::is_configured();
		$key_disabled  = ApiKey::is_defined_via_constant();

		echo '<div class="wrap roxyapi-settings">';

		settings_errors( self::OPTION_NAME );

		if ( ! $is_configured ) {
			$html = Templates::render(
				'admin-onboarding',
				array(
					'signup_url'     => Onboarding::signup_url(),
					'playground_url' => Onboarding::playground_url(),
					'samples'        => Onboarding::quickstart_samples(),
					'key_input'      => SettingsFields::api_key_input_html(),
					'key_help'       => SettingsFields::api_key_help_html(),
					'key_disabled'   => $key_disabled,
					'is_configured'  => false,
					'option_group'   => self::OPTION_GROUP,
				)
			);
		} else {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab selector on a manage_options page.
			$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'connect';
			if ( ! in_array( $active_tab, array( 'connect', 'branding', 'display', 'privacy', 'advanced' ), true ) ) {
				$active_tab = 'connect';
			}

			$html = Templates::render(
				'admin-connected',
				array(
					'active_tab'             => $active_tab,
					'attribution_input'      => SettingsFields::attribution_checkbox_html(),
					'consent_label_input'    => SettingsFields::consent_label_textarea_html(),
					'accent_color_input'     => SettingsFields::accent_color_input_html(),
					'display_language_input' => SettingsFields::display_language_input_html(),
					'disclaimer_show_input'  => SettingsFields::disclaimer_show_html(),
					'disclaimer_text_input'  => SettingsFields::disclaimer_text_html(),
					'form_title_input'       => SettingsFields::form_title_input_html(),
					'form_submit_input'      => SettingsFields::form_submit_input_html(),
					'cache_preset_input'     => SettingsFields::cache_preset_input_html(),
					'privacy_policy_url'     => admin_url( 'options-privacy.php' ),
					'samples'                => Onboarding::quickstart_samples(),
					'key_input'              => SettingsFields::api_key_input_html(),
					'key_help'               => SettingsFields::api_key_help_html(),
					'key_disabled'           => $key_disabled,
					'option_group'           => self::OPTION_GROUP,
					'docs_url'               => Onboarding::docs_url(),
					'support_url'            => Onboarding::support_url(),
					'dashboard_url'          => Onboarding::dashboard_url(),
				)
			);
		}

		// Templates assemble $html from pre-escaped fragments: every dynamic
		// scalar passes through esc_html / esc_attr / esc_url at the call
		// site, every form-control HTML blob passes through wp_kses() with a
		// per-control allowlist (input / select / option / textarea / label).
		// Wrapping the whole document in wp_kses_post() here strips form
		// controls (the post-content allowlist excludes input/select/textarea)
		// and would render the cache-preset select + attribution toggles as
		// plain text. The template-level escape contract is the source of
		// truth; trust it instead of double-filtering.
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- See contract above; admin-onboarding.php and admin-connected.php escape every value at the source.

		echo '</div>';
	}

	public static function enqueue( string $hook ): void {
		if ( $hook !== 'toplevel_page_' . self::PAGE_SLUG ) {
			return;
		}
		Assets::enqueue_admin_css();
		Assets::enqueue_admin_script();
	}
}
