<?php
/**
 * Tests for the SettingsFields::sanitize() callback under the WP Settings API
 * filter chain. The chain calls our sanitize callback re-entrantly (once via
 * sanitize_option(), once via update_option() internally), and an earlier
 * version of the callback clobbered the freshly encrypted value on the
 * second pass because it read $existing from the DB while the DB still
 * carried the OLD value.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\SettingsFields;
use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Support\ApiKey;
use RoxyAPI\Support\Encryption;

class Test_Settings_Fields_Sanitize extends \WP_UnitTestCase {

	private const VALID_KEY = '1978c678-9c7b-490a-8229-bc27613d3b77.a965fb53df2b49d8.0JYe5fTdjrZKfSrT26yB0EYn7Mt6qJ4jS-TENkEm3kc';

	public function setUp(): void {
		parent::setUp();
		delete_option( SettingsPage::OPTION_NAME );
		// Re-register the option directly. We avoid `do_action('admin_init')`
		// here because that fires every admin_init hook in the test process,
		// some of which (`auth_redirect`, `nocache_headers`, etc.) try to send
		// HTTP headers and trip "headers already sent" in PHPUnit.
		SettingsPage::register_setting();
	}

	public function tearDown(): void {
		delete_option( SettingsPage::OPTION_NAME );
		parent::tearDown();
	}

	/**
	 * The end-to-end save path: simulate options.php's two-pass sanitize
	 * (sanitize_option then update_option's internal sanitize) and assert the
	 * encrypted value survives both passes intact.
	 */
	public function test_sanitize_chain_preserves_encrypted_value_through_both_passes(): void {
		// Pass 1: explicit sanitize_option() pre-write (mirrors options.php).
		$pass1 = sanitize_option( SettingsPage::OPTION_NAME, array( 'api_key' => self::VALID_KEY ) );
		$this->assertNotEmpty( $pass1['api_key_encrypted'], 'pass 1 must produce ciphertext' );

		// Pass 2: update_option() internally re-runs sanitize_option(); the
		// DB row is still the OLD empty value at this moment.
		update_option( SettingsPage::OPTION_NAME, $pass1 );

		$stored = get_option( SettingsPage::OPTION_NAME );
		$this->assertNotEmpty(
			$stored['api_key_encrypted'],
			'second sanitize pass must not clobber the encrypted value when the DB still holds the old empty row'
		);
		$this->assertSame( self::VALID_KEY, ApiKey::get(), 'round-tripped plaintext must match the input' );
		$this->assertTrue( ApiKey::is_configured() );
	}

	/**
	 * Direct re-entrant call: sanitize() called with the encrypted shape (no
	 * api_key, only api_key_encrypted) must return that same encrypted value
	 * rather than silently falling back to a stale DB read.
	 */
	public function test_sanitize_idempotent_on_already_encrypted_input(): void {
		$encrypted = Encryption::encrypt( self::VALID_KEY );
		$this->assertNotFalse( $encrypted );

		// DB is empty here. If the callback reads $existing from DB without
		// preferring the input, this assertion fails.
		$result = SettingsFields::sanitize( array( 'api_key_encrypted' => $encrypted ) );
		$this->assertSame( $encrypted, $result['api_key_encrypted'] );
	}

	/**
	 * Truly blank submission (user clears the field intending to keep the
	 * existing key) must preserve whatever is in the DB.
	 */
	public function test_sanitize_blank_submission_preserves_db_value(): void {
		$encrypted = Encryption::encrypt( self::VALID_KEY );
		update_option(
			SettingsPage::OPTION_NAME,
			array(
				'api_key_encrypted' => $encrypted,
				'cache_ttl'         => HOUR_IN_SECONDS,
			)
		);

		$result = SettingsFields::sanitize( array( 'api_key' => '' ) );
		$this->assertSame( $encrypted, $result['api_key_encrypted'] );
	}
}
