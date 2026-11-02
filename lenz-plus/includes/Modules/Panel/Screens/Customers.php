<?php
/**
 * Customers screen: people with an account and guest buyers (grouped by
 * email or phone), each with their orders, what they bought, contact
 * buttons, private notes for the staff and, with TeraWallet, their wallet.
 * Both lists download as CSV.
 *
 * Account figures come from WooCommerce's customer object (order count and
 * total spent, which WooCommerce keeps up to date); guests come from
 * Shop\Guests (cached until the orders change). Ported from Daftar's
 * Customers_Controller.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Core\Persian;
use LenzPlus\Modules\Panel\Activity\Log;
use LenzPlus\Modules\Panel\Shop\Csv;
use LenzPlus\Modules\Panel\Shop\Guests;
use LenzPlus\Modules\Panel\Shop\Money;
use LenzPlus\Modules\Panel\Shop\Order_Text;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * Customers list and customer page.
 */
final class Customers extends Screen {

	/** Most accounts in one CSV download. */
	private const EXPORT_LIMIT = 5000;

	/** Screen id. */
	public function id(): string {
		return 'customers';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Customers', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'users';
	}

	/** Seeing customers needs the user list (shop managers have it). */
	public function capability(): string {
		return 'list_users';
	}

	/** Settings switch. */
	public function section(): string {
		return 'customers';
	}

	/** Needs WooCommerce. */
	public function is_available(): bool {
		return class_exists( 'WC_Customer' );
	}

	/** Whether the guest list is shown (else accounts). */
	private static function guests_tab(): bool {
		return 'guests' === self::arg( 'type' );
	}

	/** The customer whose page is open: [user ID, guest key, name], or null. */
	private function who(): ?array {
		$id = self::arg_int( 'user' );
		if ( $id ) {
			$user = get_userdata( $id );

			return $user ? array( $user->ID, '', $user->display_name ) : null;
		}

		$key = self::arg( 'guest' );
		if ( preg_match( '/^[a-f0-9]{32}$/', $key ) ) {
			$guest = Guests::find( $key );

			return $guest ? array( 0, $key, $guest['name'] ) : null;
		}

		return null;
	}

	/** Whether a customer's page is open. */
	private static function is_page(): bool {
		return self::arg_int( 'user' ) > 0 || '' !== self::arg( 'guest' );
	}

	/** The customer's name on their page. */
	public function page_title(): string {
		$who = self::is_page() ? $this->who() : null;

		return $who ? $who[2] : $this->title();
	}

	/** Back to the list. */
	public function back_url(): string {
		return self::is_page() ? $this->url() : '';
	}

	/**
	 * The panel page of an order's customer ('' when the order has neither an account nor an email/phone).
	 *
	 * @param \WC_Order $order Order.
	 */
	public function customer_url( \WC_Order $order ): string {
		if ( $order->get_customer_id() ) {
			return $this->url( array( 'user' => $order->get_customer_id() ) );
		}

		$key = Guests::key( (string) $order->get_billing_email(), (string) $order->get_billing_phone() );

		return '' !== $key ? $this->url( array( 'guest' => $key ) ) : '';
	}

	/** Sends the CSV of the current tab. */
	public function download(): bool {
		if ( ! $this->is_download() ) {
			return false;
		}

		$header = array( __( 'Name', 'lenz-plus' ), __( 'Email', 'lenz-plus' ), __( 'Phone', 'lenz-plus' ), __( 'City', 'lenz-plus' ), __( 'Orders', 'lenz-plus' ), __( 'Total spent', 'lenz-plus' ), __( 'Last order', 'lenz-plus' ) );
		$rows   = array();
		if ( self::guests_tab() ) {
			foreach ( Guests::all() as $guest ) {
				$rows[] = array( $guest['name'], $guest['email'], $guest['phone'], $guest['city'], $guest['orders'], Money::round( $guest['spent'] ), $guest['last_order'] ? wp_date( 'Y-m-d', $guest['last_order'] ) : '' );
			}
		} else {
			$query = new \WP_User_Query(
				array(
					'role'    => 'customer',
					'number'  => self::EXPORT_LIMIT,
					'orderby' => 'registered',
					'order'   => 'DESC',
				)
			);
			foreach ( (array) $query->get_results() as $user ) {
				$row    = self::account( $user );
				$rows[] = array( $row['name'], $row['email'], $row['phone'], $row['city'], $row['orders'], $row['spent'], $row['last_order'] ? wp_date( 'Y-m-d', $row['last_order'] ) : '' );
			}
		}

		Log::add( 'export', 'customer', 0, self::guests_tab() ? __( 'Guest buyers', 'lenz-plus' ) : __( 'Customers', 'lenz-plus' ) );
		Csv::send( self::guests_tab() ? 'guest-buyers' : 'customers', $header, $rows );

		return true;
	}

