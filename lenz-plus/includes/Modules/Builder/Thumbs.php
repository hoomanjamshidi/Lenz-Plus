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

	/** The store footer: the full footer with white trust badge tiles above the copyright. */
	private static function draw_footer_store(): string {
		$svg = self::footer_frame()
			. self::brand( 172 )
			. self::links( 130 ) . self::links( 86 )
			. self::r( 12, 64, 64, 40, self::PANEL, 6 ) . self::r( 22, 76, 44, 3, self::ON_INK, 1 ) . self::r( 30, 88, 36, 3, self::ON_INK, 1 )
			. self::r( 150, 111, 78, 3, self::ON_INK, 1 ) . self::r( 166, 118, 62, 3, self::ON_INK, 1 );
		foreach ( array( 12, 32, 52 ) as $x ) {
			$svg .= self::r( $x, 108, 16, 16, self::WHITE, 3 );
		}

		return $svg;
	}

	/** The stepped booking page (and any booking template): hero, numbered steps over a card of project cards. */
	private static function draw_booking(): string {
		$svg = self::page_frame()
			. self::r( 196, 18, 30, 4, self::MUTED, 1 ) . self::r( 126, 26, 100, 8, self::INK, 2 ) . self::r( 150, 38, 76, 3, self::STRONG, 1 )
			. self::r( 30, 50, 180, 92, self::WHITE, 6, self::LINE );
		foreach ( array( 186, 140, 94, 48 ) as $i => $x ) {
			$svg .= '<circle cx="' . $x . '" cy="62" r="5" fill="' . ( 0 === $i ? self::INK : self::WHITE ) . '" stroke="' . self::STRONG . '"/>';
		}
		foreach ( array( 152, 96, 40 ) as $i => $x ) {
			$svg .= self::r( $x, 76, 48, 32, 0 === $i ? self::SOFT : self::WHITE, 4, 0 === $i ? self::INK : self::LINE ) . self::r( $x + 34, 81, 8, 8, self::LINE, 2 ) . self::r( $x + 14, 96, 28, 3, self::INK, 1 );
		}

		return $svg . self::r( 40, 120, 160, 1, self::LINE, 0 ) . self::r( 160, 126, 40, 10, self::INK, 3 ) . self::r( 40, 126, 30, 10, self::WHITE, 3, self::STRONG );
	}

	/** The booking page with the ink panel: steps and summary beside the form. */
	private static function draw_booking_split(): string {
		$svg = self::page_frame() . self::r( 168, 18, 58, 6, self::INK, 2 )
			. self::r( 14, 32, 212, 108, self::WHITE, 6, self::LINE ) . self::r( 152, 32, 74, 108, self::INK, 6 )
			. self::r( 164, 44, 50, 6, self::WHITE, 2 ) . self::r( 170, 54, 44, 3, self::ON_INK, 1 );
		foreach ( array( 70, 82, 94, 106 ) as $i => $y ) {
			$svg .= '<circle cx="208" cy="' . $y . '" r="4" fill="' . ( 1 === $i ? self::WHITE : 'none' ) . '" stroke="' . self::ON_INK . '"/>' . self::r( 172, $y - 2, 28, 3, self::ON_INK, 1 );
		}

		return $svg . self::r( 24, 42, 118, 3, self::LINE, 1 ) . self::r( 90, 42, 52, 3, self::INK, 1 )
			. self::r( 24, 56, 118, 12, self::SOFT, 2, self::LINE ) . self::r( 24, 74, 56, 12, self::SOFT, 2, self::LINE ) . self::r( 86, 74, 56, 12, self::SOFT, 2, self::LINE )
			. self::r( 24, 92, 118, 24, self::SOFT, 2, self::LINE ) . self::r( 102, 124, 40, 9, self::INK, 3 );
	}

	/** One question at a time: a progress line and one large step on the soft band. */
	private static function draw_booking_focus(): string {
		return self::r( 0, 0, 240, 150, self::SOFT, 0 ) . self::r( 0, 0, 240, 12, self::WHITE, 0 )
			. self::r( 90, 22, 60, 6, self::INK, 2 ) . self::r( 50, 38, 140, 3, self::LINE, 1 ) . self::r( 105, 38, 85, 3, self::INK, 1 )
			. self::r( 160, 50, 30, 6, self::LINE, 3 ) . self::r( 90, 60, 100, 9, self::INK, 2 )
			. self::r( 50, 78, 140, 18, self::WHITE, 4, self::LINE ) . self::r( 50, 100, 140, 18, self::WHITE, 4, self::LINE )
			. self::r( 140, 128, 50, 11, self::INK, 3 ) . self::r( 50, 128, 40, 11, self::WHITE, 3, self::STRONG );
	}

	/** The ordinary booking form: hero, one-page form beside contact cards. */
	private static function draw_booking_simple(): string {
		$svg = self::page_frame()
			. self::r( 196, 18, 30, 4, self::MUTED, 1 ) . self::r( 146, 26, 80, 8, self::INK, 2 )
			. self::r( 82, 44, 144, 100, self::WHITE, 6, self::LINE );
		foreach ( array( 54, 72, 90 ) as $y ) {
			$svg .= self::r( 92, $y, 60, 10, self::SOFT, 2, self::LINE ) . self::r( 156, $y, 60, 10, self::SOFT, 2, self::LINE );
		}

		return $svg . self::r( 92, 108, 124, 20, self::SOFT, 2, self::LINE ) . self::r( 176, 132, 40, 8, self::INK, 3 )
			. self::r( 14, 44, 60, 30, self::WHITE, 5, self::LINE ) . self::r( 14, 80, 60, 30, self::WHITE, 5, self::LINE )
			. self::r( 58, 50, 10, 10, self::LINE, 2 ) . self::r( 58, 86, 10, 10, self::LINE, 2 ) . self::r( 24, 64, 40, 3, self::INK, 1 ) . self::r( 24, 100, 40, 3, self::INK, 1 );
	}

	/** The classic contact page (and any contact template): text hero, four cards, the form beside the map. */
	private static function draw_contact(): string {
		$svg = self::page_frame()
			. self::r( 196, 20, 30, 4, self::MUTED, 1 ) . self::r( 126, 28, 100, 8, self::INK, 2 ) . self::r( 150, 40, 76, 4, self::STRONG, 1 ) . self::r( 190, 50, 36, 9, self::INK, 3 );
		foreach ( array( 172, 118, 64, 10 ) as $x ) {
			$svg .= self::r( $x + 4, 66, 50, 22, self::WHITE, 4, self::LINE ) . self::r( $x + 40, 70, 9, 9, self::LINE, 2 ) . self::r( $x + 16, 82, 32, 3, self::INK, 1 );
		}

		return $svg . self::r( 126, 96, 100, 46, self::WHITE, 5, self::LINE ) . self::r( 134, 104, 84, 7, self::SOFT, 2 ) . self::r( 134, 114, 84, 7, self::SOFT, 2 ) . self::r( 186, 128, 32, 8, self::INK, 3 )
			. self::r( 14, 96, 104, 46, self::SOFT, 5, self::LINE ) . '<path d="M14 120h104M50 96v46M84 96v46" stroke="' . self::LINE . '"/>' . self::r( 60, 112, 10, 10, self::INK, 5 );
	}

	/** The split contact page: contact rows on ink beside the form, a wide map. */
	private static function draw_contact_split(): string {
		$svg = self::page_frame() . self::r( 140, 16, 86, 74, self::INK, 6 );
		foreach ( array( 30, 48, 66 ) as $y ) {
			$svg .= self::r( 210, $y, 9, 9, self::PANEL, 2 ) . self::r( 160, $y + 2, 44, 3, self::ON_INK, 1 ) . self::r( 172, $y + 7, 32, 2, self::RULE, 1 );
		}
		$svg .= self::r( 70, 18, 60, 6, self::INK, 2 ) . self::r( 14, 30, 116, 9, self::SOFT, 2, self::LINE ) . self::r( 14, 43, 116, 9, self::SOFT, 2, self::LINE ) . self::r( 14, 56, 116, 22, self::SOFT, 2, self::LINE ) . self::r( 90, 82, 40, 8, self::INK, 3 );

		return $svg . self::r( 14, 98, 212, 30, self::SOFT, 5, self::LINE ) . '<path d="M14 113h212M80 98v30M160 98v30" stroke="' . self::LINE . '"/>' . self::r( 115, 108, 10, 10, self::INK, 5 )
			. self::r( 14, 134, 212, 12, self::INK, 4 );
	}

	/** The minimal contact page: a centred title, three large values, the map and the ink form. */
	private static function draw_contact_minimal(): string {
		$svg = self::page_frame() . self::r( 80, 20, 80, 7, self::INK, 2 ) . self::r( 95, 31, 50, 3, self::STRONG, 1 );
		foreach ( array( 170, 108, 46 ) as $x ) {
			$svg .= '<circle cx="' . ( $x + 12 ) . '" cy="48" r="6" fill="' . self::LINE . '"/>' . self::r( $x - 6, 58, 36, 5, self::INK, 2 );
		}

		return $svg . self::r( 30, 72, 180, 30, self::SOFT, 5, self::LINE ) . '<path d="M30 87h180M90 72v30M150 72v30" stroke="' . self::LINE . '"/>' . self::r( 115, 82, 10, 10, self::INK, 5 )
			. self::r( 30, 108, 180, 38, self::INK, 5 ) . self::r( 40, 116, 160, 7, self::PANEL, 2 ) . self::r( 40, 126, 160, 7, self::PANEL, 2 ) . self::r( 40, 136, 34, 6, self::WHITE, 2 );
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

	/** The video project page: title with fact boxes, the film on the ink band, text beside a checklist, more videos. */
	private static function draw_portfolio_video(): string {
		$svg = self::page_frame()
			. self::r( 196, 20, 30, 6, self::LINE, 3 ) . self::r( 126, 30, 100, 8, self::INK, 2 ) . self::r( 146, 42, 80, 3, self::STRONG, 1 );
		foreach ( array( 70, 14 ) as $x ) {
			$svg .= self::r( $x, 20, 50, 12, self::WHITE, 3, self::DASH ) . self::r( $x, 36, 50, 12, self::WHITE, 3, self::DASH );
		}
		$svg .= self::r( 0, 54, 240, 58, self::INK, 0 ) . self::r( 40, 59, 160, 48, self::PANEL, 4 ) . self::play( 120, 83, 8 );
		foreach ( array( 120, 14 ) as $x ) {
			$svg .= self::r( $x, 118, 106, 28, self::STRONG, 4 ) . self::play( $x + 53, 132, 5 );
		}

		return $svg;
	}

	/** The photo and video project page: the cover, a row of photos, then the videos on the ink band. */
	private static function draw_portfolio_mixed(): string {
		$svg = self::page_frame()
			. self::r( 196, 20, 30, 6, self::LINE, 3 ) . self::r( 126, 30, 100, 8, self::INK, 2 ) . self::r( 146, 42, 80, 3, self::STRONG, 1 )
			. self::r( 14, 52, 212, 34, self::STRONG, 5 );
		foreach ( array( 172, 118, 64, 10 ) as $x ) {
			$svg .= self::r( $x + 4, 90, 50, 22, self::STRONG, 4 );
		}
		$svg .= self::r( 0, 116, 240, 34, self::INK, 0 );
		foreach ( array( 124, 14 ) as $x ) {
			$svg .= self::r( $x, 121, 102, 24, self::PANEL, 4 ) . self::play( $x + 51, 133, 5 );
		}

		return $svg;
	}

	/**
	 * A white play button.
	 *
	 * @param float $cx Centre X.
	 * @param float $cy Centre Y.
	 * @param float $r  Radius.
	 */
	private static function play( $cx, $cy, $r ): string {
		return sprintf(
			'<circle cx="%1$s" cy="%2$s" r="%3$s" fill="%4$s"/><path d="M%5$s %6$sl%7$s %8$s-%7$s %8$sz" fill="%9$s"/>',
			$cx,
			$cy,
			$r,
			self::WHITE,
			$cx - $r * 0.3,
			$cy - $r * 0.45,
			$r * 0.8,
			$r * 0.45,
			self::INK
		);
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

	/** Article with a cover: the title on the full-width image, a centred column. */
	private static function draw_blog_single_cover(): string {
		$svg = self::page_frame()
			. self::r( 0, 12, 240, 62, self::STRONG, 0 ) . self::r( 0, 44, 240, 30, self::INK, 0 )
			. self::r( 100, 48, 40, 4, self::ON_INK, 1 ) . self::r( 60, 56, 120, 7, self::WHITE, 2 ) . self::r( 90, 67, 60, 3, self::ON_INK, 1 )
			. self::r( 60, 82, 120, 12, self::SOFT, 3, self::LINE );
		foreach ( array( 100, 106, 112, 118, 124, 130 ) as $y ) {
			$svg .= self::r( 60, $y, 120, 3, self::STRONG, 1 );
		}

		return $svg . self::r( 60, 136, 120, 10, self::WHITE, 3, self::LINE );
	}

	/** Article with the image beside the title, then the text beside a sidebar. */
	private static function draw_blog_single_split(): string {
		$svg = self::page_frame()
			. self::r( 0, 13, 120, 2, self::INK, 0 )
			. self::r( 186, 22, 30, 5, self::LINE, 2 ) . self::r( 130, 32, 86, 7, self::INK, 2 ) . self::r( 140, 43, 76, 3, self::STRONG, 1 ) . self::r( 180, 52, 36, 10, self::WHITE, 3, self::LINE )
			. self::r( 22, 20, 98, 50, self::STRONG, 6 );
		foreach ( array( 80, 86, 92, 98, 104, 110, 116 ) as $y ) {
			$svg .= self::r( 92, $y, 124, 3, self::STRONG, 1 );
		}

		return $svg . self::r( 92, 124, 124, 20, self::WHITE, 4, self::LINE ) . self::r( 196, 129, 14, 10, self::LINE, 5 )
			. self::r( 24, 80, 58, 30, self::WHITE, 4, self::LINE ) . self::r( 36, 88, 38, 3, self::INK, 1 ) . self::r( 42, 96, 32, 3, self::STRONG, 1 )
			. self::r( 24, 116, 58, 28, self::INK, 4 );
	}

	/** Minimal article: a narrow column with a centred header. */
	private static function draw_blog_single_minimal(): string {
		$svg = self::page_frame()
			. self::r( 0, 13, 120, 2, self::INK, 0 )
			. self::r( 104, 22, 32, 5, self::LINE, 2 ) . self::r( 70, 32, 100, 7, self::INK, 2 ) . self::r( 85, 43, 70, 3, self::STRONG, 1 )
			. self::r( 62, 54, 116, 34, self::STRONG, 5 );
		foreach ( array( 96, 102, 108, 114, 120 ) as $y ) {
			$svg .= self::r( 74, $y, 92, 3, self::STRONG, 1 );
		}

		return $svg . self::r( 74, 128, 92, 16, self::WHITE, 4, self::LINE ) . self::r( 80, 132, 8, 8, self::LINE, 4 ) . self::r( 92, 134, 40, 3, self::INK, 1 );
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
