<?php
/**
 * Attribution helpers — opt-in credit link + JSON-LD structured data.
 *
 * Two surfaces:
 *
 * 1. **Visible credit line** ("Astrology data by Roxy" / "Tarot data by Roxy"
 *    etc.) appended to each rendered card. **Default OFF** per
 *    https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
 *    guideline #10 — "All 'Powered By' or credit displays and links must be
 *    optional and default to NOT show". Site owners opt in via the
 *    Settings → Roxy "Show attribution credit" checkbox (`attribution_show`
 *    option). Anchor text follows the brand-book rule "lead with what we
 *    offer, not who we are" — keyword-leading, brand-trailing — and can be
 *    overridden per-call via the `roxyapi_credit_anchor` filter.
 *
 * 2. **JSON-LD `CreativeWork` / `Article` block** with `sourceOrganization`,
 *    `isBasedOn`, `citation` (methodology), `inLanguage`, `sameAs` array.
 *    Default OFF so SEO plugins (Yoast, RankMath, SEOPress) keep their
 *    page-level JSON-LD authority. Site owners with no SEO plugin can opt in
 *    via `add_filter('roxyapi_emit_jsonld', '__return_true')`.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Attribution {

	/** Brand canonical URL. UTM tags appended at render time. */
	private const BRAND_URL = 'https://roxyapi.com';

	/** Methodology page used as the `citation` provenance anchor. */
	private const METHODOLOGY_URL = 'https://roxyapi.com/methodology';

	/**
	 * Cross-platform identity edges for `sameAs`. Wikidata QID slot is
	 * empty for now — fill once the entity is published. Each entry must
	 * be a stable canonical URL.
	 *
	 * @var array<int, string>
	 */
	private const SAME_AS = array(
		'https://roxyapi.com',
		'https://github.com/RoxyAPI',
	);

	/**
	 * Operation-id prefixes that produce time-bound editorial readings.
	 * These render as `schema.org/Article` with `datePublished` / `dateModified`
	 * / `articleSection`. Anything not matching here renders as `CreativeWork`.
	 *
	 * @var array<int, string>
	 */
	private const ARTICLE_PREFIXES = array( 'getDailyHoroscope', 'getWeeklyHoroscope', 'getMonthlyHoroscope', 'getDailyCard', 'getDailyDreamSymbol', 'getDailyCrystal' );

	/**
	 * Render the credit line. Returns empty unless the site owner has opted
	 * in via the `attribution_show` setting AND the `roxyapi_render_credit`
	 * filter agrees.
	 *
	 * @param string $operation_id OpenAPI operation id (for anchor text variation).
	 * @return string
	 */
	public static function credit_link( string $operation_id = '' ): string {
		if ( ! self::should_show_credit() ) {
			return '';
		}
		/**
		 * Filter the anchor text. Default leads with what we offer
		 * (e.g. "Astrology data by Roxy") per brand-positioning.md.
		 *
		 * @param string $anchor       Default anchor text for this operation.
		 * @param string $operation_id Operation id, e.g. "getDailyHoroscope".
		 */
		$anchor = (string) apply_filters( 'roxyapi_credit_anchor', self::default_anchor( $operation_id ), $operation_id );

		$href = add_query_arg(
			array(
				'utm_source' => 'wp-plugin',
				'utm_medium' => 'attribution',
			),
			self::BRAND_URL . '/'
		);

		// Site owner has opted in — opt-in IS the editorial act, so we
		// drop `nofollow` per https://developers.google.com/search/docs/crawling-indexing/qualify-outbound-links.
		// `noopener` stays for security.
		return '<p class="roxyapi-credit"><a class="roxyapi-credit-link" href="'
			. esc_url( $href ) . '"'
			. ' rel="noopener" target="_blank">'
			. esc_html( $anchor )
			. '</a></p>';
	}

	/**
	 * Render the JSON-LD provider block. Default OFF; opt in via filter.
	 *
	 * @param string               $operation_id OpenAPI operation id.
	 * @param array<string, mixed> $data         Decoded API response.
	 * @return string
	 */
	public static function jsonld( string $operation_id, array $data ): string {
		/**
		 * Filter whether to emit per-card JSON-LD. Default false to avoid
		 * collisions with SEO plugins that already generate page-level
		 * structured data.
		 *
		 * @param bool $emit True to emit, false to skip.
		 */
		if ( ! apply_filters( 'roxyapi_emit_jsonld', false ) ) {
			return '';
		}

		$name = '';
		foreach ( array( 'name', 'title', 'sign', 'card' ) as $key ) {
			if ( isset( $data[ $key ] ) && is_scalar( $data[ $key ] ) && (string) $data[ $key ] !== '' ) {
				$name = (string) $data[ $key ];
				break;
			}
		}

		$is_article = self::is_article( $operation_id );

		$payload = array(
			'@context'           => 'https://schema.org',
			'@type'              => $is_article ? 'Article' : 'CreativeWork',
			'name'               => $name !== '' ? $name : $operation_id,
			'inLanguage'         => self::current_language(),
			'sourceOrganization' => array(
				'@type'  => 'Organization',
				'name'   => 'Roxy',
				'url'    => self::BRAND_URL,
				'sameAs' => self::SAME_AS,
			),
			'citation'           => array(
				'@type' => 'CreativeWork',
				'name'  => 'RoxyAPI Methodology',
				'url'   => self::METHODOLOGY_URL,
			),
		);

		$endpoint_url = self::endpoint_url( $operation_id );
		if ( $endpoint_url !== '' ) {
			$payload['isBasedOn'] = $endpoint_url;
		}

		if ( $is_article ) {
			$now                       = current_datetime();
			$payload['datePublished']  = $now->format( DATE_ATOM );
			$payload['dateModified']   = $now->format( DATE_ATOM );
			$payload['articleSection'] = self::section_for( $operation_id );
		}

		foreach ( array( 'meaning', 'description', 'summary', 'message', 'overview' ) as $key ) {
			if ( isset( $data[ $key ] ) && is_string( $data[ $key ] ) && $data[ $key ] !== '' ) {
				$payload['description'] = (string) $data[ $key ];
				break;
			}
		}
		foreach ( array( 'image', 'image_url', 'imageUrl' ) as $key ) {
			if ( isset( $data[ $key ] ) && is_string( $data[ $key ] ) && $data[ $key ] !== '' ) {
				$payload['image'] = (string) $data[ $key ];
				break;
			}
		}

		$json = wp_json_encode( $payload, JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			return '';
		}
		return '<script type="application/ld+json">' . $json . '</script>';
	}

	/**
	 * True when the site owner has opted in to the visible credit line.
	 * The `roxyapi_render_credit` filter still wins so a theme can force
	 * it off without touching the option.
	 *
	 * @return bool
	 */
	private static function should_show_credit(): bool {
		$opts  = \RoxyAPI\Admin\SettingsSchema::get_option();
		$opted = ! empty( $opts['attribution_show'] );
		/**
		 * Filter the resolved attribution-show decision. Defaults to the
		 * site owner's setting; returning false from a theme forces the
		 * credit off regardless.
		 *
		 * @param bool $show
		 */
		return (bool) apply_filters( 'roxyapi_render_credit', $opted );
	}

	/**
	 * Default anchor text per operation family, leading with the domain
	 * keyword and trailing with the brand. Falls back to a generic anchor.
	 *
	 * @param string $operation_id Operation id, e.g. "getDailyHoroscope".
	 * @return string
	 */
	private static function default_anchor( string $operation_id ): string {
		$section = self::section_for( $operation_id );
		if ( $section === '' ) {
			return __( 'Spiritual data by Roxy', 'roxyapi' );
		}
		return sprintf(
			/* translators: %s: lowercased domain section, e.g. "astrology" */
			__( '%s data by Roxy', 'roxyapi' ),
			ucfirst( $section )
		);
	}

	/**
	 * Map an operation id to its `articleSection` / brand domain bucket.
	 * Conservative — only matches well-known prefixes the spec defines.
	 *
	 * @param string $operation_id Operation id, e.g. "getDailyHoroscope".
	 * @return string Lowercased domain or '' when unknown.
	 */
	private static function section_for( string $operation_id ): string {
		// Look up the OpenAPI tag for this operation in the generated
		// registry, then map the tag to a brand-domain label via the
		// curated `Domains::all()` registry. Replaces a brittle
		// prefix-matching chain — every new endpoint already carries
		// its tag in `Endpoints::all()` so this stays accurate without
		// PHP edits.
		if ( ! class_exists( '\\RoxyAPI\\Generated\\Endpoints' ) ) {
			return '';
		}
		$entry = \RoxyAPI\Generated\Endpoints::get( $operation_id );
		if ( ! is_array( $entry ) || empty( $entry['tag'] ) ) {
			return '';
		}
		$tag = (string) $entry['tag'];

		if ( ! class_exists( '\\RoxyAPI\\Generated\\Domains' ) ) {
			return strtolower( $tag );
		}
		$domains = \RoxyAPI\Generated\Domains::all();
		if ( isset( $domains[ $tag ]['label'] ) ) {
			return strtolower( (string) $domains[ $tag ]['label'] );
		}
		return strtolower( $tag );
	}

	/**
	 * True when an operation produces time-bound editorial readings that
	 * deserve the `Article` schema type.
	 *
	 * @param string $operation_id Operation id, e.g. "getDailyHoroscope".
	 * @return bool
	 */
	private static function is_article( string $operation_id ): bool {
		foreach ( self::ARTICLE_PREFIXES as $prefix ) {
			if ( strpos( $operation_id, $prefix ) === 0 ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Resolve the canonical RoxyAPI endpoint URL for an operation id, used
	 * as the `isBasedOn` value. Reads from the generated registry; returns
	 * empty when the registry is missing or the op is unknown.
	 *
	 * @param string $operation_id Operation id, e.g. "getDailyHoroscope".
	 * @return string
	 */
	private static function endpoint_url( string $operation_id ): string {
		if ( ! class_exists( '\\RoxyAPI\\Generated\\Endpoints' ) ) {
			return '';
		}
		$entry = \RoxyAPI\Generated\Endpoints::get( $operation_id );
		if ( ! is_array( $entry ) || empty( $entry['path'] ) ) {
			return '';
		}
		return self::BRAND_URL . '/api/v2' . (string) $entry['path'];
	}

	/**
	 * Site language as a BCP-47 string for `inLanguage`. WordPress stores
	 * locales as `en_US`; schema.org wants `en-US`.
	 *
	 * @return string
	 */
	private static function current_language(): string {
		$raw = (string) get_bloginfo( 'language' );
		return $raw !== '' ? $raw : 'en';
	}
}
