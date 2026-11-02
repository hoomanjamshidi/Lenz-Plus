<?php
/**
 * Course students screen: every course with its number of students, and a
 * course page listing who bought it (name, contact, order, date), the
 * seats left, the money it brought in, the waiting list of upcoming courses
 * and a CSV of the students.
 *
 * A student is the buyer of a paid order (processing, completed, on-hold)
 * that holds the course product. Order IDs are found through WooCommerce's
 * order item tables, which both order storages (HPOS and posts) use, and
 * the orders are then read through WooCommerce's CRUD API. Counts are cached
 * until the orders change (the report version is part of the key).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Modules\Builder\Course_Data;
use LenzPlus\Modules\Builder\Newsletter;
use LenzPlus\Modules\Panel\Activity\Log;
use LenzPlus\Modules\Panel\Shop\Csv;
use LenzPlus\Modules\Panel\Shop\Money;
use LenzPlus\Modules\Panel\Shop\Order_Text;
use LenzPlus\Modules\Panel\Shop\Report;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * Courses with their students.
 */
final class Students extends Screen {

	/** Order statuses whose buyers count as students. */
	private const PAID = array( 'processing', 'completed', 'on-hold' );

	/** Most orders read for one course. */
	private const MAX_ORDERS = 2000;

	/** Screen id. */
	public function id(): string {
		return 'students';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Course students', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'users';
	}

	/** Seeing buyers needs the orders capability. */
	public function capability(): string {
		return 'edit_shop_orders';
	}

	/** Settings switch. */
	public function section(): string {
		return 'students';
	}

	/** Needs WooCommerce. */
	public function is_available(): bool {
		return Course_Data::has_woo();
	}

	/** The course whose page is open, or 0. */
	private static function course_id(): int {
		$id = self::arg_int( 'course' );

		return Course_Data::is_course( $id ) ? $id : 0;
	}

