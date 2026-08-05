<?php
/**
 * `lang` must reach the API on the QUERY STRING, on POST as well as GET.
 *
 * Every operation that accepts a language declares it `in: query` in the spec, POST included.
 * The client used to leave it in the JSON body for POST, where the API ignores it SILENTLY: the
 * reading came back in English with a 200, so no error, no log line, and nothing in the admin
 * hinted at a failure. The blast radius was 120 of 175 operations and 13 of the 17 featured
 * readings (natal chart, kundli, panchang, synastry, numerology, tarot, biorhythm), which is to
 * say both the Reading language setting and the site-locale fallback did nothing on the readings
 * customers actually place. The four GET heroes translated correctly and masked it. Found from a
 * Spanish-site customer report on 2026-08-05.
 *
 * These assert on the URL and body the client actually builds, because that is the exact seam
 * where the bug lived: a test that only checked "was lang injected into the payload" passed
 * throughout.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Admin\SettingsPage;
use RoxyAPI\Api\Cache;

class Test_Post_Language_Query extends \WP_UnitTestCase {

	/** Last request the mock intercepted, as [url, args]. */
	private array $seen = array();

	public function setUp(): void {
		parent::setUp();
		delete_option( SettingsPage::OPTION_NAME );
		Cache::flush_all();
		wp_cache_flush();
		$this->seen = array();
		add_filter( 'pre_http_request', array( $this, 'capture' ), 10, 3 );
	}

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'capture' ), 10 );
		delete_option( SettingsPage::OPTION_NAME );
		Cache::flush_all();
		wp_cache_flush();
		parent::tearDown();
	}

	public function capture( $preempt, $args, $url ) {
		$this->seen = array( $url, $args );
		return array(
			'headers'  => array(),
			'body'     => wp_json_encode( array( 'ok' => true ) ),
			'response' => array( 'code' => 200, 'message' => 'OK' ),
			'cookies'  => array(),
			'filename' => null,
		);
	}

	private function set_language( string $lang ): void {
		update_option( SettingsPage::OPTION_NAME, array( 'display_language' => $lang ) );
	}

	/** Decoded JSON body of the captured request. */
	private function body(): array {
		$raw = $this->seen[1]['body'] ?? '{}';
		return json_decode( (string) $raw, true ) ?: array();
	}

	public function test_post_sends_language_on_the_query_string_not_the_body(): void {
		$this->set_language( 'es' );
		\RoxyAPI\Generated\Client::generateNatalChart(
			array(
				'date'      => '1990-05-15',
				'time'      => '14:30',
				'latitude'  => -34.6037,
				'longitude' => -58.3816,
				'timezone'  => -3,
			)
		);

		$this->assertStringContainsString( 'lang=es', $this->seen[0], 'POST must carry lang in the URL.' );
		$this->assertArrayNotHasKey( 'lang', $this->body(), 'lang in the JSON body is ignored by the API.' );
	}

	public function test_post_body_still_carries_the_reading_fields(): void {
		// Lifting lang out of the payload must not disturb anything else in it.
		$this->set_language( 'de' );
		\RoxyAPI\Generated\Client::generateNatalChart(
			array(
				'date'      => '1990-05-15',
				'time'      => '14:30',
				'latitude'  => -34.6037,
				'longitude' => -58.3816,
				'timezone'  => -3,
			)
		);

		$body = $this->body();
		$this->assertSame( '1990-05-15', $body['date'] ?? null );
		$this->assertSame( -34.6037, $body['latitude'] ?? null );
		$this->assertStringContainsString( 'lang=de', $this->seen[0] );
	}

	public function test_no_language_configured_adds_no_query_parameter(): void {
		// An English site must not gain a stray `lang=` on every request.
		$this->set_language( '' );
		add_filter( 'locale', static fn() => 'en_US' );

		\RoxyAPI\Generated\Client::generateNatalChart(
			array(
				'date'      => '1990-05-15',
				'time'      => '14:30',
				'latitude'  => -34.6037,
				'longitude' => -58.3816,
				'timezone'  => -3,
			)
		);

		$this->assertStringNotContainsString( 'lang=', $this->seen[0] );
		$this->assertArrayNotHasKey( 'lang', $this->body() );
	}

	public function test_get_operations_keep_sending_language_in_the_query(): void {
		// The GET path was always correct and must stay that way.
		$this->set_language( 'fr' );
		\RoxyAPI\Generated\Client::getDailyHoroscope( 'leo' );

		$this->assertStringContainsString( 'lang=fr', $this->seen[0] );
	}
}
