<?php
/**
 * Demo: live preview of every shortcode the plugin ships.
 *
 * Maintainer-only surface (manage_options) for QA. Lists every entry from
 * Catalog::all() grouped by domain and lets you click "Run" on a single row to
 * render it live.
 *
 * **One row per click, and there is no batch.** Every render here is a metered
 * call, so a control that ran a whole domain, or all of them, spent real money
 * for one click on a page that ships to every site. The batch runners were
 * removed for that reason and `run_filter()` refuses their URL shapes as well,
 * because the trigger was a plain GET that outlives its button. Do not
 * reintroduce a run-many control here in any form.
 *
 * The page does NOT auto-run shortcodes. Each render is explicit, and the
 * underlying transient cache (default 1 hour TTL) covers any repeat visits.
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
		echo '<h1>' . esc_html__( 'RoxyAPI Demo', 'roxyapi' ) . '</h1>';
		echo '<p class="description">' . esc_html__( 'Live preview of every shortcode the plugin ships. Click "Run" on a row to render that one reading. Each run is a live call against your plan, so rows render one at a time and never in bulk. Results are cached server-side for an hour by default.', 'roxyapi' ) . '</p>';

		self::render_toolbar( $run );

		foreach ( $grouped as $slug => $rows ) {
			$domain_label = self::domain_label( $domains, $slug );
			echo '<h2 id="' . esc_attr( $slug ) . '" class="roxyapi-demo-domain">';
			echo esc_html( $domain_label );
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
	 * Render the top-of-page toolbar.
	 *
	 * @param string $run Current run filter ('' or a single shortcode tag).
	 * @return void
	 */
	private static function render_toolbar( string $run ): void {
		if ( $run === '' ) {
			return;
		}
		echo '<p class="roxyapi-demo-toolbar">';
		echo '<a class="button button-link-delete" href="' . esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ) . '">' . esc_html__( 'Clear renders', 'roxyapi' ) . '</a>';
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

		$should_run = self::should_run( $run, $tag );
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
	 * Exactly one row can match, by design. See the class docblock.
	 *
	 * @param string $run Filter value: '' or a single shortcode tag.
	 * @param string $tag Row shortcode tag.
	 * @return bool
	 */
	private static function should_run( string $run, string $tag ): bool {
		return $run !== '' && $run === $tag;
	}

	/**
	 * Read the `run` query var and validate it. One shortcode tag, and nothing else.
	 *
	 * Every reading on this page is a metered call, so a filter that matches more
	 * than one row spends real money per click. The batch shapes this used to
	 * accept (`all`, `heroes`, `domain:<slug>`) are rejected here rather than
	 * merely unlinked, because the trigger was a plain GET: an old bookmark, a
	 * browser history entry or a pasted URL would otherwise still run the batch
	 * with the buttons gone.
	 *
	 * @return string
	 */
	private static function run_filter(): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter on a maintainer-only page.
		$raw = isset( $_GET['run'] ) ? sanitize_text_field( wp_unslash( $_GET['run'] ) ) : '';
		if ( $raw === '' ) {
			return '';
		}
		$tag = preg_replace( '/[^a-z0-9_]/', '', $raw );
		if ( ! is_string( $tag ) || $tag === '' ) {
			return '';
		}

		// Whitelist against the registry rather than trusting the shape. The old
		// batch values happen to survive sanitising as tag-shaped strings that
		// match no row, which is safe only by accident; resolving them to '' makes
		// "one registered reading, or nothing" the property the code states.
		return shortcode_exists( $tag ) ? $tag : '';
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
		Assets::enqueue_admin_css();
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