	/** The course's name on its page. */
	public function page_title(): string {
		$id = self::course_id();

		return $id ? html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ) : $this->title();
	}

	/** Back to the courses. */
	public function back_url(): string {
		return self::arg_int( 'course' ) ? $this->url() : '';
	}

	/**
	 * IDs of the orders holding a product, newest first.
	 *
	 * @param int $product_id Product ID.
	 * @return int[]
	 */
	private static function order_ids( int $product_id ): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- WooCommerce's item tables have no query API by product; cached by the callers.
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT items.order_id FROM {$wpdb->prefix}woocommerce_order_items AS items
				INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS meta ON meta.order_item_id = items.order_item_id
				WHERE items.order_item_type = 'line_item' AND meta.meta_key = '_product_id' AND meta.meta_value = %d
				ORDER BY items.order_id DESC LIMIT %d",
				$product_id,
				self::MAX_ORDERS
			)
		);

		return array_map( 'intval', (array) $ids );
	}

	/**
	 * Paid orders of a course, newest first.
	 *
	 * @param int $product_id Course product ID.
	 * @return \WC_Order[]
	 */
	private static function orders( int $product_id ): array {
		$ids = self::order_ids( $product_id );
		if ( ! $ids ) {
			return array();
		}

		return wc_get_orders(
			array(
				'type'     => 'shop_order',
				'status'   => self::PAID,
				'post__in' => $ids,
				'limit'    => -1,
				'orderby'  => 'date',
				'order'    => 'DESC',
			)
		);
	}

	/**
	 * Number of students of a course (cached until orders change).
	 *
	 * @param int $product_id Course product ID.
	 */
	private static function count( int $product_id ): int {
		$key   = 'lenz_plus_students_' . $product_id . '_' . (int) get_option( Report::VERSION_OPTION, 0 );
		$count = get_transient( $key );
		if ( false === $count ) {
			$count = count( self::orders( $product_id ) );
			set_transient( $key, $count, DAY_IN_SECONDS );
		}

		return (int) $count;
	}

	/**
	 * People on a course's waiting list (Newsletter sign-ups with the course as topic).
	 *
	 * @param int $product_id Course product ID.
	 * @return \WP_Post[]
	 */
	private static function waitlist( int $product_id ): array {
		if ( ! post_type_exists( Newsletter::POST_TYPE ) ) {
			return array();
		}

		return get_posts(
			array(
				'post_type'      => Newsletter::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => 100,
				'no_found_rows'  => true,
				'meta_key'       => Newsletter::META_TOPIC, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one course's waiting list.
				'meta_value'     => html_entity_decode( get_the_title( $product_id ), ENT_QUOTES, 'UTF-8' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
	}

	/** Sends a course's students as CSV. */
	public function download(): bool {
		$id = self::course_id();
		if ( ! $id || ! $this->is_download() ) {
			return false;
		}

		$rows = array();
		foreach ( self::orders( $id ) as $order ) {
			$created = $order->get_date_created();
			$rows[]  = array(
				Order_Text::customer_name( $order ),
				(string) $order->get_billing_phone(),
				(string) $order->get_billing_email(),
				$order->get_order_number(),
				$created ? wp_date( 'Y-m-d', $created->getTimestamp() ) : '',
				wc_get_order_status_name( $order->get_status() ),
			);
		}

		Log::add( 'export', 'students', $id, get_the_title( $id ) );
		Csv::send( 'students-' . $id, array( __( 'Name', 'lenz-plus' ), __( 'Phone', 'lenz-plus' ), __( 'Email', 'lenz-plus' ), __( 'Order', 'lenz-plus' ), __( 'Date', 'lenz-plus' ), __( 'Status', 'lenz-plus' ) ), $rows );

		return true;
	}

	/** Prints the courses or a course's students. */
	public function render(): void {
		if ( ! self::arg_int( 'course' ) ) {
			$this->render_courses();
			return;
		}

		$id = self::course_id();
		if ( $id ) {
			$this->render_course( $id );
		} else {
			Ui::empty_state( __( 'This item does not exist.', 'lenz-plus' ) );
		}
	}

	/** Every course with its students and seats. */
	private function render_courses(): void {
		$rows = array();
		foreach ( Course_Data::course_ids() as $id ) {
			$count = self::count( $id );
			$left  = Course_Data::seats_left( $id );
			$meta  = array( Course_Data::format_label( Course_Data::details( $id ) ) );
			if ( null !== $left ) {
				/* translators: %s: seats left. */
				$meta[] = sprintf( __( '%s seats left', 'lenz-plus' ), Ui::number( $left ) );
			}

			$rows[] = array(
				'url'   => $this->url( array( 'course' => $id ) ),
				'title' => html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ),
				'meta'  => implode( ' · ', array_filter( $meta ) ),
				'thumb' => (string) get_the_post_thumbnail_url( $id, 'thumbnail' ),
				'icon'  => 'graduation',
				/* translators: %s: number of students. */
				'aside' => sprintf( _n( '%s student', '%s students', $count, 'lenz-plus' ), Ui::number( $count ) ),
			);
		}

		Ui::rows( $rows, __( 'No published courses yet.', 'lenz-plus' ) );
	}

	/**
	 * A course's figures, students and waiting list.
	 *
	 * @param int $id Course product ID.
	 */
	private function render_course( int $id ): void {
		$orders  = self::orders( $id );
		$left    = Course_Data::seats_left( $id );
		$waiting = self::waitlist( $id );
		$income  = 0.0;
		foreach ( $orders as $order ) {
			foreach ( $order->get_items() as $item ) {
				if ( (int) $item->get_product_id() === $id ) {
					$income += (float) $item->get_total() - (float) $order->get_total_refunded_for_item( $item->get_id() );
				}
			}
		}

		$tiles = array(
			array( __( 'Students', 'lenz-plus' ), Ui::number( count( $orders ) ), 'users' ),
			array( __( 'Income', 'lenz-plus' ), Money::text( $income ), 'chart' ),
		);
		if ( null !== $left ) {
			$tiles[] = array( __( 'Seats left', 'lenz-plus' ), Ui::number( $left ), 'list' );
		}
		if ( $waiting ) {
			$tiles[] = array( __( 'Waiting list', 'lenz-plus' ), Ui::number( count( $waiting ) ), 'clock' );
		}
		Ui::stats( $tiles );

		$orders_screen = $this->module->app()->screen( 'orders' );
		$labels        = Order_Text::statuses();
		$rows          = array();
		$emails        = array();
		foreach ( $orders as $order ) {
			$created = $order->get_date_created();
			$contact = '' !== (string) $order->get_billing_phone() ? (string) $order->get_billing_phone() : (string) $order->get_billing_email();
			$rows[]  = array(
				'url'   => $orders_screen ? $orders_screen->url( array( 'view' => $order->get_id() ) ) : '',
				'title' => Order_Text::customer_name( $order ),
				'meta'  => trim( $contact . ' · ' . ( $created ? Ui::date( $created->getTimestamp() ) : '' ), ' ·' ),
				'icon'  => 'user-circle',
				'pill'  => 'completed' === $order->get_status() ? '' : Ui::pill( $order->get_status(), $labels[ $order->get_status() ] ?? '' ),
				/* translators: %s: order number. */
				'aside' => sprintf( __( '#%s', 'lenz-plus' ), Ui::number( (int) $order->get_order_number() ) ),
			);
			if ( is_email( $order->get_billing_email() ) ) {
				$emails[] = $order->get_billing_email();
			}
		}
		?>
		<section class="lzp-section">
			<div class="lzp-section__head">
				<h2 class="lzp-section__title"><?php esc_html_e( 'Students', 'lenz-plus' ); ?></h2>
				<?php if ( $rows ) : ?>
					<a href="<?php echo esc_url( $this->download_url( array( 'course' => $id ) ) ); ?>" data-lzp-noprefetch><?php esc_html_e( 'CSV', 'lenz-plus' ); ?></a>
				<?php endif; ?>
			</div>
			<?php Ui::rows( $rows, __( 'Nobody has bought this course yet.', 'lenz-plus' ) ); ?>
			<?php if ( $emails ) : ?>
				<p><a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( 'mailto:?bcc=' . rawurlencode( implode( ',', array_unique( $emails ) ) ) ); ?>"><?php Ui::the_icon( 'mail' ); ?><span><?php esc_html_e( 'Email every student', 'lenz-plus' ); ?></span></a></p>
			<?php endif; ?>
		</section>
		<?php if ( $waiting ) : ?>
			<section class="lzp-card">
				<h2 class="lzp-card__title"><?php esc_html_e( 'Waiting list', 'lenz-plus' ); ?></h2>
				<p class="lzp-card__note"><?php esc_html_e( 'People who asked to hear when registration opens.', 'lenz-plus' ); ?></p>
				<?php
				$lines = array();
				foreach ( $waiting as $post ) {
					$lines[] = array( $post->post_title, Ui::date( (int) get_post_time( 'U', true, $post ) ) );
				}
				Ui::lines( $lines );
				?>
			</section>
		<?php endif; ?>
		<?php
	}
}
