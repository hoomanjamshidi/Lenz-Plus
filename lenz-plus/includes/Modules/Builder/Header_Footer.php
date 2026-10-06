<?php
/**
 * Replaces Lenz's header and footer with Elementor templates, one per device.
 *
 * Follows Elementor Pro's theme-support approach: on `get_header` the
 * theme's header.php is loaded into an output buffer (require_once, so
 * get_header() does not print it again), and only its
 * `<header id="header-container">` block is swapped. Everything else the file
 * prints stays: the doctype, `wp_head()`, the body classes, `wp_body_open()`
 * and the opening `<div id="container">` that footer.php closes. The footer
 * is buffered from `get_footer` to the start of `wp_footer`, so only
 * `<footer id="site-footer">` is swapped and `wp_footer()` (scripts, Lenz's
 * mobile menu, the bottom navigation) runs untouched.
 *
 * Both device variants are printed and switched with a CSS media query,
 * which keeps full-page caching intact (no server-side device sniffing).
 * Other themes get a best-effort fallback: ours on `wp_body_open` and
 * `wp_footer`, theirs hidden with CSS. Pages where Elementor Pro's Theme
 * Builder has its own header or footer are left alone.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

use LenzPlus\Core\Theme_Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * Header/footer swap for the current request.
 */
final class Header_Footer {

	/** Start of Lenz's header block in header.php. */
	private const THEME_HEADER = '<header id="header-container"';

	/** Start of Lenz's footer block in footer.php. */
	private const THEME_FOOTER = '<footer id="site-footer"';

	/** Lenz's page wrapper, opened in header.php and closed in footer.php. */
	private const CONTAINER = '<div id="container">';

	/** @var Module */
	private $module;

	/** @var array{desktop:int|string, mobile:int|string}|null */
	private $header = null;

	/** @var array{desktop:int|string, mobile:int|string}|null */
	private $footer = null;

	/** @var int|null Output buffer level the footer capture started at. */
	private $footer_level = null;

	/** @var bool */
	private $header_printed = false;

	/**
	 * @param Module $module Owning module.
	 */
	public function __construct( Module $module ) {
		$this->module = $module;
	}

	/** Decides on `wp`, once the main query is known. */
	public function register(): void {
		add_action( 'wp', array( $this, 'setup' ) );
	}

	/** Resolves the slots and hooks the swap for this request. */
	public function setup(): void {
		if ( is_admin() || is_feed() || is_embed() || wp_doing_ajax() || ! Renderer::elementor_ready() || $this->is_part_template() ) {
			return;
		}

		$this->header = $this->area_slots( 'header' );
		$this->footer = $this->area_slots( 'footer' );

		if ( ! $this->header && ! $this->footer ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 20 );
		add_filter( 'body_class', array( $this, 'body_class' ) );

		if ( $this->module->resolver()->is_preview() ) {
			nocache_headers();
		}

		if ( Theme_Bridge::is_active() ) {
			if ( $this->header ) {
				add_action( 'get_header', array( $this, 'replace_header' ), PHP_INT_MAX, 2 );
			}
			if ( $this->footer ) {
				add_action( 'get_footer', array( $this, 'capture_footer' ), PHP_INT_MAX );
				add_action( 'wp_footer', array( $this, 'finish_footer' ), PHP_INT_MIN );
			}
			return;
		}

		if ( $this->header ) {
			add_action( 'wp_body_open', array( $this, 'print_header' ), 5 );
		}
		if ( $this->footer ) {
			add_action( 'wp_footer', array( $this, 'print_footer' ), 5 );
		}
	}

	/** Template CSS, the builder's assets and the device switch. */
	public function enqueue(): void {
		foreach ( array( $this->header, $this->footer ) as $slots ) {
			foreach ( (array) $slots as $slot ) {
				if ( is_int( $slot ) ) {
					Renderer::enqueue( $slot );
				}
			}
		}

		wp_enqueue_style( Assets::HANDLE );
		wp_enqueue_script( Assets::HANDLE );
		wp_add_inline_style( Assets::HANDLE, $this->inline_css() );
	}

	/**
	 * Marks the swapped areas. While our header covers every width, Lenz's
	 * `sticky-header` class goes: its script would otherwise add
	 * `sticky-header-active` on scroll, which pads the page for a fixed
	 * header that is no longer there.
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public function body_class( array $classes ): array {
		if ( $this->header ) {
			$classes[] = 'lzp-hf-header-on';
			if ( ! in_array( 'theme', $this->header, true ) ) {
				$classes = array_values( array_diff( $classes, array( 'sticky-header' ) ) );
			}
		}
		if ( $this->footer ) {
			$classes[] = 'lzp-hf-footer-on';
		}

		return $classes;
	}

	/* ---------------------------------------------------------------------
	 * Lenz: header
	 * ------------------------------------------------------------------- */

