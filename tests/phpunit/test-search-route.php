<?php
/**
 * A visitor finds an entry of a collection too large to list (the dream
 * dictionary) by typing a word into a search box, which the public search
 * route answers from the one collection the form names for that field.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Api\GeocodeRoute;
use RoxyAPI\Api\SearchRoute;
use RoxyAPI\Generated\Shortcodes\GetDreamSymbol;
use RoxyAPI\Generated\Shortcodes\SearchDreamSymbols;
use RoxyAPI\Support\FormRouter;
use WP_REST_Request;

class Test_Search_Route extends \WP_UnitTestCase {

	/** Every outgoing request URL, in order. */
	private array $requested = array();

	/** Status the mocked API answers with. */
	private int $status = 200;

	public function setUp(): void {
		parent::setUp();
		\RoxyAPI\Api\Cache::flush_all();
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_roxyapi_rl_%' OR option_name LIKE '_transient_timeout_roxyapi_rl_%'" );
		$this->requested = array();
		$this->status    = 200;
		add_filter( 'pre_http_request', array( $this, 'respond' ), 10, 3 );
		add_filter( 'roxyapi_form_router_skip_redirect', static fn( $skip, $token, $url ) => $url, 10, 3 );
		do_action( 'rest_api_init' );
	}

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'respond' ), 10 );
		remove_all_filters( 'roxyapi_form_router_skip_redirect' );
		\RoxyAPI\Api\Cache::flush_all();
		parent::tearDown();
	}

	public function respond( $pre, $args, $url ) {
		$this->requested[] = $url;
		$path              = trim( substr( (string) wp_parse_url( $url, PHP_URL_PATH ), strlen( '/api/v2/' ) ), '/' );
		$body              = $path === 'dreams/symbols'
			? array(
				'total'   => 1,
				'limit'   => 50,
				'offset'  => 0,
				'symbols' => array(
					array(
						'id'     => 'feral-cats',
						'name'   => 'Feral Cats',
						'letter' => 'f',
					),
				),
			)
			: array(
				'id'      => basename( $path ),
				'name'    => 'Feral Cats',
				'meaning' => 'A wild independence.',
			);
		return array(
			'headers'  => array(),
			'body'     => wp_json_encode( $this->status === 200 ? $body : array( 'error' => 'Unavailable' ) ),
			'response' => array(
				'code'    => $this->status,
				'message' => $this->status === 200 ? 'OK' : 'Error',
			),
			'cookies'  => array(),
			'filename' => null,
		);
	}

	private function search( array $query ) {
		$request = new WP_REST_Request( 'GET', '/' . GeocodeRoute::REST_NAMESPACE . SearchRoute::ROUTE );
		$request->set_query_params( $query );
		return rest_do_request( $request );
	}

	private function dream( string $q ) {
		return $this->search(
			array(
				'form'  => 'getDreamSymbol',
				'field' => 'id',
				'q'     => $q,
			)
		);
	}

	public function test_a_spaced_name_resolves_to_the_identifier_it_posts(): void {
		$response = $this->dream( 'Feral cats' );

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame(
			array(
				array(
					'label' => 'Feral Cats',
					'value' => 'feral-cats',
				),
			),
			$response->get_data()['results']
		);
		$this->assertCount( 1, $this->requested );
		$this->assertStringContainsString( '/dreams/symbols?', $this->requested[0] );
		$this->assertStringContainsString( 'q=feral%20cats', $this->requested[0] );
	}

	public function test_a_word_is_read_once_whatever_its_case(): void {
		$this->dream( 'Snake' );
		$this->dream( 'snake' );

		$this->assertCount( 1, $this->requested );
	}

	/** The route reads only a collection a form searches, never a path the caller names. */
	public function test_only_a_searched_field_of_a_generated_form_is_answered(): void {
		$refused = array(
			array(
				'form'  => 'getZodiacSign',
				'field' => 'id',
			),
			array(
				'form'  => 'getDreamSymbol',
				'field' => 'name',
			),
			array(
				'form'  => 'getUsageStats',
				'field' => 'id',
			),
			array(
				'form'  => 'nothingHere',
				'field' => 'id',
			),
		);
		foreach ( $refused as $query ) {
			$response = $this->search( $query + array( 'q' => 'snake' ) );
			$this->assertSame( 404, $response->get_status(), (string) wp_json_encode( $query ) );
			$this->assertSame( array(), $response->get_data()['results'] );
		}
		$this->assertSame( 400, $this->search( array( 'form' => '../account', 'field' => 'id', 'q' => 'snake' ) )->get_status() );
		$this->assertSame( array(), $this->requested, 'A refused search reads nothing, not even the form\'s own dropdowns.' );
	}

	public function test_a_word_too_short_or_too_long_requests_nothing(): void {
		$this->assertSame( 400, $this->dream( 's' )->get_status() );
		$this->assertSame( 400, $this->dream( str_repeat( 'a', 101 ) )->get_status() );
		$this->assertSame( array(), $this->requested );
	}

	public function test_the_search_is_rate_limited_with_a_reason(): void {
		add_filter( 'pre_option_roxyapi_settings', static fn() => array( 'rate_limit_per_hour' => 1 ) );
		$this->dream( 'snake' );
		$response = $this->dream( 'water' );
		remove_all_filters( 'pre_option_roxyapi_settings' );

		$this->assertSame( 429, $response->get_status() );
		$this->assertSame( 'roxyapi_search_rate_limit', $response->get_data()['error']['code'] );
		$this->assertCount( 1, $this->requested );
	}

	public function test_an_unavailable_collection_says_so(): void {
		$this->status = 503;
		$response     = $this->dream( 'snake' );

		$this->assertSame( 502, $response->get_status() );
		$this->assertSame( array(), $response->get_data()['results'] );
		$this->assertNotSame( '', $response->get_data()['error']['message'] );
	}

	public function test_a_bare_lookup_is_a_search_box_that_still_posts_without_javascript(): void {
		\RoxyAPI\Plugin::register_geocode_script();
		$html = GetDreamSymbol::render( array(), '', GetDreamSymbol::TAG );

		$this->assertMatchesRegularExpression( '#<input type="text"[^>]*name="id"[^>]*data-roxyapi-geocode="\#roxyapi-form-getDreamSymbol"#', $html );
		$this->assertStringContainsString( esc_attr( (string) wp_json_encode( array( 'value' => 'id' ) ) ), $html );
		$this->assertStringContainsString( esc_url( SearchRoute::url( 'getDreamSymbol', 'id' ) ), $html );
		$this->assertTrue( wp_script_is( 'roxyapi-geocode', 'enqueued' ) );
		$this->assertSame( array(), $this->requested, 'A waiting search box reads nothing.' );

		$this->submit( 'feral-cats' );
		$this->assertStringContainsString( '/dreams/symbols/feral-cats', end( $this->requested ) );
	}

	public function test_a_bare_dictionary_offers_the_lookup_and_a_narrowed_one_stays_a_reading(): void {
		$this->assertStringContainsString( 'name="roxyapi_form" value="getDreamSymbol"', SearchDreamSymbols::render( array(), '', SearchDreamSymbols::TAG ) );
		$this->assertSame( array(), $this->requested );

		$html = SearchDreamSymbols::render( array( 'q' => 'cats' ), '', SearchDreamSymbols::TAG );
		$this->assertStringNotContainsString( 'class="roxyapi-form"', $html );
		$this->assertStringContainsString( 'q=cats', end( $this->requested ) );
	}

	/**
	 * Post a consented dream symbol form through the router.
	 *
	 * @param string $id Posted identifier.
	 */
	private function submit( string $id ): void {
		$_POST = array(
			'id'              => $id,
			'roxyapi_form'    => 'getDreamSymbol',
			'_wpnonce'        => wp_create_nonce( 'roxyapi_form_getDreamSymbol' ),
			'roxyapi_consent' => '1',
		);
		FormRouter::maybe_handle();
		$_POST = array();
	}
}
