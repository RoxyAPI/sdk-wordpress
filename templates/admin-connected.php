<?php
/**
 * Template: connected view rendered when the API key is configured.
 *
 * Five tabs, deep-linkable via `?tab=connect|branding|display|privacy|advanced`.
 * Each tab is a server-rendered fragment; the active one is selected with
 * the `is-active` class. No JS reload — just a normal page load when the
 * site owner clicks a tab. Mobile collapses to one column via WP admin CSS.
 *
 * Pre-escaped variables surfaced from SettingsPage::render():
 *   string $attribution_input
 *   string $consent_label_input
 *   string $accent_color_input
 *   string $display_language_input
 *   string $disclaimer_show_input
 *   string $disclaimer_text_input
 *   string $form_title_input
 *   string $form_submit_input
 *   string $cache_preset_input
 *   string $key_input
 *   string $key_help
 *   bool   $key_disabled
 *   array  $samples
 *   string $option_group / $docs_url / $support_url / $dashboard_url
 *   string $privacy_policy_url
 *   string $active_tab
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
// Variables in this file are local-scope, not globals: templates receive
// them via Templates::render's `extract( $vars, EXTR_SKIP )` and block
// render.php files receive them by exact unprefixed name from the WP
// block API (core passes $attributes, $content, $block, $sign, $date,
// $period, $wrapper_attributes by contract). PHPCS's static analyzer
// cannot see the extract or the block-API contract; suppress here to
// keep Plugin Check's plugin_repo report clean without per-line ignores.

$attribution_input      = isset( $attribution_input ) ? (string) $attribution_input : '';
$consent_label_input    = isset( $consent_label_input ) ? (string) $consent_label_input : '';
$accent_color_input     = isset( $accent_color_input ) ? (string) $accent_color_input : '';
$display_language_input = isset( $display_language_input ) ? (string) $display_language_input : '';
$disclaimer_show_input  = isset( $disclaimer_show_input ) ? (string) $disclaimer_show_input : '';
$disclaimer_text_input  = isset( $disclaimer_text_input ) ? (string) $disclaimer_text_input : '';
$form_title_input       = isset( $form_title_input ) ? (string) $form_title_input : '';
$form_submit_input      = isset( $form_submit_input ) ? (string) $form_submit_input : '';
$cache_preset_input     = isset( $cache_preset_input ) ? (string) $cache_preset_input : '';
$privacy_policy_url     = isset( $privacy_policy_url ) ? (string) $privacy_policy_url : '';
$samples                = isset( $samples ) && is_array( $samples ) ? $samples : array();
$key_input              = isset( $key_input ) ? (string) $key_input : '';
$key_help               = isset( $key_help ) ? (string) $key_help : '';
$key_disabled           = ! empty( $key_disabled );
$option_group           = isset( $option_group ) ? (string) $option_group : 'roxyapi';
$docs_url               = isset( $docs_url ) ? (string) $docs_url : '';
$support_url            = isset( $support_url ) ? (string) $support_url : '';
$dashboard_url          = isset( $dashboard_url ) ? (string) $dashboard_url : '';
$active_tab             = isset( $active_tab ) ? (string) $active_tab : 'connect';

$roxy_tabs = array(
	'connect'  => __( 'Connect', 'roxyapi' ),
	'branding' => __( 'Branding', 'roxyapi' ),
	'display'  => __( 'Display', 'roxyapi' ),
	'privacy'  => __( 'Privacy', 'roxyapi' ),
	'advanced' => __( 'Advanced', 'roxyapi' ),
);
if ( ! isset( $roxy_tabs[ $active_tab ] ) ) {
	$active_tab = 'connect';
}

$kses_input    = \RoxyAPI\Admin\SettingsFields::input_kses_allowed();
$kses_textarea = array(
	'textarea' => array(
		'class'       => true,
		'name'        => true,
		'rows'        => true,
		'cols'        => true,
		'placeholder' => true,
		'id'          => true,
	),
);
$kses_select   = array(
	'select' => array(
		'class' => true,
		'name'  => true,
		'id'    => true,
	),
	'option' => array(
		'value'    => true,
		'selected' => true,
	),
);
?>
<h1 class="roxyapi-heading">
	<span class="roxyapi-setup"><?php echo esc_html__( 'Roxy is', 'roxyapi' ); ?></span>
	<span class="roxyapi-punchline"><?php echo esc_html__( 'connected.', 'roxyapi' ); ?></span>
</h1>

<div class="roxyapi-test-banner" id="roxyapi-test-banner" role="status" aria-live="polite"></div>

<nav class="roxyapi-tabs" role="tablist" aria-label="<?php echo esc_attr__( 'Roxy settings', 'roxyapi' ); ?>">
	<?php foreach ( $roxy_tabs as $slug => $label ) : ?>
		<a class="roxyapi-tab<?php echo $slug === $active_tab ? ' is-active' : ''; ?>"
			role="tab"
			aria-selected="<?php echo $slug === $active_tab ? 'true' : 'false'; ?>"
			href="
			<?php
			echo esc_url(
				add_query_arg(
					array(
						'page' => 'roxyapi',
						'tab'  => $slug,
					),
					admin_url( 'admin.php' )
				)
			);
			?>
					">
			<?php echo esc_html( $label ); ?>
		</a>
	<?php endforeach; ?>
</nav>

<?php if ( $active_tab === 'connect' ) : ?>
	<section class="roxyapi-tab-panel">
		<div class="roxyapi-connected-banner" role="status">
			<span class="roxyapi-check" aria-hidden="true">&#10003;</span>
			<span class="roxyapi-banner-body">
				<strong><?php echo esc_html__( 'Connected to Roxy.', 'roxyapi' ); ?></strong>
				<?php echo esc_html__( 'Drop a shortcode on any page to render a reading.', 'roxyapi' ); ?>
			</span>
		</div>

		<section class="roxyapi-section" id="roxyapi-shortcode-section">
			<h2 class="roxyapi-section-title"><?php echo esc_html__( 'Quick start', 'roxyapi' ); ?></h2>
			<p class="roxyapi-section-lede">
				<?php echo esc_html__( 'Three working shortcodes. Copy. Paste. Publish.', 'roxyapi' ); ?>
			</p>
			<ul class="roxyapi-shortcode-list">
				<?php foreach ( $samples as $sample ) : ?>
					<li class="roxyapi-shortcode">
						<code class="roxyapi-shortcode-code"><?php echo esc_html( $sample['code'] ); ?></code>
						<button
							type="button"
							class="roxyapi-copy"
							data-roxyapi-copy="<?php echo esc_attr( $sample['code'] ); ?>"
							aria-label="<?php echo esc_attr( sprintf( /* translators: %s: shortcode label */ __( 'Copy %s shortcode', 'roxyapi' ), $sample['label'] ) ); ?>"
						>
							<?php echo esc_html__( 'Copy', 'roxyapi' ); ?>
						</button>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>

		<section class="roxyapi-section">
			<h2 class="roxyapi-section-title"><?php echo esc_html__( 'Manage your key', 'roxyapi' ); ?></h2>
			<details class="roxyapi-manage-key">
				<summary><?php echo esc_html__( 'Replace key', 'roxyapi' ); ?></summary>
				<form method="post" action="options.php" class="roxyapi-form">
					<?php settings_fields( $option_group ); ?>
					<div class="roxyapi-key-input">
						<?php echo wp_kses( $key_input, $kses_input ); ?>
						<button type="submit" name="submit" class="button button-primary"<?php echo $key_disabled ? ' disabled' : ''; ?>>
							<?php echo esc_html__( 'Save key', 'roxyapi' ); ?>
						</button>
						<button type="button" class="button" data-roxyapi-test-connection>
							<?php echo esc_html__( 'Test Connection', 'roxyapi' ); ?>
						</button>
						<span class="roxyapi-test-connection-result" aria-live="polite"></span>
					</div>
					<p class="roxyapi-key-help">
						<?php echo wp_kses_post( $key_help ); ?>
					</p>
				</form>
			</details>
		</section>
	</section>

