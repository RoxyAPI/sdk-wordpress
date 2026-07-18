<?php
/**
 * Tests that a block's camelCase attributes reach the API.
 *
 * A generated block's render.php hands its block.json attributes (camelCase) to
 * the shared shortcode render(), which resolves inputs with snake_case
 * shortcode_atts keys. BlockOutput::to_shortcode_atts bridges the two. Without
 * it, multi-word attributes (fullName, birthDate, nodeType) are dropped:
 * required inputs produce a failed reading, optional ones are silently ignored.
 * This guards that regression at both the unit level (the key rewrite) and end
 * to end (render a block, capture the outgoing request, assert the value made
 * it into the body). Skipped when the plugin has not been built, since block
 * rendering reads the build output.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

use RoxyAPI\Support\BlockOutput;

class Test_Block_Attribute_Mapping extends \WP_UnitTestCase {

	/**
	 * @var array<string, mixed>|null Captured outgoing request args.
	 */
	private $captured = null;

	private static function blocks_dir(): string {
		return dirname( ROXYAPI_PLUGIN_FILE ) . '/build/blocks';
	}

	public function setUp(): void {
		parent::setUp();
		if ( ! is_dir( self::blocks_dir() ) ) {
			$this->markTestSkipped( 'Plugin not built; run `npm run build:all` before phpunit.' );
		}
	}

	public function test_camelcase_keys_map_to_snake_case(): void {
		$this->assertSame(
			array(
				'full_name'     => 'Ada Lovelace',
				'node_type'     => 'mean',
				'birth_date'    => '1990-05-15',
				'house_system'  => 'placidus',
				'date'          => '2026-02-03',
				'id'            => 'amethyst',
			),
			BlockOutput::to_shortcode_atts(
				array(
					'fullName'    => 'Ada Lovelace',
					'nodeType'    => 'mean',
					'birthDate'   => '1990-05-15',
					'houseSystem' => 'placidus',
					'date'        => '2026-02-03',
					'id'          => 'amethyst',
				)
			),
			'Multi-word block attributes must rewrite to the snake_case keys shortcode_atts reads; single-word keys stay unchanged.'
		);
	}

	public function test_block_required_camelcase_attribute_reaches_the_api_body(): void {
		add_filter( 'pre_http_request', array( $this, 'capture_request' ), 10, 3 );

		render_block(
			array(
				'blockName' => 'roxyapi/calculate-expression',
				'attrs'     => array( 'fullName' => 'Ada Lovelace' ),
			)
		);

		remove_filter( 'pre_http_request', array( $this, 'capture_request' ), 10 );

		$this->assertNotNull( $this->captured, 'Rendering the block should have triggered an API request.' );
		$body = is_string( $this->captured['body'] ?? null ) ? $this->captured['body'] : '';
		$this->assertStringContainsString(
			'Ada Lovelace',
			$body,
			'The block camelCase attribute must survive into the API request body; a mapping regression drops multi-word attributes.'
		);
	}

	/**
	 * Short-circuit outgoing HTTP, record the request, return a canned reading.
	 *
	 * @param mixed                $preempt Filter short-circuit value.
	 * @param array<string, mixed> $args    Request args (method, headers, body).
	 * @param string               $url     Request URL.
	 * @return array<string, mixed> A fake successful response.
	 */
	public function capture_request( $preempt, $args, $url ): array {
		$this->captured = array(
			'url'  => $url,
			'body' => $args['body'] ?? null,
		);
		return array(
			'response' => array( 'code' => 200 ),
			'body'     => wp_json_encode( array( 'number' => 9, 'type' => 'single' ) ),
			'headers'  => array(),
		);
	}
}
