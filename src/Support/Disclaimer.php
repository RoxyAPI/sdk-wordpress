<?php
/**
 * Site-owner-controlled disclaimer line appended to every rendered reading.
 *
 * Off by default. Site owner enables via Settings → Display → Disclaimer.
 * Custom text lives in `disclaimer_text`; empty falls back to a localised
 * "for entertainment purposes only" line.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Admin\SettingsSchema;

class Disclaimer {

	/**
	 * Render the disclaimer paragraph, or empty string when disabled.
	 *
	 * @return string
	 */
	public static function render(): string {
		$opts = SettingsSchema::get_option();
		if ( empty( $opts['disclaimer_show'] ) ) {
			return '';
		}
		$text = (string) ( $opts['disclaimer_text'] ?? '' );
		if ( $text === '' ) {
			$text = __( 'For entertainment purposes only. Not a substitute for medical, legal, or financial advice.', 'roxyapi' );
		}
		return '<p class="roxyapi-card-disclaimer">' . esc_html( $text ) . '</p>';
	}
}
