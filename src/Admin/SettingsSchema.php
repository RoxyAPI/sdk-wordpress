<?php
/**
 * Schema-driven settings registry.
 *
 * Single source of truth for every key the plugin stores under the
 * `roxyapi_settings` option. The sanitiser (`SettingsFields::sanitize`),
 * the option default, and the per-field renderers all read this schema
 * instead of hardcoding individual keys. New settings slot in by adding
 * an entry here OR by extending the `roxyapi_settings_schema` filter
 * from a third-party plugin / mu-plugin.
 *
 * Field shape:
 *   'option_key' => array(
 *     'type'       => 'string' | 'multiline' | 'int' | 'bool' | 'encrypted_key',
 *     'default'    => mixed,
 *     'input_key'  => string  Optional. POST-array key when it differs from
 *                             the stored option key (e.g. the form posts
 *                             `api_key`, we store `api_key_encrypted`).
 *     'min'        => int     Optional, integer fields only.
 *     'max'        => int     Optional, integer fields only.
 *   )
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SettingsSchema {

	/**
	 * Return every field descriptor in canonical order. Filter
	 * `roxyapi_settings_schema` lets extensions append, override, or
	 * remove fields. The filter return MUST stay shaped like the input
	 * (key → field-descriptor map); malformed entries are silently
	 * dropped at sanitise time.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function fields(): array {
		$schema = array(
			'api_key_encrypted' => array(
				'type'      => 'encrypted_key',
				'default'   => '',
				'input_key' => 'api_key',
			),
			'cache_ttl'         => array(
				'type'    => 'int',
				'default' => HOUR_IN_SECONDS,
				'min'     => 0,
			),
			'cache_preset'      => array(
				'type'    => 'enum',
				'default' => 'balanced',
				'enum'    => array( 'fresh', 'balanced', 'quota_saver' ),
			),
			'attribution_show'  => array(
				'type'    => 'bool',
				'default' => false,
			),
			'consent_label'     => array(
				'type'    => 'multiline',
				'default' => '',
			),
			'accent_color'      => array(
				'type'    => 'color',
				'default' => '',
			),
			'display_language'  => array(
				'type'    => 'enum',
				'default' => '',
				'enum'    => array( '', 'en', 'de', 'hi', 'es', 'tr', 'pt', 'fr', 'ru' ),
			),
			'disclaimer_show'   => array(
				'type'    => 'bool',
				'default' => false,
			),
			'disclaimer_text'   => array(
				'type'    => 'multiline',
				'default' => '',
			),
			'form_title'        => array(
				'type'    => 'string',
				'default' => '',
			),
			'form_submit_label' => array(
				'type'    => 'string',
				'default' => '',
			),
		);

		/**
		 * Filter the settings schema. Extensions can add new keys, change
		 * defaults, or remove keys. Returned shape must keep the
		 * `key => array<string, mixed>` contract.
		 *
		 * @param array<string, array<string, mixed>> $schema
		 */
		$filtered = apply_filters( 'roxyapi_settings_schema', $schema );
		return is_array( $filtered ) ? $filtered : $schema;
	}

	/**
	 * Default-value map keyed by option-key. Fed to `register_setting`'s
	 * `default` so a fresh install reads sensible values without having
	 * to run the sanitiser.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults(): array {
		$out = array();
		foreach ( self::fields() as $key => $field ) {
			$out[ $key ] = $field['default'] ?? '';
		}
		return $out;
	}

	/**
	 * Read the saved options merged with schema defaults so callers never
	 * have to handle "key not set yet" branches when a new field has been
	 * added in a later release.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_option(): array {
		$saved = get_option( SettingsPage::OPTION_NAME, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return wp_parse_args( $saved, self::defaults() );
	}
}
