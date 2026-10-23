<?php
/**
 * Renders every RoxyAPI reading the three ways a site uses it, as a logged-out
 * visitor against the live API, and fails if any of them shows an error.
 *
 * 1. Bare: every shortcode with no attributes must show a visitor form, a
 *    reading that needs no input, or nothing (an owner-only setup note).
 * 2. Placed: every generated shortcode with its library sample attributes must
 *    render a reading or its form.
 * 3. Submitted: every visitor form, with each field a visitor must fill given
 *    the sample its input offers, goes through the submission path a visitor
 *    POST takes and must return a reading.
 *
 * A failure is retried once and the retries are listed, so a dropped
 * connection is told apart from a reading that fails every time.
 *
 * Run with `npm run check:readings`, which needs wp-env running and
 * ROXYAPI_TEST_KEY set to a live key. Writes nothing; it flushes the plugin
 * cache first so a cached failure cannot hide.
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

// The wp-env containers have no IPv6 route, so a connection that tries IPv6 first stalls until the client timeout.
add_action(
	'http_api_curl',
	static function ( $handle ) {
		curl_setopt( $handle, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt -- pins this maintainer check, not a plugin request.
	}
);

/**
 * Classify rendered shortcode output.
 *
 * @param string $out Rendered HTML.
 * @return array{0:string,1:string} Kind (error, form, nothing or reading) and the visible text.
 */
function roxyapi_check_render( string $out ): array {
	if ( str_contains( $out, 'roxyapi-error' ) ) {
		return array( 'error', trim( wp_strip_all_tags( $out ) ) );
	}
	if ( str_contains( $out, '<form' ) ) {
		return array( 'form', '' );
	}
	return array( trim( $out ) === '' ? 'nothing' : 'reading', '' );
}

/**
 * The value a visitor's input posts for one field: the curated placeholder,
 * else the UI model example, else the default, held to what the input can
 * send (a choice from its list, an IANA zone, a typed sample).
 *
 * @param array<string,mixed> $field Form spec field.
 * @param array<string,mixed> $model UI model field, when the form has one.
 * @return string
 */
function roxyapi_check_sample( array $field, array $model ): string {
	$type  = (string) ( $field['type'] ?? 'text' );
	$value = (string) ( $field['placeholder'] ?? $model['example'] ?? $field['default'] ?? '' );
	if ( $type === 'enum' ) {
		return in_array( $value, $field['enum'], true ) ? $value : (string) $field['enum'][0];
	}
	if ( $type === 'timezone' && ! in_array( $value, \DateTimeZone::listIdentifiers(), true ) ) {
		return 'UTC';
	}
	if ( $type === 'datetime' ) {
		// A datetime-local input posts the wall clock with no offset.
		return substr( $value !== '' ? $value : '1990-07-15T14:30:00', 0, 19 );
	}
	$typed = array(
		'date'    => '1990-07-15',
		'time'    => '14:30',
		'number'  => '1',
		'integer' => '1',
	);
	return $value !== '' ? $value : ( $typed[ $type ] ?? '' );
}

/**
 * The values a visitor posts for one form: every required field, nested along
 * each section's wire path the way the browser posts it.
 *
 * @param array<string,mixed> $spec  Form spec.
 * @param array<string,array> $model UI model fields by wire key.
 * @return array<string,mixed>
 */
function roxyapi_check_post( array $spec, array $model ): array {
	$fill = static function ( array $fields, string $prefix ) use ( $model ): array {
		$values = array();
		foreach ( $fields as $field ) {
			if ( ! empty( $field['required'] ) ) {
				$values[ $field['name'] ] = roxyapi_check_sample( $field, $model[ $prefix . $field['name'] ] ?? array() );
			}
		}
		return $values;
	};
	$post = $fill( $spec['flat_fields'] ?? array(), '' );
	foreach ( $spec['sections'] ?? array() as $section ) {
		\RoxyAPI\Support\FormRouter::put_section( $post, $section['name'], $fill( $section['fields'], $section['name'] . '.' ) );
	}
	return $post;
}

/**
 * Every probe, by pass: label to a callable returning [kind, detail].
 *
 * @var array<string, array<string, callable(): array{0:string,1:string}>>
 */
$roxyapi_probes = array();

