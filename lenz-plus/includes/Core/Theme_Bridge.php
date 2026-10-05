<?php
/**
 * Read-only bridge to the Lenz theme.
 *
 * Lenz stores its Redux options in the `lenz` option row (also exposed as
 * `$GLOBALS['lenz']` by Redux) and prints them as CSS custom properties
 * (`--primary-1`, `--secondary-1`, `--main-font`, ...) in its compiled
 * `uploads/lenz.css`. Modules default to those variables so they follow the
 * theme's palette, including its "dark" demo, which only swaps the values.
 * This class reads the same options on the PHP side, for admin previews and
 * for links the theme lets the admin configure.
 *
 * Everything here degrades gracefully when Lenz is not the active theme.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Theme detection, Redux option access and the theme's colour variables.
 */
final class Theme_Bridge {

	private const THEME_SLUG  = 'lenz';
	private const OPTION_NAME = 'lenz';

	/**
	 * Lenz CSS variables the plugin follows, with the Redux key that sets each
	 * one and the theme's own default (assets/css/style.min.css `:root`).
	 */
	private const COLORS = array(
		'--body'        => array( 'body_color', '#ffffff' ),
		'--text-main'   => array( 'text_main_color', '#666666' ),
		'--primary-1'   => array( 'primary_color_1', '#000000' ),
		'--primary-2'   => array( 'primary_color_2', '#8b8b8b' ),
		'--secondary-1' => array( 'secondary_color_1', '#ffffff' ),
		'--secondary-2' => array( 'secondary_color_2', '#eeeeee' ),
		'--text-2'      => array( 'text_color_2', '#a3a3a3' ),
		'--text-4'      => array( 'text_color_4', '#ffffff' ),
	);

	/** The theme's default body font (Typography → main-typography). */
	private const DEFAULT_FONT = 'IRANYekanXFANum';

	/**
	 * Whether Lenz (or a child theme of it) is active.
	 */
	public static function is_active(): bool {
		return self::THEME_SLUG === get_template();
	}

	/**
	 * One Redux option of the theme.
	 *
	 * Redux fills `$GLOBALS['lenz']` once the theme's options panel has
	 * loaded; before that, and when the theme's license check keeps the panel
	 * from loading, the stored row is the source. Lenz's own
	 * `Options::get_options()` returns only the defaults it is given in that
	 * case, so it is not used here.
	 *
	 * @param string $key      Redux option id (e.g. `header-reserve-link`).
	 * @param mixed  $fallback Value when the option is missing or empty.
	 * @return mixed
	 */
	public static function option( string $key, $fallback = null ) {
		$value = self::options()[ $key ] ?? null;

		return ( null === $value || '' === $value ) ? $fallback : $value;
	}

	/**
	 * A Redux switch. Lenz stores switches as "1" / "" (or "0"), and an option
	 * that was never saved means the field's default.
	 *
	 * @param string $key      Redux option id.
	 * @param bool   $fallback The field's default in the theme.
	 */
	public static function flag( string $key, bool $fallback ): bool {
		$value = self::options()[ $key ] ?? null;

		return null === $value ? $fallback : in_array( $value, array( true, 1, '1', 'true', 'on' ), true );
	}

	/**
	 * The theme's colours keyed by the CSS variable that carries each one,
	 * e.g. `[ '--primary-1' => '#000000', ... ]`. Admin previews print them on
	 * their own wrapper, because the theme's stylesheet is not loaded there.
	 *
	 * @return array<string, string>
	 */
	public static function palette(): array {
		$palette = array();

		foreach ( self::COLORS as $var => $source ) {
			$palette[ $var ] = self::color_option( $source[0], $source[1] );
		}

		return $palette;
	}

	/**
	 * The body font family chosen in Lenz → Typography (default IRANYekanXFANum).
	 */
	public static function font(): string {
		$typography = self::option( 'main-typography', array() );
		$family     = is_array( $typography ) ? (string) ( $typography['font-family'] ?? '' ) : '';

		return '' !== $family ? Sanitizer::font_family( $family ) : self::DEFAULT_FONT;
	}

	/**
	 * Where the theme's header account button points (Header → Account): the
	 * admin's link for members or for guests, '' when it is not set (callers
	 * fall back to WooCommerce's account page or the WordPress login).
	 *
	 * @param bool $logged_in Link for logged-in visitors (else for guests).
	 */
	public static function account_url( bool $logged_in ): string {
		return self::is_active() ? (string) self::option( $logged_in ? 'header-account-link' : 'header-account-link-guest', '' ) : '';
	}

	/**
	 * The theme's header "Reserve" button (Header → Reserve): its link and text,
	 * with Lenz's own defaults (`/booking`, «رزرو وقت») when never saved.
	 * Both are '' when Lenz is not active.
	 *
	 * @return array{url:string, text:string}
	 */
	public static function reserve_link(): array {
		if ( ! self::is_active() ) {
			return array(
				'url'  => '',
				'text' => '',
			);
		}

		return array(
			'url'  => (string) self::option( 'header-reserve-link', home_url( 'booking' ) ),
			'text' => (string) self::option( 'header-reserve-text', '' ),
		);
	}

	/**
	 * The stored option row: the Redux global when it holds values, else the
	 * database row (WordPress caches it per request).
	 *
	 * @return array<string, mixed>
	 */
	private static function options(): array {
		$global = $GLOBALS[ self::OPTION_NAME ] ?? null;
		if ( is_array( $global ) && $global ) {
			return $global;
		}

		$stored = get_option( self::OPTION_NAME, array() );

		return is_array( $stored ) ? $stored : array();
	}

	/**
	 * A colour option as a sanitized hex value.
	 *
	 * @param string $key      Redux option id.
	 * @param string $fallback Theme default.
	 */
	private static function color_option( string $key, string $fallback ): string {
		$value = self::option( $key, '' );

		// Redux "color_rgba"/"link_color" fields store arrays.
		if ( is_array( $value ) ) {
			$value = $value['color'] ?? ( $value['regular'] ?? '' );
		}

		$color = Sanitizer::color( (string) $value );

		return '' !== $color ? $color : $fallback;
	}
}
