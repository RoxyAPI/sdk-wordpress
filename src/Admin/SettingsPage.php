<?php
/**
 * Settings submenu page under Settings, RoxyAPI.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

class SettingsPage {

	public const PAGE_SLUG    = 'roxyapi';
	public const OPTION_NAME  = 'roxyapi_settings';
	public const OPTION_GROUP = 'roxyapi';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'add_menu' ) );
		add_action( 'admin_init', array( self::class, 'register_setting' ) );
		add_action( 'rest_api_init', array( self::class, 'register_setting' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	public static function add_menu(): void {
		add_options_page(
			esc_html__( 'RoxyAPI Settings', 'roxyapi' ),
			esc_html__( 'RoxyAPI', 'roxyapi' ),
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render' )
		);
	}

	public static function register_setting(): void {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			array(
				'type'              => 'object',
				'sanitize_callback' => array( SettingsFields::class, 'sanitize' ),
				'default'           => array(
					'api_key_encrypted' => '',
					'cache_ttl'         => HOUR_IN_SECONDS,
				),
				'show_in_rest'      => false,
			)
		);

		add_settings_section(
			'roxyapi_main',
			esc_html__( 'API Connection', 'roxyapi' ),
			array( SettingsFields::class, 'section_intro' ),
			self::PAGE_SLUG
		);

		add_settings_field(
			'api_key',
			esc_html__( 'API Key', 'roxyapi' ),
			array( SettingsFields::class, 'field_api_key' ),
			self::PAGE_SLUG,
			'roxyapi_main'
		);
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap roxyapi-settings">
			<h1><?php echo esc_html__( 'RoxyAPI Settings', 'roxyapi' ); ?></h1>
			<p>
				<?php echo esc_html__( 'Drop horoscopes, tarot, numerology, I Ching, natal charts, and more onto any page with shortcodes or Gutenberg blocks. Sign up at roxyapi.com to get an API key.', 'roxyapi' ); ?>
			</p>
			<form method="post" action="options.php">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public static function enqueue( string $hook ): void {
		if ( $hook !== 'settings_page_' . self::PAGE_SLUG ) {
			return;
		}
		wp_enqueue_style(
			'roxyapi-admin',
			plugins_url( 'assets/css/admin.css', ROXYAPI_PLUGIN_FILE ),
			array(),
			ROXYAPI_VERSION
		);
		wp_enqueue_script(
			'roxyapi-admin',
			plugins_url( 'assets/js/admin.js', ROXYAPI_PLUGIN_FILE ),
			array( 'wp-api-fetch' ),
			ROXYAPI_VERSION,
			true
		);
		wp_localize_script(
			'roxyapi-admin',
			'RoxyAPIAdmin',
			array(
				'restNonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
	}
}