foreach ( array_keys( $GLOBALS['shortcode_tags'] ) as $roxyapi_tag ) {
	if ( str_starts_with( $roxyapi_tag, 'roxy' ) ) {
		$roxyapi_probes['bare'][ "[$roxyapi_tag]" ] = static fn() => roxyapi_check_render( do_shortcode( "[$roxyapi_tag]" ) );
	}
}

foreach ( \RoxyAPI\Generated\Endpoints::all() as $roxyapi_endpoint ) {
	$roxyapi_tag = (string) $roxyapi_endpoint['shortcode_tag'];
	if ( ! empty( $roxyapi_endpoint['attributes'] ) && shortcode_exists( $roxyapi_tag ) ) {
		$roxyapi_probes['placed'][ "[$roxyapi_tag]" ] = static fn() => roxyapi_check_render(
			(string) call_user_func( $GLOBALS['shortcode_tags'][ $roxyapi_tag ], $roxyapi_endpoint['attributes'], '', $roxyapi_tag )
		);
	}
}

// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- a local vendored file, not a remote request.
$roxyapi_models = json_decode( (string) file_get_contents( __DIR__ . '/ui-forms/forms.json' ), true )['forms'];
foreach ( glob( dirname( __DIR__ ) . '/src/Generated/Forms/*Form.php' ) as $roxyapi_file ) {
	$roxyapi_class = '\\RoxyAPI\\Generated\\Forms\\' . basename( $roxyapi_file, '.php' );
	$roxyapi_spec  = $roxyapi_class::spec();
	$roxyapi_post  = roxyapi_check_post( $roxyapi_spec, array_column( $roxyapi_models[ $roxyapi_spec['operation_id'] ]['fields'] ?? array(), null, 'key' ) );

	$roxyapi_probes['submitted'][ (string) $roxyapi_spec['operation_id'] ] = static function () use ( $roxyapi_class, $roxyapi_post ): array {
		$outcome = \RoxyAPI\Support\FormRouter::submit( $roxyapi_class, $roxyapi_post );
		if ( isset( $outcome['result'] ) ) {
			return array( 'reading', '' );
		}
		if ( isset( $outcome['wp_error'] ) ) {
			return array( 'api error', (string) ( $outcome['wp_error']['data']['saas_msg'] ?? $outcome['wp_error']['message'] ) );
		}
		return array( 'rejected', (string) wp_json_encode( $outcome['validation_errors'] ?? array() ) );
	};
}

// What each pass accepts: placed samples must render, submissions must return a reading.
$roxyapi_passes = array(
	'bare'      => array( 'form', 'nothing', 'reading' ),
	'placed'    => array( 'form', 'reading' ),
	'submitted' => array( 'reading' ),
);

$roxyapi_failures = array();
$roxyapi_retried  = array();
foreach ( $roxyapi_passes as $roxyapi_pass => $roxyapi_accepted ) {
	$roxyapi_counts = array();
	foreach ( $roxyapi_probes[ $roxyapi_pass ] as $roxyapi_label => $roxyapi_probe ) {
		$roxyapi_result = $roxyapi_probe();
		if ( ! in_array( $roxyapi_result[0], $roxyapi_accepted, true ) ) {
			sleep( 2 );
			$roxyapi_retried[] = "$roxyapi_pass $roxyapi_label";
			$roxyapi_result    = $roxyapi_probe();
		}
		$roxyapi_counts[ $roxyapi_result[0] ] = ( $roxyapi_counts[ $roxyapi_result[0] ] ?? 0 ) + 1;
		if ( ! in_array( $roxyapi_result[0], $roxyapi_accepted, true ) ) {
			$roxyapi_failures[] = "$roxyapi_pass $roxyapi_label $roxyapi_result[0]: $roxyapi_result[1]";
		}
	}
	ksort( $roxyapi_counts );
	WP_CLI::log( $roxyapi_pass . ': ' . http_build_query( $roxyapi_counts, '', ', ' ) );
}

if ( $roxyapi_retried ) {
	WP_CLI::warning( 'Passed or failed on a second attempt: ' . implode( ', ', $roxyapi_retried ) );
}
if ( $roxyapi_failures ) {
	WP_CLI::error( "Readings that fail a visitor:\n" . implode( "\n", $roxyapi_failures ) );
}
WP_CLI::success( 'Every reading renders for a visitor bare, placed and submitted.' );
