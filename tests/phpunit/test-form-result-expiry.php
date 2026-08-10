<?php
/**
 * A consumed reading says so, instead of rendering an empty form.
 *
 * A submitted reading is delivered exactly once: the payload carries the birth
 * details the visitor typed, so the transient holding it is deleted as soon as
 * it has been read rather than kept for its whole TTL. That part is deliberate
 * and these tests hold it.
 *
 * What was wrong is what happened next. A refresh, a bookmark or a link shared
 * with a partner re-requests the same `?roxyapi_r=` token, found nothing, and
 * returned `null` — the same answer as "no result was asked for" — so the page
 * rendered a bare form with no explanation. To the person who had a chart on
 * screen a second earlier, that reads as the site having lost it.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Support\FormRouter;

class Test_Form_Result_Expiry extends \WP_UnitTestCase {

	private const FORM_ID = 'generateNatalChart';
	private const TOKEN   = 'abcdefghijklmnopqrstuvwxyz012345';

	public function tearDown(): void {
		unset( $_GET['roxyapi_r'] );
		parent::tearDown();
	}

	/** Store a result the way the POST handler does, via the router's own key derivation. */
	private function store( array $payload ): void {
		$m = new \ReflectionMethod( FormRouter::class, 'transient_key' );
		$m->setAccessible( true );
		set_transient( (string) $m->invoke( null, self::FORM_ID, self::TOKEN ), $payload, 300 );
	}

	public function test_a_stored_result_is_returned_once(): void {
		$this->store( array( 'result' => array( 'sign' => 'Aries' ) ) );
		$_GET['roxyapi_r'] = self::TOKEN;

		$first = FormRouter::consume_result( self::FORM_ID );

		$this->assertIsArray( $first );
		$this->assertSame( array( 'sign' => 'Aries' ), $first['result'] );
		$this->assertArrayNotHasKey( 'expired', $first );
	}

	/** The privacy property: the payload does not survive its own delivery. */
	public function test_the_stored_payload_is_deleted_after_it_is_read(): void {
		$this->store( array( 'result' => array( 'sign' => 'Aries' ) ) );
		$_GET['roxyapi_r'] = self::TOKEN;

		FormRouter::consume_result( self::FORM_ID );
		$second = FormRouter::consume_result( self::FORM_ID );

		$this->assertNotSame( array( 'sign' => 'Aries' ), $second['result'] ?? null, 'A delivered reading must not be re-servable.' );
	}

	/** The fix: a second read is reported as expired, not as silence. */
	public function test_a_second_read_reports_expiry_rather_than_nothing(): void {
		$this->store( array( 'result' => array( 'sign' => 'Aries' ) ) );
		$_GET['roxyapi_r'] = self::TOKEN;

		FormRouter::consume_result( self::FORM_ID );
		$second = FormRouter::consume_result( self::FORM_ID );

		$this->assertIsArray( $second, 'A consumed token must not return null; the renderer cannot tell that apart from no request.' );
		$this->assertTrue( $second['expired'] );
	}

	/** A token that was never valid is expiry too: it cannot produce a reading either. */
	public function test_an_unknown_token_reports_expiry(): void {
		$_GET['roxyapi_r'] = self::TOKEN;

		$result = FormRouter::consume_result( self::FORM_ID );

		$this->assertTrue( $result['expired'] ?? false );
	}

	/**
	 * The blast radius, and why `expired` is not simply always returned: an
	 * ordinary page view asks for no result and must render a plain form with
	 * no notice at all.
	 */
	public function test_a_plain_page_view_reports_nothing(): void {
		$this->assertNull( FormRouter::consume_result( self::FORM_ID ) );
	}

	/** A malformed token is not a reading request either. */
	public function test_a_malformed_token_reports_nothing(): void {
		$_GET['roxyapi_r'] = 'not-a-valid-token';

		$this->assertNull( FormRouter::consume_result( self::FORM_ID ) );
	}

	/** One visitor's token must never resolve against another form. */
	public function test_a_token_does_not_cross_forms(): void {
		$this->store( array( 'result' => array( 'sign' => 'Aries' ) ) );
		$_GET['roxyapi_r'] = self::TOKEN;

		$other = FormRouter::consume_result( 'calculateLifePath' );

		$this->assertTrue( $other['expired'] ?? false, 'A token from one form must not deliver another form\'s reading.' );
	}
}
