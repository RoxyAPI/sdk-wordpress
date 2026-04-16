<?php
/**
 * Tests for the Encryption helper.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Support\Encryption;

class Test_Encryption extends \WP_UnitTestCase {

	public function test_round_trip_returns_plaintext(): void {
		$plain = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.0123456789abcdef.test';
		$enc   = Encryption::encrypt( $plain );
		$this->assertNotFalse( $enc );
		$this->assertNotSame( $plain, $enc );
		$dec = Encryption::decrypt( $enc );
		$this->assertSame( $plain, $dec );
	}

	public function test_empty_input_returns_empty(): void {
		$this->assertSame( '', Encryption::encrypt( '' ) );
		$this->assertSame( '', Encryption::decrypt( '' ) );
	}

	public function test_ciphertext_is_not_plaintext(): void {
		$plain = 'my-secret-api-key';
		$enc   = Encryption::encrypt( $plain );
		$this->assertNotFalse( $enc );
		$this->assertStringNotContainsString( $plain, (string) $enc );
	}

	public function test_decrypt_rejects_tampered_ciphertext(): void {
		$enc     = Encryption::encrypt( 'secret' );
		$this->assertNotFalse( $enc );
		$tampered = base64_encode( 'garbage_garbage_garbage_garbage_garbage_garbage' );
		$this->assertFalse( Encryption::decrypt( $tampered ) );
	}
}
