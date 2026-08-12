<?php
/**
 * Month paging for the monthly ephemeris, as a shortcode and as a block.
 *
 * Both operations default to the month in progress, so a published ephemeris
 * page shows that month and nothing else: a visitor cannot look at the month
 * before or after it without the site owner publishing one page per month. This
 * adds a month to the query string, a previous / next pair, and a month and
 * year picker under the table.
 *
 * It belongs to the plugin rather than to the chart component because the
 * component would have to fetch the new month from the browser, and the API key
 * never leaves the server here.
 *
 * Four filters carry it and none of them needs the generated shortcodes or
 * blocks to change. Each authoring path needs the same two things done, so they
 * come in pairs: one filter rewrites the year and month the render asks the API
 * for, the other appends the nav to what came back.
 *
 *   shortcode  `shortcode_atts_{$tag}`  then  `do_shortcode_tag`
 *   block      `render_block_data`      then  `render_block`
 *
 * A block needs its own pair because its `render.php` calls the generated
 * shortcode class directly with no tag, so `shortcode_atts()` filters under the
 * empty name and `do_shortcode_tag` never runs. Both pairs call the SAME
 * resolution, validation and rendering below; a second copy of any of it is how
 * the two paths would start paging differently on the same page.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use DateTimeZone;
use RoxyAPI\Generated\Shortcodes\GetMonthlyEphemeris;
use RoxyAPI\Generated\Shortcodes\GetMonthlyTropicalEphemeris;
use WP_Locale;

class EphemerisNav {

	/**
	 * The month being read, as `YYYY-MM`. Every link the nav emits sets this
	 * one, and two ephemeris shortcodes on the same page therefore share it and
	 * page together. That is deliberate: one visible control that moved half the
	 * page would read as broken.
	 */
	public const QUERY_VAR = 'roxy_eph';

	/**
	 * The picker posts the year and the month as two values because a GET form
	 * cannot join two `<select>` controls into one parameter without JavaScript,
	 * and this nav ships none. They are read only when {@see QUERY_VAR} is
	 * absent, so a link always wins over a pair left in a bookmarked URL.
	 */
	public const YEAR_VAR = 'roxy_eph_year';

	/** Month half of the picker pair. @see YEAR_VAR */
	public const MONTH_VAR = 'roxy_eph_month';

	/**
	 * Year bounds, read off the live spec rather than chosen here: `year` on
	 * both `/astrology/planets/monthly` and
	 * `/vedic-astrology/planetary-positions/monthly` is an integer with minimum
	 * 1900 and maximum 2100. Paging stops at them and the query string is
	 * refused outside them, so the nav can never link to a month the API will
	 * reject with a 400.
	 */
	private const MIN_YEAR = 1900;

	/** Upper year bound. @see MIN_YEAR */
	private const MAX_YEAR = 2100;

	/**
	 * Marks a rendered nav. Built into the opening tag below, so it cannot go
	 * stale, and used to recognise output that already carries one.
	 */
	private const MARKER = 'class="roxyapi-eph-nav"';

	/**
	 * The two placements this pages: shortcode tag on the left, block name on
	 * the right. One map rather than two lists, because they are the same
	 * endpoint authored two ways and a page has to page identically whichever
	 * one the owner reached for. The tags come from the generated classes so a
	 * renamed shortcode cannot leave the nav pointing at a tag that is gone.
	 *
	 * @return array<string, string>
	 */
	private static function paged(): array {
		return array(
			GetMonthlyTropicalEphemeris::TAG => 'roxyapi/get-monthly-tropical-ephemeris',
			GetMonthlyEphemeris::TAG         => 'roxyapi/get-monthly-ephemeris',
		);
	}

	/**
	 * The shortcode tags this pages.
	 *
	 * @return array<int, string>
	 */
	public static function tags(): array {
		return array_keys( self::paged() );
	}

	/**
	 * The block names this pages.
	 *
	 * @return array<int, string>
	 */
	public static function blocks(): array {
		return array_values( self::paged() );
	}

	/**
	 * Hook both pairs.
	 *
	 * @return void
	 */
	public static function register(): void {
		foreach ( self::tags() as $tag ) {
			add_filter( 'shortcode_atts_' . $tag, array( self::class, 'filter_atts' ) );
		}
		add_filter( 'do_shortcode_tag', array( self::class, 'append_nav' ), 10, 3 );

		add_filter( 'render_block_data', array( self::class, 'filter_block_data' ) );
		add_filter( 'render_block', array( self::class, 'append_block_nav' ), 10, 2 );
	}

	/**
	 * Point the shortcode at the month the visitor asked for.
	 *
	 * @param array<string, string> $out Attributes resolved by `shortcode_atts()`.
	 * @return array<string, string>
	 */
	public static function filter_atts( $out ): array {
		return is_array( $out ) ? self::apply_query_month( $out ) : array();
	}

	/**
	 * Point the block at the same month, before it renders.
	 *
	 * `render_block_data` is the last point at which a parsed block is still
	 * mutable, and its `attrs` are what `render.php` hands to the shortcode
	 * class. `year` and `month` are single words, so the camelCase block key and
	 * the snake_case shortcode key are the same string here. That is a
	 * coincidence of these two names and not a rule: anything multi-word would
	 * need the rewrite {@link BlockOutput::to_shortcode_atts} does.
	 *
	 * @param array<string, mixed> $parsed_block A parsed block, pre-render.
	 * @return array<string, mixed>
	 */
	public static function filter_block_data( $parsed_block ) {
		if ( ! is_array( $parsed_block ) || ! self::is_paged_block( $parsed_block ) ) {
			return $parsed_block;
		}
		$attrs                 = isset( $parsed_block['attrs'] ) && is_array( $parsed_block['attrs'] ) ? $parsed_block['attrs'] : array();
		$parsed_block['attrs'] = self::apply_query_month( $attrs );
		return $parsed_block;
	}

	/**
	 * Append the nav to a rendered ephemeris shortcode.
	 *
	 * Appended for an API error too, since the nav is then the only way back to
	 * a month that works.
	 *
	 * @param string               $output Rendered shortcode output.
	 * @param string               $tag    Shortcode tag.
	 * @param array<string, mixed> $attr   Attributes as written on the page.
	 * @return string
	 */
	public static function append_nav( $output, $tag, $attr ): string {
		$output = is_string( $output ) ? $output : '';
		if ( ! in_array( (string) $tag, self::tags(), true ) ) {
			return $output;
		}
		return $output . self::nav( is_array( $attr ) ? $attr : array() );
	}

	/**
	 * Append the nav to a rendered ephemeris block.
	 *
	 * Output that already carries a nav is left alone. A block whose markup
	 * reaches `do_shortcode` would have been served by the shortcode half
	 * already, and two navs under one table would page against each other.
	 *
	 * @param string               $block_content Rendered block markup.
	 * @param array<string, mixed> $block         The parsed block.
	 * @return string
	 */
	public static function append_block_nav( $block_content, $block ): string {
		$content = is_string( $block_content ) ? $block_content : '';
		if ( ! is_array( $block ) || ! self::is_paged_block( $block ) ) {
			return $content;
		}
		if ( strpos( $content, self::MARKER ) !== false ) {
			return $content;
		}
		$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
		return $content . self::nav( $attrs );
	}

	/**
	 * Whether a parsed block is one of the two this pages.
	 *
	 * @param array<string, mixed> $block A parsed block.
	 * @return bool
	 */
	private static function is_paged_block( array $block ): bool {
		$name = isset( $block['blockName'] ) ? $block['blockName'] : '';
		return is_string( $name ) && in_array( $name, self::blocks(), true );
	}

	/**
	 * Overwrite the year and month with the one the visitor asked for, if any.
	 *
	 * The single home of the precedence rule, called from both entry points: a
	 * `year` or `month` attribute sets the month the page STARTS on, and the
	 * query string overrides it while the visitor is paging. Without that, the
	 * nav would render on a pinned page and then do nothing when clicked. A
	 * shortcode resolves its attributes through `shortcode_atts()` and a block
	 * carries its own `attrs`, but the rule they apply has to be one function or
	 * the two authoring paths drift.
	 *
	 * @param array<string, mixed> $atts Attributes keyed `year` and `month`.
	 * @return array<string, mixed>
	 */
	private static function apply_query_month( array $atts ): array {
		$month = self::from_query();
		if ( $month === null ) {
			return $atts;
		}
		$atts['year']  = (string) $month[0];
		$atts['month'] = (string) $month[1];
		return $atts;
	}

	/**
	 * The whole nav for one placement.
	 *
	 * @param array<string, mixed> $attr Attributes as written on the page.
	 * @return string
	 */
	private static function nav( array $attr ): string {
		list( $year, $month ) = self::current( $attr );

		$base = self::base_url();

		return '<nav ' . self::MARKER . ' aria-label="' . esc_attr__( 'Ephemeris month', 'roxyapi' ) . '">'
			. '<div class="roxyapi-eph-steps">'
			. self::step_link( self::step( $year, $month, -1 ), $base, __( 'Previous month', 'roxyapi' ), 'prev' )
			. '<span class="roxyapi-eph-current">' . esc_html( self::label( $year, $month ) ) . '</span>'
			. self::step_link( self::step( $year, $month, 1 ), $base, __( 'Next month', 'roxyapi' ), 'next' )
			. '</div>'
			. self::picker( $year, $month )
			. '</nav>';
	}

	/**
	 * One paging link, or an inert label at the end of the range the API covers.
	 *
	 * @param array<int, int>|null $target Year and month to link to, or null at the bound.
	 * @param string               $base   Current URL the link is built from.
	 * @param string               $label  Visitor-facing text.
	 * @param string               $rel    `prev` or `next`.
	 * @return string
	 */
	private static function step_link( ?array $target, string $base, string $label, string $rel ): string {
		if ( $target === null ) {
			return '<span class="roxyapi-eph-link is-disabled" aria-disabled="true">' . esc_html( $label ) . '</span>';
		}
		$url = add_query_arg( self::QUERY_VAR, self::value( $target[0], $target[1] ), $base );
		return '<a class="roxyapi-eph-link" rel="' . esc_attr( $rel ) . '" href="' . esc_url( $url ) . '">'
			. esc_html( $label )
			. '</a>';
	}

	/**
	 * The month and year selects, as a plain GET form.
	 *
	 * @param int $year  Year on screen.
	 * @param int $month Month on screen.
	 * @return string
	 */
	private static function picker( int $year, int $month ): string {
		$out = '<form class="roxyapi-eph-form" method="get" action="' . esc_url( self::form_action() ) . '">'
			. self::carried_fields();

		$out .= '<label class="roxyapi-eph-field"><span>' . esc_html__( 'Month', 'roxyapi' ) . '</span>'
			. '<select name="' . esc_attr( self::MONTH_VAR ) . '">';
		foreach ( self::month_names() as $number => $name ) {
			$out .= '<option value="' . esc_attr( sprintf( '%02d', $number ) ) . '"'
				. selected( $number, $month, false ) . '>' . esc_html( $name ) . '</option>';
		}
		$out .= '</select></label>';

		$out .= '<label class="roxyapi-eph-field"><span>' . esc_html__( 'Year', 'roxyapi' ) . '</span>'
			. '<select name="' . esc_attr( self::YEAR_VAR ) . '">';
		for ( $candidate = self::MIN_YEAR; $candidate <= self::MAX_YEAR; $candidate++ ) {
			$out .= '<option value="' . esc_attr( (string) $candidate ) . '"'
				. selected( $candidate, $year, false ) . '>' . esc_html( (string) $candidate ) . '</option>';
		}
		$out .= '</select></label>';

		return $out . '<button type="submit" class="roxyapi-eph-submit">'
			. esc_html__( 'Show month', 'roxyapi' )
			. '</button></form>';
	}

	/**
	 * The rest of the query string, as hidden fields.
	 *
	 * A browser drops the query string of a GET form action, so on a site with
	 * plain permalinks (`?page_id=12`) a picker without these would submit to
	 * the front page and lose the ephemeris entirely. The three parameters this
	 * class owns are left out: two are what the selects post, and the third is
	 * the one they replace.
	 *
	 * @return string
	 */
	private static function carried_fields(): string {
		$query = (string) wp_parse_url( self::request_uri(), PHP_URL_QUERY );
		$args  = array();
		wp_parse_str( $query, $args );

		$ours = array( self::QUERY_VAR, self::YEAR_VAR, self::MONTH_VAR );
		$out  = '';
		foreach ( $args as $key => $value ) {
			$key = (string) $key;
			if ( in_array( $key, $ours, true ) || ! is_scalar( $value ) ) {
				continue;
			}
			if ( preg_match( '/^[A-Za-z0-9_-]+$/', $key ) !== 1 ) {
				continue;
			}
			$out .= '<input type="hidden" name="' . esc_attr( $key ) . '"'
				. ' value="' . esc_attr( sanitize_text_field( (string) $value ) ) . '" />';
		}
		return $out;
	}

	/**
	 * The month on screen, as `array( year, month )`.
	 *
	 * @param array<string, mixed> $attr Attributes as written on the page.
	 * @return array<int, int>
	 */
	private static function current( array $attr ): array {
		$asked = self::from_query();
		if ( $asked !== null ) {
			return $asked;
		}

		$now   = self::now();
		$year  = self::year( $attr['year'] ?? null );
		$month = self::month( $attr['month'] ?? null );

		return array( $year ?? $now[0], $month ?? $now[1] );
	}

	/**
	 * The month the query string asks for, or null when it does not ask for one
	 * this API can answer.
	 *
	 * Anything unreadable is silently not an opinion, so the shortcode default
	 * stands and the page still renders. `roxy_eph` decides on its own whenever
	 * it is present, valid or not, so a bad link cannot fall through to a stale
	 * picker pair further along the same URL.
	 *
	 * @return array<int, int>|null
	 */
	private static function from_query(): ?array {
		$asked = self::query_value( self::QUERY_VAR );
		if ( $asked !== '' ) {
			if ( preg_match( '/^(\d{4})-(\d{2})$/', $asked, $parts ) !== 1 ) {
				return null;
			}
			$year  = self::year( $parts[1] );
			$month = self::month( $parts[2] );
			return $year !== null && $month !== null ? array( $year, $month ) : null;
		}

		$year  = self::year( self::query_value( self::YEAR_VAR ) );
		$month = self::month( self::query_value( self::MONTH_VAR ) );
		return $year !== null && $month !== null ? array( $year, $month ) : null;
	}

	/**
	 * One query parameter as a plain string.
	 *
	 * @param string $key Parameter name.
	 * @return string
	 */
	private static function query_value( string $key ): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only navigation state on a public page. Every value is narrowed to a year or a month before it is used or echoed.
		return isset( $_GET[ $key ] ) ? (string) sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '';
	}

	/**
	 * A year the API accepts, or null.
	 *
	 * @param mixed $value Raw value.
	 * @return int|null
	 */
	private static function year( $value ): ?int {
		if ( ! is_scalar( $value ) || preg_match( '/^\d{4}$/', trim( (string) $value ) ) !== 1 ) {
			return null;
		}
		$year = (int) trim( (string) $value );
		return $year >= self::MIN_YEAR && $year <= self::MAX_YEAR ? $year : null;
	}

	/**
	 * A month the API accepts, or null.
	 *
	 * @param mixed $value Raw value.
	 * @return int|null
	 */
	private static function month( $value ): ?int {
		if ( ! is_scalar( $value ) || preg_match( '/^\d{1,2}$/', trim( (string) $value ) ) !== 1 ) {
			return null;
		}
		$month = (int) trim( (string) $value );
		return $month >= 1 && $month <= 12 ? $month : null;
	}

	/**
	 * The month both operations fall back to when nothing names one: the current
	 * one in UTC, which is what their spec documents. Reading the site timezone
	 * instead would put the nav a month away from the table it sits under on the
	 * first and last day of a month.
	 *
	 * @return array<int, int>
	 */
	private static function now(): array {
		return array( (int) gmdate( 'Y' ), (int) gmdate( 'n' ) );
	}

	/**
	 * The month `$delta` months away, or null past the end of the range.
	 *
	 * @param int $year  Year on screen.
	 * @param int $month Month on screen.
	 * @param int $delta Months to move.
	 * @return array<int, int>|null
	 */
	private static function step( int $year, int $month, int $delta ): ?array {
		$month += $delta;
		if ( $month < 1 ) {
			--$year;
			$month = 12;
		} elseif ( $month > 12 ) {
			++$year;
			$month = 1;
		}
		return $year >= self::MIN_YEAR && $year <= self::MAX_YEAR ? array( $year, $month ) : null;
	}

	/**
	 * `YYYY-MM` for the query string.
	 *
	 * @param int $year  Year.
	 * @param int $month Month.
	 * @return string
	 */
	private static function value( int $year, int $month ): string {
		return sprintf( '%04d-%02d', $year, $month );
	}

	/**
	 * The month on screen in the site language. Built through `wp_date()` in UTC
	 * so the month name is translated and the date it names is the one the API
	 * calculated.
	 *
	 * @param int $year  Year.
	 * @param int $month Month.
	 * @return string
	 */
	private static function label( int $year, int $month ): string {
		$timestamp = gmmktime( 12, 0, 0, $month, 1, $year );
		if ( $timestamp === false ) {
			return self::value( $year, $month );
		}
		$label = wp_date( 'F Y', $timestamp, new DateTimeZone( 'UTC' ) );
		return is_string( $label ) ? $label : self::value( $year, $month );
	}

	/**
	 * Month names from the site locale, keyed 1 to 12. `WP_Locale` is what every
	 * core date control reads, so the picker speaks the language the rest of the
	 * page does instead of shipping an English list of its own.
	 *
	 * @return array<int, string>
	 */
	private static function month_names(): array {
		$locale = $GLOBALS['wp_locale'] ?? null;
		$out    = array();
		for ( $month = 1; $month <= 12; $month++ ) {
			$padded        = sprintf( '%02d', $month );
			$out[ $month ] = $locale instanceof WP_Locale ? $locale->get_month( $padded ) : $padded;
		}
		return $out;
	}

	/**
	 * The URL the paging links are built from: this exact request, minus the
	 * picker pair so a submitted form cannot outlive the link that follows it.
	 *
	 * @return string
	 */
	private static function base_url(): string {
		return remove_query_arg(
			array( self::YEAR_VAR, self::MONTH_VAR ),
			self::origin() . self::request_uri()
		);
	}

	/**
	 * The URL the picker submits to: this request without its query string,
	 * which the browser would drop anyway. What has to survive is carried as
	 * hidden fields instead.
	 *
	 * @return string
	 */
	private static function form_action(): string {
		$path = (string) wp_parse_url( self::request_uri(), PHP_URL_PATH );
		return self::origin() . ( $path !== '' ? $path : '/' );
	}

	/**
	 * Scheme, host and port from `home_url()`, so every link stays on the site
	 * the visitor is already on. Joined to the request path rather than passed
	 * through `home_url( $path )`, because the request path already carries the
	 * subdirectory of a subdirectory install and `home_url()` would add it twice.
	 *
	 * @return string
	 */
	private static function origin(): string {
		$parts = wp_parse_url( home_url( '/' ) );
		if ( ! is_array( $parts ) || ! isset( $parts['host'] ) ) {
			return untrailingslashit( home_url( '/' ) );
		}
		$origin = ( isset( $parts['scheme'] ) ? (string) $parts['scheme'] : 'http' ) . '://' . (string) $parts['host'];
		return isset( $parts['port'] ) ? $origin . ':' . (int) $parts['port'] : $origin;
	}

	/**
	 * The path and query of this request. `add_query_arg()` with nothing to add
	 * returns it verbatim, which keeps the superglobal read inside core. It is
	 * not escaped there, so every caller here escapes at the point it emits.
	 *
	 * @return string
	 */
	private static function request_uri(): string {
		return isset( $_SERVER['REQUEST_URI'] ) ? (string) add_query_arg( array() ) : '/';
	}
}
