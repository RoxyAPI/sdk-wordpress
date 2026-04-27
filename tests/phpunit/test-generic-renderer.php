<?php
/**
 * Tests for the GenericRenderer used by auto-generated shortcodes.
 *
 * The renderer emits a Roxy card layout: header (title + meta), lede paragraph,
 * scalar field grid, table for uniform object lists, sections / details for
 * deeper nested content.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Support\GenericRenderer;

class Test_Generic_Renderer extends \WP_UnitTestCase {

	public function test_empty_data_yields_empty_string(): void {
		$this->assertSame( '', GenericRenderer::render( 'getZodiacSign', array() ) );
	}

	public function test_top_level_card_class_includes_operation_id(): void {
		$out = GenericRenderer::render( 'getDailyHoroscope', array( 'name' => 'Aries' ) );
		$this->assertStringContainsString( 'class="roxyapi-card roxyapi-getDailyHoroscope"', $out );
	}

	public function test_class_attribute_sanitizes_markup_in_operation_id(): void {
		$out = GenericRenderer::render( '<script>alert(1)</script>', array( 'name' => 'X' ) );
		$this->assertStringNotContainsString( '<script>', $out );
		$this->assertStringNotContainsString( 'alert(1)', $out );
	}

	public function test_name_field_renders_as_h3_card_title(): void {
		$out = GenericRenderer::render( 'getX', array( 'name' => 'Pumpkin' ) );
		$this->assertMatchesRegularExpression( '#<h3 class="roxyapi-card-title">Pumpkin</h3>#', $out );
	}

	public function test_lede_field_renders_below_header(): void {
		$out = GenericRenderer::render(
			'getX',
			array(
				'name'    => 'Pumpkin',
				'meaning' => 'Pumpkins emerge in your dream to show resilience.',
			)
		);
		$this->assertStringContainsString( '<p class="roxyapi-card-lede">Pumpkins emerge in your dream to show resilience.</p>', $out );
		// Lede must render after the header.
		$header_pos = strpos( $out, 'roxyapi-card-header' );
		$lede_pos   = strpos( $out, 'roxyapi-card-lede' );
		$this->assertNotFalse( $header_pos );
		$this->assertNotFalse( $lede_pos );
		$this->assertGreaterThan( $header_pos, $lede_pos );
	}

	public function test_meta_keys_appear_in_meta_strip_not_field_grid(): void {
		$out = GenericRenderer::render(
			'getX',
			array(
				'name' => 'Pumpkin',
				'date' => '2026-04-26',
			)
		);
		$this->assertStringContainsString( 'class="roxyapi-card-meta"', $out );
		$this->assertStringContainsString( 'Date: 2026-04-26', $out );
		// Date should NOT also appear in the fields grid.
		$this->assertStringNotContainsString( '<dt>Date</dt>', $out );
	}

	public function test_scalar_fields_render_in_field_grid(): void {
		$out = GenericRenderer::render(
			'getX',
			array(
				'name'    => 'Pumpkin',
				'element' => 'Earth',
				'planet'  => 'Moon',
			)
		);
		$this->assertStringContainsString( '<dl class="roxyapi-fields">', $out );
		$this->assertStringContainsString( '<dt>Element</dt>', $out );
		$this->assertStringContainsString( '<dd>Earth</dd>', $out );
		$this->assertStringContainsString( '<dt>Planet</dt>', $out );
		$this->assertStringContainsString( '<dd>Moon</dd>', $out );
	}

	public function test_id_field_is_suppressed_when_name_present(): void {
		$out = GenericRenderer::render(
			'getX',
			array(
				'id'   => 'pumpkin',
				'name' => 'Pumpkin',
			)
		);
		// The diagnostic id should not appear anywhere user-facing.
		$this->assertStringNotContainsString( '<dt>Id</dt>', $out );
		$this->assertStringNotContainsString( '>pumpkin<', $out );
	}

	public function test_id_field_renders_when_no_name_or_title(): void {
		$out = GenericRenderer::render( 'getX', array( 'id' => 'pumpkin' ) );
		$this->assertStringContainsString( '<dt>Id</dt>', $out );
		$this->assertStringContainsString( '<dd>pumpkin</dd>', $out );
	}

	public function test_seed_field_is_always_suppressed(): void {
		$out = GenericRenderer::render(
			'getX',
			array(
				'seed' => '2026-04-26',
				'name' => 'Pumpkin',
			)
		);
		$this->assertStringNotContainsString( 'Seed', $out );
		$this->assertStringNotContainsString( '2026-04-26', $out );
	}

	public function test_uniform_object_list_renders_as_table(): void {
		$data = array(
			'cards' => array(
				array(
					'name'   => 'The Fool',
					'arcana' => 'major',
				),
				array(
					'name'   => 'Two of Cups',
					'arcana' => 'minor',
				),
			),
		);
		$out  = GenericRenderer::render( 'castSpread', $data );

		$this->assertStringContainsString( '<table class="roxyapi-table">', $out );
		$this->assertStringContainsString( '<thead><tr><th>Name</th><th>Arcana</th></tr></thead>', $out );
		$this->assertStringContainsString( '<td>The Fool</td>', $out );
		$this->assertStringContainsString( '<td>Two of Cups</td>', $out );
	}

	public function test_section_title_appears_for_nested_object(): void {
		$out = GenericRenderer::render(
			'getX',
			array(
				'name'   => 'Aries',
				'planet' => array(
					'name'    => 'Mars',
					'element' => 'fire',
				),
			)
		);
		$this->assertStringContainsString( '<section class="roxyapi-section">', $out );
		$this->assertStringContainsString( '<h4 class="roxyapi-section-title">Planet</h4>', $out );
		// Nested header renders the planet name.
		$this->assertStringContainsString( 'Mars', $out );
	}

	public function test_large_section_collapses_into_details(): void {
		// 9 keys is above DETAILS_THRESHOLD (8).
		$out = GenericRenderer::render(
			'getX',
			array(
				'planet' => array(
					'k1' => 1,
					'k2' => 2,
					'k3' => 3,
					'k4' => 4,
					'k5' => 5,
					'k6' => 6,
					'k7' => 7,
					'k8' => 8,
					'k9' => 9,
				),
			)
		);
		$this->assertStringContainsString( '<details class="roxyapi-details">', $out );
		$this->assertStringContainsString( '<summary>Planet</summary>', $out );
	}

	public function test_long_table_collapses_into_details(): void {
		$rows = array();
		for ( $i = 0; $i < 13; $i++ ) {
			$rows[] = array(
				'name'  => 'Item ' . $i,
				'value' => $i,
			);
		}
		$out = GenericRenderer::render( 'getX', array( 'rows' => $rows ) );
		$this->assertStringContainsString( '<details class="roxyapi-details">', $out );
		$this->assertStringContainsString( '<table class="roxyapi-table">', $out );
	}

	public function test_scalar_value_is_escaped(): void {
		$out = GenericRenderer::render( 'getX', array( 'note' => '<script>alert(1)</script>' ) );
		$this->assertStringNotContainsString( '<script>alert(1)</script>', $out );
		$this->assertStringContainsString( '&lt;script&gt;', $out );
	}

	public function test_humanize_camel_snake_kebab_pascal(): void {
		$cases = array(
			'luckyNumber'  => 'Lucky number',
			'lucky_number' => 'Lucky number',
			'lucky-number' => 'Lucky number',
			'LuckyNumber'  => 'Lucky number',
		);
		foreach ( $cases as $input => $expected ) {
			$out = GenericRenderer::render( 'getX', array( $input => 7 ) );
			$this->assertStringContainsString( '<dt>' . $expected . '</dt>', $out, "Failed humanizing $input" );
		}
	}

	public function test_image_url_field_renders_as_img_tag(): void {
		$out = GenericRenderer::render(
			'getCrystal',
			array(
				'name'      => 'Amethyst',
				'image_url' => 'https://roxyapi.com/img/crystals/amethyst.jpg',
			)
		);
		$this->assertStringContainsString( '<img class="roxyapi-image"', $out );
		$this->assertStringContainsString( 'src="https://roxyapi.com/img/crystals/amethyst.jpg"', $out );
		$this->assertStringContainsString( 'loading="lazy"', $out );
		$this->assertStringContainsString( 'decoding="async"', $out );
		// Image promoted into the header, alt-texted with the title.
		$this->assertStringContainsString( 'alt="Amethyst"', $out );
		// Plain text URL must NOT appear as a fallback.
		$this->assertStringNotContainsString( '>https://roxyapi.com/img/crystals/amethyst.jpg<', $out );
	}

	public function test_image_extension_detected_on_unrecognised_field_name(): void {
		$out = GenericRenderer::render(
			'getX',
			array(
				'name'           => 'Card',
				'illustration'   => 'https://example.com/foo.webp',
			)
		);
		$this->assertStringContainsString( '<img class="roxyapi-image" src="https://example.com/foo.webp"', $out );
	}

	public function test_non_image_url_renders_as_link(): void {
		$out = GenericRenderer::render(
			'getX',
			array(
				'name'    => 'Library',
				'website' => 'https://example.com/docs',
			)
		);
		$this->assertStringContainsString( '<a class="roxyapi-link" href="https://example.com/docs"', $out );
		$this->assertStringContainsString( 'rel="noopener noreferrer"', $out );
		$this->assertStringContainsString( 'target="_blank"', $out );
	}

	public function test_affirmation_renders_as_blockquote(): void {
		$out = GenericRenderer::render(
			'getCrystal',
			array(
				'name'        => 'Lepidolite',
				'affirmation' => 'I welcome awareness and allow it to flow through me.',
			)
		);
		$this->assertStringContainsString( '<blockquote class="roxyapi-quote">I welcome awareness', $out );
		// Affirmation must NOT also appear as a regular dt/dd row.
		$this->assertStringNotContainsString( '<dt>Affirmation</dt>', $out );
	}

	public function test_table_cell_list_of_scalars_joined_without_indices(): void {
		$out = GenericRenderer::render(
			'getX',
			array(
				'crystals' => array(
					array(
						'name'    => 'Amethyst',
						'chakras' => array( 'Third Eye', 'Crown' ),
					),
					array(
						'name'    => 'Citrine',
						'chakras' => array( 'Sacral', 'Solar Plexus' ),
					),
				),
			)
		);
		$this->assertStringContainsString( '<td>Third Eye, Crown</td>', $out );
		$this->assertStringContainsString( '<td>Sacral, Solar Plexus</td>', $out );
		// Indexed-key dump must NOT survive.
		$this->assertStringNotContainsString( '0:', $out );
		$this->assertStringNotContainsString( '1:', $out );
	}

	public function test_empty_section_does_not_render_header(): void {
		$out = GenericRenderer::render(
			'getX',
			array(
				'name'     => 'Crystal',
				'keywords' => array(),
			)
		);
		// Empty "Keywords" array should not produce a section heading at all.
		$this->assertStringNotContainsString( '>Keywords<', $out );
	}

	public function test_list_with_only_empty_strings_does_not_render(): void {
		$out = GenericRenderer::render(
			'getX',
			array(
				'name'     => 'Crystal',
				'keywords' => array( '', '', '' ),
			)
		);
		// The list filters out empty strings; with nothing left the section is suppressed.
		$this->assertStringNotContainsString( '>Keywords<', $out );
		$this->assertStringNotContainsString( '<ul class="roxyapi-list">', $out );
	}

	public function test_dream_symbol_shape_renders_compact_card(): void {
		// Mirrors the actual shape returned by getDailyDreamSymbol so we lock
		// the redesigned layout against the screenshot bug report.
		$data = array(
			'date'          => '2026-04-26',
			'seed'          => '2026-04-26',
			'symbol'        => array(
				'id'      => 'pumpkin',
				'name'    => 'Pumpkin',
				'letter'  => 'p',
				'meaning' => 'Pumpkins emerge in your dream to show your resilience.',
			),
			'daily_message' => 'Your dream symbol for 2026-04-26: Pumpkin. Pumpkins emerge.',
		);
		$out  = GenericRenderer::render( 'getDailyDreamSymbol', $data );

		// The seed never surfaces.
		$this->assertStringNotContainsString( 'Seed', $out );
		// Date appears once, in the meta strip.
		$this->assertSame( 1, substr_count( $out, 'Date: 2026-04-26' ) );
		// Daily message becomes the lede.
		$this->assertStringContainsString( '<p class="roxyapi-card-lede">Your dream symbol for 2026-04-26', $out );
		// Symbol renders as a section with its own h4 heading.
		$this->assertStringContainsString( '<h4 class="roxyapi-section-title">Symbol</h4>', $out );
		// Symbol name surfaces as a nested h4 title (depth > 0).
		$this->assertStringContainsString( '<h4 class="roxyapi-card-title">Pumpkin</h4>', $out );
		// Pumpkin id is suppressed because name is present.
		$this->assertStringNotContainsString( '>pumpkin<', $out );
	}
}
