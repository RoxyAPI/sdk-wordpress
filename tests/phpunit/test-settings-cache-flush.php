<?php
/**
 * Saving the RoxyAPI key must flush the response cache. Cache keys are derived
 * from endpoint + args only, never from auth state, so a free-tier or wrong-key
 * response would otherwise keep serving after the owner connects or rotates a
 * key until each transient expires. SettingsPage hooks add_option_/update_option_
 * to flush on a real key change while leaving unrelated saves untouched.
 *
 * Assertions read the options table directly via $wpdb: Cache::flush_all() runs
 * a direct DELETE, so a get_transient() round trip could report a stale value
 * from the in-memory object cache even though the row is gone.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\SettingsPage;

class Test_Settings_Cache_Flush extends \WP_UnitTestCase {

	private const KEY_A = array( 'api_key_encrypted' => 'cipher-aaa' );
	private const KEY_B = array( 'api_key_encrypted' => 'cipher-bbb' );

	public function setUp(): void {
		parent::setUp();
		delete_option( SettingsPage::OPTION_NAME );
		// Wire the add_option_/update_option_ cache-flush hooks under test
		// (idempotent: re-adding the same static callback overwrites its slot).
		SettingsPage::register();
	}

	public function tearDown(): void {
		delete_option( SettingsPage::OPTION_NAME );
		delete_transient( 'roxyapi_probe' );
		parent::tearDown();
	}

	private function seed_cache(): void {
		set_transient( 'roxyapi_probe', 'cached', HOUR_IN_SECONDS );
	}

	private function probe_row_exists(): bool {
		global $wpdb;
		return (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name = %s",
				'_transient_roxyapi_probe'
			)
		);
	}

	public function test_first_key_save_flushes_cache(): void {
		$this->seed_cache();
		$this->assertTrue( $this->probe_row_exists(), 'sanity: probe transient seeded' );

		// No option row yet, so add_option_ fires rather than update_option_.
		update_option( SettingsPage::OPTION_NAME, self::KEY_A );

		$this->assertFalse( $this->probe_row_exists(), 'connecting a key must flush roxyapi_ transients' );
	}

	public function test_key_rotation_flushes_cache(): void {
		update_option( SettingsPage::OPTION_NAME, self::KEY_A );
		$this->seed_cache();

		// Existing row, key value changes: update_option_ fires.
		update_option( SettingsPage::OPTION_NAME, self::KEY_B );

		$this->assertFalse( $this->probe_row_exists(), 'rotating the key must flush the cache' );
	}

	public function test_unrelated_setting_save_does_not_flush(): void {
		update_option( SettingsPage::OPTION_NAME, self::KEY_A );
		$this->seed_cache();

		// Same key, a different field changes: the cache must survive.
		update_option(
			SettingsPage::OPTION_NAME,
			array(
				'api_key_encrypted' => 'cipher-aaa',
				'cache_preset'      => 'fresh',
			)
		);

		$this->assertTrue( $this->probe_row_exists(), 'saving an unrelated setting must not flush the cache' );
	}
}
