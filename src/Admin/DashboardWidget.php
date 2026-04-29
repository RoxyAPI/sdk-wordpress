<?php
/**
 * Dashboard widget that nudges admins toward connecting Roxy or, once
 * connected, surfaces the five most-used hero shortcodes.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Support\ApiKey;

class DashboardWidget {

	private const WIDGET_ID = 'roxyapi_connection_widget';

	/**
	 * Register the widget. Capability checked before adding so non-admins
	 * never see it.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_dashboard_setup', array( self::class, 'maybe_add_widget' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	/**
	 * Add the widget if the current user can manage options.
	 *
	 * @return void
	 */
	public static function maybe_add_widget(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		wp_add_dashboard_widget(
			self::WIDGET_ID,
			esc_html__( 'Roxy connection', 'roxyapi' ),
			array( self::class, 'render' )
		);
	}

	/**
	 * Enqueue the admin CSS and JS on the dashboard screen so the widget
	 * picks up the same styles and clipboard JS as the Settings page.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue( string $hook ): void {
		if ( $hook !== 'index.php' ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		Assets::enqueue_admin_css();
		Assets::enqueue_admin_script();
	}

	/**
	 * Render the widget. Defensively re-checks capability before emitting
	 * any markup.
	 *
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings_url = admin_url( 'admin.php?page=' . SettingsPage::PAGE_SLUG );

		echo '<div class="roxyapi-widget">';
		if ( ! ApiKey::is_configured() ) {
			self::render_empty( $settings_url );
		} else {
			self::render_connected( $settings_url );
		}
		echo '</div>';
	}

	/**
	 * Render the empty state: nudge to connect.
	 *
	 * @param string $settings_url Absolute URL to the plugin settings page.
	 * @return void
	 */
	private static function render_empty( string $settings_url ): void {
		?>
		<div class="roxyapi-widget-empty">
			<p>
				<?php echo esc_html__( 'Roxy is not connected yet. Add your key to start rendering horoscopes, tarot pulls, and natal charts.', 'roxyapi' ); ?>
			</p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( $settings_url ); ?>">
					<?php echo esc_html__( 'Connect Roxy', 'roxyapi' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Render the connected state: top shortcodes plus link row.
	 *
	 * @param string $settings_url Absolute URL to the plugin settings page.
	 * @return void
	 */
	private static function render_connected( string $settings_url ): void {
		$shortcodes = Onboarding::widget_top_shortcodes();
		?>
		<div class="roxyapi-widget-connected">
			<p class="roxyapi-widget-status">
				<span class="roxyapi-check" aria-hidden="true">&#10003;</span>
				<span><?php echo esc_html__( 'Connected to Roxy', 'roxyapi' ); ?></span>
			</p>
			<ul class="roxyapi-shortcode-list">
				<?php foreach ( $shortcodes as $sample ) : ?>
					<li class="roxyapi-shortcode">
						<code class="roxyapi-shortcode-code"><?php echo esc_html( $sample['code'] ); ?></code>
						<button
							type="button"
							class="roxyapi-copy"
							data-roxyapi-copy="<?php echo esc_attr( $sample['code'] ); ?>"
							aria-label="<?php echo esc_attr( sprintf( /* translators: %s: shortcode label */ __( 'Copy %s shortcode', 'roxyapi' ), $sample['title'] ) ); ?>"
						>
							<?php echo esc_html__( 'Copy', 'roxyapi' ); ?>
						</button>
					</li>
				<?php endforeach; ?>
			</ul>
			<nav class="roxyapi-widget-links" aria-label="<?php echo esc_attr__( 'Roxy widget links', 'roxyapi' ); ?>">
				<a href="<?php echo esc_url( $settings_url ); ?>"><?php echo esc_html__( 'Open settings', 'roxyapi' ); ?></a>
				<a href="<?php echo esc_url( Onboarding::dashboard_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Open Roxy dashboard', 'roxyapi' ); ?></a>
			</nav>
		</div>
		<?php
	}
}
