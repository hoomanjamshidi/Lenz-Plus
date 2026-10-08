<?php
/**
 * Building blocks shared by the page presets: the mockups' section bands
 * (boxed to 1200px inside 40px gutters, the same vertical rhythm), section
 * headings, two-column rows that stack on phones, and URL control values.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Presets;

use LenzPlus\Core\Theme_Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * Static helpers that return El::box() / El::w() data.
 */
final class Blocks {

	/**
	 * A page section: content boxed to the mockups' 1280px container (1200px
	 * inside its gutters), on an optional brand surface.
	 *
	 * @param array $children Content.
	 * @param array $o        Container options overriding the defaults (`surface`, `pad`…).
	 */
	public static function band( array $children, array $o = array() ): array {
		return El::box(
			array_merge(
				array(
					'boxed'      => 1200,
					'tag'        => 'section',
					'pad'        => array( 64, 40 ),
					'pad_tablet' => array( 44, 24 ),
					'pad_mobile' => array( 30, 16 ),
				),
				$o
			),
			$children
		);
	}

	/**
	 * Vertical padding of a band, as [desktop, tablet, phone] → El::box() options.
	 *
	 * @param int $desktop Desktop.
	 * @param int $tablet  Tablet.
	 * @param int $mobile  Phone.
	 */
	public static function pad_y( int $desktop, int $tablet, int $mobile ): array {
		return array(
			'pad'        => array( $desktop, 40 ),
			'pad_tablet' => array( $tablet, 24 ),
			'pad_mobile' => array( $mobile, 16 ),
		);
	}

	/**
	 * A full-width section without padding, for widgets that box their own
	 * content (the page hero).
	 *
	 * @param array $children Content.
	 */
	public static function bleed( array $children ): array {
		return El::box(
			array(
				'tag' => 'section',
				'pad' => 0,
			),
			$children
		);
	}

	/**
	 * Section heading widget.
	 *
	 * @param string $title    Title.
	 * @param string $subtitle Muted line under it.
	 * @param array  $more     More settings (`space_below`, `index`, `latin`…).
	 */
	public static function heading( string $title, string $subtitle = '', array $more = array() ): array {
		$settings = array_merge(
			array(
				'title'    => $title,
				'subtitle' => $subtitle,
			),
			$more
		);

		// Without a subtitle the mockups leave 22px under the title.
		if ( '' === $subtitle && ! isset( $settings['space_below'] ) ) {
			$settings['space_below'] = El::px( 22 );
		}

		return El::w( 'lzp-heading', $settings );
	}

	/**
	 * Two columns side by side that stack on phones (the mockups'
	 * `repeat(auto-fit, minmax(300px, 1fr))` rows).
	 *
	 * @param array $start Start column content.
	 * @param array $end   End column content.
	 * @param array $o     Row options (`align`, `gap`).
	 */
	public static function columns( array $start, array $end, array $o = array() ): array {
		$column = static function ( array $children ): array {
			return El::box(
				array(
					'width'        => 50,
					'width_mobile' => 100,
					'fill'         => true,
				),
				$children
			);
		};

		return El::box(
			array(
				'dir'        => 'row',
				'dir_mobile' => 'column',
				'align'      => $o['align'] ?? 'flex-start',
				'gap'        => $o['gap'] ?? 52,
				'gap_tablet' => 32,
				'gap_mobile' => 32,
			),
			array( $column( $start ), $column( $end ) )
		);
	}

	/**
	 * URL control value.
	 *
	 * @param string $url Address.
	 */
	public static function link( string $url ): array {
		return array(
			'url'         => $url,
			'is_external' => '',
			'nofollow'    => '',
		);
	}

	/** Booking link: Lenz's reserve button target, else the home page's booking section. */
	public static function booking_url(): string {
		$link = Theme_Bridge::reserve_link()['url'];

		return '' !== $link ? $link : home_url( '/#booking' );
	}

	/** The portfolio archive, or the home page when Lenz is not active. */
	public static function portfolio_url(): string {
		$url = post_type_exists( 'portfolio' ) ? get_post_type_archive_link( 'portfolio' ) : '';

		return $url ? $url : home_url( '/' );
	}

	/** The WooCommerce shop (where course products are listed), or the home page. */
	public static function courses_url(): string {
		$url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';

		return $url ? $url : home_url( '/' );
	}

	/**
	 * Repeater rows with Elementor's `_id` added.
	 *
	 * @param array $rows Rows (settings arrays).
	 */
	public static function rows( array $rows ): array {
		return array_map(
			static function ( array $row ): array {
				return array( '_id' => El::id() ) + $row;
			},
			$rows
		);
	}
}
