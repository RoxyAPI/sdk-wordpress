<?php
/**
 * Tests for the GenericRenderer used by auto-generated shortcodes.
 *
 * The renderer emits a RoxyAPI card layout: header (title + meta), lede paragraph,
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
		$this->assertStringContainsString( 'Date: ', $out );
		$this->assertStringNotContainsString( 'Date: 2026-04-26', $out );
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

	public function test_long_table_stays_visible_in_a_named_scroll_region(): void {
		$rows = array();
		for ( $i = 0; $i < 13; $i++ ) {
			$rows[] = array(
				'name'  => 'Item ' . $i,
				'value' => $i,
			);
		}
		$out = GenericRenderer::render( 'getX', array( 'events' => $rows ) );
		$this->assertStringContainsString( '<details class="roxyapi-details" open><summary>Events</summary>', $out );
		$this->assertStringContainsString( 'class="roxyapi-data-scroll long" tabindex="0" role="region" aria-label="Events"', $out );
		$this->assertSame( 1, substr_count( $out, '<details' ) );
		$this->assertStringContainsString( '<caption>13 rows</caption>', $out );
		$this->assertSame( 13, substr_count( $out, '<td>Item ' ) );

		array_pop( $rows );
		$short = GenericRenderer::render( 'getX', array( 'events' => $rows ) );
		$this->assertStringContainsString( '<details class="roxyapi-details" open><summary>Events</summary>', $short );
		$this->assertStringContainsString( 'class="roxyapi-data-scroll" tabindex="0"', $short );
		$this->assertStringNotContainsString( 'roxyapi-data-scroll long', $short );
		$this->assertStringNotContainsString( '<caption>', $short );
	}

	public function test_long_table_escapes_its_heading_label_and_cells(): void {
		$rows = array_fill( 0, 13, array( 'name' => '<script>alert(1)</script>' ) );
		$out  = GenericRenderer::render( 'getX', array( 'events"<img src=x>' => $rows ) );
		$this->assertStringNotContainsString( '<img', $out );
		$this->assertStringNotContainsString( '<script', $out );
		$this->assertStringContainsString( '&quot;', $out );
		$this->assertStringContainsString( '&lt;img', $out );
		$this->assertSame( 13, substr_count( $out, '&lt;script&gt;alert(1)&lt;/script&gt;' ) );
	}

	public function test_compact_root_records_share_the_table_policy(): void {
		$out = GenericRenderer::render(
			'getX',
			array(
				array( 'name' => 'First item', 'description' => 'First reading', 'element' => 'fire' ),
				array( 'name' => 'Second item', 'description' => 'Second reading', 'element' => 'water' ),
			)
		);
		$this->assertStringContainsString( '<table class="roxyapi-table">', $out );
		$this->assertStringContainsString( '<td>First item</td>', $out );
		$this->assertStringContainsString( '<td>Second reading</td>', $out );
		$this->assertStringNotContainsString( 'roxyapi-section-title', $out );
		$this->assertStringNotContainsString( '<details', $out );
		$this->assertStringContainsString( 'class="roxyapi-data-scroll" tabindex="0"', $out );
	}

	public function test_root_scalar_list_uses_the_existing_escaped_list_render(): void {
		$out = GenericRenderer::render( 'getX', array( 'Fire', '<img src=x>', 7 ) );
		$this->assertStringContainsString( '<ul class="roxyapi-list"><li>Fire</li><li>&lt;img src=x&gt;</li><li>7</li></ul>', $out );
		$this->assertStringNotContainsString( '<dl', $out );
	}

	public function test_filtering_root_list_entries_preserves_list_identity(): void {
		$out = GenericRenderer::render(
			'getX',
			array(
				array( 'description' => 'Only prose' ),
				array( 'name' => 'Kept item', 'description' => 'Hidden prose' ),
			),
			false,
			true
		);
		$this->assertStringContainsString( '<div class="roxyapi-records" role="list"><div class="roxyapi-record" role="listitem"><header', $out );
		$this->assertStringContainsString( '>Kept item</h4>', $out );
		$this->assertStringNotContainsString( 'prose', $out );
		$this->assertStringNotContainsString( 'roxyapi-section-title', $out );
	}

	public function test_long_root_list_stays_visible_and_retains_every_record(): void {
		$items = array();
		for ( $i = 0; $i < 13; $i++ ) {
			$items[] = array( 'name' => 'Item ' . $i, 'description' => 'Reading ' . $i, 'element' => 'fire', 'remedies' => array( 'mantra' => 'Chant' ) );
		}
		$out = GenericRenderer::render( 'getX', $items );
		$this->assertStringContainsString( '<p class="roxyapi-card-meta">13 rows</p><div class="roxyapi-records" role="list">', $out );
		$this->assertSame( 13, substr_count( $out, 'class="roxyapi-card-title"' ) );
		$this->assertStringContainsString( 'Reading 12', $out );
		$this->assertStringNotContainsString( 'roxyapi-data-scroll', $out );
		$this->assertSame( 13, substr_count( $out, 'role="listitem"' ) );
		$this->assertStringNotContainsString( '<details class="roxyapi-details" open>', $out );

		array_pop( $items );
		$short = GenericRenderer::render( 'getX', $items );
		$this->assertStringNotContainsString( 'roxyapi-data-scroll', $short );
	}

	public function test_compact_table_preserves_nested_planet_positions_and_house_lists(): void {
		$row = array(
			'datetime' => '2025-01-15T00:00:00',
			'planets' => array( 'Sun' => array( 'longitude' => 270.7362, 'sign' => 'Capricorn' ) ),
			'significators' => array( array( 'planet' => 'Jupiter', 'signifies' => array( 3, 1 ) ) ),
		);
		$out = GenericRenderer::render( 'getX', array( 'intervals' => array( $row, $row ) ) );
		$this->assertStringContainsString( '<table class="roxyapi-table">', $out );
		$this->assertStringContainsString( '<summary>Sun</summary>', $out );
		$this->assertStringContainsString( '<dt>Longitude</dt><dd>270.74</dd>', $out );
		$this->assertStringContainsString( 'Capricorn', $out );
		$this->assertStringContainsString( 'Jupiter', $out );
		$this->assertStringContainsString( '<li>3</li><li>1</li>', $out );
	}

	public function test_compact_table_preserves_array_valued_house_levels(): void {
		$row = array(
			'datetime' => '2026-02-03T00:00:00',
			'moonSignLordSignifies' => array( 'L1' => array( 11, 6 ), 'L4' => array( 5, 6 ) ),
		);
		$out = GenericRenderer::render( 'getX', array( 'intervals' => array( $row, $row ) ) );
		$this->assertStringContainsString( '<table class="roxyapi-table">', $out );
		$this->assertStringContainsString( '<li>11</li><li>6</li>', $out );
		$this->assertStringContainsString( '<li>5</li><li>6</li>', $out );
	}

	public function test_wide_named_records_retain_short_prose_and_nested_details(): void {
		$rows = array();
		for ( $i = 0; $i < 13; $i++ ) {
			$rows[] = array(
				'name' => 'Item ' . $i,
				'deity' => 'Agni',
				'characteristics' => 'Short prose',
				'remedies' => array( 'mantra' => 'Chant', 'color' => 'Red' ),
			);
		}
		$out = GenericRenderer::render( 'getX', array( 'items' => $rows ) );
		$this->assertStringNotContainsString( '<table', $out );
		$this->assertStringContainsString( '<details class="roxyapi-details" open><summary>Items</summary>', $out );
		$this->assertSame( 13, substr_count( $out, 'class="roxyapi-card-title"' ) );
		$this->assertStringContainsString( '<p class="roxyapi-card-meta">13 rows</p>', $out );
		$this->assertStringContainsString( '<details class="roxyapi-details"><summary>Remedies</summary>', $out );
		$this->assertStringContainsString( 'Chant', $out );
		$this->assertStringContainsString( 'Item 12', $out );
	}

	public function test_wide_paragraph_records_retain_all_visible_fields(): void {
		$row = array( 'name' => 'First', 'deity' => 'Agni', 'symbol' => 'Fire', 'characteristics' => str_repeat( 'Long prose. ', 9 ) );
		$out = GenericRenderer::render( 'getX', array( 'items' => array( $row, $row ) ) );
		$this->assertStringNotContainsString( '<table', $out );
		$this->assertStringContainsString( str_repeat( 'Long prose. ', 8 ), $out );
		$this->assertStringContainsString( '<dt>Symbol</dt><dd>Fire</dd>', $out );
	}

	public function test_deep_named_tables_start_closed_with_every_row_retained(): void {
		foreach ( array( 2, 39 ) as $count ) {
			$rows = array();
			for ( $i = 0; $i < $count; $i++ ) {
				$rows[] = array( 'planet' => 'Sun', 'longitude' => $i );
			}
			$out = GenericRenderer::render( 'getX', array( 'chart' => array( 'events' => $rows ) ) );
			$this->assertStringContainsString( '<details class="roxyapi-details"><summary>Events</summary>', $out );
			$this->assertStringNotContainsString( '<details class="roxyapi-details" open>', $out );
			$this->assertSame( $count, substr_count( $out, '<td>Sun</td>' ) );
			if ( $count > 12 ) {
				$this->assertStringContainsString( '<caption>39 rows</caption>', $out );
				$this->assertStringContainsString( 'roxyapi-data-scroll', $out );
			}
		}
	}

	public function test_paragraph_boundary_counts_unicode_characters(): void {
		foreach ( array( 80, 81 ) as $length ) {
			$row = array( 'name' => 'First', 'deity' => 'Agni', 'symbol' => 'Fire', 'characteristics' => str_repeat( '🌟', $length ) );
			$out = GenericRenderer::render( 'getX', array( 'items' => array( $row, $row ) ) );
			if ( $length === 80 ) {
				$this->assertStringContainsString( '<table', $out );
			} else {
				$this->assertStringNotContainsString( '<table', $out );
			}
		}
	}

	public function test_long_compact_root_records_have_one_scroll_frame_and_caption(): void {
		$rows = array();
		for ( $i = 0; $i < 27; $i++ ) {
			$rows[] = array( 'name' => 'Item ' . $i, 'value' => $i );
		}
		$out = GenericRenderer::render( 'getX', $rows );
		$this->assertSame( 1, substr_count( $out, '<table' ) );
		$this->assertSame( 1, substr_count( $out, 'roxyapi-data-scroll long' ) );
		$this->assertStringContainsString( '<caption>27 rows</caption>', $out );
		$this->assertStringContainsString( '<td>Item 26</td>', $out );
		$this->assertStringNotContainsString( '<details', $out );
		$this->assertStringNotContainsString( 'roxyapi-section-title', $out );
	}

	public function test_date_display_uses_the_locale_without_shifting_naive_clocks(): void {
		$previous_zone = get_option( 'timezone_string' );
		$previous_date = get_option( 'date_format' );
		$previous_time = get_option( 'time_format' );
		$locale = 'en_US';
		$filter = static function () use ( &$locale ) { return $locale; };
		add_filter( 'determine_locale', $filter );
		update_option( 'timezone_string', 'America/New_York' );
		update_option( 'date_format', 'Y-m-d' );
		update_option( 'time_format', 'H:i' );
		try {
			$cases = array(
				array( 'en_US', '2026-03-08T02:30:00', 'Mar 8, 2026, 2:30 AM', '2026-03-08 02:30' ),
				array( 'de_DE', '2026-02-03T14:30:00', '03.02.2026, 14:30', '2026-02-03 14:30' ),
				array( 'en_US', '2026-02-03', 'Feb 3, 2026', '2026-02-03' ),
				array( 'en_US', '2026-02-03T00:00:00Z', 'Feb 2, 2026, 7:00 PM', '2026-02-02 19:00' ),
				array( 'en_US', '2026-02-30T00:00:00', '2026-02-30T00:00:00', '2026-02-30T00:00:00' ),
			);
			foreach ( $cases as $case ) {
				$locale = $case[0];
				$html = GenericRenderer::render( 'getX', array( 'datetime' => $case[1], 'startDatetime' => $case[1] ) );
				$text = preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $html ) );
				$supported = class_exists( \IntlDateFormatter::class ) && \Locale::getPrimaryLanguage( (string) ( new \IntlDateFormatter( $locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT ) )->getLocale() ) === \Locale::getPrimaryLanguage( $locale );
				$expected = $supported ? $case[2] : $case[3];
				$this->assertSame( 2, substr_count( $text, $expected ), $case[0] . ' ' . $case[1] . ': ' . $text );
			}
			$this->assertStringContainsString( 'Time: 14:30', GenericRenderer::render( 'getX', array( 'time' => '14:30' ) ) );
		} finally {
			remove_filter( 'determine_locale', $filter );
			update_option( 'timezone_string', $previous_zone );
			update_option( 'date_format', $previous_date );
			update_option( 'time_format', $previous_time );
		}
	}

	public function test_offset_instants_use_numeric_site_timezones(): void {
		$previous = array();
		foreach ( array( 'timezone_string', 'gmt_offset', 'date_format', 'time_format' ) as $option ) {
			$previous[ $option ] = get_option( $option );
		}
		$filter = static fn() => 'en_US';
		add_filter( 'determine_locale', $filter );
		update_option( 'timezone_string', '' );
		update_option( 'date_format', 'Y-m-d' );
		update_option( 'time_format', 'H:i' );
		try {
			foreach ( array( array( 5.5, 'Feb 3, 2026, 5:30 AM', '2026-02-03 05:30' ), array( -4, 'Feb 2, 2026, 8:00 PM', '2026-02-02 20:00' ) ) as $case ) {
				update_option( 'gmt_offset', $case[0] );
				foreach ( array( '2026-02-03T00:00:00Z', '2026-02-03T05:30:00+05:30' ) as $instant ) {
					$html = GenericRenderer::render( 'getX', array( 'datetime' => $instant, 'startDatetime' => $instant ) );
					$text = preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $html ) );
					$expected = class_exists( \IntlDateFormatter::class ) ? $case[1] : $case[2];
					$this->assertSame( 2, substr_count( $text, $expected ), (string) $case[0] . ': ' . $text );
				}
			}
		} finally {
			remove_filter( 'determine_locale', $filter );
			foreach ( $previous as $option => $value ) {
				update_option( $option, $value );
			}
		}
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
		$this->assertSame( 1, substr_count( $out, 'Date: ' ) );
		// Daily message becomes the lede.
		$this->assertStringContainsString( '<p class="roxyapi-card-lede">Your dream symbol for 2026-04-26', $out );
		// Symbol renders as a section with its own h4 heading.
		$this->assertStringContainsString( '<h4 class="roxyapi-section-title">Symbol</h4>', $out );
		// Symbol name surfaces as a nested h4 title (depth > 0).
		$this->assertStringContainsString( '<h4 class="roxyapi-card-title">Pumpkin</h4>', $out );
		// Pumpkin id is suppressed because name is present.
		$this->assertStringNotContainsString( '>pumpkin<', $out );
	}

	public function test_card_scalar_field_prefers_localized_value_over_canonical(): void {
		// Regression: before the fold this rendered BOTH "Name" (Sun) and
		// "Name Localized" (Sol) as separate dt/dd rows.
		$out = GenericRenderer::render(
			'getX',
			array(
				'element'          => 'Fire',
				'elementLocalized' => 'Fuego',
			)
		);
		$this->assertStringContainsString( '<dt>Element</dt>', $out );
		$this->assertStringContainsString( '<dd>Fuego</dd>', $out );
		$this->assertStringNotContainsString( 'Element Localized', $out );
		$this->assertStringNotContainsString( '<dd>Fire</dd>', $out );
	}

	public function test_card_title_field_prefers_localized_value(): void {
		$out = GenericRenderer::render(
			'getX',
			array(
				'name'          => 'Sun',
				'nameLocalized' => 'Sol',
			)
		);
		$this->assertStringContainsString( '<h3 class="roxyapi-card-title">Sol</h3>', $out );
		$this->assertStringNotContainsString( 'Name Localized', $out );
		$this->assertStringNotContainsString( '>Sun<', $out );
	}

	public function test_localized_field_with_no_canonical_partner_still_renders(): void {
		// A stray `fooLocalized` with no `foo` beside it must still render,
		// or the renderer silently eats data the response carries.
		$out = GenericRenderer::render(
			'getX',
			array(
				'name'          => 'Pumpkin',
				'omenLocalized' => 'Presagio',
			)
		);
		$this->assertStringContainsString( '<dt>Omen localized</dt>', $out );
		$this->assertStringContainsString( '<dd>Presagio</dd>', $out );
	}

	public function test_fold_runs_before_suppression_so_hidden_field_takes_translation_with_it(): void {
		// `type` is suppressed noise (schema discriminator). Neither the
		// English nor the localized value may leak through as its own row:
		// the fold has to run BEFORE noise-suppression, not after.
		$out = GenericRenderer::render(
			'getX',
			array(
				'name'          => 'Trine',
				'type'          => 'TRINE',
				'typeLocalized' => 'Trigono',
			)
		);
		$this->assertStringNotContainsString( 'Trigono', $out );
		$this->assertStringNotContainsString( 'TRINE', $out );
		$this->assertStringNotContainsString( '<dt>Type</dt>', $out );
		$this->assertStringNotContainsString( 'Type Localized', $out );
	}

	public function test_table_row_localized_fields_fold_into_canonical_columns(): void {
		// Regression: the reported bug. A table built from array_keys() on
		// each row had no idea `nameLocalized` and `signLocalized` were
		// translations, so it grew two extra English-headed columns the day
		// the API began sending them, duplicating data already on the row.
		$data = array(
			'planets' => array(
				array(
					'name'          => 'Sun',
					'nameLocalized' => 'Sol',
					'sign'          => 'Leo',
					'signLocalized' => 'Leon',
				),
				array(
					'name'          => 'Moon',
					'nameLocalized' => 'Luna',
					'sign'          => 'Cancer',
					'signLocalized' => 'Cancer',
				),
			),
		);
		$out = GenericRenderer::render( 'getX', $data );

		// Exactly two columns: the localized twin never gets its own header.
		$this->assertStringContainsString( '<thead><tr><th>Name</th><th>Sign</th></tr></thead>', $out );
		$this->assertStringNotContainsString( 'Name Localized', $out );
		$this->assertStringNotContainsString( 'Sign Localized', $out );
		// Cells carry the READER'S value, not the canonical English one.
		$this->assertStringContainsString( '<td>Sol</td>', $out );
		$this->assertStringContainsString( '<td>Leon</td>', $out );
		$this->assertStringNotContainsString( '<td>Sun</td>', $out );
	}

	public function test_table_row_localized_field_without_partner_still_renders(): void {
		// Collections share suppression while keeping translations with no canonical partner.
		$data = array(
			'items' => array(
				array(
					'name'          => 'Alpha',
					'omenLocalized' => 'Presagio Alpha',
				),
				array(
					'name'          => 'Beta',
					'omenLocalized' => 'Presagio Beta',
				),
			),
		);
		$out = GenericRenderer::render( 'getX', $data );
		$this->assertStringContainsString( '<th>Omen localized</th>', $out );
		$this->assertStringContainsString( '<td>Presagio Alpha</td>', $out );
	}
	public function test_blank_lines_in_a_field_render_as_paragraphs(): void {
		$out = GenericRenderer::render(
			'getMonthlyHoroscope',
			array( 'column' => "Venus turns retrograde on the 3rd.\n\nThe stretch it opens runs through November.\n\nKeep the 25th free." )
		);
		$this->assertStringContainsString( '<dd><p>Venus turns retrograde on the 3rd.</p><p>The stretch it opens runs through November.</p><p>Keep the 25th free.</p></dd>', $out );
	}

	public function test_blank_lines_in_the_lede_render_as_lede_paragraphs(): void {
		$out = GenericRenderer::render( 'getX', array( 'overview' => "First.\n \nSecond." ) );
		$this->assertSame( 2, substr_count( $out, '<p class="roxyapi-card-lede">' ) );
		$this->assertStringContainsString( '<p class="roxyapi-card-lede">Second.</p>', $out );
	}

	public function test_a_single_newline_does_not_split_a_paragraph(): void {
		$out = GenericRenderer::render( 'getX', array( 'note' => "one line\nwrapped" ) );
		$this->assertStringNotContainsString( '<p>', $out );
		$this->assertStringContainsString( "one line\nwrapped", $out );
	}

	public function test_paragraphs_are_escaped(): void {
		$out = GenericRenderer::render( 'getX', array( 'note' => "<b>a</b>\n\n<i>b</i>" ) );
		$this->assertStringContainsString( '<p>&lt;b&gt;a&lt;/b&gt;</p><p>&lt;i&gt;b&lt;/i&gt;</p>', $out );
	}
}
