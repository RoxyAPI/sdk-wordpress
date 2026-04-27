<?php
/**
 * Tests for RoxyAPI\Support\Templates::api_error audience-aware rendering.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Support\Templates;
use WP_Error;

class Test_Templates_Api_Error extends \WP_UnitTestCase {

	public function tearDown(): void {
		wp_set_current_user( 0 );
		parent::tearDown();
	}

	private function sample_error( string $code = 'roxyapi_quota', string $msg = '', array $data = array() ): WP_Error {
		if ( $msg === '' ) {
			$msg = 'Daily readings are temporarily unavailable. Please check back later.';
		}
		return new WP_Error( $code, $msg, $data );
	}

	public function test_visitor_sees_only_friendly_message(): void {
		// Default user is anonymous (id 0) which lacks manage_options.
		wp_set_current_user( 0 );

		$err = $this->sample_error(
			'roxyapi_quota',
			'Daily readings are temporarily unavailable. Please check back later.',
			array(
				'status'    => 429,
				'saas_code' => 'rate_limit_exceeded',
				'saas_msg'  => 'Rate limit exceeded: 50 requests per month',
			)
		);
		$out = Templates::api_error( $err );

		$this->assertStringContainsString( 'Daily readings are temporarily unavailable', $out );
		$this->assertStringContainsString( 'roxyapi-error', $out );
		$this->assertStringNotContainsString( 'Admin only:', $out );
		$this->assertStringNotContainsString( 'HTTP 429', $out );
		$this->assertStringNotContainsString( 'rate_limit_exceeded', $out );
		$this->assertStringNotContainsString( 'Rate limit exceeded: 50 requests per month', $out );
	}

	public function test_admin_sees_status_code_and_messages(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$err = $this->sample_error(
			'roxyapi_quota',
			'Daily readings are temporarily unavailable. Please check back later.',
			array(
				'status'    => 429,
				'saas_code' => 'rate_limit_exceeded',
				'saas_msg'  => 'Rate limit exceeded: 50 requests per month',
			)
		);
		$out = Templates::api_error( $err );

		$this->assertStringContainsString( 'Daily readings are temporarily unavailable', $out );
		$this->assertStringContainsString( 'roxyapi-error-admin', $out );
		$this->assertStringContainsString( 'Admin only:', $out );
		$this->assertStringContainsString( 'HTTP 429', $out );
		$this->assertStringContainsString( 'rate_limit_exceeded', $out );
		$this->assertStringContainsString( 'Rate limit exceeded: 50 requests per month', $out );
		// The detail parts must be joined by " / ".
		$this->assertStringContainsString( ' / ', $out );
	}

	public function test_admin_xss_probe_in_saas_msg_is_escaped(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$err = $this->sample_error(
			'roxyapi_upstream',
			'Service unavailable',
			array(
				'status'    => 500,
				'saas_code' => 'oops',
				'saas_msg'  => '<script>alert(1)</script>',
			)
		);
		$out = Templates::api_error( $err );

		// A live <script> tag must NOT appear; the escaped form must.
		$this->assertStringNotContainsString( '<script>alert(1)</script>', $out );
		$this->assertStringContainsString( '&lt;script&gt;', $out );
	}

	public function test_admin_with_empty_data_sees_only_friendly_message(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$err = new WP_Error( 'roxyapi_no_key', 'RoxyAPI key not configured.' );
		$out = Templates::api_error( $err );

		$this->assertStringContainsString( 'RoxyAPI key not configured.', $out );
		$this->assertStringNotContainsString( 'Admin only:', $out );
		$this->assertStringNotContainsString( 'roxyapi-error-admin', $out );
	}

	public function test_visitor_friendly_message_appears_only_once(): void {
		wp_set_current_user( 0 );
		$err = $this->sample_error( 'roxyapi_auth', 'Friendly only' );
		$out = Templates::api_error( $err );
		$this->assertSame( 1, substr_count( $out, 'Friendly only' ) );
	}

	public function test_admin_sees_did_you_mean_for_404_with_suggestion(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$err = $this->sample_error(
			'roxyapi_http_404',
			'This reading could not be loaded right now. Please try again.',
			array(
				'status'          => 404,
				'saas_code'       => 'not_found',
				'saas_msg'        => 'No endpoint found at GET /api/v2/vedic/astrology/yoga',
				'saas_suggestion' => 'GET /api/v2/vedic-astrology/yoga',
				'saas_hint'       => 'List all planetary yogas',
			)
		);
		$out = Templates::api_error( $err );

		// The fuzzy-match line surfaces the corrected method+path the SaaS
		// suggested, plus the human hint.
		$this->assertStringContainsString( 'Did you mean:', $out );
		$this->assertStringContainsString( 'GET /api/v2/vedic-astrology/yoga', $out );
		$this->assertStringContainsString( 'List all planetary yogas', $out );
	}

	public function test_visitor_never_sees_did_you_mean_or_hint(): void {
		wp_set_current_user( 0 );

		$err = $this->sample_error(
			'roxyapi_http_404',
			'This reading could not be loaded right now. Please try again.',
			array(
				'status'          => 404,
				'saas_suggestion' => 'GET /api/v2/vedic-astrology/yoga',
				'saas_hint'       => 'Sensitive endpoint shape',
			)
		);
		$out = Templates::api_error( $err );

		$this->assertStringNotContainsString( 'Did you mean:', $out );
		$this->assertStringNotContainsString( 'vedic-astrology', $out );
		$this->assertStringNotContainsString( 'Sensitive', $out );
	}

	public function test_admin_did_you_mean_xss_probe_is_escaped(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$err = $this->sample_error(
			'roxyapi_http_404',
			'msg',
			array(
				'status'          => 404,
				'saas_suggestion' => '<script>alert(2)</script>',
				'saas_hint'       => '<img src=x onerror=alert(3)>',
			)
		);
		$out = Templates::api_error( $err );

		$this->assertStringNotContainsString( '<script>alert(2)</script>', $out );
		$this->assertStringNotContainsString( '<img src=x', $out );
		$this->assertStringContainsString( '&lt;script&gt;', $out );
	}

	public function test_simple_error_helper_emits_one_div(): void {
		$out = Templates::error( 'Plain error message' );
		$this->assertSame(
			'<div class="roxyapi-error">Plain error message</div>',
			$out
		);
	}

	public function test_simple_error_helper_escapes_html(): void {
		$out = Templates::error( '<script>boom</script>' );
		$this->assertStringNotContainsString( '<script>boom</script>', $out );
		$this->assertStringContainsString( '&lt;script&gt;', $out );
	}

	public function test_admin_with_partial_data_only_emits_present_fields(): void {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$err = $this->sample_error(
			'roxyapi_upstream',
			'Service unavailable',
			array(
				'status'    => 500,
				'saas_code' => '',
				'saas_msg'  => '',
			)
		);
		$out = Templates::api_error( $err );
		$this->assertStringContainsString( 'HTTP 500', $out );
		// No empty " / " trailers if only HTTP is present.
		$this->assertStringNotContainsString( ' /  / ', $out );
	}
}
