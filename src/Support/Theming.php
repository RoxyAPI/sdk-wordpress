<?php
/**
 * Brand palette, forced light/dark theme, and hidden sections for the RoxyAPI
 * readings.
 *
 * Three jobs, one file, because they answer the same question: what does a
 * reading look like on this site. All three leave through one stylesheet,
 * {@link Theming::inline_css}, which the plugin attaches to the frontend handle.
 *
 * The components read every colour from the vendored token stylesheet as a
 * `--roxy-*` custom property. Custom properties inherit downward and cross the
 * shadow boundary, so a value set on the document `:root` reaches every chart,
 * table, and card; a value set on an inner wrapper does not. That is why the
 * override is emitted at `:root` and why the forced mode is applied to the page
 * `<html>` tag through the `language_attributes` filter. Only `--roxy-*`
 * properties are declared, which nothing but our components read, so neither
 * touches the surrounding site.
 *
 * Both light and dark are emitted, never one for both. The token layer pins a
 * mode-specific value for each colour, and in dark it uses the accent as TEXT
 * (`--roxy-accent-ink` resolves to the raw accent there). A single accent forced
 * into all three theme states therefore turned a dark brand colour into dark
 * text on a near-black surface. `accent-ink` and `ring` are still left alone:
 * both are `color-mix` derivations of the accent, so they follow whatever the
 * accent is set to in that mode.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

use RoxyAPI\Admin\SettingsSchema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Theming {

	/**
	 * The colours a site owner can set, mapped to the custom property each one
	 * writes. Option keys are derived: `{token}_color` for light and
	 * `{token}_color_dark` for dark, which is why the pre-existing single accent
	 * setting (`accent_color`) keeps working untouched as the light accent.
	 *
	 * @var array<string, string>
	 */
	private const TOKENS = array(
		'accent'  => '--roxy-accent',
		'bg'      => '--roxy-bg',
		'surface' => '--roxy-surface',
		'fg'      => '--roxy-fg',
		'muted'   => '--roxy-muted',
		'border'  => '--roxy-border',
		'danger'  => '--roxy-danger',
	);

	/**
	 * The four shipped palettes, HEX values only.
	 *
	 * Vendored as data rather than as the upstream theme stylesheets on purpose:
	 * each of those files opens with an `@import` of a Google Fonts URL, which
	 * would make every front-end page request a third-party server on the site
	 * owner's behalf. That is a consent problem under the GDPR and a WordPress.org
	 * guideline 7 problem, and neither is worth a typeface.
	 *
	 * Light and dark are tuned as a pair, not derived from one another: each dark
	 * surface sits far enough above its background, and each foreground far
	 * enough above its surface, to stay legible.
	 *
	 * @var array<string, array<string, array<string, string>>>
	 */
	private const PRESETS = array(
		'practitioner' => array(
			'light' => array(
				'accent'  => '#914955',
				'bg'      => '#fbf6f3',
				'surface' => '#f5e8e0',
				'fg'      => '#3e2a2c',
				'muted'   => '#7e625f',
				'border'  => '#ead9d2',
				'danger'  => '#b23a38',
			),
			'dark'  => array(
				'accent'  => '#d9a2a6',
				'bg'      => '#231619',
				'surface' => '#37272b',
				'fg'      => '#f2e4df',
				'muted'   => '#b39698',
				'border'  => '#49353a',
				'danger'  => '#e4736b',
			),
		),
		'eucalyptus'   => array(
			'light' => array(
				'accent'  => '#4c7060',
				'bg'      => '#f8f7f2',
				'surface' => '#edefe6',
				'fg'      => '#22281f',
				'muted'   => '#666d63',
				'border'  => '#dce0d3',
				'danger'  => '#b23a38',
			),
			'dark'  => array(
				'accent'  => '#9cc0ac',
				'bg'      => '#191e19',
				'surface' => '#2a312a',
				'fg'      => '#e9ede3',
				'muted'   => '#9fa89b',
				'border'  => '#384138',
				'danger'  => '#e4736b',
			),
		),
		'kiln'         => array(
			'light' => array(
				'accent'  => '#a44a24',
				'bg'      => '#faf4ea',
				'surface' => '#efe6d6',
				'fg'      => '#322820',
				'muted'   => '#74634f',
				'border'  => '#e6d8c2',
				'danger'  => '#b23a38',
			),
			'dark'  => array(
				'accent'  => '#d98d5f',
				'bg'      => '#211710',
				'surface' => '#35281e',
				'fg'      => '#f2e7d9',
				'muted'   => '#b49d87',
				'border'  => '#463729',
				'danger'  => '#e4736b',
			),
		),
		'moonlit'      => array(
			'light' => array(
				'accent'  => '#254b5a',
				'bg'      => '#faf6ec',
				'surface' => '#f1eadb',
				'fg'      => '#14232e',
				'muted'   => '#5c6a76',
				'border'  => '#e4dbc6',
				'danger'  => '#b23a38',
			),
			'dark'  => array(
				'accent'  => '#c9a96b',
				'bg'      => '#0b1826',
				'surface' => '#1b2a39',
				'fg'      => '#efe7d3',
				'muted'   => '#92a4b2',
				'border'  => '#263a4c',
				'danger'  => '#e4736b',
			),
		),
	);

	/**
	 * Hook the `<html>` filter.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_filter( 'language_attributes', array( self::class, 'html_theme_attr' ) );
	}

	/**
	 * Token names in emit order.
	 *
	 * @return array<int, string>
	 */
	public static function tokens(): array {
		return array_keys( self::TOKENS );
	}

	/**
	 * Preset names in menu order.
	 *
	 * @return array<int, string>
	 */
	public static function preset_names(): array {
		return array_keys( self::PRESETS );
	}

	/**
	 * The light and dark colours of one preset, or an empty array when the name
	 * is not one we ship. Callers may pass unvalidated input.
	 *
	 * @param string $name Preset name.
	 * @return array<string, array<string, string>>
	 */
	public static function preset( string $name ): array {
		return self::PRESETS[ $name ] ?? array();
	}

	/**
	 * Every option key the palette owns, light and dark. Used by the reset
	 * control and by the schema so the list lives in one place.
	 *
	 * @return array<int, string>
	 */
	public static function option_keys(): array {
		$keys = array();
		foreach ( array_keys( self::TOKENS ) as $token ) {
			$keys[] = self::option_key( $token, 'light' );
			$keys[] = self::option_key( $token, 'dark' );
		}
		return $keys;
	}

	/**
	 * Option key for one token in one mode.
	 *
	 * @param string $token Token name from {@link Theming::tokens()}.
	 * @param string $mode  `light` or `dark`.
	 * @return string
	 */
	public static function option_key( string $token, string $mode ): string {
		return $mode === 'dark' ? $token . '_color_dark' : $token . '_color';
	}

	/**
	 * The colours in force on this site, as `mode => token => hex`.
	 *
	 * A chosen preset answers on its own and the stored colours are not read:
	 * the values then come from a constant in this file rather than from the
	 * database, so a preset can never carry anything into a stylesheet. Custom
	 * colours are re-validated here even though the sanitiser already ran,
	 * because an option can be written by anything holding `update_option`, and
	 * a settings field feeding a stylesheet is the one place that has to assume
	 * it was.
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function palette(): array {
		$opts   = SettingsSchema::get_option();
		$preset = self::preset( (string) ( $opts['palette_preset'] ?? '' ) );
		if ( $preset !== array() ) {
			return $preset;
		}

		$palette = array(
			'light' => array(),
			'dark'  => array(),
		);
		foreach ( array_keys( self::TOKENS ) as $token ) {
			foreach ( array( 'light', 'dark' ) as $mode ) {
				$value = sanitize_hex_color(
					(string) ( $opts[ self::option_key( $token, $mode ) ] ?? '' )
				);
				if ( is_string( $value ) && $value !== '' ) {
					$palette[ $mode ][ $token ] = $value;
				}
			}
		}
		return $palette;
	}

	/**
	 * The stylesheet that carries the palette onto the page.
	 *
	 * Three blocks on the front end, in an order the cascade depends on. The
	 * token layer wraps every one of its own selectors in `:where()`, so it has
	 * zero specificity and any of these wins over it; what these have to settle
	 * is which of THEM wins. `:root[data-theme="light"]` outranks the plain
	 * `:root` inside the dark media query, so a site forced to light stays light
	 * on a dark device. The media block comes last so that with no attribute at
	 * all it beats the equally specific light block and the reading follows the
	 * visitor.
	 *
	 * @param bool $resolve_forced_mode Emit only the forced mode's colours, on a
	 *                                  plain `:root`. The block editor renders
	 *                                  its canvas in an iframe whose `<html>`
	 *                                  this plugin never sees, so inside the
	 *                                  editor the attribute the front end relies
	 *                                  on is not there to match, and a forced
	 *                                  dark preview would silently render light.
	 * @return string
	 */
	public static function inline_css( bool $resolve_forced_mode = false ): string {
		$palette = self::palette();
		$mode    = self::mode();

		// Components inherit the site's own typeface unless the owner overrides
		// the token: the shipped default names a font that is not on the page,
		// which made every reading fall back to a stack that matched nothing
		// around it. `inherit` at the root leaves `font-family` to resolve from
		// the element the component sits in.
		$base = '--roxy-font-sans:inherit;';

		if ( $resolve_forced_mode && $mode !== 'auto' ) {
			return ':root{' . $base . self::declarations( $palette[ $mode ] ) . '}';
		}

		$css  = ':root,:root[data-theme="light"]{' . $base;
		$css .= self::declarations( $palette['light'] ) . '}';

		$dark = self::declarations( $palette['dark'] );
		if ( $dark !== '' ) {
			$css .= ':root[data-theme="dark"]{' . $dark . '}';
			$css .= '@media (prefers-color-scheme:dark){:root{' . $dark . '}}';
		}

		return $css;
	}

	/**
	 * Section names the site owner has hidden, normalised. The SITE DEFAULT only.
	 *
	 * The one reader of the `hide_sections` setting, and its only caller is
	 * {@link ComponentRenderer}, which folds a per-placement attribute over it
	 * and hands the resolved list to both surfaces that act on it: the
	 * `hide-sections` attribute on the element, and {@link GenericRenderer},
	 * which drops the same block out of the server-rendered fallback the
	 * no-JavaScript view and a crawler read.
	 *
	 * **This deliberately no longer emits a stylesheet, and that is not a
	 * simplification.** A site-wide `::part()` rule lives in the OUTER tree, so
	 * it outranks anything inside the component and would win against a
	 * placement asking to KEEP a block. Once one placement can opt out, a global
	 * rule stops being a shortcut for the common case and becomes a rule that
	 * cannot be overridden. The resolved list travels per element instead,
	 * exactly as `hide-readings` already does.
	 *
	 * @return array<int, string>
	 */
	public static function hidden_sections(): array {
		$opts = SettingsSchema::get_option();
		return Sanitize::section_names( $opts['hide_sections'] ?? '' );
	}


	/**
	 * Custom-property declarations for one mode.
	 *
	 * @param array<string, string> $colors Token name to HEX.
	 * @return string
	 */
	private static function declarations( array $colors ): string {
		$out = '';
		foreach ( self::TOKENS as $token => $property ) {
			if ( ! isset( $colors[ $token ] ) ) {
				continue;
			}
			$value = sanitize_hex_color( (string) $colors[ $token ] );
			if ( is_string( $value ) && $value !== '' ) {
				$out .= $property . ':' . $value . ';';
			}
		}
		return $out;
	}

	/**
	 * The forced theme mode: `auto`, `light`, or `dark`.
	 *
	 * The schema default is `light`, not `auto`: most WordPress themes are light,
	 * and a dark reading card dropped into a light page reads as broken. A site
	 * owner who wants the components to follow the visitor picks `auto`.
	 *
	 * @return string
	 */
	public static function mode(): string {
		$opts = SettingsSchema::get_option();
		$mode = (string) ( $opts['theme_mode'] ?? 'auto' );
		return in_array( $mode, array( 'auto', 'light', 'dark' ), true ) ? $mode : 'auto';
	}

	/**
	 * Append `data-theme="dark"` or `data-theme="light"` to the `<html>` tag when
	 * the site owner forces a mode. `auto` returns the attributes unchanged so
	 * the components follow `prefers-color-scheme`.
	 *
	 * Applied in wp-admin too. It used to return early there, which left the
	 * accent applied and the mode ignored, so the Demo page and every editor
	 * preview showed a light card to an owner who had chosen dark.
	 *
	 * @param string $output The language attributes string for the `<html>` tag.
	 * @return string
	 */
	public static function html_theme_attr( string $output ): string {
		$mode = self::mode();
		if ( $mode === 'dark' || $mode === 'light' ) {
			$output .= ' data-theme="' . $mode . '"';
		}
		return $output;
	}
}