	/** Prints the list or a customer's page. */
	public function render(): void {
		if ( ! self::is_page() ) {
			$this->render_list();
			return;
		}

		$who = $this->who();
		if ( ! $who ) {
			Ui::empty_state( __( 'This item does not exist.', 'lenz-plus' ) );
			return;
		}

		$this->render_customer( $who );
	}

	/**
	 * Figures of one account.
	 *
	 * @param \WP_User $user User.
	 */
	private static function account( \WP_User $user ): array {
		$customer = new \WC_Customer( $user->ID );
		$last     = $customer->get_last_order();
		$created  = $last ? $last->get_date_created() : null;
		$name     = trim( $customer->get_first_name() . ' ' . $customer->get_last_name() );

		return array(
			'name'       => '' !== $name ? $name : $user->display_name,
			'email'      => $user->user_email,
			'phone'      => (string) $customer->get_billing_phone(),
			'city'       => (string) $customer->get_billing_city(),
			'orders'     => (int) $customer->get_order_count(),
			'spent'      => Money::round( $customer->get_total_spent() ),
			'last_order' => $created ? $created->getTimestamp() : 0,
		);
	}

	/** The accounts or guests list, with sorting, search and the CSV link. */
	private function render_list(): void {
		$guests = self::guests_tab();
		$search = self::search_term();
		$sort   = self::arg( 'orderby' );
		$sort   = in_array( $sort, array( 'spent', 'orders', 'name' ), true ) ? $sort : '';
		$keep   = array_filter(
			array(
				'type'    => $guests ? 'guests' : '',
				'orderby' => $sort,
			)
		);

		Ui::search( $this->url(), $search, __( 'Name, email or mobile', 'lenz-plus' ), $keep + $this->plain_args() );
		Ui::chips(
			$this->url( array_filter( array( 's' => $search ) ) ),
			'type',
			array(
				''       => __( 'With an account', 'lenz-plus' ),
				'guests' => __( 'Guest buyers', 'lenz-plus' ),
			),
			$guests ? 'guests' : ''
		);
		Ui::chips(
			$this->url( array_filter( array( 's' => $search, 'type' => $keep['type'] ?? '' ) ) ), // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			'orderby',
			array(
				''       => __( 'Newest', 'lenz-plus' ),
				'spent'  => __( 'Spent most', 'lenz-plus' ),
				'orders' => __( 'Most orders', 'lenz-plus' ),
				'name'   => __( 'Name', 'lenz-plus' ),
			),
			$sort
		);

		list( $rows, $pages ) = $guests ? $this->guest_rows( $search, $sort ) : $this->account_rows( $search, $sort );

		Ui::rows( $rows, '' !== $search ? __( 'Nothing matches this search.', 'lenz-plus' ) : __( 'No customers yet.', 'lenz-plus' ) );
		Ui::pagination( $this->url( array_filter( array( 's' => $search ) ) + $keep ), self::page_number(), $pages );
		?>
		<p><a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( $this->download_url( $keep ) ); ?>" data-lzp-noprefetch><?php Ui::the_icon( 'download' ); ?><span><?php esc_html_e( 'Download as CSV (Excel)', 'lenz-plus' ); ?></span></a></p>
		<?php
	}

