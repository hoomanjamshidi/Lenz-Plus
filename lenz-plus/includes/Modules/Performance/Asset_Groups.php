<?php
/**
 * The files Lenz (and WooCommerce) print on every page, grouped by the markup
 * that needs them, for Asset_Trimmer.
 *
 * Lenz enqueues Slick, Isotope, lightGallery, Select2, the Persian date
 * picker, Dragscroll, its booking page files and eight widget scripts on
 * every front-end page (about 700 KB before compression), whether the page has a slider, a gallery or a
 * booking form or not. Each group lists its style and script handles, the
 * markup that means the page needs them (a regex run on the page's body
 * without its script and style tags), the handles whose presence also keeps
 * them (scripts that use the library on pages without that markup) and the
 * stand-in Asset_Trimmer prints when the library is left out: Lenz's own
 * scripts call `$('#sort').select2()`, `.slick()` and `lgZoom` without
 * checking, and one TypeError would stop the rest of script.js (mobile menu,
 * wishlist).
 *
 * Verified against Lenz 1.6.1.0 (inc/Scripts.php, inc/PublicScripts.php and
 * assets/js). Filter `lenz_plus_performance_asset_groups` adds or changes groups.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Performance;

use LenzPlus\Core\Theme_Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * Asset groups for the current settings.
 */
final class Asset_Groups {

	/** jQuery plugins Lenz calls without checking: a no-op keeps its scripts running. */
	private const JQUERY_STANDIN = '$.fn.%1$s=$.fn.%1$s||n;';

	/**
	 * The groups that may be left out of a page with these settings.
	 *
	 * @param array $settings Module settings.
	 * @return array<string, array{styles:string[], scripts:string[], markers:string, needs:string[], standin:string}>
	 */
	public static function for_settings( array $settings ): array {
		$groups = array();

		if ( Theme_Bridge::is_active() ) {
			if ( $settings['lenz_assets'] ) {
				$groups = self::lenz();
			}
			if ( 'auto' === $settings['font_awesome'] ) {
				// Lenz's CSS never uses the Font Awesome font itself, only fa-* classes in markup do.
				$groups['font_awesome'] = self::group( '/class=["\'][^"\']*(?<![\w-])fa(?:[srbl]|-[a-z0-9-]+)?(?=[\s"\'])/i', array( 'lenz-font-awesome' ) );
			}
		}

		if ( $settings['wc_blocks'] && class_exists( 'WooCommerce' ) ) {
			// WooCommerce prints its block styles on every page, even where no WooCommerce block is used.
			$groups['wc_blocks'] = self::group( '/wp-block-woocommerce|wc-block-/', array( 'wc-blocks-style', 'wc-blocks-vendors-style' ) );
		}

		return (array) apply_filters( 'lenz_plus_performance_asset_groups', $groups, $settings );
	}

	/**
	 * Lenz's libraries and widget scripts.
	 *
	 * @return array<string, array>
	 */
	private static function lenz(): array {
		return array(
			'slick'          => self::group(
				'/lenz-slider|lenz_experts_wrap|lenz_v_portfolios|lenz_portfolios_items|product-main-slider|product-thumb-slider/',
				array( 'lenz-slick' ),
				array( 'lenz-slick' ),
				array(),
				sprintf( self::JQUERY_STANDIN, 'slick' )
			),
			'isotope'        => self::group(
				'/lenz_portfolios_items|lenz-isotope-posts/',
				array(),
				array( 'lenz-isotope' ),
				array(),
				sprintf( self::JQUERY_STANDIN, 'isotope' )
			),
			'lightgallery'   => self::group(
				'/lenz_image_lightbox-wrapper|lenz_portfolios_wrap|id=["\']portfolios["\']/',
				array( 'lenz-lightgallery', 'lenz-lightgallery-zoom', 'lenz-lightgallery-thumbnail', 'lenz-lightgallery-fullscreen', 'lenz-lightgallery-rotate' ),
				array( 'lenz-lightgallery', 'lenz-lightgallery-zoom', 'lenz-lightgallery-fullscreen', 'lenz-lightgallery-rotate', 'lenz-lightgallery-thumbnail', 'lenz-lightgallery-video' ),
				array(),
				// single-portfolio.js lists the plugins by their globals even when the gallery is missing.
				'w.lightGallery=w.lightGallery||function(){};["lgZoom","lgThumbnail","lgFullscreen","lgRotate","lgVideo"].forEach(function(k){w[k]=w[k]||{}});'
			),
			'select2'        => self::group(
				'/id=["\']sort["\']/',
				array( 'lenz-select2' ),
				array( 'lenz-select2' ),
				array(),
				sprintf( self::JQUERY_STANDIN, 'select2' )
			),
			// Lenz loads its booking page files everywhere until a booking page is chosen; listed before the date picker, which they keep.
			'booking'        => self::group( '/lenz-booking|ir-map-province|lenz_reservation_wrap/', array( 'lenz-booking' ), array( 'lenz-booking' ) ),
			'datepicker'     => self::group(
				'/lenz_reservation_wrap/',
				array( 'lenz-pdp' ),
				array( 'lenz-pd', 'lenz-pdp' ),
				array( 'lenz-datepicker', 'lenz-booking' )
			),
			'dragscroll'     => self::group( '/dragscroll/', array(), array( 'lenz-dragscroll' ), array( 'lenz-wc-my-account' ) ),
			'portfolios'     => self::group( '/lenz_portfolios_wrap|lenz_portfolios_items/', array(), array( 'lenz-portfolios' ) ),
			'experts'        => self::group( '/lenz_experts_wrap/', array(), array( 'lenz-experts-slider' ) ),
			'video_sliders'  => self::group( '/lenz_v_portfolios/', array(), array( 'lenz-video-portfolios' ) ),
			'filmstrip'      => self::group( '/lenz_filmstrip/', array(), array( 'lenz-filmstrip-text' ) ),
			'reservation'    => self::group( '/lenz_reservation_wrap/', array(), array( 'lenz-reservation' ) ),
			'archive_filter' => self::group( '/archive-filter/', array(), array( 'lenz-archive-filters-widget' ) ),
			// Only the script: archive.css also styles parts of other pages.
			'archive_grid'   => self::group( '/lenz-isotope-posts/', array(), array( 'lenz-archive' ) ),
			'video_player'   => self::group( '/video-container|video-player/', array(), array( 'lenz-video-player' ) ),
		);
	}

	/**
	 * One group.
	 *
	 * @param string   $markers Regex that finds the markup needing the group.
	 * @param string[] $styles  Style handles.
	 * @param string[] $scripts Script handles.
	 * @param string[] $needs   Handles that keep the group when printed.
	 * @param string   $standin JS run (with `w` = window, `$` = jQuery, `n` = a no-op returning this) when it is left out.
	 */
	private static function group( string $markers, array $styles, array $scripts = array(), array $needs = array(), string $standin = '' ): array {
		return array(
			'markers' => $markers,
			'styles'  => $styles,
			'scripts' => $scripts,
			'needs'   => $needs,
			'standin' => $standin,
		);
	}
}
