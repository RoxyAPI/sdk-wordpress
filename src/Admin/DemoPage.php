<?php
/**
 * Demo: live preview of every shortcode the plugin ships.
 *
 * Maintainer-only surface (manage_options) for QA. Lists every entry from
 * Catalog::all() grouped by domain, lets you click "Run" on any single row to
 * render it live, or "Run all in this domain" / "Run all" for batch QA.
 *
 * Quota-conscious: the page does NOT auto-run shortcodes. Each render is
 * explicit, and the underlying transient cache (default 1 hour TTL) covers
 * any repeat visits.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DemoPage {

	public const PAGE_SLUG = 'roxyapi-demo';

	/**
	 * True when the Demo page should be exposed in this environment.
	 *
	 * Hidden on production. Visible on local / development / staging, or
	 * whenever WP_DEBUG is on (covers wp-env, Local, and any developer who
	 * has explicitly enabled debug mode). `wp_get_environment_type()` is
	 * WordPress 5.5+ and returns 'production' by default.
	 *
	 * Site owners can force it on by setting the
	 * `WP_ENVIRONMENT_TYPE` constant or `WP_ENVIRONMENT_TYPE` env var to one
	 * of `local`, `development`, or `staging`.
	 *
	 * @return bool
	 */
	public static function is_available(): bool {
		$env = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production';
		if ( in_array( $env, array( 'local', 'development', 'staging' ), true ) ) {
			return true;
		}
		return defined( 'WP_DEBUG' ) && WP_DEBUG;
	}

	/**
	 * Register the admin_enqueue hook. The submenu entry itself is registered
	 * by SettingsPage::add_menu so the page nests under the same top-level menu.
	 *
	 * @return void
	 */
	public static function register(): void {
		if ( ! self::is_available() ) {
			return;
		}
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	/**
	 * Render the demo page.
	 *
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$run     = self::run_filter();
		$grouped = Catalog::grouped();
		$domains = Catalog::domains();

		echo '<div class="wrap roxyapi-demo">';
		echo '<h1>' . esc_html__( 'Roxy Demo', 'roxyapi' ) . '</h1>';
		echo '<p class="description">' . esc_html__( 'Live preview of every shortcode the plugin ships. Click "Run" on any row to render it. Results are cached server-side for an hour by default.', 'roxyapi' ) . '</p>';

		self::render_toolbar( $run );

		foreach ( $grouped as $slug => $rows ) {
			$domain_label = self::domain_label( $domains, $slug );
			echo '<h2 id="' . esc_attr( $slug ) . '" class="roxyapi-demo-domain">';
			echo esc_html( $domain_label );
			echo ' <a class="page-title-action" href="' . esc_url( self::run_url( 'domain:' . $slug ) ) . '">' . esc_html__( 'Run all in this domain', 'roxyapi' ) . '</a>';
			echo '</h2>';

			echo '<div class="roxyapi-demo-rows">';
			foreach ( $rows as $row ) {
				self::render_row( $row, $run );
			}
			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Render the top-of-page toolbar with batch run links.
	 *
	 * @param string $run Current run filter ('', 'all', 'heroes', 'domain:<slug>', or a single tag).
	 * @return void
	 */
	private static function render_toolbar( string $run ): void {
		echo '<p class="roxyapi-demo-toolbar">';
		echo '<a class="button button-primary" href="' . esc_url( self::run_url( 'all' ) ) . '">' . esc_html__( 'Run all', 'roxyapi' ) . '</a> ';
		echo '<a class="button" href="' . esc_url( self::run_url( 'heroes' ) ) . '">' . esc_html__( 'Run heroes only', 'roxyapi' ) . '</a> ';
		if ( $run !== '' ) {
			echo '<a class="button button-link-delete" href="' . esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ) . '">' . esc_html__( 'Clear renders', 'roxyapi' ) . '</a>';
		}
		echo '</p>';
	}

	/**
	 * Render a single shortcode row.
	 *
	 * @param array<string, mixed> $row Catalog row.
	 * @param string               $run Current run filter.
	 * @return void
	 */
	private static function render_row( array $row, string $run ): void {
		$tag       = (string) ( $row['tag'] ?? '' );
		$title     = (string) ( $row['title'] ?? $tag );
		$sample    = (string) ( $row['sample'] ?? '[' . $tag . ']' );
		$hero      = ! empty( $row['hero'] );
		$form_mode = ! empty( $row['block_only'] );
		$slug      = (string) ( $row['domain_slug'] ?? '' );

		$should_run = self::should_run( $run, $tag, $slug, $hero );
		$open_attr  = $should_run ? ' open' : '';

		$row_id = 'row-' . sanitize_html_class( $tag );
		echo '<details class="roxyapi-demo-row" id="' . esc_attr( $row_id ) . '"' . esc_attr( $open_attr ) . '>';
		echo '<summary>';
		echo '<span class="roxyapi-demo-title">' . esc_html( $title );
		if ( $hero ) {
			echo ' <span class="roxyapi-demo-badge">' . esc_html__( 'hero', 'roxyapi' ) . '</span>';
		}
		if ( $form_mode ) {
			echo ' <span class="roxyapi-demo-badge roxyapi-demo-badge-form">' . esc_html__( 'form', 'roxyapi' ) . '</span>';
		}
		echo '</span>';
		echo '<code class="roxyapi-demo-code">' . esc_html( $sample ) . '</code>';
		echo '<a class="button button-small" href="' . esc_url( self::run_url( $tag ) . '#' . $row_id ) . '">' . esc_html__( 'Run', 'roxyapi' ) . '</a>';
		echo '</summary>';

		echo '<div class="roxyapi-demo-output">';
		if ( $should_run ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- do_shortcode output is HTML emitted by our own renderer/templates.
			echo do_shortcode( $sample );
		} else {
			echo '<p class="description">' . esc_html__( 'Click Run above to render this shortcode live.', 'roxyapi' ) . '</p>';
		}
		echo '</div>';
		echo '</details>';
	}

	/**
	 * Decide whether a row should render live based on the current run filter.
	 *
	 * @param string $run    Filter value: '', 'all', 'heroes', 'domain:<slug>', or a tag.
	 * @param string $tag    Row shortcode tag.
	 * @param string $slug   Row domain slug.
	 * @param bool   $hero   Row is a hero entry.
	 * @return bool
	 */
	private static function should_run( string $run, string $tag, string $slug, bool $hero ): bool {
		if ( $run === '' ) {
			return false;
		}
		if ( $run === 'all' ) {
			return true;
		}
		if ( $run === 'heroes' ) {
			return $hero;
		}
		if ( strpos( $run, 'domain:' ) === 0 ) {
			return $slug === substr( $run, 7 );
		}
		return $run === $tag;
	}

	/**
	 * Read the `run` query var and validate it. Allowed shapes: 'all',
	 * 'heroes', 'domain:<slug>', or a single shortcode tag.
	 *
	 * @return string
	 */
	private static function run_filter(): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter on a maintainer-only page.
		$raw = isset( $_GET['run'] ) ? sanitize_text_field( wp_unslash( $_GET['run'] ) ) : '';
		if ( $raw === '' ) {
			return '';
		}
		if ( in_array( $raw, array( 'all', 'heroes' ), true ) ) {
			return $raw;
		}
		if ( strpos( $raw, 'domain:' ) === 0 ) {
			$slug = preg_replace( '/[^a-z0-9-]/', '', substr( $raw, 7 ) );
			return is_string( $slug ) && $slug !== '' ? 'domain:' . $slug : '';
		}
		// Treat anything else as a shortcode tag.
		$tag = preg_replace( '/[^a-z0-9_]/', '', $raw );
		return is_string( $tag ) ? $tag : '';
	}

	/**
	 * Build a run URL for a given filter value.
	 *
	 * @param string $value Run filter value.
	 * @return string
	 */
	private static function run_url( string $value ): string {
		return add_query_arg(
			array(
				'page' => self::PAGE_SLUG,
				'run'  => $value,
			),
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Resolve a domain slug back to its human label using the domain index.
	 *
	 * @param array<int, array{tag: string, slug: string, count: int, accent: string}> $domains Domain index from Catalog::domains().
	 * @param string                                                                   $slug    Domain slug to look up.
	 * @return string
	 */
	private static function domain_label( array $domains, string $slug ): string {
		foreach ( $domains as $domain ) {
			if ( ( $domain['slug'] ?? '' ) === $slug ) {
				return (string) $domain['tag'];
			}
		}
		return ucfirst( str_replace( '-', ' ', $slug ) );
	}

	/**
	 * Enqueue assets only on the demo page.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue( string $hook ): void {
		if ( $hook !== get_plugin_page_hookname( self::PAGE_SLUG, SettingsPage::PAGE_SLUG ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		wp_enqueue_style(
			'roxyapi-admin',
			plugins_url( 'assets/css/admin.css', ROXYAPI_PLUGIN_FILE ),
			array(),
			ROXYAPI_VERSION
		);
		// Reuse the frontend stylesheet so rendered cards look identical to the
		// front end. Plugin::register_frontend_style hooks admin_enqueue_scripts
		// to register the handle so this enqueue resolves.
		wp_enqueue_style( 'roxyapi-frontend' );

		// Tiny, scoped style block for the demo layout. Inline because it is
		// page-specific and trivial; not worth a second CSS file.
		$css = '
			.roxyapi-demo-toolbar { margin: 1rem 0 1.5rem; }
			.roxyapi-demo-domain { margin-top: 2rem; display: flex; align-items: center; gap: 0.5rem; }
			.roxyapi-demo-rows { display: flex; flex-direction: column; gap: 0.5rem; }
			.roxyapi-demo-row { background: #fff; border: 1px solid #dcdcde; border-radius: 6px; padding: 0.5rem 0.75rem; }
			.roxyapi-demo-row[open] { padding-bottom: 1rem; }
			.roxyapi-demo-row > summary { display: flex; align-items: center; gap: 0.75rem; cursor: pointer; list-style: none; padding: 0.25rem 0; }
			.roxyapi-demo-row > summary::-webkit-details-marker { display: none; }
			.roxyapi-demo-title { font-weight: 600; min-width: 14rem; }
			.roxyapi-demo-code { background: #f0f0f1; padding: 2px 6px; border-radius: 3px; font-size: 12px; flex: 1; overflow-x: auto; white-space: nowrap; }
			.roxyapi-demo-badge { font-size: 11px; background: #2271b1; color: #fff; padding: 1px 6px; border-radius: 3px; vertical-align: middle; margin-inline-start: 4px; }
			.roxyapi-demo-output { margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #f0f0f1; }
			.roxyapi-demo-output > .description { color: #646970; font-style: italic; }
		';
		wp_add_inline_style( 'roxyapi-admin', $css );
	}
}
