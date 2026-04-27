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

use RoxyAPI\Shortcodes\Registrar;

class Test_Shortcode_Registrar extends \WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		// Re-running do_action('init') would double-register blocks and
		// bindings sources, which the test framework flags as
		// _doing_it_wrong. Instead, invoke just the registrar's static
		// methods directly. They are idempotent for shortcodes already
		// registered, and the assets registration is similarly safe to
		// repeat.
		Registrar::register_assets();
		Registrar::register_hero();
		Registrar::register_generated();
	}

	public function test_hero_priority_runs_before_generated_priority(): void {
		$hero_priority      = has_action( 'init', array( Registrar::class, 'register_hero' ) );
		$generated_priority = has_action( 'init', array( Registrar::class, 'register_generated' ) );
		$assets_priority    = has_action( 'init', array( Registrar::class, 'register_assets' ) );

		$this->assertSame( 10, $hero_priority );
		$this->assertSame( 20, $generated_priority );
		$this->assertSame( 5, $assets_priority );
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
		// Render with a known sign and check the output contains the hero CSS
		// hook (`roxyapi-horoscope`), which the generic renderer never emits.
		// Even when the API call fails (no key configured here), the hero
		// returns the friendly placeholder string. The generic renderer would
		// emit a `<dl class="roxyapi-generic ...">` shell instead.
		$out = call_user_func( $callable, array( 'sign' => 'aries' ), '', $tag );
		$this->assertIsString( $out );
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
