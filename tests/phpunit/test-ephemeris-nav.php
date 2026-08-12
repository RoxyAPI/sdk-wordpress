<?php
/**
 * Tests for month paging on the monthly ephemeris, shortcode and block.
 *
 * Three things have to hold together or the nav renders and does nothing: the
 * query string has to be narrowed to a month the API accepts, the render has to
 * ask the API for THAT month, and the nav has to appear under the table and
 * nowhere else. Then all three have to hold a second time, because a block
 * reaches the same endpoint through filters the shortcode half cannot see.
 *
 * The API call is canned. An unmocked render reaches production and still
 * passes, so every test here goes through Mock_Http_TestCase.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Api\Cache;
use RoxyAPI\Shortcodes\Registrar;
use RoxyAPI\Support\EphemerisNav;

class Test_Ephemeris_Nav extends Mock_Http_TestCase {

	/** Request bodies seen by the canned HTTP layer, newest last. */
	private array $sent = array();

	public function setUp(): void {
		parent::setUp();

		$response = array(
			'year'  => 2026,
			'month' => 8,
			'days'  => array(
				array(
					'date'      => '2026-08-01',
					'positions' => array(
						array(
							'planet'       => 'Sun',
							'longitude'    => 129.1,
							'sign'         => 'Leo',
							'degreeInSign' => 9.1,
							'isRetrograde' => false,
						),
					),
				),
			),
		);

		$this->mock_responses['astrology/planets/monthly']                     = $response;
		$this->mock_responses['vedic-astrology/planetary-positions/monthly']   = $response;

		// Watch what the shortcode actually asked for. Runs ahead of the canned
		// responder and returns nothing, so it observes without answering.
		add_filter( 'pre_http_request', array( $this, 'record_request' ), 1, 3 );

		Registrar::register_generated();
		EphemerisNav::register();

		$_SERVER['REQUEST_URI'] = '/?page_id=70';
		Cache::flush_all();
	}

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'record_request' ), 1 );
		unset( $_GET[ EphemerisNav::QUERY_VAR ], $_GET[ EphemerisNav::YEAR_VAR ], $_GET[ EphemerisNav::MONTH_VAR ] );
		$_SERVER['REQUEST_URI'] = '/';
		Cache::flush_all();
		parent::tearDown();
	}

	/**
	 * @param mixed                $preempt Short-circuit value.
	 * @param array<string, mixed> $args    Request args.
	 * @param string               $url     Request URL.
	 * @return mixed
	 */
	public function record_request( $preempt, $args, $url ) {
		if ( strpos( $url, 'monthly' ) !== false && isset( $args['body'] ) && is_string( $args['body'] ) ) {
			$decoded      = json_decode( $args['body'], true );
			$this->sent[] = is_array( $decoded ) ? $decoded : array();
		}
		return $preempt;
	}

	/** The body of the last call the shortcode made. */
	private function last_request(): array {
		$this->assertNotEmpty( $this->sent, 'The shortcode must have called the API.' );
		return (array) end( $this->sent );
	}

	private function ask( string $value ): void {
		$_GET[ EphemerisNav::QUERY_VAR ] = $value;
		$_SERVER['REQUEST_URI']          = '/?page_id=70&' . EphemerisNav::QUERY_VAR . '=' . rawurlencode( $value );
	}

	private function this_month(): array {
		return array( (int) gmdate( 'Y' ), (int) gmdate( 'n' ) );
	}

	// -------------------------------------------------------------------------
	// Which month gets requested
	// -------------------------------------------------------------------------

	public function test_no_query_and_no_attribute_asks_for_nothing(): void {
		do_shortcode( '[roxy_get_monthly_tropical_ephemeris]' );

		// Both operations default to the month in progress, so the plugin sends
		// neither field rather than pinning the page to a month.
		$this->assertSame( array(), $this->last_request() );
	}

	public function test_a_valid_query_month_is_what_gets_requested(): void {
		$this->ask( '2019-03' );
		do_shortcode( '[roxy_get_monthly_tropical_ephemeris]' );

		$this->assertSame( array( 'year' => 2019, 'month' => 3 ), $this->last_request() );
	}

	/**
	 * The precedence rule, and the reason the nav works on a pinned page: the
	 * attribute is where the visitor STARTS, the query string is where they are.
	 */
	public function test_the_query_string_overrides_a_pinned_attribute(): void {
		$this->ask( '2019-03' );
		do_shortcode( '[roxy_get_monthly_tropical_ephemeris year="2001" month="7"]' );

		$this->assertSame( array( 'year' => 2019, 'month' => 3 ), $this->last_request() );
	}

	public function test_the_attribute_still_decides_with_no_query_string(): void {
		do_shortcode( '[roxy_get_monthly_tropical_ephemeris year="2001" month="7"]' );

		$this->assertSame( array( 'year' => 2001, 'month' => 7 ), $this->last_request() );
	}

	/**
	 * Everything the query string can carry that is not a month this API
	 * answers. Each one falls back silently: the page renders the shortcode
	 * default rather than erroring or asking for a month that would 400.
	 *
	 * @return array<string, array<int, string>>
	 */
	public function bad_query_values(): array {
		return array(
			'below the range'   => array( '1899-12' ),
			'above the range'   => array( '2101-01' ),
			'month zero'        => array( '2026-00' ),
			'month thirteen'    => array( '2026-13' ),
			'unpadded month'    => array( '2026-3' ),
			'not a date'        => array( 'now' ),
			'sql-ish'           => array( "2026-08' OR 1=1" ),
			'html'              => array( '<script>alert(1)</script>' ),
			'empty'             => array( '' ),
		);
	}

	/**
	 * @dataProvider bad_query_values
	 */
	public function test_an_invalid_query_month_falls_back_to_the_default( string $value ): void {
		$this->ask( $value );
		$out = do_shortcode( '[roxy_get_monthly_tropical_ephemeris]' );

		$this->assertSame( array(), $this->last_request() );
		$this->assertStringContainsString( 'roxyapi-eph-nav', $out, 'The page still renders.' );
	}

	/**
	 * An invalid value is not an opinion, so the attribute it would have
	 * overridden still stands.
	 */
	public function test_an_invalid_query_month_leaves_a_pinned_attribute_alone(): void {
		$this->ask( '2101-01' );
		do_shortcode( '[roxy_get_monthly_tropical_ephemeris year="2001" month="7"]' );

		$this->assertSame( array( 'year' => 2001, 'month' => 7 ), $this->last_request() );
	}

	/**
	 * The picker posts two values because a GET form cannot join two selects
	 * into one parameter without JavaScript. They are read only when the
	 * canonical parameter is absent.
	 */
	public function test_the_picker_pair_selects_a_month(): void {
		$_GET[ EphemerisNav::YEAR_VAR ]  = '2019';
		$_GET[ EphemerisNav::MONTH_VAR ] = '03';
		$_SERVER['REQUEST_URI']          = '/?page_id=70&roxy_eph_year=2019&roxy_eph_month=03';

		do_shortcode( '[roxy_get_monthly_tropical_ephemeris]' );

		$this->assertSame( array( 'year' => 2019, 'month' => 3 ), $this->last_request() );
	}

	public function test_a_link_wins_over_a_stale_picker_pair(): void {
		$this->ask( '2020-05' );
		$_GET[ EphemerisNav::YEAR_VAR ]  = '2019';
		$_GET[ EphemerisNav::MONTH_VAR ] = '03';

		do_shortcode( '[roxy_get_monthly_tropical_ephemeris]' );

		$this->assertSame( array( 'year' => 2020, 'month' => 5 ), $this->last_request() );
	}

	// -------------------------------------------------------------------------
	// The nav itself
	// -------------------------------------------------------------------------

	/**
	 * @return array<string, array<int, string>>
	 */
	public function paged_tags(): array {
		return array(
			'tropical' => array( 'roxy_get_monthly_tropical_ephemeris' ),
			'sidereal' => array( 'roxy_get_monthly_ephemeris' ),
		);
	}

	/**
	 * @dataProvider paged_tags
	 */
	public function test_both_ephemeris_tags_get_the_nav( string $tag ): void {
		$this->ask( '2019-03' );
		$out = do_shortcode( '[' . $tag . ']' );

		$this->assertStringContainsString( 'roxyapi-eph-nav', $out );
		$this->assertStringContainsString( 'roxy_eph=2019-02', $out, 'Previous month.' );
		$this->assertStringContainsString( 'roxy_eph=2019-04', $out, 'Next month.' );
		$this->assertStringContainsString( '<form class="roxyapi-eph-form" method="get"', $out );
		$this->assertStringContainsString( 'name="' . EphemerisNav::MONTH_VAR . '"', $out );
		$this->assertStringContainsString( 'name="' . EphemerisNav::YEAR_VAR . '"', $out );
	}

	public function test_no_other_shortcode_gets_the_nav(): void {
		$this->mock_responses['astrology/signs/aries'] = array(
			'sign'    => 'aries',
			'element' => 'Fire',
		);

		$this->assertStringNotContainsString(
			'roxyapi-eph-nav',
			do_shortcode( '[roxy_get_zodiac_sign id="aries"]' )
		);
	}

	/**
	 * The nav has to be able to say "there is nothing before this", or a visitor
	 * pages into a month the API refuses and reads an error instead of a table.
	 */
	public function test_paging_stops_at_the_ends_of_the_range(): void {
		$this->ask( '1900-01' );
		$first = do_shortcode( '[roxy_get_monthly_tropical_ephemeris]' );
		$this->assertStringContainsString( 'roxyapi-eph-link is-disabled', $first );
		$this->assertStringContainsString( 'roxy_eph=1900-02', $first );

		$this->ask( '2100-12' );
		$last = do_shortcode( '[roxy_get_monthly_tropical_ephemeris]' );
		$this->assertStringContainsString( 'roxyapi-eph-link is-disabled', $last );
		$this->assertStringContainsString( 'roxy_eph=2100-11', $last );
	}

	public function test_paging_crosses_the_year_boundary(): void {
		$this->ask( '2020-01' );
		$out = do_shortcode( '[roxy_get_monthly_tropical_ephemeris]' );

		$this->assertStringContainsString( 'roxy_eph=2019-12', $out );
		$this->assertStringContainsString( 'roxy_eph=2020-02', $out );
	}

	/**
	 * A browser drops the query string of a GET form action, so the parameters
	 * that identify the page have to travel as hidden fields or the picker
	 * submits to the front page on a plain-permalink site.
	 */
	public function test_the_picker_carries_the_rest_of_the_query_string(): void {
		$this->ask( '2019-03' );
		$out = do_shortcode( '[roxy_get_monthly_tropical_ephemeris]' );

		$this->assertStringContainsString( '<input type="hidden" name="page_id" value="70" />', $out );
		$this->assertStringNotContainsString( 'name="' . EphemerisNav::QUERY_VAR . '"', $out );
	}

	/**
	 * With nothing asked for, the nav names the month the API defaulted to. It
	 * reads UTC because the API does; the site timezone would disagree with the
	 * table on the first and last day of a month.
	 */
	public function test_the_nav_names_the_month_on_screen(): void {
		list( $year, $month ) = $this->this_month();
		$out                  = do_shortcode( '[roxy_get_monthly_tropical_ephemeris]' );

		$this->assertStringContainsString(
			'<span class="roxyapi-eph-current">' . wp_date( 'F Y', gmmktime( 12, 0, 0, $month, 1, $year ), new \DateTimeZone( 'UTC' ) ) . '</span>',
			$out
		);
	}

	// -------------------------------------------------------------------------
	// The block path
	//
	// A generated block's render.php calls the shortcode class directly with no
	// tag, so `shortcode_atts()` filters under the empty name and
	// `do_shortcode_tag` never runs. Neither shortcode filter can reach a block,
	// so without its own pair a page authored with the block rather than the
	// shortcode would ignore the query string and show no nav.
	// -------------------------------------------------------------------------

	/** Block rendering reads the build output. */
	private function require_build(): void {
		if ( ! is_dir( dirname( ROXYAPI_PLUGIN_FILE ) . '/build/blocks' ) ) {
			$this->markTestSkipped( 'Plugin not built; run `npm run build:all` before phpunit.' );
		}
	}

	/**
	 * @param array<string, mixed> $attrs Block attributes.
	 * @return array<string, mixed>
	 */
	private function parsed_block( string $name, array $attrs = array() ): array {
		return array(
			'blockName'    => $name,
			'attrs'        => $attrs,
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);
	}

	/**
	 * @param array<string, mixed> $attrs Block attributes.
	 */
	private function render_eph_block( string $name, array $attrs = array() ): string {
		$this->require_build();
		return (string) render_block( $this->parsed_block( $name, $attrs ) );
	}

	/**
	 * @return array<string, array<int, string>>
	 */
	public function paged_blocks(): array {
		return array(
			'tropical' => array( 'roxyapi/get-monthly-tropical-ephemeris' ),
			'sidereal' => array( 'roxyapi/get-monthly-ephemeris' ),
		);
	}

	/**
	 * @dataProvider paged_blocks
	 */
	public function test_both_ephemeris_blocks_get_the_nav( string $name ): void {
		$this->ask( '2019-03' );
		$out = $this->render_eph_block( $name );

		$this->assertStringContainsString( 'roxyapi-eph-nav', $out );
		$this->assertStringContainsString( 'roxy_eph=2019-02', $out, 'Previous month.' );
		$this->assertStringContainsString( 'roxy_eph=2019-04', $out, 'Next month.' );
		$this->assertStringContainsString( 'name="' . EphemerisNav::MONTH_VAR . '"', $out );
		$this->assertStringContainsString( 'name="' . EphemerisNav::YEAR_VAR . '"', $out );
	}

	/**
	 * @dataProvider paged_blocks
	 */
	public function test_a_block_asks_the_api_for_the_month_in_the_query_string( string $name ): void {
		$this->ask( '2019-03' );
		$this->render_eph_block( $name );

		$this->assertSame( array( 'year' => 2019, 'month' => 3 ), $this->last_request() );
	}

	/** The precedence rule holds identically on the block path. */
	public function test_the_query_string_overrides_a_pinned_block_attribute(): void {
		$this->ask( '2019-03' );
		$this->render_eph_block(
			'roxyapi/get-monthly-tropical-ephemeris',
			array(
				'year'  => '2001',
				'month' => '7',
			)
		);

		$this->assertSame( array( 'year' => 2019, 'month' => 3 ), $this->last_request() );
	}

	public function test_a_pinned_block_attribute_still_decides_with_no_query_string(): void {
		$out = $this->render_eph_block(
			'roxyapi/get-monthly-tropical-ephemeris',
			array(
				'year'  => '2001',
				'month' => '7',
			)
		);

		$this->assertSame( array( 'year' => 2001, 'month' => 7 ), $this->last_request() );
		$this->assertStringContainsString( 'roxy_eph=2001-06', $out, 'The nav pages from the pinned month.' );
	}

	public function test_the_picker_pair_selects_a_month_on_a_block(): void {
		$_GET[ EphemerisNav::YEAR_VAR ]  = '2019';
		$_GET[ EphemerisNav::MONTH_VAR ] = '03';

		$this->render_eph_block( 'roxyapi/get-monthly-tropical-ephemeris' );

		$this->assertSame( array( 'year' => 2019, 'month' => 3 ), $this->last_request() );
	}

	/**
	 * @dataProvider bad_query_values
	 */
	public function test_an_invalid_query_month_falls_back_on_a_block( string $value ): void {
		$this->ask( $value );
		$out = $this->render_eph_block( 'roxyapi/get-monthly-tropical-ephemeris' );

		$this->assertSame( array(), $this->last_request() );
		$this->assertStringContainsString( 'roxyapi-eph-nav', $out );
	}

	public function test_no_other_block_gets_the_nav(): void {
		$this->mock_responses['astrology/signs/aries'] = array(
			'sign'    => 'aries',
			'element' => 'Fire',
		);

		$out = $this->render_eph_block( 'roxyapi/get-zodiac-sign', array( 'id' => 'aries' ) );

		// Assert it rendered before asserting what it lacks, or an unregistered
		// block name would pass this by returning nothing at all.
		$this->assertStringContainsString( 'roxyapi-embed', $out );
		$this->assertStringNotContainsString( 'roxyapi-eph-nav', $out );
	}

	/**
	 * Exactly one nav per placement. Output that already carries one is left
	 * alone, so a block whose markup passed through `do_shortcode` on the way
	 * out cannot end up with two navs paging against each other.
	 */
	public function test_the_nav_is_never_appended_twice(): void {
		$this->ask( '2019-03' );
		$block = $this->parsed_block( 'roxyapi/get-monthly-tropical-ephemeris' );

		$this->require_build();
		$once  = (string) render_block( $block );
		$twice = EphemerisNav::append_block_nav( $once, $block );

		$this->assertSame( 1, substr_count( $once, 'roxyapi-eph-nav' ) );
		$this->assertSame( $once, $twice );
	}

	/** The whole pipeline, from serialized block markup as a post stores it. */
	public function test_serialized_block_markup_pages_end_to_end(): void {
		$this->require_build();
		$this->ask( '2019-03' );
		$out = do_blocks( '<!-- wp:roxyapi/get-monthly-tropical-ephemeris /-->' );

		$this->assertSame( array( 'year' => 2019, 'month' => 3 ), $this->last_request() );
		$this->assertSame( 1, substr_count( $out, 'roxyapi-eph-nav' ) );
	}

	/** No script tag, no inline handler, no key: the nav is plain HTML. */
	public function test_the_nav_ships_no_javascript(): void {
		$this->ask( '2019-03' );
		$out = do_shortcode( '[roxy_get_monthly_tropical_ephemeris]' );
		$nav = substr( $out, (int) strpos( $out, '<nav class="roxyapi-eph-nav"' ) );

		$this->assertStringNotContainsString( '<script', $nav );
		$this->assertStringNotContainsString( 'onclick', $nav );
		$this->assertStringNotContainsString( 'X-API-Key', $nav );
	}
}
