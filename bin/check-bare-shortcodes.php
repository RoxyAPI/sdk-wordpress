<?php
/**
 * Renders every RoxyAPI shortcode with no attributes as a logged-out visitor
 * against the live API and fails if any of them shows the visitor an error.
 *
 * A bare placement must show a visitor form, a reading that needs no input, or
 * nothing at all (an owner-only setup note). Run with `npm run check:bare`,
 * which needs wp-env running and ROXYAPI_TEST_KEY set to a live key. Writes
 * nothing; it flushes the plugin cache first so a cached failure cannot hide.
 *
 * @package RoxyAPI
 */

defined( 'ABSPATH' ) || exit;

$roxyapi_key = (string) getenv( 'ROXYAPI_TEST_KEY' );
if ( $roxyapi_key === '' ) {
	WP_CLI::error( 'Set ROXYAPI_TEST_KEY to a live API key.' );
}
if ( ! defined( 'ROXYAPI_KEY' ) ) {
	define( 'ROXYAPI_KEY', $roxyapi_key );
}

\RoxyAPI\Api\Cache::flush_all();
wp_set_current_user( 0 );

$roxyapi_counts = array();
$roxyapi_errors = array();
$roxyapi_tags   = array_filter(
	array_keys( $GLOBALS['shortcode_tags'] ),
	static function ( $tag ) {
		return str_starts_with( $tag, 'roxy' );
	}
);
sort( $roxyapi_tags );

foreach ( $roxyapi_tags as $roxyapi_tag ) {
	$roxyapi_out = do_shortcode( "[$roxyapi_tag]" );
	if ( str_contains( $roxyapi_out, 'roxyapi-error' ) ) {
		$roxyapi_kind     = 'error';
		$roxyapi_errors[] = "[$roxyapi_tag] " . trim( wp_strip_all_tags( $roxyapi_out ) );
	} elseif ( str_contains( $roxyapi_out, '<form' ) ) {
		$roxyapi_kind = 'form';
	} elseif ( trim( $roxyapi_out ) === '' ) {
		$roxyapi_kind = 'nothing';
	} else {
		$roxyapi_kind = 'reading';
	}
	$roxyapi_counts[ $roxyapi_kind ] = ( $roxyapi_counts[ $roxyapi_kind ] ?? 0 ) + 1;
}

foreach ( $roxyapi_counts as $roxyapi_kind => $roxyapi_n ) {
	WP_CLI::log( "$roxyapi_kind: $roxyapi_n" );
}
if ( $roxyapi_errors ) {
	WP_CLI::error( "Bare shortcodes showing visitors an error:\n" . implode( "\n", $roxyapi_errors ) );
}
WP_CLI::success( count( $roxyapi_tags ) . ' bare shortcodes, none shows a visitor an error.' );
