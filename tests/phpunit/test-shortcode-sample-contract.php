<?php
/**
 * A copy-paste sample may only offer attributes the shortcode actually reads.
 *
 * The library and the demo screen build their samples from the `attributes`
 * map in the generated endpoint registry, and `shortcode_atts()` silently
 * discards any attribute it was not given a default for. The two used to
 * disagree on 97 shortcodes and 140 attributes, `lang` worst of all: the
 * generator's POST branch built its attribute list from path params and body
 * fields and dropped the QUERY parameters that the GET branch had always kept,
 * so `[roxy_x lang="es"]` was pasted from the admin, produced no error, and did
 * nothing. Nothing in the UI could show it, because a dropped attribute looks
 * exactly like an attribute that was never passed.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Generated\Endpoints;

class Test_Shortcode_Sample_Contract extends Mock_Http_TestCase {

	/** @var array<int, array{url: string, args: array<string, mixed>}> Requests the shortcodes made. */
	private array $requests = array();

	public function setUp(): void {
		parent::setUp();
		\RoxyAPI\Api\Cache::flush_all();
		$this->requests = array();
		add_filter( 'pre_http_request', array( $this, 'record_request' ), 9, 3 );
	}

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'record_request' ), 9 );
		\RoxyAPI\Api\Cache::flush_all();
		parent::tearDown();
	}

	/**
	 * Record the request and let the mock in the base class answer it.
	 *
	 * @param mixed                $preempt Short-circuit value.
	 * @param array<string, mixed> $args    Request args.
	 * @param string               $url     Request URL.
	 * @return mixed
	 */
	public function record_request( $preempt, $args, $url ) {
		$this->requests[] = array( 'url' => (string) $url, 'args' => $args );
		return $preempt;
	}

	/**
	 * PHP class name for an operationId, matching the generator's own rule.
	 *
	 * @param string $operation_id Operation id.
	 * @return string
	 */
	private function shortcode_class( string $operation_id ): string {
		return '\\RoxyAPI\\Generated\\Shortcodes\\' . ucfirst( $operation_id );
	}

	/**
	 * Every sample attribute of every generated shortcode is one that shortcode
	 * accepts. Hero rows are excluded because their sample is the curated
	 * `example` string in bin/hero-config.json, not this map.
	 */
	public function test_no_sample_offers_an_attribute_the_shortcode_discards(): void {
		$offenders = array();
		$checked   = 0;

		foreach ( Endpoints::all() as $operation_id => $endpoint ) {
			if ( ! empty( $endpoint['hero'] ) ) {
				continue;
			}
			$class = $this->shortcode_class( (string) $operation_id );
			if ( ! class_exists( $class ) ) {
				continue;
			}
			$checked++;
			$dropped = array_diff(
				array_keys( (array) $endpoint['attributes'] ),
				array_keys( $class::DEFAULTS )
			);
			if ( $dropped ) {
				$offenders[] = $operation_id . ': ' . implode( ', ', $dropped );
			}
		}

		// Non-vacuity: an empty registry would make the loop assert nothing.
		$this->assertGreaterThan( 100, $checked );
		$this->assertSame(
			array(),
			$offenders,
			'These samples paste attributes WordPress throws away.'
		);
	}

	/**
	 * A form-only shortcode reads no attributes at all, so its sample is the
	 * bare tag. It used to advertise a language it could not accept either.
	 */
	public function test_a_form_only_reading_offers_a_bare_sample(): void {
		$synastry = Endpoints::get( 'calculateSynastry' );
		$this->assertNotNull( $synastry );
		$this->assertTrue( (bool) $synastry['block_only'] );
		$this->assertSame( array(), $synastry['attributes'] );
	}

	/**
	 * A frozen date in a sample is wrong the moment the month turns, and the two
	 * ephemeris cards proved it by shipping different months as though the
	 * choice meant something. Both parameters document their own default, so the
	 * correct sample omits them and the reading answers for the current month
	 * wherever it is pasted.
	 */
	public function test_a_sample_never_freezes_a_field_that_defaults_to_now(): void {
		foreach ( array( 'getMonthlyEphemeris', 'getMonthlyTropicalEphemeris' ) as $operation_id ) {
			$attributes = (array) Endpoints::get( $operation_id )['attributes'];
			$this->assertArrayNotHasKey( 'year', $attributes, $operation_id );
			$this->assertArrayNotHasKey( 'month', $attributes, $operation_id );
		}

		// A REQUIRED field keeps its example: the sample has to carry something.
		$this->assertArrayHasKey(
			'year',
			(array) Endpoints::get( 'getEclipticCrossings' )['attributes']
		);
	}

	/**
	 * The end of the wire, not just the declaration: a POST shortcode's `lang`
	 * has to arrive on the URL, which is the only place the API reads it.
	 */
	public function test_lang_on_a_post_shortcode_reaches_the_query_string(): void {
		$this->mock_responses['vedic-astrology/arudha'] = array( 'padas' => array() );

		do_shortcode( '[roxy_calculate_arudha_padas date="1990-05-15" time="14:30" latitude="28.61" longitude="77.20" timezone="5.5" lang="es"]' );

		$this->assertNotEmpty( $this->requests, 'The shortcode should have called the API.' );
		$last = end( $this->requests );
		$this->assertStringContainsString( 'lang=es', (string) $last['url'] );
		$body = json_decode( (string) ( $last['args']['body'] ?? '{}' ), true );
		$this->assertArrayNotHasKey( 'lang', (array) $body, 'The API ignores a language in the body.' );
	}

	/**
	 * `focus`, `include`, `orb` and `strictOrbs` are query parameters on POST
	 * operations too, and were unreachable from WordPress for the same reason.
	 */
	public function test_a_non_language_query_parameter_reaches_the_query_string(): void {
		$this->mock_responses['vedic-astrology/kp/cusps'] = array( 'cusps' => array() );

		do_shortcode( '[roxy_get_kp_cusps date="1990-05-15" time="14:30" latitude="28.61" longitude="77.20" timezone="5.5" focus="finance"]' );

		$this->assertNotEmpty( $this->requests );
		$last = end( $this->requests );
		$this->assertStringContainsString( 'focus=finance', (string) $last['url'] );
		$body = json_decode( (string) ( $last['args']['body'] ?? '{}' ), true );
		$this->assertArrayNotHasKey( 'focus', (array) $body );
	}

	/**
	 * Two values of a query parameter are two readings. Sharing a cache key
	 * would serve the first answer for the second question.
	 */
	public function test_a_query_parameter_changes_the_cache_key(): void {
		$this->mock_responses['vedic-astrology/kp/cusps'] = array( 'cusps' => array() );

		$shared = 'date="1990-05-15" time="14:30" latitude="28.61" longitude="77.20" timezone="5.5"';
		do_shortcode( "[roxy_get_kp_cusps {$shared} focus=\"general\"]" );
		$after_first = count( $this->requests );
		do_shortcode( "[roxy_get_kp_cusps {$shared} focus=\"finance\"]" );

		$this->assertGreaterThan(
			$after_first,
			count( $this->requests ),
			'A different query parameter must miss the cache, not reuse the previous reading.'
		);
	}
}