	/**
	 * Rows of the accounts list.
	 *
	 * @param string $search Search term.
	 * @param string $sort   Sort key.
	 * @return array{0: array[], 1: int} Rows and the number of pages.
	 */
	private function account_rows( string $search, string $sort ): array {
		$args = array(
			'role'        => 'customer',
			'number'      => $this->per_page(),
			'paged'       => self::page_number(),
			'count_total' => true,
			'orderby'     => 'registered',
			'order'       => 'DESC',
		);
		if ( '' !== $search ) {
			$args['search']         = '*' . $search . '*';
			$args['search_columns'] = array( 'user_login', 'user_email', 'display_name', 'user_nicename' );
		}
		if ( 'spent' === $sort || 'orders' === $sort ) {
			// WooCommerce keeps both figures in user meta.
			$args['meta_key'] = 'spent' === $sort ? '_money_spent' : '_order_count'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['orderby']  = 'meta_value_num';
		} elseif ( 'name' === $sort ) {
			$args['orderby'] = 'display_name';
			$args['order']   = 'ASC';
		}

		$query = new \WP_User_Query( $args );
		$rows  = array();
		foreach ( (array) $query->get_results() as $user ) {
			$data   = self::account( $user );
			$rows[] = array(
				'url'   => $this->url( array( 'user' => $user->ID ) ),
				'title' => $data['name'],
				'meta'  => self::row_meta( $data ),
				'thumb' => (string) get_avatar_url( $user->ID, array( 'size' => 96 ) ),
				'aside' => $data['spent'] ? Money::text( $data['spent'] ) : '',
			);
		}

		return array( $rows, (int) ceil( $query->get_total() / $this->per_page() ) );
	}

	/**
	 * Rows of the guests list.
	 *
	 * @param string $search Search term.
	 * @param string $sort   Sort key.
	 * @return array{0: array[], 1: int} Rows and the number of pages.
	 */
	private function guest_rows( string $search, string $sort ): array {
		$guests = Guests::all();
		if ( '' !== $search ) {
			$needle = mb_strtolower( Persian::latin_digits( $search ) );
			$guests = array_values(
				array_filter(
					$guests,
					static function ( array $guest ) use ( $needle ): bool {
						return false !== mb_strpos( mb_strtolower( $guest['name'] . ' ' . $guest['email'] . ' ' . Persian::latin_digits( $guest['phone'] ) ), $needle );
					}
				)
			);
		}
		if ( '' !== $sort ) {
			usort(
				$guests,
				static function ( array $a, array $b ) use ( $sort ): int {
					return 'name' === $sort ? strcmp( $a['name'], $b['name'] ) : $b[ $sort ] <=> $a[ $sort ];
				}
			);
		}

		$rows = array();
		foreach ( array_slice( $guests, ( self::page_number() - 1 ) * $this->per_page(), $this->per_page() ) as $guest ) {
			$rows[] = array(
				'url'   => $this->url( array( 'guest' => $guest['key'] ) ),
				'title' => $guest['name'],
				'meta'  => self::row_meta( $guest ),
				'icon'  => 'user-circle',
				'aside' => $guest['spent'] ? Money::text( $guest['spent'] ) : '',
			);
		}

		return array( $rows, (int) ceil( count( $guests ) / $this->per_page() ) );
	}

	/**
	 * The second line of a customer row: orders, then the phone or email.
	 *
	 * @param array $data Customer figures.
	 */
	private static function row_meta( array $data ): string {
		/* translators: %s: number of orders. */
		$parts   = array( sprintf( _n( '%s order', '%s orders', (int) $data['orders'], 'lenz-plus' ), Ui::number( (int) $data['orders'] ) ) );
		$parts[] = '' !== $data['phone'] ? $data['phone'] : $data['email'];

		return implode( ' · ', array_filter( $parts ) );
	}

