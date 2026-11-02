<?php
/**
 * TeraWallet (formerly WooWallet, plugin `woo-wallet`) in the panel.
 *
 * Balances and transactions go through the plugin's own API
 * (woo_wallet()->wallet, get_wallet_transactions()), so its caches, emails
 * and hooks run as in wp-admin. The settings card edits a curated set of the
 * plugin's options (top-up, partial payment, transfers, cashback) and keeps
 * every other key of those options untouched. Ported from Daftar's
 * Integrations\Wallet.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Integrations;

use LenzPlus\Modules\Panel\Shop\Money;

defined( 'ABSPATH' ) || exit;

/**
 * TeraWallet bridge.
 */
final class Wallet {

	/** Balance cache the plugin keeps in user meta (used only for sorting). */
	private const BALANCE_META = '_current_woo_wallet_balance';

	/**
	 * Settings we edit, by option: key => type (`switch` = 'on'/'off', `number`, or a list of choices).
	 */
	public const FIELDS = array(
		'_wallet_settings_general' => array(
			'is_enable_wallet_topup'    => 'switch',
			'min_topup_amount'          => 'number',
			'max_topup_amount'          => 'number',
			'is_enable_partial_payment' => 'switch',
			'is_enable_wallet_transfer' => 'switch',
			'min_transfer_amount'       => 'number',
			'max_transfer_amount'       => 'number',
		),
		'_wallet_settings_credit'  => array(
			'is_enable_cashback_reward_program' => 'switch',
			'cashback_rule'                     => array( 'cart', 'product', 'product_cat' ),
			'cashback_type'                     => array( 'percent', 'fixed' ),
			'cashback_amount'                   => 'number',
			'min_cart_amount'                   => 'number',
			'max_cashback_amount'               => 'number',
		),
	);

	/** Whether TeraWallet is active. */
	public static function active(): bool {
		return function_exists( 'woo_wallet' ) && function_exists( 'get_wallet_transactions' );
	}

	/**
	 * A user's balance.
	 *
	 * @param int $user_id User ID.
	 */
	public static function balance( int $user_id ): float {
		return (float) woo_wallet()->wallet->get_wallet_balance( $user_id, 'edit' );
	}

	/**
	 * Money held in all wallets, how many people hold it, and this month's credits and debits.
	 *
	 * @return array{balance: float, users: int, month_credits: float, month_debits: float}
	 */
	public static function summary(): array {
		global $wpdb;
		$table = $wpdb->base_prefix . 'woo_wallet_transactions';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- read-only sums over the plugin's own table.
		$row   = $wpdb->get_row(
			"SELECT SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) AS credits, SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) AS debits, COUNT(DISTINCT user_id) AS users FROM {$table} WHERE deleted = 0",
			ARRAY_A
		);
		$month = $wpdb->get_row(
			$wpdb->prepare( "SELECT SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) AS credits, SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) AS debits FROM {$table} WHERE deleted = 0 AND date >= %s", gmdate( 'Y-m-01 00:00:00' ) ),
			ARRAY_A
		);
		// phpcs:enable

		return array(
			'balance'       => (float) ( $row['credits'] ?? 0 ) - (float) ( $row['debits'] ?? 0 ),
			'users'         => (int) ( $row['users'] ?? 0 ),
			'month_credits' => (float) ( $month['credits'] ?? 0 ),
			'month_debits'  => (float) ( $month['debits'] ?? 0 ),
		);
	}

	/**
	 * People with a wallet, highest balance first (or matching a search).
	 *
	 * @param int    $page     Page.
	 * @param int    $per_page Page size.
	 * @param string $search   Name or email.
	 * @return array{0: \WP_User[], 1: int}
	 */
	public static function users( int $page, int $per_page, string $search ): array {
		$args = array(
			'number'      => $per_page,
			'paged'       => $page,
			'count_total' => true,
			'meta_key'    => self::BALANCE_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- the plugin's own sort key.
			'orderby'     => 'meta_value_num',
			'order'       => 'DESC',
		);
		if ( '' !== $search ) {
			$args['search']         = '*' . $search . '*';
			$args['search_columns'] = array( 'user_login', 'user_email', 'display_name' );
			unset( $args['meta_key'], $args['orderby'] );
		}

		$query = new \WP_User_Query( $args );

		return array( (array) $query->get_results(), (int) $query->get_total() );
	}

	/**
	 * Transactions, newest first (everyone, or one person).
	 *
	 * @param int $user_id  User ID, 0 for everyone.
	 * @param int $page     Page.
	 * @param int $per_page Page size.
	 * @return array{0: object[], 1: int}
	 */
	public static function transactions( int $user_id, int $page, int $per_page ): array {
		$rows  = (array) get_wallet_transactions(
			array(
				'user_id' => $user_id ? $user_id : '',
				'limit'   => ( ( $page - 1 ) * $per_page ) . ',' . $per_page,
				'nocache' => true,
			)
		);
		$total = function_exists( 'get_wallet_transactions_count' ) ? (int) get_wallet_transactions_count( $user_id ? $user_id : null ) : count( $rows );

		return array( $rows, $total );
	}

	/**
	 * Credits or debits a wallet by hand.
	 *
	 * @param int    $user_id User ID.
	 * @param string $type    `credit` or `debit`.
	 * @param float  $amount  Amount.
	 * @param string $details Note the customer sees.
	 * @return int|false Transaction ID, or false when the plugin refused (locked wallet, low balance).
	 */
	public static function adjust( int $user_id, string $type, float $amount, string $details ) {
		$wallet = woo_wallet()->wallet;

		return 'debit' === $type ? $wallet->debit( $user_id, $amount, $details ) : $wallet->credit( $user_id, $amount, $details );
	}

	/**
	 * The curated settings, read from the plugin's options.
	 *
	 * @return array<string, bool|string>
	 */
	public static function settings(): array {
		$out = array();
		foreach ( self::FIELDS as $option => $fields ) {
			$saved = get_option( $option, array() );
			foreach ( $fields as $key => $type ) {
				$value       = is_array( $saved ) && isset( $saved[ $key ] ) ? $saved[ $key ] : '';
				$out[ $key ] = 'switch' === $type ? 'on' === $value : (string) $value;
			}
		}

		return $out;
	}

	/**
	 * Saves the curated settings into the plugin's options, keeping its other keys.
	 *
	 * @param array $input Values by key (switches missing = off).
	 */
	public static function save_settings( array $input ): void {
		foreach ( self::FIELDS as $option => $fields ) {
			$saved = get_option( $option, array() );
			$saved = is_array( $saved ) ? $saved : array();
			foreach ( $fields as $key => $type ) {
				if ( 'switch' === $type ) {
					$saved[ $key ] = ! empty( $input[ $key ] ) ? 'on' : 'off';
				} elseif ( ! isset( $input[ $key ] ) || ! is_scalar( $input[ $key ] ) ) {
					continue;
				} elseif ( 'number' === $type ) {
					$saved[ $key ] = Money::parse( $input[ $key ] );
				} elseif ( in_array( (string) $input[ $key ], $type, true ) ) {
					$saved[ $key ] = (string) $input[ $key ];
				}
			}
			update_option( $option, $saved );
		}
	}
}
