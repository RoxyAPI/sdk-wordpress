<?php
/**
 * Shortcode registration. Hero shortcodes register first; generated shortcodes
 * skip any tag already registered. Hero always wins.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Shortcodes;

class Registrar {

	private const HERO_SHORTCODES = array(
		'roxy_horoscope'     => Horoscope::class,
		'roxy_natal_chart'   => NatalChart::class,
		'roxy_tarot_card'    => TarotCard::class,
		'roxy_numerology'    => Numerology::class,
		'roxy_life_path'     => LifePath::class,
		'roxy_iching'        => IChing::class,
		'roxy_dream'         => Dream::class,
		'roxy_biorhythm'     => Biorhythm::class,
		'roxy_angel_number'  => AngelNumber::class,
		'roxy_crystal'       => Crystal::class,
		'roxy_compatibility' => Compatibility::class,
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
			add_shortcode( $tag, array( $class, 'render' ) );
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