	/**
	 * A customer's page.
	 *
	 * @param array $who [ user ID, guest key, name ].
	 */
	private function render_customer( array $who ): void {
		list( $user_id, $key ) = $who;

		if ( $user_id ) {
			$data    = self::account( get_userdata( $user_id ) );
			$orders  = wc_get_orders(
				array(
					'customer_id' => $user_id,
					'type'        => 'shop_order',
					'limit'       => 50,
					'orderby'     => 'date',
					'order'       => 'DESC',
				)
			);
			$address = trim( wp_strip_all_tags( str_replace( array( '<br/>', '<br>' ), "\n", (string) WC()->countries->get_formatted_address( ( new \WC_Customer( $user_id ) )->get_billing() ) ) ) );
		} else {
			$data    = Guests::find( $key );
			$orders  = Guests::orders( $data );
			$address = $orders ? trim( wp_strip_all_tags( str_replace( array( '<br/>', '<br>' ), "\n", (string) $orders[0]->get_formatted_billing_address() ) ) ) : '';
		}

		Ui::stats(
			array(
				array( __( 'Orders', 'lenz-plus' ), Ui::number( (int) $data['orders'] ), 'receipt' ),
				array( __( 'Total spent', 'lenz-plus' ), Money::text( (float) $data['spent'] ), 'chart' ),
				array( __( 'Average order', 'lenz-plus' ), Money::text( $data['orders'] ? (float) $data['spent'] / (int) $data['orders'] : 0 ), 'bag' ),
			)
		);
		?>
		<div class="lzp-edit__grid">
			<div class="lzp-edit__main">
				<?php $this->render_orders( $orders ); ?>
				<?php $this->render_products( $orders ); ?>
				<?php $this->render_notes( $user_id, $key ); ?>
			</div>
			<aside class="lzp-edit__side">
				<section class="lzp-card">
					<h2 class="lzp-card__title"><?php esc_html_e( 'Contact', 'lenz-plus' ); ?></h2>
					<?php Ui::contact_buttons( (string) $data['phone'], (string) $data['email'] ); ?>
					<?php if ( '' !== $address ) : ?>
						<address class="lzp-address"><?php echo nl2br( esc_html( $address ) ); ?></address>
					<?php endif; ?>
					<?php if ( $user_id && current_user_can( 'edit_user', $user_id ) && ! $this->module->access()->is_panel_only() ) : ?>
						<p><a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( get_edit_user_link( $user_id ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Edit in WordPress', 'lenz-plus' ); ?></a></p>
					<?php endif; ?>
				</section>
				<?php
				$wallet = $this->module->app()->screen( 'wallet' );
				if ( $user_id && $wallet instanceof Wallet ) {
					$wallet->render_person_card( $user_id );
				}
				?>
			</aside>
		</div>
		<?php
	}

	/**
	 * The customer's latest orders.
	 *
	 * @param \WC_Order[] $orders Orders, newest first.
	 */
	private function render_orders( array $orders ): void {
		$screen = $this->module->app()->screen( 'orders' );
		$labels = Order_Text::statuses();
		$rows   = array();
		foreach ( array_slice( $orders, 0, 10 ) as $order ) {
			$created = $order->get_date_created();
			$rows[]  = array(
				'url'   => $screen ? $screen->url( array( 'view' => $order->get_id() ) ) : '',
				/* translators: %s: order number. */
				'title' => sprintf( __( 'Order #%s', 'lenz-plus' ), Ui::number( (int) $order->get_order_number() ) ),
				'meta'  => $created ? Ui::date( $created->getTimestamp(), true ) : '',
				'icon'  => 'receipt',
				'pill'  => Ui::pill( $order->get_status(), $labels[ $order->get_status() ] ?? $order->get_status() ),
				'aside' => Money::text( (float) $order->get_total(), $order->get_currency() ),
			);
		}
		?>
		<section class="lzp-section">
			<h2 class="lzp-section__title"><?php esc_html_e( 'Latest orders', 'lenz-plus' ); ?></h2>
			<?php Ui::rows( $rows, __( 'No orders yet.', 'lenz-plus' ) ); ?>
		</section>
		<?php
	}

	/**
	 * What the customer bought most (paid orders).
	 *
	 * @param \WC_Order[] $orders Orders.
	 */
	private function render_products( array $orders ): void {
		$products = array();
		foreach ( $orders as $order ) {
			if ( ! $order->is_paid() ) {
				continue;
			}
			foreach ( $order->get_items() as $item ) {
				$id = (int) $item->get_product_id();
				if ( ! isset( $products[ $id ] ) ) {
					$products[ $id ] = array( $item->get_name(), 0, 0.0 );
				}
				$products[ $id ][1] += (int) $item->get_quantity();
				$products[ $id ][2] += (float) $item->get_total();
			}
		}
		if ( ! $products ) {
			return;
		}

		usort(
			$products,
			static function ( array $a, array $b ): int {
				return $b[2] <=> $a[2];
			}
		);

		$lines = array();
		foreach ( array_slice( $products, 0, 8 ) as $product ) {
			$lines[] = array( $product[0] . ' × ' . Ui::number( $product[1] ), Money::text( $product[2] ) );
		}
		?>
		<section class="lzp-card">
			<h2 class="lzp-card__title"><?php esc_html_e( 'What they bought', 'lenz-plus' ); ?></h2>
			<?php Ui::lines( $lines ); ?>
		</section>
		<?php
	}

	/**
	 * Staff notes about the customer.
	 *
	 * @param int    $user_id User ID (0 for a guest).
	 * @param string $key     Guest key.
	 */
	private function render_notes( int $user_id, string $key ): void {
		$who = array_filter(
			array(
				'user_id' => $user_id,
				'guest'   => $key,
			)
		);
		?>
		<section class="lzp-card">
			<h2 class="lzp-card__title"><?php esc_html_e( 'Notes for the staff', 'lenz-plus' ); ?></h2>
			<?php $this->form_open( 'note' ); ?>
				<?php foreach ( $who as $name => $value ) : ?>
					<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>">
				<?php endforeach; ?>
				<?php Ui::textarea( 'note', __( 'New note', 'lenz-plus' ), '', array( 'rows' => 2 ) ); ?>
				<button type="submit" class="lzp-btn lzp-btn--ghost"><?php esc_html_e( 'Add note', 'lenz-plus' ); ?></button>
			</form>
			<?php $notes = Guests::notes( $user_id, $key ); ?>
			<?php if ( $notes ) : ?>
				<ul class="lzp-notes">
					<?php foreach ( $notes as $note ) : ?>
						<li>
							<p><?php echo nl2br( esc_html( (string) $note['text'] ) ); ?></p>
							<small><?php echo esc_html( Ui::date( (int) $note['date'], true ) . ' · ' . $note['author'] ); ?></small>
							<?php $this->button_form( 'delete_note', $who + array( 'note_id' => $note['id'] ), __( 'Delete', 'lenz-plus' ), 'lzp-btn lzp-btn--ghost', __( 'Delete this note?', 'lenz-plus' ) ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</section>
		<?php
	}

	/**
	 * Adds or deletes a note.
	 *
	 * @param string $action `note` or `delete_note`.
	 */
	public function handle( string $action ): string {
		$user_id = absint( self::posted( 'user_id' ) );
		$key     = sanitize_key( self::posted( 'guest' ) );
		$back    = $user_id ? array( 'user' => $user_id ) : array( 'guest' => $key );
		$name    = '';
		if ( $user_id ) {
			$user = get_userdata( $user_id );
			$name = $user ? $user->display_name : '';
		} elseif ( preg_match( '/^[a-f0-9]{32}$/', $key ) ) {
			$guest = Guests::find( $key );
			$name  = $guest ? $guest['name'] : '';
		}
		if ( '' === $name ) {
			return $this->done( 'error' );
		}

		if ( 'note' === $action ) {
			$text = trim( sanitize_textarea_field( self::posted( 'note' ) ) );
			if ( '' === $text ) {
				return $this->done( 'empty', $back );
			}
			Guests::add_note( $user_id, $user_id ? '' : $key, $text );
			Log::add( 'note', 'customer', $user_id, $name );

			return $this->done( 'note', $back );
		}

		if ( 'delete_note' === $action ) {
			Guests::delete_note( $user_id, $user_id ? '' : $key, sanitize_text_field( self::posted( 'note_id' ) ) );

			return $this->done( 'deleted', $back );
		}

		return $this->url( $back );
	}
}
