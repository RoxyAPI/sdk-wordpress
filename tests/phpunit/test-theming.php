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

use RoxyAPI\Activation;
use RoxyAPI\Admin\SettingsFields;
use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Generated\Palettes;
use RoxyAPI\Plugin;
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

	/** The style tags a page with a reading prints, from a fresh registry. */
	private function frontend_styles(): string {
		$saved_styles         = $GLOBALS['wp_styles'] ?? null;
		$GLOBALS['wp_styles'] = new \WP_Styles();
		Plugin::register_frontend_style();
		ob_start();
		wp_styles()->do_items( array( 'roxyapi-frontend' ) );
		$html                 = (string) ob_get_clean();
		$GLOBALS['wp_styles'] = $saved_styles;
		return $html;
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
		$moonlit = Palettes::all()['moonlit'];
		$this->assertStringContainsString( '--roxy-accent:' . $moonlit['light']['accent'] . ';', $css, 'Moonlit light accent.' );
		$this->assertStringContainsString( '--roxy-accent:' . $moonlit['dark']['accent'] . ';', $css, 'Moonlit dark accent.' );
		$this->assertStringNotContainsString( '#8B5CF6', $css );
	}

	/** Form labels and chart strokes read `--roxy-secondary`, so every preset must set it in both modes. */
	public function test_a_preset_emits_the_labels_colour_in_both_modes(): void {
		$this->save( array( 'palette_preset' => 'moonlit' ) );

		$css = Theming::inline_css();
		$this->assertStringContainsString( '--roxy-secondary:#2d3c47;', $css, 'Moonlit light labels.' );
		$this->assertStringContainsString( '--roxy-secondary:#ced0c7;', $css, 'Moonlit dark labels.' );
	}

	/** A preset that skipped a token would leave that token on the stock slate. */
	public function test_every_preset_sets_every_token_in_both_modes(): void {
		foreach ( Theming::preset_names() as $name ) {
			foreach ( array( 'light', 'dark' ) as $mode ) {
				$colors = Theming::preset( $name )[ $mode ];
				$this->assertSame( Theming::tokens(), array_keys( $colors ), "{$name} {$mode} token set." );
				foreach ( $colors as $token => $hex ) {
					$this->assertSame( $hex, sanitize_hex_color( $hex ), "{$name} {$mode} {$token} must be a HEX colour." );
				}
			}
		}
	}

	/** The labels colour is a settings field like the rest. */
	public function test_a_custom_labels_colour_is_saved_and_emitted(): void {
		$stored = $this->save( array( 'secondary_color_dark' => '#E0CECB' ) );

		$this->assertSame( '#E0CECB', $stored['secondary_color_dark'] );
		$this->assertStringContainsString( '--roxy-secondary:#E0CECB;', Theming::inline_css() );
	}

	/** Choosing a preset never writes into the colour fields; they stay the site owner's own. */
	public function test_choosing_a_preset_leaves_the_colour_fields_alone(): void {
		$stored = $this->save( array( 'palette_preset' => 'kiln' ) );

		$this->assertSame( '', $stored['accent_color'] );
		$this->assertSame( '', $stored['accent_color_dark'] );
		$this->assertSame( '', $stored['border_color'] );
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

	/**
	 * The stylesheet a 1.x install emitted, recorded from the released Theming
	 * against each saved option below. Neither option holds a `font` key, which
	 * is what an install upgrading from 1.x has.
	 *
	 * @return array<string, array{0: array<string, string>, 1: string, 2: string}>
	 */
	public function saved_installs(): array {
		$preset = ':root,:root[data-theme="light"]{--roxy-font-sans:inherit;--roxy-accent:#914955;--roxy-secondary:#503a3a;--roxy-bg:#fbf6f3;--roxy-surface:#f5e8e0;--roxy-fg:#3e2a2c;--roxy-muted:#7e625f;--roxy-border:#ead9d2;--roxy-danger:#b23a38;}:root[data-theme="dark"]{--roxy-accent:#d9a2a6;--roxy-secondary:#e0cecb;--roxy-bg:#231619;--roxy-surface:#37272b;--roxy-fg:#f2e4df;--roxy-muted:#b39698;--roxy-border:#49353a;--roxy-danger:#e4736b;}@media (prefers-color-scheme:dark){:root{--roxy-accent:#d9a2a6;--roxy-secondary:#e0cecb;--roxy-bg:#231619;--roxy-surface:#37272b;--roxy-fg:#f2e4df;--roxy-muted:#b39698;--roxy-border:#49353a;--roxy-danger:#e4736b;}}';
		$custom = ':root,:root[data-theme="light"]{--roxy-font-sans:inherit;--roxy-accent:#8B5CF6;--roxy-bg:#ffffff;--roxy-fg:#1e293b;}:root[data-theme="dark"]{--roxy-accent:#C4B5FD;}@media (prefers-color-scheme:dark){:root{--roxy-accent:#C4B5FD;}}';
		return array(
			'custom colours' => array(
				array(
					'accent_color'      => '#8B5CF6',
					'accent_color_dark' => '#C4B5FD',
					'fg_color'          => '#1e293b',
					'bg_color'          => '#ffffff',
					'theme_mode'        => 'auto',
				),
				$custom,
				$custom,
			),
			'preset over custom colours' => array(
				array(
					'palette_preset' => 'practitioner',
					'accent_color'   => '#8B5CF6',
					'theme_mode'     => 'light',
				),
				$preset,
				':root{--roxy-font-sans:inherit;--roxy-accent:#914955;--roxy-secondary:#503a3a;--roxy-bg:#fbf6f3;--roxy-surface:#f5e8e0;--roxy-fg:#3e2a2c;--roxy-muted:#7e625f;--roxy-border:#ead9d2;--roxy-danger:#b23a38;}',
			),
		);
	}

	/**
	 * An install upgraded from 1.x renders its widgets byte-identical, in both
	 * modes and in the editor: the one-time upgrade step keeps a preset site on
	 * its own font, and loads no font file.
	 *
	 * @dataProvider saved_installs
	 *
	 * @param array<string, string> $saved  A 1.x option.
	 * @param string                $page   What 1.x emitted on the page.
	 * @param string                $editor What 1.x emitted in the editor.
	 */
	public function test_a_1x_install_renders_its_widgets_byte_identical_after_the_upgrade( array $saved, string $page, string $editor ): void {
		// Written as the 1.x row sits in the database, past today's sanitiser.
		remove_all_filters( 'sanitize_option_' . SettingsPage::OPTION_NAME );
		update_option( SettingsPage::OPTION_NAME, $saved );
		Activation::upgrade();

		$this->assertSame( $page, Theming::inline_css() );
		$this->assertSame( $editor, Theming::inline_css( true ) );
		$this->assertSame( '', Theming::font_stylesheet() );
		$upgraded = get_option( SettingsPage::OPTION_NAME );
		$this->assertSame( $saved + array( 'font' => isset( $saved['palette_preset'] ) ? Theming::SITE_FONT : '' ), $upgraded, 'Every saved key is kept; only the font is added.' );

		Activation::upgrade();
		$this->assertSame( $upgraded, get_option( SettingsPage::OPTION_NAME ), 'The step runs once.' );
	}

	public function test_a_fresh_install_takes_the_palette_font_and_the_upgrade_leaves_it_alone(): void {
		global $wpdb;
		Activation::upgrade();
		$this->assertNull( $wpdb->get_var( $wpdb->prepare( "SELECT option_id FROM {$wpdb->options} WHERE option_name = %s", SettingsPage::OPTION_NAME ) ), 'Nothing saved, nothing written.' );

		$this->save( array( 'palette_preset' => 'practitioner' ) );
		Activation::upgrade();
		$this->assertSame( 'fraunces-jost', Theming::font() );
		$this->assertStringContainsString( '--roxy-font:fraunces-jost;', Theming::inline_css() );
	}

	public function test_the_site_font_loads_nothing_whatever_the_palette(): void {
		$this->save(
			array(
				'palette_preset' => 'practitioner',
				'font'           => Theming::SITE_FONT,
			)
		);
		$this->assertSame( '', Theming::font() );
		$this->assertStringNotContainsString( '--roxy-font:', Theming::inline_css() );
		$this->assertSame( '', Theming::font_stylesheet() );
		$this->assertStringContainsString( '<option value="site" selected>Your site font</option>', SettingsFields::font_input_html() );
	}

	public function test_a_save_from_another_tab_keeps_the_site_font(): void {
		$this->save(
			array(
				'palette_preset' => 'practitioner',
				'font'           => Theming::SITE_FONT,
			)
		);
		$this->assertSame( Theming::SITE_FONT, $this->save( array( 'report_name' => 'Luna' ) )['font'] );
	}

	public function test_the_font_picker_defaults_to_the_font_of_the_chosen_palette(): void {
		foreach ( Theming::preset_names() as $name ) {
			$this->save( array( 'palette_preset' => $name ) );
			$font = Palettes::all()[ $name ]['font'];
			$this->assertSame( $font, Theming::font(), $name );
			$this->assertStringContainsString( '<option value="" selected>' . esc_html( sprintf( 'Palette default: %s', Palettes::FONTS[ $font ] ) ) . '</option>', SettingsFields::font_input_html(), $name );
			$this->assertStringContainsString( 'value="' . $name . '" data-font="' . esc_attr( sprintf( 'Palette default: %s', Palettes::FONTS[ $font ] ) ) . '"', SettingsFields::palette_preset_input_html(), "{$name} card names its font for the picker." );
		}

		$this->save( array( 'palette_preset' => '' ) );
		$this->assertSame( '', Theming::font() );
		$this->assertStringContainsString( '<option value="" selected>Palette default: Your site font</option>', SettingsFields::font_input_html() );
		$this->assertStringNotContainsString( '--roxy-font:', Theming::inline_css() );
	}

	public function test_a_chosen_font_wins_over_the_palette_default(): void {
		$this->save(
			array(
				'palette_preset' => 'practitioner',
				'font'           => 'geist',
			)
		);
		$this->assertSame( 'geist', Theming::font() );
		$this->assertStringContainsString( '--roxy-font:geist;', Theming::inline_css() );
		$this->assertStringContainsString( '<option value="geist" selected>Geist</option>', SettingsFields::font_input_html() );
		$this->assertSame( 'assets/css/fonts/geist.css', Theming::font_stylesheet() );
	}

	public function test_a_font_drawn_from_system_fonts_loads_no_stylesheet(): void {
		$this->save( array( 'font' => 'system' ) );
		$this->assertStringContainsString( '--roxy-font:system;', Theming::inline_css() );
		$this->assertSame( '', Theming::font_stylesheet() );
	}

	public function test_a_font_the_catalogue_does_not_publish_is_refused(): void {
		$this->assertSame( '', $this->save( array( 'font' => 'comic-sans;}body{x:url(https://evil.test)' ) )['font'] );
		update_option( SettingsPage::OPTION_NAME, array( 'font' => 'comic-sans' ) );
		$this->assertSame( '', Theming::font(), 'A value written past the sanitiser never reaches the page.' );
	}

	/** Every published pair but the system one ships its stylesheet with the plugin. */
	public function test_every_font_pair_but_the_system_one_is_vendored(): void {
		foreach ( array_keys( Palettes::FONTS ) as $font ) {
			$this->save( array( 'font' => $font ) );
			$this->assertSame( $font === 'system' ? '' : 'assets/css/fonts/' . $font . '.css', Theming::font_stylesheet(), $font );
		}
	}

	/**
	 * A font never loads from a host the site owner did not choose: every
	 * stylesheet the plugin prints for a reading is its own, and every vendored
	 * stylesheet points only at files beside it.
	 */
	public function test_no_remote_font_url_anywhere_the_plugin_emits(): void {
		$this->save(
			array(
				'palette_preset' => 'practitioner',
				'font'           => 'fraunces-jost',
			)
		);
		$html = $this->frontend_styles();

		$this->assertStringContainsString( 'assets/css/fonts/fraunces-jost.css', $html );
		$this->assertStringContainsString( '--roxy-font:fraunces-jost;', $html );
		preg_match_all( '#(?:href=[\'"]|url\(\s*[\'"]?)([^\'")\s]+)#', $html, $urls );
		$this->assertNotEmpty( $urls[1] );
		foreach ( $urls[1] as $url ) {
			$this->assertStringStartsWith( plugins_url( '/', ROXYAPI_PLUGIN_FILE ), $url, 'Printed for a reading.' );
		}

		$root = plugin_dir_path( ROXYAPI_PLUGIN_FILE );
		$css  = array_merge( array( $root . 'assets/css/roxy-ui-tokens.css' ), (array) glob( $root . 'assets/css/fonts/*.css' ) );
		$this->assertGreaterThan( 1, count( $css ) );
		foreach ( $css as $file ) {
			$body = (string) file_get_contents( (string) $file );
			$this->assertStringNotContainsString( '@import', $body, $file );
			preg_match_all( '#url\(\s*[\'"]?([^\'")\s]+)#', $body, $refs );
			foreach ( $refs[1] as $ref ) {
				$this->assertStringStartsWith( './', $ref, $file );
				$this->assertFileExists( dirname( (string) $file ) . '/' . $ref, $file );
			}
		}
	}

	/** Every reading loads the glyph face from the site, under the family the tokens draw symbols in. */
	public function test_the_glyph_face_loads_under_the_family_the_tokens_name(): void {
		$this->assertStringContainsString( 'assets/css/fonts/symbols.css', $this->frontend_styles() );

		$root = plugin_dir_path( ROXYAPI_PLUGIN_FILE ) . 'assets/css/';
		preg_match( '#--roxy-font-symbol:\s*["\']([^"\']+)#', (string) file_get_contents( $root . 'roxy-ui-tokens.css' ), $token );
		preg_match_all( '#font-family:\s*["\']([^"\']+)#', (string) file_get_contents( $root . 'fonts/symbols.css' ), $faces );
		$this->assertNotEmpty( $token, 'The tokens name a glyph family.' );
		$this->assertSame( array( $token[1] ), array_values( array_unique( $faces[1] ) ) );
	}
}
