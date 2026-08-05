/**
 * Every visitor-facing string in a GENERATED form spec must be wrapped in `__()`.
 *
 * A raw literal never reaches gettext, so it is absent from the POT and from
 * translate.wordpress.org and cannot be translated however complete a language pack is.
 * The visible result is an English form above a correctly translated reading, which looks
 * like a broken plugin rather than a missing translation.
 *
 * This scans the GENERATED SOURCE rather than calling `spec()`, deliberately: at runtime
 * `__()` has already returned a plain string, so a translated and an untranslated build are
 * indistinguishable through the public API. The difference is only visible in emitted text.
 *
 * Keys deliberately NOT asserted: `name` (the request key), `type` (picks the control) and
 * `enum` (API values posted verbatim, whose display text FormRenderer derives with
 * ucwords). Translating any of those would post a translated value to the API.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Tests;

class Test_Form_Strings_Translatable extends \WP_UnitTestCase {

	/** Spec keys holding prose a visitor reads. Mirrors TRANSLATABLE_SPEC_KEYS in bin/generate.mjs, plus the form title. */
	private const VISITOR_FACING_KEYS = array( 'label', 'help', 'placeholder', 'title' );

	/** @return string[] Absolute paths of every generated form class. */
	private function form_files(): array {
		$files = glob( dirname( ROXYAPI_PLUGIN_FILE ) . '/src/Generated/Forms/*.php' );
		return is_array( $files ) ? $files : array();
	}

	public function test_generated_form_files_exist(): void {
		// Non-vacuity: an empty glob would make every assertion below pass silently.
		$this->assertGreaterThan(
			20,
			count( $this->form_files() ),
			'Expected the generated form classes to be present; a passing suite over zero files proves nothing.'
		);
	}

	public function test_every_visitor_facing_string_is_wrapped_in_gettext(): void {
		$offenders = array();

		foreach ( $this->form_files() as $file ) {
			$source = (string) file_get_contents( $file );
			foreach ( self::VISITOR_FACING_KEYS as $key ) {
				// Match `'key' => <value>,` and inspect what the value starts with.
				preg_match_all(
					"/'" . preg_quote( $key, '/' ) . "'\s*=>\s*([^,\n]+)/",
					$source,
					$matches
				);
				foreach ( $matches[1] as $value ) {
					$value = trim( $value );
					// A non-string value (array(), true, a number) is machinery, not prose.
					if ( strpos( $value, "'" ) !== 0 ) {
						continue;
					}
					// An empty literal is legitimate: __( '' ) collides with the PO header.
					if ( $value === "''" ) {
						continue;
					}
					$offenders[] = basename( $file ) . ": '{$key}' => {$value}";
				}
			}
		}

		$this->assertSame(
			array(),
			$offenders,
			"Generated form strings must be wrapped in __( ..., 'roxyapi' ) so they can be translated. "
				. "Fix bin/generate.mjs (see TRANSLATABLE_SPEC_KEYS), never the generated file. Offenders:\n"
				. implode( "\n", array_slice( $offenders, 0, 15 ) )
		);
	}

	public function test_the_text_domain_is_the_plugin_slug_everywhere(): void {
		// wp.org matches the text domain to the plugin slug to deliver language packs;
		// a stray domain silently receives no translations at all.
		$offenders = array();
		foreach ( $this->form_files() as $file ) {
			$source = (string) file_get_contents( $file );
			preg_match_all( "/__\(\s*'[^']*'\s*,\s*'([^']+)'\s*\)/", $source, $matches );
			foreach ( array_unique( $matches[1] ) as $domain ) {
				if ( $domain !== 'roxyapi' ) {
					$offenders[] = basename( $file ) . ": {$domain}";
				}
			}
		}
		$this->assertSame( array(), $offenders, 'Text domain must be "roxyapi" (the wp.org slug).' );
	}
}
