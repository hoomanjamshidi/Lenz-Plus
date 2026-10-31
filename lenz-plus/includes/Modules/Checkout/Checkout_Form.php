<?php
/**
 * Billing fields of WooCommerce's checkout: which ones show and which are
 * required, a shorter form when nothing in the cart is shipped (downloads,
 * virtual products, courses), and Persian/Arabic digits turned into Latin ones.
 *
 * Works with the classic checkout (shortcode), which Lenz styles and which
 * Persian WooCommerce and Persian WooCommerce Shipping (PWS) need. Field
 * rules run after those plugins (`woocommerce_checkout_fields` 1000) and only
 * remove fields or change `required`, so their province/city lists and IDs
 * stay theirs; a field left on "default" is not touched at all. PWS's
 * district field follows the city field.
 *
 * Digits: WooCommerce validates phones and postcodes as Latin digits, and
 * gateways send the phone to the bank, so «۰۹۱۲…» must become «0912…».
 * Persian WooCommerce fixes phone and postcode in `$_POST` on
 * `woocommerce_checkout_process` (20); this does the same earlier (5) for
 * every number-like field, on the account's address and details forms, and
 * in the Store API requests of the block checkout. shop.js converts as the
 * visitor types, so the field shows what will be saved.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Checkout;

use LenzPlus\Core\Persian;

defined( 'ABSPATH' ) || exit;

/**
 * Checkout field rules and digit normalisation.
 */
final class Checkout_Form {

	/** Field names whose value is a number, an email or a code: their digits are made Latin. */
	private const LATIN_PATTERN = '/(phone|mobile|tel|postcode|zip|email|national|melli|code)/';

	/** @var Module */
	private $module;

	/**
	 * @param Module $module Owning module.
	 */
	public function __construct( Module $module ) {
		$this->module = $module;
	}

	/** Hooks the field rules and the digit fixes. */
	public function register(): void {
		add_filter( 'woocommerce_checkout_fields', array( $this, 'checkout_fields' ), 1000 );
		add_filter( 'woocommerce_enable_order_notes_field', array( $this, 'order_notes' ), 1000 );

		if ( $this->module->settings()['latin_digits'] ) {
			add_action( 'woocommerce_checkout_process', array( $this, 'latin_checkout_post' ), 5 );
			// WooCommerce saves the account forms on template_redirect (10).
			add_action( 'template_redirect', array( $this, 'latin_account_post' ), 5 );
			add_filter( 'rest_request_before_callbacks', array( $this, 'latin_store_api' ), 10, 3 );
		}
	}

	/**
	 * Applies the field modes to the billing fields, and gives number fields
	 * a left-to-right direction and a numeric keyboard.
	 *
	 * @param array $fields Checkout fields by group.
	 * @return array
	 */
	public function checkout_fields( $fields ) {
		if ( empty( $fields['billing'] ) || ! is_array( $fields['billing'] ) ) {
			return $fields;
		}

		$modes = $this->modes();
		foreach ( $fields['billing'] as $key => $field ) {
			$mode = $this->mode_of( (string) $key, $modes );

			if ( 'hidden' === $mode ) {
				unset( $fields['billing'][ $key ] );
				continue;
			}
			if ( 'default' !== $mode ) {
				$fields['billing'][ $key ]['required'] = 'required' === $mode;
			}
		}

		foreach ( array( 'billing', 'shipping' ) as $group ) {
			if ( ! empty( $fields[ $group ] ) ) {
				$fields[ $group ] = $this->number_inputs( $fields[ $group ] );
			}
		}

		if ( isset( $fields['order'] ) && 'default' !== $modes['notes'] && isset( $fields['order']['order_comments'] ) ) {
			$fields['order']['order_comments']['required'] = 'required' === $modes['notes'];
		}

		return $fields;
	}

	/**
	 * Hides the order notes when their mode says so.
	 *
	 * @param bool $enabled WooCommerce's answer.
	 */
	public function order_notes( $enabled ): bool {
		return 'hidden' === $this->modes()['notes'] ? false : (bool) $enabled;
	}

	/** Latin digits in the posted checkout before WooCommerce and other plugins read it. */
	public function latin_checkout_post(): void {
		$this->latin_post();
	}

