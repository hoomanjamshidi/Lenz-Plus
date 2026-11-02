<?php
/**
 * Orders screen: WooCommerce orders with status chips and search, and an
 * order page with items, totals, the customer's contact links, status change,
 * notes, refunds, the parcel tracking code and the invoice email.
 *
 * Orders are read and written through WooCommerce's order objects
 * (wc_get_orders(), update_status(), add_order_note()), so it works with both
 * order storages (HPOS and posts) and WooCommerce's own emails and stock
 * changes run as they do in wp-admin.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Core\Persian;
use LenzPlus\Modules\Panel\Activity\Log;
use LenzPlus\Modules\Panel\Activity\Watcher;
use LenzPlus\Modules\Panel\Integrations\Shipping;
use LenzPlus\Modules\Panel\Shop\Money;
use LenzPlus\Modules\Panel\Shop\Order_Text;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * Orders list and order page.
 */
final class Orders extends Screen {

	/** Statuses waiting for the studio, counted on the menu badge. */
	public const WAITING = array( 'processing', 'on-hold' );

	/** Screen id. */
	public function id(): string {
		return 'orders';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Orders', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'receipt';
	}

	/** WooCommerce's order capability. */
	public function capability(): string {
		return 'edit_shop_orders';
	}

	/** Settings switch. */
	public function section(): string {
		return 'orders';
	}

	/** Needs WooCommerce. */
	public function is_available(): bool {
		return function_exists( 'wc_get_orders' );
	}

	/** Orders waiting for the studio. */
	public function badge(): int {
		return self::count( self::WAITING );
	}

	/**
	 * Number of orders in some statuses.
	 *
	 * @param string[] $statuses Statuses without `wc-`.
	 */
	public static function count( array $statuses ): int {
		if ( ! function_exists( 'wc_get_orders' ) ) {
			return 0;
		}

		$result = wc_get_orders(
			array(
				'status'   => $statuses,
				'limit'    => 1,
				'paginate' => true,
				'return'   => 'ids',
			)
		);

		return is_object( $result ) ? (int) $result->total : 0;
	}

	/** The order being viewed, or null. */
	private function order() {
		$id    = self::arg_int( 'view' );
		$order = $id ? wc_get_order( $id ) : null;

		return $order instanceof \WC_Order ? $order : null;
	}

	/** "Order #123" on the order page. */
	public function page_title(): string {
		$order = self::arg_int( 'view' ) ? $this->order() : null;
		if ( ! $order ) {
			return $this->title();
		}

		/* translators: %s: order number. */
		return sprintf( __( 'Order #%s', 'lenz-plus' ), Ui::number( (int) $order->get_order_number() ) );
	}

	/** Back to the list from an order. */
	public function back_url(): string {
		return self::arg_int( 'view' ) ? $this->url() : '';
	}

	/** Prints the list or one order. */
	public function render(): void {
		if ( self::arg_int( 'view' ) ) {
			$order = $this->order();
			if ( $order ) {
				$this->render_order( $order );
			} else {
				Ui::empty_state( __( 'This order does not exist.', 'lenz-plus' ) );
			}
			return;
		}

		$this->render_list();
	}

