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
			$this->assertStringContainsString( '>Zona horaria ', $html );
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
				$this->assertNotSame( 'hidden', $control->item( 0 )->getAttribute( 'type' ), 'Manual location entry must work without JavaScript.' );
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

	public function test_sections_fold_only_when_the_model_marks_them(): void {
		$xp = new \DOMXPath( $this->document( FormRenderer::render( \RoxyAPI\Generated\Forms\CalculateEntrancePadaForm::class ) ) );
		$this->assertCount( 1, $xp->query( '//fieldset[@id="roxyapi-section-calculateEntrancePada-plot"]' ), 'An optional section without fold remains visible.' );
		$xp = new \DOMXPath( $this->document( FormRenderer::render( \RoxyAPI\Generated\Forms\GenerateTimelineForm::class ) ) );
		$this->assertCount( 1, $xp->query( '//details[@id="roxyapi-section-generateTimeline-domainWeights"]' ) );
		$this->assertCount( 0, $xp->query( '//details[@id="roxyapi-section-generateTimeline-domainWeights"]//details' ), 'A folded group already folds its marked fields.' );
	}

	public function test_curated_hero_html_matches_the_pre_model_renderer(): void {
		// Frozen from all 13 curated forms rendered before the renderer change.
		// Normalise only request-specific markup, timezone option lists and the
		// new target metadata; labels, field order and layout remain covered.
		$hashes = array(
			'NatalChartForm'       => 'f2fb57cf681750aa2d0b7682ec3ee3bc40d5cff0e319e9e6fe92e73a3ef7c900',
			'KundliForm'           => 'b021c8b85364477756a9a256b5cb91af58106c7aa9760813629673394b0d7241',
			'PanchangForm'         => '48a1a001d19b683477652d76dc02a5f0fc5c98bb29811ffc4aeb665ef7aa30f0',
			'MangalDoshaForm'      => '96194b0280b53488827de4d9c0331f6db99398cd8c620d3070ae5b19b624d535',
			'KpChartForm'          => 'd32c49b0740316a1bdc4fcc03ffeb5f20d33fd0269c1c25c4d6479bcef3d6ed0',
			'BodygraphForm'        => '215ccf1fe1f9327a4be89fb35d44070b1fd50a0c8a24275c8ddc13f0ccf44acb',
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
