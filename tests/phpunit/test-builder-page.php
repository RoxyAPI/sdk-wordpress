<?php
/**
 * Builder access, bundled data, and entry points from the library.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\BuilderPage;
use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Admin\ShortcodesPage;
use RoxyAPI\Generated\Builder\Manifest;

class Test_Builder_Page extends \WP_UnitTestCase {

	/**
	 * Admin menu state restored after each test.
	 *
	 * @var array<string, mixed>
	 */
	private array $menu_state = array();

	public function setUp(): void {
		parent::setUp();
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		foreach ( array( 'menu', 'submenu', 'admin_page_hooks', '_registered_pages', '_parent_pages' ) as $key ) {
			$this->menu_state[ $key ] = $GLOBALS[ $key ] ?? null;
			$GLOBALS[ $key ]          = array();
		}
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	public function tearDown(): void {
		unset( $_GET['reading'] );
		wp_dequeue_script( 'roxyapi-builder' );
		wp_deregister_script( 'roxyapi-builder' );
		wp_dequeue_script( 'roxyapi-geocode' );
		wp_dequeue_style( 'roxyapi-builder' );
		foreach ( $this->menu_state as $key => $value ) {
			if ( null === $value ) {
				unset( $GLOBALS[ $key ] );
			} else {
				$GLOBALS[ $key ] = $value;
			}
		}
		parent::tearDown();
	}

	public function test_menu_requires_admin_capability(): void {
		global $submenu;

		SettingsPage::add_menu();
		$items = array_values(
			array_filter(
				$submenu[ SettingsPage::PAGE_SLUG ],
				static function ( $item ) {
					return BuilderPage::PAGE_SLUG === $item[2];
				}
			)
		);

		$this->assertCount( 1, $items );
		$this->assertSame( 'manage_options', $items[0][1] );
		$this->assertTrue( current_user_can( $items[0][1] ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$this->assertFalse( current_user_can( $items[0][1] ) );
		ob_start();
		BuilderPage::render();
		$this->assertSame( '', ob_get_clean() );

		BuilderPage::enqueue( get_plugin_page_hookname( BuilderPage::PAGE_SLUG, SettingsPage::PAGE_SLUG ) );
		$this->assertFalse( wp_script_is( 'roxyapi-builder', 'enqueued' ) );
	}

	public function test_renders_with_localized_manifest_and_preselection(): void {
		$_GET['reading'] = 'roxy_natal_chart';
		BuilderPage::enqueue( get_plugin_page_hookname( BuilderPage::PAGE_SLUG, SettingsPage::PAGE_SLUG ) );

		ob_start();
		BuilderPage::render();
		$html = (string) ob_get_clean();
		$this->assertStringContainsString( 'id="roxyapi-builder"', $html );
		$this->assertStringContainsString( 'Create a reading', $html );
		$this->assertTrue( wp_script_is( 'roxyapi-builder', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'roxyapi-geocode', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'roxyapi-builder', 'enqueued' ) );

		$inline = wp_scripts()->get_data( 'roxyapi-builder', 'data' );
		$this->assertIsString( $inline );
		$this->assertSame( 1, preg_match( '/var RoxyAPIBuilder = (.*);/', $inline, $matches ) );
		$data = json_decode( $matches[1], true );
		$this->assertSame( Manifest::all(), $data['manifest'] );
		$entries = array_column( $data['manifest']['entries'], null, 'tag' );
		$this->assertContains( 'patterns', $entries['roxy_natal_chart']['parts'] );
		$this->assertNotContains( 'annual-pillars', $entries['roxy_natal_chart']['parts'] );
		$this->assertContains( 'annual-pillars', $entries['roxy_calculate_luck_pillars']['parts'] );
		$this->assertSame( array(), $entries['roxy_generate_composite_chart']['parts'] );
		$this->assertSame( 'roxy_natal_chart', $data['reading'] );
		$this->assertContains( 'America/New_York', $data['timezones'] );
		$this->assertSame( 'Needed before the shortcode works.', $data['i18n']['needed'] );
	}

	public function test_assets_stay_off_other_screens(): void {
		BuilderPage::enqueue( 'dashboard' );
		$this->assertFalse( wp_script_is( 'roxyapi-builder', 'enqueued' ) );
	}

	public function test_library_links_known_reading_to_builder(): void {
		ob_start();
		ShortcodesPage::render();
		$html = (string) ob_get_clean();
		$url  = add_query_arg(
			array(
				'page'    => BuilderPage::PAGE_SLUG,
				'reading' => 'roxy_horoscope',
			),
			admin_url( 'admin.php' )
		);

		$this->assertStringContainsString( 'href="' . esc_url( $url ) . '"', $html );
		$this->assertMatchesRegularExpression( '/>\s*Customize\s*<\/a>/', $html );
	}
}
