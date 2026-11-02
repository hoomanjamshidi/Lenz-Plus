<?php
/**
 * Page caches and optimisers (WP Rocket, LiteSpeed Cache, W3 Total Cache,
 * WP Super Cache…) and the studio panel.
 *
 * The panel is a private page: it must never be cached, and optimisers must
 * not delay, combine or lazy-load its scripts (WP Rocket's «Delay JavaScript
 * execution» would hold the panel's script until the first tap). Its address
 * is kept out of WP Rocket's cache, and while the panel prints, the common
 * «do not cache / do not optimise» signals are raised. People who manage the
 * site can clear the page cache from the panel's account screen. Ported from
 * Daftar's Integrations\Cache.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Integrations;

defined( 'ABSPATH' ) || exit;

/**
 * Cache plugin bridge.
 */
final class Cache {

	/** @var string Panel slug (address). */
	private static $slug = '';

	/**
	 * Hooks the exclusion of the panel's address.
	 *
	 * @param string $slug Panel slug.
	 */
	public static function register( string $slug ): void {
		self::$slug = $slug;
		add_filter( 'rocket_cache_reject_uri', array( self::class, 'reject_uri' ) );
	}

	/**
	 * Keeps the panel's address out of WP Rocket's cache (WP Rocket also writes it to its config file).
	 *
	 * @param mixed $uris URI patterns.
	 * @return array
	 */
	public static function reject_uri( $uris ) {
		$uris   = (array) $uris;
		$uris[] = '/' . self::$slug . '/(.*)';

		return $uris;
	}

	/**
	 * Raises the no-cache / no-optimise signals for the current request
	 * (called just before the panel prints).
	 */
	public static function bypass(): void {
		foreach ( array( 'DONOTCACHEPAGE', 'DONOTROCKETOPTIMIZE', 'DONOTMINIFY', 'DONOTCACHEDB', 'DONOTCACHEOBJECT' ) as $constant ) {
			if ( ! defined( $constant ) ) {
				define( $constant, true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.VariableConstantNameFound -- the cache plugins' own constants.
			}
		}

		do_action( 'litespeed_control_set_nocache', 'lenz-plus studio panel' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- LiteSpeed Cache's hook.

		// WP Rocket: no lazy-load, delay, defer or minify on this page even if the constants are ignored.
		add_filter( 'do_rocket_lazyload', '__return_false', PHP_INT_MAX );
		add_filter( 'rocket_delay_js_exclusions', array( self::class, 'all_scripts' ), PHP_INT_MAX );
		foreach ( array( 'delay_js', 'defer_all_js', 'minify_js', 'minify_css', 'remove_unused_css' ) as $option ) {
			add_filter( 'pre_get_rocket_option_' . $option, '__return_false', PHP_INT_MAX );
		}
	}

	/**
	 * Excludes every script from WP Rocket's delay (pattern list).
	 *
	 * @param mixed $patterns Exclusions.
	 * @return array
	 */
	public static function all_scripts( $patterns ) {
		$patterns   = (array) $patterns;
		$patterns[] = '(.*)';

		return $patterns;
	}

	/**
	 * Names of the cache plugins the panel can clear.
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
