<?php
/**
 * Search screen: one box that looks through orders (number, name, email,
 * phone), customers, courses and products, articles, projects and pages at
 * once, a few results per kind with a link to the full list. Only the
 * sections open to the user are searched. Ported from Daftar's
 * Search_Controller.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Core\Persian;
use LenzPlus\Modules\Panel\Shop\Money;
use LenzPlus\Modules\Panel\Shop\Order_Text;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * Search across the panel.
 */
final class Search extends Screen {

	/** Results per kind. */
	private const LIMIT = 5;

	/** Content screens searched, in order. */
	private const CONTENT = array( 'courses', 'products', 'posts', 'portfolio', 'pages' );

	/** Screen id. */
	public function id(): string {
		return 'search';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Search', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'search';
	}

	/** Every panel user (each kind checks its own screen). */
	public function capability(): string {
		return 'read';
	}

	/** Settings switch. */
	public function section(): string {
		return 'search';
	}

	/** Prints the box and the results. */
	public function render(): void {
		$search = self::search_term();
		Ui::search( $this->url(), $search, __( 'Order number, name, phone, title…', 'lenz-plus' ), $this->plain_args() );

		if ( mb_strlen( $search ) < 2 && ! ctype_digit( Persian::latin_digits( $search ) ) ) {
			Ui::empty_state( __( 'Type at least two letters.', 'lenz-plus' ), 'search' );
			return;
		}

		$found = $this->render_orders( $search );
		$found = $this->render_customers( $search ) || $found;
		foreach ( self::CONTENT as $id ) {
			$found = $this->render_content( $id, $search ) || $found;
		}

		if ( ! $found ) {
			Ui::empty_state( __( 'Nothing matches this search.', 'lenz-plus' ), 'search' );
		}
	}

	/**
	 * A group of results with a link to the full list.
	 *
	 * @param Screen  $screen Screen of the kind.
	 * @param array[] $rows   Rows.
	 * @param string  $search Search term.
	 */
	private function group( Screen $screen, array $rows, string $search ): bool {
		if ( ! $rows ) {
			return false;
		}
		?>
		<section class="lzp-section">
			<div class="lzp-section__head">
				<h2 class="lzp-section__title"><?php echo esc_html( $screen->title() ); ?></h2>
				<a href="<?php echo esc_url( $screen->url( array( 's' => $search ) ) ); ?>"><?php esc_html_e( 'See all', 'lenz-plus' ); ?></a>
			</div>
			<?php Ui::rows( $rows, '' ); ?>
		</section>
		<?php
		return true;
	}

	/**
	 * Orders by number or WooCommerce's order search.
	 *
	 * @param string $search Search term.
	 */
	private function render_orders( string $search ): bool {
		$screen = $this->module->app()->screen( 'orders' );
		if ( ! $screen ) {
			return false;
		}

		$latin = Persian::latin_digits( $search );
		$ids   = array();
		$num   = ltrim( $latin, '#' );
		if ( preg_match( '/^\d{1,9}$/', $num ) && wc_get_order( (int) $num ) ) {
			$ids[] = (int) $num;
		}
		$ids = array_slice( array_unique( array_merge( $ids, array_map( 'intval', (array) \WC_Data_Store::load( 'order' )->search_orders( wc_clean( $latin ) ) ) ) ), 0, self::LIMIT );

		$labels = Order_Text::statuses();
		$rows   = array();
		foreach ( $ids as $id ) {
			$order = wc_get_order( $id );
			if ( ! $order instanceof \WC_Order || $order instanceof \WC_Order_Refund ) {
				continue;
			}
			$rows[] = array(
				'url'   => $screen->url( array( 'view' => $order->get_id() ) ),
				/* translators: 1: order number, 2: customer name. */
				'title' => sprintf( __( '#%1$s · %2$s', 'lenz-plus' ), Ui::number( (int) $order->get_order_number() ), Order_Text::customer_name( $order ) ),
				'icon'  => 'receipt',
				'pill'  => Ui::pill( $order->get_status(), $labels[ $order->get_status() ] ?? '' ),
				'aside' => Money::text( (float) $order->get_total(), $order->get_currency() ),
			);
		}

		return $this->group( $screen, $rows, $search );
	}

	/**
	 * Customers with an account.
	 *
	 * @param string $search Search term.
	 */
	private function render_customers( string $search ): bool {
		$screen = $this->module->app()->screen( 'customers' );
		if ( ! $screen ) {
			return false;
		}

		$query = new \WP_User_Query(
			array(
				'role'           => 'customer',
				'number'         => self::LIMIT,
				'search'         => '*' . $search . '*',
				'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
				'count_total'    => false,
			)
		);
		$rows  = array();
		foreach ( (array) $query->get_results() as $user ) {
			$rows[] = array(
				'url'   => $screen->url( array( 'user' => $user->ID ) ),
				'title' => $user->display_name,
				'meta'  => $user->user_email,
				'thumb' => (string) get_avatar_url( $user->ID, array( 'size' => 96 ) ),
			);
		}

		return $this->group( $screen, $rows, $search );
	}

	/**
	 * Posts of a content screen.
	 *
	 * @param string $id     Screen id.
	 * @param string $search Search term.
	 */
	private function render_content( string $id, string $search ): bool {
		$screen = $this->module->app()->screen( $id );
		if ( ! $screen instanceof Content_Screen ) {
			return false;
		}

		return $this->group( $screen, $screen->search( $search, self::LIMIT ), $search );
	}
}