	/**
	 * Prints the theme's header file with our header in place of its own.
	 *
	 * @param string|null $name Header name passed to get_header().
	 * @param array       $args Arguments passed to get_header().
	 */
	public function replace_header( $name, $args = array() ): void {
		$templates = array();
		if ( is_string( $name ) && '' !== $name ) {
			$templates[] = "header-{$name}.php";
		}
		$templates[] = 'header.php';

		ob_start();
		// require_once: get_header() finds the file loaded and does not print it a second time.
		locate_template( $templates, true, true, (array) $args );
		$html = (string) ob_get_clean();

		echo $this->swap_header( $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme and Elementor markup.
		$this->header_printed = true;
	}

	/**
	 * @param string $html Everything header.php printed.
	 */
	private function swap_header( string $html ): string {
		$start = strpos( $html, self::THEME_HEADER );
		$end   = false !== $start ? strrpos( $html, '</header>' ) : false;

		if ( false !== $start && false !== $end && $end > $start ) {
			$end += strlen( '</header>' );

			return substr( $html, 0, $start ) . $this->markup( 'header', $this->header, substr( $html, $start, $end - $start ) ) . substr( $html, $end );
		}

		// Lenz's header is switched off (Header → show header): ours opens the page wrapper.
		$open = strpos( $html, self::CONTAINER );
		if ( false !== $open ) {
			$open += strlen( self::CONTAINER );

			return substr( $html, 0, $open ) . $this->markup( 'header', $this->header, '' ) . substr( $html, $open );
		}

		return $html . $this->markup( 'header', $this->header, '' );
	}

	/* ---------------------------------------------------------------------
	 * Lenz: footer
	 * ------------------------------------------------------------------- */

	/** Starts buffering footer.php. */
	public function capture_footer(): void {
		if ( null !== $this->footer_level ) {
			return;
		}

		ob_start();
		$this->footer_level = ob_get_level();
	}

	/** Runs first on `wp_footer`: prints the buffered footer with ours in place of Lenz's. */
	public function finish_footer(): void {
		if ( null === $this->footer_level ) {
			return;
		}

		if ( ob_get_level() !== $this->footer_level ) {
			// Someone else's buffer is still open: leave everything as it is.
			$this->footer_level = null;
			return;
		}

		$html               = (string) ob_get_clean();
		$this->footer_level = null;

		echo $this->swap_footer( $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme and Elementor markup.
	}

	/**
	 * @param string $html Everything footer.php printed before `wp_footer()`.
	 */
	private function swap_footer( string $html ): string {
		$start = strpos( $html, self::THEME_FOOTER );
		$end   = false !== $start ? strpos( $html, '</footer>', $start ) : false;

		if ( false !== $start && false !== $end ) {
			$end += strlen( '</footer>' );

			return substr( $html, 0, $start ) . $this->markup( 'footer', $this->footer, substr( $html, $start, $end - $start ) ) . substr( $html, $end );
		}

		// Lenz's footer is switched off: ours goes where it would have been.
		return $html . $this->markup( 'footer', $this->footer, '' );
	}

	/* ---------------------------------------------------------------------
	 * Other themes
	 * ------------------------------------------------------------------- */

	/** Prints our header at the top of the body. */
	public function print_header(): void {
		if ( $this->header_printed ) {
			return;
		}

		echo $this->markup( 'header', $this->header, '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor markup.
		$this->header_printed = true;
	}

	/** Prints our footer before the theme's scripts. */
	public function print_footer(): void {
		echo $this->markup( 'footer', $this->footer, '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor markup.
	}

	/* ---------------------------------------------------------------------
	 * Markup
	 * ------------------------------------------------------------------- */

	/**
	 * Header/footer wrapper with one slot per device. Identical slots are
	 * printed once.
	 *
	 * @param string $area       `header` or `footer`.
	 * @param array  $slots      Resolved slots.
	 * @param string $theme_html Captured theme markup for `theme` slots.
	 */
	private function markup( string $area, array $slots, string $theme_html ): string {
		$render = static function ( $slot ) use ( $theme_html ): string {
			if ( 'theme' === $slot ) {
				return $theme_html;
			}

			return is_int( $slot ) ? Renderer::render( $slot ) : '';
		};

		if ( $slots['desktop'] === $slots['mobile'] ) {
			$inner = '<div class="lzp-hf__slot">' . $render( $slots['desktop'] ) . '</div>';
		} else {
			$inner = '<div class="lzp-hf__slot lzp-hf__slot--desktop">' . $render( $slots['desktop'] ) . '</div>'
				. '<div class="lzp-hf__slot lzp-hf__slot--mobile">' . $render( $slots['mobile'] ) . '</div>';
		}

		$attrs = '';
		if ( 'header' === $area ) {
			// Lenz's own header keeps its own sticky behaviour.
			$settings = $this->module->settings()['header'];
			$attrs    = sprintf(
				' data-lzp-sticky-desktop="%s" data-lzp-sticky-mobile="%s"',
				esc_attr( is_int( $slots['desktop'] ) ? $settings['sticky_desktop'] : 'none' ),
				esc_attr( is_int( $slots['mobile'] ) ? $settings['sticky_mobile'] : 'none' )
			);
		}

		return sprintf( '<div class="lzp-hf lzp-hf--%1$s"%2$s>%3$s</div>', esc_attr( $area ), $attrs, $inner );
	}

	/** Device switch, Lenz layout fixes and the other-theme fallback. */
	private function inline_css(): string {
		$bp  = (int) $this->module->settings()['breakpoint'];
		$css = sprintf(
			'@media (max-width:%1$dpx){.lzp-hf__slot--desktop{display:none!important}}@media (min-width:%2$dpx){.lzp-hf__slot--mobile{display:none!important}}',
			$bp,
			$bp + 1
		);

		if ( Theme_Bridge::is_active() ) {
			return $css . $this->mixed_header_css( $bp );
		}

		/**
		 * Selectors of the active theme's own header/footer, hidden when a
		 * Lenz+ template replaces them on themes other than Lenz.
		 *
		 * @param array{header: string[], footer: string[]} $selectors Selectors.
		 */
		$selectors = apply_filters(
			'lenz_plus_builder_theme_selectors',
			array(
				'header' => array( '#masthead', 'body > header.site-header', '.site > header.site-header', '#site-header' ),
				'footer' => array( '#colophon', 'body > footer.site-footer', '.site > footer.site-footer', '#site-footer' ),
			)
		);

		foreach ( Schema::AREAS as $area ) {
			if ( $this->{$area} && ! in_array( 'theme', $this->{$area}, true ) && ! empty( $selectors[ $area ] ) ) {
				$css .= implode( ',', array_map( 'strval', $selectors[ $area ] ) ) . '{display:none!important}';
			}
		}

		return $css;
	}

	/**
	 * When one device keeps Lenz's header, the body keeps `sticky-header`, so
	 * Lenz pads the page for its fixed header on scroll. On the widths that
	 * show ours instead, that padding is removed.
	 *
	 * @param int $bp Breakpoint.
	 */
	private function mixed_header_css( int $bp ): string {
		if ( ! $this->header || ! in_array( 'theme', $this->header, true ) || $this->header['desktop'] === $this->header['mobile'] ) {
			return '';
		}

		$query = 'theme' === $this->header['desktop'] ? sprintf( '(max-width:%dpx)', $bp ) : sprintf( '(min-width:%dpx)', $bp + 1 );

		return '@media ' . $query . '{body.sticky-header-active #page-body{padding-top:0}}';
	}

	/**
	 * Resolved slots of an area, or null when nothing of ours shows there.
	 *
	 * @param string $area `header` or `footer`.
	 */
	private function area_slots( string $area ): ?array {
		$slots = $this->module->resolver()->slots( $area );

		if ( 'theme' === $slots['desktop'] && 'theme' === $slots['mobile'] ) {
			return null;
		}

		// Elementor Pro's Theme Builder owns this area on this page.
		if ( function_exists( 'elementor_location_exits' ) && elementor_location_exits( $area, true ) ) {
			return null;
		}

		return $slots;
	}

	/**
	 * Whether a header or footer template is being viewed on its own (it is
	 * shown on Elementor's blank canvas). Page designs are whole pages, so
	 * they get the site header and footer.
	 */
	private function is_part_template(): bool {
		return is_singular( Template_Post_Type::POST_TYPE ) && in_array( Template_Post_Type::type_of( (int) get_queried_object_id() ), Schema::AREAS, true );
	}
}
