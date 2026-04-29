<?php
/**
 * Tests for the Shortcodes Library catalog.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\Catalog;
use RoxyAPI\Admin\Onboarding;
use RoxyAPI\Admin\ShortcodesPage;

class Test_Catalog extends \WP_UnitTestCase {

	public function test_domains_returns_at_least_nine_reading_domains_with_non_zero_counts(): void {
		$domains = Catalog::domains();

		// User-facing reading domains: Western Astrology, Vedic Astrology, Tarot,
		// Numerology, I Ching, Dreams, Biorhythm, Angel Numbers, Crystals.
		$this->assertGreaterThanOrEqual(
			9,
			count( $domains ),
			'Expected at least nine domains in the library tab list.'
		);

		foreach ( $domains as $domain ) {
			$this->assertNotEmpty( $domain['tag'], 'Domain tag must not be empty.' );
			$this->assertNotEmpty( $domain['slug'], 'Domain slug must not be empty.' );
			$this->assertGreaterThan( 0, $domain['count'], 'Domain ' . $domain['tag'] . ' should have at least one shortcode.' );
		}
	}

	public function test_all_returns_hero_rows_plus_long_tail_endpoints(): void {
		// Catalog composition = one row per hero shortcode (Manifest) +
		// one row per non-hero endpoint (Endpoints). Heroes that have an
		// operationId already in the hero-flag set still contribute one
		// hero row, NOT a long-tail row, because Endpoints filters those
		// out via the `hero` flag and Catalog adds them via Manifest. Shape
		// the assertion against the live counts so the test stays valid as
		// the spec evolves.
		$hero_rows           = count( \RoxyAPI\Generated\Heroes\Manifest::all() );
		$non_hero_endpoints  = count(
			array_filter(
				\RoxyAPI\Generated\Endpoints::all(),
				static function ( $ep ) {
					return empty( $ep['hero'] );
				}
			)
		);
		$entries = Catalog::all();
		$this->assertSame(
			$hero_rows + $non_hero_endpoints,
			count( $entries ),
			'Catalog rows must equal Manifest hero count plus non-hero endpoint count.'
		);
	}

	public function test_every_hero_shortcode_appears_with_hero_true(): void {
		$entries = Catalog::all();
		$tag_to_row = array();
		foreach ( $entries as $row ) {
			$tag_to_row[ $row['tag'] ] = $row;
		}

		foreach ( Onboarding::hero_shortcodes() as $hero ) {
			$tag = $this->extract_tag( $hero['code'] );
			$this->assertArrayHasKey( $tag, $tag_to_row, 'Hero ' . $tag . ' missing from catalog.' );
			$this->assertTrue( $tag_to_row[ $tag ]['hero'], 'Hero ' . $tag . ' should have hero => true.' );
			$this->assertSame( $hero['code'], $tag_to_row[ $tag ]['sample'], 'Hero sample should match Onboarding.' );
		}
	}

	public function test_no_duplicate_tags_in_catalog(): void {
		$tags = array();
		foreach ( Catalog::all() as $row ) {
			$tags[] = $row['tag'];
		}
		$this->assertSame(
			count( $tags ),
			count( array_unique( $tags ) ),
			'Catalog must not contain duplicate shortcode tags.'
		);
	}

	public function test_every_entry_has_non_empty_sample_description_and_domain(): void {
		foreach ( Catalog::all() as $row ) {
			$this->assertNotSame( '', $row['sample'], 'Sample is required for ' . $row['tag'] );
			$this->assertNotSame( '', $row['description'], 'Description is required for ' . $row['tag'] );
			$this->assertNotSame( '', $row['domain'], 'Domain is required for ' . $row['tag'] );
			$this->assertStringStartsWith( '[', $row['sample'], 'Sample must look like a shortcode for ' . $row['tag'] );
		}
	}

	public function test_render_outputs_library_for_admin(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		ob_start();
		ShortcodesPage::render();
		$out = (string) ob_get_clean();

		$this->assertStringContainsString( 'roxyapi-shortcodes', $out );
		$this->assertStringContainsString( 'roxyapi-library-grid', $out );
		$this->assertStringContainsString( '[roxy_horoscope', $out, 'A hero shortcode should appear in the rendered library.' );
	}

	public function test_render_returns_early_for_non_admin(): void {
		$subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber_id );

		ob_start();
		ShortcodesPage::render();
		$out = (string) ob_get_clean();

		$this->assertSame( '', $out, 'Non-admins must not see the library.' );
	}

	private function extract_tag( string $sample ): string {
		if ( preg_match( '/^\[\s*([a-z0-9_]+)/i', $sample, $matches ) === 1 ) {
			return strtolower( $matches[1] );
		}
		return '';
	}
}
