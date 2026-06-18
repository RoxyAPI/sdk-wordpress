<?php
/**
 * The response cache key must vary by the effective display language.
 *
 * The site-wide language is injected into the outgoing request below the cache
 * layer (RoxyAPI\Api\Client::request), so it never appears in the $args the
 * generated client hands to Cache::remember. Before the fix the key ignored it
 * entirely: switching the language kept serving the previously cached reading
 * until each transient expired (up to 30 days for static lists), and a
 * "Match site language" multilingual site collided every locale onto one key.
 * Cache::key now folds in RoxyAPI\Support\Language::resolve(), so each language
 * gets its own entry while an explicit shortcode `lang` attribute stays
 * authoritative.
 *
 * key() is private, so these assertions drive the public Cache::remember()
 * surface with a call counter: a re-fetch (counter increments) proves a cache
 * miss, i.e. a distinct key.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Api\Cache;

class Test_Cache_Language_Key extends \WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		delete_option( SettingsPage::OPTION_NAME );
		Cache::flush_all();
		wp_cache_flush();
	}

	public function tearDown(): void {
		delete_option( SettingsPage::OPTION_NAME );
		Cache::flush_all();
		wp_cache_flush();
		parent::tearDown();
	}

	private function set_language( string $lang ): void {
		update_option( SettingsPage::OPTION_NAME, array( 'display_language' => $lang ) );
	}

	/**
	 * A fetch callback that counts how many times the API would be hit.
	 *
	 * @param int $calls Counter passed by reference.
	 * @return callable
	 */
	private function counting_fetch( int &$calls ): callable {
		return static function () use ( &$calls ) {
			++$calls;
			return array( 'overview' => 'reading-' . $calls );
		};
	}

	public function test_switching_site_language_misses_the_prior_language_cache(): void {
		$calls = 0;
		$fetch = $this->counting_fetch( $calls );

		$this->set_language( 'es' );
		Cache::remember( 'astrology/horoscope/aries/daily', array(), 3600, $fetch );
		Cache::remember( 'astrology/horoscope/aries/daily', array(), 3600, $fetch );
		$this->assertSame( 1, $calls, 'Second render in the same language must hit the cache.' );

		// Same endpoint and args, only the site language changed.
		$this->set_language( 'fr' );
		Cache::remember( 'astrology/horoscope/aries/daily', array(), 3600, $fetch );
		$this->assertSame( 2, $calls, 'Switching the site language must miss the prior-language entry, not serve it stale.' );
	}

	public function test_each_language_keeps_its_own_entry(): void {
		$calls = 0;
		$fetch = $this->counting_fetch( $calls );

		$this->set_language( 'es' );
		Cache::remember( 'astrology/horoscope/aries/daily', array(), 3600, $fetch ); // miss -> 1
		$this->set_language( 'de' );
		Cache::remember( 'astrology/horoscope/aries/daily', array(), 3600, $fetch ); // miss -> 2
		$this->set_language( 'es' );
		Cache::remember( 'astrology/horoscope/aries/daily', array(), 3600, $fetch ); // hit, still 2

		$this->assertSame( 2, $calls, 'Returning to a language must reuse its own cached entry, not re-fetch.' );
	}

	public function test_explicit_lang_arg_is_authoritative_and_isolated(): void {
		$calls = 0;
		$fetch = $this->counting_fetch( $calls );

		// Site says Spanish, but explicit shortcode lang must win and each
		// explicit language is its own entry regardless of the site setting.
		$this->set_language( 'es' );
		Cache::remember( 'astrology/horoscope/aries/weekly', array( 'lang' => 'hi' ), 3600, $fetch ); // miss -> 1
		Cache::remember( 'astrology/horoscope/aries/weekly', array( 'lang' => 'en' ), 3600, $fetch ); // miss -> 2
		Cache::remember( 'astrology/horoscope/aries/weekly', array( 'lang' => 'hi' ), 3600, $fetch ); // hit, still 2

		$this->assertSame( 2, $calls, 'Explicit lang attributes must key independently of the site setting.' );
	}
}
