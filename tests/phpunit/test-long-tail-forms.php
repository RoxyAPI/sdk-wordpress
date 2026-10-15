<?php
/**
 * A long-tail shortcode whose required inputs a visitor can enter renders the
 * visitor form while any of them is missing, the same `mode` contract the
 * heroes keep: `auto` shows the form when an input is missing, `form` always
 * shows it, `static` never does.
 *
 * The sweep is derived from the generated classes, so a reading that gains a
 * form on regeneration is covered without an edit here.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Support\FormRouter;

class Test_Long_Tail_Forms extends \WP_UnitTestCase {

	/** Every outgoing request URL, in order. */
	private array $requested = array();

	/** The last outgoing request body, decoded. */
	private ?array $body = null;

	/** Redirect target FormRouter computed. */
	private ?string $redirect = null;

	public function setUp(): void {
		parent::setUp();
		\RoxyAPI\Api\Cache::flush_all();
		$this->requested = array();
		$this->body      = null;
		$this->redirect  = null;
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) {
				$this->requested[] = $url;
				$this->body        = is_string( $args['body'] ?? null ) ? json_decode( $args['body'], true ) : null;
				return array(
					'headers'  => array(),
					'body'     => wp_json_encode( array( 'ok' => true ) ),
					'response' => array(
						'code'    => 200,
						'message' => 'OK',
					),
					'cookies'  => array(),
					'filename' => null,
				);
			},
			10,
			3
		);
		add_filter(
			'roxyapi_form_router_skip_redirect',
			function ( $skip, $token, $url ) {
				$this->redirect = (string) $url;
				return $url;
			},
			10,
			3
		);
	}

	public function tearDown(): void {
		remove_all_filters( 'pre_http_request' );
		remove_all_filters( 'roxyapi_form_router_skip_redirect' );
		\RoxyAPI\Api\Cache::flush_all();
		parent::tearDown();
	}

	/**
	 * Generated shortcode classes that take a `mode`, keyed by operationId.
	 *
	 * @return array<string, class-string>
	 */
	private function form_capable(): array {
		$out = array();
		foreach ( \RoxyAPI\Generated\Endpoints::all() as $op_id => $ep ) {
			$class = '\\RoxyAPI\\Generated\\Shortcodes\\' . ucfirst( $op_id );
			if ( class_exists( $class ) && array_key_exists( 'mode', $class::DEFAULTS ) ) {
				$out[ $op_id ] = $class;
			}
		}
		return $out;
	}

	public function test_the_sweep_is_not_vacuous(): void {
		$this->assertArrayHasKey( 'calculateLuckPillars', $this->form_capable() );
		$this->assertArrayHasKey( 'getAnnualFlyingStars', $this->form_capable(), 'A GET keyed by a year takes a form too.' );
		$this->assertArrayHasKey( 'getDailyZodiacReading', $this->form_capable(), 'An `id` with a closed list of values is a picker.' );
		$this->assertArrayNotHasKey( 'getCard', $this->form_capable(), 'A free-string identifier stays placed by the site owner.' );
	}

	public function test_every_bare_form_capable_shortcode_renders_its_form_and_sends_nothing(): void {
		foreach ( $this->form_capable() as $op_id => $class ) {
			$html = $class::render( array(), '', $class::TAG );
			$this->assertStringContainsString( 'name="roxyapi_form" value="' . $op_id . '"', $html, $op_id );
		}
		$this->assertSame( array(), $this->requested, 'A form waiting for a visitor must not call the API.' );
	}

	/** Every required form field is an attribute the shortcode accepts, so the two describe one reading. */
	public function test_every_required_form_field_is_a_shortcode_attribute(): void {
		foreach ( $this->form_capable() as $op_id => $class ) {
			$form = '\\RoxyAPI\\Generated\\Forms\\' . ucfirst( $op_id ) . 'Form';
			$this->assertTrue( class_exists( $form ), $op_id );
			$required = array_filter( $form::spec()['flat_fields'], static fn( $f ) => ! empty( $f['required'] ) );
			$this->assertNotEmpty( $required, $op_id );
			foreach ( $required as $field ) {
				$attr = strtolower( (string) preg_replace( '/([a-z0-9])([A-Z])/', '$1_$2', (string) $field['name'] ) );
				$this->assertArrayHasKey( $attr, $class::DEFAULTS, $op_id . ' ' . $field['name'] );
			}
		}
	}

	public function test_an_id_picker_is_labelled_with_what_it_names(): void {
		$html = \RoxyAPI\Generated\Shortcodes\GetDailyZodiacReading::render( array() );

		$this->assertMatchesRegularExpression( '#<label[^>]*>\s*Animal#', $html );
		$this->assertStringContainsString( '<option value="dragon"', $html );
		foreach ( $this->form_capable() as $op_id => $class ) {
			$form = '\\RoxyAPI\\Generated\\Forms\\' . ucfirst( $op_id ) . 'Form';
			$spec = $form::spec();
			$fields = $spec['flat_fields'];
			foreach ( $spec['sections'] as $section ) {
				$fields = array_merge( $fields, $section['fields'] );
			}
			foreach ( $fields as $field ) {
				$this->assertNotSame( 'id', strtolower( trim( $field['label'] ) ), $op_id );
			}
		}
	}

	public function test_mode_decides_between_form_and_reading(): void {
		$class = \RoxyAPI\Generated\Shortcodes\CalculateLuckPillars::class;
		$full  = array(
			'date'     => '1990-06-15',
			'time'     => '14:30:00',
			'timezone' => 'UTC',
			'gender'   => 'male',
		);

		$this->assertStringNotContainsString( 'roxyapi-form', $class::render( array( 'mode' => 'static' ), '', $class::TAG ) );
		$this->assertSame( array(), $this->requested, 'Static mode with inputs missing keeps the owner notice and sends nothing.' );

		$this->assertStringContainsString( 'roxyapi-form', $class::render( $full + array( 'mode' => 'form' ), '', $class::TAG ) );
		$this->assertSame( array(), $this->requested );

		$this->assertStringNotContainsString( 'class="roxyapi-form"', $class::render( $full, '', $class::TAG ) );
		$this->assertCount( 1, $this->requested, 'Every required input placed renders the reading.' );
	}

	public function test_model_folded_inputs_fold_and_the_city_search_fills_the_flat_location(): void {
		$html = \RoxyAPI\Generated\Shortcodes\CalculateLuckPillars::render( array() );

		$this->assertStringContainsString( 'data-roxyapi-geocode="#roxyapi-form-calculateLuckPillars"', $html );
		$this->assertStringContainsString( 'id="roxyapi-form-calculateLuckPillars"', $html );
		$advanced = substr( $html, (int) strpos( $html, 'roxyapi-form-calculateLuckPillars-advanced' ) );
		$this->assertStringContainsString( 'name="dayBoundary"', $advanced, 'An input marked fold by the UI model folds under Advanced.' );
		$this->assertStringNotContainsString( 'name="latitude"', $advanced, 'Optional location inputs stay visible when the model does not fold them.' );
		$this->assertStringNotContainsString( 'name="longitude"', $advanced );
		$this->assertStringNotContainsString( 'name="gender"', $advanced, 'A required input stays in view.' );
	}

	public function test_a_curated_hero_form_keeps_its_optional_fields_in_view(): void {
		$html = \RoxyAPI\Support\FormRenderer::render( \RoxyAPI\Generated\Forms\TarotYesNoForm::class );
		$this->assertStringNotContainsString( '-advanced', $html );
	}

	public function test_a_submitted_flat_post_form_reaches_the_api_body(): void {
		$this->submit(
			'calculateLuckPillars',
			array(
				'date'     => '1990-06-15',
				'time'     => '14:30',
				'timezone' => 'America/New_York',
				'gender'   => 'male',
				'latitude' => '40.7128',
				'count'    => '8',
			)
		);

		$this->assertStringContainsString( 'chinese-astrology/bazi/luck-pillars', $this->requested[0] );
		$this->assertSame( 'America/New_York', $this->body['timezone'] );
		$this->assertSame( 40.7128, $this->body['latitude'] );
		$this->assertSame( 8, $this->body['count'] );
		$this->assertArrayNotHasKey( 'dayBoundary', $this->body, 'An input left empty is not sent.' );
	}

	public function test_a_submitted_get_form_fills_the_path(): void {
		$this->submit( 'getAnnualFlyingStars', array( 'year' => '2026' ) );

		$this->assertStringContainsString( 'feng-shui/flying-stars/annual/2026', $this->requested[0] );
	}

	public function test_a_submitted_id_picker_fills_the_path(): void {
		$this->submit( 'getRashi', array( 'id' => 'simha' ) );

		$this->assertStringContainsString( 'vedic-astrology/rashis/simha', $this->requested[0] );
	}

	/**
	 * Post a consented visitor form through the router.
	 *
	 * @param string               $form_id Form id.
	 * @param array<string, mixed> $fields  Field values.
	 */
	private function submit( string $form_id, array $fields ): void {
		$_POST                     = $fields + array(
			'roxyapi_form'    => $form_id,
			'_wpnonce'        => wp_create_nonce( 'roxyapi_form_' . $form_id ),
			'roxyapi_consent' => '1',
		);
		$_SERVER['REQUEST_METHOD'] = 'POST';
		FormRouter::maybe_handle();
		$_POST = array();
		unset( $_SERVER['REQUEST_METHOD'] );
		$this->assertNotNull( $this->redirect, 'The submission must complete its redirect.' );
		$this->assertNotEmpty( $this->requested, 'The submission must reach the API.' );
	}
}
