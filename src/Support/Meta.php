<?php
/**
 * The optional trailing block appended to a rendered reading: the disclaimer
 * line, the attribution credit link, and the JSON-LD structured data. Shared by
 * {@link GenericRenderer} (which nests it inside the card) and
 * {@link ComponentRenderer} (which emits it outside the custom element so it
 * survives the upgrade). All three pieces are off or empty unless the site owner
 * opts in, so the common case returns an empty string.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Meta {

	/**
	 * Build the disclaimer + attribution + JSON-LD fragment for a reading.
	 *
	 * @param string               $operation_id Spec operationId.
	 * @param array<string, mixed> $data         API response data.
	 * @return string
	 */
	public static function block( string $operation_id, array $data ): string {
		return self::visible( $operation_id ) . Attribution::jsonld( $operation_id, $data );
	}

	/**
	 * The same block for a placement that sits outside the reading rather than
	 * inside it.
	 *
	 * {@link ComponentRenderer} emits this NEXT TO the custom element, because an
	 * element hides its light-DOM children once it upgrades. That puts the two
	 * visible lines on the page rather than on a surface of ours, where they
	 * would take their background from whatever section the site owner built
	 * while keeping the document text colour. One wrapper gives them the same
	 * surface the reading has.
	 *
	 * The wrapper is emitted only when there is something to show, so a site
	 * with both lines off gets no empty strip, and the structured data stays
	 * outside it: it carries no colour and renders nothing.
	 *
	 * @param string               $operation_id Spec operationId.
	 * @param array<string, mixed> $data         API response data.
	 * @return string
	 */
	public static function standalone_block( string $operation_id, array $data ): string {
		$visible = self::visible( $operation_id );
		if ( '' !== $visible ) {
			$visible = '<div class="roxyapi-meta">' . $visible . '</div>';
		}
		return $visible . Attribution::jsonld( $operation_id, $data );
	}

	/**
	 * The two visible lines, both empty unless the site owner opts in. One
	 * source, so the nested and standalone forms cannot compose different
	 * things.
	 *
	 * @param string $operation_id Spec operationId.
	 * @return string
	 */
	private static function visible( string $operation_id ): string {
		return Disclaimer::render() . Attribution::credit_link( $operation_id );
	}
}
