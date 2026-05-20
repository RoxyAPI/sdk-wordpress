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
		return Disclaimer::render()
			. Attribution::credit_link( $operation_id )
			. Attribution::jsonld( $operation_id, $data );
	}
}
