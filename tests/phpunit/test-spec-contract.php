<?php
/**
 * Spec-driven contract test: every hero shortcode's outgoing HTTP request
 * matches the SaaS-side OpenAPI spec snapshot at specs/openapi.json.
 *
 * Tests the plugin from the outside in. Each hero is exercised through
 * do_shortcode() with the canonical example surfaced in admin onboarding,
 * the outgoing wp_remote_request is captured via pre_http_request, and the
 * captured URL plus args are diffed against the spec. A mismatch surfaces
 * as a precise diagnostic so the next contributor knows exactly what
 * regenerated.
 *
 * Negative tests at the bottom prove the validator actually rejects
 * non-compliant requests; without those the hero tests could be passing
 * because the validator is broken, not because the plugin is correct.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\Onboarding;
use RoxyAPI\Support\Encryption;
use RoxyAPI\Tests\Lib\Spec_Contract_Validator;

require_once __DIR__ . '/lib/Spec_Contract_Validator.php';

class Test_Spec_Contract extends \WP_UnitTestCase {

	/**
	 * Captured (URL, args) tuples from the most recent shortcode render.
	 *
	 * @var array<int, array{url:string, args:array<string,mixed>}>
	 */
	private array $captured = array();

	public function setUp(): void {
		parent::setUp();
		// A real-shaped key so ApiKey::get() returns a value and the request
		// goes through. The pre_http_request hook short-circuits the network.
		$test_key = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.0123456789abcdef.test_key_plaintext';
		update_option(
			'roxyapi_settings',
			array(
				'api_key_encrypted' => Encryption::encrypt( $test_key ),
			)
		);
		$this->captured = array();
		add_filter( 'pre_http_request', array( $this, 'capture_http' ), 10, 3 );
		// Each test case starts with an empty cache so the request actually fires.
		\RoxyAPI\Api\Cache::flush_all();
	}

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'capture_http' ), 10 );
		parent::tearDown();
	}

	/**
	 * Capture the outgoing request, then short-circuit with a benign 200 and
	 * empty JSON body so the renderer does not try to render against null.
	 *
	 * @param mixed                $preempt Filter passthrough.
	 * @param array<string, mixed> $args    wp_remote_request args.
	 * @param string               $url     Target URL.
	 * @return array<string, mixed>
	 */
	public function capture_http( $preempt, $args, $url ) {
		$this->captured[] = array(
			'url'  => (string) $url,
			'args' => is_array( $args ) ? $args : array(),
		);
		return array(
			'headers'  => array(),
			'body'     => '{}',
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
			'filename' => null,
		);
	}

	/**
	 * Look up the canonical example for a given shortcode tag from
	 * Onboarding::hero_shortcodes(). Keeps the contract test in lockstep with
	 * what the docs advertise: changing the example code there changes the
	 * shape under test here automatically.
	 */
	private function hero_example( string $tag ): string {
		foreach ( Onboarding::hero_shortcodes() as $hero ) {
			$code = (string) ( $hero['code'] ?? '' );
			if ( strpos( $code, '[' . $tag ) === 0 ) {
				return $code;
			}
		}
		$this->fail( "No hero example registered for tag {$tag}." );
	}

	/**
	 * Wrap an assertion: the captured request at index $i must validate
	 * against $opId with no violations. On failure, dump the full violation
	 * list so the diagnostic is precise.
	 */
	private function assert_compliant( string $opId, int $captured_index = 0 ): void {
		$this->assertArrayHasKey(
			$captured_index,
			$this->captured,
			"Expected at least " . ( $captured_index + 1 ) . " captured request for {$opId}, got " . count( $this->captured )
		);
		$capture    = $this->captured[ $captured_index ];
		$violations = Spec_Contract_Validator::validate( $opId, $capture['url'], $capture['args'] );
		$this->assertSame(
			array(),
			$violations,
			"Spec contract violations for {$opId} at {$capture['url']}:\n  - " . implode( "\n  - ", $violations )
		);
	}

	// ---------------------------------------------------------------------
	// Hero contract tests (one per dispatch path).
	// ---------------------------------------------------------------------

	public function test_horoscope_matches_spec(): void {
		do_shortcode( $this->hero_example( 'roxy_horoscope' ) );
		$this->assert_compliant( 'getDailyHoroscope' );
	}

	public function test_natal_chart_matches_spec(): void {
		do_shortcode( $this->hero_example( 'roxy_natal_chart' ) );
		$this->assert_compliant( 'generateNatalChart' );
	}

	public function test_tarot_daily_matches_spec(): void {
		// Default spread (no attribute) dispatches to the daily endpoint.
		do_shortcode( '[roxy_tarot_card]' );
		$this->assert_compliant( 'getDailyCard' );
	}

	public function test_tarot_three_card_matches_spec(): void {
		// Canonical hero example uses spread=three.
		do_shortcode( $this->hero_example( 'roxy_tarot_card' ) );
		$this->assert_compliant( 'castThreeCard' );
	}

	public function test_tarot_celtic_cross_matches_spec(): void {
		do_shortcode( '[roxy_tarot_card spread="celtic" question="Where is my career heading"]' );
		$this->assert_compliant( 'castCelticCross' );
	}

	public function test_numerology_matches_spec(): void {
		do_shortcode( $this->hero_example( 'roxy_numerology' ) );
		$this->assert_compliant( 'generateNumerologyChart' );
	}

	public function test_life_path_matches_spec(): void {
		do_shortcode( $this->hero_example( 'roxy_life_path' ) );
		$this->assert_compliant( 'calculateLifePath' );
	}

	public function test_biorhythm_matches_spec(): void {
		do_shortcode( $this->hero_example( 'roxy_biorhythm' ) );
		$this->assert_compliant( 'getReading' );
	}

	public function test_angel_number_matches_spec(): void {
		do_shortcode( $this->hero_example( 'roxy_angel_number' ) );
		$this->assert_compliant( 'getAngelNumber' );
	}

	// ---------------------------------------------------------------------
	// v1.1 money-hero additions. Each new hero must round-trip its canonical
	// example through the same spec validator: catches any made-up field
	// name (the v1.1 spec mostly uses { date, time, latitude, longitude,
	// timezone } body shape but the test does not trust that — every
	// outgoing request is diffed against the live OpenAPI snapshot).
	// ---------------------------------------------------------------------

	public function test_kundli_matches_spec(): void {
		do_shortcode( $this->hero_example( 'roxy_kundli' ) );
		$this->assert_compliant( 'generateBirthChart' );
	}

	public function test_panchang_matches_spec(): void {
		do_shortcode( $this->hero_example( 'roxy_panchang' ) );
		$this->assert_compliant( 'getDetailedPanchang' );
	}

	public function test_mangal_dosha_matches_spec(): void {
		do_shortcode( $this->hero_example( 'roxy_mangal_dosha' ) );
		$this->assert_compliant( 'checkManglikDosha' );
	}

	public function test_kp_chart_matches_spec(): void {
		do_shortcode( $this->hero_example( 'roxy_kp_chart' ) );
		$this->assert_compliant( 'generateKpChart' );
	}

	public function test_moon_phase_matches_spec(): void {
		do_shortcode( $this->hero_example( 'roxy_moon_phase' ) );
		$this->assert_compliant( 'getCurrentMoonPhase' );
	}

	public function test_tarot_yes_no_matches_spec(): void {
		do_shortcode( $this->hero_example( 'roxy_tarot_yes_no' ) );
		$this->assert_compliant( 'castYesNo' );
	}

	public function test_crystals_by_zodiac_matches_spec(): void {
		do_shortcode( $this->hero_example( 'roxy_crystals_by_zodiac' ) );
		$this->assert_compliant( 'getCrystalsByZodiac' );
	}

	// ---------------------------------------------------------------------
	// Negative tests: prove the validator actually fails when given a
	// non-compliant request. Without these, a broken validator could rubber
	// stamp the entire suite.
	// ---------------------------------------------------------------------

	public function test_validator_rejects_unresolved_named_day_for_horoscope_date(): void {
		// `date=today` is the regression class we hit shipping v1.0: the SaaS
		// query param schema requires ^\d{4}-\d{2}-\d{2}$ and rejects literal
		// "today" with a 400. Sanitize::date_string is the production guard;
		// this test verifies the validator catches the bypass.
		$violations = Spec_Contract_Validator::validate(
			'getDailyHoroscope',
			'https://roxyapi.com/api/v2/astrology/horoscope/aries/daily?date=today',
			array( 'method' => 'GET' )
		);
		$this->assertNotEmpty( $violations, 'Validator must reject date=today as a horoscope query param.' );
		$this->assertTrue(
			$this->any_contains( $violations, 'date' ) && $this->any_contains( $violations, 'pattern' ),
			'Expected a pattern-mismatch violation for date param. Got: ' . implode( ' | ', $violations )
		);
	}

	public function test_validator_rejects_missing_required_timezone_for_natal_chart(): void {
		$body       = wp_json_encode(
			array(
				'date'      => '1990-05-15',
				'time'      => '14:30:00',
				'latitude'  => 40.7128,
				'longitude' => -74.006,
				// timezone deliberately omitted
			)
		);
		$violations = Spec_Contract_Validator::validate(
			'generateNatalChart',
			'https://roxyapi.com/api/v2/astrology/natal-chart',
			array(
				'method' => 'POST',
				'body'   => $body,
			)
		);
		$this->assertContains(
			'missing required body field: timezone',
			$violations,
			'Validator must flag a missing timezone. Got: ' . implode( ' | ', $violations )
		);
	}

	public function test_validator_rejects_out_of_range_latitude(): void {
		$body       = wp_json_encode(
			array(
				'date'      => '1990-05-15',
				'time'      => '14:30:00',
				'latitude'  => 91.0,
				'longitude' => -74.006,
				'timezone'  => 'America/New_York',
			)
		);
		$violations = Spec_Contract_Validator::validate(
			'generateNatalChart',
			'https://roxyapi.com/api/v2/astrology/natal-chart',
			array(
				'method' => 'POST',
				'body'   => $body,
			)
		);
		$this->assertTrue(
			$this->any_contains( $violations, 'latitude' ) && $this->any_contains( $violations, 'maximum' ),
			'Validator must flag latitude=91 as above maximum. Got: ' . implode( ' | ', $violations )
		);
	}

	public function test_validator_rejects_out_of_enum_zodiac_sign(): void {
		// Path param enum check: aries123 is not a valid zodiac sign.
		$violations = Spec_Contract_Validator::validate(
			'getDailyHoroscope',
			'https://roxyapi.com/api/v2/astrology/horoscope/aries123/daily?date=2026-01-01',
			array( 'method' => 'GET' )
		);
		$this->assertTrue(
			$this->any_contains( $violations, 'sign' ) && $this->any_contains( $violations, 'enum' ),
			'Validator must flag aries123 as not in the zodiac sign enum. Got: ' . implode( ' | ', $violations )
		);
	}

	public function test_validator_passes_a_known_good_request(): void {
		// Sanity check: a request that hand-matches the spec must validate
		// clean. If this ever turns red, the validator has gotten too strict.
		$body       = wp_json_encode(
			array(
				'date'      => '1990-05-15',
				'time'      => '14:30:00',
				'latitude'  => 40.7128,
				'longitude' => -74.006,
				'timezone'  => 'America/New_York',
			)
		);
		$violations = Spec_Contract_Validator::validate(
			'generateNatalChart',
			'https://roxyapi.com/api/v2/astrology/natal-chart',
			array(
				'method' => 'POST',
				'body'   => $body,
			)
		);
		$this->assertSame( array(), $violations, 'Hand-built compliant natal chart request should pass.' );
	}

	private function any_contains( array $haystack, string $needle ): bool {
		foreach ( $haystack as $line ) {
			if ( strpos( (string) $line, $needle ) !== false ) {
				return true;
			}
		}
		return false;
	}
}
