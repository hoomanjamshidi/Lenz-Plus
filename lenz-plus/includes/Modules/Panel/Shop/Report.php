<?php
/**
 * Sales reports of the studio panel, built from WooCommerce orders.
 *
 * Orders are read through WooCommerce's CRUD API (works with both order
 * storages and without WooCommerce Analytics), in pages of 200, and summed in
 * PHP. Only paid-like statuses count (completed, processing, on-hold; filter
 * `lenz_plus_panel_report_statuses`). A report is cached for ten minutes
 * under a key that also holds a counter bumped whenever an order is created,
 * changes status or is refunded, so new sales show at once. Ported from
 * Daftar's Shop\Report.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Shop;

use LenzPlus\Modules\Panel\Integrations\Shipping;

defined( 'ABSPATH' ) || exit;

/**
 * Report builder.
 */
final class Report {

	/** Most orders summed for one report; larger ranges are flagged as partial. */
	private const MAX_ORDERS = 20000;

	/** Most customers checked for an earlier order (new vs returning). */
	private const MAX_CUSTOMER_CHECKS = 300;

	private const BATCH = 200;

	private const CACHE_TTL = 600;

	public const VERSION_OPTION = 'lenz_plus_panel_report_version';

	/** Order statuses counted as sales (without `wc-`). */
	private const STATUSES = array( 'completed', 'processing', 'on-hold' );

	/**
	 * Hooks that make cached reports stale.
	 */
	public static function register(): void {
		foreach ( array( 'woocommerce_new_order', 'woocommerce_order_status_changed', 'woocommerce_order_refunded', 'woocommerce_delete_order', 'woocommerce_trash_order' ) as $hook ) {
			add_action( $hook, array( self::class, 'bump' ) );
		}
	}

	/**
	 * Marks every cached report stale. One checkout fires several of the
	 * hooks above, so the option is written once per request; it is only read
	 * by panel screens, so it is not autoloaded (an autoloaded write rewrites
	 * the whole cached options list).
	 */
	public static function bump(): void {
		static $bumped = false;
		if ( $bumped ) {
			return;
		}

		$bumped = true;
		update_option( self::VERSION_OPTION, (int) get_option( self::VERSION_OPTION, 0 ) + 1, false );
	}

	/**
	 * Resolves a preset ('7d', '30d', '90d', 'month', 'year', 'today') or a custom range
	 * (Y-m-d dates in the site's time zone) into start/end and the previous period.
	 *
	 * @param string $preset Preset key or 'custom'.
	 * @param string $from   Custom start date.
	 * @param string $to     Custom end date.
	 */
	public static function range( string $preset, string $from = '', string $to = '' ): array {
		$zone  = wp_timezone();
		$today = new \DateTimeImmutable( 'today', $zone );

		switch ( $preset ) {
			case 'today':
				$start = $today;
				$end   = $today;
				break;
			case 'yesterday':
				$start = $today->modify( '-1 day' );
				$end   = $start;
				break;
			case '7d':
				$start = $today->modify( '-6 days' );
				$end   = $today;
				break;
			case '90d':
				$start = $today->modify( '-89 days' );
				$end   = $today;
				break;
			case 'month':
				$start = $today->modify( 'first day of this month' );
				$end   = $today;
				break;
			case 'year':
				$start = $today->modify( 'first day of january this year' );
				$end   = $today;
				break;
			case 'custom':
				$start = self::date( $from, $zone );
				$end   = self::date( $to, $zone );
				if ( $start && $end ) {
					if ( $start > $end ) {
						list( $start, $end ) = array( $end, $start );
					}
					break;
				}
				// Bad custom dates fall back to the last 30 days.
				// fall through.
			default:
				$start = $today->modify( '-29 days' );
				$end   = $today;
		}

		$days       = (int) $start->diff( $end )->days + 1;
		$prev_end   = $start->modify( '-1 day' );
		$prev_start = $prev_end->modify( '-' . ( $days - 1 ) . ' days' );

		return array(
			'start'      => $start,
			'end'        => $end,
			'prev_start' => $prev_start,
			'prev_end'   => $prev_end,
			'days'       => $days,
			'interval'   => $days <= 45 ? 'day' : ( $days <= 200 ? 'week' : 'month' ),
		);
	}