	/** The list with status chips and search. */
	private function render_list(): void {
		$statuses = self::statuses();
		$status   = self::arg( 'status' );
		$status   = isset( $statuses[ $status ] ) ? $status : '';
		$search   = self::search_term();

		$args = array(
			'limit'    => $this->per_page(),
			'paged'    => self::page_number(),
			'paginate' => true,
			'orderby'  => 'date',
			'order'    => 'DESC',
			'type'     => 'shop_order',
		);
		if ( '' !== $status ) {
			$args['status'] = array( $status );
		}
		$ids = '' !== $search ? self::search_ids( $search ) : array();
		if ( '' !== $search && ! $ids ) {
			$orders = array();
			$pages  = 0;
		} else {
			if ( $ids ) {
				$args['post__in'] = $ids;
			}
			$result = wc_get_orders( $args );
			$orders = is_object( $result ) ? $result->orders : array();
			$pages  = is_object( $result ) ? (int) $result->max_num_pages : 0;
		}

		Ui::search( $this->url(), $search, __( 'Order number, name, email or mobile', 'lenz-plus' ), ( '' !== $status ? array( 'status' => $status ) : array() ) + $this->plain_args() );
		Ui::chips(
			$this->url( array_filter( array( 's' => $search ) ) ),
			'status',
			array(
				''           => __( 'All', 'lenz-plus' ),
				'processing' => $statuses['processing'] ?? '',
				'on-hold'    => $statuses['on-hold'] ?? '',
				'completed'  => $statuses['completed'] ?? '',
				'pending'    => $statuses['pending'] ?? '',
				'cancelled'  => $statuses['cancelled'] ?? '',
			),
			$status
		);

		$rows = array();
		foreach ( $orders as $order ) {
			if ( ! $order instanceof \WC_Order ) {
				continue;
			}
			$created = $order->get_date_created();
			$rows[]  = array(
				'url'   => $this->url( array( 'view' => $order->get_id() ) ),
				/* translators: 1: order number, 2: customer name. */
				'title' => trim( sprintf( __( '#%1$s · %2$s', 'lenz-plus' ), Ui::number( (int) $order->get_order_number() ), self::customer_name( $order ) ), ' ·' ),
				'meta'  => $created ? Ui::date( $created->getTimestamp(), true ) : '',
				'icon'  => 'receipt',
				'pill'  => Ui::pill( $order->get_status(), $statuses[ $order->get_status() ] ?? $order->get_status() ),
				'aside' => self::money( (float) $order->get_total(), $order->get_currency() ),
			);
		}

		Ui::rows( $rows, '' !== $search ? __( 'Nothing matches this search.', 'lenz-plus' ) : __( 'No orders yet.', 'lenz-plus' ) );
		Ui::pagination(
			$this->url(
				array_filter(
					array(
						's'      => $search,
						'status' => $status,
					)
				)
			),
			self::page_number(),
			$pages
		);
	}

	/**
	 * IDs of the orders a search finds: the order with that number («#12» or
	 * «۱۲»), plus WooCommerce's own order search (names, emails, phones,
	 * addresses, items; works with both order storages).
	 *
	 * @param string $search Search term.
	 * @return int[]
	 */
	private static function search_ids( string $search ): array {
		$search = Persian::latin_digits( $search );
		$ids    = array();

		$number = ltrim( $search, '#' );
		if ( preg_match( '/^\d{1,9}$/', $number ) && wc_get_order( (int) $number ) ) {
			$ids[] = (int) $number;
		}

		$found = \WC_Data_Store::load( 'order' )->search_orders( wc_clean( $search ) );

		return array_values( array_unique( array_map( 'intval', array_merge( $ids, (array) $found ) ) ) );
	}

	/**
	 * Order statuses without `wc-` → label.
	 *
	 * @return array<string, string>
	 */
	private static function statuses(): array {
		return Order_Text::statuses();
	}

	/**
	 * Billing name, else the email.
	 *
	 * @param \WC_Order $order Order.
	 */
	private static function customer_name( \WC_Order $order ): string {
		return Order_Text::customer_name( $order );
	}

	/**
	 * An amount as plain text.
	 *
	 * @param float  $amount   Amount.
	 * @param string $currency Currency code.
	 */
	private static function money( float $amount, string $currency ): string {
		return Money::text( $amount, $currency );
	}

