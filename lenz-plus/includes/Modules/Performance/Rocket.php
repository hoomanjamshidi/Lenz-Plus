<?php
/**
 * WP Rocket and Lenz / Lenz+: what WP Rocket needs to know to optimise them
 * without breaking them, and the state of its settings for the admin page.
 *
 * - Remove Unused CSS keeps only selectors found in the cached HTML, but Lenz
 *   and Lenz+ add classes from JS (`body.mobile|tablet|desktop` on load,
 *   `mobile-menu-opened`, Slick/lightGallery/Select2/date picker markup,
 *   `lzp-*` and `is-*` states of the bottom bar, sheets, sticky header and
 *   buy bar). They are added to its safelist (`rocket_rucss_safelist`).
 * - LazyLoad: photos printed with `fetchpriority="high"` (the Builder's
 *   heroes, Picture `eager`) are the page's LCP and must not wait for
 *   scrolling (`rocket_lazyload_excluded_attributes`).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Performance;

defined( 'ABSPATH' ) || exit;

/**
 * WP Rocket bridge.
 */
final class Rocket {

	/** Selectors added by JS (WP Rocket's `(.*)` wildcard). */
	private const SAFELIST = array(
		'.desktop',
		'.tablet',
		'.mobile',
		'.mobile-menu-opened(.*)',
		'.sticky-header-active(.*)',
		'#mobile-menu(.*)',
		'#overlay',
		'.closed',
		'.opened',
		'.active',
		'.loading',
		'.slick(.*)',
		'.lg-(.*)',
		'.select2(.*)',
		'.datepicker(.*)',
		'.pwt-(.*)',
		'.lenz_isotope-active(.*)',
		'.lzp-(.*)',
		'.is-(.*)',
	);

	/** Whether WP Rocket is active. */
	public static function is_active(): bool {
		return defined( 'WP_ROCKET_VERSION' );
	}

	/** Hooks the safelist and LazyLoad exclusions. */
	public static function register(): void {
		add_filter( 'rocket_rucss_safelist', array( self::class, 'safelist' ) );
		add_filter( 'rocket_lazyload_excluded_attributes', array( self::class, 'lazyload_exclusions' ) );
	}

	/**
	 * Adds the JS-made selectors to Remove Unused CSS's safelist.
	 *
	 * @param mixed $safelist Selectors.
	 * @return array
	 */
	public static function safelist( $safelist ) {
		return array_values( array_unique( array_merge( (array) $safelist, self::SAFELIST ) ) );
	}

	/**
	 * Keeps high-priority (LCP) photos out of LazyLoad.
	 *
	 * @param mixed $attributes Attribute strings.
	 * @return array
	 */
	public static function lazyload_exclusions( $attributes ) {
		$attributes   = (array) $attributes;
		$attributes[] = 'fetchpriority="high"';

		return $attributes;
	}

	/**
	 * WP Rocket's speed settings that matter next to this module, with their state.
	 *
	 * @return array<string, array{label:string, on:bool, note:string}>
	 */
	public static function status(): array {
		if ( ! self::is_active() || ! function_exists( 'get_rocket_option' ) ) {
			return array();
		}

		$options = array(
			'cache_mobile'      => array( __( 'Cache for mobile devices', 'lenz-plus' ), __( 'Keep it on: the bottom bar and the phone header are in the same page and switch with CSS.', 'lenz-plus' ) ),
			'minify_css'        => array( __( 'Minify CSS files', 'lenz-plus' ), '' ),
			'remove_unused_css' => array( __( 'Remove unused CSS', 'lenz-plus' ), __( 'Safe with Lenz: the classes Lenz and Lenz+ add from JavaScript are kept.', 'lenz-plus' ) ),
			'minify_js'         => array( __( 'Minify JavaScript files', 'lenz-plus' ), '' ),
			'defer_all_js'      => array( __( 'Load JavaScript deferred', 'lenz-plus' ), '' ),
			'delay_js'          => array( __( 'Delay JavaScript execution', 'lenz-plus' ), __( 'Safe with Lenz: menus, sliders and the bottom bar start on the first touch or scroll, and links work before that.', 'lenz-plus' ) ),
			'lazyload'          => array( __( 'LazyLoad for images', 'lenz-plus' ), __( 'The first large photo of each page is excluded, so it is not delayed.', 'lenz-plus' ) ),
			'lazyload_iframes'  => array( __( 'LazyLoad for iframes and videos', 'lenz-plus' ), '' ),
			'emoji'             => array( __( 'Disable emoji', 'lenz-plus' ), '' ),
		);

		$out = array();
		foreach ( $options as $key => $option ) {
			$out[ $key ] = array(
				'label' => $option[0],
				'on'    => (bool) get_rocket_option( $key ),
				'note'  => $option[1],
			);
		}

		return $out;
	}
}
