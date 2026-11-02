<?php
/**
 * Records changes made anywhere (panel or wp-admin) into the activity log:
 * order statuses and refunds, product and coupon fields, publish/trash of
 * articles, pages, projects, products and coupons, comment moderation and
 * booking statuses.
 *
 * Product and coupon edits are compared before WooCommerce saves them: until
 * apply_changes() runs, WC_Data::get_data() still holds the old values and
 * get_changes() the new ones, so the log stores both sides. Stock changes
 * made by orders (wc_update_product_stock) bypass object saves and are not
 * logged as edits. Ported from Daftar's Activity\Watcher.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Activity;

use LenzPlus\Modules\Builder\Booking_Requests;

defined( 'ABSPATH' ) || exit;

/**
 * Hooks that feed the log.
 */
final class Watcher {

	/** Product fields worth recording. */
	private const PRODUCT_FIELDS = array( 'name', 'status', 'regular_price', 'sale_price', 'stock_quantity', 'stock_status', 'manage_stock', 'sku', 'featured', 'catalog_visibility', 'date_on_sale_from', 'date_on_sale_to', 'backorders' );

	/** Coupon fields worth recording. */
	private const COUPON_FIELDS = array( 'code', 'amount', 'discount_type', 'date_expires', 'usage_limit', 'usage_limit_per_user', 'minimum_amount', 'maximum_amount', 'free_shipping', 'individual_use' );

	/** Post types whose status changes are logged → object type in the log. */
	private const POST_TYPES = array(
		'post'        => 'post',
		'page'        => 'page',
		'portfolio'   => 'portfolio',
		'product'     => 'product',
		'shop_coupon' => 'coupon',
	);

	/** Hooks everything. */
	public static function register(): void {
		add_action( 'woocommerce_order_status_changed', array( self::class, 'order_status' ), 10, 4 );
		add_action( 'woocommerce_order_refunded', array( self::class, 'order_refunded' ), 10, 2 );
		add_action( 'woocommerce_trash_order', array( self::class, 'order_trashed' ) );
		add_action( 'woocommerce_before_product_object_save', array( self::class, 'product_saving' ) );
		add_action( 'woocommerce_before_product_variation_object_save', array( self::class, 'product_saving' ) );
		add_action( 'woocommerce_before_coupon_object_save', array( self::class, 'coupon_saving' ) );
		add_action( 'transition_post_status', array( self::class, 'post_status' ), 10, 3 );
		add_action( 'transition_comment_status', array( self::class, 'comment_status' ), 10, 3 );
		add_action( 'updated_post_meta', array( self::class, 'booking_status' ), 10, 4 );
	}

	/**
	 * Order status changes.
	 *
	 * @param int       $id    Order ID.
	 * @param string    $from  Old status.
	 * @param string    $to    New status.
	 * @param \WC_Order $order Order.
	 */
	public static function order_status( $id, $from, $to, $order ): void {
		Log::add( 'status', 'order', (int) $id, '#' . $order->get_order_number(), array( self::change( 'status', $from, $to ) ) );
	}

	/**
	 * Refunds.
	 *
	 * @param int $order_id  Order ID.
	 * @param int $refund_id Refund ID.
	 */
	public static function order_refunded( $order_id, $refund_id ): void {
		$order  = wc_get_order( $order_id );
		$refund = wc_get_order( $refund_id );
		if ( $order && $refund ) {
			Log::add( 'refunded', 'order', (int) $order_id, '#' . $order->get_order_number(), array( self::change( 'amount', '', (string) $refund->get_amount() ), self::change( 'reason', '', $refund->get_reason() ) ) );
		}
	}

	/**
	 * Orders moved to the trash (HPOS).
	 *
	 * @param int $id Order ID.
	 */
	public static function order_trashed( $id ): void {
		Log::add( 'trashed', 'order', (int) $id, '#' . $id );
	}

	/**
	 * Product and variation field edits (old → new).
	 *
	 * @param \WC_Product $product Product being saved.
	 */
	public static function product_saving( $product ): void {
		if ( ! $product->get_id() ) {
			return;
		}

		$changes = self::diff( $product->get_data(), $product->get_changes(), self::PRODUCT_FIELDS );
		if ( $changes ) {
			$label = $product->is_type( 'variation' ) ? wp_strip_all_tags( $product->get_formatted_name() ) : $product->get_name();
			Log::add( 'updated', 'product', $product->get_parent_id() ? $product->get_parent_id() : $product->get_id(), $label, $changes );
		}
	}

