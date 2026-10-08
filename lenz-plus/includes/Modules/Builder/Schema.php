<?php
/**
 * Settings schema and defaults for the page templates module (Builder).
 *
 * Template kinds grow with the roadmap: page designs and headers/footers
 * first, then portfolio, blog and course templates. The brand palette and the
 * global look options come from the design mockups in Design/ (their
 * `inkColor`, `photoTone` and `showGuides` props).
 *
 * Header and footer slots store a template post ID as a string, or one of the
 * keywords `theme` (Lenz's own header/footer), `none` (print nothing) and
 * `same` (the phone slot mirrors the desktop slot). Routes (portfolio list,
 * project page…) store a template ID or `theme`.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

use LenzPlus\Core\Icon_Library;
use LenzPlus\Core\Theme_Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * Template kinds, brand tokens, settings shape and defaults.
 */
final class Schema {

	/** Template kinds, in the order the admin shows them. */
	public const TYPES = array( 'header', 'footer', 'home', 'about', 'services', 'courses', 'portfolio_archive', 'portfolio' );

	/**
	 * Whole-page designs: "Create page" copies them into a regular page, and
	 * they are edited and previewed with the site's header and footer.
	 */
	public const PAGE_TYPES = array( 'home', 'about', 'services', 'courses' );

	/**
	 * Kinds that replace the theme's layout of a group of pages (Page_Routes):
	 * the portfolio list (archive and categories) and a project page.
	 */
	public const ROUTE_TYPES = array( 'portfolio_archive', 'portfolio' );

	/** Kinds that replace a part of every page, one slot per device. */
	public const AREAS = array( 'header', 'footer' );

	/** Sticky header modes: off, always on screen, or back on screen when scrolling up. */
	public const STICKY_MODES = array( 'none', 'always', 'scroll_up' );

	/** How photos look in the designs: black and white (the mockups' default) or in colour. */
	public const PHOTO_TONES = array( 'grayscale', 'color' );

	/**
	 * Brand tokens → default value, taken from the mockups (Design/*.dc.html).
	 * Printed as `--lzp-<token>` (underscores become dashes) by Assets.
	 */
	public const BRAND_DEFAULTS = array(
		'ink'           => '#022d4f',
		'accent'        => '#185e82',
		'text'          => '#55636f',
		'sub'           => '#3e5566',
		'muted'         => '#8c9aa6',
		'bg'            => '#ffffff',
		'soft'          => '#f5f8fa',
		'chip'          => '#e8eef3',
		'line'          => '#e3ebf1',
		'line_strong'   => '#b9c6d0',
		'dashed'        => '#d3dce3',
		'field_line'    => '#dce3e9',
		'dark_surface'  => '#0b3a5e',
		'on_dark'       => '#c4d3df',
		'on_dark_muted' => '#8fa9be',
		'on_dark_line'  => '#2a5878',
		'on_dark_hover' => '#134669',
		'success'       => '#1e8e5a',
		'danger'        => '#c0392b',
	);

	/**
	 * Every setting's default. Empty brand colours mean "the design's colour".
	 * Icons default to Lenz's own glyphs (with Lucide for keys the theme font
	 * lacks), the same mix the mockups use; without Lenz they are all SVG.
	 * The designs' header stays on screen (sticky with a blurred background).
	 */
	public static function defaults(): array {
		return array(
			'enabled'    => false,
			'header'     => array(
				'desktop'        => 'theme',
				'mobile'         => 'same',
				'sticky_desktop' => 'always',
				'sticky_mobile'  => 'always',
			),
			'footer'     => array(
				'desktop' => 'theme',
				'mobile'  => 'same',
			),
			// `theme` keeps Lenz's own layout; a template ID replaces it.
			'routes'     => array_fill_keys( self::ROUTE_TYPES, 'theme' ),
			// 1024 is Elementor's tablet breakpoint, where the designs' menu row stops fitting.
			'breakpoint' => 1024,
			'options'    => array(
				'icon_pack'      => Theme_Bridge::is_active() ? Icon_Library::LENZ : 'lucide',
				'persian_digits' => true,
				'photo_tone'     => self::PHOTO_TONES[0],
				'guides'         => true,
			),
			'brand'      => array_merge(
				array_fill_keys( array_keys( self::BRAND_DEFAULTS ), '' ),
				array( 'radius' => 16 )
			),
		);
	}

	/**
	 * Sanitizer schema with the same shape as defaults().
	 */
	public static function fields(): array {
		$brand = array_fill_keys( array_keys( self::BRAND_DEFAULTS ), array( 'type' => 'color' ) );

		$brand['radius'] = array(
			'type' => 'int',
			'min'  => 0,
			'max'  => 40,
		);

		$ref    = array( 'type' => 'key' );
		$sticky = array(
			'type'    => 'enum',
			'options' => self::STICKY_MODES,
		);

		return array(
			'enabled'    => array( 'type' => 'bool' ),
			'header'     => array(
				'type'   => 'group',
				'fields' => array(
					'desktop'        => $ref,
					'mobile'         => $ref,
					'sticky_desktop' => $sticky,
					'sticky_mobile'  => $sticky,
				),
			),
			'footer'     => array(
				'type'   => 'group',
				'fields' => array(
					'desktop' => $ref,
					'mobile'  => $ref,
				),
			),
			'routes'     => array(
				'type'   => 'group',
				'fields' => array_fill_keys( self::ROUTE_TYPES, $ref ),
			),
			'breakpoint' => array(
				'type' => 'int',
				'min'  => 600,
				'max'  => 1440,
			),
			'options'    => array(
				'type'   => 'group',
				'fields' => array(
					// Font Awesome is left out: the designs' icons have no Font Awesome look.
					'icon_pack'      => array(
						'type'    => 'enum',
						'options' => array_values( array_diff( Icon_Library::pack_ids(), array( Icon_Library::FONT_AWESOME ) ) ),
					),
					'persian_digits' => array( 'type' => 'bool' ),
					'photo_tone'     => array(
						'type'    => 'enum',
						'options' => self::PHOTO_TONES,
					),
					'guides'         => array( 'type' => 'bool' ),
				),
			),
			'brand'      => array(
				'type'   => 'group',
				'fields' => $brand,
			),
		);
	}
}
