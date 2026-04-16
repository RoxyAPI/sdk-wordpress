<?php
/**
 * Tests for the [roxy_horoscope] hero shortcode.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Support\Encryption;

class Test_Shortcode_Horoscope extends Mock_Http_TestCase {

	public function setUp(): void {
		parent::setUp();
		$test_key = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.0123456789abcdef.test_key_plaintext';
		update_option(
			'roxyapi_settings',
			array(
				'api_key_encrypted' => Encryption::encrypt( $test_key ),
			)
		);
		$this->mock_responses['horoscope-api/daily'] = array(
			'sign'            => 'aries',
			'overview'        => 'A bold day ahead.',
			'love'            => 'Good vibes with Leo.',
			'career'          => 'Focus on planning.',
			'health'          => 'Hydrate.',
			'finance'         => 'Review your budget.',
			'advice'          => 'Stay patient.',
			'luckyNumber'     => 7,
			'luckyColor'      => 'red',
			'energyRating'    => 8,
			'moonSign'        => 'leo',
			'moonPhase'       => 'waxing crescent',
			'compatibleSigns' => array( 'leo', 'sagittarius' ),
		);
	}

	public function test_renders_horoscope_card(): void {
		$out = do_shortcode( '[roxy_horoscope sign="aries"]' );
		$this->assertStringContainsString( 'roxyapi-horoscope', $out );
		$this->assertStringContainsString( 'A bold day ahead.', $out );
		$this->assertStringContainsString( 'Lucky number', $out );
	}

	public function test_invalid_sign_falls_back_to_default(): void {
		$out = do_shortcode( '[roxy_horoscope sign="dragon"]' );
		$this->assertStringContainsString( 'roxyapi-horoscope', $out );
		$this->assertStringContainsString( 'Aries', $out );
	}

	public function test_no_script_tags_in_output(): void {
		$out = do_shortcode( '[roxy_horoscope sign="aries"]' );
		$this->assertStringNotContainsString( '<script', $out );
	}

	public function test_caches_response(): void {
		do_shortcode( '[roxy_horoscope sign="aries"]' );
		$key = 'roxyapi_' . md5( 'horoscope-api/daily|' . wp_json_encode( array( 'sign' => 'aries', 'date' => 'today' ) ) );
		$this->assertNotFalse( get_transient( $key ) );
	}
}
