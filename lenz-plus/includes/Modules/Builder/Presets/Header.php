<?php
/**
 * The mockups' site header.
 *
 * Two layouts in one template: the desktop row (logo and name, the menu,
 * booking and phone buttons; hidden on tablets and phones) and a 64px bar
 * for small screens (logo, phone and the menu button that opens Lenz's
 * mobile menu; hidden on desktop). The designs have no phone header, and
 * squeezing the desktop row onto a phone wraps it onto several lines.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Presets;

defined( 'ABSPATH' ) || exit;

/**
 * Header preset builders.
 */
final class Header {

	/** Latin line under the name, as in the mockups. */
	private const TAGLINE = 'PHOTO & VIDEO';

	/** The translucent sticky bar with both layouts. */
	public static function main(): array {
		return array(
			El::box(
				array(
					'surface' => 'bar',
					'tag'     => 'header',
				),
				array( self::desktop_row(), self::mobile_bar() )
			),
		);
	}

	/** Desktop row: the menu takes the space between the logo and the buttons. */
	private static function desktop_row(): array {
		return El::box(
			array(
				'boxed' => 1200,
				'dir'   => 'row',
				'align' => 'center',
				'gap'   => 32,
				'pad'   => array( 14, 40 ),
				'hide'  => array( 'tablet', 'mobile' ),
			),
			array(
				self::logo( 36 ),
				El::w(
					'lzp-nav-menu',
					array(
						'toggle' => 'never',
						'align'  => 'center',
					),
					array( 'fill' => true )
				),
				El::w( 'lzp-header-action' ),
			)
		);
	}

	/** Phone and tablet bar: logo at the start, phone and menu buttons at the end. */
	private static function mobile_bar(): array {
		return El::box(
			array(
				'dir'         => 'row',
				'align'       => 'center',
				'justify'     => 'space-between',
				'wrap_tablet' => 'nowrap',
				'wrap_mobile' => 'nowrap',
				'gap'         => 12,
				'pad'         => array( 10, 24 ),
				'pad_mobile'  => array( 10, 16 ),
				'min_h'       => 64,
				'hide'        => array( 'desktop' ),
			),
			array(
				self::logo( 30 ),
				El::box(
					array(
						'dir'         => 'row',
						'align'       => 'center',
						'gap'         => 8,
						'width'       => 'auto',
						'wrap_tablet' => 'nowrap',
						'wrap_mobile' => 'nowrap',
					),
					array(
						El::w( 'lzp-header-action', array( 'show_cta' => '' ) ),
						El::w( 'lzp-nav-menu', array( 'toggle' => 'always' ) ),
					)
				),
			)
		);
	}

	/**
	 * Lenz's logo with the two-line name.
	 *
	 * @param int $height Logo height in px.
	 */
	private static function logo( int $height ): array {
		return El::w(
			'lzp-site-logo',
			array(
				'sub'    => self::TAGLINE,
				'height' => El::px( $height ),
			)
		);
	}
}
