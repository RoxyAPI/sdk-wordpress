<?php
/**
 * Tests for the ApiKey helper.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Support\ApiKey;
use RoxyAPI\Support\Encryption;

class Test_Api_Key extends \WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		delete_option( 'roxyapi_settings' );
	}

	public function test_empty_config_returns_empty_string(): void {
		$this->assertSame( '', ApiKey::get() );
		$this->assertFalse( ApiKey::is_configured() );
	}

	public function test_encrypted_option_round_trips(): void {
		$plain = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.0123456789abcdef.test';
		update_option(
			'roxyapi_settings',
			array( 'api_key_encrypted' => Encryption::encrypt( $plain ) )
		);
		$this->assertSame( $plain, ApiKey::get() );
		$this->assertTrue( ApiKey::is_configured() );
	}

	public function test_masked_hides_most_of_key(): void {
		$plain = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.0123456789abcdef.plaintextabcdef';
		update_option(
			'roxyapi_settings',
			array( 'api_key_encrypted' => Encryption::encrypt( $plain ) )
		);
		$masked = ApiKey::masked();
		$this->assertStringStartsWith( '********', $masked );
		$this->assertStringEndsWith( 'cdef', $masked );
		$this->assertStringNotContainsString( 'plaintext', $masked );
	}
}