	/** Latin digits in the account's address and details forms before WooCommerce saves them. */
	public function latin_account_post(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce checks the nonce when it saves; only digits change here.
		$action = isset( $_POST['action'] ) ? sanitize_key( wp_unslash( $_POST['action'] ) ) : '';
		if ( in_array( $action, array( 'edit_address', 'save_account_details' ), true ) ) {
			$this->latin_post();
		}
	}

	/**
	 * Latin digits in the addresses the block checkout sends to the Store API.
	 *
	 * @param mixed            $response Response so far (passed through).
	 * @param array            $handler  Route handler.
	 * @param \WP_REST_Request $request  Request.
	 * @return mixed
	 */
	public function latin_store_api( $response, $handler, $request ) {
		if ( ! $request instanceof \WP_REST_Request || 0 !== strpos( $request->get_route(), '/wc/store' ) ) {
			return $response;
		}

		foreach ( array( 'billing_address', 'shipping_address' ) as $param ) {
			$address = $request->get_param( $param );
			if ( is_array( $address ) ) {
				$request->set_param( $param, $this->latin_values( $address ) );
			}
		}

		return $response;
	}

	/**
	 * Whether nothing in the cart is shipped: downloadable and virtual
	 * products, and the Builder's courses (Course_Shop says they need no
	 * shipping). Asked per product, so a store that ships physical goods
	 * without WooCommerce's shipping settings keeps its address fields.
	 * Filter `lenz_plus_is_quick_order` lets another plugin answer.
	 */
	public static function is_quick_order(): bool {
		$quick = false;
		if ( function_exists( 'WC' ) && WC()->cart && ! WC()->cart->is_empty() ) {
			$quick = true;
			foreach ( WC()->cart->get_cart() as $item ) {
				$product = isset( $item['data'] ) ? $item['data'] : null;
				if ( ! $product instanceof \WC_Product || $product->needs_shipping() ) {
					$quick = false;
					break;
				}
			}
		}

		return (bool) apply_filters( 'lenz_plus_is_quick_order', $quick );
	}

	/** The field modes for this cart: the short set when nothing is shipped (when switched on), else the general set. */
	private function modes(): array {
		$settings = $this->module->settings();

		return $settings['quick'] && self::is_quick_order() ? $settings['quick_fields'] : $settings['fields'];
	}

	/**
	 * Mode of a billing field. Fields the settings do not list (added by
	 * other plugins) stay as they are, except PWS's district, which goes
	 * with the city.
	 *
	 * @param string $key   Field key (`billing_…`).
	 * @param array  $modes Field modes.
	 */
	private function mode_of( string $key, array $modes ): string {
		$name = (string) preg_replace( '/^billing_/', '', $key );
		if ( 'district' === $name ) {
			$name = 'city';
		}

		return isset( $modes[ $name ] ) ? $modes[ $name ] : 'default';
	}

	/**
	 * Phone and postcode fields read left to right and open the number pad.
	 *
	 * @param array $fields Fields of one group.
	 * @return array
	 */
	private function number_inputs( array $fields ): array {
		foreach ( $fields as $key => $field ) {
			$is_phone    = false !== strpos( (string) $key, 'phone' );
			$is_postcode = false !== strpos( (string) $key, 'postcode' );
			if ( ! $is_phone && ! $is_postcode ) {
				continue;
			}

			$attrs                               = isset( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ? $field['custom_attributes'] : array();
			$attrs['dir']                        = 'ltr';
			$attrs['inputmode']                  = $is_phone ? 'tel' : 'numeric';
			$fields[ $key ]['custom_attributes'] = $attrs;
		}

		return $fields;
	}

	/** Converts the digits of number-like fields in `$_POST`. */
	private function latin_post(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput -- WooCommerce verifies and sanitizes the form when it handles it; only digits change here, in place, so every later reader (WooCommerce, gateways, PWS) sees the same value.
		foreach ( $_POST as $key => $value ) {
			if ( is_string( $value ) && preg_match( self::LATIN_PATTERN, (string) $key ) ) {
				$_POST[ $key ] = Persian::latin_digits( $value );
			}
		}
		// phpcs:enable
	}

	/**
	 * Latin digits in the number-like values of an address.
	 *
	 * @param array $values Address fields.
	 * @return array
	 */
	private function latin_values( array $values ): array {
		foreach ( $values as $key => $value ) {
			if ( is_string( $value ) && preg_match( self::LATIN_PATTERN, (string) $key ) ) {
				$values[ $key ] = Persian::latin_digits( $value );
			}
		}

		return $values;
	}
}
