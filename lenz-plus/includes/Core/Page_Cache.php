<?php
/**
 * The page cache plugins the site uses (WP Rocket, LiteSpeed Cache, W3 Total
 * Cache, WP Super Cache): which ones can be cleared, and clearing them.
 *
 * Shared by the studio panel (its «clear the cache» button) and the speed
 * module (clearing after Lenz+ settings or header/footer templates change,
 * since every cached page holds a copy of the bottom bar, the support button
 * and the header).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Page cache plugin bridge.
 */
final class Page_Cache {

	/**
	 * Names of the cache plugins that can be cleared.
	 *
	 * @return string[]
	 */
	public static function purgeable(): array {
		$out = array();
		if ( function_exists( 'rocket_clean_domain' ) ) {
			$out[] = 'WP Rocket';
		}
		if ( has_action( 'litespeed_purge_all' ) ) {
			$out[] = 'LiteSpeed Cache';
		}
		if ( function_exists( 'w3tc_flush_all' ) ) {
			$out[] = 'W3 Total Cache';
		}
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			$out[] = 'WP Super Cache';
		}

		return $out;
	}

	/** Clears the page caches the site uses. */
	public static function purge(): void {
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
			if ( function_exists( 'rocket_clean_minify' ) ) {
				rocket_clean_minify();
			}
		}
		if ( has_action( 'litespeed_purge_all' ) ) {
			do_action( 'litespeed_purge_all' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- LiteSpeed Cache's hook.
		}
		if ( function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all();
		}
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			wp_cache_clear_cache();
		}
	}
}
