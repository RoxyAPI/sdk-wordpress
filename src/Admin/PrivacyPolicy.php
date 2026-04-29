<?php
/**
 * Privacy Policy content registration.
 *
 * Injects a section into the WordPress-managed Privacy Policy editor
 * (`Tools → Privacy → Suggested text`) describing what data the plugin
 * collects, where it goes, and how long it lives. Required for any plugin
 * that processes visitor input — see https://developer.wordpress.org/plugins/privacy/suggesting-text-for-the-site-privacy-policy/.
 *
 * Birth date + time + location together qualify as special-category personal
 * data under GDPR Art. 9; visitors must be informed AND consent (the form-
 * mode consent checkbox is the consent record). This text is the disclosure
 * record.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PrivacyPolicy {

	public static function register(): void {
		add_action( 'admin_init', array( self::class, 'register_content' ) );
	}

	/**
	 * Register the suggested privacy-policy section. Idempotent —
	 * `wp_add_privacy_policy_content` deduplicates by plugin name.
	 *
	 * @return void
	 */
	public static function register_content(): void {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$content = '<p>'
			. esc_html__( 'When a visitor submits a Roxy reading form on this site (for example a synastry or compatibility chart), the form data is sent to the third-party Roxy service at https://roxyapi.com to compute the reading. The site owner controls when these forms appear by placing the matching shortcode or block on a page.', 'roxyapi' )
			. '</p><p>'
			. esc_html__( 'What is sent: name (optional), birth date, birth time, latitude, longitude, and timezone. Birth time and location together qualify as special-category data under EU GDPR Art. 9, so a consent checkbox is required before each submission.', 'roxyapi' )
			. '</p><p>'
			. esc_html__( 'How long it is stored on this site: the rendered reading is cached server-side for up to one hour by default (configurable). The visitor inputs themselves are not persisted. Only the final reading text is cached, keyed by a hash of the inputs.', 'roxyapi' )
			. '</p><p>'
			. esc_html__( 'How long Roxy stores the data: see the Roxy privacy policy at https://roxyapi.com/policy/privacy.', 'roxyapi' )
			. '</p><p>'
			. esc_html__( 'No visitor data is sent to Roxy when a page renders a static-attribute shortcode (the data is supplied by the site owner). Form-mode shortcodes are the only case where visitor input crosses to the third party.', 'roxyapi' )
			. '</p>';

		wp_add_privacy_policy_content( 'Roxy', $content );
	}
}