<?php elseif ( $active_tab === 'branding' ) : ?>
	<section class="roxyapi-tab-panel">
		<form method="post" action="options.php" class="roxyapi-form">
			<?php settings_fields( $option_group ); ?>
			<section class="roxyapi-section">
				<h2 class="roxyapi-section-title"><?php echo esc_html__( 'Brand accent', 'roxyapi' ); ?></h2>
				<p class="description">
					<?php echo esc_html__( 'A single accent colour for card titles, form buttons, and source links. Leave blank to inherit your theme.', 'roxyapi' ); ?>
				</p>
				<p>
					<?php echo wp_kses( $accent_color_input, $kses_input ); ?>
				</p>
			</section>

			<section class="roxyapi-section">
				<h2 class="roxyapi-section-title"><?php echo esc_html__( 'Reading language', 'roxyapi' ); ?></h2>
				<p class="description">
					<?php echo esc_html__( 'Language for visitor-facing reading text. Default follows the site language.', 'roxyapi' ); ?>
				</p>
				<p>
					<?php echo wp_kses( $display_language_input, $kses_select ); ?>
				</p>
			</section>

			<p>
				<button type="submit" name="submit" class="button button-primary">
					<?php echo esc_html__( 'Save branding', 'roxyapi' ); ?>
				</button>
			</p>
		</form>
	</section>

