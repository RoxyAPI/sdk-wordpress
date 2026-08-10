<?php
/**
 * The Demo page renders one reading per click, never many.
 *
 * Every row on that page is a live, metered call. The page used to carry
 * "Run all" (every shortcode the plugin ships), "Run heroes only" and a
 * per-domain "Run all in this domain", and it ships to every site: `src/` is
 * not in `.distignore` and the page switches on for `WP_DEBUG` or a
 * local/development/staging environment, which is the ordinary state of a
 * staging clone. One click therefore spent a whole plan's worth of calls.
 *
 * Removing the buttons alone would not have fixed it. The trigger was a plain
 * GET, so a bookmark, a history entry or a pasted URL kept working after the
 * link was gone. `run_filter()` rejects the batch shapes outright, and these
 * tests hold that half, because it is the half that is invisible on screen.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\DemoPage;

class Test_Demo_Page_No_Batch extends Mock_Http_TestCase {

	/** Every URL shape that once matched more than one row. */
	private const BATCH_SHAPES = array( 'all', 'heroes', 'domain:vedic-astrology', 'domain:tarot' );

	public function setUp(): void {
		parent::setUp();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		unset( $_GET['run'] );
	}

	public function tearDown(): void {
		unset( $_GET['run'] );
		parent::tearDown();
	}

	/** Read the private filter the page uses to decide what renders. */
	private function run_filter(): string {
		$m = new \ReflectionMethod( DemoPage::class, 'run_filter' );
		$m->setAccessible( true );
		return (string) $m->invoke( null );
	}

	/** Ask the page whether a given row would render under the current filter. */
	private function should_run( string $run, string $tag ): bool {
		$m = new \ReflectionMethod( DemoPage::class, 'should_run' );
		$m->setAccessible( true );
		return (bool) $m->invoke( null, $run, $tag );
	}

	public function test_batch_url_shapes_are_refused(): void {
		foreach ( self::BATCH_SHAPES as $shape ) {
			$_GET['run'] = $shape;
			$resolved    = $this->run_filter();

			$this->assertNotSame( $shape, $resolved, "'{$shape}' must not survive as a batch filter." );
			// Whatever it degrades to must still not match more than one row.
			$this->assertFalse(
				$this->should_run( $resolved, 'roxy_horoscope' ) && $this->should_run( $resolved, 'roxy_life_path' ),
				"'{$shape}' must never match two different readings."
			);
		}
	}

	/** The real guarantee, stated directly: no filter value matches two rows. */
	public function test_no_filter_value_can_match_two_readings(): void {
		$candidates = array_merge( self::BATCH_SHAPES, array( '', '*', 'roxy_horoscope', 'domain:', 'ALL' ) );

		foreach ( $candidates as $candidate ) {
			$_GET['run'] = $candidate;
			$resolved    = $this->run_filter();

			$matched = array_filter(
				array( 'roxy_horoscope', 'roxy_life_path', 'roxy_natal_chart' ),
				fn( $tag ) => $this->should_run( $resolved, $tag )
			);

			$this->assertLessThanOrEqual( 1, count( $matched ), "'{$candidate}' matched more than one reading." );
		}
	}

	/** A single named reading still runs: this is a preview page, not a disabled one. */
	public function test_a_single_reading_still_runs(): void {
		$_GET['run'] = 'roxy_horoscope';

		$this->assertSame( 'roxy_horoscope', $this->run_filter() );
		$this->assertTrue( $this->should_run( 'roxy_horoscope', 'roxy_horoscope' ) );
		$this->assertFalse( $this->should_run( 'roxy_horoscope', 'roxy_life_path' ) );
	}

	/** Nothing runs on a plain visit. */
	public function test_nothing_runs_without_an_explicit_click(): void {
		$this->assertSame( '', $this->run_filter() );
		$this->assertFalse( $this->should_run( '', 'roxy_horoscope' ) );
	}

	/** No batch control is offered on screen either. */
	public function test_the_page_offers_no_run_many_control(): void {
		ob_start();
		DemoPage::render();
		$html = (string) ob_get_clean();

		foreach ( array( 'run=all', 'run=heroes', 'run=domain' ) as $needle ) {
			$this->assertStringNotContainsString( $needle, $html, "The page must not link {$needle}." );
		}
	}
}
