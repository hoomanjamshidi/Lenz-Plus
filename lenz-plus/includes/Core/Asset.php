<?php
/**
 * URLs (and, for inline printing, contents) of front-end asset files.
 *
 * tools/minify.mjs writes a minified copy (`*.min.css`, `*.min.js`) next to
 * each front-end file. Visitors get that copy, so PageSpeed and GTmetrix
 * report nothing to minify; with SCRIPT_DEBUG on, or when a copy is missing,
 * the readable source is served instead.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Core;

defined( 'ABSPATH' ) || exit;

/** URLs and contents of front-end assets (minified unless SCRIPT_DEBUG). */
final class Asset {

	/** Shared script that opens Lenz's mobile menu (`window.lzpLenzMenu`). */
	public const LENZ_MENU = 'lzp-lenz-menu';

	/**
	 * Registers the scripts several modules depend on. Safe to call more than
	 * once; each module calls it before enqueuing its own script.
	 */
	public static function register_shared(): void {
		if ( wp_script_is( self::LENZ_MENU, 'registered' ) ) {
			return;
		}

		wp_register_script(
			self::LENZ_MENU,
			self::url( 'assets/modules/shared/js/lenz-menu.js' ),
			array(),
			LENZ_PLUS_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	/**
	 * @param string $path Path inside the plugin, e.g. `assets/modules/builder/css/slider.css`.
	 */
	public static function url( string $path ): string {
		return LENZ_PLUS_URL . self::served( $path );
	}

	/**
	 * Contents of the served copy, for printing inline.
	 *
	 * @param string $path Path inside the plugin.
	 */
	public static function contents( string $path ): string {
		return (string) file_get_contents( LENZ_PLUS_DIR . self::served( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file.
	}

	/**
	 * The minified copy's path when it should be served, else the source's.
	 *
	 * @param string $path Path inside the plugin.
	 */
	private static function served( string $path ): string {
		$min = (string) preg_replace( '/\.(css|js)$/', '.min.$1', $path );

		if ( $min !== $path && ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) && is_readable( LENZ_PLUS_DIR . $min ) ) {
			return $min;
		}

		return $path;
	}
}
