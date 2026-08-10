<?php
/**
 * What a reading is CALLED, everywhere a site owner or a visitor reads it.
 *
 * Block titles, inserter descriptions and visitor-facing form headings used to
 * be the raw OpenAPI summary, which is written for a developer scanning an
 * endpoint list. That put "Get the twelve Arudha padas - Arudha Lagna
 * Calculator API (Vedic Astrology)" in the block inserter and "Transit Aspects
 * - Detailed transit-to-natal aspect analysis with interpretations" as a form
 * heading three lines deep at phone width.
 *
 * Two rules hold here. The word API never appears: a site owner is placing a
 * reading, not calling an endpoint. And no two blocks share a title, because
 * two identical rows in the inserter are unpickable, which is the failure the
 * domain suffix on a title exists to prevent.
 *
 * The names are derived in bin/generate.mjs from each operation's own summary,
 * so nothing here is hand-maintained and a new endpoint is named without an
 * edit. These assertions are the second opinion on that derivation.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Generated\Endpoints;

class Test_Reading_Names extends \WP_UnitTestCase {

	private static function blocks_dir(): string {
		return dirname( __DIR__, 2 ) . '/blocks/generated';
	}

	/**
	 * Every generated block.json, keyed by file path.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function block_files(): array {
		$blocks = array();
		foreach ( (array) glob( self::blocks_dir() . '/*/block.json' ) as $path ) {
			$decoded = json_decode( (string) file_get_contents( (string) $path ), true );
			if ( is_array( $decoded ) ) {
				$blocks[ (string) $path ] = $decoded;
			}
		}
		$this->assertGreaterThan( 100, count( $blocks ), 'The block catalogue must be generated.' );
		return $blocks;
	}

	public function test_no_block_title_or_description_mentions_an_api(): void {
		$offenders = array();
		foreach ( $this->block_files() as $block ) {
			foreach ( array( 'title', 'description' ) as $field ) {
				if ( preg_match( '/\bAPI\b/i', (string) ( $block[ $field ] ?? '' ) ) === 1 ) {
					$offenders[] = $block['name'] . " ({$field})";
				}
			}
		}
		$this->assertSame( array(), $offenders );
	}

	public function test_no_two_blocks_share_a_title(): void {
		$titles = array();
		foreach ( $this->block_files() as $block ) {
			$titles[] = (string) $block['title'];
		}
		$duplicates = array_keys( array_filter( array_count_values( $titles ), static fn( $n ) => $n > 1 ) );
		$this->assertSame(
			array(),
			$duplicates,
			'Two blocks with the same title are two indistinguishable rows in the inserter.'
		);
	}

	/**
	 * The point of the change: a title is short enough to read at a glance, and
	 * it is not the raw summary.
	 */
	public function test_a_block_title_is_the_reading_not_the_endpoint_summary(): void {
		$arudha = null;
		foreach ( $this->block_files() as $block ) {
			if ( $block['name'] === 'roxyapi/calculate-arudha-padas' ) {
				$arudha = $block;
			}
		}
		$this->assertNotNull( $arudha );
		$this->assertSame( 'Arudha Padas (Vedic Astrology)', $arudha['title'] );

		$summary = (string) Endpoints::get( 'calculateArudhaPadas' )['summary'];
		$this->assertStringContainsString( 'API', $summary, 'The summary is unchanged upstream.' );
	}

	/**
	 * A visitor reads the form heading, so it is the reading's name and nothing
	 * else. `calculateTransitAspects` is the worked example because its raw
	 * summary is long enough to wrap to three lines at phone width.
	 */
	public function test_a_visitor_form_heading_is_the_reading_name(): void {
		$spec = \RoxyAPI\Generated\Forms\CalculateTransitAspectsForm::spec();
		$this->assertSame( 'Transit Aspects', $spec['title'] );
	}

	public function test_no_generated_form_heading_mentions_an_api(): void {
		$offenders = array();
		foreach ( (array) glob( dirname( __DIR__, 2 ) . '/src/Generated/Forms/*.php' ) as $path ) {
			$class = '\\RoxyAPI\\Generated\\Forms\\' . basename( (string) $path, '.php' );
			if ( ! class_exists( $class ) ) {
				continue;
			}
			$title = (string) ( $class::spec()['title'] ?? '' );
			if ( preg_match( '/\bAPI\b/i', $title ) === 1 ) {
				$offenders[] = $class . ': ' . $title;
			}
		}
		$this->assertSame( array(), $offenders );
	}

	/**
	 * The registry carries one name per operation, and the admin library reads
	 * it, so the shortcode browser and the inserter cannot drift apart.
	 */
	public function test_every_endpoint_has_a_display_name(): void {
		$missing = array();
		foreach ( Endpoints::all() as $operation_id => $endpoint ) {
			$name = (string) ( $endpoint['display_name'] ?? '' );
			if ( $name === '' || preg_match( '/\bAPI\b/i', $name ) === 1 ) {
				$missing[] = $operation_id . ': ' . $name;
			}
		}
		$this->assertSame( array(), $missing );
	}

	public function test_the_admin_library_uses_the_display_name(): void {
		$rows = \RoxyAPI\Admin\Catalog::all();
		$by_tag = array();
		foreach ( $rows as $row ) {
			$by_tag[ $row['tag'] ] = $row;
		}
		$this->assertArrayHasKey( 'roxy_calculate_arudha_padas', $by_tag );
		$this->assertSame( 'Arudha Padas', $by_tag['roxy_calculate_arudha_padas']['title'] );
	}
}
