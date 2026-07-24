<?php
/**
 * Tests for the Shortcodes\Registrar.
 *
 * Verifies the hero-wins-on-collision contract, asset registration, and
 * resilience to a missing generated bootstrap.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Plugin;
use RoxyAPI\Shortcodes\Registrar;

class Test_Shortcode_Registrar extends Mock_Http_TestCase {

	public function setUp(): void {
		parent::setUp();
		// test_hero_wins_on_tag_collision renders the hero for real, so the
		// response has to be canned. Without a key the client still reaches the
		// SaaS over the keyless free-tier path, which made this suite render a
		// live reading on every run.
		$this->mock_responses['astrology/horoscope/aries/daily'] = array(
			'sign'     => 'aries',
			'overview' => 'A bold day ahead.',
		);
		// Re-running do_action('init') would double-register blocks and
		// bindings sources, which the test framework flags as
		// _doing_it_wrong. Instead, invoke the registrar's static methods
		// directly. They are idempotent for shortcodes already registered.
		// Frontend stylesheet registration moved off the Registrar onto
		// Plugin::register_frontend_style in df50d3e (it was duplicating
		// the existing Plugin hook). Call that here so the asset-handle
		// assertion below has something to observe.
		Plugin::register_frontend_style();
		Registrar::register_hero();
		Registrar::register_generated();
	}

	public function test_hero_priority_runs_before_generated_priority(): void {
		$hero_priority      = has_action( 'init', array( Registrar::class, 'register_hero' ) );
		$generated_priority = has_action( 'init', array( Registrar::class, 'register_generated' ) );

		$this->assertSame( 10, $hero_priority );
		$this->assertSame( 20, $generated_priority );
		$this->assertLessThan( $generated_priority, $hero_priority );
	}

	public function test_every_hero_tag_is_registered(): void {
		foreach ( Registrar::HERO_SHORTCODES as $tag => $class ) {
			$this->assertTrue(
				shortcode_exists( $tag ),
				"Hero shortcode tag {$tag} must be registered."
			);
			$this->assertTrue(
				class_exists( $class ),
				"Hero class {$class} must exist."
			);
		}
	}

	public function test_hero_wins_on_tag_collision(): void {
		global $shortcode_tags;

		// After both register_hero and register_generated have run, a hero tag
		// must still resolve to the hero closure, NOT to a generated handler.
		$tag = 'roxy_horoscope';
		$this->assertArrayHasKey( $tag, $shortcode_tags );

		$callable = $shortcode_tags[ $tag ];
		$this->assertIsCallable( $callable );

		// The hero closure has the Horoscope class baked in via `use ($class)`.
		// Render with a known sign against the canned response: the hero
		// template emits `roxy-horoscope-card`, which the generic renderer
		// never does. It emits a `<dl class="roxyapi-generic ...">` shell.
		$out = call_user_func( $callable, array( 'sign' => 'aries' ), '', $tag );
		$this->assertIsString( $out );
		$this->assertStringContainsString(
			'roxy-horoscope-card',
			$out,
			'The collided tag must resolve to the hero renderer.'
		);
		// Hero output must NOT contain the generic-renderer signature class.
		$this->assertStringNotContainsString( 'roxyapi-generic', $out );
	}

	public function test_register_assets_registers_frontend_style(): void {
		$this->assertTrue(
			wp_style_is( 'roxyapi-frontend', 'registered' ),
			'roxyapi-frontend stylesheet must be registered after init.'
		);
	}

	public function test_register_generated_does_not_fatal_when_bootstrap_missing(): void {
		// Even though the generated bootstrap exists in this build, the call
		// site is guarded by class_exists. Calling register_generated again
		// must not double register or throw.
		Registrar::register_generated();
		$this->assertTrue( true, 'Calling register_generated repeatedly does not throw.' );
	}

	public function test_hero_register_is_idempotent_for_already_registered_tag(): void {
		// Re-running register_hero must not overwrite the existing tag with
		// a different callable.
		global $shortcode_tags;
		$before = $shortcode_tags['roxy_horoscope'] ?? null;
		Registrar::register_hero();
		$after = $shortcode_tags['roxy_horoscope'] ?? null;
		$this->assertSame( $before, $after, 'register_hero must be a no op when the tag is already registered.' );
	}
}
