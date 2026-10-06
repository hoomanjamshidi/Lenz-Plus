<?php
/**
 * Wireframe thumbnails for the admin cards (RTL, like the designs), drawn in
 * the mockups' palette. The live preview shows the real rendering; these are
 * the quick glance. Presets get their own drawing (`draw_<preset-key>()`);
 * anything else gets a generic picture.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Static SVG thumbnails keyed by preset key, template kind or keyword.
 */
final class Thumbs {

	private const INK   = '#022d4f';
	private const LINE  = '#e3ebf1';
	private const SOFT  = '#f5f8fa';
	private const WHITE = '#ffffff';

	/**
	 * SVG markup for a preset key, a template kind or `theme` / `none` / `same`.
	 *
	 * @param string $key Preset key / kind / keyword.
	 */
	public static function svg( string $key ): string {
		$method = 'draw_' . str_replace( '-', '_', $key );
		$body   = method_exists( self::class, $method ) ? self::$method() : self::generic( $key );

		return '<svg class="lzp-thumb" viewBox="0 0 240 150" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">' . $body . '</svg>';
	}

	/**
	 * A rectangle.
	 *
	 * @param float  $x      X.
	 * @param float  $y      Y.
	 * @param float  $w      Width.
	 * @param float  $h      Height.
	 * @param string $fill   Fill.
	 * @param float  $rx     Radius.
	 * @param string $stroke Stroke colour ('' for none).
	 */
	private static function r( $x, $y, $w, $h, string $fill, $rx = 2, string $stroke = '' ): string {
		return sprintf(
			'<rect x="%s" y="%s" width="%s" height="%s" rx="%s" fill="%s"%s/>',
			$x,
			$y,
			$w,
			$h,
			$rx,
			$fill,
			'' !== $stroke ? ' stroke="' . $stroke . '" stroke-width="1"' : ''
		);
	}

	/**
	 * The theme option, "nothing", "same as desktop", and custom templates.
	 *
	 * @param string $key Kind or keyword.
	 */
	private static function generic( string $key ): string {
		if ( 'theme' === $key ) {
			// A camera: "keep Lenz's own design".
			return self::r( 0, 0, 240, 150, self::SOFT, 0 )
				. self::r( 80, 50, 80, 56, self::WHITE, 10, self::LINE ) . self::r( 104, 42, 32, 12, self::INK, 4 )
				. '<circle cx="120" cy="78" r="16" fill="none" stroke="' . self::INK . '" stroke-width="5"/>';
		}

		if ( 'none' === $key ) {
			return self::r( 0, 0, 240, 150, self::SOFT, 0 ) . '<path d="M100 55l40 40M140 55l-40 40" stroke="#b9c6d0" stroke-width="6" stroke-linecap="round"/>';
		}

		if ( 'same' === $key ) {
			return self::r( 0, 0, 240, 150, self::SOFT, 0 )
				. self::r( 38, 34, 104, 70, self::WHITE, 7, self::LINE ) . self::r( 38, 34, 104, 14, self::INK, 7 )
				. self::r( 152, 44, 50, 80, self::WHITE, 8, self::LINE ) . self::r( 152, 44, 50, 14, self::INK, 8 );
		}

		// Custom template: blank page with the Elementor "e".
		return self::r( 0, 0, 240, 150, self::SOFT, 0 )
			. self::r( 80, 35, 80, 80, self::WHITE, 40, self::LINE )
			. '<path d="M107 60h6v30h-6zM118 60h16v6h-16zM118 72h16v6h-16zM118 84h16v6h-16z" fill="#92003b"/>';
	}
}
