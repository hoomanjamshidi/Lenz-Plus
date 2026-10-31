<?php
/**
 * Home screen of the panel: a greeting, what is waiting (orders, bookings,
 * requests, comments), the month's sales, quick "add" buttons and the latest
 * orders. Every block shows only when its section is open to the user.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Modules\Panel\Router;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * Overview with counts, sales and shortcuts.
 */
final class Dashboard extends Screen {

	/** Paid statuses counted as sales. */
	private const PAID = array( 'processing', 'completed', 'on-hold' );

	/** WooCommerce's capability to see sales figures. */
	private const REPORTS_CAP = 'view_woocommerce_reports';

	/** Most orders summed for the sales figure (keeps the page fast on big stores). */
	private const SALES_LIMIT = 500;

	/** Screen id. */
	public function id(): string {
		return Router::HOME;
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Home', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'home';
	}

	/** Every panel user. */
	public function capability(): string {
		return 'read';
	}

	/** Prints the overview. */
	public function render(): void {
		$app  = $this->module->app();
		$user = wp_get_current_user();
		$name = '' !== $user->first_name ? $user->first_name : $user->display_name;
		?>
		<section class="lzp-hello">
			<div>
				<p class="lzp-hello__date"><?php echo esc_html( Ui::date( time() ) ); ?></p>
				<h2 class="lzp-hello__title">
					<?php
					/* translators: %s: user's first name. */
					echo esc_html( sprintf( __( 'Hello %s', 'lenz-plus' ), $name ) );
					?>
				</h2>
				<p><?php esc_html_e( 'Here is what is happening on your site.', 'lenz-plus' ); ?></p>
			</div>
			<a class="lzp-btn lzp-btn--light" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php Ui::the_icon( 'globe' ); ?><span><?php esc_html_e( 'View the site', 'lenz-plus' ); ?></span></a>
		</section>

		<?php $this->render_stats(); ?>

		<?php $actions = $app->create_actions(); ?>
		<?php if ( $actions ) : ?>
			<section class="lzp-section">
				<h2 class="lzp-section__title"><?php esc_html_e( 'Quick add', 'lenz-plus' ); ?></h2>
				<ul class="lzp-quick">
					<?php foreach ( $actions as $action ) : ?>
						<li><a class="lzp-quick__item" href="<?php echo esc_url( $action[0] ); ?>"><?php Ui::the_icon( $action[2] ); ?><span><?php echo esc_html( $action[1] ); ?></span></a></li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

		<?php
		$orders = $app->screen( 'orders' );
		if ( $orders ) {
			$this->render_recent_orders( $orders );
		}
	}

	/** Tiles of what is waiting, plus the sales of the last 30 days. */
	private function render_stats(): void {
		$labels = array(
			'orders'   => __( 'Orders to handle', 'lenz-plus' ),
			'bookings' => __( 'New bookings', 'lenz-plus' ),
			'requests' => __( 'New requests', 'lenz-plus' ),
			'comments' => __( 'Comments waiting', 'lenz-plus' ),
		);

		$tiles = array();
		foreach ( $labels as $id => $label ) {
			$screen = $this->module->app()->screen( $id );
			if ( $screen ) {
				$args    = 'orders' === $id ? array( 'status' => 'processing' ) : ( 'bookings' === $id ? array( 'status' => 'new' ) : array() );
				$tiles[] = array( $screen->url( $args ), $label, Ui::number( $screen->badge() ), $screen->icon() );
			}
		}

		$sales = $this->sales();
		if ( null !== $sales ) {
			$tiles[] = array( $this->module->app()->screen( 'orders' )->url( array( 'status' => 'completed' ) ), __( 'Sales, last 30 days', 'lenz-plus' ), $sales, 'chart' );
		}

		if ( ! $tiles ) {
			return;
		}

		echo '<ul class="lzp-stats">';
		foreach ( $tiles as $tile ) {
			printf(
				'<li><a class="lzp-stat" href="%1$s">%4$s<span class="lzp-stat__value">%3$s</span><span class="lzp-stat__label">%2$s</span></a></li>',
				esc_url( $tile[0] ),
				esc_html( $tile[1] ),
				esc_html( $tile[2] ),
				Ui::icon( $tile[3] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG.
			);
		}
		echo '</ul>';
	}

	/** Paid order totals of the last 30 days as text, or null without access. */
	private function sales(): ?string {
		if ( ! $this->module->app()->screen( 'orders' ) || ! current_user_can( self::REPORTS_CAP ) ) {
			return null;
		}

		$orders = wc_get_orders(
			array(
				'status'       => self::PAID,
				'date_created' => '>' . ( time() - 30 * DAY_IN_SECONDS ),
				'limit'        => self::SALES_LIMIT,
				'type'         => 'shop_order',
			)
		);

		$total = 0.0;
		foreach ( $orders as $order ) {
			$total += (float) $order->get_total() - (float) $order->get_total_refunded();
		}

		return html_entity_decode( wp_strip_all_tags( wc_price( $total ) ), ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * The five latest orders.
	 *
	 * @param Screen $orders Orders screen.
	 */
	private function render_recent_orders( Screen $orders ): void {
		$latest = wc_get_orders(
			array(
				'limit'   => 5,
				'orderby' => 'date',
				'order'   => 'DESC',
				'type'    => 'shop_order',
			)
		);
		if ( ! $latest ) {
			return;
		}

		$labels = wc_get_order_statuses();
		$rows   = array();
		foreach ( $latest as $order ) {
			$created = $order->get_date_created();
			$rows[]  = array(
				'url'   => $orders->url( array( 'view' => $order->get_id() ) ),
				/* translators: 1: order number, 2: customer name. */
				'title' => trim( sprintf( __( '#%1$s · %2$s', 'lenz-plus' ), Ui::number( (int) $order->get_order_number() ), $order->get_formatted_billing_full_name() ), ' ·' ),
				'meta'  => $created ? Ui::date( $created->getTimestamp(), true ) : '',
				'icon'  => 'receipt',
				'pill'  => Ui::pill( $order->get_status(), $labels[ 'wc-' . $order->get_status() ] ?? $order->get_status() ),
				'aside' => html_entity_decode( wp_strip_all_tags( wc_price( (float) $order->get_total(), array( 'currency' => $order->get_currency() ) ) ), ENT_QUOTES, 'UTF-8' ),
			);
		}
		?>
		<section class="lzp-section">
			<div class="lzp-section__head">
				<h2 class="lzp-section__title"><?php esc_html_e( 'Latest orders', 'lenz-plus' ); ?></h2>
				<a href="<?php echo esc_url( $orders->url() ); ?>"><?php esc_html_e( 'All orders', 'lenz-plus' ); ?></a>
			</div>
			<?php Ui::rows( $rows, '' ); ?>
		</section>
		<?php
	}
}
