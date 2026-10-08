<?php
/**
 * Photo frames of the mockups: an image cropped to a fixed aspect ratio
 * with rounded corners, following the global photo tone, or the mockups'
 * striped placeholder with a small label when no image is chosen yet.
 *
 * Presets leave their images empty, so a page made from a design looks like
 * the mockup until the owner picks real photos; the placeholder tells them
 * which shape each photo should have.
 *
 * - `high`: the picture most likely to be the Largest Contentful Paint (a
 *   hero photo). Loads right away with `fetchpriority="high"` and carries
 *   the usual "do not lazy-load" markers, because optimisation plugins
 *   (WP Rocket, LiteSpeed, Smush, Jetpack…) would otherwise lazy-load it.
 * - `lazy` (default): native `loading="lazy"`.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor;

defined( 'ABSPATH' ) || exit;

/**
 * Image or placeholder markup in a ratio box.
 */
final class Picture {

	/** Ratios offered by the widgets' "Shape" controls (the mockups' frames). */
	public const RATIOS = array( '1/1', '4/5', '3/4', '2/3', '9/16', '4/3', '3/2', '16/9', '21/9' );

	/**
	 * Ratio → label, for SELECT controls.
	 *
	 * @return array<string, string>
	 */
	public static function ratio_options(): array {
		$options = array();
		foreach ( self::RATIOS as $ratio ) {
			$options[ $ratio ] = str_replace( '/', ':', $ratio );
		}

		return $options;
	}

	/**
	 * Frame markup.
	 *
	 * @param array $media Elementor MEDIA value (`id`, `url`); empty for the placeholder.
	 * @param array $o {
	 *     @type string $ratio   One of RATIOS ('' keeps the image's own shape).
	 *     @type string $label   Placeholder label (what the photo should show).
	 *     @type string $alt     Alt text when the attachment has none.
	 *     @type string $loading `high` or `lazy`.
	 *     @type string $sizes   `sizes` attribute.
	 *     @type string $class   Extra classes on the frame.
	 *     @type string $tag     `div` or `figure`.
	 * }
	 */
	public static function frame( array $media, array $o = array() ): string {
		$o = array_merge(
			array(
				'ratio'   => '4/3',
				'label'   => '',
				'alt'     => '',
				'loading' => 'lazy',
				'sizes'   => '(max-width: 767px) 100vw, 50vw',
				'class'   => '',
				'tag'     => 'div',
			),
			$o
		);

		$ratio = in_array( $o['ratio'], self::RATIOS, true ) ? $o['ratio'] : '';
		$style = '' !== $ratio ? ' style="--lzp-ratio:' . esc_attr( str_replace( '/', ' / ', $ratio ) ) . '"' : '';
		$tag   = 'figure' === $o['tag'] ? 'figure' : 'div';
		$image = self::image( $media, $o );

		$classes = 'lzp-pic' . ( '' === $ratio ? ' lzp-pic--natural' : '' ) . ( '' === $image ? ' lzp-pic--empty' : '' ) . ( '' !== $o['class'] ? ' ' . $o['class'] : '' );

		if ( '' === $image ) {
			$label = '' !== $ratio ? str_replace( '/', ':', $ratio ) : '';
			if ( '' !== $o['label'] ) {
				$label .= ( '' !== $label ? "\n" : '' ) . $o['label'];
			}
			$image = '<span class="lzp-pic__label" aria-hidden="true">' . nl2br( esc_html( $label ) ) . '</span>';
		}

		return '<' . $tag . ' class="' . esc_attr( $classes ) . '"' . $style . '>' . $image . '</' . $tag . '>';
	}

	/**
	 * Whether a MEDIA value holds an image.
	 *
	 * @param mixed $media Elementor MEDIA value.
	 */
	public static function has_image( $media ): bool {
		return is_array( $media ) && ( ! empty( $media['id'] ) || ! empty( $media['url'] ) );
	}

	/**
	 * `<img>` for an attachment or a plain URL, '' without one.
	 *
	 * @param array $media Elementor MEDIA value.
	 * @param array $o     Options (see frame()).
	 */
	private static function image( array $media, array $o ): string {
		$id   = (int) ( $media['id'] ?? 0 );
		$url  = (string) ( $media['url'] ?? '' );
		$attr = self::loading_attributes( (string) $o['loading'] );

		$attr['class'] = trim( 'lzp-pic__img ' . ( $attr['class'] ?? '' ) );

		if ( $id ) {
			$attr['sizes'] = (string) $o['sizes'];
			if ( '' === trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) ) ) {
				$attr['alt'] = (string) $o['alt'];
			}

			$img = wp_get_attachment_image( $id, 'large', false, $attr );
			if ( '' !== $img ) {
				return $img;
			}
		}

		if ( '' === $url ) {
			return '';
		}

		$html = '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( (string) $o['alt'] ) . '"';
		foreach ( $attr as $name => $value ) {
			$html .= ' ' . $name . '="' . esc_attr( $value ) . '"';
		}

		return $html . '>';
	}

	/**
	 * @param string $loading `high` or `lazy`.
	 * @return array<string, string>
	 */
	private static function loading_attributes( string $loading ): array {
		if ( 'high' !== $loading ) {
			return array(
				'loading'  => 'lazy',
				'decoding' => 'async',
			);
		}

		return array(
			'class'          => 'skip-lazy',
			'loading'        => 'eager',
			'fetchpriority'  => 'high',
			'data-skip-lazy' => '1',
			'data-no-lazy'   => '1',
		);
	}
}
