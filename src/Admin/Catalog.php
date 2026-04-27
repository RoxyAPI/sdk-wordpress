<?php
/**
 * Catalog: single source of truth for the Shortcodes Library admin page.
 *
 * Combines the ten hand-crafted hero shortcodes from
 * {@see Onboarding::hero_shortcodes()} with the long-tail generated entries
 * from {@see \RoxyAPI\Generated\Endpoints::all()} into one normalised list.
 *
 * Domain ordering follows the brand book: astrology first (Western, then
 * Vedic), then Tarot, Numerology, I Ching, Dreams, Biorhythm, Angel Numbers,
 * Crystals. Location and Usage sit at the end. Anything unrecognised falls
 * to "Other" at the very end.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Catalog {

	/**
	 * Brand-ordered map of OpenAPI tag → admin-UI metadata. Pulled from
	 * the auto-generated `Domains` registry which is emitted from
	 * `bin/domains.json` by `npm run generate`. Add a new domain there;
	 * no PHP edits required.
	 *
	 * @return array<string, array{label: string, slug: string, accent: string}>
	 */
	private static function domain_map(): array {
		if ( ! class_exists( '\\RoxyAPI\\Generated\\Domains' ) ) {
			return array();
		}
		return \RoxyAPI\Generated\Domains::all();
	}

	/**
	 * Map a hero shortcode tag to the OpenAPI tag (domain) that owns it.
	 *
	 * Sourced from the auto-generated hero manifest so a new entry in
	 * bin/hero-config.json shows up here automatically. The map is built once
	 * per request via {@see self::hero_tag_to_domain()}.
	 *
	 * @return array<string, string>
	 */
	private static function hero_tag_to_domain(): array {
		if ( ! class_exists( '\\RoxyAPI\\Generated\\Heroes\\Manifest' ) ) {
			return array();
		}
		$out = array();
		foreach ( \RoxyAPI\Generated\Heroes\Manifest::all() as $tag => $entry ) {
			$out[ (string) $tag ] = (string) $entry['domain'];
		}
		return $out;
	}

	/**
	 * Domains used by the library tab nav, in brand order.
	 *
	 * Each entry carries a human tab label, the kebab slug used as data
	 * attribute and URL hash, the count of shortcodes in that domain (hero
	 * plus generated, deduplicated), and a brand-aligned accent color.
	 *
	 * @return array<int, array{tag: string, slug: string, count: int, accent: string}>
	 */
	public static function domains(): array {
		$grouped = self::grouped();
		$out     = array();
		foreach ( self::domain_map() as $tag => $info ) {
			$slug  = $info['slug'];
			$count = isset( $grouped[ $slug ] ) ? count( $grouped[ $slug ] ) : 0;
			if ( $count === 0 ) {
				continue;
			}
			$out[] = array(
				'tag'    => $info['label'],
				'slug'   => $slug,
				'count'  => $count,
				'accent' => $info['accent'],
			);
		}
		// Surface "Other" only if anything fell through.
		if ( isset( $grouped['other'] ) && count( $grouped['other'] ) > 0 ) {
			$out[] = array(
				'tag'    => 'Other',
				'slug'   => 'other',
				'count'  => count( $grouped['other'] ),
				'accent' => 'neutral',
			);
		}
		return $out;
	}

	/**
	 * Every shortcode in the catalog, deduplicated so each canonical tag
	 * appears exactly once. Hero rows always win when both a hero entry and
	 * a matching generated entry would map to the same tag.
	 *
	 * @return array<int, array{
	 *   operationId: string,
	 *   tag: string,
	 *   title: string,
	 *   description: string,
	 *   domain: string,
	 *   domain_slug: string,
	 *   accent: string,
	 *   sample: string,
	 *   attributes: array<string, string>,
	 *   hero: bool,
	 *   block_only: bool,
	 *   ttl: int,
	 * }>
	 */
	public static function all(): array {
		$endpoints = self::endpoints_index();
		$rows      = array();
		$seen_tags = array();

		// Hero entries first so they own their tag.
		$hero_tag_to_domain = self::hero_tag_to_domain();
		foreach ( Onboarding::hero_shortcodes() as $hero ) {
			$tag = self::extract_tag_from_sample( $hero['code'] );
			if ( $tag === '' ) {
				continue;
			}
			$op_tag      = $hero_tag_to_domain[ $tag ] ?? '';
			$domain_info = self::domain_map()[ $op_tag ] ?? array(
				'label'  => 'Other',
				'slug'   => 'other',
				'accent' => 'neutral',
			);
			$op_id       = self::find_hero_op_id( $tag );
			$ttl         = ( $op_id !== '' && isset( $endpoints[ $op_id ] ) ) ? (int) $endpoints[ $op_id ]['ttl'] : 0;

			$rows[]            = array(
				'operationId' => $op_id,
				'tag'         => $tag,
				'title'       => (string) $hero['title'],
				'description' => (string) $hero['description'],
				'domain'      => $domain_info['label'],
				'domain_slug' => $domain_info['slug'],
				'accent'      => $domain_info['accent'],
				'sample'      => (string) $hero['code'],
				'attributes'  => array(),
				'hero'        => true,
				'block_only'  => false,
				'ttl'         => $ttl,
			);
			$seen_tags[ $tag ] = true;
		}

		// Generated entries, skipping any whose tag was already taken by a hero.
		foreach ( $endpoints as $op_id => $ep ) {
			if ( ! empty( $ep['hero'] ) ) {
				// Hero ops are surfaced via Onboarding::hero_shortcodes() above.
				continue;
			}
			$shortcode_tag = 'roxy_' . self::to_snake_case( $op_id );
			if ( isset( $seen_tags[ $shortcode_tag ] ) ) {
				continue;
			}
			$op_tag      = (string) $ep['tag'];
			$domain_info = self::domain_map()[ $op_tag ] ?? array(
				'label'  => 'Other',
				'slug'   => 'other',
				'accent' => 'neutral',
			);

			$attr_examples = isset( $ep['attributes'] ) && is_array( $ep['attributes'] ) ? $ep['attributes'] : array();
			$sample        = self::build_sample( $shortcode_tag, $attr_examples );

			$rows[]                      = array(
				'operationId' => (string) $op_id,
				'tag'         => $shortcode_tag,
				'title'       => self::humanise_op_id( (string) $op_id ),
				'description' => (string) $ep['summary'],
				'domain'      => $domain_info['label'],
				'domain_slug' => $domain_info['slug'],
				'accent'      => $domain_info['accent'],
				'sample'      => $sample,
				'attributes'  => $attr_examples,
				'hero'        => false,
				'block_only'  => ! empty( $ep['block_only'] ),
				'ttl'         => (int) $ep['ttl'],
			);
			$seen_tags[ $shortcode_tag ] = true;
		}

		return $rows;
	}

	/**
	 * Grouped catalog keyed by domain slug, with domains in brand order.
	 *
	 * @return array<string, array<int, array{
	 *   operationId: string,
	 *   tag: string,
	 *   title: string,
	 *   description: string,
	 *   domain: string,
	 *   domain_slug: string,
	 *   accent: string,
	 *   sample: string,
	 *   attributes: array<string, string>,
	 *   hero: bool,
	 *   block_only: bool,
	 *   ttl: int,
	 * }>>
	 */
	public static function grouped(): array {
		// Pre-seed the result with the brand domain order so iteration in the
		// template renders sections in the right order even if a domain has
		// only a hero or only generated entries.
		$ordered = array();
		foreach ( self::domain_map() as $info ) {
			$ordered[ $info['slug'] ] = array();
		}

		foreach ( self::all() as $row ) {
			$slug = $row['domain_slug'];
			if ( ! isset( $ordered[ $slug ] ) ) {
				$ordered[ $slug ] = array();
			}
			$ordered[ $slug ][] = $row;
		}

		// Drop empty domains so the template only renders sections with cards.
		$ordered = array_filter(
			$ordered,
			static function ( $items ) {
				return count( $items ) > 0;
			}
		);

		// Sort each group: heroes first, then alphabetised by title.
		foreach ( $ordered as $slug => $items ) {
			usort(
				$items,
				static function ( $a, $b ) {
					if ( $a['hero'] !== $b['hero'] ) {
						return $a['hero'] ? -1 : 1;
					}
					return strcasecmp( $a['title'], $b['title'] );
				}
			);
			$ordered[ $slug ] = $items;
		}

		return $ordered;
	}

	/**
	 * Build a sample shortcode string from a tag plus an attribute-example map.
	 * `[roxy_xxx]` if no attributes; `[roxy_xxx attr="val"]` otherwise.
	 *
	 * @param string                $tag      Shortcode tag.
	 * @param array<string, string> $examples Attribute name → example value.
	 * @return string
	 */
	private static function build_sample( string $tag, array $examples ): string {
		if ( empty( $examples ) ) {
			return '[' . $tag . ']';
		}
		$parts = array( $tag );
		foreach ( $examples as $name => $value ) {
			if ( ! is_scalar( $value ) || (string) $value === '' ) {
				continue;
			}
			$parts[] = $name . '="' . str_replace( '"', '&quot;', (string) $value ) . '"';
		}
		return '[' . implode( ' ', $parts ) . ']';
	}

	/**
	 * Endpoints index. Wraps the generated registry in a defensive callable
	 * lookup so PHPStan stays happy if generation has not run.
	 *
	 * @return array<string, array{path: string, method: string, tag: string, summary: string, ttl: int, hero: bool, block_only: bool, attributes: array<string, string>}>
	 */
	private static function endpoints_index(): array {
		if ( ! class_exists( '\\RoxyAPI\\Generated\\Endpoints' ) ) {
			return array();
		}
		return \RoxyAPI\Generated\Endpoints::all();
	}

	/**
	 * Extract the bare shortcode tag from a sample like
	 * `[roxy_horoscope sign="aries"]`.
	 *
	 * @param string $sample The full shortcode markup.
	 * @return string The tag (e.g. "roxy_horoscope") or '' if unrecognised.
	 */
	private static function extract_tag_from_sample( string $sample ): string {
		if ( preg_match( '/^\[\s*([a-z0-9_]+)/i', $sample, $matches ) === 1 ) {
			return strtolower( $matches[1] );
		}
		return '';
	}

	/**
	 * Find the operationId (if any) that matches a given hero shortcode tag.
	 *
	 * @param string $tag Hero shortcode tag (e.g. "roxy_horoscope").
	 * @return string The operationId or '' if not in the hero map.
	 */
	private static function find_hero_op_id( string $tag ): string {
		if ( ! class_exists( '\\RoxyAPI\\Generated\\Heroes\\Manifest' ) ) {
			return '';
		}
		$entries = \RoxyAPI\Generated\Heroes\Manifest::all();
		$entry   = $entries[ $tag ] ?? null;
		if ( ! is_array( $entry ) ) {
			return '';
		}
		return (string) ( $entry['operation_id'] ?? '' );
	}

	/**
	 * Convert a camelCase / PascalCase operationId into a snake_case shortcode
	 * tag suffix. Mirrors the regex used by `bin/generate.mjs::toSnakeCase`.
	 *
	 * @param string $op_id Operation id (e.g. "getMonthlyHoroscope").
	 * @return string Snake case (e.g. "get_monthly_horoscope").
	 */
	private static function to_snake_case( string $op_id ): string {
		$with_underscores = preg_replace( '/([a-z0-9])([A-Z])/', '$1_$2', $op_id );
		$with_underscores = is_string( $with_underscores ) ? $with_underscores : $op_id;
		$cleaned          = preg_replace( '/[^a-zA-Z0-9]/', '_', $with_underscores );
		$cleaned          = is_string( $cleaned ) ? $cleaned : $with_underscores;
		return strtolower( $cleaned );
	}

	/**
	 * Humanise an operationId for display: split on caps, title-case words.
	 *
	 * Example: "getMonthlyHoroscope" -> "Get Monthly Horoscope".
	 *
	 * @param string $op_id Operation id from the OpenAPI spec.
	 * @return string Humanised label.
	 */
	private static function humanise_op_id( string $op_id ): string {
		$spaced = preg_replace( '/([a-z0-9])([A-Z])/', '$1 $2', $op_id );
		$spaced = is_string( $spaced ) ? $spaced : $op_id;
		$spaced = preg_replace( '/[_\\-]+/', ' ', $spaced );
		$spaced = is_string( $spaced ) ? $spaced : $op_id;
		$spaced = trim( $spaced );
		if ( $spaced === '' ) {
			return $op_id;
		}
		// ucwords lowercases nothing; we want the first letter of each word
		// capitalised regardless of original case.
		$parts = preg_split( '/\\s+/', $spaced );
		if ( ! is_array( $parts ) ) {
			return ucfirst( strtolower( $spaced ) );
		}
		$parts = array_map(
			static function ( $word ) {
				return ucfirst( strtolower( (string) $word ) );
			},
			$parts
		);
		return implode( ' ', $parts );
	}
}
