<?php
/**
 * Which generated blocks the inserter offers.
 *
 * The catalogue is one block per endpoint, and at 154 entries the inserter
 * stopped being a menu: someone looking for a birth chart scrolled past dozens
 * of near-identical rows to find it. Two kinds of block are not a reading and
 * account for that noise. A reference lookup answers with a catalogue meant to
 * fill a dropdown or let a developer discover valid ids, and an interval dump
 * answers with a month of rows meant for a chart.
 *
 * They are hidden, never deregistered, because a saved post references a block
 * by name and removing the type would break content that already uses it. The
 * shortcodes are untouched, so anyone who does want the table on a page still
 * has it.
 *
 * The list lives in `bin/generate.mjs` as `NON_READING_OPERATIONS` and drives
 * `supports.inserter` in every generated block.json. The copy below is a second
 * opinion on purpose: the generated files are overwritten on every run, so
 * without it a slip of the wrist in the generator would hide a reading and
 * nothing would say so.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

class Test_Inserter_Visibility extends \WP_UnitTestCase {

	/** Reference lookups and enumerations. A catalogue, not a reading. */
	private const LOOKUPS = array(
		'get-cities-by-country',
		'get-field-labels',
		'get-random-crystal',
		'get-random-symbols',
		'get-symbol-letter-counts',
		'list-avasthas',
		'list-bagua-sectors',
		'list-countries',
		'list-crystal-colors',
		'list-crystal-planets',
		'list-five-elements',
		'list-flying-stars',
		'list-hexagrams',
		'list-languages',
		'list-nakshatras',
		'list-nine-periods',
		'list-rashis',
		'list-trigrams',
		'list-zodiac-animals',
		'list-zodiac-signs',
		'search-cities',
	);

	/** Interval and month-range dumps. Rows for a chart, not a reading. */
	private const INTERVALS = array(
		'get-ecliptic-crossings',
		'get-kp-planets-interval',
		'get-kp-rasi-changes',
		'get-kp-ruling-interval',
		'get-kp-sublord-changes',
		'get-lunar-aspects',
		'get-monthly-almanac',
		'get-monthly-aspects',
		'get-monthly-ephemeris',
		'get-monthly-parallels',
		'get-monthly-transits',
		'get-monthly-tropical-ephemeris',
	);

	/**
	 * Hidden for a different reason and by a different list
	 * (`ACCOUNT_SCOPED_OPERATIONS`), but it shares the flag, so the exact-match
	 * assertion below has to expect it.
	 */
	private const ACCOUNT_SCOPED = array( 'get-usage-stats' );

	/** Readings that must stay one click away, one per shape of block. */
	private const MUST_STAY = array(
		// A component-backed reading.
		'calculate-soul-urge',
		// A reading with no bound component: it falls back to the generic card
		// and renders fine, which is the whole reason unbound is not the axis.
		'calculate-chaldean',
		// A lookup by id that answers with an interpretation someone reads.
		'get-crystal',
		// The list endpoints that are content rather than a dropdown source.
		'list-cards',
		'list-angel-numbers',
	);

	private static function blocks_dir(): string {
		return dirname( __DIR__, 2 ) . '/blocks/generated';
	}

	/**
	 * Every slug the generator flagged, read back out of the committed block.json.
	 *
	 * @return array<int, string>
	 */
	private function hidden_slugs(): array {
		$hidden = array();
		foreach ( (array) glob( self::blocks_dir() . '/*/block.json' ) as $path ) {
			$block = json_decode( (string) file_get_contents( (string) $path ), true );
			if ( is_array( $block ) && ( $block['supports']['inserter'] ?? true ) === false ) {
				$hidden[] = basename( dirname( (string) $path ) );
			}
		}
		sort( $hidden );
		return $hidden;
	}

	public function test_exactly_the_listed_operations_are_hidden(): void {
		$expected = array_merge( self::LOOKUPS, self::INTERVALS, self::ACCOUNT_SCOPED );
		sort( $expected );

		$this->assertSame(
			$expected,
			$this->hidden_slugs(),
			'The inserter-hidden set drifted from NON_READING_OPERATIONS in bin/generate.mjs.'
		);
	}

	public function test_the_inserter_still_offers_the_readings(): void {
		$hidden  = $this->hidden_slugs();
		$offered = 0;
		foreach ( (array) glob( self::blocks_dir() . '/*/block.json' ) as $path ) {
			$offered++;
		}
		$offered -= count( $hidden );

		// The point of the change is a usable menu, not an empty one.
		$this->assertGreaterThan(
			100,
			$offered,
			'Hiding the non-reading blocks must leave the reading catalogue intact.'
		);
	}

	/**
	 * @dataProvider reading_blocks
	 * @param string $slug Block slug that must stay insertable.
	 */
	public function test_a_reading_block_stays_insertable( string $slug ): void {
		$path = self::blocks_dir() . "/{$slug}/block.json";
		$this->assertFileExists( $path, "{$slug} block.json is missing." );

		$block = json_decode( (string) file_get_contents( $path ), true );
		$this->assertTrue(
			$block['supports']['inserter'] ?? true,
			"{$slug} renders a reading and must stay in the inserter."
		);
	}

	/**
	 * @return array<int, array<int, string>>
	 */
	public function reading_blocks(): array {
		return array_map( static fn( $slug ) => array( $slug ), self::MUST_STAY );
	}

	/**
	 * Hiding a block is an inserter decision, not a capability one. Every hidden
	 * operation keeps its shortcode, so a site owner who wants the raw table on a
	 * page pastes it and gets it.
	 */
	public function test_a_hidden_operation_keeps_its_shortcode(): void {
		foreach ( array( 'listCountries', 'getMonthlyEphemeris' ) as $operation ) {
			$class = '\\RoxyAPI\\Generated\\Shortcodes\\' . ucfirst( $operation );
			$this->assertTrue(
				class_exists( $class ),
				"{$operation} must keep its shortcode class; only the inserter entry is hidden."
			);
		}
	}
}
