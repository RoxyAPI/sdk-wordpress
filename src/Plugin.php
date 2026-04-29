<?php
/**
 * Main plugin bootstrap.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Admin\ActionLinks;
use RoxyAPI\Admin\DashboardWidget;
use RoxyAPI\Admin\DemoPage;
use RoxyAPI\Admin\Notice;
use RoxyAPI\Admin\PrivacyPolicy;
use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Admin\ShortcodesPage;
use RoxyAPI\Api\GeocodeRoute;
use RoxyAPI\Api\TestKeyRoute;
use RoxyAPI\Blocks\Bindings;
use RoxyAPI\Blocks\Category;
use RoxyAPI\Blocks\Registrar as BlocksRegistrar;
use RoxyAPI\Shortcodes\LegacyAliases;
use RoxyAPI\Shortcodes\Registrar as ShortcodesRegistrar;
use RoxyAPI\Support\FormRouter;

class Plugin {

	private static string $plugin_file = '';

	public static function load( string $file ): void {
		self::$plugin_file = $file;

		register_activation_hook( $file, array( Activation::class, 'activate' ) );
		register_deactivation_hook( $file, array( Activation::class, 'deactivate' ) );

		add_action( 'plugins_loaded', array( self::class, 'boot' ) );
	}

	public static function boot(): void {
		SettingsPage::register();
		ShortcodesPage::register();
		DemoPage::register();
		Notice::register();
		PrivacyPolicy::register();
		ActionLinks::register();
		DashboardWidget::register();
		TestKeyRoute::register();
		GeocodeRoute::register();
		Category::register();
		BlocksRegistrar::register();
		ShortcodesRegistrar::register();
		LegacyAliases::register();
		Bindings::register();
		FormRouter::register();

		// Priority 5 so the handle is registered BEFORE shortcodes / blocks /
		// the Demo page call wp_enqueue_style('roxyapi-frontend') at the
		// default priority 10. Otherwise the enqueue silently no-ops because
		// the handle has not been registered yet.
		add_action( 'wp_enqueue_scripts', array( self::class, 'register_frontend_style' ), 5 );
		add_action( 'enqueue_block_assets', array( self::class, 'register_frontend_style' ), 5 );
		add_action( 'admin_enqueue_scripts', array( self::class, 'register_frontend_style' ), 5 );

		add_action( 'wp_enqueue_scripts', array( self::class, 'register_geocode_script' ), 5 );
		add_action( 'admin_enqueue_scripts', array( self::class, 'register_geocode_script' ), 5 );

		add_action( 'admin_init', array( self::class, 'maybe_redirect_after_activation' ) );
	}

	/**
	 * Register the shared frontend stylesheet under the `roxyapi-frontend`
	 * handle. Every shortcode and generated block calls
	 * `wp_enqueue_style( 'roxyapi-frontend' )` lazily so the file only ships on
	 * pages that actually render a reading. Registering on admin too lets the
	 * Demo page reuse the same look without a second enqueue path.
	 *
	 * @return void
	 */
	public static function register_frontend_style(): void {
		if ( wp_style_is( 'roxyapi-frontend', 'registered' ) ) {
			return;
		}
		wp_register_style(
			'roxyapi-frontend',
			plugins_url( 'assets/css/frontend.css', self::$plugin_file ),
			array(),
			ROXYAPI_VERSION
		);

		// Inject the brand-accent CSS variable when the site owner set one.
		// Layered as a custom property so theme.json tokens still drive
		// everything else; only accent surfaces in `frontend.css` consult
		// `--roxy-accent`.
		$opts  = \RoxyAPI\Admin\SettingsSchema::get_option();
		$color = (string) ( $opts['accent_color'] ?? '' );
		if ( $color !== '' ) {
			wp_add_inline_style(
				'roxyapi-frontend',
				':where(.roxyapi-card, .roxyapi-form) { --roxy-accent: ' . sanitize_hex_color( $color ) . '; }'
			);
		}
	}

	/**
	 * Register the geocode autocomplete script. The handle is registered (not
	 * enqueued) here so `FormRenderer::render()` can opt-in only on pages
	 * that actually render a form section with a lat/lon/tz triplet.
	 *
	 * Localised data carries the REST URL and a `wp_rest` nonce. The route
	 * itself is public, but the nonce keeps logged-in visitor traffic on the
	 * normal rate-limit bucket and lets caches identify "fresh" requests.
	 *
	 * @return void
	 */
	public static function register_geocode_script(): void {
		if ( wp_script_is( 'roxyapi-geocode', 'registered' ) ) {
			return;
		}
		wp_register_script(
			'roxyapi-geocode',
			plugins_url( 'assets/js/geocode-combobox.js', self::$plugin_file ),
			array(),
			ROXYAPI_VERSION,
			true
		);
		wp_localize_script(
			'roxyapi-geocode',
			'RoxyAPIGeocode',
			array(
				// Public route (`permission_callback => __return_true`). No nonce
				// is shipped because attaching one would imply a check the route
				// does not perform. Abuse is bounded by the per-IP rate-limit.
				'restUrl' => esc_url_raw( rest_url( 'roxyapi/v1/geocode' ) ),
				'i18n'    => array(
					'searching' => __( 'Searching cities…', 'roxyapi' ),
					'noResults' => __( 'No matching cities. Try a different spelling.', 'roxyapi' ),
					'selected'  => __( 'City selected.', 'roxyapi' ),
					'error'     => __( 'Search is unavailable. Enter coordinates manually.', 'roxyapi' ),
				),
			)
		);
	}

	/**
	 * One-shot redirect to the Settings page right after activation.
	 *
	 * Set by Activation::activate(). Skipped when WordPress just activated
	 * multiple plugins at once, when the request is an AJAX, REST, cron, or
	 * CLI request, or when the user lacks the manage_options capability.
	 *
	 * @return void
	 */
	public static function maybe_redirect_after_activation(): void {
		if ( ! get_transient( 'roxyapi_activated' ) ) {
			return;
		}

		// Check every guard BEFORE consuming the transient so that an AJAX/REST
		// request landing on admin_init doesn't eat the redirect intended for
		// the next real admin page load.
		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check on a WP-controlled query var.
		if ( isset( $_GET['activate-multi'] ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		delete_transient( 'roxyapi_activated' );
		wp_safe_redirect( admin_url( 'admin.php?page=' . SettingsPage::PAGE_SLUG ) );
		exit;
	}

	public static function file(): string {
		return self::$plugin_file;
	}
}
