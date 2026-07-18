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
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UiBundle {

	/** Local handle for the vendored web-component bundle. */
	public const HANDLE = 'roxyapi-ui';

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
	 * Register the vendored bundle handle. Idempotent: re-registering an
	 * existing handle is a WordPress no-op, so firing on three hooks is safe.
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
	}

	/**
	 * Enqueue the bundle. Called per render so the asset only loads on pages
	 * that place a component.
	 *
	 * @return void
	 */
	public static function enqueue(): void {
		wp_enqueue_script( self::HANDLE );
	}
}
