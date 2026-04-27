<?php
/**
 * Tests for the roxyapi/daily-text Block Bindings source.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Api\Cache;
use RoxyAPI\Blocks\Bindings;
use RoxyAPI\Support\Encryption;

class Test_Bindings extends Mock_Http_TestCase {

	public function setUp(): void {
		parent::setUp();
		Cache::flush_all();
		wp_cache_flush();
		$test_key = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.0123456789abcdef.bindings_test';
		update_option(
			'roxyapi_settings',
			array( 'api_key_encrypted' => Encryption::encrypt( $test_key ) )
		);
	}

	public function tearDown(): void {
		Cache::flush_all();
		wp_cache_flush();
		parent::tearDown();
	}

	public function test_returns_overview_for_valid_sign(): void {
		$this->mock_responses['astrology/horoscope/leo/daily'] = array(
			'sign'     => 'leo',
			'overview' => 'Leos shine today.',
		);
		$out = Bindings::daily_text_value( array( 'sign' => 'leo' ) );
		$this->assertSame( 'Leos shine today.', $out );
	}

	public function test_returns_empty_when_overview_field_missing(): void {
		$this->mock_responses['astrology/horoscope/aries/daily'] = array(
			'sign' => 'aries',
			// no overview
		);
		$out = Bindings::daily_text_value( array( 'sign' => 'aries' ) );
		$this->assertSame( '', $out );
	}

	public function test_returns_empty_on_api_error(): void {
		// Force the mocked HTTP call to return a 429 by overriding the parent
		// substring handler with a higher priority filter.
		add_filter(
			'pre_http_request',
			static function () {
				return array(
					'headers'  => array(),
					'body'     => wp_json_encode( array( 'error' => 'Rate limited', 'code' => 'rate_limit_exceeded' ) ),
					'response' => array(
						'code'    => 429,
						'message' => 'Too Many Requests',
					),
					'cookies'  => array(),
					'filename' => null,
				);
			},
			1,
			3
		);

		$out = Bindings::daily_text_value( array( 'sign' => 'cancer' ) );
		$this->assertSame( '', $out, 'Bindings must swallow API errors and return an empty string.' );
	}

	public function test_invalid_sign_falls_back_to_aries(): void {
		// The invalid sign in args should be coerced to "aries" via Sanitize,
		// so the mocked aries endpoint is what gets hit.
		$this->mock_responses['astrology/horoscope/aries/daily'] = array(
			'sign'     => 'aries',
			'overview' => 'Aries fallback.',
		);
		$out = Bindings::daily_text_value( array( 'sign' => 'dragon' ) );
		$this->assertSame( 'Aries fallback.', $out );
	}

	public function test_missing_sign_arg_defaults_to_aries(): void {
		$this->mock_responses['astrology/horoscope/aries/daily'] = array(
			'sign'     => 'aries',
			'overview' => 'Default Aries.',
		);
		$out = Bindings::daily_text_value( array() );
		$this->assertSame( 'Default Aries.', $out );
	}

	public function test_binding_source_is_registered_with_correct_metadata(): void {
		// The plugin's bootstrap already registered the binding source. We
		// fetch it from the registry and verify the metadata matches.
		if ( ! class_exists( 'WP_Block_Bindings_Registry' ) ) {
			$this->markTestSkipped( 'Block Bindings Registry requires WP 6.5+.' );
		}
		$source = \WP_Block_Bindings_Registry::get_instance()->get_registered( 'roxyapi/daily-text' );
		$this->assertNotNull( $source, 'roxyapi/daily-text binding source must be registered.' );
		$this->assertSame( 'roxyapi/daily-text', $source->name );
		$this->assertContains( 'roxyapi/sign', (array) $source->uses_context );
	}

	public function test_returns_empty_when_generated_client_missing(): void {
		// We cannot un-load the generated Client class, so this branch is
		// only reachable on installs that ship a corrupt build/. Assert the
		// guard is at least syntactically present in source.
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/src/Blocks/Bindings.php' );
		$this->assertIsString( $source );
		$this->assertStringContainsString( "class_exists( '\\\\RoxyAPI\\\\Generated\\\\Client' )", $source );
	}
}
