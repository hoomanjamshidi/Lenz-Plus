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

	private const INK    = '#022d4f';
	private const LINE   = '#e3ebf1';
	private const SOFT   = '#f5f8fa';
	private const WHITE  = '#ffffff';
	private const MUTED  = '#8c9aa6';
	private const STRONG = '#b9c6d0';
	private const PANEL  = '#0b3a5e';
	private const ON_INK = '#c4d3df';
	private const RULE   = '#2a5878';

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

	/** The header preset (and any header template): logo, menu, booking and phone over a page. */
	private static function draw_header(): string {
		return self::r( 0, 0, 240, 150, self::SOFT, 0 )
			. self::r( 0, 0, 240, 34, self::WHITE, 0 ) . self::r( 0, 34, 240, 1, self::LINE, 0 )
			. self::r( 208, 10, 14, 14, self::INK, 3 ) . self::r( 170, 11, 32, 6, self::INK, 2 ) . self::r( 180, 19, 22, 3, self::MUTED, 1 )
			. self::r( 136, 15, 18, 4, self::INK, 2 ) . self::r( 110, 15, 18, 4, self::STRONG, 2 ) . self::r( 84, 15, 18, 4, self::STRONG, 2 )
			. self::r( 32, 10, 36, 14, self::INK, 4 ) . self::r( 12, 10, 14, 14, self::WHITE, 4, self::LINE )
			. self::r( 20, 52, 200, 40, self::WHITE, 6, self::LINE ) . self::r( 124, 100, 96, 34, self::WHITE, 6, self::LINE ) . self::r( 20, 100, 96, 34, self::WHITE, 6, self::LINE );
	}

	/** The full footer: brand box, two link columns and the contact card on ink. */
	private static function draw_footer(): string {
		return self::footer_frame()
			. self::brand( 172 )
			. self::r( 182, 96, 46, 3, self::ON_INK, 1 ) . self::r( 188, 104, 40, 3, self::ON_INK, 1 )
			. self::links( 130 ) . self::links( 86 )
			. self::r( 12, 64, 64, 50, self::PANEL, 6 ) . self::r( 22, 76, 44, 3, self::ON_INK, 1 ) . self::r( 30, 88, 36, 3, self::ON_INK, 1 ) . self::r( 26, 100, 40, 3, self::ON_INK, 1 );
	}

	/** The compact footer: brand box, one link column and the contact card on ink. */
	private static function draw_footer_compact(): string {
		return self::footer_frame()
			. self::brand( 172 )
			. self::links( 112 )
			. self::r( 12, 64, 84, 50, self::PANEL, 6 ) . self::r( 24, 76, 60, 3, self::ON_INK, 1 ) . self::r( 34, 88, 50, 3, self::ON_INK, 1 ) . self::r( 30, 100, 54, 3, self::ON_INK, 1 );
	}

	/** Page above an ink band with a dashed rule and the copyright line. */
	private static function footer_frame(): string {
		return self::r( 0, 0, 240, 150, self::SOFT, 0 )
			. self::r( 20, 12, 200, 26, self::WHITE, 6, self::LINE )
			. self::r( 0, 48, 240, 102, self::INK, 0 )
			. '<path d="M12 128h216" stroke="' . self::RULE . '" stroke-dasharray="4 3"/>'
			. self::r( 90, 136, 60, 3, self::MUTED, 1 );
	}

	/**
	 * The framed logo box and social squares of the footer's first column.
	 *
	 * @param float $x Left edge.
	 */
	private static function brand( $x ): string {
		return self::r( $x, 62, 56, 22, self::INK, 4, self::RULE ) . self::r( $x + 40, 67, 10, 12, self::WHITE, 2 ) . self::r( $x + 8, 70, 28, 5, self::WHITE, 2 )
			. self::r( $x + 42, 112, 12, 12, self::INK, 3, self::RULE ) . self::r( $x + 26, 112, 12, 12, self::INK, 3, self::RULE ) . self::r( $x + 10, 112, 12, 12, self::INK, 3, self::RULE );
	}

	/**
	 * A link column: a white title and bulleted lines.
	 *
	 * @param float $x Left edge.
	 */
	private static function links( $x ): string {
		$svg = self::r( $x + 10, 64, 26, 5, self::WHITE, 2 );
		foreach ( array( 78, 90, 102, 114 ) as $y ) {
			$svg .= self::r( $x + 32, $y, 4, 4, self::MUTED, 0 ) . self::r( $x + 4, $y, 24, 4, self::ON_INK, 1 );
		}

		return $svg;
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
			return self::r( 0, 0, 240, 150, self::SOFT, 0 ) . '<path d="M100 55l40 40M140 55l-40 40" stroke="' . self::STRONG . '" stroke-width="6" stroke-linecap="round"/>';
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
