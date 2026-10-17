<?php
/**
 * Fills a visitor form dropdown from the collection that lists its choices.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Api\Cache;
use RoxyAPI\Api\Client;

/**
 * Server-side twin of the UI library's option picker.
 *
 * A form field whose choices the API serves as a collection (a planet, a sign, a tarot card) carries a `source` from the vendored UI model. The browser never calls the API here, so the collection is read on the server through the cache, once per language per TTL, and the field becomes an ordinary `enum`: the renderer draws it and the router validates a submission against it with no source awareness of their own. Choices are read from the response SHAPE, never its name, by the same rule as the UI library's `optionsFrom`.
 */
class OptionSource {

	/** The most pages one collection is read in, the UI library's ceiling for a collection that keeps reporting more than it sends. */
	private const MAX_PAGES = 10;

	/**
	 * Turn every sourced field of a form spec into an `enum` of its collection.
	 *
	 * @remarks A field whose collection cannot be read keeps the `text` type it was generated with, so the visitor can still type the name and the form never renders an empty dropdown.
	 *
	 * @param array<string, mixed> $spec Form spec from a generated `<Op>Form::spec()`.
	 * @return array<string, mixed>
	 */
	public static function resolve( array $spec ): array {
		foreach ( $spec['sections'] ?? array() as $i => $section ) {
			$spec['sections'][ $i ]['fields'] = array_map( array( self::class, 'resolve_field' ), $section['fields'] ?? array() );
		}
		$spec['flat_fields'] = array_map( array( self::class, 'resolve_field' ), $spec['flat_fields'] ?? array() );
		return $spec;
	}

	/**
	 * One field as an `enum` of its collection, or unchanged when it has none or the read failed.
	 *
	 * @param array<string, mixed> $field Field spec.
	 * @return array<string, mixed>
	 */
	private static function resolve_field( array $field ): array {
		if ( empty( $field['source'] ) || ! is_array( $field['source'] ) ) {
			return $field;
		}
		$choices = self::choices( $field['source'], (string) $field['name'] );
		if ( $choices ) {
			$field['type'] = 'enum';
			// PHP keys a numeric value as an int; the router compares strings.
			$field['enum']        = array_map( 'strval', array_keys( $choices ) );
			$field['enum_labels'] = array_values( $choices );
		}
		return $field;
	}

	/**
	 * Every choice a collection offers, page by page up to the total it reports.
	 *
	 * @param array<string, mixed> $source `path`, `ttl`, and optionally `limit` and `lang`.
	 * @param string               $field  Request field the choices fill; a record without an `id` carries its value here.
	 * @return array<array-key, string> Value to label, empty when the collection cannot be read.
	 */
	private static function choices( array $source, string $field ): array {
		$path  = ltrim( (string) $source['path'], '/' );
		$limit = (int) ( $source['limit'] ?? 0 );
		// Choice labels are form chrome, so they follow the screen locale like
		// every other form label, not the reading language.
		$lang = empty( $source['lang'] ) ? '' : Language::from_locale( determine_locale() );
		$out  = array();
		for ( $page = 0; $page < self::MAX_PAGES; $page++ ) {
			$query = array_filter(
				array(
					'lang'   => $lang,
					'limit'  => $limit ? (string) $limit : '',
					'offset' => $out ? (string) count( $out ) : '',
				),
				static fn( $v ) => $v !== ''
			);
			$json  = Cache::remember(
				$path,
				$query,
				(int) $source['ttl'],
				static fn() => Client::get( $path, $query )
			);
			if ( ! is_array( $json ) ) {
				break;
			}
			$got   = self::options_from( $json, $field );
			$out  += $got; // `+` keeps numeric keys that array_merge would renumber.
			$total = isset( $json['total'] ) && is_int( $json['total'] ) ? $json['total'] : null;
			if ( ! $limit || $total === null || count( $out ) >= $total || ! $got ) {
				break;
			}
		}
		return $out;
	}

	/**
	 * The choices one response offers: a list of strings, or a list of records valued by `id`, else the field, else `number`, and labelled by `name`, else `english`.
	 *
	 * @param array<mixed> $json  Decoded response.
	 * @param string       $field Request field name.
	 * @return array<array-key, string>
	 */
	private static function options_from( array $json, string $field ): array {
		$entries = array_is_list( $json ) ? $json : null;
		if ( $entries === null ) {
			foreach ( $json as $value ) {
				if ( is_array( $value ) && array_is_list( $value ) ) {
					$entries = $value;
					break;
				}
			}
		}
		$out = array();
		foreach ( $entries ?? array() as $item ) {
			if ( is_string( $item ) ) {
				$out[ $item ] = $item;
				continue;
			}
			if ( ! is_array( $item ) ) {
				continue;
			}
			$raw = $item['id'] ?? $item[ $field ] ?? $item['number'] ?? null;
			if ( ! is_scalar( $raw ) ) {
				continue;
			}
			$name                 = $item['name'] ?? $item['english'] ?? null;
			$out[ (string) $raw ] = is_string( $name ) ? $name : (string) $raw;
		}
		return $out;
	}
}
