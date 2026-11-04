<?php
/**
 * Lenz's font files: visible text while they load, and the body font asked
 * for first.
 *
 * Lenz links one small stylesheet per active font (`lenz-font-<name>`, e.g.
 * IRANYekanXFANum.min.css) whose `@font-face` rules have no `font-display`,
 * so browsers hide Persian text for up to three seconds while the font
 * downloads. With `font_swap` the stylesheet is printed inline instead of
 * linked (one render-blocking request less) with `font-display: swap` added
 * and its relative `url()`s made absolute. With `font_preload` the regular
 * weight of the site font (`Theme_Bridge::font()`) gets a preload hint, so
 * it starts downloading with the HTML instead of after the CSS. WP Rocket
 * keeps both (its own font preload, when on, only repeats the hint).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Performance;

use LenzPlus\Core\Theme_Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * Font stylesheet rewriting and preload.
 */
final class Fonts {

	/** Prefix of Lenz's font stylesheet handles. */
	private const HANDLE_PREFIX = 'lenz-font-';

	/** @var bool Print the font CSS inline with font-display: swap. */
	private $swap;

	/** @var bool Preload the body font. */
	private $preload;

	/** @var string[] Font files to preload. */
	private $preloads = array();

	/**
	 * @param bool $swap    Inline the CSS with font-display: swap.
	 * @param bool $preload Preload the body font.
	 */
	public function __construct( bool $swap, bool $preload ) {
		$this->swap    = $swap;
		$this->preload = $preload;
	}

	/** Hooks in after Lenz has enqueued its fonts (wp_enqueue_scripts 10). */
	public function register(): void {
		if ( ( $this->swap || $this->preload ) && Theme_Bridge::is_active() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'prepare' ), 100 );
			add_action( 'wp_head', array( $this, 'print_preloads' ), 2 );
		}
	}

	/** Reads each queued Lenz font stylesheet and applies the settings. */
	public function prepare(): void {
		$styles = wp_styles();
		$family = strtolower( trim( Theme_Bridge::font(), '"\' ' ) );

		foreach ( $styles->queue as $handle ) {
			if ( 0 !== strpos( $handle, self::HANDLE_PREFIX ) || empty( $styles->registered[ $handle ]->src ) ) {
				continue;
			}

			$src = (string) $styles->registered[ $handle ]->src;
			$css = self::read( $src );
			if ( '' === $css ) {
				continue;
			}

			$css = self::absolute_urls( $css, $src );

			if ( $this->preload ) {
				$this->preloads = array_merge( $this->preloads, self::regular_files( $css, $family ) );
			}

			if ( $this->swap ) {
				// Without a src WordPress prints only the inline CSS, under the same handle.
				$styles->registered[ $handle ]->src = false;
				wp_add_inline_style( $handle, self::with_swap( $css ) );
			}
		}
	}

	/** Prints the preload hints near the top of `<head>`. */
	public function print_preloads(): void {
		foreach ( array_unique( $this->preloads ) as $url ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( $url ) );
		}
	}

	/**
	 * The stylesheet's contents when it is one of the theme's own files.
	 *
	 * @param string $src Stylesheet URL.
	 */
	private static function read( string $src ): string {
		$base = trailingslashit( get_template_directory_uri() );
		$path = strtok( $src, '?' );
		if ( 0 !== strpos( $path, $base ) ) {
			return '';
		}

		$file = trailingslashit( get_template_directory() ) . substr( $path, strlen( $base ) );
		if ( ! is_readable( $file ) || '.css' !== substr( $file, -4 ) ) {
			return '';
		}

		return (string) file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- a local theme file.
	}

	/**
	 * Makes `url(../x)` paths absolute: the CSS moves from its file into the page.
	 *
	 * @param string $css CSS.
	 * @param string $src Stylesheet URL.
	 */
	private static function absolute_urls( string $css, string $src ): string {
		$dir = dirname( (string) strtok( $src, '?' ) );

		return (string) preg_replace_callback(
			'#url\(\s*(["\']?)(?!data:|https?:|//|/)([^"\')]+)\1\s*\)#i',
			static function ( array $m ) use ( $dir ): string {
				return 'url("' . self::resolve( $dir, $m[2] ) . '")';
			},
			$css
		);
	}

	/**
	 * Joins a relative path to a directory URL, resolving `..` and `.`.
	 *
	 * @param string $dir      Directory URL.
	 * @param string $relative Relative path.
	 */
	private static function resolve( string $dir, string $relative ): string {
		$parts = explode( '/', $dir );
		foreach ( explode( '/', $relative ) as $segment ) {
			if ( '..' === $segment ) {
				array_pop( $parts );
			} elseif ( '.' !== $segment && '' !== $segment ) {
				$parts[] = $segment;
			}
		}

		return implode( '/', $parts );
	}

	/**
	 * Adds `font-display: swap` to every `@font-face` that does not set it.
	 *
	 * @param string $css CSS.
	 */
	private static function with_swap( string $css ): string {
		return (string) preg_replace_callback(
			'#@font-face\s*\{([^}]*)\}#i',
			static function ( array $m ): string {
				return false !== stripos( $m[1], 'font-display' ) ? $m[0] : '@font-face{font-display:swap;' . $m[1] . '}';
			},
			$css
		);
	}

	/**
	 * The woff2 files of the family's regular weight.
	 *
	 * @param string $css    CSS with absolute URLs.
	 * @param string $family Lower-case family name.
	 * @return string[]
	 */
	private static function regular_files( string $css, string $family ): array {
		$files = array();
		preg_match_all( '#@font-face\s*\{([^}]*)\}#i', $css, $faces );

		foreach ( $faces[1] as $face ) {
			$is_family  = preg_match( '#font-family\s*:\s*["\']?([^;"\']+)#i', $face, $name ) && strtolower( trim( $name[1] ) ) === $family;
			$is_regular = ! preg_match( '#font-weight\s*:#i', $face ) || preg_match( '#font-weight\s*:\s*(400|normal)\b#i', $face );
			$is_upright = ! preg_match( '#font-style\s*:\s*(italic|oblique)#i', $face );

			if ( $is_family && $is_regular && $is_upright && preg_match( '#url\("([^"]+\.woff2)"\)#i', $face, $url ) ) {
				$files[] = $url[1];
			}
		}

		return $files;
	}
}
