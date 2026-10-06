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
	 * The palette and font as one declaration list (`--main-font:…;--body:…;`)
	 * for admin previews, which print it on their wrapper so the front-end
	 * stylesheets resolve the same defaults there as on the site.
	 */
	public static function preview_vars(): string {
		$vars = '--main-font:' . self::font() . ';';
		foreach ( self::palette() as $name => $value ) {
			$vars .= $name . ':' . $value . ';';
		}

		return $vars;
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
	 * The phone number the admin put in the support box of Lenz's mobile menu
	 * (Header → Mobile menu → support "top text"), or '' when none is saved.
	 * Lenz's own default there is demo data, so only a saved value counts, and
	 * only when it holds at least seven digits (Latin, Persian or Arabic).
	 */
	public static function support_phone(): string {
		$text = self::is_active() ? trim( (string) self::option( 'mobile-menu-support-top-text', '' ) ) : '';

		return preg_match_all( '/[0-9\x{06F0}-\x{06F9}\x{0660}-\x{0669}]/u', $text ) >= 7 ? $text : '';
	}

	/**
	 * The theme's logo as an `<img>`: the header logo (`logo-img`) or the
	 * footer one (`footer-logo-img`, Lenz's white version). Like the theme,
	 * Lenz's own logo files stand in when nothing is saved; '' when Lenz is
	 * set to a text logo there. Without Lenz: the Customizer's site logo.
	 *
	 * @param string $area `header` or `footer`.
	 */
	public static function logo_html( string $area ): string {
		$alt = array( 'alt' => get_bloginfo( 'name' ) );

		if ( ! self::is_active() ) {
			$logo_id = (int) get_theme_mod( 'custom_logo' );

			return $logo_id ? (string) wp_get_attachment_image( $logo_id, 'full', false, $alt ) : '';
		}

		$prefix = 'footer' === $area ? 'footer-' : '';
		if ( 'text' === self::option( $prefix . 'logo-type', 'img' ) ) {
			return '';
		}

		$image = self::option( $prefix . 'logo-img', '' );
		if ( is_array( $image ) && ! empty( $image['id'] ) ) {
			return (string) wp_get_attachment_image( (int) $image['id'], 'full', false, $alt );
		}

		$url = is_array( $image ) ? (string) ( $image['url'] ?? '' ) : (string) $image;
		if ( '' === $url && defined( 'LENZ_URI' ) ) {
			$url = LENZ_URI . ( 'footer' === $area ? 'assets/images/logo-white.svg' : 'assets/images/logo.svg' );
		}

		return '' !== $url ? sprintf( '<img src="%1$s" alt="%2$s">', esc_url( $url ), esc_attr( $alt['alt'] ) ) : '';
	}

	/**
	 * One of Lenz's two footer menus: its menu location and the column title
	 * saved in Footer → menu 1/2 title ('' when never saved).
	 *
	 * @param int $number 1 or 2.
	 * @return array{location:string, title:string}
	 */
	public static function footer_menu( int $number ): array {
		return array(
			'location' => 'footer-menu' . $number,
			'title'    => self::is_active() ? (string) self::option( 'footer-menu-' . $number . '-title', '' ) : '',
		);
	}

	/**
	 * Footer texts the admin saved in Lenz (Footer → about, copyright). Only
	 * saved values count: Lenz's defaults there are its demo studio's copy.
	 *
	 * @param string $key `about` or `copyright`.
	 */
	public static function footer_text( string $key ): string {
		$options = array(
			'about'     => 'footer-about',
			'copyright' => 'footer-copyright-text',
		);

		return self::is_active() && isset( $options[ $key ] ) ? trim( (string) self::option( $options[ $key ], '' ) ) : '';
	}

	/**
	 * Branches saved in Lenz's footer (Footer → addresses), in order. Redux
	 * stores the repeater as one list per field.
	 *
	 * @return array<int, array{title:string, address:string, link:string, phone:string}>
	 */
	public static function footer_addresses(): array {
		$rows = self::is_active() ? self::option( 'footer-addresses', array() ) : array();
		if ( ! is_array( $rows ) || empty( $rows['footer-address-title'] ) || ! is_array( $rows['footer-address-title'] ) ) {
			return array();
		}

		$list = array();
		foreach ( $rows['footer-address-title'] as $index => $title ) {
			$list[] = array(
				'title'   => (string) $title,
				'address' => (string) ( $rows['footer-address-location'][ $index ] ?? '' ),
				'link'    => (string) ( $rows['footer-address-link'][ $index ] ?? '' ),
				'phone'   => (string) ( $rows['footer-address-phone'][ $index ] ?? '' ),
			);
		}

		return $list;
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