	/**
	 * A Y-m-d date at midnight in the zone, or null.
	 *
	 * @param string        $value Date.
	 * @param \DateTimeZone $zone  Zone.
	 */
	private static function date( string $value, \DateTimeZone $zone ): ?\DateTimeImmutable {
		$date = \DateTimeImmutable::createFromFormat( '!Y-m-d', $value, $zone );

		return $date ? $date : null;
	}

	/**
	 * The full report for a range (cached).
	 *
	 * @param \DateTimeImmutable $start    First day.
	 * @param \DateTimeImmutable $end      Last day.
	 * @param string             $interval day, week or month.
	 * @param bool               $details  Also products, customers, coupons… (false = totals and series only).
	 */
	public static function build( \DateTimeImmutable $start, \DateTimeImmutable $end, string $interval = 'day', bool $details = true ): array {
		$statuses = array_values( array_filter( array_map( 'sanitize_key', (array) apply_filters( 'lenz_plus_panel_report_statuses', self::STATUSES ) ) ) );
		$key      = 'lenz_plus_rep_' . md5( (string) wp_json_encode( array( $start->format( 'Y-m-d' ), $end->format( 'Y-m-d' ), $interval, $details, $statuses, LENZ_PLUS_VERSION ) ) );

		return Order_Cache::remember(
			$key,
			self::CACHE_TTL,
			static function () use ( $start, $end, $interval, $statuses, $details ) {
				return ( new self( $start, $end, $interval, $statuses ) )->run( $details );
			}
		);
	}

	/**
	 * First day.
	 *
	 * @var \DateTimeImmutable
	 */
	private $start;

	/**
	 * Last day.
	 *
	 * @var \DateTimeImmutable
	 */
	private $end;

	/**
	 * Bucket size.
	 *
	 * @var string
	 */
	private $interval;

	/**
	 * Order statuses counted as sales.
	 *
	 * @var string[]
	 */
	private $statuses;

	/**
	 * Running sums.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Category names by term ID (product → categories lookups are cached per product).
	 *
	 * @var array
	 */
	private $product_terms = array();

	/**
	 * Constructor.
	 *
	 * @param \DateTimeImmutable $start    First day.
	 * @param \DateTimeImmutable $end      Last day.
	 * @param string             $interval Bucket size.
	 * @param string[]           $statuses Statuses counted as sales.
	 */
	private function __construct( \DateTimeImmutable $start, \DateTimeImmutable $end, string $interval, array $statuses ) {
		$this->start    = $start;
		$this->end      = $end;
		$this->interval = $interval;
		$this->statuses = $statuses;
	}

