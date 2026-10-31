<?php
/**
 * Icon markup in the designs' icon style, shared by the Elementor widgets
 * and the parts the builder prints outside Elementor (shop pages).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

use LenzPlus\Core\Icon_Library;

defined( 'ABSPATH' ) || exit;

/**
 * Prints a Lenz glyph or an SVG for a semantic icon key.
 */
final class Icon {

	/**
	 * A Lenz glyph (`<i class="lenz-icon-…">`) when the `lenz` pack is chosen
	 * and the font has the key, else the SVG chain of the pack.
	 *
	 * @param string $pack       Icon pack (the builder's `options.icon_pack`).
	 * @param string $key        Semantic icon key.
	 * @param string $class_name Extra classes.
	 */
	public static function html( string $pack, string $key, string $class_name = '' ): string {
		// The RTL "forward" arrow is mirrored on LTR sites (builder.css).
		if ( 'arrow-forward' === $key ) {
			$class_name = trim( $class_name . ' lzp-ico--forward' );
		}

		$glyph = Icon_Library::LENZ === $pack && Icon_Library::is_available( $pack ) ? Icon_Library::lenz_class( $key ) : '';
		if ( '' !== $glyph ) {
			// Lenz styles glyphs through `[class^=lenz-icon-]`, so its class goes first.
			return '<i class="' . esc_attr( trim( $glyph . ' lzp-ico lzp-ico--glyph ' . $class_name ) ) . '" aria-hidden="true"></i>';
		}

		return Icon_Library::svg( $pack, $key, false, trim( 'lzp-ico ' . $class_name ) );
	}
}
