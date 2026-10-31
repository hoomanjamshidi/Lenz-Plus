<?php
/**
 * WooCommerce's cart, checkout and account pages in the builder's brand
 * style, so a visitor who goes from a designed page to the shop does not
 * land in a different-looking site.
 *
 * Lenz prints these pages with its own templates and one stylesheet per page
 * (black buttons, nested dashed frames, a slider for the account menu). Its
 * markup stays; only its page stylesheets are swapped for shop.css, which
 * styles both its classic templates and WooCommerce's cart and checkout
 * blocks. A few additions make the flow easier: a step bar above the cart,
 * checkout and order-received pages, a success hero after the order (what
 * happens next, and where to go), and a plain account menu in place of
 * Lenz's slider (one item per screen on phones).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Styles, step bar and account menu of the WooCommerce pages.
 */
final class Shop_Pages {

	/** Lenz's per-page WooCommerce stylesheets that shop.css replaces (its global `lenz-wc` stays: mini cart, product pages). */
	private const THEME_STYLES = array( 'lenz-wc-cart', 'lenz-wc-checkout', 'lenz-checkout-rtl', 'lenz-wc-order', 'lenz-wc-my-account', 'lenz-login-wc' );

	/** Account endpoint → icon key of the menu. */
	private const ACCOUNT_ICONS = array(
		'dashboard'       => 'grid',
		'orders'          => 'receipt',
		'downloads'       => 'download',
		'edit-address'    => 'map-pin',
		'payment-methods' => 'wallet',
		'edit-account'    => 'settings',
		'wishlist'        => 'heart',
		'requests'        => 'notes',
		'customer-logout' => 'logout',
	);

	/** WooCommerce templates this replaces → view file. */
	private const TEMPLATES = array(
		// Lenz's slider menu → a plain list (a column on wide screens, a scrolling row on phones).
		'myaccount/navigation.php'    => 'account-nav.php',
		// The one-line "order received" notice → a success hero with next steps.
		'checkout/order-received.php' => 'order-received.php',
	);

	/** HTML id of the order's downloads table (shop.js sets it), the target of the success hero's download button. */
	public const DOWNLOADS_ID = 'lzp-downloads';

	/** @var Module */
	private $module;

	/** @var bool|null Whether this request is a styled shop page (worked out once). */
	private $applies;

	/** @var string Icon pack of the account menu (WooCommerce includes its view outside this object). */
	private static $icon_pack = 'lucide';

	/**
	 * @param Module $module Owning module.
	 */
	public function __construct( Module $module ) {
		$this->module = $module;
	}

	/** Hooks the assets, body classes, step bar and account menu. */
	public function register(): void {
		// After Lenz (10), so its page stylesheets are already queued when they are removed.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 20 );
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_filter( 'the_content', array( $this, 'add_steps' ), 20 );
		add_filter( 'wc_get_template', array( $this, 'templates' ), 10, 2 );
		add_action( 'wp', array( $this, 'add_translations' ) );
	}

	/** Fills WooCommerce strings its Persian translation lacks, on the shop pages only. */
	public function add_translations(): void {
		if ( $this->applies() ) {
			add_filter( 'gettext_woocommerce', array( $this, 'translate' ), 10, 2 );
		}
	}

	/**
	 * Lenz's checkout coupon box prints this WooCommerce sentence, which
	 * WooCommerce's fa_IR translation does not have (it stays English).
	 *
	 * @param string $translation Translated text.
	 * @param string $text        Original text.
	 */
	public function translate( $translation, $text ) {
		if ( $translation === $text && 'If you have a coupon code, please apply it below.' === $text ) {
			return __( 'If you have a coupon code, please apply it below.', 'lenz-plus' );
		}

		return $translation;
	}

	/**
	 * Cart, checkout (with order received and pay) and account pages, while
	 * the page templates and the option are on.
	 */
	public function applies(): bool {
		if ( null === $this->applies ) {
			$settings      = $this->module->settings();
			$this->applies = $this->module->is_enabled()
				&& ! empty( $settings['options']['shop_pages'] )
				&& function_exists( 'is_cart' )
				&& ( is_cart() || is_checkout() || is_account_page() );
		}

		return $this->applies;
	}

	/** Swaps Lenz's page stylesheets for shop.css. */
	public function enqueue(): void {
		if ( ! $this->applies() ) {
			return;
		}

		foreach ( self::THEME_STYLES as $handle ) {
			wp_dequeue_style( $handle );
		}

		wp_enqueue_style( Assets::SHOP_HANDLE );
		wp_enqueue_script( Assets::SHOP_HANDLE );
	}

