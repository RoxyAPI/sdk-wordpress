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
use RoxyAPI\Shortcodes\Registrar as ShortcodesRegistrar;
use RoxyAPI\Support\FormRouter;
use RoxyAPI\Support\Theming;
use RoxyAPI\Support\UiBundle;

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
		Bindings::register();
		FormRouter::register();
		UiBundle::register();
		Theming::register();

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

		// The @roxyapi/ui token defaults must load first. They define every
		// `--roxy-*` custom property on `:root` plus the automatic dark-mode
		// block the chart components read. Registering it as a dependency of
		// `roxyapi-frontend` means any `wp_enqueue_style( 'roxyapi-frontend' )`
		// pulls the token layer ahead of the plugin stylesheet, so the defaults
		// reach the document `:root` and inherit down into every component
		// shadow tree and every generic card.
		if ( ! wp_style_is( 'roxyapi-ui-tokens', 'registered' ) ) {
			wp_register_style(
				'roxyapi-ui-tokens',
				plugins_url( 'assets/css/roxy-ui-tokens.css', self::$plugin_file ),
				array(),
				ROXYAPI_UI_VERSION
			);
		}

		wp_register_style(
			'roxyapi-frontend',
			plugins_url( 'assets/css/frontend.css', self::$plugin_file ),
			array( 'roxyapi-ui-tokens' ),
			ROXYAPI_VERSION
		);

		// Override the default accent when the site owner set one. Custom
		// properties inherit downward, so the accent has to sit on `:root` (not
		// on a `.roxyapi-card` descendant) to reach the chart shadow trees. The
		// token layer pins a mode-specific accent at `:root[data-theme="dark"]`
		// (higher specificity than bare `:root`), so the override must match the
		// same theme states to win in light, dark, and auto alike. Attached to
		// `roxyapi-frontend`, which depends on the token layer, so the inline
		// rule also loads after the defaults.
		$opts  = \RoxyAPI\Admin\SettingsSchema::get_option();
		$color = sanitize_hex_color( (string) ( $opts['accent_color'] ?? '' ) );
		if ( is_string( $color ) && $color !== '' ) {
			wp_add_inline_style(
				'roxyapi-frontend',
				':root,:root[data-theme="light"],:root[data-theme="dark"]{--roxy-accent:' . $color . ';}'
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
		// Land new installs on the Shortcodes library, not Settings. The plugin
		// renders on a free daily allowance with no key, so the fastest path to
		// a working reading is the copy-paste catalog, not a key-entry form. The
		// key field stays one click away under the RoxyAPI menu for more headroom.
		wp_safe_redirect( admin_url( 'admin.php?page=' . ShortcodesPage::PAGE_SLUG ) );
		exit;
	}
}
