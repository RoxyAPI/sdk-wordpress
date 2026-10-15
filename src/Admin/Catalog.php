<?php
/**
 * Catalog: library presentation of the generated reading manifest.
 *
 * Projects the generated builder manifest into the library's existing row
 * shape. The manifest owns membership, hero collisions and reading order;
 * endpoint and hero registries retain their original library copy and samples.
 * Domain presentation comes from the generated Domains registry.
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
	 *   form_only: bool,
	 *   ttl: int,
	 * }>
	 */
	public static function all(): array {
		$manifest  = \RoxyAPI\Generated\Builder\Manifest::all();
		$heroes    = \RoxyAPI\Generated\Heroes\Manifest::all();
		$endpoints = \RoxyAPI\Generated\Endpoints::all();
		$domains   = self::domain_map();
		$rows      = array();
		foreach ( $manifest['entries'] as $entry ) {
			$hero        = $entry['hero'] ? $heroes[ $entry['tag'] ] : null;
			$op_id       = $hero ? $hero['operation_id'] : $entry['operationId'];
			$endpoint    = $endpoints[ $op_id ] ?? array();
			$domain_info = $domains[ $entry['domain'] ] ?? array(
				'label'  => 'Other',
				'slug'   => 'other',
				'accent' => 'neutral',
			);
			$examples    = $hero ? array() : ( $endpoint['attributes'] ?? array() );
			$rows[]      = array(
				'operationId' => $op_id,
				'tag'         => $entry['tag'],
				'title'       => $hero ? $hero['title'] : ( $endpoint['display_name'] ?? $op_id ),
				'description' => $hero ? $hero['description'] : ( $endpoint['summary'] ?? '' ),
				'domain'      => $domain_info['label'],
				'domain_slug' => $domain_info['slug'],
				'accent'      => $domain_info['accent'],
				'sample'      => $hero ? $hero['code'] : self::build_sample( $entry['tag'], $examples ),
				'attributes'  => $examples,
				'hero'        => $entry['hero'],
				'form_only'   => $hero ? false : $entry['formOnly'],
				'ttl'         => (int) ( $endpoint['ttl'] ?? 0 ),
			);
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
	 *   form_only: bool,
	 *   ttl: int,
	 * }>>
	 */
	public static function grouped(): array {
		$ordered = array();
		foreach ( self::all() as $row ) {
			$ordered[ $row['domain_slug'] ][] = $row;
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
			$parts[] = $name . '=' . self::quote_attribute( (string) $value );
		}
		return '[' . implode( ' ', $parts ) . ']';
	}

	/**
	 * Quote one shortcode attribute value. Shared contract and edge cases live
	 * in tests/fixtures/shortcode-attributes.json (also read by check-builder).
	 *
	 * Always use double quotes: WordPress does not support backslash-escaped
	 * quote delimiters. Encode markup delimiters and ampersands as entities,
	 * and double backslashes because shortcode_parse_atts() strips C escapes.
	 * Entities remain literal entities in parsed values; this is shortcode
	 * text escaping, not a lossless transport for arbitrary JSON or HTML.
	 * Values may contain Unicode text and whitespace; callers omit empty values.
	 *
	 * @param string $value Attribute value.
	 * @return string Quoted attribute text.
	 */
	public static function quote_attribute( string $value ): string {
		return '"' . strtr(
			$value,
			array(
				'&'  => '&amp;',
				'"'  => '&quot;',
				"'"  => '&#039;',
				'['  => '&#91;',
				']'  => '&#93;',
				'<'  => '&lt;',
				'>'  => '&gt;',
				'\\' => '\\\\',
			)
		) . '"';
	}
}
