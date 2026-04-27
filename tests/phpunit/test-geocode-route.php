<?php
/**
 * Tests for the public geocode REST proxy.
 *
 * Validates the response-shape contract (only `{label, lat, lon, tz}` reaches
 * the visitor), the rate-limit gate, the cache hit path, and that nonsensical
 * upstream responses fall through cleanly to an empty list.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Api\GeocodeRoute;
use RoxyAPI\Support\Encryption;
use WP_REST_Request;

class Test_Geocode_Route extends \WP_UnitTestCase {

	private string $test_key = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee.0123456789abcdef.test_key_plaintext';

	public function setUp(): void {
		parent::setUp();
		update_option( 'roxyapi_settings', array( 'api_key_encrypted' => Encryption::encrypt( $this->test_key ) ) );
		\RoxyAPI\Api\Cache::flush_all();
		// Reset the rate-limit bucket between tests so each starts fresh.
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_roxyapi_rl_%' OR option_name LIKE '_transient_timeout_roxyapi_rl_%'" );
		do_action( 'rest_api_init' );
	}

	public function tearDown(): void {
		remove_all_filters( 'pre_http_request' );
		delete_option( 'roxyapi_settings' );
		\RoxyAPI\Api\Cache::flush_all();
		parent::tearDown();
	}

	private function mock_upstream( array $cities ): void {
		add_filter(
			'pre_http_request',
			static function () use ( $cities ) {
				return array(
					'headers'  => array(),
					'body'     => wp_json_encode( array( 'cities' => $cities, 'total' => count( $cities ) ) ),
					'response' => array( 'code' => 200, 'message' => 'OK' ),
					'cookies'  => array(),
					'filename' => null,
				);
			},
			10,
			3
		);
	}

	private function dispatch( string $q ) {
		$request = new WP_REST_Request( 'GET', '/' . GeocodeRoute::NAMESPACE . GeocodeRoute::ROUTE );
		$request->set_query_params( array( 'q' => $q ) );
		return rest_do_request( $request );
	}

	public function test_returns_stripped_city_objects(): void {
		$this->mock_upstream(
			array(
				array(
					'city'       => 'Mumbai',
					'province'   => 'Maharashtra',
					'country'    => 'India',
					'iso2'       => 'IN',
					'latitude'   => 19.076,
					'longitude'  => 72.8777,
					'timezone'   => 'Asia/Kolkata',
					'utcOffset'  => 5.5,
					'population' => 12442373,
				),
			)
		);

		$response = $this->dispatch( 'Mumbai' );
		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'cities', $data );
		$this->assertCount( 1, $data['cities'] );

		$city = $data['cities'][0];
		$this->assertSame( 'Mumbai, Maharashtra, India', $city['label'] );
		$this->assertSame( 19.076, $city['lat'] );
		$this->assertSame( 72.8777, $city['lon'] );
		$this->assertSame( 'Asia/Kolkata', $city['tz'] );

		// Implementation detail must NOT leak.
		$this->assertArrayNotHasKey( 'population', $city );
		$this->assertArrayNotHasKey( 'iso2', $city );
		$this->assertArrayNotHasKey( 'utcOffset', $city );
	}

	public function test_drops_cities_missing_required_fields(): void {
		$this->mock_upstream(
			array(
				array(
					'city'      => 'Nowhere',
					'latitude'  => 10.0,
					'longitude' => 20.0,
					// timezone missing
				),
				array(
					'city'      => 'Somewhere',
					'latitude'  => 11.1,
					'longitude' => 22.2,
					'timezone'  => 'Asia/Tokyo',
					'country'   => 'Japan',
				),
			)
		);
		$data = $this->dispatch( 'where' )->get_data();
		$this->assertCount( 1, $data['cities'] );
		$this->assertSame( 'Somewhere, Japan', $data['cities'][0]['label'] );
	}

	public function test_short_query_returns_400(): void {
		$response = $this->dispatch( 'a' );
		$this->assertSame( 400, $response->get_status() );
		$data = $response->get_data();
		$this->assertSame( array(), $data['cities'] );
	}

	public function test_rate_limit_returns_429(): void {
		$this->mock_upstream( array() );
		// Exhaust the bucket. Default is 20/hour.
		for ( $i = 0; $i < 25; $i++ ) {
			$this->dispatch( 'city' . $i );
		}
		$response = $this->dispatch( 'over-limit' );
		$this->assertSame( 429, $response->get_status() );
		$data = $response->get_data();
		$this->assertSame( 'roxyapi_geocode_rate_limit', $data['error']['code'] );
	}

	public function test_upstream_failure_returns_502(): void {
		add_filter(
			'pre_http_request',
			static function () {
				return new \WP_Error( 'simulated', 'Upstream down' );
			}
		);
		$response = $this->dispatch( 'mumbai' );
		$this->assertSame( 502, $response->get_status() );
		$data = $response->get_data();
		$this->assertSame( array(), $data['cities'] );
	}

	public function test_repeat_query_hits_cache_not_upstream(): void {
		$call_count = 0;
		add_filter(
			'pre_http_request',
			static function () use ( &$call_count ) {
				$call_count++;
				return array(
					'headers'  => array(),
					'body'     => wp_json_encode(
						array(
							'cities' => array(
								array(
									'city'      => 'Mumbai',
									'latitude'  => 19.076,
									'longitude' => 72.8777,
									'timezone'  => 'Asia/Kolkata',
									'country'   => 'India',
								),
							),
						)
					),
					'response' => array( 'code' => 200, 'message' => 'OK' ),
					'cookies'  => array(),
					'filename' => null,
				);
			},
			10,
			3
		);

		$this->dispatch( 'Mumbai' );
		$this->dispatch( 'Mumbai' );
		$this->dispatch( 'Mumbai' );
		$this->assertSame( 1, $call_count, 'Cache should have absorbed identical repeat queries' );
	}

	public function test_case_normalised_cache_key(): void {
		$call_count = 0;
		add_filter(
			'pre_http_request',
			static function () use ( &$call_count ) {
				$call_count++;
				return array(
					'headers'  => array(),
					'body'     => wp_json_encode( array( 'cities' => array() ) ),
					'response' => array( 'code' => 200, 'message' => 'OK' ),
					'cookies'  => array(),
					'filename' => null,
				);
			},
			10,
			3
		);

		// Same query, different casing — must hit cache, not upstream twice.
		$this->dispatch( 'Mumbai' );
		$this->dispatch( 'mumbai' );
		$this->dispatch( 'MUMBAI' );
		$this->assertSame( 1, $call_count, 'Lowercased cache key should collapse case variants' );
	}
}
