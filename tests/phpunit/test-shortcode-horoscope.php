<?php
/**
 * Tests for the [roxy_horoscope] hero shortcode.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Support\Encryption;

class Test_Shortcode_Horoscope extends Mock_Http_TestCase {

	/** Every URL the mock answered, in order. */
	private array $urls = array();

	public function setUp(): void {
		parent::setUp();
		$this->urls = array();
		\RoxyAPI\Api\Cache::flush_all();
		$test_key = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.0123456789abcdef.test_key_plaintext';
		update_option(
			'roxyapi_settings',
			array(
				'api_key_encrypted' => Encryption::encrypt( $test_key ),
			)
		);
		$this->mock_responses['astrology/horoscope/aries/daily'] = array(
			'sign'            => 'aries',
			'overview'        => 'A bold day ahead.',
			'love'            => 'Good vibes with Leo.',
			'career'          => 'Focus on planning.',
			'health'          => 'Hydrate.',
			'finance'         => 'Review your budget.',
			'advice'          => 'Stay patient.',
			'luckyNumber'     => 7,
			'luckyColor'      => 'red',
			'energyRating'    => 8,
			'moonSign'        => 'leo',
			'moonPhase'       => 'waxing crescent',
			'compatibleSigns' => array( 'leo', 'sagittarius' ),
		);
		foreach ( array( 'weekly', 'monthly', 'yearly' ) as $period ) {
			$this->mock_responses[ 'astrology/horoscope/aries/' . $period ] = array(
				'sign'     => 'aries',
				'overview' => 'The ' . $period . ' reading.',
			);
		}
	}

	public function tearDown(): void {
		$_POST = array();
		\RoxyAPI\Api\Cache::flush_all();
		parent::tearDown();
	}

	public function mock_http( $preempt, $args, $url ) {
		$this->urls[] = $url;
		return parent::mock_http( $preempt, $args, $url );
	}

	/** The query string of the one request a render made. */
	private function query_of_last_request(): array {
		$this->assertCount( 1, $this->urls, 'One render is one API call.' );
		$query = array();
		parse_str( (string) wp_parse_url( $this->urls[0], PHP_URL_QUERY ), $query );
		return $query;
	}

	/** Submit the sign picker the way a visitor does, with a valid nonce. */
	private function submit_sign( string $sign ): void {
		$_POST = array(
			'roxyapi_action' => \RoxyAPI\Shortcodes\Horoscope::ACTION,
			'roxyapi_nonce'  => wp_create_nonce( \RoxyAPI\Shortcodes\Horoscope::ACTION ),
			'sign'           => $sign,
		);
	}

	public function test_renders_horoscope_card(): void {
		$out = do_shortcode( '[roxy_horoscope sign="aries"]' );
		$this->assertStringContainsString( 'roxy-horoscope-card', $out );
		$this->assertStringContainsString( 'A bold day ahead.', $out );
		$this->assertStringContainsString( 'Lucky number', $out );
	}

	public function test_invalid_sign_falls_back_to_default(): void {
		$out = do_shortcode( '[roxy_horoscope sign="dragon"]' );
		$this->assertStringContainsString( 'roxy-horoscope-card', $out );
		$this->assertStringContainsString( 'aries', $out );
	}

	public function test_payload_script_is_inert_json(): void {
		$out = do_shortcode( '[roxy_horoscope sign="aries"]' );
		// The component data ships as an inert JSON block read by the element on
		// connect, never as executable JavaScript. The API key still never
		// reaches the browser: this is the server-rendered response, not the key.
		$this->assertStringContainsString( '<script type="application/json" class="roxy-data">', $out );
		$this->assertStringNotContainsString( '<script>', $out );
	}

	public function test_caches_response(): void {
		do_shortcode( '[roxy_horoscope sign="aries"]' );
		// Sanitize::date_string("today") resolves to YYYY-MM-DD before reaching
		// the cache key (the SaaS spec rejects literal "today"). The cache key
		// therefore includes the resolved date, not the literal token.
		$resolved_date = wp_date( 'Y-m-d' );
		// The cache folds the effective display language into the key after the
		// request args, so the expected key includes it too.
		$args = array( 'date' => $resolved_date );
		$lang = \RoxyAPI\Support\Language::resolve();
		if ( $lang !== '' ) {
			$args['lang'] = $lang;
		}
		// The cache epoch leads the hash so one option write retires every key
		// at once. Read the live value rather than assume one.
		$epoch = (string) get_option( 'roxyapi_cache_epoch', '' );
		$key   = 'roxyapi_' . md5( $epoch . '|astrology/horoscope/aries/daily|' . wp_json_encode( $args ) );
		$this->assertNotFalse( get_transient( $key ) );
	}
	/**
	 * `period` picks the operation, and `date` reaches every one of them:
	 * the day for daily, any day of the week or month for the two longer
	 * periods, the year for yearly. Before this the attribute was silently
	 * dropped on three of the four, so a placed October page rendered the
	 * current month.
	 */
	public function test_period_dispatches_and_date_reaches_each_operation(): void {
		$cases = array(
			'daily'   => array( 'daily', array( 'date' => '2026-10-07' ) ),
			'weekly'  => array( 'weekly', array( 'date' => '2026-10-05' ) ),
			'monthly' => array( 'monthly', array( 'date' => '2026-10-01' ) ),
			'yearly'  => array( 'yearly', array( 'year' => '2026' ) ),
		);
		foreach ( $cases as $period => list( $path, $expected ) ) {
			$this->urls = array();
			\RoxyAPI\Api\Cache::flush_all();
			$out = do_shortcode( '[roxy_horoscope sign="aries" period="' . $period . '" date="2026-10-07"]' );
			$this->assertStringContainsString( 'astrology/horoscope/aries/' . $path, $this->urls[0] ?? '', $period );
			$query = $this->query_of_last_request();
			foreach ( $expected as $key => $value ) {
				$this->assertSame( $value, $query[ $key ] ?? null, "$period sends $key" );
			}
			$this->assertStringContainsString( 'roxy-horoscope-card', $out );
		}
	}

	/**
	 * A weekly or monthly date is anchored to the first day of its period
	 * before it reaches the cache key, so the seven days of one week share
	 * one transient and one metered call rather than seven.
	 */
	public function test_week_and_month_share_one_cache_entry_across_their_days(): void {
		do_shortcode( '[roxy_horoscope sign="aries" period="weekly" date="2026-10-07"]' );
		do_shortcode( '[roxy_horoscope sign="aries" period="weekly" date="2026-10-11"]' );
		do_shortcode( '[roxy_horoscope sign="aries" period="monthly" date="2026-10-07"]' );
		do_shortcode( '[roxy_horoscope sign="aries" period="monthly" date="2026-10-31"]' );
		$this->assertCount( 2, $this->urls, 'A Wednesday and the Sunday after it are one week; the 7th and the 31st are one month.' );
	}

	/**
	 * A Sunday belongs to the week that STARTED the Monday before it. PHP's
	 * relative formats put a Sunday in the following week, which is the
	 * off-by-one this pins.
	 */
	public function test_sunday_anchors_to_the_monday_before_it(): void {
		do_shortcode( '[roxy_horoscope sign="aries" period="weekly" date="2026-10-11"]' );
		$this->assertSame( '2026-10-05', $this->query_of_last_request()['date'] );
	}

	/**
	 * The visitor form renders the period the placement asked for. It used
	 * to render the daily whatever `period` said, so `[roxy_horoscope
	 * period="monthly"]` answered a picked sign with the wrong reading.
	 */
	public function test_form_submission_honours_the_placement_period(): void {
		$this->submit_sign( 'aries' );
		$out = do_shortcode( '[roxy_horoscope period="monthly"]' );
		$this->assertStringContainsString( 'astrology/horoscope/aries/monthly', $this->urls[0] ?? '' );
		$this->assertStringContainsString( 'The monthly reading.', $out );
		$this->assertStringContainsString( 'roxyapi-form--horoscope', $out, 'The picker renders under the result.' );
	}

	public function test_unknown_period_falls_back_to_daily(): void {
		do_shortcode( '[roxy_horoscope sign="aries" period="love"]' );
		$this->assertStringContainsString( 'astrology/horoscope/aries/daily', $this->urls[0] ?? '' );
	}
}
