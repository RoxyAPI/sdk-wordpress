<?php
/**
 * The reserved shortcode attribute CONTRACT, for whatever attributes it holds.
 *
 * A reserved attribute is a display control every reading shortcode carries
 * regardless of the endpoint it wraps. The generator appends the whole set to
 * every generated shortcode from one `RESERVED_ATTS` array, so those cannot
 * drift from each other. Everything NOT covered by that loop can, and by the
 * time anyone looked, three separate places had:
 *
 * 1. `[roxy_horoscope]`, the one hero written by hand, never gained
 *    `hide_sections` when 1.11.1 added it everywhere else.
 * 2. `FormRenderer` passed NEITHER attribute, so every hero honoured both in
 *    static mode and ignored both in form mode, from the day `hide_readings`
 *    shipped.
 * 3. The three form-only heroes declared an empty `DEFAULTS`, so
 *    `shortcode_atts()` discarded both on `[roxy_synastry]`,
 *    `[roxy_gun_milan]` and `[roxy_compatibility]`.
 *
 * **Every one of those passed the whole suite**, because each test file covered
 * one attribute on one path, and the gap was always some other attribute or
 * some other path. So nothing here names an attribute and nothing here names a
 * path as special. The set is read out of the generator, and every assertion is
 * a sweep: over every registered hero, over every reserved attribute, over the
 * static path and the form path. A third reserved attribute is covered the
 * moment it is declared, rather than the day someone remembers to write its
 * tests.
 *
 * What a given attribute MEANS is not this file's business: `none`, `1`,
 * `inherit` and the rest are asserted in the feature file that owns them.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Api\Cache;
use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Shortcodes\Registrar;
use RoxyAPI\Support\ComponentRenderer;
use RoxyAPI\Support\FormRouter;

class Test_Reserved_Atts extends Mock_Http_TestCase {

	/** Opaque PRG token for the seeded form result. Any 32 chars the router will hash. */
	private const FORM_TOKEN = 'abcdefghijklmnopqrstuvwxyz012345';

	/**
	 * One representative value per reserved attribute, and the attribute shape
	 * it must produce on the element. Hand-maintained because the attributes do
	 * not share a value grammar: one is a boolean, one is a comma-separated
	 * list, and a future one need be neither. {@see test_every_reserved_attribute_has_a_sample}
	 * fails when this falls behind the generator, so it cannot go quietly stale.
	 *
	 * @var array<string, array{value: string, expect: string}>
	 */
	private const SAMPLES = array(
		'hide_readings' => array(
			'value'  => '1',
			'expect' => 'hide-readings',
		),
		'hide_sections' => array(
			'value'  => 'key-dates',
			'expect' => 'hide-sections="key-dates"',
		),
	);

	public function setUp(): void {
		parent::setUp();
		delete_option( SettingsPage::OPTION_NAME );
		Cache::flush_all();
	}

	public function tearDown(): void {
		delete_option( SettingsPage::OPTION_NAME );
		Cache::flush_all();
		unset( $_GET['roxyapi_r'] );
		parent::tearDown();
	}

	/**
	 * The generator's `RESERVED_ATTS` array, read out of the source rather than
	 * restated, so this file cannot be the thing that goes stale.
	 *
	 * @return array<string, string> Attribute name => default value, in declared order.
	 */
	private function reserved_atts(): array {
		$source = file_get_contents( dirname( __DIR__, 2 ) . '/bin/generate.mjs' );
		$this->assertIsString( $source, 'bin/generate.mjs must be readable.' );

		$matched = preg_match( '/const RESERVED_ATTS = \[(.*?)\];/s', (string) $source, $m );
		$this->assertSame( 1, $matched, 'bin/generate.mjs must declare a RESERVED_ATTS array.' );

		preg_match_all(
			"/\{\s*att:\s*'([a-z_]+)',\s*default:\s*'([a-z]+)'\s*\}/",
			$m[1],
			$found,
			PREG_SET_ORDER
		);
		$atts = array();
		foreach ( $found as $entry ) {
			$atts[ $entry[1] ] = $entry[2];
		}

		$this->assertNotEmpty( $atts, 'RESERVED_ATTS must parse into at least one attribute.' );
		return $atts;
	}

	/**
	 * The sweeps below are only as complete as this file's sample values, so a
	 * reserved attribute with no sample would pass every test by not being
	 * tested. Fail loudly instead.
	 */
	public function test_every_reserved_attribute_has_a_sample(): void {
		$this->assertSame(
			array_keys( $this->reserved_atts() ),
			array_keys( self::SAMPLES ),
			'A reserved attribute was added or removed in bin/generate.mjs. Add its sample ' .
			'value here, and update the readme FAQ, the WordPress integration guide and ' .
			'docs/ecosystem/wordpress-plugin.md, which document this set to site owners.'
		);
	}

	/**
	 * Every hero declares every reserved attribute, generated or hand-written.
	 * A hero missing one silently drops it: `shortcode_atts()` keeps only the
	 * keys the shortcode declares, so a site owner types the attribute, sees no
	 * error, and gets no effect.
	 */
	public function test_every_rendering_hero_declares_every_reserved_attribute(): void {
		$reserved = $this->reserved_atts();
		$checked  = 0;

		foreach ( Registrar::HERO_SHORTCODES as $tag => $class ) {
			$defaults = $class::DEFAULTS;

			// **No exemption for form-only heroes.** They used to declare an
			// empty DEFAULTS on the reasoning that they take no inputs, which
			// is true and beside the point: a reserved attribute is not an
			// input, and `shortcode_atts()` drops whatever is not declared. So
			// `[roxy_synastry hide_readings="1"]` was discarded on the tag a
			// practitioner is most likely to put under their own copy.
			foreach ( $reserved as $att => $default ) {
				$this->assertArrayHasKey(
					$att,
					$defaults,
					"[{$tag}] must declare a {$att} attribute. Generated heroes get it from " .
					'bin/generate.mjs; a hand-written one has to declare it itself.'
				);
				$this->assertSame(
					$default,
					$defaults[ $att ],
					"[{$tag}] must default {$att} to {$default} so the site setting decides."
				);
			}
			++$checked;
		}

		$this->assertSame(
			count( Registrar::HERO_SHORTCODES ),
			$checked,
			'Every registered hero must be covered, with no exemptions.'
		);
	}

	/**
	 * The generator's list and the PHP list are two views of one fact, so this
	 * is the test that spans them. `ComponentRenderer::render_atts()` reads the
	 * PHP constant to decide what to forward, and the generator reads its own to
	 * decide what to declare: if they disagree, shortcodes accept an attribute
	 * that the renderer then ignores, which is silent by construction.
	 */
	public function test_the_php_reserved_list_matches_the_generator(): void {
		$this->assertSame(
			array_keys( $this->reserved_atts() ),
			ComponentRenderer::RESERVED_ATTS,
			'bin/generate.mjs and ComponentRenderer::RESERVED_ATTS disagree, in name or in ORDER. ' .
			'Order matters: render_atts() maps this list positionally onto render().'
		);
	}

	/**
	 * Declaring the attribute is half of it. A hero also has to FORWARD each one
	 * to `ComponentRenderer::render()`, which a `DEFAULTS` assertion cannot see:
	 * 1.11.1 would have passed the sweep above for `hide_readings` while
	 * dropping `hide_sections` one call later. Every generated hero derives that
	 * call from the same array, so the hand-written hero is the only place the
	 * two can disagree, and it is the one driven here.
	 */
	public function test_the_hand_written_hero_forwards_every_reserved_attribute(): void {
		foreach ( array_keys( $this->reserved_atts() ) as $att ) {
			$sample               = self::SAMPLES[ $att ];
			$this->mock_responses = array( '/astrology/horoscope/' => $this->horoscope_data() );

			Cache::flush_all();
			$set = $this->element_attrs_of(
				do_shortcode( sprintf( '[roxy_horoscope sign="leo" %s="%s"]', $att, $sample['value'] ) )
			);
			$this->assertStringContainsString(
				$sample['expect'],
				$set,
				"[roxy_horoscope] dropped {$att}. Thread it through render(), handle_submission() and render_result()."
			);

			Cache::flush_all();
			$unset = $this->element_attrs_of( do_shortcode( '[roxy_horoscope sign="leo"]' ) );
			$this->assertStringNotContainsString(
				$sample['expect'],
				$unset,
				"[roxy_horoscope] emits {$att} with no attribute and no setting, so the attribute is not what produced it."
			);
		}
	}

	/**
	 * The site setting still reaches a placement that says nothing, for every
	 * reserved attribute. This is the half that kept working when the
	 * per-placement half was broken, which is exactly why the bug stayed
	 * invisible: the feature looked fine until someone tried to override it.
	 */
	public function test_the_site_setting_reaches_the_hand_written_hero_for_every_reserved_attribute(): void {
		foreach ( array_keys( $this->reserved_atts() ) as $att ) {
			$sample = self::SAMPLES[ $att ];
			update_option( SettingsPage::OPTION_NAME, array( $att => $sample['value'] ) );
			$this->mock_responses = array( '/astrology/horoscope/' => $this->horoscope_data() );

			Cache::flush_all();
			$out = $this->element_attrs_of( do_shortcode( '[roxy_horoscope sign="leo"]' ) );
			$this->assertStringContainsString(
				$sample['expect'],
				$out,
				"The {$att} site setting must reach [roxy_horoscope] when the placement says nothing."
			);

			delete_option( SettingsPage::OPTION_NAME );
		}
	}

	/**
	 * **Form mode is a render path too, and it is the one that got missed.**
	 * A hero with its required inputs absent draws a visitor form, and the
	 * result of that submission renders through `FormRenderer`, not through the
	 * hero's own static branch. A site owner who writes
	 * `[roxy_natal_chart hide_readings="1"]` means it for whatever that
	 * placement ends up rendering, so the reserved attributes have to survive
	 * the PRG round trip the same as they survive a static render.
	 *
	 * Driven through a GENERATED hero on purpose: the static path is derived and
	 * safe, but the call into `FormRenderer` is emitted with no attributes to
	 * carry, so this is the seam where a reserved attribute silently stops.
	 */
	public function test_form_mode_carries_every_reserved_attribute_through_the_result(): void {
		foreach ( array_keys( $this->reserved_atts() ) as $att ) {
			$sample = self::SAMPLES[ $att ];
			// Keyed by the form's own `operation_id`, which is NOT the operationId
			// it renders with: `natalChart` posts, `generateNatalChart` renders.
			$this->store_form_result( 'natalChart', $this->chart_data() );

			// No birth details, so the hero renders its visitor form, with the
			// stored result from the redirect above it.
			$out = do_shortcode( sprintf( '[roxy_natal_chart %s="%s"]', $att, $sample['value'] ) );

			$this->assertStringContainsString(
				'roxyapi-form',
				$out,
				'This placement must be in form mode, or the test is proving nothing.'
			);
			$this->assertStringContainsString(
				$sample['expect'],
				$this->element_attrs_of( $out ),
				"A submitted form dropped {$att}. FormRenderer has to receive the placement attributes and forward them."
			);
		}
	}

	/**
	 * Seed a form result the way the POST handler does, through the router's own
	 * key derivation, and put the request on the GET side of the PRG redirect.
	 *
	 * @param array<string, mixed> $result
	 */
	private function store_form_result( string $form_id, array $result ): void {
		$key = new \ReflectionMethod( FormRouter::class, 'transient_key' );
		$key->setAccessible( true );
		set_transient( (string) $key->invoke( null, $form_id, self::FORM_TOKEN ), array( 'result' => $result ), 300 );
		$_GET['roxyapi_r'] = self::FORM_TOKEN;
	}

	/** Response shaped like a natal chart, with a readings block and a named section. */
	private function chart_data(): array {
		return array(
			'planets'  => array(
				array(
					'name'           => 'Sun',
					'sign'           => 'Taurus',
					'degree'         => 24.65,
					'interpretation' => array( 'summary' => 'Your Sun in Taurus reveals dependable energy.' ),
				),
			),
			'patterns' => array(
				array( 'name' => 'T-Square', 'element' => 'Fixed', 'description' => 'A T-Square drives you.' ),
			),
		);
	}

	/**
	 * The opening tag of the emitted custom element, which is where the resolved
	 * attributes land.
	 */
	private function element_attrs_of( string $html ): string {
		$start = strpos( $html, '<roxy-' );
		$this->assertIsInt( $start, 'The render must emit a custom element.' );
		$end = strpos( $html, '>', (int) $start );
		$this->assertIsInt( $end, 'The element tag must close.' );
		return substr( $html, (int) $start, (int) $end - (int) $start + 1 );
	}

	/**
	 * Response shaped like a daily horoscope: prose plus the named blocks
	 * `roxy-horoscope-card` exposes as parts.
	 *
	 * @return array<string, mixed>
	 */
	private function horoscope_data(): array {
		return array(
			'sign'     => 'Leo',
			'overview' => 'A steady day for putting one thing down and picking another up.',
			'keyDates' => array( '2026-08-14', '2026-08-19' ),
			'outlook'  => 'Momentum builds through the week.',
		);
	}
}
