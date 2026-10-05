<?php
/**
 * Turns saved navigation items into view models for the current request.
 *
 * A view model contains everything the templates need (tag, href, action,
 * icon markup, badge, sheet) so the views stay free of business logic.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Bottom_Nav;

use LenzPlus\Core\Icon_Library;
use LenzPlus\Core\Theme_Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * Resolves saved items (labels, links, actions, icons, sheets) for one request.
 */
final class Item_Resolver {

	/** Cart badges show "99+" above this count. */
	private const BADGE_CAP = 99;

	/**
	 * Menu locations shown in a sheet when the Lenz mobile menu is unavailable:
	 * Lenz's own two first, then common names of other themes.
	 */
	private const MENU_LOCATIONS = array( 'main-menu-mobile', 'main-menu', 'primary', 'menu-1' );

	/** @var array Module settings. */
	private $settings;

	/**
	 * @param array $settings Module settings.
	 */
	public function __construct( array $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Visible items for the current visitor, with `current` and `featured` resolved.
	 *
	 * @return array<int, array> View models.
	 */
	public function resolve(): array {
		$items = array();

		foreach ( $this->settings['items'] as $item ) {
			if ( ! $this->is_visible( $item ) ) {
				continue;
			}

			$model = $this->build( $item );
			if ( null !== $model ) {
				$items[] = $model;
			}
		}

		$this->mark_current( $items );
		$this->mark_featured( $items );

		return $items;
	}

	/**
	 * Cart badge markup, shared by the initial render and WooCommerce fragments.
	 *
	 * @param bool $hide_empty Hide the badge when the cart is empty.
	 */
	public static function cart_badge_html( bool $hide_empty = true ): string {
		$count = 0;
		if ( function_exists( 'WC' ) && WC()->cart ) {
			$count = (int) WC()->cart->get_cart_contents_count();
		}

		$classes = array( 'lzp-bn__badge', 'lzp-bn-cart-count' );
		if ( 0 === $count && $hide_empty ) {
			$classes[] = 'is-empty';
		}

		return sprintf(
			'<span class="%1$s" data-count="%2$d">%3$s</span>',
			esc_attr( implode( ' ', $classes ) ),
			$count,
			esc_html( $count > self::BADGE_CAP ? self::BADGE_CAP . '+' : (string) $count )
		);
	}

	/**
	 * @param array $item Saved item.
	 */
	private function is_visible( array $item ): bool {
		if ( empty( $item['enabled'] ) || ! Item_Types::is_available( $item['type'] ) ) {
			return false;
		}

		if ( 'guests' === $item['visibility'] && is_user_logged_in() ) {
			return false;
		}

		return ! ( 'members' === $item['visibility'] && ! is_user_logged_in() );
	}

	/**
	 * Builds the view model, or null when the item cannot work on this site.
	 *
	 * @param array $item Saved item.
	 */
	private function build( array $item ): ?array {
		$type_label = Item_Types::get( $item['type'] )['default_label'] ?? '';
		$label      = '' !== $item['label'] ? $item['label'] : $type_label;

		$model = array(
			'id'          => $item['id'],
			'type'        => $item['type'],
			'label'       => $label,
			'href'        => '',
			'target'      => '',
			'action'      => '',
			'data'        => array(),
			'icon'        => $this->icon_html( $item, false ),
			'icon_active' => $this->icon_html( $item, true ),
			'badge'       => '' !== $item['badge'] ? '<span class="lzp-bn__badge lzp-bn__badge--text">' . esc_html( $item['badge'] ) . '</span>' : '',
			'featured'    => false,
			'current'     => false,
			'match'       => '',
			'sheet'       => null,
		);

		$method = 'build_' . $item['type'];

		return method_exists( $this, $method ) ? $this->$method( $model, $item ) : null;
	}

	/**
	 * @param array $model View model.
	 */
	private function build_home( array $model ): array {
		$model['href']  = home_url( '/' );
		$model['match'] = 'home';

		return $model;
	}

	/**
	 * @param array $model View model.
	 * @param array $item  Saved item.
	 */
	private function build_link( array $model, array $item ): ?array {
		if ( '' === $item['url'] ) {
			return null;
		}

		$model['href']  = $item['url'];
		$model['match'] = 'url';
		if ( $item['new_tab'] ) {
			$model['target'] = '_blank';
		}

		return $model;
	}

	/**
	 * A content type's list page. Without a label the button uses the type's
	 * own plural name (e.g. «نمونه‌کارها» for Lenz's portfolio).
	 *
	 * @param array $model View model.
	 * @param array $item  Saved item.
	 */
	private function build_archive( array $model, array $item ): ?array {
		$type = get_post_type_object( $item['archive_type'] );
		$url  = $type ? get_post_type_archive_link( $type->name ) : false;
		if ( ! $url ) {
			return null;
		}

		if ( '' === $item['label'] ) {
			$model['label'] = $type->labels->name;
		}

		$model['href']         = $url;
		$model['match']        = 'archive';
		$model['archive_type'] = $type->name;

		return $model;
	}

	/**
	 * Booking link: the item's own URL, else the Lenz header "Reserve" button.
	 * Without a label it reuses that button's text, so the bar says what the
	 * header says.
	 *
	 * @param array $model View model.
	 * @param array $item  Saved item.
	 */
	private function build_reserve( array $model, array $item ): ?array {
		$theme = Theme_Bridge::reserve_link();
		$url   = '' !== $item['url'] ? $item['url'] : $theme['url'];
		if ( '' === $url ) {
			return null;
		}

		if ( '' === $item['label'] && '' !== $theme['text'] ) {
			$model['label'] = $theme['text'];
		}

		$model['href']  = $url;
		$model['match'] = 'url';
		if ( $item['new_tab'] ) {
			$model['target'] = '_blank';
		}

		return $model;
	}

	/**
	 * @param array $model View model.
	 * @param array $item  Saved item.
	 */
	private function build_search( array $model, array $item ): array {
		$model['href']   = home_url( '/?s=' );
		$model['action'] = 'sheet';
		$model['match']  = 'search';
		$model['sheet']  = array(
			'kind'      => 'search',
			'title'     => $this->sheet_title( $item, $model['label'] ),
			'item_id'   => $item['id'],
			'live'      => (bool) $item['search_live'],
			'post_type' => Search_Scope::for_results_page( Search_Scope::post_types( $item ) ),
		);

		return $model;
	}

	/**
	 * @param array $model View model.
	 * @param array $item  Saved item.
	 */
	private function build_cart( array $model, array $item ): array {
		$action = $item['cart_action'];

		$model['href']  = 'checkout' === $action ? wc_get_checkout_url() : wc_get_cart_url();
		$model['match'] = 'cart';
		$model['badge'] = self::cart_badge_html( $item['hide_empty_badge'] ) . $model['badge'];
		$model['data']  = array( 'hide-empty' => $item['hide_empty_badge'] ? '1' : '0' );

		// Lenz's own mini cart only opens on hover, so a tap opens our sheet.
		if ( 'sheet' === $action ) {
			$model['action'] = 'sheet';
			$model['sheet']  = array(
				'kind'  => 'cart',
				'title' => $this->sheet_title( $item, $model['label'] ),
			);
		}

		return $model;
	}

	/**
	 * @param array $model View model.
	 * @param array $item  Saved item.
	 */
	private function build_account( array $model, array $item ): array {
		$woo_url        = function_exists( 'wc_get_page_permalink' ) ? (string) wc_get_page_permalink( 'myaccount' ) : '';
		$model['match'] = 'account';

		if ( is_user_logged_in() ) {
			$default_url   = self::first_url( Theme_Bridge::account_url( true ), $woo_url, admin_url( 'profile.php' ) );
			$model['href'] = '' !== $item['url'] ? $item['url'] : $default_url;

			if ( $item['show_avatar'] ) {
				$avatar = get_avatar_url( get_current_user_id(), array( 'size' => 64 ) );
				if ( $avatar ) {
					$model['icon']        = sprintf( '<img class="lzp-bn__avatar" src="%s" alt="" width="32" height="32" loading="lazy" decoding="async">', esc_url( $avatar ) );
					$model['icon_active'] = '';
				}
			}

			return $model;
		}

		$model['label'] = '' !== $item['guest_label'] ? $item['guest_label'] : __( 'Login', 'lenz-plus' );
		$model['href']  = self::first_url( Theme_Bridge::account_url( false ), $woo_url, wp_login_url() );

		return $model;
	}

	/**
	 * The first non-empty URL of a fallback chain.
	 *
	 * @param string ...$urls Candidates, most specific first.
	 */
	private static function first_url( string ...$urls ): string {
		foreach ( $urls as $url ) {
			if ( '' !== $url ) {
				return $url;
			}
		}

		return '';
	}

	/**
	 * @param array $model View model.
	 * @param array $item  Saved item.
	 */
	private function build_menu( array $model, array $item ): ?array {
		$title = $this->sheet_title( $item, $model['label'] );

		if ( 'wp_menu' === $item['menu_source'] ) {
			if ( ! $item['menu_id'] || ! wp_get_nav_menu_object( $item['menu_id'] ) ) {
				return null;
			}

			$model['action'] = 'sheet';
			$model['sheet']  = array(
				'kind'     => 'menu',
				'title'    => $title,
				'menu'     => (int) $item['menu_id'],
				'location' => '',
			);

			return $model;
		}

		// Lenz prints its mobile menu (#mobile-menu) on every page; bottom-nav.js opens it.
		if ( Theme_Bridge::is_active() ) {
			$model['action'] = 'theme-menu';
			return $model;
		}

		// Another theme: show its main menu in a sheet instead.
		foreach ( self::MENU_LOCATIONS as $location ) {
			if ( has_nav_menu( $location ) ) {
				$model['action'] = 'sheet';
				$model['sheet']  = array(
					'kind'     => 'menu',
					'title'    => $title,
					'menu'     => 0,
					'location' => $location,
				);

				return $model;
			}
		}

		return null;
	}

	/**
	 * @param array $model View model.
	 * @param array $item  Saved item.
	 */
	private function build_content( array $model, array $item ): ?array {
		$uses_elementor = 'elementor' === $item['content_source'];

		if ( $uses_elementor ? ! $item['content_id'] : '' === trim( $item['content_html'] ) ) {
			return null;
		}

		$model['action'] = 'sheet';
		$model['sheet']  = array(
			'kind'       => 'content',
			'title'      => $this->sheet_title( $item, $model['label'] ),
			'source'     => $item['content_source'],
			'content_id' => (int) $item['content_id'],
			'html'       => $item['content_html'],
		);

		return $model;
	}

	/**
	 * @param array $model View model.
	 */
	private function build_back_to_top( array $model ): array {
		$model['action'] = 'top';

		return $model;
	}

	/**
	 * @param array $model View model.
	 * @param array $item  Saved item.
	 */
	private function build_selector( array $model, array $item ): ?array {
		if ( '' === $item['selector'] && '' === $item['url'] ) {
			return null;
		}

		$model['href']   = $item['url'];
		$model['action'] = '' !== $item['selector'] ? 'selector' : '';
		$model['data']   = array( 'selector' => $item['selector'] );

		return $model;
	}

	/**
	 * Icon markup for the item, or its filled "active" variant.
	 * Returns an empty string for the active variant when there is none.
	 *
	 * @param array $item   Saved item.
	 * @param bool  $active Build the active variant.
	 */
	private function icon_html( array $item, bool $active ): string {
		$pack          = $this->settings['icon_pack'];
		$wants_variant = $active && $this->settings['active_filled'];

		switch ( $item['icon_source'] ) {
			case 'image':
				if ( $active || '' === $item['icon_image'] ) {
					break;
				}
				return sprintf( '<img class="lzp-bn__img" src="%s" alt="" width="24" height="24" loading="lazy" decoding="async">', esc_url( $item['icon_image'] ) );

			case 'svg':
				if ( $active || '' === $item['icon_svg'] ) {
					break;
				}
				return Icon_Library::decorate( Icon_Library::sanitize_svg( $item['icon_svg'] ) );

			case 'fontawesome':
				if ( '' === $item['icon_fa'] ) {
					break;
				}
				if ( $active ) {
					return $wants_variant ? self::fa_icon( self::fa_solid( $item['icon_fa'] ) ) : '';
				}
				return self::fa_icon( $item['icon_fa'] );
		}

		if ( $active && ! $wants_variant ) {
			return '';
		}

		if ( $active && ! Icon_Library::has_active_variant( $pack, $item['icon'] ) ) {
			return '';
		}

		// Default: the global icon pack. Font packs need Lenz's stylesheets;
		// without them (or without a Lenz glyph for the key) the SVG fallback chain is used.
		if ( Icon_Library::FONT_AWESOME === $pack && Icon_Library::is_available( $pack ) ) {
			$classes = Icon_Library::fa_class( $item['icon'], $this->settings['fa_weight'] );
			return self::fa_icon( $active ? self::fa_solid( $classes ) : $classes );
		}

		$glyph = Icon_Library::LENZ === $pack && Icon_Library::is_available( $pack ) ? Icon_Library::lenz_class( $item['icon'], $active ) : '';
		if ( '' !== $glyph ) {
			return self::fa_icon( $glyph );
		}

		return Icon_Library::svg( $pack, $item['icon'], $active );
	}

	/**
	 * An icon-font glyph (Font Awesome or Lenz's `lenz-icon-*`).
	 *
	 * @param string $classes Glyph class list.
	 */
	private static function fa_icon( string $classes ): string {
		return '<i class="' . esc_attr( $classes ) . '" aria-hidden="true"></i>';
	}

	/**
	 * Swaps the weight prefix for the solid one; brand icons are left alone.
	 *
	 * @param string $classes Font Awesome class list.
	 */
	private static function fa_solid( string $classes ): string {
		return (string) preg_replace( '/\bfar\b/', 'fas', $classes );
	}

	/**
	 * @param array  $item  Saved item.
	 * @param string $label Resolved item label.
	 */
	private function sheet_title( array $item, string $label ): string {
		return '' !== $item['sheet_title'] ? $item['sheet_title'] : $label;
	}

	/**
	 * Marks the first item that matches the current request.
	 *
	 * @param array $items View models (by reference).
	 */
	private function mark_current( array &$items ): void {
		foreach ( $items as &$item ) {
			if ( $this->matches_request( $item ) ) {
				$item['current'] = true;
				break;
			}
		}
		unset( $item );
	}

	/**
	 * @param array $item View model.
	 */
	private function matches_request( array $item ): bool {
		switch ( $item['match'] ) {
			case 'home':
				return is_front_page();
			case 'search':
				return is_search();
			case 'cart':
				return function_exists( 'is_cart' ) && ( is_cart() || is_checkout() );
			case 'account':
				return function_exists( 'is_account_page' ) ? is_account_page() : false;
			case 'url':
				return self::is_current_url( $item['href'] ) || self::is_shop_link( $item['href'] );
			case 'archive':
				return Archive_Types::is_current( $item['archive_type'] );
		}

		return false;
	}

	/**
	 * The shop page can be served from another URL (e.g. `?post_type=product`
	 * with plain permalinks), so a link to it is current whenever is_shop().
	 *
	 * @param string $url Item URL.
	 */
	private static function is_shop_link( string $url ): bool {
		if ( ! function_exists( 'is_shop' ) || ! is_shop() ) {
			return false;
		}

		return untrailingslashit( $url ) === untrailingslashit( (string) wc_get_page_permalink( 'shop' ) );
	}

	/**
	 * Compares a link with the current request: host, path and — for plain
	 * permalinks such as `/?page_id=5` — the link's query parameters.
	 *
	 * @param string $url Item URL.
	 */
	private static function is_current_url( string $url ): bool {
		$target = wp_parse_url( $url );
		if ( ! is_array( $target ) || ( empty( $target['path'] ) && empty( $target['host'] ) && empty( $target['query'] ) ) ) {
			return false;
		}

		$home_host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		if ( ! empty( $target['host'] ) && strtolower( $target['host'] ) !== strtolower( $home_host ) ) {
			return false;
		}

		$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- only compared.
		$current_path = (string) wp_parse_url( (string) $request_uri, PHP_URL_PATH );
		$target_path  = self::normalize_path( $target['path'] ?? '/' );

		if ( self::normalize_path( $current_path ) !== $target_path ) {
			return false;
		}

		// Every query parameter of the link must be present in the request.
		parse_str( $target['query'] ?? '', $target_query );
		if ( $target_query ) {
			foreach ( $target_query as $key => $value ) {
				$current = $_GET[ $key ] ?? null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput -- read-only comparison.
				if ( ! is_scalar( $current ) || ! is_scalar( $value ) || (string) wp_unslash( $current ) !== (string) $value ) {
					return false;
				}
			}

			return true;
		}

		// A bare link to the site root only matches the front page, not `/?s=…` and friends.
		$home_path = self::normalize_path( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) );

		return $target_path !== $home_path || is_front_page();
	}

	/**
	 * @param string $path URL path.
	 */
	private static function normalize_path( string $path ): string {
		$path = strtolower( rawurldecode( $path ) );

		return '/' . trim( $path, '/' );
	}

	/**
	 * Flags the item rendered as the raised centre button in styles that have one:
	 * the item marked "featured", or the middle item otherwise.
	 *
	 * @param array $items View models (by reference).
	 */
	private function mark_featured( array &$items ): void {
		if ( ! $items || ! Styles::has_featured_button( $this->settings['style'] ) ) {
			return;
		}

		$featured_ids = wp_list_pluck(
			array_filter(
				$this->settings['items'],
				static function ( $item ) {
					return ! empty( $item['featured'] );
				}
			),
			'id'
		);

		$index = (int) floor( count( $items ) / 2 );
		foreach ( $items as $i => $item ) {
			if ( in_array( $item['id'], $featured_ids, true ) ) {
				$index = $i;
				break;
			}
		}

		$items[ $index ]['featured'] = true;
	}
}