	/**
	 * Prints one order.
	 *
	 * @param \WC_Order $order Order.
	 */
	private function render_order( \WC_Order $order ): void {
		$statuses = self::statuses();
		$created  = $order->get_date_created();
		$currency = $order->get_currency();
		$phone    = (string) $order->get_billing_phone();
		$email    = (string) $order->get_billing_email();
		$address  = Shipping::address_lines( $order );
		?>
		<div class="lzp-edit__grid">
			<div class="lzp-edit__main">
				<section class="lzp-card lzp-order-head">
					<div>
						<?php echo Ui::pill( $order->get_status(), $statuses[ $order->get_status() ] ?? $order->get_status() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built by Ui::pill(). ?>
						<p class="lzp-card__note"><?php echo esc_html( $created ? Ui::date( $created->getTimestamp(), true ) : '' ); ?></p>
					</div>
					<strong class="lzp-order-head__total"><?php echo esc_html( self::money( (float) $order->get_total(), $currency ) ); ?></strong>
				</section>

				<section class="lzp-card">
					<h2 class="lzp-card__title"><?php esc_html_e( 'Items', 'lenz-plus' ); ?></h2>
					<ul class="lzp-lines">
						<?php foreach ( $order->get_items() as $item ) : ?>
							<li>
								<span><?php echo esc_html( $item->get_name() ); ?> <small>× <?php echo esc_html( Ui::number( (int) $item->get_quantity() ) ); ?></small></span>
								<span><?php echo esc_html( self::money( (float) $order->get_line_subtotal( $item, true ), $currency ) ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
					<ul class="lzp-lines lzp-lines--totals">
						<?php foreach ( $order->get_order_item_totals() as $total ) : ?>
							<li><span><?php echo esc_html( wp_strip_all_tags( $total['label'] ) ); ?></span><span><?php echo esc_html( html_entity_decode( wp_strip_all_tags( $total['value'] ), ENT_QUOTES, 'UTF-8' ) ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				</section>

				<?php if ( '' !== $order->get_customer_note() ) : ?>
					<section class="lzp-card">
						<h2 class="lzp-card__title"><?php esc_html_e( 'Customer\'s note', 'lenz-plus' ); ?></h2>
						<p><?php echo nl2br( esc_html( $order->get_customer_note() ) ); ?></p>
					</section>
				<?php endif; ?>

				<?php $this->render_notes( $order ); ?>
			</div>

			<aside class="lzp-edit__side">
				<section class="lzp-card">
					<h2 class="lzp-card__title"><?php esc_html_e( 'Customer', 'lenz-plus' ); ?></h2>
					<p class="lzp-strong"><?php echo esc_html( self::customer_name( $order ) ); ?></p>
					<div class="lzp-actions">
						<?php if ( '' !== $phone ) : ?>
							<a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', Persian::latin_digits( $phone ) ) ); ?>"><?php Ui::the_icon( 'phone' ); ?><span dir="ltr"><?php echo esc_html( $phone ); ?></span></a>
						<?php endif; ?>
						<?php if ( '' !== $email ) : ?>
							<a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php Ui::the_icon( 'mail' ); ?><span><?php esc_html_e( 'Email', 'lenz-plus' ); ?></span></a>
						<?php endif; ?>
					</div>
					<?php if ( $address ) : ?>
						<address class="lzp-address"><?php echo implode( '<br>', array_map( 'esc_html', $address ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- each line escaped. ?></address>
					<?php endif; ?>
					<?php $this->render_customer_link( $order ); ?>
					<?php if ( $order->get_payment_method_title() ) : ?>
						<p class="lzp-card__note">
							<?php
							/* translators: %s: payment method. */
							echo esc_html( sprintf( __( 'Payment: %s', 'lenz-plus' ), $order->get_payment_method_title() ) );
							?>
						</p>
					<?php endif; ?>
				</section>

				<section class="lzp-card">
					<?php $this->form_open( 'status' ); ?>
						<input type="hidden" name="order_id" value="<?php echo esc_attr( (string) $order->get_id() ); ?>">
						<?php Ui::select( 'status', __( 'Order status', 'lenz-plus' ), $statuses, $order->get_status(), array( 'help' => __( 'Completing an order sends WooCommerce\'s email to the customer.', 'lenz-plus' ) ) ); ?>
						<button type="submit" class="lzp-btn lzp-btn--primary"><?php esc_html_e( 'Change status', 'lenz-plus' ); ?></button>
					</form>
				</section>

				<?php $this->render_tracking( $order ); ?>
				<?php $this->render_refund( $order ); ?>

				<?php if ( '' !== $email ) : ?>
					<section class="lzp-card">
						<h2 class="lzp-card__title"><?php esc_html_e( 'Invoice', 'lenz-plus' ); ?></h2>
						<p class="lzp-card__note"><?php esc_html_e( 'Emails the order\'s details (and the payment link while it is unpaid) to the customer.', 'lenz-plus' ); ?></p>
						<?php $this->button_form( 'invoice', array( 'order_id' => $order->get_id() ), __( 'Send to the customer', 'lenz-plus' ) ); ?>
					</section>
				<?php endif; ?>
			</aside>
		</div>
		<?php
	}

	/**
	 * A link to the customer's page in the panel (when Customers is open).
	 *
	 * @param \WC_Order $order Order.
	 */
	private function render_customer_link( \WC_Order $order ): void {
		$customers = $this->module->app()->screen( 'customers' );
		if ( ! $customers instanceof Customers ) {
			return;
		}

		$url = $customers->customer_url( $order );
		if ( '' !== $url ) {
			printf( '<p><a class="lzp-btn lzp-btn--ghost" href="%1$s">%2$s<span>%3$s</span></a></p>', esc_url( $url ), Ui::icon( 'user-circle' ), esc_html__( 'Customer\'s orders and notes', 'lenz-plus' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG.
		}
	}

	/**
	 * The parcel tracking code card (orders that ship something).
	 *
	 * @param \WC_Order $order Order.
	 */
	private function render_tracking( \WC_Order $order ): void {
		if ( ! $order->needs_shipping_address() && ! $order->get_meta( Shipping::TRACKING_META ) ) {
			return;
		}

		list( $code, $source ) = Shipping::tracking( $order );
		?>
		<section class="lzp-card">
			<?php $this->form_open( 'tracking' ); ?>
				<input type="hidden" name="order_id" value="<?php echo esc_attr( (string) $order->get_id() ); ?>">
				<?php
				Ui::input(
					'tracking',
					__( 'Parcel tracking code', 'lenz-plus' ),
					$code,
					array(
						'dir'  => 'ltr',
						'help' => 'pws' === $source ? __( 'Saved by the shipping plugin when the parcel was registered.', 'lenz-plus' ) : '',
					)
				);
				Ui::toggle( 'notify', __( 'Send it to the customer', 'lenz-plus' ), '' === $code, array( 'help' => __( 'The customer gets an email with the code.', 'lenz-plus' ) ) );
				?>
				<button type="submit" class="lzp-btn lzp-btn--ghost"><?php esc_html_e( 'Save the code', 'lenz-plus' ); ?></button>
			</form>
		</section>
		<?php
	}

	/**
	 * The refund card: what was refunded, and a form for a new refund. The
	 * money itself goes back by hand (bank transfer): the panel records the
	 * refund in WooCommerce without asking the payment gateway.
	 *
	 * @param \WC_Order $order Order.
	 */
	private function render_refund( \WC_Order $order ): void {
		$refunded = (float) $order->get_total_refunded();
		$left     = (float) $order->get_total() - $refunded;
		if ( ! $order->is_paid() && $refunded <= 0 ) {
			return;
		}

		$currency = $order->get_currency();
		?>
		<section class="lzp-card">
			<h2 class="lzp-card__title"><?php esc_html_e( 'Refund', 'lenz-plus' ); ?></h2>
			<?php if ( $order->get_refunds() ) : ?>
				<ul class="lzp-lines">
					<?php foreach ( $order->get_refunds() as $refund ) : ?>
						<?php $date = $refund->get_date_created(); ?>
						<li>
							<span><?php echo esc_html( '' !== $refund->get_reason() ? $refund->get_reason() : ( $date ? Ui::date( $date->getTimestamp() ) : '' ) ); ?></span>
							<span><?php echo esc_html( self::money( (float) $refund->get_amount(), $currency ) ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php if ( $left > 0 ) : ?>
				<?php $this->form_open( 'refund' ); ?>
					<input type="hidden" name="order_id" value="<?php echo esc_attr( (string) $order->get_id() ); ?>">
					<?php
					Ui::input(
						'amount',
						__( 'Amount', 'lenz-plus' ),
						'',
						array(
							'inputmode' => 'decimal',
							/* translators: %s: amount still refundable. */
							'help'      => sprintf( __( 'Up to %s. Pay the money back yourself (e.g. by bank transfer); this records it on the order.', 'lenz-plus' ), self::money( $left, $currency ) ),
							'required'  => true,
						)
					);
					Ui::input( 'reason', __( 'Reason', 'lenz-plus' ), '' );
					Ui::toggle( 'restock', __( 'Put the items back in stock', 'lenz-plus' ), false, array( 'help' => __( 'Only when the whole remaining amount is refunded.', 'lenz-plus' ) ) );
					?>
					<button type="submit" class="lzp-btn lzp-btn--ghost" data-lzp-confirm="<?php esc_attr_e( 'Record this refund on the order?', 'lenz-plus' ); ?>"><?php esc_html_e( 'Record the refund', 'lenz-plus' ); ?></button>
				</form>
			<?php endif; ?>
		</section>
		<?php
	}

	/**
	 * Notes of the order and the form to add one.
	 *
	 * @param \WC_Order $order Order.
	 */
	private function render_notes( \WC_Order $order ): void {
		$notes = wc_get_order_notes( array( 'order_id' => $order->get_id() ) );
		?>
		<section class="lzp-card">
			<h2 class="lzp-card__title"><?php esc_html_e( 'Notes', 'lenz-plus' ); ?></h2>
			<?php $this->form_open( 'note' ); ?>
				<input type="hidden" name="order_id" value="<?php echo esc_attr( (string) $order->get_id() ); ?>">
				<?php Ui::textarea( 'note', __( 'New note', 'lenz-plus' ), '', array( 'rows' => 2 ) ); ?>
				<?php Ui::toggle( 'to_customer', __( 'Send it to the customer', 'lenz-plus' ), false, array( 'help' => __( 'The customer gets it by email; otherwise only staff see it.', 'lenz-plus' ) ) ); ?>
				<button type="submit" class="lzp-btn lzp-btn--ghost"><?php esc_html_e( 'Add note', 'lenz-plus' ); ?></button>
			</form>
			<?php if ( $notes ) : ?>
				<ul class="lzp-notes">
					<?php foreach ( $notes as $note ) : ?>
						<li class="<?php echo $note->customer_note ? 'is-customer' : ''; ?>">
							<p><?php echo wp_kses_post( wpautop( $note->content ) ); ?></p>
							<small><?php echo esc_html( Ui::date( $note->date_created->getTimestamp(), true ) . ( $note->customer_note ? ' · ' . __( 'Sent to the customer', 'lenz-plus' ) : '' ) ); ?></small>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</section>
		<?php
	}

	/**
	 * Status change and notes.
	 *
	 * @param string $action `status` or `note`.
	 */
	public function handle( string $action ): string {
		$order = wc_get_order( absint( self::posted( 'order_id' ) ) );
		// App already checked the screen's capability (edit_shop_orders).
		if ( ! $order instanceof \WC_Order ) {
			return $this->done( 'denied' );
		}

		$back = array( 'view' => $order->get_id() );

		if ( 'status' === $action ) {
			$status = sanitize_key( self::posted( 'status' ) );
			if ( ! isset( self::statuses()[ $status ] ) ) {
				return $this->done( 'error', $back );
			}

			$order->update_status( $status, __( 'Changed in the studio panel.', 'lenz-plus' ), true );

			return $this->done( 'status', $back );
		}

		if ( 'tracking' === $action ) {
			$code   = sanitize_text_field( Persian::latin_digits( self::posted( 'tracking' ) ) );
			$notify = '1' === self::posted( 'notify' );
			Shipping::save_tracking( $order, $code, $notify );
			Log::add( 'tracking', 'order', $order->get_id(), '#' . $order->get_order_number(), array( Watcher::change( 'tracking', '', $code ) ) );

			return $this->done( 'saved', $back );
		}

		if ( 'refund' === $action ) {
			return $this->done( self::refund( $order ), $back );
		}

		if ( 'invoice' === $action ) {
			if ( '' === (string) $order->get_billing_email() ) {
				return $this->done( 'email', $back );
			}

			WC()->mailer()->customer_invoice( $order );
			$order->add_order_note( __( 'Order details sent to the customer from the studio panel.', 'lenz-plus' ), 0, true );
			Log::add( 'invoice', 'order', $order->get_id(), '#' . $order->get_order_number() );

			return $this->done( 'sent', $back );
		}

		if ( 'note' === $action ) {
			$note = trim( sanitize_textarea_field( self::posted( 'note' ) ) );
			if ( '' === $note ) {
				return $this->done( 'empty', $back );
			}

			$to_customer = '1' === self::posted( 'to_customer' );
			$order->add_order_note( $note, (int) $to_customer, true );
			Log::add( 'note', 'order', $order->get_id(), '#' . $order->get_order_number() );

			return $this->done( 'note', $back );
		}

		return $this->url( $back );
	}

	/**
	 * Records a refund (no payment gateway call); returns the message key.
	 *
	 * @param \WC_Order $order Order.
	 */
	private static function refund( \WC_Order $order ): string {
		$amount = (float) Money::parse( self::posted( 'amount' ) );
		$left   = (float) $order->get_total() - (float) $order->get_total_refunded();
		if ( $amount <= 0 || $amount > $left + 0.0001 ) {
			return 'amount';
		}

		$restock = '1' === self::posted( 'restock' );
		$args    = array(
			'amount'         => $amount,
			'reason'         => sanitize_text_field( self::posted( 'reason' ) ),
			'order_id'       => $order->get_id(),
			'refund_payment' => false,
			'restock_items'  => $restock,
		);

		// A full refund with restock returns every remaining line item to stock.
		if ( $restock && abs( $amount - $left ) < 0.0001 ) {
			$lines = array();
			foreach ( $order->get_items() as $item_id => $item ) {
				$qty = (int) $item->get_quantity() - absint( $order->get_qty_refunded_for_item( $item_id ) );
				if ( $qty > 0 ) {
					$lines[ $item_id ] = array(
						'qty'          => $qty,
						'refund_total' => (float) $item->get_total() - (float) $order->get_total_refunded_for_item( $item_id ),
					);
				}
			}
			$args['line_items'] = $lines;
		}

		return is_wp_error( wc_create_refund( $args ) ) ? 'error' : 'refunded';
	}
}
