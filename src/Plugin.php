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
use RoxyAPI\Admin\SiteHealth;
use RoxyAPI\Api\GeocodeRoute;
use RoxyAPI\Api\TestKeyRoute;
use RoxyAPI\Blocks\Bindings;
use RoxyAPI\Blocks\Category;
use RoxyAPI\Blocks\Registrar as BlocksRegistrar;
use RoxyAPI\Shortcodes\Registrar as ShortcodesRegistrar;
use RoxyAPI\Support\EphemerisNav;
use RoxyAPI\Support\FormRouter;
use RoxyAPI\Support\LocaleFallback;
use RoxyAPI\Support\Theming;
use RoxyAPI\Support\UiBundle;

class Plugin {

	private static string $plugin_file = '';

	public static function load( string $file ): void {
		self::$plugin_file = $file;

		register_activation_hook( $file, array( Activation::class, 'activate' ) );
		register_deactivation_hook( $file, array( Activation::class, 'deactivate' ) );

		// Registered here rather than in boot(): a just-in-time textdomain load can fire
		// before plugins_loaded, and the filter has to already be in place when it does.
		LocaleFallback::register();

		// Priority 0 so the catalogue is in memory before anything translates a string.
		// FormRouter handles the visitor POST at init priority 5 and its validation
		// messages are the earliest translated output on a page.
		add_action( 'init', array( self::class, 'load_textdomain' ), 0 );

		add_action( 'plugins_loaded', array( self::class, 'boot' ) );
	}

	/**
	 * Put the bundled translation catalogue in memory.
	 *
	 * Two steps, each covering a failure the other does not.
	 *
	 * `load_plugin_textdomain()` no longer loads anything. Since WordPress 6.7 it records the
	 * directory, hands the actual load to the just-in-time resolver, and returns true whether or
	 * not a catalogue exists, so its return value proves nothing. It is still required: before
	 * WordPress 6.8 nothing else records that directory (6.8 added a pass over the `Text Domain`
	 * and `Domain Path` headers during boot), so the resolver finds no path, never reaches
	 * `load_textdomain()`, and every non-English site renders English. Measured on clean 6.7.2
	 * and 7.0.3 installs, not inferred.
	 *
	 * The explicit load is what makes the outcome deterministic. Just-in-time resolution runs
	 * through `lang_dir_for_domain`, `pre_get_language_files_from_path` and the unloaded-domain
	 * register, any one of which a theme or plugin can intercept, and the failure is silent.
	 * Measured: adding a single filter that returns false for our domain renders the whole
	 * interface in English on 6.7.2 and 7.0.3 alike when this call is absent. Resolving the file
	 * here and loading it removes that dependency entirely.
	 *
	 * Hooked on `init` rather than earlier because loading before then suppresses the very
	 * filters translations rely on, which WordPress reports as a doing_it_wrong.
	 *
	 * @return void
	 */
	public static function load_textdomain(): void {
		load_plugin_textdomain( 'roxyapi', false, dirname( plugin_basename( self::$plugin_file ) ) . '/languages' );

		if ( is_textdomain_loaded( 'roxyapi' ) ) {
			return;
		}

		$locale = determine_locale();
		$file   = LocaleFallback::catalogue_for( $locale );
		if ( '' !== $file ) {
			load_textdomain( 'roxyapi', $file, $locale );
		}
	}

	public static function boot(): void {
		SettingsPage::register();
		ShortcodesPage::register();
		DemoPage::register();
		Notice::register();
		PrivacyPolicy::register();
		SiteHealth::register();
		ActionLinks::register();
		DashboardWidget::register();
		TestKeyRoute::register();
		GeocodeRoute::register();
		Category::register();
		BlocksRegistrar::register();
		ShortcodesRegistrar::register();
		Bindings::register();
		FormRouter::register();
		EphemerisNav::register();
		UiBundle::register();
		Theming::register();

		// Priority 5 so the handle is registered BEFORE shortcodes / blocks /
		// the Demo page call wp_enqueue_style('roxyapi-frontend') at the
		// default priority 10. Otherwise the enqueue silently no-ops because
		// the handle has not been registered yet.
		add_action( 'wp_enqueue_scripts', array( self::class, 'register_frontend_style' ), 5 );
		add_action( 'enqueue_block_assets', array( self::class, 'register_frontend_style' ), 5 );
		add_action( 'admin_enqueue_scripts', array( self::class, 'register_frontend_style' ), 5 );
		add_action( 'enqueue_block_assets', array( self::class, 'enqueue_style_in_editor' ), 10 );

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

		// Carry the site owner's palette onto the page. Custom properties inherit
		// downward, so the values have to sit on `:root` (not on a
		// `.roxyapi-card` descendant) to reach the chart shadow trees. Attached
		// to `roxyapi-frontend`, which depends on the token layer, so the
		// override also loads after the defaults it replaces. The stylesheet
		// itself, including which theme states each block has to match, is
		// {@link Theming::inline_css}.
		wp_add_inline_style( 'roxyapi-frontend', Theming::inline_css( is_admin() ) );
	}

	/**
	 * Put the reading stylesheet in the block editor canvas.
	 *
	 * Front-end pages enqueue it per render so it only ships where a reading is,
	 * but a block preview is server-rendered through a REST request whose
	 * enqueues never reach the editor. `enqueue_block_assets` is the hook whose
	 * assets WordPress loads into the canvas iframe, so without this the preview
	 * shows unstyled markup and none of the owner's palette.
	 *
	 * @return void
	 */
	public static function enqueue_style_in_editor(): void {
		if ( is_admin() ) {
			wp_enqueue_style( 'roxyapi-frontend' );
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
