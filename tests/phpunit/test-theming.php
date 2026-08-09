<?php
/**
 * The palette a site owner sets, and the stylesheet it becomes.
 *
 * A settings field that feeds a stylesheet is a CSS injection surface, so the
 * first group of tests below is about what CANNOT get through: anything that is
 * not a HEX colour is dropped on the way in and dropped again on the way out,
 * and a preset never consults the database at all.
 *
 * The second group is the contrast bug this widening exists to close. The token
 * layer resolves `--roxy-accent-ink` to the raw accent in dark mode, so the old
 * single accent forced into all three theme states turned a dark brand colour
 * into dark text on a near-black surface. Light and dark are separate values
 * now, and the emitted stylesheet has to carry both, in an order the cascade
 * depends on.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Support\Theming;

class Test_Theming extends \WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		delete_option( SettingsPage::OPTION_NAME );
		SettingsPage::register_setting();
	}

	public function tearDown(): void {
		delete_option( SettingsPage::OPTION_NAME );
		parent::tearDown();
	}

	/**
	 * @param array<string, mixed> $input
	 * @return array<string, mixed>
	 */
	private function save( array $input ): array {
		$value = sanitize_option( SettingsPage::OPTION_NAME, $input );
		update_option( SettingsPage::OPTION_NAME, $value );
		$stored = get_option( SettingsPage::OPTION_NAME );
		return is_array( $stored ) ? $stored : array();
	}

	/**
	 * Every shape someone would try if they wanted to close our declaration and
	 * open one of their own, or reach the network from a stylesheet.
	 *
	 * @return array<string, array<int, string>>
	 */
	public function injection_payloads(): array {
		return array(
			'closes the declaration'  => array( '#fff;} :root{--roxy-bg:url(https://evil.test/x)' ),
			'css function'            => array( 'url(https://evil.test/beacon.png)' ),
			'expression'              => array( 'expression(alert(1))' ),
			'closes the style block'  => array( '</style><script>alert(1)</script>' ),
			'import'                  => array( '#fff;}@import "https://evil.test/x.css"' ),
			'named colour'            => array( 'red' ),
			'rgb notation'            => array( 'rgb(255,0,0)' ),
			'var reference'           => array( 'var(--wp-admin-theme-color)' ),
			'javascript scheme'       => array( 'javascript:alert(1)' ),
			'not hex at all'          => array( '#ggghhh' ),
		);
	}

	/**
	 * @dataProvider injection_payloads
	 * @param string $payload Hostile value posted into a colour field.
	 */
	public function test_a_colour_field_rejects_anything_that_is_not_a_hex_colour( string $payload ): void {
		$stored = $this->save(
			array(
				'palette_preset' => '',
				'accent_color'   => $payload,
				'bg_color_dark'  => $payload,
			)
		);

		$this->assertSame( '', $stored['accent_color'], 'A non-colour must not be stored.' );
		$this->assertSame( '', $stored['bg_color_dark'], 'A non-colour must not be stored.' );

		$css = Theming::inline_css();
		$this->assertStringNotContainsString( 'evil.test', $css );
		$this->assertStringNotContainsString( '@import', $css );
		$this->assertStringNotContainsString( '<', $css );
		$this->assertStringNotContainsString( 'expression(', $css );
	}

	/**
	 * The sanitiser is not the only line. An option can be written by anything
	 * holding `update_option`, so the renderer re-validates rather than trusting
	 * what it reads.
	 */
	public function test_a_hostile_value_written_straight_into_the_option_never_reaches_the_stylesheet(): void {
		update_option(
			SettingsPage::OPTION_NAME,
			array(
				'palette_preset' => '',
				'accent_color'   => '#fff;}@import "https://evil.test/x.css";a{b:c',
			)
		);

		$css = Theming::inline_css();
		$this->assertStringNotContainsString( 'evil.test', $css );
		$this->assertStringNotContainsString( '@import', $css );
	}

	public function test_a_valid_hex_colour_is_kept(): void {
		$stored = $this->save(
			array(
				'palette_preset' => '',
				'accent_color'   => '#8B5CF6',
			)
		);
		$this->assertSame( '#8B5CF6', $stored['accent_color'] );
		$this->assertStringContainsString( '--roxy-accent:#8B5CF6;', Theming::inline_css() );
	}

	/**
	 * The bug. One accent forced into every theme state meant the dark token set
	 * used a light-mode brand colour as TEXT on a near-black surface.
	 */
	public function test_dark_values_emit_into_both_dark_blocks(): void {
		$this->save(
			array(
				'palette_preset'    => '',
				'accent_color'      => '#914955',
				'accent_color_dark' => '#d9a2a6',
				'bg_color_dark'     => '#231619',
			)
		);

		$css = Theming::inline_css();

		$this->assertStringContainsString(
			':root[data-theme="dark"]{--roxy-accent:#d9a2a6;--roxy-bg:#231619;}',
			$css,
			'A forced dark page must get the dark values.'
		);
		$this->assertStringContainsString(
			'@media (prefers-color-scheme:dark){:root{--roxy-accent:#d9a2a6;--roxy-bg:#231619;}}',
			$css,
			'An auto page on a dark device must get them too, or dark mode silently renders the light accent.'
		);
		$this->assertStringContainsString(
			':root,:root[data-theme="light"]{',
			$css,
			'The light block must be scoped so a forced-light page beats the dark media query.'
		);
		$this->assertStringNotContainsString(
			'#914955',
			substr( $css, (int) strpos( $css, ':root[data-theme="dark"]' ) ),
			'The light accent must not appear in either dark block.'
		);
	}

	public function test_no_dark_block_is_emitted_when_no_dark_colour_is_set(): void {
		$this->save(
			array(
				'palette_preset' => '',
				'accent_color'   => '#8B5CF6',
			)
		);

		$css = Theming::inline_css();
		$this->assertStringNotContainsString( 'data-theme="dark"', $css );
		$this->assertStringNotContainsString( 'prefers-color-scheme', $css );
	}

	/**
	 * The highest-value line in the file: components stop announcing themselves
	 * with a typeface the surrounding page does not use.
	 */
	public function test_the_font_follows_the_site_theme_by_default(): void {
		$this->assertStringContainsString( '--roxy-font-sans:inherit;', Theming::inline_css() );
	}

	public function test_a_preset_emits_its_own_colours_and_ignores_the_stored_ones(): void {
		$this->save(
			array(
				'palette_preset' => 'moonlit',
				// Would be honoured under Custom. A preset answers on its own.
				'accent_color'   => '#8B5CF6',
			)
		);

		$css = Theming::inline_css();
		$this->assertStringContainsString( '--roxy-accent:#254b5a;', $css, 'Moonlit light accent.' );
		$this->assertStringContainsString( '--roxy-accent:#c9a96b;', $css, 'Moonlit dark accent.' );
		$this->assertStringNotContainsString( '#8B5CF6', $css );
	}

	/**
	 * Choosing a preset copies its values into the colour fields so switching to
	 * Custom afterwards starts from the palette that was on screen.
	 */
	public function test_choosing_a_preset_seeds_the_colour_fields(): void {
		$stored = $this->save( array( 'palette_preset' => 'kiln' ) );

		$this->assertSame( '#a44a24', $stored['accent_color'] );
		$this->assertSame( '#d98d5f', $stored['accent_color_dark'] );
		$this->assertSame( '#e6d8c2', $stored['border_color'] );
	}

	public function test_an_unknown_preset_name_falls_back_to_custom(): void {
		$stored = $this->save(
			array(
				'palette_preset' => '../../wp-config.php',
				'accent_color'   => '#8B5CF6',
			)
		);

		$this->assertSame( '', $stored['palette_preset'] );
		$this->assertSame( '#8B5CF6', $stored['accent_color'] );
	}

	public function test_reset_clears_the_preset_and_every_colour(): void {
		$this->save( array( 'palette_preset' => 'eucalyptus' ) );

		$stored = $this->save( array( 'reset_palette' => '1' ) );

		$this->assertSame( '', $stored['palette_preset'] );
		foreach ( Theming::option_keys() as $key ) {
			$this->assertSame( '', $stored[ $key ], "{$key} must be cleared by the reset." );
		}
		// Only the font line survives, which is the shipped default.
		$this->assertSame( ':root,:root[data-theme="light"]{--roxy-font-sans:inherit;}', Theming::inline_css() );
	}

	/**
	 * options.php sanitises a POST twice. The second pass sees the OUTPUT of the
	 * first, which no longer carries the reset button, and must not undo it.
	 */
	public function test_the_reset_survives_the_second_sanitise_pass(): void {
		$this->save( array( 'palette_preset' => 'kiln' ) );

		$pass1 = sanitize_option( SettingsPage::OPTION_NAME, array( 'reset_palette' => '1' ) );
		$pass2 = sanitize_option( SettingsPage::OPTION_NAME, $pass1 );

		$this->assertSame( '', $pass2['palette_preset'] );
		$this->assertSame( '', $pass2['accent_color'] );
	}

	/**
	 * The editor renders its canvas in an iframe whose `<html>` this plugin never
	 * touches, so the attribute the front end matches on is not there. A forced
	 * mode has to arrive as values, or the preview shows light while the site
	 * shows dark.
	 */
	public function test_the_editor_preview_gets_the_forced_mode_as_values(): void {
		$this->save(
			array(
				'palette_preset'    => '',
				'theme_mode'        => 'dark',
				'accent_color'      => '#914955',
				'accent_color_dark' => '#d9a2a6',
			)
		);

		$admin = Theming::inline_css( true );
		$this->assertSame( ':root{--roxy-font-sans:inherit;--roxy-accent:#d9a2a6;}', $admin );
	}

	public function test_auto_mode_still_emits_all_three_blocks_in_the_editor(): void {
		$this->save(
			array(
				'palette_preset'    => '',
				'theme_mode'        => 'auto',
				'accent_color'      => '#914955',
				'accent_color_dark' => '#d9a2a6',
			)
		);

		$this->assertSame( Theming::inline_css(), Theming::inline_css( true ) );
	}

	/**
	 * It used to return early on `is_admin()`, which left the accent applied and
	 * the mode ignored, so the Demo page showed a light card to an owner who had
	 * chosen dark.
	 */
	public function test_the_forced_mode_reaches_the_admin_html_tag(): void {
		$this->save(
			array(
				'palette_preset' => '',
				'theme_mode'     => 'dark',
			)
		);

		set_current_screen( 'dashboard' );
		$this->assertStringContainsString( 'data-theme="dark"', Theming::html_theme_attr( 'lang="en-US"' ) );
	}
}
