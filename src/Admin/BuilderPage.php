<?php
/**
 * Guided shortcode creation using the bundled reading manifest.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Generated\Builder\Manifest;

class BuilderPage {

	public const PAGE_SLUG = 'roxyapi-builder';

	public static function register(): void {
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap roxyapi-builder"><h1>' . esc_html__( 'Create a reading', 'roxyapi' ) . '</h1><div id="roxyapi-builder"></div></div>';
	}

	/**
	 * Load only bundled assets and metadata on the builder screen.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue( string $hook ): void {
		if ( $hook !== get_plugin_page_hookname( self::PAGE_SLUG, SettingsPage::PAGE_SLUG ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		Assets::enqueue_admin_css();
		wp_enqueue_style( 'roxyapi-builder', plugins_url( 'assets/css/builder.css', ROXYAPI_PLUGIN_FILE ), array( 'roxyapi-admin' ), ROXYAPI_VERSION );
		// Build the persistent city inputs before the shared combobox scans the DOM.
		wp_enqueue_script( 'roxyapi-builder', plugins_url( 'assets/js/builder.js', ROXYAPI_PLUGIN_FILE ), array(), ROXYAPI_VERSION, true );
		wp_enqueue_script( 'roxyapi-geocode' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only selection on a capability-checked page.
		$reading = isset( $_GET['reading'] ) && is_string( $_GET['reading'] ) ? sanitize_key( wp_unslash( $_GET['reading'] ) ) : '';
		wp_localize_script(
			'roxyapi-builder',
			'RoxyAPIBuilder',
			array(
				'manifest'  => Manifest::all(),
				'reading'   => $reading,
				'timezones' => timezone_identifiers_list( \DateTimeZone::ALL_WITH_BC ),
				'today'     => current_time( 'Y-m-d' ),
				'i18n'      => array(
					'intro'         => __( 'Answer a few questions and we write the shortcode for you. Then paste it into any page.', 'roxyapi' ),
					'pick'          => __( 'Pick a reading', 'roxyapi' ),
					'find'          => __( 'Find a reading', 'roxyapi' ),
					'searchHint'    => __( 'Try tarot, horoscope or birth chart', 'roxyapi' ),
					'who'           => __( 'Who fills in the details?', 'roxyapi' ),
					'details'       => __( 'Set the details', 'roxyapi' ),
					'display'       => __( 'Display', 'roxyapi' ),
					'finish'        => __( 'Copy your shortcode', 'roxyapi' ),
					/* translators: 1: current step number, 2: total number of steps. */
					'progress'      => __( 'Step %1$d of %2$d', 'roxyapi' ),
					'back'          => __( 'Back', 'roxyapi' ),
					'continue'      => __( 'Continue', 'roxyapi' ),
					'visitor'       => __( 'Ask my visitor instead', 'roxyapi' ),
					'set'           => __( 'Show a set reading instead', 'roxyapi' ),
					'visitorChoice' => __( 'Ask my visitor', 'roxyapi' ),
					'visitorHelp'   => __( 'Shows a form. Each visitor enters their own details.', 'roxyapi' ),
					'setChoice'     => __( 'Show a set reading', 'roxyapi' ),
					'setHelp'       => __( 'You enter the details once. Every visitor sees the same reading.', 'roxyapi' ),
					'formOnly'      => __( 'This reading always asks the visitor.', 'roxyapi' ),
					'noDetails'     => __( 'This reading needs no details. It shows straight away.', 'roxyapi' ),
					'more'          => __( 'More options', 'roxyapi' ),
					'needed'        => __( 'Needed before the shortcode works.', 'roxyapi' ),
					'written'       => __( 'Written interpretation', 'roxyapi' ),
					'show'          => __( 'Show', 'roxyapi' ),
					'hide'          => __( 'Hide', 'roxyapi' ),
					'inherit'       => __( 'Follow my site setting', 'roxyapi' ),
					'sections'      => __( 'Hide parts of this reading', 'roxyapi' ),
					'copy'          => __( 'Copy shortcode', 'roxyapi' ),
					'copied'        => __( 'Copied. Paste it into any page.', 'roxyapi' ),
					'copyFailed'    => __( 'Copy failed. Select the text and copy it.', 'roxyapi' ),
					'paste'         => __( 'In the block editor, add a Shortcode block and paste it there.', 'roxyapi' ),
					'city'          => __( 'City', 'roxyapi' ),
					'yes'           => __( 'Yes', 'roxyapi' ),
					'no'            => __( 'No', 'roxyapi' ),
				),
			)
		);
	}
}
