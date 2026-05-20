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
		$this->mock_responses['astrology/horoscope/aries/daily'] = array(
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
		$this->assertStringContainsString( 'roxy-horoscope-card', $out );
		$this->assertStringContainsString( 'A bold day ahead.', $out );
		$this->assertStringContainsString( 'Lucky number', $out );
	}

	public function test_invalid_sign_falls_back_to_default(): void {
		$out = do_shortcode( '[roxy_horoscope sign="dragon"]' );
		$this->assertStringContainsString( 'roxy-horoscope-card', $out );
		$this->assertStringContainsString( 'aries', $out );
	}

	public function test_payload_script_is_inert_json(): void {
		$out = do_shortcode( '[roxy_horoscope sign="aries"]' );
		// The component data ships as an inert JSON block read by the element on
		// connect, never as executable JavaScript. The API key still never
		// reaches the browser: this is the server-rendered response, not the key.
		$this->assertStringContainsString( '<script type="application/json" class="roxy-data">', $out );
		$this->assertStringNotContainsString( '<script>', $out );
	}

	public function test_caches_response(): void {
		do_shortcode( '[roxy_horoscope sign="aries"]' );
		// Sanitize::date_string("today") resolves to YYYY-MM-DD before reaching
		// the cache key (the SaaS spec rejects literal "today"). The cache key
		// therefore includes the resolved date, not the literal token.
		$resolved_date = wp_date( 'Y-m-d' );
		$key           = 'roxyapi_' . md5( 'astrology/horoscope/aries/daily|' . wp_json_encode( array( 'date' => $resolved_date ) ) );
		$this->assertNotFalse( get_transient( $key ) );
	}
}