	/**
	 * Reads the orders and returns the report.
	 *
	 * @param bool $details Also the breakdowns.
	 */
	private function run( bool $details ): array {
		$this->data = array(
			'totals'     => array_fill_keys( array( 'gross', 'net', 'orders', 'items', 'discount', 'shipping', 'tax', 'refunds', 'fees' ), 0 ),
			'series'     => $this->empty_series(),
			'weekdays'   => array_fill( 0, 7, array( 'orders' => 0, 'gross' => 0 ) ), // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			'hours'      => array_fill( 0, 24, array( 'orders' => 0, 'gross' => 0 ) ), // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			'products'   => array(),
			'categories' => array(),
			'coupons'    => array(),
			'payments'   => array(),
			'shipping'   => array(),
			'regions'    => array(),
			'customers'  => array(),
		);

		$partial = $this->collect( $details );

		$totals              = $this->data['totals'];
		$totals['avg']       = $totals['orders'] ? Money::round( $totals['gross'] / $totals['orders'] ) : 0;
		$totals['customers'] = count( $this->data['customers'] );

		$report = array(
			'totals'   => array_map( array( Money::class, 'round' ), $totals ),
			'series'   => array_values( $this->data['series'] ),
			'interval' => $this->interval,
			'partial'  => $partial,
		);

		if ( $details ) {
			list( $new, $returning ) = $this->new_and_returning();

			$report['totals']['new_customers']       = $new;
			$report['totals']['returning_customers'] = $returning;
			$report['weekdays']                      = $this->data['weekdays'];
			$report['hours']                         = $this->data['hours'];
			$report['products']                      = self::top( $this->data['products'], 'gross', 20 );
			$report['categories']                    = self::top( $this->data['categories'], 'gross', 12 );
			$report['coupons']                       = self::top( $this->data['coupons'], 'discount', 20 );
			$report['payments']                      = self::top( $this->data['payments'], 'gross', 10 );
			$report['shipping']                      = self::top( $this->data['shipping'], 'orders', 10 );
			$report['regions']                       = self::top( $this->data['regions'], 'gross', 12 );
			$report['customers']                     = self::top( $this->data['customers'], 'gross', 10 );
			$report['statuses']                      = $this->status_counts();
		}

		return $report;
	}

	/**
	 * Reads the range's orders into the sums.
	 *
	 * @param bool $details Also the breakdowns.
	 * @return bool Whether the order limit cut the report short.
	 */
	private function collect( bool $details ): bool {
		$range   = $this->start->getTimestamp() . '...' . ( $this->end->modify( '+1 day' )->getTimestamp() - 1 );
		$page    = 1;
		$counted = 0;
		$partial = false;

		do {
			$orders = wc_get_orders(
				array(
					'type'         => 'shop_order',
					'status'       => $this->statuses,
					'date_created' => $range,
					'limit'        => self::BATCH,
					'page'         => $page,
					'orderby'      => 'date',
					'order'        => 'ASC',
				)
			);

			foreach ( $orders as $order ) {
				$this->add_order( $order, $details );
			}

			$batch    = count( $orders );
			$counted += $batch;
			++$page;
			if ( $counted >= self::MAX_ORDERS ) {
				$partial = true;
				break;
			}
		} while ( self::BATCH === $batch );

		return $partial;
	}

