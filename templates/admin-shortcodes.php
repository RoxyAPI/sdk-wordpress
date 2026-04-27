<?php
/**
 * Template: Shortcodes Library page.
 *
 * Variables in scope:
 *   array $domains     Tab list. Each item has tag, slug, count, accent.
 *   array $groups      Cards grouped by domain_slug, in brand domain order.
 *   int   $total_count Total shortcodes across all groups.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$domains      = isset( $domains ) && is_array( $domains ) ? $domains : array();
$groups       = isset( $groups ) && is_array( $groups ) ? $groups : array();
$total_count  = isset( $total_count ) ? (int) $total_count : 0;
$can_try      = class_exists( '\\RoxyAPI\\Admin\\DemoPage' ) && \RoxyAPI\Admin\DemoPage::is_available();
$try_base_url = $can_try ? admin_url( 'admin.php?page=' . \RoxyAPI\Admin\DemoPage::PAGE_SLUG ) : '';
?>
<h1 class="roxyapi-heading">
	<span class="roxyapi-setup"><?php echo esc_html__( 'Every reading.', 'roxyapi' ); ?></span>
	<span class="roxyapi-punchline">
		<?php
		echo esc_html(
			sprintf(
				/* translators: %d: total number of shortcodes available. */
				_n( '%d shortcode.', '%d shortcodes.', $total_count, 'roxyapi' ),
				$total_count
			)
		);
		?>
	</span>
</h1>

<p class="roxyapi-page-intro">
	<?php echo esc_html__( 'Browse, search, copy. Paste into any post, page, or widget.', 'roxyapi' ); ?>
</p>

<div class="roxyapi-library-controls">
	<label class="screen-reader-text" for="roxyapi-library-search">
		<?php echo esc_html__( 'Search shortcodes', 'roxyapi' ); ?>
	</label>
	<input
		type="search"
		id="roxyapi-library-search"
		class="roxyapi-library-search"
		placeholder="<?php echo esc_attr__( 'Search by name or description', 'roxyapi' ); ?>"
		aria-label="<?php echo esc_attr__( 'Search shortcodes', 'roxyapi' ); ?>"
		autocomplete="off"
	/>
	<nav class="roxyapi-library-tabs" role="tablist" aria-label="<?php echo esc_attr__( 'Shortcode domains', 'roxyapi' ); ?>">
		<button
			type="button"
			class="roxyapi-library-tab is-active"
			data-roxyapi-domain="all"
			role="tab"
			aria-selected="true"
		>
			<?php echo esc_html__( 'All', 'roxyapi' ); ?>
			<span class="count">(<?php echo esc_html( (string) $total_count ); ?>)</span>
		</button>
		<?php foreach ( $domains as $library_domain ) : ?>
			<button
				type="button"
				class="roxyapi-library-tab roxyapi-accent-<?php echo esc_attr( $library_domain['accent'] ); ?>"
				data-roxyapi-domain="<?php echo esc_attr( $library_domain['slug'] ); ?>"
				role="tab"
				aria-selected="false"
			>
				<?php echo esc_html( $library_domain['tag'] ); ?>
				<span class="count">(<?php echo esc_html( (string) $library_domain['count'] ); ?>)</span>
			</button>
		<?php endforeach; ?>
	</nav>
</div>

<div class="roxyapi-library-results" role="tabpanel">
	<?php foreach ( $groups as $domain_slug => $items ) : ?>
		<?php
		if ( empty( $items ) ) {
			continue;
		}
		$first = $items[0];
		?>
		<section class="roxyapi-library-group" data-roxyapi-group="<?php echo esc_attr( (string) $domain_slug ); ?>">
			<h2 class="roxyapi-library-group-title"><?php echo esc_html( $first['domain'] ); ?></h2>
			<div class="roxyapi-library-grid">
				<?php foreach ( $items as $item ) : ?>
					<?php
					$haystack = strtolower(
						$item['title'] . ' '
						. $item['description'] . ' '
						. $item['tag'] . ' '
						. $item['domain']
					);
					?>
					<article
						class="roxyapi-library-card roxyapi-accent-<?php echo esc_attr( $item['accent'] ); ?>"
						data-roxyapi-tags="<?php echo esc_attr( $haystack ); ?>"
					>
						<header class="roxyapi-library-card-header">
							<h3 class="roxyapi-library-card-title"><?php echo esc_html( $item['title'] ); ?></h3>
							<div class="roxyapi-library-card-meta">
								<span class="roxyapi-library-pill"><?php echo esc_html( $item['domain'] ); ?></span>
								<?php if ( ! empty( $item['hero'] ) ) : ?>
									<span class="roxyapi-library-badge"><?php echo esc_html__( 'Hero', 'roxyapi' ); ?></span>
								<?php endif; ?>
								<?php if ( ! empty( $item['block_only'] ) ) : ?>
									<span class="roxyapi-library-badge roxyapi-library-badge-form"><?php echo esc_html__( 'Form', 'roxyapi' ); ?></span>
								<?php endif; ?>
							</div>
						</header>
						<?php if ( ! empty( $item['description'] ) ) : ?>
							<p class="roxyapi-library-desc"><?php echo esc_html( $item['description'] ); ?></p>
						<?php endif; ?>
						<div class="roxyapi-library-code">
							<code><?php echo esc_html( $item['sample'] ); ?></code>
							<button
								type="button"
								class="roxyapi-copy"
								data-roxyapi-copy="<?php echo esc_attr( $item['sample'] ); ?>"
								aria-label="<?php echo esc_attr( sprintf( /* translators: %s: shortcode title */ __( 'Copy %s shortcode', 'roxyapi' ), $item['title'] ) ); ?>"
							>
								<?php echo esc_html__( 'Copy', 'roxyapi' ); ?>
							</button>
							<?php if ( $can_try && empty( $item['block_only'] ) ) : ?>
								<a
									class="button button-small roxyapi-try-link"
									href="<?php echo esc_url( add_query_arg( 'run', $item['tag'], $try_base_url ) ); ?>#row-<?php echo esc_attr( sanitize_html_class( $item['tag'] ) ); ?>"
									aria-label="<?php echo esc_attr( sprintf( /* translators: %s: shortcode title */ __( 'Try %s in the Demo page', 'roxyapi' ), $item['title'] ) ); ?>"
								>
									<?php echo esc_html__( 'Try it', 'roxyapi' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endforeach; ?>
</div>

<p class="roxyapi-library-empty" hidden>
	<?php echo esc_html__( 'No shortcodes match. Try a different domain or clear the search.', 'roxyapi' ); ?>
</p>
