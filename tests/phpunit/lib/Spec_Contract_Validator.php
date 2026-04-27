<?php
/**
 * Spec contract validator.
 *
 * Loads the committed OpenAPI snapshot once per test process and validates
 * captured wp_remote_request shapes against the spec for a given operationId.
 *
 * The validator answers a single question: would the SaaS accept this request
 * exactly as the plugin sent it? It is intentionally narrower than a full JSON
 * Schema implementation: it covers only the keywords the RoxyAPI spec actually
 * uses (type, required, pattern, enum, minimum, maximum, minLength, anyOf,
 * oneOf, properties, items, $ref, allOf, additionalProperties via warnings).
 *
 * Returning a list of violation strings (rather than throwing) lets the caller
 * assemble a precise diagnostic when a hero contract regresses.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests\Lib;

class Spec_Contract_Validator {

	/**
	 * Cached spec keyed by file path. Loaded lazily; survives across all tests
	 * in the same PHPUnit process.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private static array $cache = array();

	/**
	 * Load the spec lazily. Cached for the lifetime of the test process so the
	 * 12 MB JSON parse runs at most once per PHPUnit invocation.
	 *
	 * @return array<string, mixed>
	 */
	public static function spec(): array {
		$path = dirname( __DIR__, 2 ) . '/../specs/openapi.json';
		$path = (string) realpath( $path );
		if ( $path === '' ) {
			throw new \RuntimeException( 'specs/openapi.json missing; run npm run generate.' );
		}
		if ( ! isset( self::$cache[ $path ] ) ) {
			$raw = file_get_contents( $path );
			if ( $raw === false ) {
				throw new \RuntimeException( "Could not read OpenAPI spec at {$path}" );
			}
			$decoded = json_decode( $raw, true );
			if ( ! is_array( $decoded ) ) {
				throw new \RuntimeException( 'Invalid JSON in specs/openapi.json' );
			}
			self::$cache[ $path ] = $decoded;
		}
		return self::$cache[ $path ];
	}

	/**
	 * Find an operation by operationId. Returns the path template, HTTP verb,
	 * and the resolved operation definition. Returns null if not found.
	 *
	 * @param string $opId OpenAPI operationId.
	 * @return array{path:string,method:string,operation:array<string,mixed>}|null
	 */
	public static function operation( string $opId ): ?array {
		$spec = self::spec();
		if ( ! isset( $spec['paths'] ) || ! is_array( $spec['paths'] ) ) {
			return null;
		}
		foreach ( $spec['paths'] as $path => $methods ) {
			if ( ! is_array( $methods ) ) {
				continue;
			}
			foreach ( $methods as $verb => $op ) {
				if ( ! is_array( $op ) ) {
					continue;
				}
				if ( isset( $op['operationId'] ) && $op['operationId'] === $opId ) {
					return array(
						'path'      => (string) $path,
						'method'    => strtoupper( (string) $verb ),
						'operation' => $op,
					);
				}
			}
		}
		return null;
	}

	/**
	 * Resolve a $ref pointer or merge an allOf into a flat schema.
	 *
	 * Only resolves pointers internal to the spec. Cycles are bounded by depth.
	 *
	 * @param mixed $schema Raw schema fragment.
	 * @return array<string, mixed>
	 */
	public static function resolve( $schema ): array {
		return self::resolve_internal( $schema, 0 );
	}

	/**
	 * @param mixed $schema Raw schema fragment.
	 * @param int   $depth  Recursion depth (bounded to avoid spec cycles).
	 * @return array<string, mixed>
	 */
	private static function resolve_internal( $schema, int $depth ): array {
		if ( $depth > 16 || ! is_array( $schema ) ) {
			return is_array( $schema ) ? $schema : array();
		}
		if ( isset( $schema['$ref'] ) && is_string( $schema['$ref'] ) ) {
			$resolved = self::lookup_ref( $schema['$ref'] );
			return self::resolve_internal( $resolved, $depth + 1 );
		}
		if ( isset( $schema['allOf'] ) && is_array( $schema['allOf'] ) ) {
			$merged = array();
			foreach ( $schema['allOf'] as $part ) {
				$merged = self::merge_schemas( $merged, self::resolve_internal( $part, $depth + 1 ) );
			}
			unset( $schema['allOf'] );
			$merged = self::merge_schemas( $merged, $schema );
			return $merged;
		}
		return $schema;
	}

	/**
	 * Validate a captured wp_remote_request against the spec for the given
	 * operationId. Returns a list of violation strings (empty = compliant).
	 *
	 * Caller passes the URL the plugin would have called and the args array
	 * exactly as it would have reached wp_remote_request (method, headers,
	 * body). The body may be a JSON string (POST) or absent (GET).
	 *
	 * @param string                     $opId          OpenAPI operationId.
	 * @param string                     $captured_url  Full URL the plugin would have called.
	 * @param array<string, mixed>       $captured_args wp_remote_request args.
	 * @param array{forbid_extras?:bool} $options       Validator knobs.
	 * @return array<int, string>                       Violations; empty when compliant.
	 */
	public static function validate( string $opId, string $captured_url, array $captured_args, array $options = array() ): array {
		$violations = array();

		$op = self::operation( $opId );
		if ( $op === null ) {
			return array( "operationId not found in spec: {$opId}" );
		}

		// 1. Method match.
		$expected_method  = $op['method'];
		$actual_method    = isset( $captured_args['method'] ) ? strtoupper( (string) $captured_args['method'] ) : 'GET';
		if ( $expected_method !== $actual_method ) {
			$violations[] = "method mismatch: spec expects {$expected_method}, plugin sent {$actual_method}";
		}

		// 2. Path + path-param match.
		$path_template = $op['path'];
		$parts         = wp_parse_url( $captured_url );
		$actual_path   = is_array( $parts ) && isset( $parts['path'] ) ? (string) $parts['path'] : '';
		$path_params   = self::match_path( $path_template, $actual_path );
		if ( $path_params === null ) {
			$violations[] = "path {$path_template} mismatched: got {$actual_path}";
			$path_params  = array();
		}

		// 3. Validate path params + query params.
		$query_params = array();
		if ( is_array( $parts ) && isset( $parts['query'] ) ) {
			parse_str( (string) $parts['query'], $query_params );
		}

		$parameters = array();
		if ( isset( $op['operation']['parameters'] ) && is_array( $op['operation']['parameters'] ) ) {
			foreach ( $op['operation']['parameters'] as $param ) {
				$parameters[] = self::resolve( $param );
			}
		}

		foreach ( $parameters as $param ) {
			$name     = isset( $param['name'] ) ? (string) $param['name'] : '';
			$in       = isset( $param['in'] ) ? (string) $param['in'] : '';
			$required = ! empty( $param['required'] );
			$schema   = isset( $param['schema'] ) ? self::resolve( $param['schema'] ) : array();

			if ( $in === 'path' ) {
				if ( ! array_key_exists( $name, $path_params ) ) {
					if ( $required ) {
						$violations[] = "missing required path param: {$name}";
					}
					continue;
				}
				$violations = array_merge(
					$violations,
					self::validate_value( $schema, rawurldecode( $path_params[ $name ] ), "path param {$name}", true )
				);
				continue;
			}

			if ( $in === 'query' ) {
				if ( ! array_key_exists( $name, $query_params ) ) {
					if ( $required ) {
						$violations[] = "missing required query param: {$name}";
					}
					continue;
				}
				$violations = array_merge(
					$violations,
					self::validate_value( $schema, $query_params[ $name ], "query param {$name}", true )
				);
			}
		}

		// 4. Validate request body for POST-like ops.
		$request_body = isset( $op['operation']['requestBody'] ) && is_array( $op['operation']['requestBody'] )
			? $op['operation']['requestBody']
			: null;

		if ( $request_body !== null ) {
			$body_schema_raw = $request_body['content']['application/json']['schema'] ?? null;
			$body_schema     = $body_schema_raw === null ? array() : self::resolve( $body_schema_raw );

			$body_string = isset( $captured_args['body'] ) ? (string) $captured_args['body'] : '';
			$decoded     = $body_string === '' ? array() : json_decode( $body_string, true );
			if ( $body_string !== '' && ! is_array( $decoded ) ) {
				$violations[] = 'request body is not valid JSON';
				$decoded      = array();
			}

			$violations = array_merge(
				$violations,
				self::validate_object( $body_schema, $decoded, 'body', $options )
			);
		}

		return $violations;
	}

	/**
	 * Merge two flat schemas. Right side wins for scalar keys; required arrays
	 * union; properties merge key-wise.
	 *
	 * @param array<string, mixed> $a Left.
	 * @param array<string, mixed> $b Right.
	 * @return array<string, mixed>
	 */
	private static function merge_schemas( array $a, array $b ): array {
		$out = $a;
		foreach ( $b as $k => $v ) {
			if ( $k === 'required' && isset( $out['required'] ) && is_array( $out['required'] ) && is_array( $v ) ) {
				$out['required'] = array_values( array_unique( array_merge( $out['required'], $v ) ) );
				continue;
			}
			if ( $k === 'properties' && isset( $out['properties'] ) && is_array( $out['properties'] ) && is_array( $v ) ) {
				$out['properties'] = array_merge( $out['properties'], $v );
				continue;
			}
			$out[ $k ] = $v;
		}
		return $out;
	}

	/**
	 * Resolve a $ref pointer like #/components/schemas/Foo.
	 *
	 * @param string $ref Pointer.
	 * @return array<string, mixed>
	 */
	private static function lookup_ref( string $ref ): array {
		if ( strpos( $ref, '#/' ) !== 0 ) {
			return array();
		}
		$parts = explode( '/', substr( $ref, 2 ) );
		$cur   = self::spec();
		foreach ( $parts as $p ) {
			$p = str_replace( array( '~1', '~0' ), array( '/', '~' ), $p );
			if ( ! is_array( $cur ) || ! array_key_exists( $p, $cur ) ) {
				return array();
			}
			$cur = $cur[ $p ];
		}
		return is_array( $cur ) ? $cur : array();
	}

	/**
	 * Match a captured path against a templated spec path. Returns the
	 * extracted path-param values keyed by name, or null if the templates do
	 * not align (different segment count or static segment mismatch).
	 *
	 * @param string $template Spec path template (e.g. "/foo/{id}/bar").
	 * @param string $actual   Captured URL path component.
	 * @return array<string, string>|null
	 */
	private static function match_path( string $template, string $actual ): ?array {
		// Normalise: the BASE_URL is `https://roxyapi.com/api/v2` so spec paths
		// need that prefix to align with captured URLs.
		$prefix = '/api/v2';
		if ( strpos( $actual, $prefix ) === 0 ) {
			$actual = substr( $actual, strlen( $prefix ) );
		}
		$tpl_parts = array_values( array_filter( explode( '/', $template ), 'strlen' ) );
		$act_parts = array_values( array_filter( explode( '/', $actual ), 'strlen' ) );
		if ( count( $tpl_parts ) !== count( $act_parts ) ) {
			return null;
		}
		$out = array();
		foreach ( $tpl_parts as $i => $seg ) {
			if ( strlen( $seg ) >= 2 && $seg[0] === '{' && $seg[ strlen( $seg ) - 1 ] === '}' ) {
				$name         = substr( $seg, 1, -1 );
				$out[ $name ] = $act_parts[ $i ];
				continue;
			}
			if ( $seg !== $act_parts[ $i ] ) {
				return null;
			}
		}
		return $out;
	}

	/**
	 * Validate an object value (request body or nested object) against its
	 * schema. Returns a list of violations.
	 *
	 * @param array<string, mixed>       $schema  Resolved schema.
	 * @param mixed                      $value   Decoded value.
	 * @param string                     $label   Context label for messages.
	 * @param array{forbid_extras?:bool} $options Validator knobs.
	 * @return array<int, string>
	 */
	private static function validate_object( array $schema, $value, string $label, array $options ): array {
		$violations = array();

		if ( ! is_array( $value ) ) {
			$violations[] = "{$label} expected object, got " . gettype( $value );
			return $violations;
		}

		// anyOf / oneOf at the object root: pass if any branch validates.
		if ( isset( $schema['anyOf'] ) && is_array( $schema['anyOf'] ) ) {
			return self::validate_any_of( $schema['anyOf'], $value, $label, $options );
		}
		if ( isset( $schema['oneOf'] ) && is_array( $schema['oneOf'] ) ) {
			return self::validate_any_of( $schema['oneOf'], $value, $label, $options );
		}

		$props    = isset( $schema['properties'] ) && is_array( $schema['properties'] ) ? $schema['properties'] : array();
		$required = isset( $schema['required'] ) && is_array( $schema['required'] ) ? $schema['required'] : array();

		// Required keys.
		foreach ( $required as $key ) {
			if ( ! is_string( $key ) ) {
				continue;
			}
			if ( ! array_key_exists( $key, $value ) ) {
				$violations[] = $label === 'body'
					? "missing required body field: {$key}"
					: "missing required field: {$label}.{$key}";
			}
		}

		// Per-property validation.
		foreach ( $value as $key => $v ) {
			if ( ! is_string( $key ) ) {
				continue;
			}
			if ( ! array_key_exists( $key, $props ) ) {
				if ( ! empty( $options['forbid_extras'] ) ) {
					$violations[] = $label === 'body'
						? "unexpected body field: {$key}"
						: "unexpected field: {$label}.{$key}";
				}
				continue;
			}
			$child_schema = self::resolve( $props[ $key ] );
			$violations   = array_merge(
				$violations,
				self::validate_value(
					$child_schema,
					$v,
					$label === 'body' ? "body field {$key}" : "{$label}.{$key}",
					false,
					$options
				)
			);
		}

		return $violations;
	}

	/**
	 * Try each branch of anyOf / oneOf. Pass if any branch reports zero
	 * violations. Otherwise return the violations from the branch that came
	 * closest (fewest errors) so the message stays useful.
	 *
	 * @param array<int, mixed>          $branches Raw branch schemas.
	 * @param mixed                      $value    Value under test.
	 * @param string                     $label    Context label.
	 * @param array{forbid_extras?:bool} $options  Validator knobs.
	 * @return array<int, string>
	 */
	private static function validate_any_of( array $branches, $value, string $label, array $options ): array {
		$best = null;
		foreach ( $branches as $branch ) {
			$resolved = self::resolve( $branch );
			$errs     = self::validate_value( $resolved, $value, $label, false, $options );
			if ( count( $errs ) === 0 ) {
				return array();
			}
			if ( $best === null || count( $errs ) < count( $best ) ) {
				$best = $errs;
			}
		}
		return $best === null ? array() : $best;
	}

	/**
	 * Validate a single value (scalar, object, or array) against a resolved
	 * schema. The string $coerce flag relaxes type checks for path/query
	 * params: those arrive as strings even when the spec types them numeric.
	 *
	 * @param array<string, mixed>       $schema    Resolved schema.
	 * @param mixed                      $value     Value under test.
	 * @param string                     $label     Context label.
	 * @param bool                        $coerce    True for path/query (string-coerced).
	 * @param array{forbid_extras?:bool} $options   Validator knobs.
	 * @return array<int, string>
	 */
	private static function validate_value( array $schema, $value, string $label, bool $coerce = false, array $options = array() ): array {
		$violations = array();

		if ( isset( $schema['anyOf'] ) && is_array( $schema['anyOf'] ) ) {
			return self::validate_any_of( $schema['anyOf'], $value, $label, $options );
		}
		if ( isset( $schema['oneOf'] ) && is_array( $schema['oneOf'] ) ) {
			return self::validate_any_of( $schema['oneOf'], $value, $label, $options );
		}

		$type = isset( $schema['type'] ) ? (string) $schema['type'] : '';

		// Object: recurse via validate_object.
		if ( $type === 'object' || ( $type === '' && isset( $schema['properties'] ) ) ) {
			return self::validate_object( $schema, $value, $label, $options );
		}

		// Array: validate items.
		if ( $type === 'array' ) {
			if ( ! is_array( $value ) ) {
				$violations[] = "{$label} expected array, got " . gettype( $value );
				return $violations;
			}
			$items = isset( $schema['items'] ) ? self::resolve( $schema['items'] ) : array();
			foreach ( $value as $i => $item ) {
				$violations = array_merge(
					$violations,
					self::validate_value( $items, $item, "{$label}[{$i}]", $coerce, $options )
				);
			}
			return $violations;
		}

		// Scalar type checks.
		if ( $type === 'string' ) {
			if ( ! is_string( $value ) && ! ( $coerce && ( is_numeric( $value ) || is_bool( $value ) ) ) ) {
				$violations[] = "{$label} expected string, got " . self::describe_type( $value );
				return $violations;
			}
			$str = (string) $value;
			if ( isset( $schema['pattern'] ) && is_string( $schema['pattern'] ) ) {
				// Use \x07 as the regex delimiter so we never have to escape the
				// `/` that shows up in IANA timezone patterns. Spec patterns
				// already escape their `/` as `\/`; double-escaping breaks the
				// regex with "Unknown modifier".
				$re = "\x07" . $schema['pattern'] . "\x07";
				if ( @preg_match( $re, $str ) !== 1 ) {
					$violations[] = "{$label} does not match pattern {$schema['pattern']} (got: {$str})";
				}
			}
			if ( isset( $schema['enum'] ) && is_array( $schema['enum'] ) ) {
				$enum = array_map( 'strval', $schema['enum'] );
				if ( ! in_array( $str, $enum, true ) ) {
					$violations[] = "{$label} value '{$str}' is not in enum [" . implode( ',', $enum ) . ']';
				}
			}
			if ( isset( $schema['minLength'] ) && is_numeric( $schema['minLength'] ) && mb_strlen( $str ) < (int) $schema['minLength'] ) {
				$violations[] = "{$label} shorter than minLength {$schema['minLength']} (got length " . mb_strlen( $str ) . ')';
			}
			if ( isset( $schema['maxLength'] ) && is_numeric( $schema['maxLength'] ) && mb_strlen( $str ) > (int) $schema['maxLength'] ) {
				$violations[] = "{$label} longer than maxLength {$schema['maxLength']}";
			}
			return $violations;
		}

		if ( $type === 'integer' ) {
			$ok_native = is_int( $value );
			$ok_string = $coerce && is_string( $value ) && preg_match( '/^-?\d+$/', $value ) === 1;
			if ( ! $ok_native && ! $ok_string ) {
				$violations[] = "{$label} expected integer, got " . self::describe_type( $value );
				return $violations;
			}
			$num        = (int) $value;
			$violations = array_merge( $violations, self::check_range( $schema, $num, $label ) );
			return $violations;
		}

		if ( $type === 'number' ) {
			$ok_native = is_int( $value ) || is_float( $value );
			$ok_string = $coerce && is_string( $value ) && is_numeric( $value );
			if ( ! $ok_native && ! $ok_string ) {
				$violations[] = "{$label} expected number, got " . self::describe_type( $value );
				return $violations;
			}
			$num        = (float) $value;
			$violations = array_merge( $violations, self::check_range( $schema, $num, $label ) );
			return $violations;
		}

		if ( $type === 'boolean' ) {
			if ( ! is_bool( $value ) && ! ( $coerce && in_array( $value, array( 'true', 'false', '1', '0', 1, 0 ), true ) ) ) {
				$violations[] = "{$label} expected boolean, got " . self::describe_type( $value );
			}
			return $violations;
		}

		// Untyped or unknown: accept.
		return $violations;
	}

	/**
	 * Apply minimum / maximum / exclusive variants.
	 *
	 * @param array<string, mixed> $schema Schema.
	 * @param int|float            $num    Value.
	 * @param string               $label  Context.
	 * @return array<int, string>
	 */
	private static function check_range( array $schema, $num, string $label ): array {
		$out = array();
		if ( isset( $schema['minimum'] ) && is_numeric( $schema['minimum'] ) && $num < (float) $schema['minimum'] ) {
			$out[] = "{$label} below minimum {$schema['minimum']} (got {$num})";
		}
		if ( isset( $schema['maximum'] ) && is_numeric( $schema['maximum'] ) && $num > (float) $schema['maximum'] ) {
			$out[] = "{$label} above maximum {$schema['maximum']} (got {$num})";
		}
		return $out;
	}

	/**
	 * Human-readable type name for error messages.
	 *
	 * @param mixed $value Anything.
	 * @return string
	 */
	private static function describe_type( $value ): string {
		if ( is_int( $value ) ) {
			return 'integer';
		}
		if ( is_float( $value ) ) {
			return 'number';
		}
		if ( is_bool( $value ) ) {
			return 'boolean';
		}
		if ( is_string( $value ) ) {
			return 'string';
		}
		if ( is_array( $value ) ) {
			return 'array';
		}
		if ( null === $value ) {
			return 'null';
		}
		return gettype( $value );
	}
}
