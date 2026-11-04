<?php
/**
 * Guest customers: people who ordered without an account, grouped by billing email
 * (or phone when an order has no email), plus private notes about any customer.
 *
 * Guests are collected from orders through WooCommerce's CRUD API in batches and
 * cached until the order data changes (Order_Cache).
 * Ported from Daftar's Shop\Guests.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Shop;

use LenzPlus\Core\Persian;

defined( 'ABSPATH' ) || exit;

/**
 * Guest aggregation and customer notes.
 */
final class Guests {

	/** Most guest orders read for the list. */
	private const MAX_ORDERS = 20000;

	/** Orders per batch. */
	private const BATCH = 200;

	/** User meta with a registered customer's notes. */
	private const USER_NOTES = '_lzp_customer_notes';

	/** Option prefix for a guest's notes (not autoloaded). */
	private const GUEST_NOTES = 'lenz_plus_guest_notes_';

	/**
	 * The key of a guest: md5 of the lower-case email, else of the phone digits.
	 *
	 * @param string $email Email.
	 * @param string $phone Phone.
	 */
	public static function key( string $email, string $phone ): string {
		$email = strtolower( trim( $email ) );
		$phone = preg_replace( '/\D+/', '', Persian::latin_digits( $phone ) );

		return '' !== $email ? md5( $email ) : ( '' !== $phone ? md5( 'tel:' . $phone ) : '' );
	}

	/**
	 * Every guest, newest order first.
	 */
	public static function all(): array {
		return (array) Order_Cache::remember( 'lenz_plus_guests', HOUR_IN_SECONDS, array( self::class, 'collect' ) );
	}

	/**
	 * Reads the guest orders into one row per guest.
	 */
	public static function collect(): array {
		$paid   = wc_get_is_paid_statuses();
		$guests = array();
		for ( $page = 1; $page <= self::MAX_ORDERS / self::BATCH; $page++ ) {
			$orders = wc_get_orders(
				array(
					'type'        => 'shop_order',
					'customer_id' => 0,
					'limit'       => self::BATCH,
					'page'        => $page,
					'orderby'     => 'date',
					'order'       => 'DESC',
				)
			);
			foreach ( $orders as $order ) {
				$key = self::key( (string) $order->get_billing_email(), (string) $order->get_billing_phone() );
				if ( '' === $key ) {
					continue;
				}
				$date = $order->get_date_created();
				$time = $date ? $date->getTimestamp() : 0;
				if ( ! isset( $guests[ $key ] ) ) {
					$guests[ $key ] = array(
						'key'        => $key,
						'name'       => Order_Text::customer_name( $order ),
						'email'      => (string) $order->get_billing_email(),
						'phone'      => (string) $order->get_billing_phone(),
						'city'       => (string) $order->get_billing_city(),
						'orders'     => 0,
						'spent'      => 0.0,
						'last_order' => $time,
						'first'      => 0,
					);
				}
				++$guests[ $key ]['orders'];
				if ( $order->has_status( $paid ) ) {
					$guests[ $key ]['spent'] += (float) $order->get_total() - (float) $order->get_total_refunded();
				}
				if ( $time ) {
					$guests[ $key ]['first'] = $time;
				}
			}
			if ( count( $orders ) < self::BATCH ) {
				break;
			}
		}

		return array_values( $guests );
	}

	/**
	 * One guest by key.
	 *
	 * @param string $key Guest key.
	 */
	public static function find( string $key ): ?array {
		foreach ( self::all() as $guest ) {
			if ( $guest['key'] === $key ) {
				return $guest;
			}
		}

		return null;
	}

	/**
	 * A guest's orders (newest first).
	 *
	 * @param array $guest Guest row.
	 * @param int   $limit Most orders.
	 */
	public static function orders( array $guest, int $limit = 50 ): array {
		$args = array(
			'type'        => 'shop_order',
			'customer_id' => 0,
			'limit'       => $limit,
			'orderby'     => 'date',
			'order'       => 'DESC',
		);
		if ( '' !== $guest['email'] ) {
			$args['billing_email'] = $guest['email'];
		} else {
			$args['billing_phone'] = $guest['phone'];
		}

		return wc_get_orders( $args );
	}

	/**
	 * Notes about a customer.
	 *
	 * @param int    $user_id Registered customer (0 for a guest).
	 * @param string $key     Guest key.
	 */
	public static function notes( int $user_id, string $key = '' ): array {
		$notes = $user_id ? get_user_meta( $user_id, self::USER_NOTES, true ) : get_option( self::GUEST_NOTES . $key, array() );

		return is_array( $notes ) ? array_values( $notes ) : array();
	}

	/**
	 * Adds a note (newest first) and returns every note.
	 *
	 * @param int    $user_id Registered customer (0 for a guest).
	 * @param string $key     Guest key.
	 * @param string $text    Note.
	 */
	public static function add_note( int $user_id, string $key, string $text ): array {
		$user  = wp_get_current_user();
		$notes = self::notes( $user_id, $key );
		array_unshift(
			$notes,
			array(
				'id'     => wp_generate_uuid4(),
				'text'   => $text,
				'date'   => time(),
				'author' => $user->display_name,
			)
		);

		self::save_notes( $user_id, $key, array_slice( $notes, 0, 200 ) );

		return $notes;
	}

	/**
	 * Deletes a note and returns the rest.
	 *
	 * @param int    $user_id Registered customer (0 for a guest).
	 * @param string $key     Guest key.
	 * @param string $id      Note ID.
	 */
	public static function delete_note( int $user_id, string $key, string $id ): array {
		$notes = array_values(
			array_filter(
				self::notes( $user_id, $key ),
				static function ( $note ) use ( $id ) {
					return $note['id'] !== $id;
				}
			)
		);
		self::save_notes( $user_id, $key, $notes );

		return $notes;
	}

	/**
	 * Stores the notes.
	 *
	 * @param int    $user_id Registered customer (0 for a guest).
	 * @param string $key     Guest key.
	 * @param array  $notes   Notes.
	 */
	private static function save_notes( int $user_id, string $key, array $notes ): void {
		if ( $user_id ) {
			update_user_meta( $user_id, self::USER_NOTES, $notes );
		} elseif ( $notes ) {
			update_option( self::GUEST_NOTES . $key, $notes, false );
		} else {
			delete_option( self::GUEST_NOTES . $key );
		}
	}
}