	/**
	 * Coupon field edits.
	 *
	 * @param \WC_Coupon $coupon Coupon being saved.
	 */
	public static function coupon_saving( $coupon ): void {
		if ( ! $coupon->get_id() ) {
			return;
		}

		$changes = self::diff( $coupon->get_data(), $coupon->get_changes(), self::COUPON_FIELDS );
		if ( $changes ) {
			Log::add( 'updated', 'coupon', $coupon->get_id(), $coupon->get_code(), $changes );
		}
	}

	/**
	 * Created, published, unpublished, trashed and restored content.
	 *
	 * @param string   $new_status New status.
	 * @param string   $old_status Old status.
	 * @param \WP_Post $post       Post.
	 */
	public static function post_status( $new_status, $old_status, $post ): void {
		if ( $new_status === $old_status || ! isset( self::POST_TYPES[ $post->post_type ] ) || 'auto-draft' === $new_status || wp_is_post_revision( $post ) ) {
			return;
		}

		$action = 'status';
		if ( 'new' === $old_status || 'auto-draft' === $old_status ) {
			$action = 'created';
		} elseif ( 'trash' === $new_status ) {
			$action = 'trashed';
		} elseif ( 'trash' === $old_status ) {
			$action = 'restored';
		}

		Log::add( $action, self::POST_TYPES[ $post->post_type ], (int) $post->ID, $post->post_title, array( self::change( 'status', $old_status, $new_status ) ) );
	}

	/**
	 * Comment moderation.
	 *
	 * @param string      $new_status New status.
	 * @param string      $old_status Old status.
	 * @param \WP_Comment $comment    Comment.
	 */
	public static function comment_status( $new_status, $old_status, $comment ): void {
		Log::add( 'status', 'comment', (int) $comment->comment_ID, $comment->comment_author . ' · ' . get_the_title( (int) $comment->comment_post_ID ), array( self::change( 'status', $old_status, $new_status ) ) );
	}

	/**
	 * Booking status changes (the Builder's project bookings).
	 *
	 * @param int    $meta_id  Meta ID.
	 * @param int    $post_id  Post ID.
	 * @param string $meta_key Meta key.
	 * @param mixed  $value    New value.
	 */
	public static function booking_status( $meta_id, $post_id, $meta_key, $value ): void {
		unset( $meta_id );
		if ( Booking_Requests::META_STATUS !== $meta_key || Booking_Requests::POST_TYPE !== get_post_type( (int) $post_id ) ) {
			return;
		}

		Log::add( 'status', 'booking', (int) $post_id, Booking_Requests::code_for( (int) $post_id ), array( self::change( 'status', '', (string) $value ) ) );
	}

	/**
	 * Changed fields between the stored data and the pending changes.
	 *
	 * @param array    $data    Stored values.
	 * @param array    $changes Pending values.
	 * @param string[] $fields  Fields to compare.
	 */
	private static function diff( array $data, array $changes, array $fields ): array {
		$out = array();
		foreach ( $fields as $field ) {
			if ( ! array_key_exists( $field, $changes ) ) {
				continue;
			}
			$from = self::scalar( $data[ $field ] ?? '' );
			$to   = self::scalar( $changes[ $field ] );
			if ( $from !== $to ) {
				$out[] = self::change( $field, $from, $to );
			}
		}

		return $out;
	}

	/**
	 * A value as text (dates as Y-m-d, booleans as 1/0).
	 *
	 * @param mixed $value Value.
	 */
	private static function scalar( $value ): string {
		if ( $value instanceof \WC_DateTime ) {
			return $value->date( 'Y-m-d' );
		}
		if ( is_bool( $value ) ) {
			return $value ? '1' : '0';
		}

		return is_scalar( $value ) || null === $value ? (string) $value : (string) wp_json_encode( $value );
	}

	/**
	 * One change entry.
	 *
	 * @param string $field Field.
	 * @param mixed  $from  Old value.
	 * @param mixed  $to    New value.
	 */
	public static function change( string $field, $from, $to ): array {
		return array(
			'field' => $field,
			'from'  => (string) $from,
			'to'    => (string) $to,
		);
	}
}
