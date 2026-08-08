<?php
/**
 * Registration and lazy enqueue of the RoxyAPI web-component bundle.
 *
 * The chart, table, and card readings are rendered by the @roxyapi/ui custom
 * elements. The bundle is vendored locally at assets/js/roxy-ui.js and served
 * from the plugin directory, so there is no CDN dependency and no Subresource
 * Integrity hash to maintain. The handle is registered (not enqueued) on the
 * standard enqueue hooks. {@link UiBundle::enqueue()} is called per render by
 * {@link ComponentRenderer} so the bundle only ships on pages that actually
 * place a component. Footer scripts still emit because shortcode and block
 * render runs before the footer hook.
 *
 * Each component self-hydrates: on connect it reads its own JSON payload child
 * and assigns it to its data property, so no separate mount script is needed.
 *
 * The labels a component draws around that payload (tab names, column headings,
 * empty states) are English in the bundle and translated by a catalogue loaded
 * beside it, one small file per language, vendored the same way. Exactly one is
 * enqueued, chosen from the locale of the screen being rendered, so a Spanish
 * site reads Spanish sign names under Spanish headings rather than English ones.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UiBundle {

	/** Local handle for the vendored web-component bundle. */
	public const HANDLE = 'roxyapi-ui';

	/** Local handle for the vendored interface-label catalogue, when one applies. */
	public const LOCALE_HANDLE = 'roxyapi-ui-locale';

	/** Plugin-relative directory holding the vendored catalogues, one file per language. */
	private const LOCALE_DIR = 'assets/js/locales/';

	/**
	 * Hook the registration callbacks. Registration runs at priority 5 on the
	 * frontend so the handle exists before any shortcode or block calls
	 * {@link UiBundle::enqueue()} at the default priority 10. Block assets and
	 * admin contexts (editor previews, the Demo page) register too.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_enqueue_scripts', array( self::class, 'register_scripts' ), 5 );
		add_action( 'enqueue_block_assets', array( self::class, 'register_scripts' ), 5 );
		add_action( 'admin_enqueue_scripts', array( self::class, 'register_scripts' ), 5 );
		add_action( 'enqueue_block_assets', array( self::class, 'enqueue_in_editor' ), 10 );
	}

	/**
	 * Eager-enqueue the bundle inside the block editor so ServerSideRender
	 * previews hydrate.
	 *
	 * @remarks A block's editor preview is server-rendered through ServerSideRender, but the `<roxy-*>` custom elements only upgrade (chart, not the plain server fallback) if the bundle is present in the editor canvas iframe. `enqueue_block_assets` is the hook whose assets WordPress loads into that iframe (6.3+). It also fires on the front end, where the per-render {@link UiBundle::enqueue} keeps the bundle lazy, so this is guarded on `is_admin()` to avoid shipping it on every front-end page.
	 *
	 * @return void
	 */
	public static function enqueue_in_editor(): void {
		if ( is_admin() ) {
			self::enqueue();
		}
	}

	/**
	 * Register the vendored bundle handle, plus the catalogue handle on a site
	 * that has one. Idempotent: re-registering an existing handle is a WordPress
	 * no-op, so firing on three hooks is safe.
	 *
	 * @return void
	 */
	public static function register_scripts(): void {
		if ( ! wp_script_is( self::HANDLE, 'registered' ) ) {
			wp_register_script(
				self::HANDLE,
				plugins_url( 'assets/js/roxy-ui.js', ROXYAPI_PLUGIN_FILE ),
				array(),
				ROXYAPI_UI_VERSION,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}

		$locale = self::locale_code();
		if ( '' !== $locale && ! wp_script_is( self::LOCALE_HANDLE, 'registered' ) ) {
			wp_register_script(
				self::LOCALE_HANDLE,
				plugins_url( self::LOCALE_DIR . $locale . '.js', ROXYAPI_PLUGIN_FILE ),
				array( self::HANDLE ),
				ROXYAPI_UI_VERSION,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
	}

	/**
	 * Enqueue the bundle, plus the catalogue for this screen when there is one.
	 *
	 * Called per render so the assets only load on pages that place a component.
	 * The catalogue declares the bundle as its dependency, so WordPress prints it
	 * second whatever order the two enqueues arrive in.
	 *
	 * @remarks Both lines enqueue a handle that may not be registered yet, and that is deliberate. Measured on a block theme: a shortcode renders while `did_action( 'wp_enqueue_scripts' )` is still 0, so {@link UiBundle::register_scripts} has not run and NEITHER handle exists at this point. WordPress accepts a handle it has not seen and resolves the source when registration lands, which is the only reason the bundle has ever worked here. Asking `wp_script_is( ..., 'registered' )` first therefore reads as a safety check and is really a coin flip on render order: it was false on every front-end page, so the catalogue silently never shipped and every translated site kept English labels. Deciding from {@link UiBundle::locale_code} instead asks the same question registration asks, so the two cannot disagree whichever runs first.
	 *
	 * @return void
	 */
	public static function enqueue(): void {
		wp_enqueue_script( self::HANDLE );
		if ( '' !== self::locale_code() ) {
			wp_enqueue_script( self::LOCALE_HANDLE );
		}
	}

	/**
	 * Language code of the catalogue to load, or '' to leave the labels English.
	 *
	 * @remarks Read from {@link determine_locale()} rather than the display-language setting, because the catalogue is chosen by the components themselves from the `lang` attribute WordPress writes on `<html>`, and that follows the locale of the screen. Reading a different value here would enqueue a catalogue the components never ask for, which costs a download and translates nothing.
	 *
	 * Two results are ordinary rather than faults, and both fall back to English in silence: English itself has no catalogue, since the labels in the bundle are already English, and a language the vendored build does not carry has no file to load. Registering a handle for either would put a 404 on a visitor page.
	 *
	 * @return string
	 */
	private static function locale_code(): string {
		$code = Language::from_locale( determine_locale() );
		if ( '' === $code || 'en' === $code ) {
			return '';
		}

		$file = plugin_dir_path( ROXYAPI_PLUGIN_FILE ) . self::LOCALE_DIR . $code . '.js';
		return is_readable( $file ) ? $code : '';
	}
}
