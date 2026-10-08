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
	private const DASH   = '#d3dce3';

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

	/** The home page: film strip, text and showreel, four service cards, a masonry and the ink booking frame. */
	private static function draw_home(): string {
		$svg = self::page_frame()
			. self::r( 212, 18, 16, 56, self::INK, 2 ) . self::r( 214, 22, 3, 48, self::WHITE, 1 ) . self::r( 223, 22, 3, 48, self::WHITE, 1 )
			. self::r( 160, 24, 44, 4, self::MUTED, 1 ) . self::r( 124, 32, 80, 8, self::INK, 2 ) . self::r( 140, 44, 64, 3, self::STRONG, 1 )
			. self::r( 172, 54, 32, 9, self::INK, 3 ) . self::r( 136, 54, 32, 9, self::WHITE, 3, self::STRONG )
			. self::r( 22, 18, 82, 56, self::STRONG, 5 ) . '<circle cx="63" cy="46" r="8" fill="' . self::INK . '"/>';
		foreach ( array( 172, 118, 64, 10 ) as $x ) {
			$svg .= self::r( $x + 4, 82, 50, 24, self::WHITE, 4, self::DASH ) . self::r( $x + 40, 87, 8, 8, self::PANEL, 2 ) . self::r( $x + 18, 99, 30, 3, self::INK, 1 );
		}
		foreach ( array( 172, 118, 64, 10 ) as $i => $x ) {
			$svg .= self::r( $x + 4, 112, 50, 0 === $i % 2 ? 14 : 10, self::STRONG, 3 );
		}

		return $svg . self::r( 12, 130, 216, 18, self::INK, 4 ) . self::r( 18, 134, 204, 10, self::WHITE, 2 );
	}

	/** The about page: text hero with a portrait, number tiles, story and timeline, ink call to action. */
	private static function draw_about(): string {
		$svg = self::page_frame()
			. self::r( 196, 22, 30, 4, self::MUTED, 1 ) . self::r( 136, 30, 90, 8, self::INK, 2 ) . self::r( 156, 42, 70, 4, self::STRONG, 1 )
			. self::r( 190, 52, 36, 9, self::INK, 3 ) . self::r( 150, 52, 36, 9, self::WHITE, 3, self::STRONG )
			. self::r( 18, 16, 60, 60, self::STRONG, 6 ) . self::r( 18, 62, 6, 14, self::WHITE, 0 );
		foreach ( array( 172, 118, 64, 10 ) as $x ) {
			$svg .= self::r( $x + 4, 84, 50, 18, self::LINE, 4 );
		}

		return $svg . self::r( 130, 110, 96, 3, self::INK, 1 ) . self::r( 140, 116, 86, 3, self::STRONG, 1 )
			. '<path d="M70 110v22" stroke="' . self::LINE . '"/><circle cx="70" cy="113" r="2" fill="' . self::INK . '"/><circle cx="70" cy="125" r="2" fill="' . self::INK . '"/>'
			. self::r( 20, 112, 42, 3, self::INK, 1 ) . self::r( 30, 124, 32, 3, self::INK, 1 )
			. self::r( 12, 136, 216, 12, self::INK, 4 );
	}

	/** The services page: hero with a photo, four jump tiles, the ink service band and price cards. */
	private static function draw_services(): string {
		$svg = self::page_frame()
			. self::r( 196, 22, 30, 4, self::MUTED, 1 ) . self::r( 136, 30, 90, 8, self::INK, 2 ) . self::r( 150, 42, 76, 4, self::STRONG, 1 )
			. self::r( 190, 52, 36, 9, self::INK, 3 )
			. self::r( 18, 18, 96, 46, self::STRONG, 6 );
		foreach ( array( 172, 118, 64, 10 ) as $x ) {
			$svg .= self::r( $x + 4, 72, 50, 12, self::WHITE, 4, self::STRONG );
		}
		$svg .= self::r( 0, 90, 240, 26, self::INK, 0 ) . self::r( 150, 98, 76, 4, self::WHITE, 1 ) . self::r( 18, 95, 70, 16, self::PANEL, 3 );
		foreach ( array( 172, 118, 64, 10 ) as $i => $x ) {
			$svg .= self::r( $x + 4, 122, 50, 24, 0 === $i ? self::INK : self::WHITE, 4, 0 === $i ? '' : self::LINE );
		}

		return $svg;
	}

	/** The portfolio list: title, chips and a masonry of photos with vertical titles. */
	private static function draw_portfolio_archive(): string {
		$svg     = self::page_frame()
			. self::r( 196, 20, 30, 4, self::MUTED, 1 ) . self::r( 126, 28, 100, 8, self::INK, 2 ) . self::r( 146, 40, 80, 3, self::STRONG, 1 )
			. self::r( 206, 50, 20, 8, self::INK, 3 ) . self::r( 180, 50, 22, 8, self::WHITE, 3, self::STRONG ) . self::r( 154, 50, 22, 8, self::WHITE, 3, self::STRONG );
		$columns = array(
			array( 172, array( 40, 28, 22 ) ),
			array( 118, array( 26, 44, 20 ) ),
			array( 64, array( 34, 24, 32 ) ),
			array( 10, array( 22, 38, 30 ) ),
		);
		foreach ( $columns as $column ) {
			$y = 64;
			foreach ( $column[1] as $h ) {
				$svg .= self::r( $column[0] + 4, $y, 50, $h, self::STRONG, 4 ) . self::r( $column[0] + 47, $y + $h - 10, 3, 8, self::WHITE, 0 );
				$y   += $h + 4;
			}
		}

		return $svg;
	}

	/** The project page: breadcrumb, title with fact boxes, a wide cover and a row of photos. */
	private static function draw_portfolio_single(): string {
		$svg = self::page_frame()
			. self::r( 176, 18, 50, 3, self::MUTED, 1 )
			. self::r( 196, 26, 30, 6, self::LINE, 3 ) . self::r( 126, 36, 100, 8, self::INK, 2 ) . self::r( 146, 48, 80, 3, self::STRONG, 1 );
		foreach ( array( 70, 14 ) as $x ) {
			$svg .= self::r( $x, 26, 50, 12, self::WHITE, 3, self::DASH ) . self::r( $x, 42, 50, 12, self::WHITE, 3, self::DASH );
		}
		$svg .= self::r( 14, 62, 212, 50, self::STRONG, 6 );
		foreach ( array( 172, 118, 64, 10 ) as $x ) {
			$svg .= self::r( $x + 4, 118, 50, 30, self::STRONG, 4 );
		}

		return $svg;
	}

	/** Custom templates of the project kind look like the ready-made one. */
	private static function draw_portfolio(): string {
		return self::draw_portfolio_single();
	}

	/** The blog page: title, a wide featured card, cards beside a sidebar and the ink newsletter band. */
	private static function draw_blog_archive(): string {
		$svg = self::page_frame()
			. self::r( 196, 18, 30, 4, self::MUTED, 1 ) . self::r( 126, 26, 100, 8, self::INK, 2 )
			. self::r( 14, 42, 212, 30, self::WHITE, 5, self::LINE ) . self::r( 120, 42, 106, 30, self::STRONG, 5 ) . self::r( 30, 50, 70, 5, self::INK, 2 ) . self::r( 40, 60, 60, 3, self::STRONG, 1 );
		foreach ( array( array( 156, 80 ), array( 84, 80 ), array( 156, 106 ), array( 84, 106 ) ) as $card ) {
			$svg .= self::r( $card[0], $card[1], 66, 16, self::STRONG, 3 ) . self::r( $card[0] + 20, $card[1] + 19, 46, 3, self::INK, 1 );
		}

		return $svg . self::r( 14, 80, 62, 14, self::WHITE, 4, self::LINE ) . self::r( 14, 98, 62, 22, self::WHITE, 4, self::LINE ) . self::r( 14, 124, 62, 10, self::SOFT, 4 )
			. self::r( 0, 136, 240, 14, self::INK, 0 );
	}

	/** The article page: header with a byline, a wide image, the text beside a table of contents. */
	private static function draw_blog_single(): string {
		$svg = self::page_frame()
			. self::r( 0, 13, 120, 2, self::INK, 0 )
			. self::r( 186, 20, 24, 5, self::LINE, 2 ) . self::r( 90, 30, 120, 7, self::INK, 2 ) . self::r( 70, 41, 140, 3, self::STRONG, 1 )
			. self::r( 196, 50, 14, 14, self::STRONG, 7 ) . self::r( 30, 52, 30, 10, self::WHITE, 3, self::LINE )
			. self::r( 30, 70, 180, 34, self::STRONG, 5 );
		foreach ( array( 112, 118, 124, 130, 136 ) as $y ) {
			$svg .= self::r( 92, $y, 118, 3, self::STRONG, 1 );
		}

		return $svg . self::r( 30, 110, 54, 32, self::WHITE, 4, self::LINE ) . self::r( 44, 118, 32, 3, self::INK, 1 ) . self::r( 50, 126, 26, 3, self::STRONG, 1 ) . self::r( 46, 132, 30, 3, self::STRONG, 1 );
	}

	/** Custom templates of the post list kind look like the ready-made one. */
	private static function draw_blog(): string {
		return self::draw_blog_archive();
	}

	/** Custom templates of the article kind look like the ready-made one. */
	private static function draw_post(): string {
		return self::draw_blog_single();
	}

	/** The courses page: title, chips, three course cards (one on ink) and the ink waitlist band. */
	private static function draw_courses(): string {
		$svg = self::page_frame()
			. self::r( 196, 18, 30, 4, self::MUTED, 1 ) . self::r( 126, 26, 100, 8, self::INK, 2 )
			. self::r( 206, 42, 20, 8, self::INK, 3 ) . self::r( 180, 42, 22, 8, self::WHITE, 3, self::STRONG ) . self::r( 154, 42, 22, 8, self::WHITE, 3, self::STRONG );
		foreach ( array( 160, 88 ) as $x ) {
			$svg .= self::r( $x, 56, 66, 64, self::WHITE, 5, self::LINE ) . self::r( $x, 56, 66, 26, self::STRONG, 5 ) . self::r( $x + 20, 88, 40, 4, self::INK, 1 ) . self::r( $x + 6, 108, 22, 7, self::INK, 2 );
		}

		return $svg . self::r( 16, 56, 66, 64, self::INK, 5 ) . self::r( 46, 64, 28, 6, self::PANEL, 2 ) . self::r( 30, 76, 44, 4, self::WHITE, 1 ) . self::r( 22, 100, 54, 7, self::WHITE, 2 ) . self::r( 22, 110, 54, 7, self::WHITE, 2 )
			. self::r( 0, 128, 240, 22, self::INK, 0 );
	}

	/** The course page: hero with facts and a video cover, ticked boxes, curriculum rows and the buy box. */
	private static function draw_course_single(): string {
		$svg = self::page_frame()
			. self::r( 186, 20, 40, 6, self::INK, 3 ) . self::r( 136, 30, 90, 8, self::INK, 2 ) . self::r( 146, 42, 80, 3, self::STRONG, 1 );
		foreach ( array( 196, 166, 136 ) as $x ) {
			$svg .= self::r( $x, 50, 12, 12, self::WHITE, 3, self::DASH ) . self::r( $x - 16, 54, 14, 3, self::INK, 1 );
		}
		$svg .= self::r( 14, 20, 100, 46, self::STRONG, 5 ) . '<circle cx="64" cy="43" r="9" fill="' . self::INK . '"/>';
		foreach ( array( 156, 84, 12 ) as $x ) {
			$svg .= self::r( $x, 74, 70, 14, self::WHITE, 3, self::DASH );
		}

		return $svg . self::r( 12, 94, 216, 10, self::WHITE, 3, self::LINE ) . self::r( 12, 108, 216, 10, self::WHITE, 3, self::LINE )
			. self::r( 12, 124, 216, 22, self::WHITE, 4, self::LINE ) . self::r( 160, 129, 58, 5, self::INK, 2 ) . self::r( 160, 137, 40, 6, self::INK, 2 );
	}

	/** The course page with the buy box sticky beside the content. */
	private static function draw_course_sidebar(): string {
		$svg = self::page_frame()
			. self::r( 186, 20, 40, 6, self::INK, 3 ) . self::r( 136, 30, 90, 8, self::INK, 2 ) . self::r( 14, 20, 100, 40, self::STRONG, 5 );
		foreach ( array( 70, 88, 106, 124 ) as $y ) {
			$svg .= self::r( 90, $y, 136, 14, self::WHITE, 3, self::DASH );
		}

		return $svg . self::r( 14, 70, 68, 70, self::WHITE, 5, self::LINE ) . self::r( 24, 80, 48, 5, self::INK, 2 ) . self::r( 24, 90, 34, 6, self::INK, 2 ) . self::r( 24, 100, 48, 8, self::INK, 3 )
			. self::r( 24, 114, 48, 2, self::LINE, 0 ) . self::r( 24, 120, 48, 2, self::LINE, 0 ) . self::r( 24, 126, 48, 2, self::LINE, 0 );
	}

	/** Custom templates of the course kind look like the ready-made one. */
	private static function draw_course(): string {
		return self::draw_course_single();
	}

	/** White page under a header strip, for the page designs. */
	private static function page_frame(): string {
		return self::r( 0, 0, 240, 150, self::WHITE, 0 )
			. self::r( 0, 0, 240, 12, self::SOFT, 0 ) . self::r( 0, 12, 240, 1, self::LINE, 0 )
			. self::r( 214, 3, 18, 6, self::INK, 2 ) . self::r( 8, 3, 22, 6, self::INK, 2 );
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
