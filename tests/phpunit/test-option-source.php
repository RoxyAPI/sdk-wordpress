<?php
/**
 * A visitor picks a listed identifier (a planet, a sign, a card) from a
 * dropdown filled server-side from the collection the API lists it in.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Generated\Shortcodes\GetCrystal;
use RoxyAPI\Generated\Shortcodes\GetPlanetMeaning;
use RoxyAPI\Generated\Shortcodes\GetTrigram;
use RoxyAPI\Generated\Shortcodes\GetZodiacSign;
use RoxyAPI\Support\FormRouter;

class Test_Option_Source extends \WP_UnitTestCase {

	/** Every outgoing request URL, in order. */
	private array $requested = array();

	/** Response body by URL path below `/api/v2/`; an unlisted path answers 404. */
	private array $routes = array();

	public function setUp(): void {
		parent::setUp();
		\RoxyAPI\Api\Cache::flush_all();
		$this->requested = array();
		$this->routes    = array(
			'astrology/signs'  => array(
				array(
					'id'   => 'aries',
					'name' => 'Aries',
				),
				array(
					'id'   => 'scorpio',
					'name' => 'Scorpio',
				),
			),
			'iching/trigrams'  => array(
				'total'    => 2,
				'trigrams' => array(
					array(
						'number'  => 1,
						'english' => 'Heaven',
					),
					array(
						'number'  => 6,
						'english' => 'Lake',
					),
				),
			),
			'astrology/signs/scorpio' => array( 'id' => 'scorpio' ),
			'iching/trigrams/6'       => array( 'number' => 6 ),
		);
		add_filter( 'pre_http_request', array( $this, 'respond' ), 10, 3 );
		add_filter(
			'roxyapi_form_router_skip_redirect',
			static fn( $skip, $token, $url ) => $url,
			10,
			3
		);
	}

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'respond' ), 10 );
		remove_all_filters( 'roxyapi_form_router_skip_redirect' );
		\RoxyAPI\Api\Cache::flush_all();
		wp_set_current_user( 0 );
		parent::tearDown();
	}

	public function respond( $pre, $args, $url ) {
		$this->requested[] = $url;
		$path              = trim( substr( (string) wp_parse_url( $url, PHP_URL_PATH ), strlen( '/api/v2/' ) ), '/' );
		if ( $path === 'crystals' ) {
			parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
			$offset = (int) ( $query['offset'] ?? 0 );
			$body   = array(
				'total'    => 3,
				'crystals' => array_slice(
					array(
						array(
							'id'   => 'amber',
							'name' => 'Amber',
						),
						array(
							'id'   => 'amethyst',
							'name' => 'Amethyst',
						),
						array(
							'id'   => 'onyx',
							'name' => 'Onyx',
						),
					),
					$offset,
					2
				),
			);
		} else {
			$body = $this->routes[ $path ] ?? null;
		}
		return array(
			'headers'  => array(),
			'body'     => wp_json_encode( $body ?? array( 'error' => 'Not found' ) ),
			'response' => array(
				'code'    => $body === null ? 404 : 200,
				'message' => $body === null ? 'Not Found' : 'OK',
			),
			'cookies'  => array(),
			'filename' => null,
		);
	}

	public function test_a_bare_lookup_offers_its_collection_as_a_dropdown(): void {
		$html = GetZodiacSign::render( array(), '', GetZodiacSign::TAG );

		$this->assertMatchesRegularExpression( '#<select[^>]*name="id"[^>]*required#', $html );
		$this->assertStringContainsString( '<option value="scorpio">Scorpio</option>', $html );
		$this->assertCount( 1, $this->requested, 'A waiting form reads its collection and no reading.' );
		$this->assertStringContainsString( '/astrology/signs?', $this->requested[0] );
	}

	public function test_the_collection_is_read_once_and_cached(): void {
		GetZodiacSign::render( array(), '', GetZodiacSign::TAG );
		GetZodiacSign::render( array(), '', GetZodiacSign::TAG );

		$this->assertCount( 1, $this->requested );
	}

	/** A record without an `id` is valued by its number and labelled by its English name. */
	public function test_a_numbered_record_posts_its_number(): void {
		$html = GetTrigram::render( array(), '', GetTrigram::TAG );

		$this->assertStringContainsString( '<option value="6">Lake</option>', $html );
		$this->submit( 'getTrigram', '6' );
		$this->assertStringContainsString( '/iching/trigrams/6', end( $this->requested ) );
	}

	/** A paged collection is read up to the total it reports. */
	public function test_a_paged_collection_is_read_to_its_total(): void {
		$html = GetCrystal::render( array(), '', GetCrystal::TAG );

		$this->assertStringContainsString( '<option value="onyx">Onyx</option>', $html );
		$this->assertCount( 2, $this->requested );
		$this->assertStringContainsString( 'offset=2', $this->requested[1] );
	}

	public function test_a_picked_entry_fills_the_path(): void {
		$this->submit( 'getZodiacSign', 'scorpio' );

		$this->assertStringContainsString( '/astrology/signs/scorpio', end( $this->requested ) );
	}

	public function test_a_value_outside_the_collection_requests_no_reading(): void {
		$this->submit( 'getZodiacSign', '../../account' );

		foreach ( $this->requested as $url ) {
			$this->assertStringNotContainsString( 'account', $url );
		}
	}

	/** An unreadable collection leaves a text input, never an empty dropdown. */
	public function test_an_unreadable_collection_falls_back_to_text(): void {
		$html = GetPlanetMeaning::render( array(), '', GetPlanetMeaning::TAG );

		$this->assertMatchesRegularExpression( '#<input type="text"[^>]*name="id"#', $html );
		$this->assertStringNotContainsString( '<select id="roxyapi-field-id"', $html );
	}

	/** An empty identifier is the owner-only setup state: nothing requested, nothing shown to visitors. */
	public function test_an_empty_identifier_in_static_mode_shows_visitors_nothing(): void {
		$atts = array( 'mode' => 'static' );

		$this->assertSame( '', GetPlanetMeaning::render( $atts, '', GetPlanetMeaning::TAG ) );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$this->assertStringContainsString( 'not been set up', GetPlanetMeaning::render( $atts, '', GetPlanetMeaning::TAG ) );
		$this->assertSame( array(), $this->requested );
	}

	/**
	 * Post a consented visitor form through the router.
	 *
	 * @param string $form_id Form id.
	 * @param string $id      Posted identifier.
	 */
	private function submit( string $form_id, string $id ): void {
		$_POST = array(
			'id'              => $id,
			'roxyapi_form'    => $form_id,
			'_wpnonce'        => wp_create_nonce( 'roxyapi_form_' . $form_id ),
			'roxyapi_consent' => '1',
		);
		FormRouter::maybe_handle();
		$_POST = array();
	}
}