<?php elseif ( $active_tab === 'display' ) : ?>
	<section class="roxyapi-tab-panel">
		<form method="post" action="options.php" class="roxyapi-form">
			<?php settings_fields( $option_group ); ?>

			<section class="roxyapi-section">
				<h2 class="roxyapi-section-title"><?php echo esc_html__( 'Show source', 'roxyapi' ); ?></h2>
				<p class="roxyapi-attribution-row">
					<?php echo wp_kses( $attribution_input, $kses_input ); ?>
				</p>
				<p class="description">
					<?php echo esc_html__( 'A small line at the bottom of each card showing where the data comes from. Off by default.', 'roxyapi' ); ?>
				</p>
			</section>

			<section class="roxyapi-section">
				<h2 class="roxyapi-section-title"><?php echo esc_html__( 'Disclaimer', 'roxyapi' ); ?></h2>
				<p class="roxyapi-attribution-row">
					<?php echo wp_kses( $disclaimer_show_input, $kses_input ); ?>
				</p>
				<p class="description">
					<?php echo esc_html__( 'Append a disclaimer to every reading. Useful for jurisdictions that require an "entertainment only" line. Empty text falls back to the localised default.', 'roxyapi' ); ?>
				</p>
				<p class="roxyapi-attribution-row">
					<?php echo wp_kses( $disclaimer_text_input, $kses_textarea ); ?>
				</p>
			</section>

			<section class="roxyapi-section">
				<h2 class="roxyapi-section-title"><?php echo esc_html__( 'Visitor form copy', 'roxyapi' ); ?></h2>
				<p class="description">
					<?php echo esc_html__( 'Override the title and submit button shown above visitor forms. Leave blank to use the localised default.', 'roxyapi' ); ?>
				</p>
				<p>
					<label class="roxyapi-attribution-row">
						<strong><?php echo esc_html__( 'Form title', 'roxyapi' ); ?></strong>
					</label>
					<?php echo wp_kses( $form_title_input, $kses_input ); ?>
				</p>
				<p>
					<label class="roxyapi-attribution-row">
						<strong><?php echo esc_html__( 'Submit button text', 'roxyapi' ); ?></strong>
					</label>
					<?php echo wp_kses( $form_submit_input, $kses_input ); ?>
				</p>
			</section>

			<p>
				<button type="submit" name="submit" class="button button-primary">
					<?php echo esc_html__( 'Save display', 'roxyapi' ); ?>
				</button>
			</p>
		</form>
	</section>

<?php elseif ( $active_tab === 'privacy' ) : ?>
	<section class="roxyapi-tab-panel">
		<section class="roxyapi-section">
			<h2 class="roxyapi-section-title"><?php echo esc_html__( 'Visitor privacy', 'roxyapi' ); ?></h2>
			<p class="description">
				<?php echo esc_html__( 'Visitor forms collect birth date, time, and location to compute readings. Visitors must confirm consent before each submission. Edit the consent text below; leave blank for the localised default.', 'roxyapi' ); ?>
			</p>
			<form method="post" action="options.php" class="roxyapi-form">
				<?php settings_fields( $option_group ); ?>
				<p>
					<label class="roxyapi-attribution-row">
						<strong><?php echo esc_html__( 'Consent label shown next to the form checkbox', 'roxyapi' ); ?></strong>
					</label>
				</p>
				<p class="roxyapi-consent-label-row">
					<?php echo wp_kses( $consent_label_input, $kses_textarea ); ?>
				</p>
				<p class="description">
					<?php
					printf(
						/* translators: %s: link to WordPress Privacy Policy editor */
						esc_html__( 'The plugin auto-injects suggested privacy text into the WordPress Privacy Policy editor. Review or accept it at %s.', 'roxyapi' ),
						'<a href="' . esc_url( $privacy_policy_url ) . '">' . esc_html__( 'Settings &rarr; Privacy', 'roxyapi' ) . '</a>'
					);
					?>
				</p>
				<p>
					<button type="submit" name="submit" class="button button-primary">
						<?php echo esc_html__( 'Save privacy', 'roxyapi' ); ?>
					</button>
				</p>
			</form>
		</section>
	</section>

<?php elseif ( $active_tab === 'advanced' ) : ?>
	<section class="roxyapi-tab-panel">
		<section class="roxyapi-section">
			<h2 class="roxyapi-section-title"><?php echo esc_html__( 'Cache', 'roxyapi' ); ?></h2>
			<p class="description">
				<?php echo esc_html__( 'How long Roxy caches each reading on this site. Lower = fresher. Higher = saves API quota.', 'roxyapi' ); ?>
			</p>
			<form method="post" action="options.php" class="roxyapi-form">
				<?php settings_fields( $option_group ); ?>
				<p>
					<?php echo wp_kses( $cache_preset_input, $kses_select ); ?>
				</p>
				<p>
					<button type="submit" name="submit" class="button button-primary">
						<?php echo esc_html__( 'Save advanced', 'roxyapi' ); ?>
					</button>
				</p>
			</form>
		</section>
	</section>
<?php endif; ?>

<nav class="roxyapi-footer-links" aria-label="<?php echo esc_attr__( 'Roxy resources', 'roxyapi' ); ?>">
	<a href="<?php echo esc_url( $docs_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Docs', 'roxyapi' ); ?></a>
	<a href="<?php echo esc_url( $support_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Support', 'roxyapi' ); ?></a>
	<a href="<?php echo esc_url( $dashboard_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Open dashboard at roxyapi.com', 'roxyapi' ); ?></a>
</nav>
