<?php
/**
 * Notifications of the studio panel: new orders, bookings and requests on
 * staff phones, and the number of waiting items on the home-screen icon.
 *
 * There is no push server: while the panel is open (or installed and in the
 * background) panel.js asks `wp_ajax_lzp_panel_pulse` once a minute, only on
 * devices whose owner switched alerts on (My account), and only while the
 * page is visible or the installed app is running. The answer is small and
 * cheap: what is waiting (the menu badges) and up to five new items since
 * the last answer. The page shows them through the service worker (served
 * at <slug>/sw/, scoped to the panel), which also opens the item when a
 * notification is tapped. Ported from Daftar's notify.ts + sw.js.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel;

use LenzPlus\Core\Asset;
use LenzPlus\Modules\Builder\Booking_Requests;
use LenzPlus\Modules\Builder\Contact_Messages;
use LenzPlus\Modules\Panel\Screens\Screen;
use LenzPlus\Modules\Panel\Shop\Money;
use LenzPlus\Modules\Panel\Shop\Order_Text;

defined( 'ABSPATH' ) || exit;

/**
 * Pulse endpoint and service worker.
 */
final class Notifier {

	/** AJAX action of the pulse. */
	public const AJAX_ACTION = 'lzp_panel_pulse';

	/** Screen segment that serves the service worker. */
	public const WORKER = 'sw';

	/** Screens whose new items are announced, with their badge. */
	private const SCREENS = array( 'orders', 'bookings', 'requests' );

	/** New items per kind in one answer. */
	private const LIMIT = 5;

	/** @var Module */
	private $module;

	/**
	 * @param Module $module Panel module.
	 */
	public function __construct( Module $module ) {
		$this->module = $module;
	}

	/** Hooks the pulse endpoint. */
	public function register(): void {
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'pulse' ) );
	}

	/**
	 * Data panel.js needs (null when notifications are off).
	 *
	 * @param Module $module Panel module.
	 * @return array<string, mixed>|null
	 */
	public static function script_data( Module $module ): ?array {
		if ( ! $module->settings()['notify'] ) {
			return null;
		}

		return array(
			'action' => self::AJAX_ACTION,
			'nonce'  => wp_create_nonce( self::AJAX_ACTION ),
			// With plain permalinks the panel lives at the site's root: a worker there would control the whole site.
			'worker' => get_option( 'permalink_structure' ) ? add_query_arg( 'ver', LENZ_PLUS_VERSION, Router::url( self::WORKER ) ) : '',
			'scope'  => Router::url(),
			'icon'   => Asset::url( 'assets/modules/panel/img/icon-192.png' ),
			'every'  => 60,
		);
	}

	/** Answers the pulse: waiting count and new items since `after`. */
	public function pulse(): void {
		check_ajax_referer( self::AJAX_ACTION, 'nonce' );
		if ( ! $this->module->access()->can_use() ) {
			wp_send_json_error( null, 403 );
		}

		$after = isset( $_POST['after'] ) ? absint( $_POST['after'] ) : 0;
		$app   = $this->module->app();
		$badge = 0;
		$items = array();

		foreach ( self::SCREENS as $id ) {
			$screen = $app->screen( $id );
			if ( ! $screen ) {
				continue;
			}

			$badge += $screen->badge();
			if ( $after ) {
				$items = array_merge( $items, 'orders' === $id ? $this->new_orders( $screen, $after ) : $this->new_posts( $screen, $after ) );
			}
		}

		wp_send_json_success(
			array(
				'now'   => time(),
				'badge' => $badge,
				'items' => array_slice( $items, 0, self::LIMIT ),
			)
		);
	}

	/**
	 * Orders created after a time.
	 *
	 * @param Screen $screen Orders screen.
	 * @param int            $after  Unix time.
	 */
	private function new_orders( Screen $screen, int $after ): array {
		$orders = wc_get_orders(
			array(
				'type'         => 'shop_order',
				'status'       => array( 'processing', 'on-hold', 'completed', 'pending' ),
				'date_created' => '>' . $after,
				'limit'        => self::LIMIT,
				'orderby'      => 'date',
				'order'        => 'DESC',
			)
		);

		$out = array();
		foreach ( $orders as $order ) {
			$out[] = array(
				'tag'   => 'order-' . $order->get_id(),
				/* translators: %s: order number. */
				'title' => sprintf( __( 'New order #%s', 'lenz-plus' ), Ui::number( (int) $order->get_order_number() ) ),
				'body'  => trim( Order_Text::customer_name( $order ) . ' · ' . Money::text( (float) $order->get_total(), $order->get_currency() ), ' ·' ),
				'url'   => $screen->url( array( 'view' => $order->get_id() ) ),
			);
		}

		return $out;
	}

	/**
	 * Bookings or requests stored after a time.
	 *
	 * @param Screen $screen Bookings or Requests screen.
	 * @param int            $after  Unix time.
	 */
	private function new_posts( Screen $screen, int $after ): array {
		$types                = array(
			'bookings' => array( Booking_Requests::POST_TYPE, __( 'New booking', 'lenz-plus' ) ),
			'requests' => array( Contact_Messages::POST_TYPE, __( 'New request', 'lenz-plus' ) ),
		);
		list( $type, $title ) = $types[ $screen->id() ];

		$posts = get_posts(
			array(
				'post_type'      => $type,
				'post_status'    => 'private',
				'posts_per_page' => self::LIMIT,
				'no_found_rows'  => true,
				'date_query'     => array(
					array(
						'column' => 'post_date_gmt',
						'after'  => gmdate( 'Y-m-d H:i:s', $after ),
					),
				),
			)
		);

		$out = array();
		foreach ( $posts as $post ) {
			$out[] = array(
				'tag'   => $screen->id() . '-' . $post->ID,
				'title' => $title,
				'body'  => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
				'url'   => $screen->url( array( 'view' => $post->ID ) ),
			);
		}

		return $out;
	}

	/** Sends the service worker (Router, at <slug>/sw/), allowed to control the whole panel. */
	public static function send_worker(): void {
		header( 'Content-Type: application/javascript; charset=utf-8' );
		header( 'Service-Worker-Allowed: ' . (string) wp_parse_url( Router::url(), PHP_URL_PATH ) );
		header( 'Cache-Control: no-cache' );
		readfile( LENZ_PLUS_DIR . 'assets/modules/panel/js/sw.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- our own bundled script.
	}
}
