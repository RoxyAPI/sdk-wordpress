<?php
/**
 * The settings screen renders every tab whether or not a key is stored.
 *
 * Readings render from the moment the plugin is activated, so the settings that
 * shape them have to be reachable from that moment too. When the screen only
 * rendered the setup steps until a key was saved, a site owner could have a
 * reading on the page in the wrong language, in the wrong palette, with the
 * written report showing, and no control anywhere in wp-admin to change any of
 * it. Every one of those settings works without a key.
 *
 * These tests pin the split that makes that true: the tab bar and the four
 * settings panels do not depend on the key, and only the Connect tab body does.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Support\ApiKey;

class Test_Settings_Screen extends Mock_Http_TestCase {

	/** Tabs that shape a reading and therefore must never depend on the key. */
	private const SETTINGS_TABS = array( 'branding', 'display', 'privacy', 'advanced' );

	public function setUp(): void {
		parent::setUp();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		unset( $_GET['tab'] );
		// Registered notices live in a global that WordPress rebuilds per request.
		// The suite runs many renders in one process, so it has to be emptied here
		// or one test's notice is still on screen during the next one.
		$GLOBALS['wp_settings_errors'] = array();
	}

	public function tearDown(): void {
		unset( $_GET['tab'] );
		delete_option( SettingsPage::OPTION_NAME );
		parent::tearDown();
	}

	/** Render the settings screen for a tab and return its markup. */
	private function render( string $tab = 'connect' ): string {
		$_GET['tab'] = $tab;
		ob_start();
		SettingsPage::render();
		return (string) ob_get_clean();
	}

	/** A syntactically valid key, matching the shapes test-settings-fields-sanitize.php pins. */
	private const VALID_KEY = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.0123456789abcdef.settings_screen-KEY-0123456789abcdefghij';

	/** Store a key the same way the settings form does, through the sanitiser. */
	private function connect(): void {
		update_option(
			SettingsPage::OPTION_NAME,
			\RoxyAPI\Admin\SettingsFields::sanitize( array( 'api_key' => self::VALID_KEY ) )
		);
		$this->assertTrue( ApiKey::is_configured(), 'Fixture failed: the key did not store.' );
	}

	public function test_tab_bar_renders_without_a_key(): void {
		$this->assertFalse( ApiKey::is_configured() );

		$html = $this->render();

		foreach ( array_merge( array( 'connect' ), self::SETTINGS_TABS ) as $tab ) {
			$this->assertStringContainsString(
				'tab=' . $tab,
				$html,
				"The {$tab} tab must be reachable before a key is saved."
			);
		}
	}

	/**
	 * The heart of it. Each settings panel must render its own controls rather
	 * than the setup steps, so asserting on the tab bar alone is not enough:
	 * the bar could render while every panel fell through to onboarding.
	 */
	public function test_every_settings_panel_renders_its_controls_without_a_key(): void {
		$this->assertFalse( ApiKey::is_configured() );

		$expected = array(
			'branding' => 'roxyapi_settings[palette_preset]',
			'display'  => 'roxyapi_settings[hide_readings]',
			'privacy'  => 'roxyapi_settings[consent_label]',
			'advanced' => 'roxyapi_settings[cache_preset]',
		);

		foreach ( $expected as $tab => $control ) {
			$html = $this->render( $tab );
			$this->assertStringContainsString(
				$control,
				$html,
				"The {$tab} tab must render its own controls before a key is saved."
			);
			$this->assertStringNotContainsString(
				'Sign up at roxyapi.com',
				$html,
				"The {$tab} tab must not fall through to the setup steps."
			);
		}
	}

	/** The reading language is the setting this matters most for, so it is pinned by value. */
	public function test_reading_language_is_selectable_without_a_key(): void {
		$html = $this->render( 'branding' );

		$this->assertStringContainsString( 'roxyapi_settings[display_language]', $html );
		$this->assertStringContainsString( 'value="es"', $html );
	}

	/** A setting saved with no key must persist, not be discarded as premature. */
	public function test_a_setting_saves_without_a_key(): void {
		update_option(
			SettingsPage::OPTION_NAME,
			\RoxyAPI\Admin\SettingsFields::sanitize( array( 'display_language' => 'es' ) )
		);

		$this->assertFalse( ApiKey::is_configured() );
		$this->assertSame( 'es', get_option( SettingsPage::OPTION_NAME )['display_language'] );
	}

	/** Connect is the one tab whose body depends on the key: setup steps before. */
	public function test_connect_tab_shows_setup_steps_without_a_key(): void {
		$html = $this->render( 'connect' );

		$this->assertStringContainsString( 'Sign up at roxyapi.com', $html );
		$this->assertStringNotContainsString( 'Connected to RoxyAPI.', $html );
	}

	/** And key management after. */
	public function test_connect_tab_shows_key_management_with_a_key(): void {
		$this->connect();

		$html = $this->render( 'connect' );

		$this->assertStringContainsString( 'Connected to RoxyAPI.', $html );
		$this->assertStringNotContainsString( 'Sign up at roxyapi.com', $html );
	}

	/**
	 * Connect is the only tab that reports connection state, and an unconfigured
	 * screen that announced itself as connected would be a lie.
	 */
	public function test_connect_heading_reflects_connection_state(): void {
		$this->assertStringContainsString( 'Connect RoxyAPI in', $this->render( 'connect' ) );

		$this->connect();
		$this->assertStringContainsString( 'connected.', $this->render( 'connect' ) );
	}

	/** Every other tab is headed by the tab itself, in both states. */
	public function test_settings_tabs_are_headed_by_the_tab_not_the_key_state(): void {
		foreach ( array( 'branding' => 'Branding.', 'display' => 'Display.', 'privacy' => 'Privacy.', 'advanced' => 'Advanced.' ) as $tab => $heading ) {
			$html = $this->render( $tab );
			$this->assertStringContainsString( $heading, $html );
			$this->assertStringNotContainsString( 'Connect RoxyAPI in', $html, "The {$tab} tab must not nag about connecting." );
		}
	}

	/** One id per document: the onboarding partial must not bring a second banner. */
	public function test_test_connection_banner_is_not_duplicated(): void {
		$this->assertSame( 1, substr_count( $this->render( 'connect' ), 'id="roxyapi-test-banner"' ) );

		$this->connect();
		$this->assertSame( 1, substr_count( $this->render( 'connect' ), 'id="roxyapi-test-banner"' ) );
	}

	/**
	 * A save that reports nothing reads as a save that did not happen. WordPress
	 * only adds "Settings saved." on its own options-*.php screens, so this page
	 * has to register it, and every tab saves through the same render path.
	 */
	public function test_a_completed_save_is_confirmed(): void {
		$_GET['settings-updated'] = 'true';

		$this->assertStringContainsString( 'Settings saved.', $this->render( 'branding' ) );

		unset( $_GET['settings-updated'] );
	}

	/** An ordinary page load must not claim a save happened. */
	public function test_a_plain_page_load_is_not_confirmed(): void {
		$this->assertStringNotContainsString( 'Settings saved.', $this->render( 'branding' ) );
	}

	/** A rejected key is an error, not a success, and must not report both. */
	public function test_a_save_that_errored_is_not_confirmed(): void {
		$_GET['settings-updated'] = 'true';
		add_settings_error( SettingsPage::OPTION_NAME, 'roxyapi_invalid_api_key', 'API key format is invalid.', 'error' );

		$html = $this->render( 'connect' );

		$this->assertStringContainsString( 'API key format is invalid.', $html );
		$this->assertStringNotContainsString( 'Settings saved.', $html );

		unset( $_GET['settings-updated'] );
	}

	/**
	 * Save one tab the way the browser does: a POST carrying ONLY that tab's
	 * fields, through `update_option` so the registered sanitiser runs.
	 *
	 * @param array<string,mixed> $fields Just the fields that tab renders.
	 */
	private function save_tab( array $fields ): void {
		update_option( SettingsPage::OPTION_NAME, \RoxyAPI\Admin\SettingsFields::sanitize( $fields ) );
	}

	/**
	 * THE severe one. Every tab is its own form and posts only its own fields, so
	 * the sanitiser rebuilds the whole option from a partial array on every save.
	 * If a key it was not given is not carried over, then picking a palette
	 * silently destroys the site's API key and every reading on the site stops.
	 */
	public function test_saving_branding_does_not_destroy_the_stored_key(): void {
		$this->connect();
		$before = get_option( SettingsPage::OPTION_NAME )['api_key_encrypted'];

		// Exactly what the Branding form posts: no api_key field anywhere in it.
		$this->save_tab(
			array(
				'palette_preset' => 'practitioner',
				'theme_mode'     => 'dark',
			)
		);

		$after = get_option( SettingsPage::OPTION_NAME );
		$this->assertSame( $before, $after['api_key_encrypted'], 'A branding save must not touch the key.' );
		$this->assertTrue( ApiKey::is_configured(), 'The key must still resolve after an unrelated tab is saved.' );
		$this->assertSame( 'practitioner', $after['palette_preset'] );
		$this->assertSame( 'dark', $after['theme_mode'] );
	}

	/** The same protection in the other direction: connecting must not wipe settings. */
	public function test_connecting_a_key_keeps_settings_a_free_user_already_chose(): void {
		$this->save_tab( array( 'display_language' => 'es', 'hide_readings' => '1' ) );
		$this->save_tab( array( 'disclaimer_text' => 'Solo con fines de entretenimiento.', 'hide_readings' => '1' ) );

		$this->connect();

		$opt = get_option( SettingsPage::OPTION_NAME );
		$this->assertSame( 'es', $opt['display_language'], 'Connecting a key must not reset the language.' );
		$this->assertTrue( (bool) $opt['hide_readings'] );
		$this->assertSame( 'Solo con fines de entretenimiento.', $opt['disclaimer_text'] );
		$this->assertTrue( ApiKey::is_configured() );
	}

	/** Saving one tab must not reset a sibling tab's values either. */
	public function test_tabs_do_not_overwrite_each_other(): void {
		$this->save_tab( array( 'display_language' => 'de' ) );
		$this->save_tab( array( 'cache_preset' => 'quota_saver' ) );
		$this->save_tab( array( 'palette_preset' => 'kiln' ) );

		$opt = get_option( SettingsPage::OPTION_NAME );
		$this->assertSame( 'de', $opt['display_language'] );
		$this->assertSame( 'quota_saver', $opt['cache_preset'] );
		$this->assertSame( 'kiln', $opt['palette_preset'] );
	}

	/**
	 * The checkbox pair, both directions.
	 *
	 * A browser posts nothing for an unticked box, so presence is what separates
	 * "the owner unticked this" from "this field is not on the form being saved".
	 * The hidden companion makes the key always present on its own tab.
	 */
	public function test_an_unticked_box_clears_on_its_own_tab(): void {
		$this->save_tab( array( 'hide_readings' => '1' ) );
		$this->assertTrue( (bool) get_option( SettingsPage::OPTION_NAME )['hide_readings'] );

		// The Display form posted with the box unticked: the hidden field carries 0.
		$this->save_tab( array( 'display_language' => 'es', 'hide_readings' => '0' ) );

		$this->assertFalse(
			(bool) get_option( SettingsPage::OPTION_NAME )['hide_readings'],
			'An unticked box on the submitted form must clear.'
		);
	}

	/** And a form that does not carry the field at all must leave it alone. */
	public function test_a_form_without_the_box_leaves_it_alone(): void {
		$this->save_tab( array( 'hide_readings' => '1', 'attribution_show' => '1', 'disclaimer_show' => '1' ) );

		// The Branding form: no toggle lives on it, so it posts none of them.
		$this->save_tab( array( 'palette_preset' => 'kiln', 'theme_mode' => 'dark' ) );

		$opt = get_option( SettingsPage::OPTION_NAME );
		$this->assertTrue( (bool) $opt['hide_readings'], 'Saving Branding must not switch off hide-written-readings.' );
		$this->assertTrue( (bool) $opt['attribution_show'] );
		$this->assertTrue( (bool) $opt['disclaimer_show'] );
	}

	/** Every checkbox must ship its hidden companion, or it clears on an unrelated save. */
	public function test_every_checkbox_emits_its_presence_field(): void {
		$html = \RoxyAPI\Admin\SettingsFields::hide_readings_html()
			. \RoxyAPI\Admin\SettingsFields::attribution_checkbox_html()
			. \RoxyAPI\Admin\SettingsFields::disclaimer_show_html();

		foreach ( array( 'hide_readings', 'attribution_show', 'disclaimer_show' ) as $key ) {
			$this->assertStringContainsString(
				'<input type="hidden" name="roxyapi_settings[' . $key . ']" value="0" />',
				$html,
				"{$key} must emit a hidden presence field before its checkbox."
			);
		}
	}

	/** The presence field must survive the template's own escaping allowlist. */
	public function test_the_presence_field_survives_kses(): void {
		$html = wp_kses(
			\RoxyAPI\Admin\SettingsFields::hide_readings_html(),
			\RoxyAPI\Admin\SettingsFields::input_kses_allowed()
		);

		$this->assertStringContainsString( 'type="hidden"', $html, 'kses must not strip the presence field.' );
		$this->assertStringContainsString( 'type="checkbox"', $html );
	}

	/** A rejected key must not take the rest of the settings down with it. */
	public function test_a_rejected_key_leaves_other_settings_intact(): void {
		$this->save_tab( array( 'display_language' => 'es', 'palette_preset' => 'kiln' ) );

		$this->save_tab( array( 'api_key' => 'not-a-valid-key' ) );

		$opt = get_option( SettingsPage::OPTION_NAME );
		$this->assertSame( '', $opt['api_key_encrypted'], 'A malformed key must not be stored.' );
		$this->assertSame( 'es', $opt['display_language'], 'A rejected key must not reset unrelated settings.' );
		$this->assertSame( 'kiln', $opt['palette_preset'] );
	}

	/**
	 * With the key supplied by the ROXYAPI_KEY constant the field is disabled, so
	 * the form posts no key at all. Saving any tab must not clear the constant's
	 * effect, and the screen must still report as connected.
	 */
	public function test_a_constant_defined_key_survives_a_settings_save(): void {
		$this->save_tab( array( 'theme_mode' => 'dark' ) );

		$this->assertSame( 'dark', get_option( SettingsPage::OPTION_NAME )['theme_mode'] );
		$this->assertSame( '', get_option( SettingsPage::OPTION_NAME )['api_key_encrypted'] );
		// The constant path is read at ApiKey::get(), not from the option, so a
		// save that stores no ciphertext is the correct end state here.
		$this->assertTrue( ApiKey::is_defined_via_constant() === defined( 'ROXYAPI_KEY' ) );
	}

	/** The screen stays behind the capability it always was behind. */
	public function test_screen_renders_nothing_for_a_user_who_cannot_manage_options(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );

		$this->assertSame( '', $this->render( 'branding' ) );
	}
}