	/**
	 * `lzp-shop` scopes shop.css; `lzp-shop--<page>` lets it lay out each page;
	 * `lzp-currency-first` tells it the store's prices start with the currency.
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public function body_class( array $classes ): array {
		if ( $this->applies() ) {
			$classes[] = 'lzp-shop';
			$classes[] = 'lzp-shop--' . $this->page();

			// The store prints the currency before the amount; shop.css shows Persian prices amount first.
			if ( in_array( get_option( 'woocommerce_currency_pos' ), array( 'left', 'left_space' ), true ) ) {
				$classes[] = 'lzp-currency-first';
			}
		}

		return $classes;
	}

	/**
	 * Prints the step bar above the cart, checkout and order-received
	 * content (classic shortcodes and blocks alike). An empty cart gets none:
	 * there is no order to walk through yet.
	 *
	 * @param string $content Page content.
	 */
	public function add_steps( $content ) {
		if ( ! $this->applies() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$current = array_search( $this->page(), array( 'cart', 'checkout', 'order' ), true );
		if ( false === $current || ( 0 === $current && WC()->cart && WC()->cart->is_empty() ) ) {
			return $content;
		}

		$steps = array(
			array(
				'label' => __( 'Cart', 'lenz-plus' ),
				'url'   => wc_get_cart_url(),
			),
			array(
				'label' => __( 'Details and payment', 'lenz-plus' ),
				'url'   => wc_get_checkout_url(),
			),
			array(
				'label' => __( 'Order complete', 'lenz-plus' ),
				'url'   => '',
			),
		);

		// After the order the earlier steps are history, not links.
		$linked = 2 !== $current;

		ob_start();
		require __DIR__ . '/views/shop/steps.php';

		return ob_get_clean() . $content;
	}

	/**
	 * Points WooCommerce at the plugin's views for the templates above while
	 * the shop pages are styled. Their hooks and classes stay WooCommerce's.
	 *
	 * @param string $template      Template path.
	 * @param string $template_name Template name relative to the templates folder.
	 */
	public function templates( $template, $template_name ) {
		if ( ! isset( self::TEMPLATES[ $template_name ] ) || ! $this->applies() ) {
			return $template;
		}

		self::$icon_pack = $this->module->settings()['options']['icon_pack'];

		return __DIR__ . '/views/shop/' . self::TEMPLATES[ $template_name ];
	}

	/**
	 * Built-in texts of the success hero, by scenario: `title` (empty =
	 * WooCommerce's own heading), one lead per order state (`course`,
	 * `download`, `paid`, `on_hold`, `pending`) and `email`, added after the
	 * lead when the order has an email. Placeholders: {name}, {order}, {email}.
	 *
	 * @return array<string, string>
	 */
	public static function default_thanks_texts(): array {
		return array(
			'title'    => '',
			'course'   => __( 'Your seat is booked. We will send the class details and how to join before it starts.', 'lenz-plus' ),
			'download' => __( 'Your files are ready. Download them below; the links are in your email too.', 'lenz-plus' ),
			'paid'     => __( 'Your payment went through and we are preparing your order.', 'lenz-plus' ),
			'on_hold'  => __( 'Your order is saved. It goes ahead as soon as your payment is confirmed; the payment details are below.', 'lenz-plus' ),
			'pending'  => __( 'Your order is saved, but its payment is not complete yet.', 'lenz-plus' ),
			/* translators: {email} stays as is: it is replaced with the customer's email address. */
			'email'    => __( 'A copy of the order is on its way to {email}.', 'lenz-plus' ),
		);
	}

	/**
	 * What the success hero says after the order: its heading, a line on
	 * what happens next (by order state; courses and downloads first, as the
	 * site sells those most), its buttons, and the courses enrolled in.
	 * Filter `lenz_plus_thanks_texts` changes the texts (the checkout module
	 * fills it from its settings).
	 *
	 * @param \WC_Order|false $order The order, or false when WooCommerce could not load it.
	 * @return array{title: string, lead: string, actions: array<int, array{label: string, url: string, primary: bool}>, courses: array<int, array{title: string, url: string, meta: string[]}>}
	 */
	public static function thanks( $order ): array {
		$texts   = array_merge( self::default_thanks_texts(), (array) apply_filters( 'lenz_plus_thanks_texts', self::default_thanks_texts(), $order ) );
		$shop_id = function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( 'shop' ) : 0;
		$actions = array(
			array(
				'label'   => __( 'Continue shopping', 'lenz-plus' ),
				'url'     => $shop_id > 0 ? (string) get_permalink( $shop_id ) : home_url( '/' ),
				'primary' => false,
			),
			array(
				'label'   => __( 'Back to the home page', 'lenz-plus' ),
				'url'     => home_url( '/' ),
				'primary' => false,
			),
		);

		if ( ! $order instanceof \WC_Order ) {
			$actions[0]['primary'] = true;

			return array(
				'title'   => '',
				'lead'    => '',
				'actions' => $actions,
				'courses' => array(),
			);
		}

		$courses   = self::order_courses( $order );
		$paid      = $order->is_paid();
		$downloads = $paid && $order->has_downloadable_item() && $order->is_download_permitted();

		if ( ! $paid ) {
			$scenario = 'on-hold' === $order->get_status() ? 'on_hold' : 'pending';
		} elseif ( array() !== $courses ) {
			$scenario = 'course';
		} else {
			$scenario = $downloads ? 'download' : 'paid';
		}

		$lead = trim( (string) $texts[ $scenario ] );
		if ( '' !== $order->get_billing_email() && '' !== trim( (string) $texts['email'] ) ) {
			$lead = trim( $lead . ' ' . $texts['email'] );
		}

		// The one thing to do next comes first: the files, else the order in the account, else the shop.
		if ( $downloads ) {
			array_unshift(
				$actions,
				array(
					'label'   => __( 'Download the files', 'lenz-plus' ),
					'url'     => '#' . self::DOWNLOADS_ID,
					'primary' => true,
				)
			);
		} elseif ( is_user_logged_in() && (int) $order->get_customer_id() === get_current_user_id() ) {
			array_unshift(
				$actions,
				array(
					'label'   => __( 'View my order', 'lenz-plus' ),
					'url'     => $order->get_view_order_url(),
					'primary' => true,
				)
			);
		} else {
			$actions[0]['primary'] = true;
		}

		return array(
			'title'   => self::fill( trim( (string) $texts['title'] ), $order ),
			'lead'    => self::fill( $lead, $order ),
			'actions' => array_slice( $actions, 0, 2 ),
			'courses' => $courses,
		);
	}

	/**
	 * Replaces the placeholders of a success text with the order's values.
	 *
	 * @param string    $text  Text with {name}, {order} and {email}.
	 * @param \WC_Order $order Order.
	 */
	private static function fill( string $text, \WC_Order $order ): string {
		return strtr(
			$text,
			array(
				'{name}'  => $order->get_billing_first_name(),
				'{order}' => (string) $order->get_order_number(),
				'{email}' => $order->get_billing_email(),
			)
		);
	}

	/**
	 * The courses of an order, for the cards under the success message:
	 * title, link and what a student needs to know (format and place,
	 * schedule, length, instructor).
	 *
	 * @param \WC_Order $order Order.
	 * @return array<int, array{title: string, url: string, meta: string[]}>
	 */
	private static function order_courses( \WC_Order $order ): array {
		$courses = array();
		foreach ( $order->get_items() as $item ) {
			$id = $item instanceof \WC_Order_Item_Product ? (int) $item->get_product_id() : 0;
			if ( ! Course_Data::is_course( $id ) ) {
				continue;
			}

			$details    = Course_Data::details( $id );
			$instructor = Course_Data::instructor( $id );
			$meta       = array_filter(
				array(
					Course_Data::format_label( $details ),
					(string) $details['schedule'],
					(string) $details['duration'],
					$instructor ? $instructor['name'] : '',
				)
			);

			$courses[] = array(
				'title' => $item->get_name(),
				'url'   => (string) get_permalink( $id ),
				'meta'  => array_values( $meta ),
			);
		}

		return $courses;
	}

	/**
	 * Icon of an account menu item in the designs' icon style.
	 *
	 * @param string $endpoint Account endpoint.
	 */
	public static function account_icon( string $endpoint ): string {
		$key = isset( self::ACCOUNT_ICONS[ $endpoint ] ) ? self::ACCOUNT_ICONS[ $endpoint ] : 'list';

		return Icon::html( self::$icon_pack, $key, 'lzp-account-nav__icon' );
	}

	/** Which shop page this is: cart, checkout, order (received) or account. */
	private function page(): string {
		if ( is_cart() ) {
			return 'cart';
		}
		if ( is_order_received_page() ) {
			return 'order';
		}

		return is_checkout() ? 'checkout' : 'account';
	}
}
