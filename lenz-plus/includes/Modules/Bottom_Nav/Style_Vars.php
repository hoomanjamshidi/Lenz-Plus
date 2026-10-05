<?php
/**
 * Builds the inline CSS that turns settings into CSS custom properties.
 *
 * Default design tokens live in bottom-nav.css and already point at the
 * Lenz theme variables; this class only emits what the admin changed
 * (non-empty colors, sizes, fonts) plus the breakpoint media queries.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Bottom_Nav;

defined( 'ABSPATH' ) || exit;

/**
 * Settings → inline CSS custom properties and breakpoint media queries.
 */
final class Style_Vars {

	/**
	 * The module's inline stylesheet: tokens on the bar and its sheets, the
	 * reserved bottom space, and the breakpoint above which the bar is hidden.
	 *
	 * @param array $settings Module settings.
	 */
	public static function inline_css( array $settings ): string {
		$layout     = $settings['layout'];
		$breakpoint = (int) $layout['breakpoint'];

		$vars = self::base_vars( $settings ) + self::color_vars( $settings['colors'] );

		// Sheets are siblings of the bar, so they receive the same tokens.
		$css = '.lzp-bn,.lzp-sheet{' . self::declarations( $vars ) . '}';

		// Reserved space below the page content, also used to lift other fixed elements.
		$css .= sprintf(
			'@media (max-width:%1$.2fpx){:root{--lzp-bn-space:calc(%2$dpx + %3$dpx + env(safe-area-inset-bottom, 0px))}}',
			$breakpoint - 0.02,
			(int) $layout['height'],
			Styles::is_floating( $settings['style'] ) ? (int) $layout['offset'] : 0
		);

		$css .= sprintf( '@media (min-width:%dpx){.lzp-bn,.lzp-bn-spacer,.lzp-sheet{display:none!important}}', $breakpoint );

		return $css;
	}

	/**
	 * Size, typography and motion tokens.
	 *
	 * @param array $settings Module settings.
	 * @return array<string, string>
	 */
	public static function base_vars( array $settings ): array {
		$layout     = $settings['layout'];
		$typography = $settings['typography'];

		return array(
			'--lzp-bn-h'           => (int) $layout['height'] . 'px',
			'--lzp-bn-icon'        => (int) $layout['icon_size'] . 'px',
			'--lzp-bn-radius'      => (int) $layout['radius'] . 'px',
			'--lzp-bn-offset'      => (int) $layout['offset'] . 'px',
			'--lzp-bn-max-w'       => (int) $layout['max_width'] . 'px',
			'--lzp-bn-stroke'      => (string) (float) $settings['icon_stroke'],
			'--lzp-bn-font'        => self::font_stack( $typography ),
			'--lzp-bn-font-size'   => (int) $typography['font_size'] . 'px',
			'--lzp-bn-font-weight' => (string) $typography['font_weight'],
			'--lzp-bn-z'           => (string) (int) $settings['behavior']['z_index'],
		);
	}

	/**
	 * Only colors the admin actually set; empty means "use the theme default".
	 *
	 * @param array $colors Color slot => value (see Schema::COLOR_SLOTS).
	 * @return array<string, string>
	 */
	public static function color_vars( array $colors ): array {
		$vars = array();
		foreach ( Schema::COLOR_SLOTS as $slot ) {
			if ( ! empty( $colors[ $slot ] ) ) {
				$vars[ "--lzp-bn-o-{$slot}" ] = $colors[ $slot ];
			}
		}

		return $vars;
	}

	/**
	 * Font stack for labels. The theme option uses Lenz's `--main-font`; if the
	 * variable is missing the whole value is invalid and the bar simply
	 * inherits the page font.
	 *
	 * @param array $typography Typography settings.
	 */
	private static function font_stack( array $typography ): string {
		if ( 'inherit' === $typography['font_source'] ) {
			return 'inherit';
		}

		if ( 'custom' === $typography['font_source'] && '' !== $typography['font_family'] ) {
			return $typography['font_family'] . ', Tahoma, Arial, sans-serif';
		}

		return 'var(--main-font), Tahoma, Arial, sans-serif';
	}

	/**
	 * `name:value;` pairs for a declaration block.
	 *
	 * @param array<string, string> $vars Custom property => value.
	 */
	private static function declarations( array $vars ): string {
		$css = '';
		foreach ( $vars as $name => $value ) {
			$css .= $name . ':' . $value . ';';
		}

		return $css;
	}
}
