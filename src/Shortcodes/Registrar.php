<?php
/**
 * Shortcode registration. Hero shortcodes register first; generated shortcodes
 * skip any tag already registered. Hero always wins.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Registrar {

	/**
	 * Hero shortcode tag => fully-qualified class name. Horoscope is hand-written
	 * (form mode lives in src/Shortcodes/Horoscope.php). The other entries are
	 * auto-generated from bin/hero-config.json into src/Generated/Heroes/ on
	 * every npm run generate.
	 *
	 * Each referenced class must expose a public const DEFAULTS array. The
	 * Test_Hero_Attr_Contract regression test enforces this.
	 *
	 * v1.1 hero set: 7 v1.0 carries (horoscope, natal_chart, tarot_card,
	 * numerology, life_path, biorhythm, angel_number) plus 10 money
	 * heroes (kundli, panchang, mangal_dosha, kp_chart, synastry, gun_milan,
	 * compatibility, moon_phase, tarot_yes_no, crystals_by_zodiac). Three
	 * commodity heroes from v1.0 (iching, dream, crystal) demoted to
	 * long-tail; LegacyAliases::register surfaces the old shortcode tags.
	 *
	 * @var array<string, class-string>
	 */
	public const HERO_SHORTCODES = array(
		'roxy_horoscope'          => Horoscope::class,
		'roxy_natal_chart'        => \RoxyAPI\Generated\Heroes\NatalChart::class,
		'roxy_kundli'             => \RoxyAPI\Generated\Heroes\Kundli::class,
		'roxy_panchang'           => \RoxyAPI\Generated\Heroes\Panchang::class,
		'roxy_mangal_dosha'       => \RoxyAPI\Generated\Heroes\MangalDosha::class,
		'roxy_kp_chart'           => \RoxyAPI\Generated\Heroes\KpChart::class,
		'roxy_synastry'           => \RoxyAPI\Generated\Heroes\Synastry::class,
		'roxy_gun_milan'          => \RoxyAPI\Generated\Heroes\GunMilan::class,
		'roxy_compatibility'      => \RoxyAPI\Generated\Heroes\Compatibility::class,
		'roxy_moon_phase'         => \RoxyAPI\Generated\Heroes\MoonPhase::class,
		'roxy_tarot_card'         => \RoxyAPI\Generated\Heroes\TarotCard::class,
		'roxy_tarot_yes_no'       => \RoxyAPI\Generated\Heroes\TarotYesNo::class,
		'roxy_numerology'         => \RoxyAPI\Generated\Heroes\Numerology::class,
		'roxy_life_path'          => \RoxyAPI\Generated\Heroes\LifePath::class,
		'roxy_biorhythm'          => \RoxyAPI\Generated\Heroes\Biorhythm::class,
		'roxy_angel_number'       => \RoxyAPI\Generated\Heroes\AngelNumber::class,
		'roxy_crystals_by_zodiac' => \RoxyAPI\Generated\Heroes\CrystalsByZodiac::class,
	);

	public static function register(): void {
		add_action( 'init', array( self::class, 'register_hero' ), 10 );
		add_action( 'init', array( self::class, 'register_generated' ), 20 );
		add_action( 'init', array( self::class, 'register_assets' ), 5 );
	}

	public static function register_hero(): void {
		foreach ( self::HERO_SHORTCODES as $tag => $class ) {
			if ( shortcode_exists( $tag ) ) {
				continue;
			}
			add_shortcode(
				$tag,
				static function ( $atts, $content, $shortcode_tag ) use ( $class ): string {
					return $class::render( $atts, $content ?? '', (string) $shortcode_tag );
				}
			);
		}
	}

	public static function register_generated(): void {
		if ( class_exists( '\\RoxyAPI\\Generated\\ShortcodeBootstrap' ) ) {
			\RoxyAPI\Generated\ShortcodeBootstrap::register();
		}
	}

	public static function register_assets(): void {
		wp_register_style(
			'roxyapi-frontend',
			plugins_url( 'assets/css/frontend.css', ROXYAPI_PLUGIN_FILE ),
			array(),
			ROXYAPI_VERSION
		);
	}
}
