<?php
/**
 * The variant half of the operationId-to-component map.
 *
 * A component that serves many operations picks its view from an attribute:
 * `roxy-numerology-card` renders a Soul Urge or a Personal Year from the same
 * element, `roxy-tarot-spread` a love spread or a career one. The map carries
 * that selector per binding and the renderer has to put it on the tag.
 *
 * This exists because the failure is invisible. Drop the attribute and the
 * right element still mounts, still fetches, still renders: it just renders the
 * DEFAULT view for every one of those operations. Nothing errors, the page
 * looks finished, and the reader gets the wrong reading. `check:component-map`
 * guards the map against the published catalogue; these guard the step after,
 * where the map becomes markup.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Generated\ComponentMap;
use RoxyAPI\Support\ComponentRenderer;

class Test_Component_Map_Attrs extends Mock_Http_TestCase {

	/** Minimal payload; these assertions are about the tag, not the body. */
	private function payload(): array {
		return array(
			'number'  => 7,
			'name'    => 'Soul Urge',
			'meaning' => 'A need for truth.',
		);
	}

	public function test_map_carries_the_variant_attribute_for_a_shared_component(): void {
		$rows = ComponentMap::for( 'calculateSoulUrge' );

		$this->assertNotEmpty( $rows, 'calculateSoulUrge must be bound; it rendered as raw roxy-data until 2026-08-09.' );
		$this->assertSame( 'roxy-numerology-card', $rows[0]['component'] );
		$this->assertSame( array( 'type' => 'soul-urge' ), $rows[0]['attrs'] );
	}

	public function test_renderer_emits_the_variant_attribute_on_the_element(): void {
		$out = ComponentRenderer::render( 'calculateSoulUrge', $this->payload() );

		$this->assertStringContainsString( '<roxy-numerology-card', $out );
		$this->assertStringContainsString( 'type="soul-urge"', $out );
	}

	public function test_two_operations_sharing_one_component_emit_different_views(): void {
		// The whole point: same tag, different reading. If these ever match,
		// the selector has been dropped and both render the default.
		$soul = ComponentRenderer::render( 'calculateSoulUrge', $this->payload() );
		$year = ComponentRenderer::render( 'calculatePersonalYear', $this->payload() );

		$this->assertStringContainsString( 'type="soul-urge"', $soul );
		$this->assertStringContainsString( 'type="personal-year"', $year );
	}

	public function test_a_binding_with_no_variant_emits_no_stray_attribute(): void {
		// Most operations own their component outright and must stay byte-clean.
		$rows = ComponentMap::for( 'generateNatalChart' );
		$this->assertArrayNotHasKey( 'attrs', $rows[0] );

		$out = ComponentRenderer::render( 'generateNatalChart', $this->payload() );
		$this->assertStringContainsString( '<roxy-natal-chart', $out );
		$this->assertMatchesRegularExpression(
			'/<roxy-natal-chart class="roxyapi-component" data-operation="generateNatalChart"[^>]*>/',
			$out
		);
	}

	public function test_every_mapped_attribute_name_is_a_safe_attribute_name(): void {
		// The renderer also serves a hand-written fallback row, so it filters
		// names itself rather than trusting codegen. Assert the data it is
		// given never needs that filter to fire.
		$path = dirname( __DIR__, 2 ) . '/bin/component-map.json';
		$map  = json_decode( (string) file_get_contents( $path ), true );

		$checked = 0;
		foreach ( $map['operations'] as $operation_id => $rows ) {
			foreach ( $rows as $row ) {
				foreach ( array_keys( $row['attrs'] ?? array() ) as $name ) {
					$this->assertMatchesRegularExpression(
						'/^[a-z][a-z0-9-]*$/',
						$name,
						"Unsafe attribute name on {$operation_id}."
					);
					++$checked;
				}
			}
		}
		$this->assertGreaterThan( 0, $checked, 'No variant attributes found; the map lost its attrs.' );
	}
}
