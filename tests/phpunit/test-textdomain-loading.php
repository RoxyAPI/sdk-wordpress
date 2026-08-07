<?php
/**
 * The bundled catalogue has to reach memory without help from WordPress.
 *
 * Two shipped releases rendered English on every non-English site because nothing loaded the
 * catalogue: before WordPress 6.8 nothing registered the plugin's own `languages` directory, so
 * just-in-time resolution found no path, never called `load_textdomain()`, and the
 * `load_textdomain_mofile` filter that maps regional locales never even ran. Measured on clean
 * 6.7.2 and 7.0.3 installs.
 *
 * These tests pin the behaviour that fix depends on: the plugin resolves its own catalogue and
 * loads it, rather than waiting to be asked.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Plugin;

class Test_Textdomain_Loading extends \WP_UnitTestCase {

	public function tear_down(): void {
		// A catalogue left in memory would translate strings in every later test.
		unload_textdomain( 'roxyapi' );
		parent::tear_down();
	}

	public function force_es_ar(): string {
		return 'es_AR';
	}

	public function force_es_es(): string {
		return 'es_ES';
	}

	public function test_an_exact_locale_loads(): void {
		add_filter( 'pre_determine_locale', array( $this, 'force_es_es' ) );
		unload_textdomain( 'roxyapi' );

		Plugin::load_textdomain();

		$this->assertTrue( is_textdomain_loaded( 'roxyapi' ) );
		$this->assertSame( 'Fecha de nacimiento', __( 'Birth date', 'roxyapi' ) );
	}

	public function test_a_regional_locale_loads_the_catalogue_for_its_language(): void {
		// es_AR is the locale that exposed this on a real customer site. We ship no file for it.
		add_filter( 'pre_determine_locale', array( $this, 'force_es_ar' ) );
		unload_textdomain( 'roxyapi' );

		Plugin::load_textdomain();

		$this->assertTrue( is_textdomain_loaded( 'roxyapi' ) );
		$this->assertSame( 'Fecha de nacimiento', __( 'Birth date', 'roxyapi' ) );
	}

	public function test_the_catalogue_loads_even_when_just_in_time_resolution_cannot_run(): void {
		add_filter( 'pre_determine_locale', array( $this, 'force_es_es' ) );

		/*
		 * A non-reloadable unload is the one state just-in-time loading cannot recover from:
		 * core records the domain in $l10n_unloaded and short-circuits every later attempt
		 * before it looks at any path. Loading the file directly is unaffected, which is the
		 * whole point of doing it ourselves.
		 */
		unload_textdomain( 'roxyapi' );
		$this->assertFalse( is_textdomain_loaded( 'roxyapi' ) );

		Plugin::load_textdomain();

		$this->assertTrue( is_textdomain_loaded( 'roxyapi' ) );
		$this->assertSame( 'Fecha de nacimiento', __( 'Birth date', 'roxyapi' ) );
	}

	public function test_an_english_locale_loads_nothing_and_does_not_error(): void {
		unload_textdomain( 'roxyapi' );

		Plugin::load_textdomain();

		// We ship no English catalogue: the source strings already are the English ones.
		$this->assertFalse( is_textdomain_loaded( 'roxyapi' ) );
		$this->assertSame( 'Birth date', __( 'Birth date', 'roxyapi' ) );
	}

	public function test_loading_is_hooked_late_enough_to_keep_the_fallback_filter_working(): void {
		/*
		 * Loading before `init` suppresses the filters translations depend on, and WordPress
		 * reports it as a doing_it_wrong. LocaleFallback is one of those filters, so an early
		 * load would defeat the regional-locale mapping it exists to provide.
		 */
		$this->assertSame( 0, has_action( 'init', array( Plugin::class, 'load_textdomain' ) ) );
	}
}
