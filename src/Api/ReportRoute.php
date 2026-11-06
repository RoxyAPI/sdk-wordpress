<?php
/**
 * Branded reports: the `[roxy_report]` visitor form and the REST route that
 * answers it with the finished document.
 *
 * The form is the template's own visitor form from the UI model, posted to
 * this route rather than to the page. The route checks the REST nonce, the
 * signed placement (this report, on a post the visitor may read), the per-form
 * rate limit and the visitor's consent, adds the site's branding from the
 * Branding and Reports tabs, calls the API server side, and streams the
 * document back: a PDF as a download, HTML as a page. Nothing is stored. A
 * visitor's details and the document exist for the length of the request; a
 * failure goes back to the form through the PRG redirect every visitor form
 * uses, carrying the message only, never what the visitor typed.
 *
 * @package RoxyAPI
 */

namespace RoxyAPI\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RoxyAPI\Admin\SettingsSchema;
use RoxyAPI\Generated\Reports;
use RoxyAPI\Support\ComponentRenderer;
use RoxyAPI\Support\FormRenderer;
use RoxyAPI\Support\FormRouter;
use RoxyAPI\Support\RateLimit;
use RoxyAPI\Support\Templates;
use RoxyAPI\Support\Theming;
use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

class ReportRoute {

	public const ROUTE = '/report/(?P<report>[a-z][a-z0-9-]*)';

