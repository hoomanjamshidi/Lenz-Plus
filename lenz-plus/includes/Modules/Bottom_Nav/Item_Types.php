<?php
/**
 * Registry of button types (what a navigation item does when tapped).
 *
 * The metadata drives the admin editor: `fields` lists the type-specific
 * item settings the editor shows. Runtime behaviour lives in Item_Resolver
 * (PHP) and bottom-nav.js (actions).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Bottom_Nav;

defined( 'ABSPATH' ) || exit;

/**
 * Button types and their editor metadata.
 */
final class Item_Types {

	/**
	 * Every button type with its label, description, default label and icon,
	 * the item fields its editor shows and the plugin it needs.
	 *
	 * @return array<string, array{label:string, description:string, default_label:string, icon:string, fields:string[], requires:string}>
	 */
	public static function all(): array {
		return array(
			'home'        => array(
				'label'         => __( 'Home', 'lenz-plus' ),
				'description'   => __( 'Links to the site front page.', 'lenz-plus' ),
				'default_label' => __( 'Home', 'lenz-plus' ),
				'icon'          => 'home',
				'fields'        => array(),
				'requires'      => '',
			),
			'link'        => array(
				'label'         => __( 'Custom link', 'lenz-plus' ),
				'description'   => __( 'Any page, category, phone number or external URL.', 'lenz-plus' ),
				'default_label' => __( 'Link', 'lenz-plus' ),
				'icon'          => 'images',
				'fields'        => array( 'url', 'new_tab' ),
				'requires'      => '',
			),
			'archive'     => array(
				'label'         => __( 'Archive', 'lenz-plus' ),
				'description'   => __( 'Links to the list of a content type (portfolio, videos, blog, shop) and stays active on its items.', 'lenz-plus' ),
				// Empty: the button shows the content type's own name until a label is set.
				'default_label' => '',
				'icon'          => 'images',
				'fields'        => array( 'archive_type' ),
				'requires'      => '',
			),
			'reserve'     => array(
				'label'         => __( 'Booking', 'lenz-plus' ),
				'description'   => __( 'Links to the booking page of the Lenz "Reserve" header button, or to your own URL.', 'lenz-plus' ),
				'default_label' => __( 'Book a session', 'lenz-plus' ),
				'icon'          => 'calendar',
				'fields'        => array( 'url', 'new_tab' ),
				'requires'      => '',
			),
			'search'      => array(
				'label'         => __( 'Search', 'lenz-plus' ),
				'description'   => __( 'Opens a search sheet.', 'lenz-plus' ),
				'default_label' => __( 'Search', 'lenz-plus' ),
				'icon'          => 'search',
				'fields'        => array( 'search_post_types', 'search_live', 'sheet_title' ),
				'requires'      => '',
			),
			'cart'        => array(
				'label'         => __( 'Cart', 'lenz-plus' ),
				'description'   => __( 'Live item count; opens a cart sheet or the cart page.', 'lenz-plus' ),
				'default_label' => __( 'Cart', 'lenz-plus' ),
				'icon'          => 'bag',
				'fields'        => array( 'cart_action', 'hide_empty_badge', 'sheet_title' ),
				'requires'      => 'woocommerce',
			),
			'account'     => array(
				'label'         => __( 'Account', 'lenz-plus' ),
				'description'   => __( 'User dashboard, or login for guests.', 'lenz-plus' ),
				'default_label' => __( 'Account', 'lenz-plus' ),
				'icon'          => 'user',
				'fields'        => array( 'guest_label', 'show_avatar', 'url' ),
				'requires'      => '',
			),
			'menu'        => array(
				'label'         => __( 'Menu', 'lenz-plus' ),
				'description'   => __( 'Opens the Lenz mobile menu or a WordPress menu.', 'lenz-plus' ),
				'default_label' => __( 'Menu', 'lenz-plus' ),
				'icon'          => 'menu',
				'fields'        => array( 'menu_source', 'menu_id', 'sheet_title' ),
				'requires'      => '',
			),
			'content'     => array(
				'label'         => __( 'Content sheet', 'lenz-plus' ),
				'description'   => __( 'Shows an Elementor template or shortcode in a sheet.', 'lenz-plus' ),
				'default_label' => __( 'More', 'lenz-plus' ),
				'icon'          => 'more',
				'fields'        => array( 'content_source', 'content_id', 'content_html', 'sheet_title' ),
				'requires'      => '',
			),
			'back_to_top' => array(
				'label'         => __( 'Back to top', 'lenz-plus' ),
				'description'   => __( 'Smoothly scrolls to the top of the page.', 'lenz-plus' ),
				'default_label' => __( 'Top', 'lenz-plus' ),
				'icon'          => 'arrow-up',
				'fields'        => array(),
				'requires'      => '',
			),
			'selector'    => array(
				'label'         => __( 'Click an element', 'lenz-plus' ),
				'description'   => __( 'Advanced: clicks any element on the page by CSS selector.', 'lenz-plus' ),
				'default_label' => __( 'Action', 'lenz-plus' ),
				'icon'          => 'sparkles',
				'fields'        => array( 'selector', 'url' ),
				'requires'      => '',
			),
		);
	}

	/**
	 * Every type id, in editor order.
	 *
	 * @return string[]
	 */
	public static function ids(): array {
		return array_keys( self::all() );
	}

	/**
	 * One type's metadata, or null for an unknown id.
	 *
	 * @param string $type Type id.
	 */
	public static function get( string $type ): ?array {
		return self::all()[ $type ] ?? null;
	}

	/**
	 * Whether the type's dependency (e.g. WooCommerce) is present.
	 *
	 * @param string $type Type id.
	 */
	public static function is_available( string $type ): bool {
		$definition = self::get( $type );
		if ( ! $definition ) {
			return false;
		}

		return 'woocommerce' !== $definition['requires'] || class_exists( 'WooCommerce' );
	}
}
