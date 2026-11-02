<?php
/**
 * Amounts in the panel: the store's own formatting as plain text (lists,
 * tiles, CSV), and numbers typed by people (Persian or Arabic digits,
 * thousands separators) as decimals WooCommerce accepts.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Shop;

use LenzPlus\Core\Persian;

defined( 'ABSPATH' ) || exit;

/**
 * Money helpers of the panel.
 */
final class Money {

	/**
	 * An amount as plain text with the store's separators and symbol
	 * («۱۲۰,۰۰۰ تومان»).
	 *
	 * @param float  $amount   Amount.
	 * @param string $currency Currency code ('' = the store's).
	 */
	public static function text( float $amount, string $currency = '' ): string {
		$number = number_format( abs( $amount ), wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() );
		$symbol = html_entity_decode( get_woocommerce_currency_symbol( $currency ), ENT_QUOTES, 'UTF-8' );
		$number = Persian::is_site_persian() ? Persian::digits( $number ) : $number;
		$sign   = $amount < 0 ? '−' : '';

		// Right-to-left sites read the amount first («۱۲۰,۰۰۰ تومان»), like the shop pages show it.
		if ( is_rtl() || in_array( get_option( 'woocommerce_currency_pos', 'left' ), array( 'right', 'right_space' ), true ) ) {
			return $sign . $number . ' ' . $symbol;
		}

		return $sign . $symbol . ' ' . $number;
	}

	/**
	 * A number typed by a person as a decimal string ('' when empty).
	 *
	 * @param mixed $value Raw value.
	 */
	public static function parse( $value ): string {
		$text = Persian::latin_digits( trim( (string) $value ) );
		$text = str_replace( array( '٫', '٬', ',', ' ' ), array( '.', '', '', '' ), $text );

		return '' === $text ? '' : wc_format_decimal( $text );
	}

	/**
	 * An amount rounded to the store's decimals.
	 *
	 * @param mixed $amount Amount.
	 */
	public static function round( $amount ): float {
		return round( (float) $amount, wc_get_price_decimals() );
	}

	/**
	 * A stored number shown with the site's digits in a form field ('' stays empty).
	 *
	 * @param string $number Number.
	 */
	public static function field( string $number ): string {
		return '' === $number ? '' : ( Persian::is_site_persian() ? Persian::digits( $number ) : $number );
	}
}