	/** How long a placement token holds: as long as the REST nonce beside it. */
	private const PLACEMENT_TTL = DAY_IN_SECONDS;

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_route' ) );
		add_filter( 'rest_pre_serve_request', array( self::class, 'serve' ), 10, 3 );
		add_filter( 'rest_authentication_errors', array( self::class, 'stale_nonce' ), 101 );
	}

	/**
	 * Core answers a stale REST nonce (a page served from a cache past the nonce
	 * lifetime) with a JSON 403 before any route runs, which a visitor reads as
	 * a broken page. On this route the refusal is left to handle(), which
	 * rejects the nonce itself and sends the visitor back to the form.
	 *
	 * @param mixed $result Authentication result so far.
	 * @return mixed
	 */
	public static function stale_nonce( $result ) {
		$route = (string) ( $GLOBALS['wp']->query_vars['rest_route'] ?? '' );
		return is_wp_error( $result ) && $result->get_error_code() === 'rest_cookie_invalid_nonce' && strpos( $route, '/' . GeocodeRoute::NAMESPACE . '/report/' ) === 0 ? null : $result;
	}

	public static function register_route(): void {
		register_rest_route(
			GeocodeRoute::NAMESPACE,
			self::ROUTE,
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'handle' ),
					// A visitor form: the nonce, rate limit and consent are
					// checked in handle(), where a refusal can go back to the form.
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * The visitor form of the placement's report, posting to this route.
	 *
	 * @param array<string, string> $atts Resolved `[roxy_report]` attributes.
	 * @return string HTML.
	 */
	public static function form( array $atts ): string {
		$report   = sanitize_key( (string) ( $atts['report'] ?? '' ) );
		$template = Reports::templates()[ $report ] ?? null;
		$form     = $template ? FormRouter::form_class( $template['operation_id'] ) : null;
		if ( $form === null ) {
			return Templates::api_error( Client::not_configured() );
		}
		$format   = in_array( $atts['format'] ?? '', Reports::FORMATS, true ) ? (string) $atts['format'] : Reports::FORMATS[0];
		$readings = ! ComponentRenderer::hide_readings( $atts['hide_readings'] ?? null );
		$post_id  = (int) get_the_ID();
		$expires  = time() + self::PLACEMENT_TTL;
		// The placement's resolved display choices ride the action, like the
		// format, signed with the post that shows it.
		$query = array( 'format' => $format ) + ( $readings ? array() : array( 'readings' => '0' ) ) + array(
			'placement' => $post_id . '.' . $expires . '.' . self::sign( $report, $format, $readings, $post_id, $expires ),
		);
		return FormRenderer::render( $form, $atts, add_query_arg( $query, rest_url( GeocodeRoute::NAMESPACE . '/report/' . $report ) ) );
	}

	/**
	 * The signature binding one placement: which report, in which format, with
	 * or without its readings, shown on which post, until when. The REST nonce
	 * is the same for every logged-out visitor on every page, so without it a
	 * nonce from a free report would generate any other report on the site.
	 *
	 * @param string $report   Report id.
	 * @param string $format   One of `Reports::FORMATS`.
	 * @param bool   $readings Whether the report carries its written readings.
	 * @param int    $post_id  The post showing the form, or 0 outside any post.
	 * @param int    $expires  Unix time the token stops holding.
	 * @return string
	 */
	private static function sign( string $report, string $format, bool $readings, int $post_id, int $expires ): string {
		return wp_hash( implode( '|', array( 'roxyapi_report', $report, $format, (int) $readings, $post_id, $expires ) ), 'nonce' );
	}

	/**
	 * Whether a post carries a live placement token for this exact request,
	 * shown on a post the current visitor may read.
	 *
	 * @param string $token    The posted `placement`: `{post id}.{expires}.{signature}`.
	 * @param string $report   Report id.
	 * @param string $format   One of `Reports::FORMATS`.
	 * @param bool   $readings Whether the report carries its written readings.
	 * @return bool
	 */
	private static function placement_allowed( string $token, string $report, string $format, bool $readings ): bool {
		if ( preg_match( '/^(\d+)\.(\d+)\.([0-9a-f]+)$/', $token, $match ) !== 1 || (int) $match[2] < time() ) {
			return false;
		}
		$post_id = (int) $match[1];
		if ( ! hash_equals( self::sign( $report, $format, $readings, $post_id, (int) $match[2] ), $match[3] ) ) {
			return false;
		}
		if ( $post_id === 0 ) {
			return true;
		}
		// The rules WordPress uses to show the post, and never past its
		// password: a guest reads a published post; a signed-in visitor reads
		// what `read_post` grants, so a membership plugin that withdraws it
		// withdraws the form with it.
		$post = get_post( $post_id );
		return $post instanceof WP_Post
			&& ( is_user_logged_in() ? current_user_can( 'read_post', $post_id ) : is_post_publicly_viewable( $post ) )
			&& ! post_password_required( $post );
	}

	/**
	 * Answer one report form post: the document, or a redirect back to the form.
	 *
	 * @param WP_REST_Request $request The visitor's form post.
	 * @return WP_REST_Response
	 */
	public static function handle( WP_REST_Request $request ): WP_REST_Response {
		$report   = (string) $request->get_param( 'report' );
		$template = Reports::templates()[ $report ] ?? null;
		if ( $template === null ) {
			return new WP_REST_Response( '', 404 );
		}
		$form_id = $template['operation_id'];

		// Core lets a post with no nonce through as a logged-out request, so the
		// route requires one itself; one that is present core has verified.
		$format   = in_array( $request->get_param( 'format' ), Reports::FORMATS, true ) ? (string) $request->get_param( 'format' ) : Reports::FORMATS[0];
		$readings = $request->get_param( 'readings' ) !== '0';
		if ( ! wp_verify_nonce( (string) $request->get_param( '_wpnonce' ), 'wp_rest' ) || ! self::placement_allowed( (string) $request->get_param( 'placement' ), $report, $format, $readings ) ) {
			return self::back( $form_id, array( 'error' => __( 'Could not verify the form. Reload the page and try again.', 'roxyapi' ) ) );
		}
		if ( ! RateLimit::check( 'form_' . $form_id ) ) {
			return self::back( $form_id, array( 'error' => __( 'Too many submissions. Please wait a minute and try again.', 'roxyapi' ) ) );
		}
		if ( empty( $request->get_param( 'roxyapi_consent' ) ) ) {
			return self::back( $form_id, array( 'validation_errors' => array( '_consent' => __( 'Please confirm consent before submitting.', 'roxyapi' ) ) ) );
		}

		$outcome = self::deliver( $report, $format, $request->get_body_params(), $readings );
		return isset( $outcome['document'] ) ? self::document( $outcome['document'] ) : self::back( $form_id, $outcome );
	}

	/**
	 * Build one report from a visitor post: validated by the template's form
	 * spec, branded from the Branding and Reports tabs, rendered by the API.
	 *
	 * @param string              $report Report id, a key of `Reports::templates()`.
	 * @param string              $format One of `Reports::FORMATS`.
	 * @param array<string,mixed> $post     Posted values.
	 * @param bool                $readings Whether the report carries its written readings.
	 * @return array<string,mixed> `document` (`pdf`, `title` and the `bytes` to send), `validation_errors` or `wp_error`.
	 */
	public static function deliver( string $report, string $format, array $post, bool $readings = true ): array {
		$form = FormRouter::form_class( Reports::templates()[ $report ]['operation_id'] ?? '' );
		if ( $form === null ) {
			return array( 'wp_error' => FormRouter::error_payload( Client::not_configured() ) );
		}
		$prepared = FormRouter::prepare( $form, $post );
		if ( ! isset( $prepared['body'] ) ) {
			return array( 'validation_errors' => $prepared['validation_errors'] );
		}

		// What the visitor chose wins over the site default (a page size).
		$result = $form::call( array_replace_recursive( self::request( $format, $readings ), $prepared['body'] ) );
		if ( is_wp_error( $result ) ) {
			return array( 'wp_error' => FormRouter::error_payload( $result ) );
		}
		$pdf = ( $result['format'] ?? '' ) === 'pdf';
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- The API sends the PDF base64 encoded in its JSON answer.
		$bytes = $pdf ? base64_decode( (string) ( $result['pdf'] ?? '' ), true ) : (string) ( $result['html'] ?? '' );
		if ( ! is_string( $bytes ) || $bytes === '' ) {
			return array(
				'wp_error' => FormRouter::error_payload(
					new WP_Error( 'roxyapi_report', __( 'This reading could not be loaded right now. Please try again.', 'roxyapi' ) )
				),
			);
		}
		return array(
			'document' => array(
				'pdf'   => $pdf,
				'title' => (string) ( $result['title'] ?? '' ),
				'bytes' => $bytes,
			),
		);
	}

	/**
	 * The brand name a report carries while the Reports tab leaves it empty: the
	 * site title, held to the length a report prints.
	 *
	 * @return string
	 */
	public static function site_name(): string {
		return mb_substr( wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), 0, Reports::NAME_MAX_LENGTH );
	}

	/**
	 * The parts of a report request no visitor fills in: the format the
	 * placement asks for and whether it keeps the written readings, the name,
	 * logo, footer, page size and language the Reports tab holds (an empty name
	 * takes the site title and an empty logo the site logo; the site address is
	 * the website on the cover), and the look of the Branding tab, so a report
	 * wears the same preset, colours and font as the readings on the site. A
	 * report is a printed page, so it takes the light colours: the accent as its
	 * accent, the text colour as its heading ink.
	 *
	 * @param string $format   One of `Reports::FORMATS`.
	 * @param bool   $readings Whether the report carries its written readings; the API default is yes.
	 * @return array<string, mixed>
	 */
	public static function request( string $format, bool $readings = true ): array {
		$opts   = SettingsSchema::get_option();
		$logo   = wp_get_attachment_image_url( (int) $opts['report_logo'] > 0 ? (int) $opts['report_logo'] : (int) get_theme_mod( 'custom_logo' ), 'full' );
		$light  = Theming::palette()['light'];
		$colors = array_map(
			// The API takes six digits; the colour fields also accept three.
			static fn( string $hex ): string => (string) preg_replace( '/^#(\w)(\w)(\w)$/', '#$1$1$2$2$3$3', $hex ),
			array_filter(
				array(
					'primary' => $light['fg'] ?? '',
					'accent'  => $light['accent'] ?? '',
				)
			)
		);
		$branding = array_filter(
			array(
				'name'    => '' !== (string) $opts['report_name'] ? (string) $opts['report_name'] : self::site_name(),
				// The API fetches the logo itself, over https only.
				'logo'    => is_string( $logo ) && wp_parse_url( $logo, PHP_URL_SCHEME ) === 'https' ? $logo : '',
				'footer'  => (string) $opts['report_footer'],
				'website' => home_url( '/' ),
				'colors'  => $colors,
				'font'    => Theming::font(),
				'theme'   => in_array( $opts['palette_preset'], Reports::THEMES, true ) ? (string) $opts['palette_preset'] : '',
			)
		);
		return array_filter(
			array(
				'format'   => $format,
				'lang'     => (string) $opts['report_language'],
				'branding' => $branding,
				'page'     => array_filter( array( 'size' => (string) $opts['report_page_size'] ) ),
			)
		) + ( $readings ? array() : array( 'readings' => false ) );
	}

	/**
	 * Print a document response as its own bytes. WordPress has already sent
	 * the status and the headers the response carries; every other route is
	 * left to the JSON server.
	 *
	 * @param bool            $served  Whether a filter already served the request.
	 * @param mixed           $result  The response about to be served.
	 * @param WP_REST_Request $request The request.
	 * @return bool
	 */
	public static function serve( $served, $result, $request ): bool {
		if ( $served || ! $result instanceof WP_REST_Response || ! is_string( $result->get_data() ) || strpos( $request->get_route(), '/' . GeocodeRoute::NAMESPACE . '/report/' ) !== 0 ) {
			return (bool) $served;
		}
		echo $result->get_data(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The document itself, sent with its own Content-Type; HTML is sandboxed by its Content-Security-Policy.
		return true;
	}

	/**
	 * The finished document: a PDF to save, or HTML to read in the browser.
	 *
	 * @param array{pdf: bool, title: string, bytes: string} $document What `deliver()` built.
	 * @return WP_REST_Response
	 */
	private static function document( array $document ): WP_REST_Response {
		$name = sanitize_file_name( $document['title'] );
		$name = ( $name !== '' ? $name : 'report' ) . ( $document['pdf'] ? '.pdf' : '.html' );
		return new WP_REST_Response(
			$document['bytes'],
			200,
			array(
				'Content-Type'            => $document['pdf'] ? 'application/pdf' : 'text/html; charset=utf-8',
				'Content-Disposition'     => ( $document['pdf'] ? 'attachment' : 'inline' ) . '; filename="' . $name . '"',
				'Cache-Control'           => 'no-store, private',
				'Content-Security-Policy' => "sandbox; default-src 'none'; style-src 'unsafe-inline'; img-src data: https:; font-src data:",
			)
		);
	}

	/**
	 * Send the visitor back to the form with a message: the GET half of the PRG
	 * cycle, holding no visitor input.
	 *
	 * @param string               $form_id Form id of the template.
	 * @param array<string, mixed> $payload `error`, `validation_errors` or `wp_error`.
	 * @return WP_REST_Response
	 */
	private static function back( string $form_id, array $payload ): WP_REST_Response {
		return new WP_REST_Response( '', 303, array( 'Location' => FormRouter::result_url( $form_id, $payload ) ) );
	}
}
