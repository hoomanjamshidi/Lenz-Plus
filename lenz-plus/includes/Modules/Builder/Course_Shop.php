<?php
/**
 * WooCommerce rules for course products, so the shop agrees with the
 * course widgets: only an open course that registers through the checkout
 * and still has seats can be bought, one seat per order. Without these, a
 * crafted `?add-to-cart=<id>` (or a cached "Register" button) could sell a
 * coming-soon, archived or full course.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Purchasable and sold-individually filters for courses.
 */
final class Course_Shop {

	/** Hooks the product filters. */
	public function register(): void {
		add_filter( 'woocommerce_is_purchasable', array( $this, 'purchasable' ), 10, 2 );
		add_filter( 'woocommerce_is_sold_individually', array( $this, 'sold_individually' ), 10, 2 );
	}

	/**
	 * Courses are for sale only while open, with checkout registration and seats left.
	 *
	 * @param bool        $purchasable WooCommerce's answer.
	 * @param \WC_Product $product     Product.
	 */
	public function purchasable( $purchasable, $product ): bool {
		$id = $product instanceof \WC_Product ? (int) $product->get_id() : 0;
		if ( ! $purchasable || ! Course_Data::is_course( $id ) ) {
			return (bool) $purchasable;
		}

		$details = Course_Data::details( $id );

		return 'open' === $details['status'] && 'cart' === $details['registration'] && 0 !== Course_Data::seats_left( $id );
	}

	/**
	 * One seat per order: a second "Register" from a cached page does not add another.
	 *
	 * @param bool        $individually WooCommerce's answer.
	 * @param \WC_Product $product      Product.
	 */
	public function sold_individually( $individually, $product ): bool {
		return (bool) $individually || ( $product instanceof \WC_Product && Course_Data::is_course( (int) $product->get_id() ) );
	}
}
