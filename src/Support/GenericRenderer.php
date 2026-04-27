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
	 * @param array<string, mixed> $data  Associative array (object).
	 * @param int                  $depth Recursion depth (0 = top-level card).
	 * @return string
	 */
	private static function render_object( array $data, int $depth ): string {
		$data = self::suppress( $data );
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
			$body  = self::render_object( $value, $depth );
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
				$col_str = (string) $col;
				if ( in_array( strtolower( $col_str ), self::SUPPRESS_ALWAYS, true ) ) {
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
	 * Drop diagnostic-only keys when a nicer sibling exists.
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
			$lc = strtolower( (string) $key );
			if ( in_array( $lc, self::SUPPRESS_ALWAYS, true ) ) {
				continue;
			}
			if ( $has_named && in_array( $lc, self::SUPPRESS_NAMED, true ) ) {
				continue;
			}
			$out[ $key ] = $value;
		}
		return $out;
	}

	/**
	 * Take the first key from $candidates that exists in $data and pluck it.
	 *
	 * @param array<string, mixed> $data       Source data.
	 * @param array<int, string>   $candidates Lowercased key names to try.
	 * @return array{0: mixed, 1: array<string, mixed>} [value-or-null, remaining-data]
	 */
	private static function pluck_first( array $data, array $candidates ): array {
		foreach ( $candidates as $cand ) {
			foreach ( $data as $key => $value ) {
				if ( strtolower( (string) $key ) === $cand ) {
					unset( $data[ $key ] );
					return array( $value, $data );
				}
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
	 *
	 * @param string $field Raw field name (camelCase, snake_case, kebab-case).
	 * @return string
	 */
	private static function humanize( string $field ): string {
		$with_spaces = preg_replace( '/(?<!^)([A-Z])/', ' $1', $field );
		$with_spaces = str_replace( array( '_', '-' ), ' ', (string) $with_spaces );
		return ucfirst( strtolower( (string) $with_spaces ) );
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
