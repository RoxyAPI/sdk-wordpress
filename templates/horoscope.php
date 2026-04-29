<?php
/**
 * Template: daily horoscope card.
 *
 * Variables in scope (extracted from the render call):
 *   string $sign  Zodiac slug, one of the 12 signs
 *   string $date  Date string ("today", "tomorrow", or YYYY-MM-DD)
 *   array  $data  Response from GET /api/v2/horoscope-api/daily
 *
 * Response fields (verified against packages/astrology-western/src/routes/horoscope.ts):
 *   overview, love, career, health, finance, advice (strings)
 *   luckyNumber (int), luckyColor (string), energyRating (int 1-10)
 *   compatibleSigns (string[]), moonSign (string), moonPhase (string)
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

$sign          = isset( $sign ) ? (string) $sign : 'aries';
$data          = isset( $data ) && is_array( $data ) ? $data : array();
$overview      = (string) ( $data['overview'] ?? '' );
$love          = (string) ( $data['love'] ?? '' );
$career        = (string) ( $data['career'] ?? '' );
$health        = (string) ( $data['health'] ?? '' );
$finance       = (string) ( $data['finance'] ?? '' );
$advice        = (string) ( $data['advice'] ?? '' );
$lucky_number  = isset( $data['luckyNumber'] ) ? (int) $data['luckyNumber'] : null;
$lucky_color   = (string) ( $data['luckyColor'] ?? '' );
$energy_rating = isset( $data['energyRating'] ) ? (int) $data['energyRating'] : null;
$moon_sign     = (string) ( $data['moonSign'] ?? '' );
$moon_phase    = (string) ( $data['moonPhase'] ?? '' );
$compatible    = isset( $data['compatibleSigns'] ) && is_array( $data['compatibleSigns'] ) ? $data['compatibleSigns'] : array();
?>
<article class="roxyapi-horoscope roxyapi-horoscope-daily" data-sign="<?php echo esc_attr( $sign ); ?>">
	<header class="roxyapi-horoscope__header">
		<h3 class="roxyapi-horoscope__sign"><?php echo esc_html( ucfirst( $sign ) ); ?></h3>
		<?php if ( $moon_sign !== '' || $moon_phase !== '' ) : ?>
			<p class="roxyapi-horoscope__moon">
				<?php if ( $moon_sign !== '' ) : ?>
					<span><?php echo esc_html__( 'Moon in', 'roxyapi' ); ?> <?php echo esc_html( $moon_sign ); ?></span>
				<?php endif; ?>
				<?php if ( $moon_phase !== '' ) : ?>
					<span><?php echo esc_html( $moon_phase ); ?></span>
				<?php endif; ?>
			</p>
		<?php endif; ?>
	</header>

	<?php if ( $overview !== '' ) : ?>
		<p class="roxyapi-horoscope__overview"><?php echo esc_html( $overview ); ?></p>
	<?php endif; ?>

	<dl class="roxyapi-horoscope__details">
		<?php if ( $love !== '' ) : ?>
			<dt><?php echo esc_html__( 'Love', 'roxyapi' ); ?></dt>
			<dd><?php echo esc_html( $love ); ?></dd>
		<?php endif; ?>
		<?php if ( $career !== '' ) : ?>
			<dt><?php echo esc_html__( 'Career', 'roxyapi' ); ?></dt>
			<dd><?php echo esc_html( $career ); ?></dd>
		<?php endif; ?>
		<?php if ( $health !== '' ) : ?>
			<dt><?php echo esc_html__( 'Health', 'roxyapi' ); ?></dt>
			<dd><?php echo esc_html( $health ); ?></dd>
		<?php endif; ?>
		<?php if ( $finance !== '' ) : ?>
			<dt><?php echo esc_html__( 'Finance', 'roxyapi' ); ?></dt>
			<dd><?php echo esc_html( $finance ); ?></dd>
		<?php endif; ?>
		<?php if ( $advice !== '' ) : ?>
			<dt><?php echo esc_html__( 'Advice', 'roxyapi' ); ?></dt>
			<dd><?php echo esc_html( $advice ); ?></dd>
		<?php endif; ?>
	</dl>

	<footer class="roxyapi-horoscope__footer">
		<ul class="roxyapi-horoscope__stats">
			<?php if ( $lucky_number !== null ) : ?>
				<li>
					<span class="roxyapi-horoscope__stat-label"><?php echo esc_html__( 'Lucky number', 'roxyapi' ); ?></span>
					<span class="roxyapi-horoscope__stat-value"><?php echo esc_html( (string) $lucky_number ); ?></span>
				</li>
			<?php endif; ?>
			<?php if ( $lucky_color !== '' ) : ?>
				<li>
					<span class="roxyapi-horoscope__stat-label"><?php echo esc_html__( 'Lucky color', 'roxyapi' ); ?></span>
					<span class="roxyapi-horoscope__stat-value"><?php echo esc_html( $lucky_color ); ?></span>
				</li>
			<?php endif; ?>
			<?php if ( $energy_rating !== null ) : ?>
				<li>
					<span class="roxyapi-horoscope__stat-label"><?php echo esc_html__( 'Energy', 'roxyapi' ); ?></span>
					<span class="roxyapi-horoscope__stat-value"><?php echo esc_html( (string) $energy_rating ); ?>/10</span>
				</li>
			<?php endif; ?>
		</ul>
		<?php if ( ! empty( $compatible ) ) : ?>
			<p class="roxyapi-horoscope__compatible">
				<span class="roxyapi-horoscope__stat-label"><?php echo esc_html__( 'Compatible with', 'roxyapi' ); ?></span>
				<?php echo esc_html( implode( ', ', array_map( 'ucfirst', $compatible ) ) ); ?>
			</p>
		<?php endif; ?>
	</footer>
</article>
