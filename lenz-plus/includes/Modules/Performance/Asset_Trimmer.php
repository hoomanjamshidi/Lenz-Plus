<?php
/**
 * Leaves the stylesheets and scripts of Asset_Groups out of pages whose
 * markup does not use them.
 *
 * What a page holds is only known once it is printed (Elementor widgets,
 * shortcodes, menus, the footer), so the page is buffered and the unused
 * `<link>`/`<script>` tags are removed from the finished HTML by their
 * WordPress ids (`<handle>-css`, `<handle>-js`, `-js-extra`…).
 *
 * WP Rocket compatibility: its page buffer starts before WordPress loads,
 * ours on `template_redirect`, so ours is the inner one and runs first. WP
 * Rocket then minifies, combines, delays and caches the already lighter page,
 * and cached copies keep the result at no cost. Autoptimize and LiteSpeed
 * buffer the same way.
 *
 * Safety: markers are looked for in the body with external scripts, styles
 * and link tags taken out (inline scripts stay, so markup inside JS templates
 * still counts); a wrong match only keeps a file. Partial flushes are passed
 * through untouched. When a jQuery plugin is left out, a stand-in is printed
 * right after jQuery so Lenz's scripts that call it without checking keep
 * running; without a jQuery tag to anchor it, those groups are kept.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Performance;

defined( 'ABSPATH' ) || exit;

/**
 * Output buffer that trims unused theme assets.
 */
final class Asset_Trimmer {

	/** Id of the stand-in script. */
	public const STANDIN_ID = 'lzp-perf-standin';

	/** WordPress's jQuery tag, the stand-in's anchor. */
	private const JQUERY_TAG = '#<script\b[^>]*\bid=(["\'])jquery-core-js\1[^>]*>\s*</script>#i';

	/** @var array<string, array> Groups from Asset_Groups. */
	private $groups;

	/**
	 * @param array<string, array> $groups Groups that may be left out.
	 */
	public function __construct( array $groups ) {
		$this->groups = $groups;
	}

	/** Starts the buffer late on `template_redirect`, after redirects and the studio panel. */
	public function register(): void {
		if ( $this->groups ) {
			add_action( 'template_redirect', array( $this, 'start' ), 1000 );
		}
	}

	/** Buffers ordinary front-end page views. */
	public function start(): void {
		if ( $this->should_buffer() ) {
			ob_start( array( $this, 'process' ) );
		}
	}

	/**
	 * Output callback: the page with unused groups removed.
	 *
	 * @param string $html  Buffered output.
	 * @param int    $phase Output handler phase flags.
	 * @return string
	 */
	public function process( $html, $phase = PHP_OUTPUT_HANDLER_FINAL ) {
		$whole = ( $phase & PHP_OUTPUT_HANDLER_START ) && ( $phase & PHP_OUTPUT_HANDLER_FINAL );
		if ( ! $whole || ! is_string( $html ) ) {
			// Someone flushed part of the page early: markers in the rest would be missed.
			return $html;
		}

		$body_at = stripos( $html, '<body' );
		if ( false === $body_at || false === stripos( $html, '</html>' ) ) {
			return $html;
		}

		return $this->trim( $html, self::markup( substr( $html, $body_at ) ) );
	}

	/**
	 * Removes the groups the page does not need and adds the stand-ins.
	 *
	 * @param string $html   Whole page.
	 * @param string $markup Body without external scripts and styles.
	 */
	private function trim( string $html, string $markup ): string {
		$has_jquery = (bool) preg_match( self::JQUERY_TAG, $html );
		$standins   = '';

		foreach ( $this->groups as $group ) {
			if ( ! $this->can_drop( $group, $html, $markup, $has_jquery ) ) {
				continue;
			}

			foreach ( $group['styles'] as $handle ) {
				$html = self::remove_style( $html, $handle );
			}
			foreach ( $group['scripts'] as $handle ) {
				$html = self::remove_script( $html, $handle );
			}
			$standins .= $group['standin'];
		}

		if ( '' === $standins ) {
			return $html;
		}

		$script = sprintf(
			'<script id="%s">(function(w){var n=function(){return this};function s(){var $=w.jQuery;if(!$){return false}%s return true}if(!s()){document.addEventListener("DOMContentLoaded",s)}})(window);</script>',
			self::STANDIN_ID,
			$standins
		);

		// The DOMContentLoaded fallback covers jQuery being deferred by an optimiser.
		return (string) preg_replace_callback(
			self::JQUERY_TAG,
			static function ( array $tag ) use ( $script ): string {
				return $tag[0] . "\n" . $script;
			},
			$html,
			1
		);
	}

	/**
	 * Whether a group can be left out of this page.
	 *
	 * @param array  $group      Group.
	 * @param string $html       Whole page.
	 * @param string $markup     Body without external scripts and styles.
	 * @param bool   $has_jquery Whether the stand-in can be printed.
	 */
	private function can_drop( array $group, string $html, string $markup, bool $has_jquery ): bool {
		if ( '' !== $group['standin'] && ! $has_jquery ) {
			return false;
		}
		if ( preg_match( $group['markers'], $markup ) ) {
			return false;
		}
		foreach ( $group['needs'] as $handle ) {
			if ( preg_match( '#\bid=(["\'])' . preg_quote( $handle, '#' ) . '-js\1#', $html ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * The page body with external scripts, stylesheets and inline styles taken
	 * out, so file names and CSS selectors do not count as markup.
	 *
	 * @param string $body Body HTML.
	 */
	private static function markup( string $body ): string {
		return (string) preg_replace(
			array( '#<script\b[^>]*\bsrc=[^>]*>\s*</script>#i', '#<style\b[^>]*>.*?</style>#is', '#<link\b[^>]*>#i' ),
			'',
			$body
		);
	}

	/**
	 * Removes a stylesheet and its inline CSS.
	 *
	 * @param string $html   Page.
	 * @param string $handle Style handle.
	 */
	private static function remove_style( string $html, string $handle ): string {
		$id = preg_quote( $handle, '#' );

		return (string) preg_replace(
			array(
				'#<link\b[^>]*\bid=(["\'])' . $id . '-css\1[^>]*>\s*#i',
				'#<style\b[^>]*\bid=(["\'])' . $id . '-inline-css\1[^>]*>.*?</style>\s*#is',
			),
			'',
			$html
		);
	}

	/**
	 * Removes a script with its data and inline parts.
	 *
	 * @param string $html   Page.
	 * @param string $handle Script handle.
	 */
	private static function remove_script( string $html, string $handle ): string {
		return (string) preg_replace(
			'#<script\b[^>]*\bid=(["\'])' . preg_quote( $handle, '#' ) . '-js(?:-extra|-before|-after|-translations)?\1[^>]*>.*?</script>\s*#is',
			'',
			$html
		);
	}

	/** Ordinary page views only: no admin, AJAX, REST, feeds, previews or page builders. */
	private function should_buffer(): bool {
		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';

		$skip = is_admin()
			|| wp_doing_ajax()
			|| ( defined( 'REST_REQUEST' ) && REST_REQUEST )
			|| ( defined( 'DOING_CRON' ) && DOING_CRON )
			|| ! in_array( $method, array( 'GET', 'HEAD' ), true )
			|| is_feed()
			|| is_embed()
			|| is_robots()
			|| is_customize_preview()
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only checks of Elementor's preview flags.
			|| isset( $_GET['elementor-preview'] ) || isset( $_GET['elementor_library'] );

		return ! (bool) apply_filters( 'lenz_plus_performance_skip_trim', $skip );
	}
}
