<?php
/**
 * Visitor HTML follows the UI model and its language catalogues.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Support\FormRenderer;

class Test_Form_Render_Intent extends \WP_UnitTestCase {

	public function test_spanish_locale_translates_generated_fields_options_and_legends(): void {
		$saved = get_option( 'roxyapi_settings', array() );
		update_option( 'roxyapi_settings', array( 'display_language' => '' ) );
		$locale = static fn() => 'es_ES';
		add_filter( 'locale', $locale );
		try {
			$html = FormRenderer::render( \RoxyAPI\Generated\Forms\CalculateLuckPillarsForm::class );
			$this->assertStringContainsString( '>Fecha ', $html );
			$this->assertStringContainsString( '<option value="male">Masculino</option>', $html );
			$this->assertStringContainsString( '<option value="midnight">Medianoche</option>', $html );
			$this->assertStringNotContainsString( '>Male</option>', $html );
			$synastry = FormRenderer::render( \RoxyAPI\Generated\Forms\CalculateSynastryForm::class );
			$this->assertStringContainsString( '<legend>Persona 1</legend>', $synastry );
			$this->assertStringContainsString( '<legend>Persona 2</legend>', $synastry );

			update_option( 'roxyapi_settings', array( 'display_language' => 'en' ) );
			$reading_en = FormRenderer::render( \RoxyAPI\Generated\Forms\CalculateLuckPillarsForm::class );
			$this->assertStringContainsString( '<option value="male">Masculino</option>', $reading_en, 'The form is page chrome and follows the site locale; the reading language only changes the reading.' );
		} finally {
			remove_filter( 'locale', $locale );
			update_option( 'roxyapi_settings', $saved );
		}
	}

	public function test_missing_language_and_catalogue_entries_fall_back_to_english(): void {
		$class = \RoxyAPI\Generated\FormLabels::class;
		$this->assertSame( $class::for_language( 'en' ), $class::for_language( '' ) );
		$this->assertSame( $class::for_language( 'en' ), $class::for_language( 'unsupported' ) );
		$this->assertSame( 'Brahma', $class::for_language( 'es' )['enums']['id.brahma'] );
	}

	public function test_relocation_searches_target_separate_exact_posted_names(): void {
		$html = FormRenderer::render( \RoxyAPI\Generated\Forms\GenerateRelocationChartForm::class );
		$dom  = $this->document( $html );
		$xp   = new \DOMXPath( $dom );
		$city = $xp->query( '//input[@data-roxyapi-geocode-fields]' );
		$this->assertCount( 2, $city );
		$expected = array(
			array( 'timezone' => 'timezone', 'latitude' => 'birthLatitude', 'longitude' => 'birthLongitude' ),
			array( 'latitude' => 'relocationLatitude', 'longitude' => 'relocationLongitude' ),
		);
		foreach ( $city as $index => $input ) {
			$this->assertSame( $expected[ $index ], json_decode( $input->getAttribute( 'data-roxyapi-geocode-fields' ), true ) );
			$this->assertSame( '#roxyapi-form-generateRelocationChart', $input->getAttribute( 'data-roxyapi-geocode' ) );
			$anchor = $xp->query( 'following::input[@name] | following::select[@name]', $input )->item( 0 );
			$this->assertSame( $index === 0 ? 'timezone' : 'relocationLatitude', $anchor->getAttribute( 'name' ) );
			foreach ( $expected[ $index ] as $name ) {
				$control = $xp->query( '//*[@name="' . $name . '"]' );
				$this->assertCount( 1, $control, $name );
				$this->assertSame( 'hidden', $control->item( 0 )->getAttribute( 'type' ), 'The city search fills it; a visitor never types it.' );
			}
		}
		$this->assertNotSame( $city->item( 0 )->getAttribute( 'id' ), $city->item( 1 )->getAttribute( 'id' ) );
	}

	public function test_natal_synastry_and_luck_pillars_have_one_search_per_location(): void {
		foreach ( array( 'CalculateSynastryForm' => 2, 'CalculateLuckPillarsForm' => 1, 'NatalChartForm' => 1 ) as $name => $count ) {
			$html = FormRenderer::render( '\\RoxyAPI\\Generated\\Forms\\' . $name );
			$this->assertSame( $count, substr_count( $html, 'data-roxyapi-geocode-fields=' ), $name );
			if ( $name === 'CalculateSynastryForm' ) {
				$xp = new \DOMXPath( $this->document( $html ) );
				foreach ( $xp->query( '//input[@data-roxyapi-geocode-fields]' ) as $index => $input ) {
					$person = 'person' . ( $index + 1 );
					$this->assertSame(
						array( 'latitude' => $person . '[latitude]', 'longitude' => $person . '[longitude]', 'timezone' => $person . '[timezone]' ),
						json_decode( $input->getAttribute( 'data-roxyapi-geocode-fields' ), true )
					);
				}
			}
		}
	}

	public function test_a_clock_timezone_preselects_the_visitor_zone_and_a_birth_timezone_does_not(): void {
		\RoxyAPI\Plugin::register_viewer_zone_script();
		$xp     = new \DOMXPath( $this->document( FormRenderer::render( \RoxyAPI\Generated\Forms\GetEclipticCrossingsForm::class ) ) );
		$marked = $xp->query( '//select[@name="timezone"][@data-roxyapi-viewer-zone]' );
		$this->assertSame( 1, $marked->length );
		$this->assertTrue( wp_script_is( 'roxyapi-viewer-zone', 'enqueued' ) );
		$xp = new \DOMXPath( $this->document( FormRenderer::render( \RoxyAPI\Generated\Forms\GenerateRelocationChartForm::class ) ) );
		$this->assertSame( 0, $xp->query( '//select[@data-roxyapi-viewer-zone]' )->length );
	}

	public function test_sections_fold_only_when_the_model_marks_them(): void {
		$xp = new \DOMXPath( $this->document( FormRenderer::render( \RoxyAPI\Generated\Forms\CalculateEntrancePadaForm::class ) ) );
		$this->assertCount( 1, $xp->query( '//fieldset[@id="roxyapi-section-calculateEntrancePada-plot"]' ), 'An optional section without fold remains visible.' );
		$xp = new \DOMXPath( $this->document( FormRenderer::render( \RoxyAPI\Generated\Forms\GenerateTimelineForm::class ) ) );
		$this->assertCount( 1, $xp->query( '//details[@id="roxyapi-section-generateTimeline-domainWeights"]' ) );
		$this->assertCount( 0, $xp->query( '//details[@id="roxyapi-section-generateTimeline-domainWeights"]//details' ), 'A folded group already folds its marked fields.' );
	}

	/**
	 * Every visitor form, generated or curated: each field of a location group
	 * is a hidden field the city search writes, posted under its unchanged
	 * name, and none is ever a visible coordinate or timezone input.
	 */
	public function test_no_visitor_form_shows_a_typed_coordinate_or_timezone(): void {
		$checked = 0;
		foreach ( (array) glob( dirname( __DIR__, 2 ) . '/src/Generated/Forms/*Form.php' ) as $file ) {
			$class = '\\RoxyAPI\\Generated\\Forms\\' . basename( (string) $file, '.php' );
			$spec  = $class::spec();
			if ( empty( $spec['locations'] ) ) {
				continue;
			}
			$xp = new \DOMXPath( $this->document( FormRenderer::render( $class ) ) );
			foreach ( $spec['locations'] as $location ) {
				foreach ( $location['fields'] as $role => $name ) {
					$controls = $xp->query( '//*[@name="' . $name . '"]' );
					$this->assertCount( 1, $controls, "{$class} {$name}" );
					$this->assertSame( 'input', $controls->item( 0 )->nodeName, "{$class} {$name}" );
					$this->assertSame( 'hidden', $controls->item( 0 )->getAttribute( 'type' ), "{$class} {$role} is filled by the city search." );
				}
			}
			$this->assertSame( 0, $xp->query( '//label[@for][contains(@for, "atitude") or contains(@for, "ongitude")]' )->length, $class );
			++$checked;
		}
		$this->assertGreaterThan( 10, $checked );
	}

	/**
	 * The natal chart form as it renders after a visitor post of `$birth`.
	 *
	 * @param array<string, string> $birth Posted birth section.
	 * @return string HTML.
	 */
	private function render_after_post( array $birth ): string {
		$redirect = '';
		$capture  = static function ( $skip, $token, $url ) use ( &$redirect ) {
			$redirect = (string) $url;
			return $url;
		};
		add_filter( 'roxyapi_form_router_skip_redirect', $capture, 10, 3 );
		$_POST                     = array(
			'roxyapi_form'    => 'natalChart',
			'_wpnonce'        => wp_create_nonce( 'roxyapi_form_natalChart' ),
			'roxyapi_consent' => '1',
			'birth'           => $birth,
		);
		$_SERVER['REQUEST_METHOD'] = 'POST';
		try {
			\RoxyAPI\Support\FormRouter::maybe_handle();
			parse_str( (string) wp_parse_url( $redirect, PHP_URL_QUERY ), $query );
			$_GET['roxyapi_r'] = (string) ( $query['roxyapi_r'] ?? '' );
			return FormRenderer::render( \RoxyAPI\Generated\Forms\NatalChartForm::class );
		} finally {
			$_POST = array();
			unset( $_SERVER['REQUEST_METHOD'], $_GET['roxyapi_r'] );
			remove_filter( 'roxyapi_form_router_skip_redirect', $capture );
		}
	}

	public function test_a_post_with_no_city_shows_the_missing_input_notice_on_the_city_search(): void {
		$html = $this->render_after_post(
			array(
				'birth_date' => '1990-05-15',
				'birth_time' => '14:30',
				'lat'        => '',
				'lon'        => '',
				'tz'         => '',
			)
		);

		$xp     = new \DOMXPath( $this->document( $html ) );
		$search = $xp->query( '//p[contains(@class, "roxyapi-form-geocode")][contains(@class, "has-error")]' );
		$this->assertCount( 1, $search, 'The missing place is reported on the city search.' );
		$this->assertSame( 'City is required.', trim( $xp->query( './/span[@class="roxyapi-form-error"]', $search->item( 0 ) )->item( 0 )->textContent ) );
		$this->assertSame( 0, $xp->query( '//input[@type="hidden"][@aria-describedby]' )->length, 'A hidden field never carries an error of its own.' );
	}

	/**
	 * The city search text is not posted, so a place restored after a refused
	 * post would go out again under an empty search. The form keeps what the
	 * visitor typed and asks for the city again.
	 */
	public function test_a_refused_post_never_restores_a_place_the_city_search_no_longer_shows(): void {
		$html = $this->render_after_post(
			array(
				'birth_date' => '',
				'birth_time' => '14:30',
				'lat'        => '19.076',
				'lon'        => '72.8777',
				'tz'         => 'Asia/Kolkata',
			)
		);

		$xp = new \DOMXPath( $this->document( $html ) );
		foreach ( array( 'birth[lat]', 'birth[lon]', 'birth[tz]' ) as $name ) {
			$this->assertSame( '', $xp->query( '//input[@name="' . $name . '"]' )->item( 0 )->getAttribute( 'value' ), $name );
		}
		$this->assertSame( '14:30', $xp->query( '//input[@name="birth[birth_time]"]' )->item( 0 )->getAttribute( 'value' ), 'Every typed field comes back.' );
	}

	public function test_curated_hero_html_matches_the_pre_model_renderer(): void {
		// Frozen from all 13 curated forms, re-frozen when location fields moved
		// behind the city search as hidden fields (2026-11-04). Normalise only
		// request-specific markup, timezone option lists and the target
		// metadata; labels, field order and layout remain covered.
		$hashes = array(
			'NatalChartForm'       => '5d9de0d8bfede51074da81c66f4988f48a0406ee83add24cf5ba8036ffd85948',
			'KundliForm'           => '7eafd7dedb4c48dbb5c651a06e7280a1b5e5fc1e2435b08c6e47f6a4af0d0f4f',
			'PanchangForm'         => '290e00b59d08a92dc1dcc3e9edbc863326dead4993d1f4143fac10e1bd190f43',
			'MangalDoshaForm'      => 'd25a33de6e3b2ab2455ed26f8373029b4890352ee4db57ea82d9a7fc61b93c40',
			'KpChartForm'          => 'baf8042ebc90e57908b0e190fac4bc50694eab3ee5889ec65242ab840d252c03',
			'BodygraphForm'        => '4b955cf0a4184fd22c46c56d04a17612e6a3ce0e0d9478a49207d55382e1b69c',
			'TarotYesNoForm'       => '972b01bb89f56897bd5c5aac8782aed21f1fb74495cf45d0d514b19dbae0d1bf',
			'TarotCardForm'        => '4db9c5cabc80160f0e182c0f1e24a402218583ac9b34b134c76823cac141b987',
			'NumerologyForm'       => '48e146257180f736f356e4b80428e595c73e48cb3d2986c02f8ba4c281965353',
			'LifePathForm'         => 'dc1fbee18cdbf14691f0b04ace11784b7474187eb53499e114b06abeb319c599',
			'BiorhythmForm'        => 'b0971c7255cf23ffcc6c71bf80f26efc036e7256b105dbf63a2cecc847f548fb',
			'AngelNumberForm'      => 'c5915a1030a371cd7195d80f3070632753bb93f03967704e4412c5eed861baa4',
			'CrystalsByZodiacForm' => '919640e23a82190cabaeb09851baf2d1fac5f1a8881c0b71eb0ad858d95f5edc',
		);
		$saved = get_option( 'roxyapi_settings', array() );
		update_option( 'roxyapi_settings', array() );
		try {
			foreach ( $hashes as $name => $expected ) {
				$html = FormRenderer::render( '\\RoxyAPI\\Generated\\Forms\\' . $name );
				$html = preg_replace( '/ action="[^"]*"/', '', $html );
				$html = preg_replace( '/<input[^>]+name="(?:_wpnonce|_wp_http_referer)"[^>]*>/', '', $html );
				$html = preg_replace( '/ data-roxyapi-geocode-fields="[^"]*"/', '', $html );
				$html = preg_replace( '/(<select[^>]+name="[^"]*(?:tz|timezone)\\]?"[^>]*>).*?(<\\/select>)/s', '$1$2', $html );
				$html = preg_replace( '/>\\s+</', '><', $html );
				$this->assertSame( $expected, hash( 'sha256', $html ), $name );
			}
		} finally {
			update_option( 'roxyapi_settings', $saved );
		}
	}

	/**
	 * Parse generated HTML without relying on JavaScript.
	 *
	 * @param string $html Form HTML.
	 * @return \DOMDocument
	 */
	private function document( string $html ): \DOMDocument {
		$dom = new \DOMDocument();
		$dom->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING );
		return $dom;
	}
}
