<?php
/**
 * Attribution helper tests.
 *
 * Locks in WP.org compliance (credit defaults OFF, opt-in via setting),
 * JSON-LD shape (sourceOrganization, isBasedOn, citation, dispatch by op
 * family), anchor text variation, and filter overrides.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Support\Attribution;

class Test_Attribution extends \WP_UnitTestCase {

	public function tearDown(): void {
		remove_all_filters( 'roxyapi_render_credit' );
		remove_all_filters( 'roxyapi_emit_jsonld' );
		remove_all_filters( 'roxyapi_credit_anchor' );
		delete_option( 'roxyapi_settings' );
		parent::tearDown();
	}

	public function test_credit_link_off_by_default_per_wporg_rule(): void {
		// Plugin Directory guideline #10: "must be optional and default to NOT show".
		$this->assertSame( '', Attribution::credit_link( 'getCrystal' ) );
	}

	public function test_credit_link_renders_when_owner_opts_in(): void {
		update_option( 'roxyapi_settings', array( 'attribution_show' => true ) );
		$out = Attribution::credit_link( 'getCrystal' );
		$this->assertStringContainsString( 'class="roxyapi-credit"', $out );
		$this->assertStringContainsString( 'utm_source=wp-plugin', $out );
		$this->assertStringContainsString( 'utm_medium=attribution', $out );
		$this->assertStringContainsString( 'target="_blank"', $out );
	}

	public function test_credit_link_uses_noopener_only_after_opt_in(): void {
		// Drops `nofollow` once the site owner explicitly opts in — the opt-in
		// is the editorial act per Google's qualify-outbound-links guidance.
		update_option( 'roxyapi_settings', array( 'attribution_show' => true ) );
		$out = Attribution::credit_link( 'getDailyHoroscope' );
		$this->assertStringContainsString( 'rel="noopener"', $out );
		$this->assertStringNotContainsString( 'nofollow', $out );
	}

	public function test_credit_anchor_leads_with_domain_keyword(): void {
		// Brand-positioning rule: lead with what we offer, not who we are.
		update_option( 'roxyapi_settings', array( 'attribution_show' => true ) );
		$astro = Attribution::credit_link( 'getDailyHoroscope' );
		// Registry-driven section uses the brand-book label "Western Astrology".
		$this->assertStringContainsString( 'Western astrology data by Roxy', $astro );

		$tarot = Attribution::credit_link( 'getDailyCard' );
		$this->assertStringContainsString( 'Tarot data by Roxy', $tarot );

		$dream = Attribution::credit_link( 'getDailyDreamSymbol' );
		$this->assertStringContainsString( 'Dream interpretation data by Roxy', $dream );
	}

	public function test_credit_anchor_filter_can_override(): void {
		update_option( 'roxyapi_settings', array( 'attribution_show' => true ) );
		add_filter(
			'roxyapi_credit_anchor',
			static function () {
				return 'My custom anchor';
			}
		);
		$out = Attribution::credit_link( 'getDailyHoroscope' );
		$this->assertStringContainsString( 'My custom anchor', $out );
	}

	public function test_render_credit_filter_can_force_off_even_when_opted_in(): void {
		update_option( 'roxyapi_settings', array( 'attribution_show' => true ) );
		add_filter( 'roxyapi_render_credit', '__return_false' );
		$this->assertSame( '', Attribution::credit_link( 'getCrystal' ) );
	}

	public function test_jsonld_off_by_default(): void {
		$out = Attribution::jsonld( 'getCrystal', array( 'name' => 'Amethyst' ) );
		$this->assertSame( '', $out );
	}

	public function test_jsonld_emits_source_organization_not_provider(): void {
		// Per schema.org/sourceOrganization — the upstream data source.
		// `provider` is removed; `publisher` stays free for SEO plugins.
		add_filter( 'roxyapi_emit_jsonld', '__return_true' );
		$out = Attribution::jsonld( 'getCrystal', array( 'name' => 'Amethyst' ) );
		$this->assertStringContainsString( '"sourceOrganization"', $out );
		$this->assertStringNotContainsString( '"provider"', $out );
	}

	public function test_jsonld_includes_methodology_citation(): void {
		add_filter( 'roxyapi_emit_jsonld', '__return_true' );
		$out = Attribution::jsonld( 'getCrystal', array( 'name' => 'Amethyst' ) );
		$this->assertStringContainsString( '"citation"', $out );
		$this->assertStringContainsString( '"url":"https://roxyapi.com/methodology"', $out );
	}

	public function test_jsonld_includes_inlanguage(): void {
		add_filter( 'roxyapi_emit_jsonld', '__return_true' );
		$out = Attribution::jsonld( 'getCrystal', array( 'name' => 'Amethyst' ) );
		$this->assertStringContainsString( '"inLanguage"', $out );
	}

	public function test_jsonld_dispatches_article_for_time_bound_readings(): void {
		add_filter( 'roxyapi_emit_jsonld', '__return_true' );
		$out = Attribution::jsonld( 'getDailyHoroscope', array( 'name' => 'Aries' ) );
		$this->assertStringContainsString( '"@type":"Article"', $out );
		$this->assertStringContainsString( '"datePublished"', $out );
		$this->assertStringContainsString( '"articleSection":"western astrology"', $out );
	}

	public function test_jsonld_dispatches_creativework_for_reference_entries(): void {
		add_filter( 'roxyapi_emit_jsonld', '__return_true' );
		$out = Attribution::jsonld( 'getCrystal', array( 'name' => 'Amethyst' ) );
		$this->assertStringContainsString( '"@type":"CreativeWork"', $out );
		$this->assertStringNotContainsString( '"datePublished"', $out );
	}

	public function test_jsonld_includes_isbasedon_when_endpoint_known(): void {
		add_filter( 'roxyapi_emit_jsonld', '__return_true' );
		$out = Attribution::jsonld( 'getCrystal', array( 'name' => 'Amethyst' ) );
		// Endpoints registry resolves to /crystals/{id} or similar; just assert
		// the field is present and points at roxyapi.com/api/v2.
		$this->assertStringContainsString( '"isBasedOn":"https://roxyapi.com/api/v2', $out );
	}

	public function test_jsonld_sameas_array_carries_known_identity_edges(): void {
		add_filter( 'roxyapi_emit_jsonld', '__return_true' );
		$out = Attribution::jsonld( 'getCrystal', array( 'name' => 'Amethyst' ) );
		$this->assertStringContainsString( '"sameAs":["https://roxyapi.com","https://github.com/RoxyAPI"', $out );
	}

	public function test_jsonld_falls_back_to_operationid_when_no_name_field(): void {
		add_filter( 'roxyapi_emit_jsonld', '__return_true' );
		$out = Attribution::jsonld( 'getReading', array( 'value' => 7 ) );
		$this->assertStringContainsString( '"name":"getReading"', $out );
	}

	public function test_credit_link_does_not_appear_until_opted_in(): void {
		// End-to-end check: GenericRenderer should emit no credit by default.
		$out = \RoxyAPI\Support\GenericRenderer::render( 'getCrystal', array( 'name' => 'Amethyst' ) );
		$this->assertStringNotContainsString( 'roxyapi-credit', $out );
	}

	public function test_credit_link_appears_in_generic_renderer_after_opt_in(): void {
		update_option( 'roxyapi_settings', array( 'attribution_show' => true ) );
		$out = \RoxyAPI\Support\GenericRenderer::render( 'getCrystal', array( 'name' => 'Amethyst' ) );
		$this->assertStringContainsString( 'roxyapi-credit', $out );
	}
}
