<?php
/**
 * How the panel names an order's customer in lists, pages and exports.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Shop;

defined( 'ABSPATH' ) || exit;

/**
 * Order labels.
 */
final class Order_Text {

	/**
	 * Billing name, else the company, else the email.
	 *
	 * @param \WC_Order $order Order.
	 */
	public static function customer_name( \WC_Order $order ): string {
		$name = trim( $order->get_formatted_billing_full_name() );
		if ( '' === $name ) {
			$name = (string) $order->get_billing_company();
		}

		return '' !== $name ? $name : (string) $order->get_billing_email();
	}

	/**
	 * Order statuses without `wc-` → label.
	 *
	 * @return array<string, string>
	 */
	public static function statuses(): array {
		$statuses = array();
		foreach ( wc_get_order_statuses() as $key => $label ) {
			$statuses[ (string) preg_replace( '/^wc-/', '', $key ) ] = $label;
		}

		return $statuses;
	}
}