	/**
	 * Adds one order to the sums.
	 *
	 * @param \WC_Order $order   Order.
	 * @param bool      $details Also the breakdowns.
	 */
	private function add_order( $order, bool $details ): void {
		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		$gross    = (float) $order->get_total();
		$tax      = (float) $order->get_total_tax();
		$shipping = (float) $order->get_shipping_total();
		$refunds  = (float) $order->get_total_refunded();
		$items    = (int) $order->get_item_count();
		$fees     = 0.0;
		foreach ( $order->get_fees() as $fee ) {
			$fees += (float) $fee->get_total();
		}

		$t              = &$this->data['totals'];
		$t['gross']    += $gross;
		$t['net']      += $gross - $tax - $shipping - $refunds;
		$t['orders']   += 1;
		$t['items']    += $items;
		$t['discount'] += (float) $order->get_discount_total();
		$t['shipping'] += $shipping;
		$t['tax']      += $tax;
		$t['refunds']  += $refunds;
		$t['fees']     += $fees;
		unset( $t );

		$created = $order->get_date_created();
		$local   = $created ? ( new \DateTimeImmutable( '@' . $created->getTimestamp() ) )->setTimezone( wp_timezone() ) : $this->start;
		$bucket  = $this->bucket( $local );
		if ( isset( $this->data['series'][ $bucket ] ) ) {
			$this->data['series'][ $bucket ]['gross']  += $gross;
			$this->data['series'][ $bucket ]['net']    += $gross - $tax - $shipping - $refunds;
			$this->data['series'][ $bucket ]['orders'] += 1;
			$this->data['series'][ $bucket ]['items']  += $items;
		}

		$customer = $this->customer_key( $order );
		self::add( $this->data['customers'], $customer['key'], $customer, array( 'orders' => 1, 'gross' => $gross ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

		if ( ! $details ) {
			return;
		}

		$weekday = (int) $local->format( 'w' );
		$hour    = (int) $local->format( 'G' );
		$this->data['weekdays'][ $weekday ]['orders'] += 1;
		$this->data['weekdays'][ $weekday ]['gross']  += $gross;
		$this->data['hours'][ $hour ]['orders']       += 1;
		$this->data['hours'][ $hour ]['gross']        += $gross;

		$this->add_items( $order );
		$this->add_coupons( $order );
		$this->add_places( $order, $gross );
	}

	/**
	 * Adds an order to the payment, shipping and region sums.
	 *
	 * @param \WC_Order $order Order.
	 * @param float     $gross Order total.
	 */
	private function add_places( \WC_Order $order, float $gross ): void {
		$method = (string) $order->get_payment_method();
		self::add(
			$this->data['payments'],
			'' !== $method ? $method : 'none',
			array( 'label' => '' !== $method ? (string) $order->get_payment_method_title() : __( 'No payment', 'lenz-plus' ) ),
			array( 'orders' => 1, 'gross' => $gross ) // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		);

		$shipping_label = (string) $order->get_shipping_method();
		self::add(
			$this->data['shipping'],
			'' !== $shipping_label ? md5( $shipping_label ) : 'none',
			array( 'label' => '' !== $shipping_label ? $shipping_label : __( 'No shipping', 'lenz-plus' ) ),
			array( 'orders' => 1, 'gross' => $gross ) // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		);

		$region = $this->region( $order );
		self::add( $this->data['regions'], md5( $region ), array( 'label' => $region ), array( 'orders' => 1, 'gross' => $gross ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	}

	/**
	 * Adds an order's line items to the product and category sums.
	 *
	 * @param \WC_Order $order Order.
	 */
	private function add_items( \WC_Order $order ): void {
		foreach ( $order->get_items() as $item ) {
			if ( ! $item instanceof \WC_Order_Item_Product ) {
				continue;
			}

			$product_id = (int) $item->get_product_id();
			$qty        = (int) $item->get_quantity() - absint( $order->get_qty_refunded_for_item( $item->get_id() ) );
			$total      = (float) $item->get_total() - (float) $order->get_total_refunded_for_item( $item->get_id() );

			self::add(
				$this->data['products'],
				(string) $product_id,
				array(
					'id'   => $product_id,
					'name' => $item->get_name(),
				),
				array( 'qty' => $qty, 'gross' => $total ) // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			);

			foreach ( $this->categories_of( $product_id ) as $term_id => $name ) {
				self::add(
					$this->data['categories'],
					(string) $term_id,
					array(
						'id'    => $term_id,
						'label' => $name,
					),
					array( 'qty' => $qty, 'gross' => $total ) // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
				);
			}
		}
	}

	/**
	 * Adds an order's coupons to the coupon sums.
	 *
	 * @param \WC_Order $order Order.
	 */
	private function add_coupons( \WC_Order $order ): void {
		foreach ( $order->get_items( 'coupon' ) as $coupon ) {
			if ( ! $coupon instanceof \WC_Order_Item_Coupon ) {
				continue;
			}

			$code = wc_strtolower( $coupon->get_code() );
			self::add(
				$this->data['coupons'],
				$code,
				array( 'code' => $code ),
				array( 'orders' => 1, 'discount' => (float) $coupon->get_discount() + (float) $coupon->get_discount_tax(), 'gross' => (float) $order->get_total() ) // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			);
		}
	}

	/**
	 * Category names of a product (variations use their parent), cached per product.
	 *
	 * @param int $product_id Product ID.
	 */
	private function categories_of( int $product_id ): array {
		if ( ! isset( $this->product_terms[ $product_id ] ) ) {
			$terms = $product_id ? get_the_terms( $product_id, 'product_cat' ) : array();
			$names = array();
			if ( is_array( $terms ) ) {
				foreach ( $terms as $term ) {
					$names[ $term->term_id ] = $term->name;
				}
			}
			$this->product_terms[ $product_id ] = $names;
		}

		return $this->product_terms[ $product_id ];
	}

	/**
	 * Who placed an order: the account, else the billing email, else «Guest».
	 *
	 * @param \WC_Order $order Order.
	 */
	private function customer_key( \WC_Order $order ): array {
		$id    = (int) $order->get_customer_id();
		$email = wc_strtolower( (string) $order->get_billing_email() );
		$name  = trim( $order->get_formatted_billing_full_name() );

		return array(
			'key'   => $id ? 'u' . $id : ( '' !== $email ? 'e' . md5( $email ) : 'guest' ),
			'id'    => $id,
			'email' => $email,
			'name'  => '' !== $name ? $name : ( '' !== $email ? $email : __( 'Guest', 'lenz-plus' ) ),
		);
	}

	/**
	 * «State – City» of the billing address (state names from WooCommerce's lists).
	 *
	 * @param \WC_Order $order Order.
	 */
	private function region( \WC_Order $order ): string {
		$country = (string) $order->get_billing_country();
		$parts   = array_filter(
			array(
				Shipping::place_name( $country, (string) $order->get_billing_state(), true ),
				Shipping::place_name( $country, (string) $order->get_billing_city(), false ),
			)
		);

		return $parts ? implode( ' – ', $parts ) : __( 'Unknown', 'lenz-plus' );
	}

	/**
	 * Adds numbers to a keyed row, creating it with its labels first.
	 *
	 * @param array  $rows   Rows by key (by reference).
	 * @param string $key    Row key.
	 * @param array  $labels Fields set once.
	 * @param array  $sums   Numbers to add.
	 */
	private static function add( array &$rows, string $key, array $labels, array $sums ): void {
		if ( ! isset( $rows[ $key ] ) ) {
			$rows[ $key ] = array_merge( $labels, array_fill_keys( array_keys( $sums ), 0 ) );
		}
		foreach ( $sums as $field => $value ) {
			$rows[ $key ][ $field ] += $value;
		}
	}

	/**
	 * The biggest rows by a field.
	 *
	 * @param array  $rows  Rows.
	 * @param string $field Sort field.
	 * @param int    $limit Count.
	 */
	private static function top( array $rows, string $field, int $limit ): array {
		usort(
			$rows,
			static function ( $a, $b ) use ( $field ) {
				return $b[ $field ] <=> $a[ $field ];
			}
		);

		return array_slice( $rows, 0, $limit );
	}

	/**
	 * Empty buckets for every day/week/month of the range, so charts show gaps as zero.
	 */
	private function empty_series(): array {
		$series = array();
		$cursor = $this->bucket_start( $this->start );
		while ( $cursor <= $this->end ) {
			$series[ $cursor->format( 'Y-m-d' ) ] = array(
				'date'   => $cursor->format( 'Y-m-d' ),
				'gross'  => 0,
				'net'    => 0,
				'orders' => 0,
				'items'  => 0,
			);
			$cursor                               = $cursor->modify( '+1 ' . $this->interval );
		}

		return $series;
	}

	/**
	 * The bucket key of a date.
	 *
	 * @param \DateTimeImmutable $date Local date.
	 */
	private function bucket( \DateTimeImmutable $date ): string {
		return $this->bucket_start( $date )->format( 'Y-m-d' );
	}

	/**
	 * Start of the bucket holding a date (weeks start on the site's first weekday).
	 *
	 * @param \DateTimeImmutable $date Local date.
	 */
	private function bucket_start( \DateTimeImmutable $date ): \DateTimeImmutable {
		$day = $date->setTime( 0, 0 );
		if ( 'month' === $this->interval ) {
			return $day->modify( 'first day of this month' );
		}
		if ( 'week' === $this->interval ) {
			$back = ( (int) $day->format( 'w' ) - (int) get_option( 'start_of_week', 6 ) + 7 ) % 7;

			return $day->modify( '-' . $back . ' days' );
		}

		return $day;
	}

	/**
	 * Counts this range's customers who also ordered before it (returning) or not (new).
	 * Checks at most MAX_CUSTOMER_CHECKS customers; the rest count as new.
	 *
	 * @return int[] [ new, returning ]
	 */
	private function new_and_returning(): array {
		$before    = '<' . $this->start->getTimestamp();
		$returning = 0;
		$checked   = 0;
		foreach ( $this->data['customers'] as $customer ) {
			if ( $checked >= self::MAX_CUSTOMER_CHECKS || 'guest' === $customer['key'] ) {
				continue;
			}
			++$checked;
			$earlier = wc_get_orders(
				array(
					'type'         => 'shop_order',
					'status'       => $this->statuses,
					'customer'     => $customer['id'] ? $customer['id'] : $customer['email'],
					'date_created' => $before,
					'limit'        => 1,
					'return'       => 'ids',
				)
			);
			if ( $earlier ) {
				++$returning;
			}
		}

		return array( max( 0, count( $this->data['customers'] ) - $returning ), $returning );
	}

	/**
	 * Orders of every status placed in the range.
	 */
	private function status_counts(): array {
		$range  = $this->start->getTimestamp() . '...' . ( $this->end->modify( '+1 day' )->getTimestamp() - 1 );
		$counts = array();
		foreach ( array_keys( wc_get_order_statuses() ) as $status ) {
			$result = wc_get_orders(
				array(
					'type'         => 'shop_order',
					'status'       => array( $status ),
					'date_created' => $range,
					'limit'        => 1,
					'paginate'     => true,
					'return'       => 'ids',
				)
			);
			$total  = is_object( $result ) ? (int) $result->total : 0;
			if ( $total ) {
				$counts[ substr( $status, 3 ) ] = $total;
			}
		}

		return $counts;
	}

	/**
	 * Stock overview: counts and the products that need attention.
	 */
	public static function stock(): array {
		$low_amount = (int) get_option( 'woocommerce_notify_low_stock_amount', 2 );
		$base       = array(
			'status' => 'publish',
			'limit'  => 50,
			'type'   => array( 'simple', 'variation', 'variable', 'grouped', 'external' ),
		);

		$out = wc_get_products( $base + array( 'stock_status' => 'outofstock' ) );
		$low = array_filter(
			wc_get_products( $base + array( 'manage_stock' => true, 'stock_status' => 'instock', 'orderby' => 'meta_value_num', 'meta_key' => '_stock', 'order' => 'ASC', 'limit' => 100 ) ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			static function ( $product ) use ( $low_amount ) {
				return (int) $product->get_stock_quantity() <= $low_amount;
			}
		);

		$row = static function ( $product ) {
			return array(
				'id'     => $product->get_parent_id() ? $product->get_parent_id() : $product->get_id(),
				'name'   => wp_strip_all_tags( $product->get_formatted_name() ),
				'stock'  => $product->managing_stock() ? (int) $product->get_stock_quantity() : null,
				'status' => $product->get_stock_status(),
				'image'  => (string) wp_get_attachment_image_url( (int) $product->get_image_id(), 'thumbnail' ),
			);
		};

		$count = static function ( $status ) {
			$result = wc_get_products(
				array(
					'status'       => 'publish',
					'stock_status' => $status,
					'limit'        => 1,
					'paginate'     => true,
					'return'       => 'ids',
				)
			);

			return is_object( $result ) ? (int) $result->total : 0;
		};

		return array(
			'counts' => array(
				'instock'     => $count( 'instock' ),
				'outofstock'  => $count( 'outofstock' ),
				'onbackorder' => $count( 'onbackorder' ),
				'low'         => count( $low ),
			),
			'low'    => array_values( array_map( $row, $low ) ),
			'out'    => array_map( $row, $out ),
		);
	}
}
