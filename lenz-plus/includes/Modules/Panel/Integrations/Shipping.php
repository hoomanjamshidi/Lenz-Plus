<?php
/**
 * Iranian shipping plugins in the panel: «حمل و نقل ووکامرس» (PWS) and
 * Persian WooCommerce.
 *
 * PWS saves the province and city of an address as IDs of its `state_city`
 * terms; the order page and the reports turn them back into names. The panel
 * also stores a parcel tracking code per order and shows the one PWS saved
 * after registering a parcel with the post office or Tapin. Ported from
 * Daftar's Integrations\Shipping.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Integrations;

defined( 'ABSPATH' ) || exit;

/**
 * Shipping bridge.
 */
final class Shipping {

	/** Order meta holding the tracking code typed in the panel. */
	public const TRACKING_META = '_lzp_tracking_code';

	/** Order meta where PWS and its post/Tapin add-ons keep the tracking code. */
	private const PWS_TRACKING_KEYS = array( 'post_barcode', '_post_barcode', 'tapin_barcode', '_tapin_barcode', 'pws_tracking_code' );

	/**
	 * A province or city value as a name: PWS term IDs become term names,
	 * WooCommerce state codes become the store's state names.
	 *
	 * @param string $country  Country code.
	 * @param string $value    Saved value.
	 * @param bool   $is_state Whether the value is a state/province.
	 */
	public static function place_name( string $country, string $value, bool $is_state ): string {
		if ( '' === $value ) {
			return '';
		}

		if ( ctype_digit( $value ) && taxonomy_exists( 'state_city' ) ) {
			$term = get_term( (int) $value, 'state_city' );
			if ( $term instanceof \WP_Term ) {
				return $term->name;
			}
		}

		if ( $is_state && '' !== $country && function_exists( 'WC' ) ) {
			$states = WC()->countries->get_states( $country );
			if ( is_array( $states ) && isset( $states[ $value ] ) ) {
				return html_entity_decode( $states[ $value ], ENT_QUOTES, 'UTF-8' );
			}
		}

		return $value;
	}

	/**
	 * The order's address as lines, with PWS's province and city names
	 * (WooCommerce's own formatting would print their term IDs).
	 *
	 * @param \WC_Order $order Order.
	 * @return string[]
	 */
	public static function address_lines( \WC_Order $order ): array {
		$type    = $order->has_shipping_address() ? 'shipping' : 'billing';
		$country = (string) $order->{'get_' . $type . '_country'}();
		$place   = array_filter(
			array(
				self::place_name( $country, (string) $order->{'get_' . $type . '_state'}(), true ),
				self::place_name( $country, (string) $order->{'get_' . $type . '_city'}(), false ),
			)
		);

		$lines    = array_filter(
			array(
				implode( '، ', $place ),
				trim( $order->{'get_' . $type . '_address_1'}() . ' ' . $order->{'get_' . $type . '_address_2'}() ),
			)
		);
		$postcode = (string) $order->{'get_' . $type . '_postcode'}();
		if ( '' !== $postcode ) {
			/* translators: %s: postcode. */
			$lines[] = sprintf( __( 'Postcode: %s', 'lenz-plus' ), $postcode );
		}

		return array_values( $lines );
	}

	/**
	 * The order's tracking code and where it came from (`panel`, `pws` or '').
	 *
	 * @param \WC_Order $order Order.
	 * @return string[] [ code, source ]
	 */
	public static function tracking( \WC_Order $order ): array {
		$code = (string) $order->get_meta( self::TRACKING_META );
		if ( '' !== $code ) {
			return array( $code, 'panel' );
		}

		foreach ( self::PWS_TRACKING_KEYS as $key ) {
			$value = (string) $order->get_meta( $key );
			if ( '' !== $value ) {
				return array( $value, 'pws' );
			}
		}

		return array( '', '' );
	}

	/**
	 * Saves a tracking code and optionally tells the customer (as a customer
	 * note, which WooCommerce emails).
	 *
	 * @param \WC_Order $order  Order.
	 * @param string    $code   Code.
	 * @param bool      $notify Whether to send it to the customer.
	 */
	public static function save_tracking( \WC_Order $order, string $code, bool $notify ): void {
		$order->update_meta_data( self::TRACKING_META, $code );
		$order->save();

		if ( $notify && '' !== $code ) {
			/* translators: %s: parcel tracking code. */
			$order->add_order_note( sprintf( __( 'Your parcel is on its way. Tracking code: %s', 'lenz-plus' ), $code ), 1, true );
		}
	}
}
