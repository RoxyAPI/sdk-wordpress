<?php
/**
 * Every container the plugin puts at the top level of a reading is one the
 * stylesheet paints.
 *
 * A reading lands inside whatever section the site owner built. A container
 * that takes its background from the page and its text colour from the theme
 * reads correctly only while those two agree, and a page builder section with a
 * background of its own breaks exactly that: the text stays the document colour
 * and the background becomes the section. Nothing errors, no gate complains,
 * and the reading is unreadable.
 *
 * `bin/painted-surfaces.json` is the one list of those containers, and it has
 * two enforcers so neither half can drift:
 *
 * 1. `bin/check-frontend-contrast.mjs` renders the real stylesheet in a browser
 *    and proves every listed surface paints both halves and clears AA.
 * 2. This file renders the plugin and proves nothing else reaches the top
 *    level. New chrome therefore cannot ship unmeasured: it fails here until it
 *    is listed, and fails there until it is painted.
 *
 * The sweep is over every registered hero rather than over a chosen few,
 * because the gap is always the path nobody wrote a test for.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Api\Cache;
use RoxyAPI\Generated\Shortcodes\GetMonthlyTropicalEphemeris;
use RoxyAPI\Shortcodes\Registrar;
use RoxyAPI\Support\EphemerisNav;
use RoxyAPI\Support\Templates;

class Test_Painted_Surfaces extends Mock_Http_TestCase {

	/**
	 * Class names allowed at the top level: the painted surfaces plus the
	 * containers that carry no text of their own.
	 *
	 * @var list<string>
	 */
	private array $allowed = array();

	public function setUp(): void {
		parent::setUp();
		delete_option( SettingsPage::OPTION_NAME );
		Cache::flush_all();

		$listed        = $this->listed();
		$this->allowed = array_merge(
			array_keys( $listed['surfaces'] ),
			array_keys( $listed['passthrough'] )
		);

		// The pager rides `do_shortcode_tag`, so it only appears in rendered
		// output once the filters are attached. It is also the container this
		// whole check exists for, so a sweep that never sees it is worthless.
		EphemerisNav::register();
	}

	public function tearDown(): void {
		delete_option( SettingsPage::OPTION_NAME );
		Cache::flush_all();
		parent::tearDown();
	}

	/**
	 * The shared list, read from the same file the browser check reads.
	 *
	 * @return array{surfaces: array<string, string>, passthrough: array<string, string>}
	 */
	private function listed(): array {
		$path = dirname( __DIR__, 2 ) . '/bin/painted-surfaces.json';
		$this->assertFileExists( $path, 'The painted-surface list is what both checks read.' );
		/** @var array{surfaces: array<string, string>, passthrough: array<string, string>} $listed */
		$listed = json_decode( (string) file_get_contents( $path ), true );
		$this->assertIsArray( $listed['surfaces'] ?? null );
		$this->assertIsArray( $listed['passthrough'] ?? null );
		return $listed;
	}

	/**
	 * Assert every element at the top level of a rendered fragment is listed.
	 *
	 * @param string $html    Rendered output.
	 * @param string $context What produced it, named in the failure.
	 * @return int Number of top-level elements checked.
	 */
	private function assert_top_level_listed( string $html, string $context ): int {
		if ( '' === trim( $html ) ) {
			return 0;
		}

		$doc = new \DOMDocument();
		libxml_use_internal_errors( true );
		$doc->loadHTML(
			'<?xml encoding="utf-8" ?><body><div id="roxyapi-root">' . $html . '</div></body>',
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		);
		libxml_clear_errors();

		$xpath = new \DOMXPath( $doc );
		$root  = $xpath->query( '//div[@id="roxyapi-root"]' )->item( 0 );
		$this->assertInstanceOf( \DOMElement::class, $root, "{$context}: output did not parse." );

		$checked = 0;
		foreach ( $root->childNodes as $node ) {
			if ( ! $node instanceof \DOMElement ) {
				continue;
			}
			// Structured data and styles paint nothing and read as nothing.
			if ( in_array( strtolower( $node->tagName ), array( 'script', 'style' ), true ) ) {
				continue;
			}
			$classes = preg_split( '/\s+/', $node->getAttribute( 'class' ), -1, PREG_SPLIT_NO_EMPTY );
			$this->assertNotEmpty(
				array_intersect( (array) $classes, $this->allowed ),
				sprintf(
					'%s emits <%s class="%s"> at the top level of a reading, where nothing of ours is painted behind it. '
						. 'Add it to bin/painted-surfaces.json, under surfaces if it carries text (the stylesheet must then '
						. 'paint its background and its colour) or under passthrough if it does not.',
					$context,
					$node->tagName,
					$node->getAttribute( 'class' )
				)
			);
			++$checked;
		}

		return $checked;
	}

	/**
	 * Every hero with no attributes renders the visitor form, which is the path
	 * a practitioner actually publishes and the one that reaches every hero
	 * without a per-endpoint fixture.
	 */
	public function test_every_hero_renders_only_listed_containers(): void {
		$swept = 0;
		foreach ( array_keys( Registrar::HERO_SHORTCODES ) as $tag ) {
			$this->assert_top_level_listed( do_shortcode( "[{$tag}]" ), "[{$tag}]" );
			++$swept;
		}

		$this->assertSame(
			count( Registrar::HERO_SHORTCODES ),
			$swept,
			'Every registered hero must be swept, with no exemptions.'
		);
	}

	/**
	 * A rendered reading with both trailing lines on. They are emitted beside
	 * the element rather than inside it, so they arrive at the top level and
	 * must bring a surface with them.
	 */
	public function test_a_rendered_reading_and_its_trailing_block(): void {
		update_option(
			SettingsPage::OPTION_NAME,
			array(
				'disclaimer_show'  => 1,
				'attribution_show' => 1,
			)
		);
		$this->mock_responses = array( '/astrology/horoscope/' => $this->horoscope_data() );

		$html = do_shortcode( '[roxy_horoscope sign="leo"]' );

		$this->assertStringContainsString( 'roxyapi-card-disclaimer', $html, 'The disclaimer must be on.' );
		$this->assertStringContainsString( 'roxyapi-credit', $html, 'The credit must be on.' );
		$this->assertGreaterThan( 1, $this->assert_top_level_listed( $html, '[roxy_horoscope]' ) );
	}

	/** The pager the ephemeris appends, which is what this check was written for. */
	public function test_the_ephemeris_pager_is_listed(): void {
		$this->mock_responses = array( '/astrology/planets/monthly' => $this->ephemeris_data() );

		$html = do_shortcode( '[' . GetMonthlyTropicalEphemeris::TAG . ']' );

		$this->assertStringContainsString( 'roxyapi-eph-nav', $html, 'The pager must be in the output, or this sweeps nothing.' );
		$this->assert_top_level_listed( $html, '[' . GetMonthlyTropicalEphemeris::TAG . ']' );
	}

	/** A shortcode that cannot render at all still returns a container. */
	public function test_a_shortcode_level_error_is_listed(): void {
		$this->assert_top_level_listed( Templates::error( 'Something went wrong.' ), 'Templates::error' );
	}

	/**
	 * Sample horoscope payload.
	 *
	 * @return array<string, mixed>
	 */
	private function horoscope_data(): array {
		return array(
			'sign'     => 'Leo',
			'overview' => 'A steady day for putting one thing down and picking another up.',
			'outlook'  => 'Momentum builds through the week.',
		);
	}

	/**
	 * Sample monthly ephemeris payload, trimmed to the shape the renderer needs.
	 *
	 * @return array<string, mixed>
	 */
	private function ephemeris_data(): array {
		return array(
			'year'  => 2026,
			'month' => 8,
			'days'  => array(
				array(
					'date'      => '2026-08-01',
					'positions' => array(
						array(
							'planet'    => 'Sun',
							'longitude' => 129.27,
							'sign'      => 'Leo',
							'degree'    => 9.27,
							'retrograde' => false,
						),
					),
				),
			),
		);
	}
}
