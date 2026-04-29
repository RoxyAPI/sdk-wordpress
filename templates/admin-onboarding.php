<?php
/**
 * Template: 3-step onboarding view rendered when the API key is not configured.
 *
 * Variables in scope (extracted from the render call):
 *   string $signup_url     Pricing page link with onboarding UTM params.
 *   string $playground_url Free Playground link (test key pre-filled, no signup).
 *   array  $samples        Quick-start shortcode samples (label + code).
 *   string $key_input      Pre-escaped HTML for the API key input control.
 *   string $key_help       Pre-escaped HTML for the helper text under the input.
 *   bool   $key_disabled   Whether the key field is locked (constant defined).
 *   bool   $is_configured  Whether the API key is configured.
 *   string $option_group   Settings option group slug, for settings_fields().
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

$signup_url     = isset( $signup_url ) ? (string) $signup_url : '';
$playground_url = isset( $playground_url ) ? (string) $playground_url : '';
$samples        = isset( $samples ) && is_array( $samples ) ? $samples : array();
$key_input      = isset( $key_input ) ? (string) $key_input : '';
$key_help       = isset( $key_help ) ? (string) $key_help : '';
$key_disabled   = ! empty( $key_disabled );
$is_configured  = ! empty( $is_configured );
$option_group   = isset( $option_group ) ? (string) $option_group : 'roxyapi';
?>
<h1 class="roxyapi-heading">
	<span class="roxyapi-setup"><?php echo esc_html__( 'Connect Roxy in', 'roxyapi' ); ?></span>
	<span class="roxyapi-punchline"><?php echo esc_html__( '30 seconds.', 'roxyapi' ); ?></span>
</h1>
<p class="roxyapi-page-intro">
	<?php echo esc_html__( 'Drop daily horoscopes, tarot pulls, numerology readings, I Ching casts, and natal charts onto any WordPress page. One key. Three steps.', 'roxyapi' ); ?>
</p>

<div class="roxyapi-test-banner" id="roxyapi-test-banner" role="status" aria-live="polite"></div>

<form method="post" action="options.php" class="roxyapi-form">
	<?php settings_fields( $option_group ); ?>

	<div class="roxyapi-steps" role="list" aria-label="<?php echo esc_attr__( 'Roxy setup steps', 'roxyapi' ); ?>">
		<section class="roxyapi-step-card" role="listitem">
			<h2 class="roxyapi-step-title">
				<span class="roxyapi-setup"><?php echo esc_html__( 'Step 1.', 'roxyapi' ); ?></span>
				<span class="roxyapi-punchline"><?php echo esc_html__( 'Get your API key.', 'roxyapi' ); ?></span>
			</h2>
			<p class="roxyapi-step-body">
				<?php echo esc_html__( 'An API key connects this plugin to your Roxy account. One key covers every reading. Pick a plan at roxyapi.com to get yours.', 'roxyapi' ); ?>
			</p>
			<p class="roxyapi-step-aside">
				<?php
				printf(
					/* translators: %s: link to the RoxyAPI Playground */
					esc_html__( 'Want to try the endpoints first? %s. No signup needed.', 'roxyapi' ),
					sprintf(
						'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
						esc_url( $playground_url ),
						esc_html__( 'Open the Playground', 'roxyapi' )
					)
				);
				?>
			</p>
			<a class="roxyapi-cta" href="<?php echo esc_url( $signup_url ); ?>" target="_blank" rel="noopener noreferrer">
				<?php echo esc_html__( 'Sign up at roxyapi.com', 'roxyapi' ); ?>
				<span class="roxyapi-cta-arrow" aria-hidden="true">&rarr;</span>
			</a>
		</section>

		<section class="roxyapi-step-card" role="listitem">
			<h2 class="roxyapi-step-title">
				<span class="roxyapi-setup"><?php echo esc_html__( 'Step 2.', 'roxyapi' ); ?></span>
				<span class="roxyapi-punchline"><?php echo esc_html__( 'Paste it here.', 'roxyapi' ); ?></span>
			</h2>
			<p class="roxyapi-step-body">
				<?php echo esc_html__( 'Drop the key into the field below. The plugin encrypts it before saving. Your key never reaches the browser at render time.', 'roxyapi' ); ?>
			</p>
			<div class="roxyapi-key-input">
				<?php echo wp_kses( $key_input, \RoxyAPI\Admin\SettingsFields::input_kses_allowed() ); ?>
				<button type="submit" name="submit" class="button button-primary"<?php echo $key_disabled ? ' disabled' : ''; ?>>
					<?php echo esc_html__( 'Save key', 'roxyapi' ); ?>
				</button>
				<button type="button" class="button" data-roxyapi-test-connection<?php echo $is_configured ? '' : ' disabled'; ?>>
					<?php echo esc_html__( 'Test Connection', 'roxyapi' ); ?>
				</button>
				<span class="roxyapi-test-connection-result" aria-live="polite"></span>
			</div>
			<p class="roxyapi-key-help">
				<?php echo wp_kses_post( $key_help ); ?>
			</p>
		</section>

		<section class="roxyapi-step-card" id="roxyapi-shortcode-section" role="listitem">
			<h2 class="roxyapi-step-title">
				<span class="roxyapi-setup"><?php echo esc_html__( 'Step 3.', 'roxyapi' ); ?></span>
				<span class="roxyapi-punchline"><?php echo esc_html__( 'Drop a shortcode on a page.', 'roxyapi' ); ?></span>
			</h2>
			<p class="roxyapi-step-body">
				<?php echo esc_html__( 'Copy a shortcode below. Paste it into any post, page, or widget. Roxy renders the reading server side. No JavaScript required on the front end.', 'roxyapi' ); ?>
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
	</div>
</form>
