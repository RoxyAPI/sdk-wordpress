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
	 * (form mode lives in src/Shortcodes/Horoscope.php). The other nine are
	 * auto-generated from bin/hero-config.json into src/Generated/Heroes/ on
	 * every npm run generate.
	 *
	 * Each referenced class must expose a public const DEFAULTS array. The
	 * Test_Hero_Attr_Contract regression test enforces this.
	 *
	 * @var array<string, class-string>
	 */
	public const HERO_SHORTCODES = array(
		'roxy_horoscope'    => Horoscope::class,
		'roxy_natal_chart'  => \RoxyAPI\Generated\Heroes\NatalChart::class,
		'roxy_tarot_card'   => \RoxyAPI\Generated\Heroes\TarotCard::class,
		'roxy_numerology'   => \RoxyAPI\Generated\Heroes\Numerology::class,
		'roxy_life_path'    => \RoxyAPI\Generated\Heroes\LifePath::class,
		'roxy_iching'       => \RoxyAPI\Generated\Heroes\Iching::class,
		'roxy_dream'        => \RoxyAPI\Generated\Heroes\Dream::class,
		'roxy_biorhythm'    => \RoxyAPI\Generated\Heroes\Biorhythm::class,
		'roxy_angel_number' => \RoxyAPI\Generated\Heroes\AngelNumber::class,
		'roxy_crystal'      => \RoxyAPI\Generated\Heroes\Crystal::class,
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
