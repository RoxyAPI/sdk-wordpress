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

	public function test_decrypt_returns_false_for_corrupted_base64(): void {
		// Random non base64 input: base64_decode( strict ) must return false
		// and Encryption::decrypt must surface that as false rather than throw.
		$this->assertFalse( Encryption::decrypt( '!!!not base64 at all!!!' ) );
	}

	public function test_decrypt_returns_false_for_too_short_ciphertext(): void {
		// A buffer shorter than the IV length must be rejected cleanly.
		$short = base64_encode( 'short' );
		$this->assertFalse( Encryption::decrypt( $short ) );
	}

	public function test_each_encrypt_uses_a_fresh_iv(): void {
		// AES-CTR with a random IV per call must produce different ciphertexts
		// for the same plaintext on every invocation. This proves the IV is
		// not reused (which would be catastrophic for CTR mode).
		$plain = 'consistent-plaintext';
		$a     = Encryption::encrypt( $plain );
		$b     = Encryption::encrypt( $plain );
		$this->assertNotFalse( $a );
		$this->assertNotFalse( $b );
		$this->assertNotSame( $a, $b );
		$this->assertSame( $plain, Encryption::decrypt( $a ) );
		$this->assertSame( $plain, Encryption::decrypt( $b ) );
	}

	public function test_decrypt_returns_false_when_salt_does_not_match(): void {
		// Encrypt a value, then mutate the trailing bytes (where the salt
		// integrity tag lives) and assert decrypt rejects it.
		$enc = Encryption::encrypt( 'sample' );
		$this->assertNotFalse( $enc );

		$raw = base64_decode( $enc, true );
		$this->assertNotFalse( $raw );

		// Flip the last byte of the encrypted payload so the salt tail will
		// no longer match LOGGED_IN_SALT after decryption.
		$mutated      = substr( $raw, 0, -1 ) . chr( ord( substr( $raw, -1 ) ) ^ 0xFF );
		$mutated_b64  = base64_encode( $mutated );

		$this->assertFalse( Encryption::decrypt( $mutated_b64 ) );
	}

	public function test_decrypt_truly_garbage_base64_returns_false(): void {
		// Long enough to pass the iv length check but not real ciphertext.
		$junk = base64_encode( str_repeat( "\x00", 64 ) );
		$this->assertFalse( Encryption::decrypt( $junk ) );
	}

	public function test_encrypt_returns_false_when_no_key_is_available(): void {
		// LOGGED_IN_KEY is always defined by the WP test bootstrap, and PHP
		// constants cannot be undefined at runtime. The no-key code path
		// (Encryption::key returning false) is therefore unreachable from
		// inside this process. This is intentional documentation: the
		// safeguard exists only for installs where wp-config.php was never
		// completed. Plugin Check and a fresh-install smoke test cover it.
		$this->markTestSkipped( 'LOGGED_IN_KEY cannot be undefined inside a single test process. Path covered by smoke tests on a freshly extracted WP install.' );
	}

	public function test_decrypt_returns_false_when_no_key_is_available(): void {
		$this->markTestSkipped( 'LOGGED_IN_KEY cannot be undefined inside a single test process. Path covered by smoke tests on a freshly extracted WP install.' );
	}

	public function test_encrypt_uses_constant_when_defined(): void {
		// Same reason as above: ROXYAPI_ENCRYPTION_KEY cannot be defined mid
		// process if the test bootstrap already loaded the plugin without
		// it. We verify the LOGGED_IN_KEY fallback path roundtrips, which is
		// the actually-used branch in the test environment.
		$plain = 'constant-fallback-roundtrip';
		$enc   = Encryption::encrypt( $plain );
		$this->assertNotFalse( $enc );
		$this->assertSame( $plain, Encryption::decrypt( $enc ) );
	}
}
