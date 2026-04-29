<?php
/**
 * Generic renderer for auto-generated shortcodes and blocks.
 *
 * Walks the API response and emits a layered card: header, lede, scalar
 * fields, tables for uniform object lists, and collapsed details for deep
 * nested content. Heuristics are field-name based and conservative; an
 * unrecognised shape still renders as a plain section list.
 *
 * Class hooks emitted (see assets/css/frontend.css):
 *   .roxyapi-card / .roxyapi-card-header / .roxyapi-card-title
 *   .roxyapi-card-meta / .roxyapi-card-lede
 *   .roxyapi-section / .roxyapi-section-title
 *   .roxyapi-fields / .roxyapi-field
 *   .roxyapi-table / .roxyapi-list / .roxyapi-details
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GenericRenderer {

	/**
	 * Field-name heuristics. Lowercased; matched after snake/camel collapse.
	 */
	private const TITLE_KEYS      = array( 'name', 'title', 'sign', 'card', 'symbol_name', 'symbolname' );
	private const SUBTITLE_KEYS   = array( 'tagline' );
	private const LEDE_KEYS       = array(
		'meaning',
		'message',
		'summary',
		'description',
		'interpretation',
		'analysis',
		'overview',
		'reading',
		'effect',
		'characteristics',
		'advice',
		'answer',
		'daily_message',
		'dailymessage',
		'core_message',
		'coremessage',
		'additional_insights',
		'additionalinsights',
	);
	private const QUOTE_KEYS      = array( 'affirmation', 'mantra', 'motto', 'quote' );
	private const META_KEYS       = array( 'date', 'time', 'datetime', 'date_time', 'letter', 'number', 'lang', 'language' );
	private const SUPPRESS_NAMED  = array( 'id', '_id', 'slug', 'key' );
	private const SUPPRESS_ALWAYS = array( 'seed' );

	/**
	 * Field names that leak internal/diagnostic data and should not surface
	 * in consumer output. Matched after lowercase + underscore collapse.
	 *
	 * `calculation` — raw numerology / astrology math like "1+2=3, Day: 10 → 1+0 = 1"
	 *                 that demos how a number was derived. Useful for engineers,
	 *                 useless for the visitor reading their own chart.
	 * `type`        — discriminator field with values like "single" / "general" /
	 *                 "primary" that index a polymorphic schema. Visible in the
	 *                 raw response, meaningless on a card.
	 * `position`    — pinnacle / challenge index 1..4. Captured implicitly by
	 *                 the row order; printing it as a separate column is noise.
	 * `*_count` / `count` — denormalised counts that match the parent list
	 *                       length. Always equals what the renderer already shows.
	 */
	private const SUPPRESS_NOISE = array(
		'calculation',
		'calculations',
		'type',
		'position',
		'count',
		'total_count',
		'totalcount',
		'has_karmic_debt',
		'haskarmicdebt',
		'has_master_number',
		'hasmasternumber',
		'is_master',
		'ismaster',
		// Pagination metadata: visitor cards never paginate. The API may
		// return total/limit/offset/page even for single-page responses;
		// surfacing them as "Total: 86, Limit: 20, Offset: 0" reads as a
		// database admin tool, not a reading.
		'total',
		'limit',
		'offset',
		'page',
		'page_size',
		'pagesize',
		'per_page',
		'perpage',
	);

	/**
	 * Field-name prefixes whose boolean-true values render as a small badge
	 * rather than a "Has karmic debt: 1" key/value pair. Boolean-false values
	 * for these fields are dropped entirely (absence is not noteworthy).
	 *
	 * Caller can still surface the underlying value via a custom template if
	 * the badge convention does not fit a specific reading.
	 */
	private const BADGE_TRUE_PREFIXES = array( 'has_', 'is_' );

	/** Field names whose string value is treated as an image URL. */
	private const IMAGE_KEYS = array( 'image', 'image_url', 'imageurl', 'photo', 'picture', 'thumbnail', 'thumbnail_url', 'cover', 'avatar' );

	/** Trailing extensions (case-insensitive) that mark a URL as an image. */
	private const IMAGE_EXTS = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg' );

	/** Sections with more than this many keys collapse into <details>. */
	private const DETAILS_THRESHOLD = 8;

	/** Tables with more than this many rows collapse into <details>. */
	private const TABLE_DETAILS_THRESHOLD = 12;

	/**
	 * Render an API response as a Roxy card.
	 *
	 * @param string               $operation_id The OpenAPI operation ID.
	 * @param array<string, mixed> $data         API response data.
	 * @return string
	 */
	public static function render( string $operation_id, array $data ): string {
		if ( empty( $data ) ) {
			return '';
		}
		$class = trim( 'roxyapi-card roxyapi-' . sanitize_html_class( $operation_id ) );
		return '<div class="' . esc_attr( $class ) . '">'
			. self::render_object( $data, 0 )
			. Disclaimer::render()
			. Attribution::credit_link( $operation_id )
			. Attribution::jsonld( $operation_id, $data )
			. '</div>';
	}

	/**
	 * Render a single object level. Recursion entry-point for nested children.
	 *
	 * @param array<string, mixed> $data       Associative array (object).
	 * @param int                  $depth      Recursion depth (0 = top-level card).
	 * @param string               $parent_key Key under which $data was nested in its
	 *                                         parent object. Used to drop redundant
	 *                                         scalar children whose value just echoes
	 *                                         this key (kundli rashi-of-rashi pattern).
	 * @return string
	 */
	private static function render_object( array $data, int $depth, string $parent_key = '' ): string {
		$data = self::suppress( $data );
		// Drop scalar fields whose lowercase string value just echoes the
		// parent section's key. Catches the kundli rashi pattern where the
		// API response wraps each rashi as `{aries: {rashi: 'aries', ...}}`
		// — the inner `rashi: aries` field is redundant noise under a
		// section already titled "Aries". Spec-agnostic: works for any
		// endpoint that name-stamps a child object with its parent key.
		if ( $parent_key !== '' ) {
			$parent_lc = strtolower( $parent_key );
			foreach ( $data as $k => $v ) {
				if ( is_scalar( $v ) && strtolower( (string) $v ) === $parent_lc ) {
					unset( $data[ $k ] );
				}
			}
		}
		if ( empty( $data ) ) {
			return '';
		}

		list( $title, $data )    = self::pluck_first( $data, self::TITLE_KEYS );
		list( $subtitle, $data ) = self::pluck_first( $data, self::SUBTITLE_KEYS );
		list( $lede, $data )     = self::pluck_first( $data, self::LEDE_KEYS );
		list( $quote, $data )    = self::pluck_first( $data, self::QUOTE_KEYS );

		// Pluck the primary image up to the header so it sits next to the
		// title rather than getting lost in the field grid.
		$image_html = '';
		foreach ( self::IMAGE_KEYS as $image_key ) {
			list( $img_val, $data ) = self::pluck( $data, $image_key );
			if ( is_string( $img_val ) && $img_val !== '' ) {
				$image_html = self::render_image( $img_val, is_string( $title ) ? (string) $title : '' );
				break;
			}
		}

		$meta_parts = array();
		foreach ( self::META_KEYS as $meta_key ) {
			list( $val, $data ) = self::pluck( $data, $meta_key );
			if ( is_scalar( $val ) && (string) $val !== '' ) {
				$meta_parts[] = esc_html( self::humanize( $meta_key ) ) . ': ' . esc_html( (string) $val );
			}
		}

		$out          = '';
		$has_subtitle = $subtitle !== null && is_scalar( $subtitle ) && (string) $subtitle !== '';
		if ( $title !== null || $has_subtitle || ! empty( $meta_parts ) || $image_html !== '' ) {
			$out .= '<header class="roxyapi-card-header">';
			if ( $image_html !== '' ) {
				$out .= $image_html;
			}
			if ( $title !== null && is_scalar( $title ) ) {
				$tag  = $depth === 0 ? 'h3' : 'h4';
				$out .= '<' . $tag . ' class="roxyapi-card-title">' . esc_html( (string) $title ) . '</' . $tag . '>';
			}
			if ( $subtitle !== null && is_scalar( $subtitle ) && (string) $subtitle !== '' ) {
				$out .= '<p class="roxyapi-card-subtitle">' . esc_html( (string) $subtitle ) . '</p>';
			}
			if ( ! empty( $meta_parts ) ) {
				$out .= '<p class="roxyapi-card-meta">' . implode( ' · ', $meta_parts ) . '</p>';
			}
			$out .= '</header>';
		}

		if ( $lede !== null && is_scalar( $lede ) && (string) $lede !== '' ) {
			$out .= '<p class="roxyapi-card-lede">' . esc_html( (string) $lede ) . '</p>';
		}

		if ( $quote !== null && is_scalar( $quote ) && (string) $quote !== '' ) {
			$out .= '<blockquote class="roxyapi-quote">' . esc_html( (string) $quote ) . '</blockquote>';
		}

		// Bucket remaining keys: scalars first, then uniform-object lists (tables),
		// then everything else (sections / details).
		$scalars  = array();
		$tables   = array();
		$sections = array();
		foreach ( $data as $key => $value ) {
			if ( is_scalar( $value ) ) {
				if ( (string) $value !== '' ) {
					$scalars[ $key ] = $value;
				}
				continue;
			}
			if ( $value === null ) {
				continue;
			}
			if ( is_array( $value ) ) {
				if ( empty( $value ) ) {
					continue;
				}
				if ( self::is_uniform_object_list( $value ) ) {
					$tables[ $key ] = $value;
					continue;
				}
				$sections[ $key ] = $value;
			}
		}

		if ( ! empty( $scalars ) ) {
			$out .= '<dl class="roxyapi-fields">';
			foreach ( $scalars as $k => $v ) {
				$out .= '<div class="roxyapi-field">';
				$out .= '<dt>' . esc_html( self::humanize( (string) $k ) ) . '</dt>';
				$out .= '<dd>' . self::render_scalar( (string) $k, $v ) . '</dd>';
				$out .= '</div>';
			}
			$out .= '</dl>';
		}

		foreach ( $tables as $k => $rows ) {
			$out .= self::render_table( (string) $k, $rows );
		}

		foreach ( $sections as $k => $value ) {
			$out .= self::render_section( (string) $k, $value, $depth + 1 );
		}

		return $out;
	}

	/**
	 * Render a key / array pair as a section, collapsing into <details> when
	 * the content is large.
	 *
	 * @param string $key   Field name.
	 * @param mixed  $value Array value (object or list).
	 * @param int    $depth Recursion depth for the inner render.
	 * @return string
	 */
	private static function render_section( string $key, $value, int $depth ): string {
		if ( ! is_array( $value ) ) {
			return '';
		}

		$title = self::humanize( $key );

		if ( self::is_list( $value ) ) {
			$body  = self::render_list( $value, $depth );
			$count = count( $value );
		} else {
			$body  = self::render_object( $value, $depth, $key );
			$count = count( $value );
		}

		if ( $body === '' ) {
			return '';
		}

		if ( $count > self::DETAILS_THRESHOLD ) {
			return '<details class="roxyapi-details"><summary>' . esc_html( $title ) . '</summary>' . $body . '</details>';
		}

		return '<section class="roxyapi-section">'
			. '<h4 class="roxyapi-section-title">' . esc_html( $title ) . '</h4>'
			. $body
			. '</section>';
	}

	/**
	 * Render an indexed list. Scalars become a ul; objects recurse; nested
	 * lists recurse.
	 *
	 * @param array<int, mixed> $items Indexed list.
	 * @param int               $depth Recursion depth.
	 * @return string
	 */
	private static function render_list( array $items, int $depth ): string {
		$rendered = array();
		foreach ( $items as $item ) {
			if ( is_scalar( $item ) ) {
				if ( (string) $item === '' ) {
					continue;
				}
				$rendered[] = '<li>' . self::render_scalar( '', $item ) . '</li>';
				continue;
			}
			if ( ! is_array( $item ) ) {
				continue;
			}
			if ( empty( $item ) ) {
				continue;
			}
			if ( self::is_list( $item ) ) {
				$nested = self::render_list( $item, $depth + 1 );
				if ( $nested !== '' ) {
					$rendered[] = '<li>' . $nested . '</li>';
				}
				continue;
			}
			$nested = self::render_object( $item, $depth + 1 );
			if ( $nested !== '' ) {
				$rendered[] = '<li>' . $nested . '</li>';
			}
		}
		if ( empty( $rendered ) ) {
			return '';
		}
		return '<ul class="roxyapi-list">' . implode( '', $rendered ) . '</ul>';
	}

	/**
	 * Render a list of uniform objects as an HTML table. Wraps in <details>
	 * when the row count crosses TABLE_DETAILS_THRESHOLD.
	 *
	 * @param string                           $key  Field name.
	 * @param array<int, array<string, mixed>> $rows Row data.
	 * @return string
	 */
	private static function render_table( string $key, array $rows ): string {
		$cols = array();
		foreach ( $rows as $row ) {
			foreach ( array_keys( $row ) as $col ) {
				$col_str   = (string) $col;
				$col_lc    = strtolower( $col_str );
				$collapsed = str_replace( array( '_', '-' ), '', $col_lc );
				if ( in_array( $col_lc, self::SUPPRESS_ALWAYS, true ) || in_array( $collapsed, self::SUPPRESS_ALWAYS, true ) ) {
					continue;
				}
				// Tables share the noise filter with the field grid: a
				// `position` column matches the row order, `type` discriminates
				// a polymorphic schema, `calculation` leaks internal math.
				// None belong in a consumer-facing card.
				if ( in_array( $col_lc, self::SUPPRESS_NOISE, true ) || in_array( $collapsed, self::SUPPRESS_NOISE, true ) ) {
					continue;
				}
				if ( ! in_array( $col_str, $cols, true ) ) {
					$cols[] = $col_str;
				}
			}
		}
		if ( empty( $cols ) ) {
			return '';
		}

		$out = '<table class="roxyapi-table"><thead><tr>';
		foreach ( $cols as $col ) {
			$out .= '<th>' . esc_html( self::humanize( $col ) ) . '</th>';
		}
		$out .= '</tr></thead><tbody>';
		foreach ( $rows as $row ) {
			$out .= '<tr>';
			foreach ( $cols as $col ) {
				$val = isset( $row[ $col ] ) ? $row[ $col ] : '';
				if ( is_scalar( $val ) ) {
					$out .= '<td>' . self::render_scalar( $col, $val ) . '</td>';
				} elseif ( is_array( $val ) && ! empty( $val ) ) {
					if ( self::is_list( $val ) ) {
						$items = array();
						foreach ( $val as $vv ) {
							if ( is_scalar( $vv ) && (string) $vv !== '' ) {
								$items[] = esc_html( (string) $vv );
							}
						}
						$out .= '<td>' . implode( ', ', $items ) . '</td>';
					} else {
						$flat = array();
						foreach ( $val as $vk => $vv ) {
							if ( is_scalar( $vv ) ) {
								$flat[] = esc_html( self::humanize( (string) $vk ) ) . ': ' . esc_html( (string) $vv );
							}
						}
						$out .= '<td>' . implode( ', ', $flat ) . '</td>';
					}
				} else {
					$out .= '<td></td>';
				}
			}
			$out .= '</tr>';
		}
		$out .= '</tbody></table>';

		$wrap_open  = '<section class="roxyapi-section">'
			. '<h4 class="roxyapi-section-title">' . esc_html( self::humanize( $key ) ) . ' (' . count( $rows ) . ')</h4>';
		$wrap_close = '</section>';

		if ( count( $rows ) > self::TABLE_DETAILS_THRESHOLD ) {
			return '<details class="roxyapi-details"><summary>'
				. esc_html( self::humanize( $key ) ) . ' (' . count( $rows ) . ')'
				. '</summary>' . $out . '</details>';
		}

		return $wrap_open . $out . $wrap_close;
	}

	/**
	 * Drop diagnostic-only keys when a nicer sibling exists, and strip the
	 * generic noise patterns (calculation / type / position / has_*-when-false)
	 * that flow in from any well-typed OpenAPI schema and have no consumer
	 * value on a rendered card.
	 *
	 * @param array<string, mixed> $data Object data.
	 * @return array<string, mixed>
	 */
	private static function suppress( array $data ): array {
		$has_named = false;
		foreach ( self::TITLE_KEYS as $tk ) {
			if ( isset( $data[ $tk ] ) ) {
				$has_named = true;
				break;
			}
		}
		$out = array();
		foreach ( $data as $key => $value ) {
			$lc        = strtolower( (string) $key );
			$collapsed = str_replace( array( '_', '-' ), '', $lc );
			if ( in_array( $lc, self::SUPPRESS_ALWAYS, true ) || in_array( $collapsed, self::SUPPRESS_ALWAYS, true ) ) {
				continue;
			}
			if ( $has_named && ( in_array( $lc, self::SUPPRESS_NAMED, true ) || in_array( $collapsed, self::SUPPRESS_NAMED, true ) ) ) {
				continue;
			}
			if ( in_array( $lc, self::SUPPRESS_NOISE, true ) || in_array( $collapsed, self::SUPPRESS_NOISE, true ) ) {
				continue;
			}
			// Boolean-false on `is_*` / `has_*` fields is silence, not data.
			// Boolean-true on those same fields surfaces as a badge inside
			// render_scalar so it reads as "Karmic debt" not "Has karmic debt: 1".
			if ( is_bool( $value ) && self::is_badge_field( $lc ) && ! $value ) {
				continue;
			}
			$out[ $key ] = $value;
		}
		return $out;
	}

	/**
	 * True when the field name matches a known boolean-as-badge convention.
	 *
	 * @param string $key_lc Already-lowercased field name.
	 * @return bool
	 */
	private static function is_badge_field( string $key_lc ): bool {
		foreach ( self::BADGE_TRUE_PREFIXES as $prefix ) {
			if ( strpos( $key_lc, $prefix ) === 0 ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Take the first key from $candidates that exists in $data AND has a
	 * scalar value, then pluck it. Title/lede/quote slots only render
	 * scalars (header H3, lede paragraph, blockquote); when a key like
	 * `meaning` is an object, leaving it in $data lets render_section
	 * surface its real contents (description, strengths, etc.) instead
	 * of silently dropping it after a failed pluck.
	 *
	 * @param array<string, mixed> $data       Source data.
	 * @param array<int, string>   $candidates Lowercased key names to try.
	 * @return array{0: mixed, 1: array<string, mixed>} [scalar-value-or-null, remaining-data]
	 */
	private static function pluck_first( array $data, array $candidates ): array {
		foreach ( $candidates as $cand ) {
			foreach ( $data as $key => $value ) {
				if ( strtolower( (string) $key ) !== $cand ) {
					continue;
				}
				if ( ! is_scalar( $value ) ) {
					// Object/array value at a "title-ish" key — leave it
					// in place so render_section picks it up below.
					continue;
				}
				unset( $data[ $key ] );
				return array( $value, $data );
			}
		}
		return array( null, $data );
	}

	/**
	 * Pluck a single key (case-insensitive) from $data.
	 *
	 * @param array<string, mixed> $data Source data.
	 * @param string               $key  Target key (lowercased).
	 * @return array{0: mixed, 1: array<string, mixed>}
	 */
	private static function pluck( array $data, string $key ): array {
		foreach ( $data as $k => $v ) {
			if ( strtolower( (string) $k ) === $key ) {
				unset( $data[ $k ] );
				return array( $v, $data );
			}
		}
		return array( null, $data );
	}

	/**
	 * True when an array is a list of associative arrays (objects) and at
	 * least 2 entries. Used to decide table vs section rendering.
	 *
	 * @param array<mixed> $value Candidate.
	 * @return bool
	 */
	private static function is_uniform_object_list( array $value ): bool {
		if ( count( $value ) < 2 ) {
			return false;
		}
		if ( ! self::is_list( $value ) ) {
			return false;
		}
		$first_keys = null;
		foreach ( $value as $item ) {
			if ( ! is_array( $item ) ) {
				return false;
			}
			if ( self::is_list( $item ) ) {
				return false;
			}
			if ( empty( $item ) ) {
				return false;
			}
			$keys = array_keys( $item );
			if ( $first_keys === null ) {
				$first_keys = $keys;
			} elseif ( count( array_intersect( $first_keys, $keys ) ) < 1 ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Convert a programmatic field name into a human-readable label.
	 * Drops boilerplate prefixes (`has_`, `is_`) so badge fields read as
	 * the property they describe rather than a question.
	 *
	 * @param string $field Raw field name (camelCase, snake_case, kebab-case).
	 * @return string
	 */
	private static function humanize( string $field ): string {
		$lc = strtolower( $field );
		foreach ( self::BADGE_TRUE_PREFIXES as $prefix ) {
			if ( strpos( $lc, $prefix ) === 0 ) {
				$field = substr( $field, strlen( $prefix ) );
				break;
			}
		}
		$with_spaces = preg_replace( '/(?<!^)([A-Z])/', ' $1', $field );
		$with_spaces = preg_replace( '/([A-Za-z])([0-9])/', '$1 $2', (string) $with_spaces );
		$with_spaces = str_replace( array( '_', '-' ), ' ', (string) $with_spaces );
		$lower       = strtolower( trim( (string) $with_spaces ) );
		return $lower === '' ? '' : ( strtoupper( $lower[0] ) . substr( $lower, 1 ) );
	}

	/**
	 * True when the array is sequentially indexed (a JSON list).
	 *
	 * @param array<mixed> $arr Candidate.
	 * @return bool
	 */
	private static function is_list( array $arr ): bool {
		if ( empty( $arr ) ) {
			return false;
		}
		return array_keys( $arr ) === range( 0, count( $arr ) - 1 );
	}

	/**
	 * Render a scalar by inferring its semantic type from the field name and
	 * value pattern. Same logic applied everywhere a scalar is emitted (field
	 * grid, list item, table cell) so the heuristics stay shape-agnostic.
	 *
	 * Detection order: image (key name OR value extension) → URL link →
	 * plain escaped text.
	 *
	 * @param string                $key   Field name (may be empty for list items).
	 * @param string|int|float|bool $value Scalar value.
	 * @return string Pre-escaped HTML.
	 */
	private static function render_scalar( string $key, $value ): string {
		if ( ! is_scalar( $value ) ) {
			return '';
		}
		// Booleans surface as a badge on `is_*` / `has_*` fields and as
		// localised yes/no everywhere else. The raw "1" / "" PHP cast is
		// confusing on a consumer card.
		if ( is_bool( $value ) ) {
			if ( self::is_badge_field( strtolower( $key ) ) ) {
				return $value
					? '<span class="roxyapi-badge">' . esc_html__( 'Yes', 'roxyapi' ) . '</span>'
					: '';
			}
			return esc_html( $value ? __( 'Yes', 'roxyapi' ) : __( 'No', 'roxyapi' ) );
		}
		// Floats with debug precision (e.g. ephemeris longitude
		// `54.662608325431`) are unreadable on a card. Round to 2 decimals
		// and trim trailing zeros so `12.50` shows as `12.5` and `4.00`
		// shows as `4`. Integer-valued floats stay integers.
		if ( is_float( $value ) ) {
			$value = self::format_float( $value );
		}
		$value_str = (string) $value;
		if ( $value_str === '' ) {
			return '';
		}

		if ( self::looks_like_image( $key, $value_str ) ) {
			return self::render_image( $value_str, '' );
		}
		if ( self::looks_like_url( $value_str ) ) {
			return self::render_link( $value_str );
		}
		return esc_html( $value_str );
	}

	/**
	 * Round a float to 2 decimal places, drop trailing zeros, and return
	 * the string. `54.662608325431` → `"54.66"`, `12.50` → `"12.5"`,
	 * `4.0` → `"4"`. Used everywhere a float lands on a card so the
	 * consumer never sees ephemeris-grade precision.
	 *
	 * @param float $value Raw float.
	 * @return string
	 */
	private static function format_float( float $value ): string {
		$rounded = round( $value, 2 );
		// Sub-precision negatives like `-1.2e-5` round to `-0.0`, which
		// stringifies as the surprising "-0". Coerce to plain zero.
		if ( $rounded === 0.0 || $rounded === -0.0 ) {
			return '0';
		}
		$str = (string) $rounded;
		if ( strpos( $str, '.' ) !== false ) {
			$str = rtrim( rtrim( $str, '0' ), '.' );
		}
		return $str;
	}

	/**
	 * True when a scalar should render as `<img>`. Either the field name is
	 * a known image-key OR the value is an HTTP(S) URL with an image extension.
	 *
	 * @param string $key   Field name.
	 * @param string $value Scalar value.
	 * @return bool
	 */
	private static function looks_like_image( string $key, string $value ): bool {
		$key_lc = strtolower( $key );
		if ( in_array( $key_lc, self::IMAGE_KEYS, true ) ) {
			return self::looks_like_url( $value );
		}
		if ( ! self::looks_like_url( $value ) ) {
			return false;
		}
		$path = wp_parse_url( $value, PHP_URL_PATH );
		if ( ! is_string( $path ) ) {
			return false;
		}
		$ext = strtolower( (string) pathinfo( $path, PATHINFO_EXTENSION ) );
		return in_array( $ext, self::IMAGE_EXTS, true );
	}

	/**
	 * True when a value looks like an HTTP(S) URL.
	 *
	 * @param string $value Scalar value to test.
	 * @return bool
	 */
	private static function looks_like_url( string $value ): bool {
		return preg_match( '#^https?://[^\s<>"\']+$#i', $value ) === 1;
	}

	/**
	 * Render an `<img>` with a friendly alt text. Lazy + async by default.
	 *
	 * @param string $url Image URL.
	 * @param string $alt Alt text (defaults to empty for decorative behaviour).
	 * @return string
	 */
	private static function render_image( string $url, string $alt ): string {
		return '<img class="roxyapi-image" src="' . esc_url( $url ) . '"'
			. ' alt="' . esc_attr( $alt ) . '"'
			. ' loading="lazy" decoding="async">';
	}

	/**
	 * Render an `<a>` for a non-image URL. Always opens in a new tab with
	 * `noopener noreferrer` since these are third-party-content URLs.
	 *
	 * @param string $url Target URL.
	 * @return string
	 */
	private static function render_link( string $url ): string {
		return '<a class="roxyapi-link" href="' . esc_url( $url ) . '"'
			. ' rel="noopener noreferrer" target="_blank">'
			. esc_html( $url )
			. '</a>';
	}
}
