<?php
/**
 * Operations that answer with the SITE OWNER's account rather than a reading.
 *
 * `getUsageStats` returns the account email, plan name and quota. It takes no
 * inputs, so its block rendered the instant it was inserted and published all
 * of that to the front end: any user who could edit a post could put the
 * owner's email and plan on a public page, and an anonymous visitor could read
 * it. It is an account-management endpoint that became a content block.
 *
 * Two defences, and the tests below hold both, because either alone still
 * leaks. Hiding it from the inserter does nothing about the shortcode, which
 * anyone who can edit a post can type. Guarding the shortcode does nothing
 * about a block already saved in a post.
 *
 * The list lives in `bin/generate.mjs` as `ACCOUNT_SCOPED_OPERATIONS` and both
 * the guard and the `inserter: false` flag are generated from it, so a new
 * account-scoped endpoint is one entry rather than two hand edits that can
 * drift apart. These assertions are what keep a regeneration honest.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Generated\Shortcodes\GetUsageStats;

class Test_Account_Scoped_Operations extends Mock_Http_TestCase {

	/** Every operation the generator treats as account-scoped. */
	private const ACCOUNT_SCOPED = array( 'get-usage-stats' );

	public function test_usage_stats_renders_nothing_for_an_anonymous_visitor(): void {
		wp_set_current_user( 0 );

		$this->assertSame( '', GetUsageStats::render( array(), '', 'roxy_get_usage_stats' ) );
	}

	public function test_usage_stats_renders_nothing_for_a_user_who_can_publish_posts(): void {
		// The real exposure. An Editor can publish a page, so if the shortcode
		// rendered for them the account email reaches the public web through a
		// perfectly ordinary editorial workflow.
		$editor = self::factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $editor );
		$this->assertTrue( current_user_can( 'publish_posts' ) );

		$this->assertSame( '', GetUsageStats::render( array(), '', 'roxy_get_usage_stats' ) );
	}

	public function test_usage_stats_still_works_for_an_administrator(): void {
		$admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin );

		$this->mock_responses['usage'] = array( 'plan' => 'Starter', 'usedThisMonth' => 10 );

		$this->assertNotSame( '', GetUsageStats::render( array(), '', 'roxy_get_usage_stats' ) );
	}

	public function test_account_scoped_blocks_are_kept_out_of_the_inserter(): void {
		foreach ( self::ACCOUNT_SCOPED as $slug ) {
			$path = dirname( __DIR__, 2 ) . "/blocks/generated/{$slug}/block.json";
			$this->assertFileExists( $path, "{$slug} block.json is missing." );

			$block = json_decode( (string) file_get_contents( $path ), true );
			$this->assertFalse(
				$block['supports']['inserter'] ?? true,
				"{$slug} must not be offered in the block inserter."
			);
		}
	}

	public function test_ordinary_reading_blocks_stay_in_the_inserter(): void {
		// Guards the blast radius: the flag must reach exactly the listed
		// operations and nothing else.
		$block = json_decode(
			(string) file_get_contents( dirname( __DIR__, 2 ) . '/blocks/generated/calculate-soul-urge/block.json' ),
			true
		);

		$this->assertTrue( $block['supports']['inserter'] ?? true );
	}
}
