<?php
/**
 * Branded reports: the report operations stay out of the long tail, the
 * `[roxy_report]` form posts to its REST route, the Reports tab settings reach
 * the request, and the route answers with the document or a redirect back to
 * the form, storing nothing a visitor typed.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\SettingsFields;
use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Admin\SettingsSchema;
use RoxyAPI\Api\Cache;
use RoxyAPI\Api\ReportRoute;
use RoxyAPI\Generated\Endpoints;
use RoxyAPI\Generated\Palettes;
use RoxyAPI\Generated\Reports;
use RoxyAPI\Support\Theming;
use WP_REST_Request;
use WP_REST_Response;

class Test_Reports extends \WP_UnitTestCase {

	/** A natal report post, as the visitor form sends it. */
	private const POST = array(
		'date'      => '1990-07-15',
		'time'      => '14:30',
		'latitude'  => '40.7128',
		'longitude' => '-74.006',
		'timezone'  => 'America/New_York',
	);

	/** @var array<int, array{url: string, body: array<string, mixed>}> Requests the API received. */
	private array $sent = array();

	/** @var array{0: int, 1: array<string, mixed>} Status and JSON body the API answers with. */
	private array $answer = array( 200, array() );

	public function setUp(): void {
		parent::setUp();
		delete_option( SettingsPage::OPTION_NAME );
		Cache::flush_all();
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_roxyapi_rl_%' OR option_name LIKE '_transient_timeout_roxyapi_rl_%'" );
		add_filter( 'pre_http_request', array( $this, 'api' ), 10, 3 );
		do_action( 'rest_api_init' );
		$_SERVER['HTTP_REFERER'] = home_url( '/reading/' );
	}

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'api' ), 10 );
		delete_option( SettingsPage::OPTION_NAME );
		Cache::flush_all();
		unset( $_SERVER['HTTP_REFERER'] );
		parent::tearDown();
	}

	/**
	 * Record a request to the API and answer it.
	 *
	 * @param mixed  $preempt Earlier short-circuit.
	 * @param array  $args    Request arguments.
	 * @param string $url     Request URL.
	 * @return array<string, mixed>
	 */
	public function api( $preempt, $args, $url ): array {
		$this->sent[] = array(
			'url'  => $url,
			'body' => (array) json_decode( (string) ( $args['body'] ?? '' ), true ),
		);
		return array(
			'headers'  => array(),
			'body'     => wp_json_encode( $this->answer[1] ),
			'response' => array(
				'code'    => $this->answer[0],
				'message' => '',
			),
			'cookies'  => array(),
			'filename' => null,
		);
	}

	private function pdf_answer(): array {
		return array(
			200,
			array(
				'template' => 'natal',
				'lang'     => 'en',
				'format'   => 'pdf',
				'title'    => 'Natal chart report',
				'pages'    => 2,
				'pdf'      => base64_encode( '%PDF-1.7 report' ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- The API's own encoding of a PDF.
			),
		);
	}

	/**
	 * The query a placement's form posts with, read from its rendered action.
	 *
	 * @param string $shortcode The placement.
	 * @param int    $post_id   The post showing it, or 0 outside any post.
	 * @return array<string, string>
	 */
	private function action_query( string $shortcode, int $post_id = 0 ): array {
		$GLOBALS['post'] = $post_id ? get_post( $post_id ) : null;
		$html            = do_shortcode( $shortcode );
		$GLOBALS['post'] = null;
		return preg_match( '/action="([^"]+)"/', $html, $action ) === 1
			? (array) wp_parse_args( (string) wp_parse_url( html_entity_decode( $action[1] ), PHP_URL_QUERY ) )
			: array();
	}

	/**
	 * Post to a report route.
	 *
	 * @param array<string, mixed>  $params Body.
	 * @param string                $report Report id in the route.
	 * @param array<string, string> $query  Query; by default the one the same report's form posts with.
	 */
	private function post( array $params, string $report = 'natal', ?array $query = null ): WP_REST_Response {
		$request = new WP_REST_Request( 'POST', '/roxyapi/v1/report/' . $report );
		$request->set_query_params( $query ?? $this->action_query( '[roxy_report report="' . $report . '"]' ) );
		$request->set_body_params( $params );
		return rest_do_request( $request );
	}

	private function visitor_post( array $extra = array() ): array {
		return array_merge(
			self::POST,
			array(
				'_wpnonce'        => wp_create_nonce( 'wp_rest' ),
				'roxyapi_consent' => '1',
			),
			$extra
		);
	}

	/** Every transient a visitor result is stored under. */
	private function stored_results(): array {
		global $wpdb;
		return $wpdb->get_col( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( '_transient_roxyapi_r_' ) . '%' ) );
	}

	public function test_every_report_operation_stays_out_of_the_long_tail(): void {
		$spec = json_decode( (string) file_get_contents( dirname( __DIR__, 2 ) . '/specs/openapi.json' ), true );
		$ids  = array();
		foreach ( $spec['paths'] as $operations ) {
			foreach ( $operations as $operation ) {
				if ( in_array( 'Reports', $operation['tags'] ?? array(), true ) || ( $operation['x-request-cost'] ?? 1 ) > 1 ) {
					$ids[] = $operation['operationId'];
				}
			}
		}
		$this->assertNotEmpty( $ids, 'The committed spec must publish the report operations.' );
		$this->assertSame( $ids, array_column( Reports::templates(), 'operation_id' ), 'Every report operation, and nothing else, is a [roxy_report] template.' );

		foreach ( $ids as $id ) {
			$endpoint = Endpoints::get( $id );
			$this->assertNotNull( $endpoint );
			$this->assertTrue( $endpoint['hero'], "{$id} is owned by [roxy_report]." );
			$this->assertSame( 0, $endpoint['ttl'], "{$id} is never cached." );
			$this->assertFalse( shortcode_exists( $endpoint['shortcode_tag'] ), "{$id} must not register a long-tail shortcode." );
			$this->assertFileDoesNotExist( dirname( __DIR__, 2 ) . '/src/Generated/Shortcodes/' . ucfirst( $id ) . '.php' );
			$this->assertDirectoryDoesNotExist( dirname( __DIR__, 2 ) . '/blocks/generated/' . strtolower( (string) preg_replace( '/([a-z])([A-Z])/', '$1-$2', $id ) ) );
		}
	}

	public function test_the_form_posts_to_the_report_route_with_the_rest_nonce(): void {
		$html = do_shortcode( '[roxy_report report="kundli" format="html"]' );
		$this->assertStringContainsString( 'action="' . esc_url( add_query_arg( 'format', 'html', rest_url( 'roxyapi/v1/report/kundli' ) ) ) . '&#038;placement=0.', $html );
		$this->assertStringContainsString( 'name="_wpnonce" value="' . wp_create_nonce( 'wp_rest' ) . '"', $html );
		$this->assertStringNotContainsString( 'name="roxyapi_form"', $html, 'The page router must never see a report post.' );
		$this->assertStringContainsString( 'name="roxyapi_consent"', $html );
		$this->assertSame( array(), $this->sent, 'Rendering the form calls nothing.' );
	}

	public function test_a_bare_placement_offers_the_default_report_as_a_pdf(): void {
		$html = do_shortcode( '[roxy_report format="markdown"]' );
		$this->assertStringContainsString( esc_url( add_query_arg( 'format', 'pdf', rest_url( 'roxyapi/v1/report/natal' ) ) ), $html );
	}

	public function test_an_unknown_report_is_an_owner_only_notice(): void {
		$this->assertSame( '', do_shortcode( '[roxy_report report="nope"]' ) );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$this->assertStringContainsString( 'roxyapi-error-setup', do_shortcode( '[roxy_report report="nope"]' ) );
	}

	public function test_empty_settings_brand_the_report_with_the_site_title_and_address(): void {
		$this->assertSame(
			array(
				'format'   => 'html',
				'branding' => array(
					'name'    => get_bloginfo( 'name' ),
					'website' => home_url( '/' ),
				),
			),
			ReportRoute::request( 'html' )
		);
	}

	public function test_the_site_title_is_decoded_and_held_to_the_length_a_report_prints(): void {
		update_option( 'blogname', 'Luna &amp; Sol ' . str_repeat( 'x', 100 ) );
		$name = ReportRoute::site_name();
		$this->assertStringStartsWith( 'Luna & Sol ', $name );
		$this->assertSame( Reports::NAME_MAX_LENGTH, mb_strlen( $name ) );
		$this->assertStringContainsString( 'placeholder="' . esc_attr( $name ) . '"', SettingsFields::report_text_html( 'report_name' ) );
		$this->assertStringContainsString( 'placeholder=""', SettingsFields::report_text_html( 'report_footer' ) );
	}

	public function test_an_empty_logo_takes_the_site_logo_and_a_chosen_one_wins(): void {
		$scheme = static fn( string $url ): string => set_url_scheme( $url, 'https' );
		add_filter( 'wp_get_attachment_url', $scheme );
		$site  = self::factory()->attachment->create_object( 'site-logo.png', 0, array( 'post_mime_type' => 'image/png' ) );
		$brand = self::factory()->attachment->create_object( 'brand-logo.png', 0, array( 'post_mime_type' => 'image/png' ) );
		set_theme_mod( 'custom_logo', $site );
		$this->assertStringEndsWith( 'site-logo.png', (string) ReportRoute::request( 'pdf' )['branding']['logo'] );
		update_option( SettingsPage::OPTION_NAME, array( 'report_logo' => $brand ) );
		$this->assertStringEndsWith( 'brand-logo.png', (string) ReportRoute::request( 'pdf' )['branding']['logo'] );
		remove_theme_mod( 'custom_logo' );
		remove_filter( 'wp_get_attachment_url', $scheme );
	}

	public function test_every_reports_tab_setting_and_the_branding_tab_look_reach_the_request(): void {
		update_option(
			SettingsPage::OPTION_NAME,
			array(
				'report_name'      => 'Luna Rivera Astrology',
				'report_footer'    => 'Book a reading at example.com.',
				'report_page_size' => 'Letter',
				'report_language'  => 'hi',
				'palette_preset'   => 'practitioner',
				'font'             => 'geist',
			)
		);
		$preset = Palettes::all()['practitioner']['light'];
		$this->assertSame(
			array(
				'format'   => 'pdf',
				'lang'     => 'hi',
				'branding' => array(
					'name'    => 'Luna Rivera Astrology',
					'footer'  => 'Book a reading at example.com.',
					'website' => home_url( '/' ),
					'colors'  => array(
						'primary' => $preset['fg'],
						'accent'  => $preset['accent'],
					),
					'font'    => 'geist',
					'theme'   => 'practitioner',
				),
				'page'     => array( 'size' => 'Letter' ),
			),
			ReportRoute::request( 'pdf' )
		);
	}

	public function test_a_preset_report_takes_the_preset_font_when_none_is_chosen(): void {
		update_option( SettingsPage::OPTION_NAME, array( 'palette_preset' => 'celeste' ) );
		$branding = ReportRoute::request( 'pdf' )['branding'];
		$this->assertSame( 'celeste', $branding['theme'] );
		$this->assertSame( Palettes::all()['celeste']['font'], $branding['font'] );
	}

	public function test_the_site_font_sends_no_font(): void {
		update_option(
			SettingsPage::OPTION_NAME,
			array(
				'palette_preset' => 'practitioner',
				'font'           => Theming::SITE_FONT,
			)
		);
		$branding = ReportRoute::request( 'pdf' )['branding'];
		$this->assertSame( 'practitioner', $branding['theme'] );
		$this->assertArrayNotHasKey( 'font', $branding );
	}

	public function test_custom_colours_reach_the_report_as_six_digit_light_colours(): void {
		update_option(
			SettingsPage::OPTION_NAME,
			array(
				'accent_color'      => '#b45',
				'fg_color'          => '#1e293b',
				'accent_color_dark' => '#ffffff',
			)
		);
		$this->assertSame(
			array(
				'format'   => 'pdf',
				'branding' => array(
					'name'    => get_bloginfo( 'name' ),
					'website' => home_url( '/' ),
					'colors'  => array(
						'primary' => '#1e293b',
						'accent'  => '#bb4455',
					),
				),
			),
			ReportRoute::request( 'pdf' ),
			'Custom colours with no preset and no font send no theme and no font.'
		);
	}

	public function test_the_logo_travels_only_over_https(): void {
		$logo = self::factory()->attachment->create_object( 'logo.png', 0, array( 'post_mime_type' => 'image/png' ) );
		update_option( SettingsPage::OPTION_NAME, array( 'report_logo' => $logo ) );

		$scheme = static fn( string $url ): string => set_url_scheme( $url, 'https' );
		add_filter( 'wp_get_attachment_url', $scheme );
		$this->assertStringStartsWith( 'https://', (string) ( ReportRoute::request( 'pdf' )['branding']['logo'] ?? '' ) );
		remove_filter( 'wp_get_attachment_url', $scheme );

		$plain = static fn( string $url ): string => set_url_scheme( $url, 'http' );
		add_filter( 'wp_get_attachment_url', $plain );
		$this->assertArrayNotHasKey( 'logo', ReportRoute::request( 'pdf' )['branding'], 'An http logo would fail the whole report.' );
		remove_filter( 'wp_get_attachment_url', $plain );
	}

	public function test_a_visitor_post_downloads_a_branded_pdf_and_stores_nothing(): void {
		update_option(
			SettingsPage::OPTION_NAME,
			array(
				'report_name'      => 'Luna',
				'report_page_size' => 'A4',
			)
		);
		$this->answer = $this->pdf_answer();

		$response = $this->post( $this->visitor_post( array( 'page' => array( 'size' => 'Letter' ) ) ) );

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( '%PDF-1.7 report', $response->get_data() );
		$headers = $response->get_headers();
		$this->assertSame( 'application/pdf', $headers['Content-Type'] );
		$this->assertSame( 'attachment; filename="Natal-chart-report.pdf"', $headers['Content-Disposition'] );
		$this->assertSame( 'no-store, private', $headers['Cache-Control'] );

		$this->assertCount( 1, $this->sent );
		$this->assertStringContainsString( '/reports/natal', $this->sent[0]['url'] );
		$body = $this->sent[0]['body'];
		$this->assertSame( 'pdf', $body['format'] );
		$this->assertSame(
			array(
				'name'    => 'Luna',
				'website' => home_url( '/' ),
			),
			$body['branding']
		);
		$this->assertSame( array( 'size' => 'Letter' ), $body['page'], 'The visitor page size wins over the site default.' );
		$this->assertSame( '1990-07-15', $body['date'] );

		$this->assertSame( array(), $this->stored_results(), 'Neither the post nor the document is stored.' );
		$this->post( $this->visitor_post() );
		$this->assertCount( 2, $this->sent, 'A report is never served from the cache.' );
	}

	public function test_html_opens_in_the_browser_sandboxed(): void {
		$this->answer = array(
			200,
			array(
				'format' => 'html',
				'title'  => 'Natal chart report',
				'html'   => '<!doctype html><title>r</title>',
			),
		);
		$response = $this->post( $this->visitor_post(), 'natal', $this->action_query( '[roxy_report format="html"]' ) );
		$headers  = $response->get_headers();
		$this->assertSame( 'html', $this->sent[0]['body']['format'] );
		$this->assertSame( '<!doctype html><title>r</title>', $response->get_data() );
		$this->assertSame( 'text/html; charset=utf-8', $headers['Content-Type'] );
		$this->assertSame( 'inline; filename="Natal-chart-report.html"', $headers['Content-Disposition'] );
		$this->assertStringStartsWith( 'sandbox;', $headers['Content-Security-Policy'] );
	}

	/**
	 * Every refusal goes back to the page the visitor submitted from, with a
	 * message the form renders and none of what they typed.
	 *
	 * @return array<string, array{0: string[], 1: array<string, string>, 2: string}>
	 */
	public function refusals(): array {
		return array(
			'no nonce'      => array( array( '_wpnonce' ), array(), 'error' ),
			'stale nonce'   => array( array(), array( '_wpnonce' => 'stale' ), 'error' ),
			'no consent'    => array( array( 'roxyapi_consent' ), array(), 'validation_errors' ),
			'missing input' => array( array(), array( 'date' => '' ), 'validation_errors' ),
		);
	}

	/**
	 * @dataProvider refusals
	 *
	 * @param string[]              $drop    Fields left out of a good post.
	 * @param array<string, string> $changed Fields that differ from a good post.
	 * @param string                $key     Payload key the form reads.
	 */
	public function test_a_refused_post_goes_back_to_the_form_without_the_input( array $drop, array $changed, string $key ): void {
		$response = $this->post( array_diff_key( $this->visitor_post( $changed ), array_flip( $drop ) ) );

		$this->assertSame( 303, $response->get_status() );
		$this->assertStringStartsWith( home_url( '/reading/?roxyapi_r=generateNatalReport.' ), $response->get_headers()['Location'] );
		$stored = $this->stored_results();
		$this->assertCount( 1, $stored );
		$payload = maybe_unserialize( $stored[0] );
		$this->assertArrayHasKey( $key, $payload );
		$this->assertArrayNotHasKey( 'previous_input', $payload, 'A visitor detail is never stored.' );
		$this->assertStringNotContainsString( '1990-07-15', $stored[0] );
		$this->assertSame( array(), $this->sent, 'A refused post calls nothing.' );
	}

	public function test_core_hands_a_stale_nonce_on_this_route_to_the_route(): void {
		$stale = new \WP_Error( 'rest_cookie_invalid_nonce', 'Cookie check failed' );
		$GLOBALS['wp']->query_vars['rest_route'] = '/roxyapi/v1/report/natal';
		$this->assertNull( ReportRoute::stale_nonce( $stale ) );
		$GLOBALS['wp']->query_vars['rest_route'] = '/wp/v2/posts';
		$this->assertSame( $stale, ReportRoute::stale_nonce( $stale ) );
		unset( $GLOBALS['wp']->query_vars['rest_route'] );
	}

	public function test_an_api_failure_goes_back_to_the_form(): void {
		$this->answer = array(
			400,
			array(
				'error' => 'branding.font: Invalid option',
				'code'  => 'validation_error',
			),
		);
		$response = $this->post( $this->visitor_post() );
		$this->assertSame( 303, $response->get_status() );
		$payload = maybe_unserialize( $this->stored_results()[0] );
		$this->assertSame( 'roxyapi_http_400', $payload['wp_error']['code'] );
	}

	public function test_an_answer_without_its_document_is_a_failure(): void {
		$this->answer = array(
			200,
			array(
				'format' => 'pdf',
				'title'  => 'Natal chart report',
				'pdf'    => '***',
			),
		);
		$outcome = ReportRoute::deliver( 'natal', 'pdf', self::POST );
		$this->assertSame( 'roxyapi_report', $outcome['wp_error']['code'] );
	}

	public function test_an_unknown_report_route_is_not_found(): void {
		$this->assertSame( 404, $this->post( $this->visitor_post(), 'nope' )->get_status() );
	}

	public function test_only_a_report_response_is_served_as_its_own_bytes(): void {
		$document = new WP_REST_Response( '%PDF-1.7', 200 );
		ob_start();
		$served = ReportRoute::serve( false, $document, new WP_REST_Request( 'POST', '/roxyapi/v1/report/natal' ) );
		$this->assertTrue( $served );
		$this->assertSame( '%PDF-1.7', ob_get_clean() );
		$this->assertFalse( ReportRoute::serve( false, $document, new WP_REST_Request( 'GET', '/roxyapi/v1/geocode' ) ) );
		$this->assertTrue( ReportRoute::serve( true, $document, new WP_REST_Request( 'POST', '/roxyapi/v1/report/natal' ) ) );
	}

	public function test_the_reports_tab_holds_each_choice_to_what_a_report_accepts(): void {
		$out = SettingsFields::sanitize(
			array(
				'report_name'      => str_repeat( 'a', Reports::NAME_MAX_LENGTH + 5 ),
				'report_footer'    => str_repeat( 'b', Reports::FOOTER_MAX_LENGTH + 5 ),
				'report_page_size' => 'Legal',
				'report_language'  => 'xx',
				'report_logo'      => 'abc',
			)
		);
		$this->assertSame( Reports::NAME_MAX_LENGTH, strlen( $out['report_name'] ) );
		$this->assertSame( Reports::FOOTER_MAX_LENGTH, strlen( $out['report_footer'] ) );
		$this->assertSame( '', $out['report_page_size'] );
		$this->assertSame( '', $out['report_language'] );
		$this->assertSame( 0, $out['report_logo'] );
	}

	public function test_the_reports_tab_holds_no_appearance_field(): void {
		$fields = array_keys( SettingsSchema::fields() );
		$this->assertSame(
			array( 'report_name', 'report_logo', 'report_footer', 'report_page_size', 'report_language' ),
			array_values( preg_grep( '/^report_/', $fields ) ),
			'Colours and the font come from the Branding tab alone.'
		);
	}

	public function test_saving_an_http_logo_tells_the_owner(): void {
		$logo = self::factory()->attachment->create_object( 'logo.png', 0, array( 'post_mime_type' => 'image/png' ) );
		$plain = static fn( string $url ): string => set_url_scheme( $url, 'http' );
		add_filter( 'wp_get_attachment_url', $plain );
		SettingsFields::sanitize( array( 'report_logo' => (string) $logo ) );
		remove_filter( 'wp_get_attachment_url', $plain );
		$this->assertContains( 'report_logo_https', array_column( get_settings_errors( SettingsPage::OPTION_NAME ), 'code' ) );
	}

	public function test_the_fonts_offered_are_the_catalogue_fonts_the_api_accepts(): void {
		$catalogue = json_decode( (string) file_get_contents( dirname( __DIR__, 2 ) . '/bin/ui-fonts.json' ), true );
		$this->assertSame( array_column( $catalogue, 'name', 'key' ), Palettes::FONTS );
		$spec = json_decode( (string) file_get_contents( dirname( __DIR__, 2 ) . '/specs/openapi.json' ), true );
		$this->assertSame(
			array_keys( Palettes::FONTS ),
			$spec['components']['schemas']['NatalReportRequest']['properties']['branding']['properties']['font']['enum'] ?? null
		);
	}

	/**
	 * `hide_readings` reaches a report the way it reaches every reading: the
	 * placement attribute wins, the site setting decides when it is silent, and
	 * a hidden reading asks the API for a chart-only report.
	 *
	 * @return array<string, array{0: array<string, mixed>, 1: string, 2: bool}>
	 */
	public function readings_cases(): array {
		return array(
			'placement hides'           => array( array(), '[roxy_report hide_readings="1"]', false ),
			'setting hides'             => array( array( 'hide_readings' => true ), '[roxy_report]', false ),
			'placement keeps over site' => array( array( 'hide_readings' => true ), '[roxy_report hide_readings="0"]', true ),
			'nothing set'               => array( array(), '[roxy_report]', true ),
		);
	}

	/**
	 * @dataProvider readings_cases
	 *
	 * @param array<string, mixed> $settings  Saved settings.
	 * @param string               $shortcode The placement.
	 * @param bool                 $readings  Whether the request keeps the readings.
	 */
	public function test_hide_readings_reaches_the_report_request( array $settings, string $shortcode, bool $readings ): void {
		update_option( SettingsPage::OPTION_NAME, $settings );
		$this->answer = $this->pdf_answer();
		$this->assertSame( 200, $this->post( $this->visitor_post(), 'natal', $this->action_query( $shortcode ) )->get_status() );

		$body = $this->sent[0]['body'];
		if ( $readings ) {
			$this->assertArrayNotHasKey( 'readings', $body, 'The API default keeps the readings.' );
		} else {
			$this->assertFalse( $body['readings'] );
		}
	}

	public function test_the_block_follows_the_site_setting_for_readings(): void {
		update_option( SettingsPage::OPTION_NAME, array( 'hide_readings' => true ) );
		$html = \RoxyAPI\Generated\Heroes\Report::render( \RoxyAPI\Support\BlockOutput::to_shortcode_atts( array( 'report' => 'tarot' ) ) );
		$this->assertStringContainsString( 'readings=0', $html );
		$this->assertStringContainsString( 'placement=0.', $html, 'A block placement is signed like a shortcode.' );
	}

	/**
	 * A placement token is good for its own report, format and readings choice
	 * only: the REST nonce is shared by every guest on every page, so a free
	 * report's form must not buy any other report.
	 *
	 * @return array<string, array{0: string, 1: callable(array<string, string>): array<string, string>}>
	 */
	public function foreign_placements(): array {
		return array(
			'another report'        => array( '[roxy_report report="tarot"]', static fn( array $q ): array => $q ),
			'another format'        => array( '[roxy_report]', static fn( array $q ): array => array( 'format' => 'html' ) + $q ),
			'readings put back'     => array( '[roxy_report hide_readings="1"]', static fn( array $q ): array => array_diff_key( $q, array( 'readings' => '' ) ) ),
			'tampered post'         => array( '[roxy_report]', static fn( array $q ): array => array( 'placement' => (string) preg_replace( '/^0\./', '7.', $q['placement'] ) ) + $q ),
			'tampered signature'    => array( '[roxy_report]', static fn( array $q ): array => array( 'placement' => (string) preg_replace( '/.$/', 'x', $q['placement'] ) ) + $q ),
			'no placement'          => array( '[roxy_report]', static fn( array $q ): array => array_diff_key( $q, array( 'placement' => '' ) ) ),
		);
	}

	/**
	 * @dataProvider foreign_placements
	 *
	 * @param string                                                     $shortcode The placement whose form the post came from.
	 * @param callable(array<string, string>): array<string, string> $change    What the post changes in its query.
	 */
	public function test_a_post_from_another_placement_is_refused( string $shortcode, callable $change ): void {
		$this->answer = $this->pdf_answer();
		$response     = $this->post( $this->visitor_post(), 'natal', $change( $this->action_query( $shortcode ) ) );

		$this->assertSame( 303, $response->get_status() );
		$this->assertArrayHasKey( 'error', maybe_unserialize( $this->stored_results()[0] ) );
		$this->assertSame( array(), $this->sent, 'A foreign placement calls nothing.' );
	}

	public function test_an_expired_placement_is_refused(): void {
		$sign    = new \ReflectionMethod( ReportRoute::class, 'sign' );
		$expires = time() - 1;
		$sign->setAccessible( true );
		$query = array(
			'format'    => 'pdf',
			'placement' => '0.' . $expires . '.' . $sign->invoke( null, 'natal', 'pdf', true, 0, $expires ),
		);
		$this->answer = $this->pdf_answer();

		$this->assertSame( 303, $this->post( $this->visitor_post(), 'natal', $query )->get_status() );
		$this->assertSame( array(), $this->sent );
		$query['placement'] = '0.' . ( $expires + 3600 ) . '.' . $sign->invoke( null, 'natal', 'pdf', true, 0, $expires + 3600 );
		$this->assertSame( 200, $this->post( $this->visitor_post(), 'natal', $query )->get_status(), 'The same signature in date is good.' );
	}

	/**
	 * The placement's post decides who may post its form, by the rules
	 * WordPress uses to show that post.
	 *
	 * @return array<string, array{0: array<string, string>, 1: string, 2: int}>
	 */
	public function placement_posts(): array {
		return array(
			'published, guest'          => array( array(), '', 200 ),
			'published, subscriber'     => array( array(), 'subscriber', 200 ),
			'private, guest'            => array( array( 'post_status' => 'private' ), '', 303 ),
			'private, editor'           => array( array( 'post_status' => 'private' ), 'editor', 200 ),
			'draft, guest'              => array( array( 'post_status' => 'draft' ), '', 303 ),
			'password, guest'           => array( array( 'post_password' => 'moon' ), '', 303 ),
			'password, editor no cookie' => array( array( 'post_password' => 'moon' ), 'editor', 303 ),
		);
	}

	/**
	 * @dataProvider placement_posts
	 *
	 * @param array<string, string> $post_args Post fields beside the placement.
	 * @param string                $role      Visitor role, or empty for a guest.
	 * @param int                   $status    Expected route status.
	 */
	public function test_a_placement_post_the_visitor_cannot_read_is_refused( array $post_args, string $role, int $status ): void {
		$post_id = self::factory()->post->create( $post_args + array( 'post_content' => '[roxy_report]' ) );
		$query   = $this->action_query( '[roxy_report]', $post_id );
		$this->assertStringStartsWith( $post_id . '.', $query['placement'] );
		if ( $role !== '' ) {
			wp_set_current_user( self::factory()->user->create( array( 'role' => $role ) ) );
		}
		$this->answer = $this->pdf_answer();

		$this->assertSame( $status, $this->post( $this->visitor_post(), 'natal', $query )->get_status() );
		$this->assertCount( $status === 200 ? 1 : 0, $this->sent );
	}

	public function test_a_signed_in_visitor_denied_read_post_is_refused_on_a_published_post(): void {
		$post_id = self::factory()->post->create( array( 'post_content' => '[roxy_report]' ) );
		$query   = $this->action_query( '[roxy_report]', $post_id );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		// A membership plugin revoking access the way core lets it: through the meta capability.
		$deny = static fn( array $caps, string $cap, int $user_id, array $args ): array => $cap === 'read_post' && (int) ( $args[0] ?? 0 ) === $post_id ? array( 'do_not_allow' ) : $caps;
		add_filter( 'map_meta_cap', $deny, 10, 4 );
		$this->answer = $this->pdf_answer();

		$this->assertSame( 303, $this->post( $this->visitor_post(), 'natal', $query )->get_status() );
		$this->assertSame( array(), $this->sent );
		remove_filter( 'map_meta_cap', $deny, 10 );
	}
}
