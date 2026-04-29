<?php
/**
 * Template: horoscope sign picker form.
 *
 * Variables in scope:
 *   string   $action    Form action identifier (hidden field value)
 *   string   $nonce     CSRF nonce
 *   string   $selected  Currently selected sign (empty on first render)
 *   string[] $signs     List of zodiac sign slugs
 *
 * Posts back to the current URL. Progressive enhancement via JavaScript
 * can replace the page reload with a fetch call to the preview REST route
 * in a future version.
 *
 * @package RoxyAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
// Post back to the same URL. Empty `action=""` works in browsers but
// trips Plugin Check and confuses some caching plugins (they sometimes
// serve cached HTML on POST when action is empty). Computing it from
// REQUEST_URI keeps the form portable and explicit.
$roxyapi_form_action = isset( $_SERVER['REQUEST_URI'] )
	? esc_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) )
	: '';
?>
<form class="roxyapi-form roxyapi-form--horoscope" method="post" action="<?php echo esc_url( $roxyapi_form_action ); ?>">
	<input type="hidden" name="roxyapi_action" value="<?php echo esc_attr( $action ); ?>" />
	<input type="hidden" name="roxyapi_nonce" value="<?php echo esc_attr( $nonce ); ?>" />

	<div class="roxyapi-form__field">
		<label for="roxyapi-horoscope-sign">
			<?php echo esc_html__( 'Your zodiac sign', 'roxyapi' ); ?>
		</label>
		<select id="roxyapi-horoscope-sign" name="sign" required>
			<option value=""><?php echo esc_html__( 'Pick a sign', 'roxyapi' ); ?></option>
			<?php foreach ( $signs as $sign ) : ?>
				<option value="<?php echo esc_attr( $sign ); ?>" <?php selected( $sign, $selected ); ?>>
					<?php echo esc_html( ucfirst( $sign ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="roxyapi-form__actions">
		<button type="submit" class="roxyapi-form__submit">
			<?php echo esc_html__( 'Read my horoscope', 'roxyapi' ); ?>
		</button>
	</div>
</form>
