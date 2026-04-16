<?php
/**
 * Block Bindings API source for sprinkling RoxyAPI content into core blocks.
 *
 * Lets users bind a core/paragraph to roxyapi/daily-text with args
 * like {"sign":"leo"} and have the daily overview render inline.
 *
 * Secondary surface only. Bindings only handle scalar string values.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Blocks;

use RoxyAPI\Support\Sanitize;

class Bindings {

	/**
	 * Register block binding sources.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'init', array( self::class, 'register_sources' ) );
	}

	/**
	 * Register the roxyapi/daily-text binding source.
	 *
	 * @return void
	 */
	public static function register_sources(): void {
		if ( ! function_exists( 'register_block_bindings_source' ) ) {
			return;
		}
		register_block_bindings_source(
			'roxyapi/daily-text',
			array(
				'label'              => __( 'RoxyAPI Daily Horoscope', 'roxyapi' ),
				'get_value_callback' => array( self::class, 'daily_text_value' ),
				'uses_context'       => array( 'roxyapi/sign' ),
			)
		);
	}

	/**
	 * Return the daily horoscope overview text for a given sign.
	 *
	 * @param array<string, string> $source_args Block binding source arguments.
	 * @return string
	 */
	public static function daily_text_value( array $source_args ): string {
		$sign = Sanitize::zodiac_sign( $source_args['sign'] ?? 'aries' );
		if ( ! class_exists( '\\RoxyAPI\\Generated\\Client' ) ) {
			return '';
		}
		$data = \RoxyAPI\Generated\Client::getDailyHoroscope( $sign );
		if ( is_wp_error( $data ) || ! is_array( $data ) ) {
			return '';
		}
		return isset( $data['overall'] ) ? (string) $data['overall'] : '';
	}
}
