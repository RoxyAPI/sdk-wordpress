<?php
/**
 * Template partial: the way to a first reading, shared by setup Step 1 and
 * the connected Quick start. Included by `admin-onboarding.php` and
 * `admin-connected.php`, so it reads their scope.
 *
 * Variables in scope:
 *   string $builder_url    Admin link to Create a reading.
 *   array  $samples        Quick-start shortcode samples (label + code).
 *   string $shortcodes_url Admin link to the full Shortcodes library page.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
// Local scope via the including template, see admin-onboarding.php.
?>
<a class="roxyapi-cta" href="<?php echo esc_url( $builder_url ); ?>">
	<?php echo esc_html__( 'Create a reading', 'roxyapi' ); ?>
	<span class="roxyapi-cta-arrow" aria-hidden="true">&rarr;</span>
</a>
<p class="roxyapi-step-aside roxyapi-quick-start-or"><?php echo esc_html__( 'Or copy a ready one.', 'roxyapi' ); ?></p>
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
<?php if ( '' !== $shortcodes_url ) : ?>
	<a class="roxyapi-cta roxyapi-cta--secondary" href="<?php echo esc_url( $shortcodes_url ); ?>">
		<?php echo esc_html__( 'Browse all shortcodes', 'roxyapi' ); ?>
		<span class="roxyapi-cta-arrow" aria-hidden="true">&rarr;</span>
	</a>
<?php endif; ?>
