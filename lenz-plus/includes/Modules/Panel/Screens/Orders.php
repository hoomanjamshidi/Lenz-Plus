<?php
/**
 * Orders screen: WooCommerce orders with status chips and search, and an
 * order page with items, totals, the customer's contact links, status change
 * and notes.
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
		$number = self::order_number( $search );
		if ( $number ) {
			// An order number opens that order alone.
			$found  = wc_get_order( $number );
			$orders = $found instanceof \WC_Order ? array( $found ) : array();
			$pages  = 1;
		} else {
			$result = wc_get_orders( $args + self::search_args( $search ) );
			$orders = is_object( $result ) ? $result->orders : array();
			$pages  = is_object( $result ) ? (int) $result->max_num_pages : 0;
		}

		Ui::search( $this->url(), $search, __( 'Order number, email or mobile', 'lenz-plus' ), ( '' !== $status ? array( 'status' => $status ) : array() ) + $this->plain_args() );
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
	 * The order number a search asks for (a short number, «#12» or «۱۲»), else 0.
	 *
	 * @param string $search Search term.
	 */
	private static function order_number( string $search ): int {
		$search = ltrim( Persian::latin_digits( $search ), '#' );

		return preg_match( '/^\d{1,6}$/', $search ) ? (int) $search : 0;
	}

	/**
	 * wc_get_orders() arguments for an email or phone number search (Persian
	 * digits accepted). Other text matches the billing email exactly, so a
	 * search that is neither finds nothing rather than everything.
	 *
	 * @param string $search Search term.
	 */
	private static function search_args( string $search ): array {
		$search = Persian::latin_digits( $search );
		if ( '' === $search ) {
			return array();
		}

		$digits = (string) preg_replace( '/\D+/', '', $search );
		if ( ! is_email( $search ) && strlen( $digits ) >= 7 ) {
			return array( 'billing_phone' => $digits );
		}

		return array( 'billing_email' => $search );
	}

	/**
	 * Order statuses without `wc-` → label.
	 *
	 * @return array<string, string>
	 */
	private static function statuses(): array {
		$statuses = array();
		foreach ( wc_get_order_statuses() as $key => $label ) {
			$plain              = (string) preg_replace( '/^wc-/', '', $key );
			$statuses[ $plain ] = $label;
		}

		return $statuses;
	}

	/**
	 * Billing name, else the email.
	 *
	 * @param \WC_Order $order Order.
	 */
	private static function customer_name( \WC_Order $order ): string {
		$name = trim( $order->get_formatted_billing_full_name() );

		return '' !== $name ? $name : (string) $order->get_billing_email();
	}

	/**
	 * An amount as plain text.
	 *
	 * @param float  $amount   Amount.
	 * @param string $currency Currency code.
	 */
	private static function money( float $amount, string $currency ): string {
		return html_entity_decode( wp_strip_all_tags( wc_price( $amount, array( 'currency' => $currency ) ) ), ENT_QUOTES, 'UTF-8' );
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
		$address  = $order->get_formatted_shipping_address() ? $order->get_formatted_shipping_address() : $order->get_formatted_billing_address();
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
						<address class="lzp-address"><?php echo wp_kses( $address, array( 'br' => array() ) ); ?></address>
					<?php endif; ?>
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
			</aside>
		</div>
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

		if ( 'note' === $action ) {
			$note = trim( sanitize_textarea_field( self::posted( 'note' ) ) );
			if ( '' === $note ) {
				return $this->done( 'empty', $back );
			}

			$to_customer = '1' === self::posted( 'to_customer' );
			$order->add_order_note( $note, (int) $to_customer, true );

			return $this->done( 'note', $back );
		}

		return $this->url( $back );
	}
}
