<?php
/**
 * Wallet screen (TeraWallet): money held in wallets, people by balance, the
 * latest transactions, a person's page with a credit/debit form, and the
 * plugin's main settings (top-up, partial payment, transfers, cashback).
 *
 * Everything goes through Integrations\Wallet, which uses the plugin's own
 * API, so its emails and hooks run as in wp-admin. Ported from Daftar's
 * Wallet screen.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Modules\Panel\Activity\Log;
use LenzPlus\Modules\Panel\Activity\Watcher;
use LenzPlus\Modules\Panel\Integrations\Wallet as Wallet_Plugin;
use LenzPlus\Modules\Panel\Shop\Money;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * Wallets overview, person page and settings.
 */
final class Wallet extends Screen {

	/** Screen id. */
	public function id(): string {
		return 'wallet';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Wallet', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'wallet';
	}

	/** Store managers. */
	public function capability(): string {
		return 'manage_woocommerce';
	}

	/** Settings switch. */
	public function section(): string {
		return 'wallet';
	}

	/** Needs TeraWallet. */
	public function is_available(): bool {
		return Wallet_Plugin::active();
	}

	/** The person whose wallet is open, or null. */
	private static function person(): ?\WP_User {
		$id   = self::arg_int( 'user' );
		$user = $id ? get_userdata( $id ) : false;

		return $user ? $user : null;
	}

	/** The person's name on their page, «Wallet settings» on the settings page. */
	public function page_title(): string {
		if ( '1' === self::arg( 'settings' ) ) {
			return __( 'Wallet settings', 'lenz-plus' );
		}

		$user = self::person();

		return $user ? $user->display_name : $this->title();
	}

	/** Back to the overview. */
	public function back_url(): string {
		return self::arg_int( 'user' ) || '1' === self::arg( 'settings' ) ? $this->url() : '';
	}

	/** Settings button on the overview. */
	public function primary_action(): array {
		return '' === $this->back_url() ? array( $this->url( array( 'settings' => 1 ) ), __( 'Wallet settings', 'lenz-plus' ), 'settings' ) : array();
	}

	/** Prints the overview, a person's page or the settings. */
	public function render(): void {
		if ( '1' === self::arg( 'settings' ) ) {
			$this->render_settings();
			return;
		}

		if ( self::arg_int( 'user' ) ) {
			$user = self::person();
			if ( $user ) {
				$this->render_person( $user );
			} else {
				Ui::empty_state( __( 'This item does not exist.', 'lenz-plus' ) );
			}
			return;
		}

		$this->render_overview();
	}

	/**
	 * The wallet card on a customer's page: balance and a link here.
	 *
	 * @param int $user_id User ID.
	 */
	public function render_person_card( int $user_id ): void {
		?>
		<section class="lzp-card">
			<h2 class="lzp-card__title"><?php esc_html_e( 'Wallet', 'lenz-plus' ); ?></h2>
			<p class="lzp-strong"><?php echo esc_html( Money::text( Wallet_Plugin::balance( $user_id ) ) ); ?></p>
			<a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( $this->url( array( 'user' => $user_id ) ) ); ?>"><?php Ui::the_icon( 'wallet' ); ?><span><?php esc_html_e( 'Credit or debit', 'lenz-plus' ); ?></span></a>
		</section>
		<?php
	}

	/** Totals, people by balance and the latest transactions. */
	private function render_overview(): void {
		$summary = Wallet_Plugin::summary();
		Ui::stats(
			array(
				array( __( 'Held in wallets', 'lenz-plus' ), Money::text( $summary['balance'] ), 'wallet' ),
				array( __( 'People with a wallet', 'lenz-plus' ), Ui::number( $summary['users'] ), 'users' ),
				array( __( 'Credited this month', 'lenz-plus' ), Money::text( $summary['month_credits'] ), 'plus' ),
				array( __( 'Spent this month', 'lenz-plus' ), Money::text( $summary['month_debits'] ), 'receipt' ),
			)
		);

		$search                 = self::search_term();
		list( $people, $total ) = Wallet_Plugin::users( self::page_number(), $this->per_page(), $search );

		Ui::search( $this->url(), $search, __( 'Name or email', 'lenz-plus' ), $this->plain_args() );
		$rows = array();
		foreach ( $people as $user ) {
			$rows[] = array(
				'url'   => $this->url( array( 'user' => $user->ID ) ),
				'title' => $user->display_name,
				'meta'  => $user->user_email,
				'thumb' => (string) get_avatar_url( $user->ID, array( 'size' => 96 ) ),
				'aside' => Money::text( Wallet_Plugin::balance( $user->ID ) ),
			);
		}
		Ui::rows( $rows, '' !== $search ? __( 'Nothing matches this search.', 'lenz-plus' ) : __( 'Nobody has a wallet yet.', 'lenz-plus' ) );
		Ui::pagination( $this->url( array_filter( array( 's' => $search ) ) ), self::page_number(), (int) ceil( $total / $this->per_page() ) );

		if ( '' === $search && 1 === self::page_number() ) {
			echo '<section class="lzp-section"><h2 class="lzp-section__title">' . esc_html__( 'Latest transactions', 'lenz-plus' ) . '</h2>';
			$this->render_transactions( 0 );
			echo '</section>';
		}
	}

	/**
	 * Transactions as rows.
	 *
	 * @param int $user_id User ID, 0 for everyone.
	 */
	private function render_transactions( int $user_id ): void {
		list( $items ) = Wallet_Plugin::transactions( $user_id, 1, 15 );
		$rows          = array();
		foreach ( $items as $tx ) {
			$user   = get_userdata( (int) $tx->user_id );
			$credit = 'credit' === $tx->type;
			$rows[] = array(
				'url'   => $this->url( array( 'user' => (int) $tx->user_id ) ),
				'title' => wp_strip_all_tags( (string) $tx->details ) ? wp_strip_all_tags( (string) $tx->details ) : ( $credit ? __( 'Credit', 'lenz-plus' ) : __( 'Debit', 'lenz-plus' ) ),
				'meta'  => trim( ( $user_id || ! $user ? '' : $user->display_name . ' · ' ) . Ui::date( (int) strtotime( $tx->date . ' UTC' ), true ) ),
				'icon'  => 'wallet',
				'pill'  => Ui::pill( $credit ? 'completed' : 'pending', $credit ? __( 'Credit', 'lenz-plus' ) : __( 'Debit', 'lenz-plus' ) ),
				'aside' => Money::text( (float) $tx->amount ),
			);
		}
		Ui::rows( $rows, __( 'No transactions yet.', 'lenz-plus' ) );
	}

	/**
	 * A person's wallet: balance, credit/debit form and transactions.
	 *
	 * @param \WP_User $user Person.
	 */
	private function render_person( \WP_User $user ): void {
		$customers = $this->module->app()->screen( 'customers' );
		?>
		<div class="lzp-edit__grid">
			<div class="lzp-edit__main">
				<?php Ui::stats( array( array( __( 'Balance', 'lenz-plus' ), Money::text( Wallet_Plugin::balance( $user->ID ) ), 'wallet' ) ) ); ?>
				<section class="lzp-section">
					<h2 class="lzp-section__title"><?php esc_html_e( 'Transactions', 'lenz-plus' ); ?></h2>
					<?php $this->render_transactions( $user->ID ); ?>
				</section>
			</div>
			<aside class="lzp-edit__side">
				<section class="lzp-card">
					<h2 class="lzp-card__title"><?php esc_html_e( 'Credit or debit', 'lenz-plus' ); ?></h2>
					<?php $this->form_open( 'adjust' ); ?>
						<input type="hidden" name="user_id" value="<?php echo esc_attr( (string) $user->ID ); ?>">
						<?php
						Ui::choices(
							'type',
							__( 'What to do', 'lenz-plus' ),
							array(
								'credit' => __( 'Add money', 'lenz-plus' ),
								'debit'  => __( 'Take money', 'lenz-plus' ),
							),
							'credit'
						);
						Ui::input(
							'amount',
							__( 'Amount', 'lenz-plus' ),
							'',
							array(
								'inputmode' => 'decimal',
								'required'  => true,
							)
						);
						Ui::input( 'details', __( 'Note the customer sees', 'lenz-plus' ), '' );
						?>
						<button type="submit" class="lzp-btn lzp-btn--primary" data-lzp-confirm="<?php esc_attr_e( 'Change this wallet\'s balance?', 'lenz-plus' ); ?>"><?php esc_html_e( 'Save', 'lenz-plus' ); ?></button>
					</form>
				</section>
				<?php if ( $customers instanceof Customers ) : ?>
					<p><a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( $customers->url( array( 'user' => $user->ID ) ) ); ?>"><?php Ui::the_icon( 'user-circle' ); ?><span><?php esc_html_e( 'Customer\'s orders and notes', 'lenz-plus' ); ?></span></a></p>
				<?php endif; ?>
			</aside>
		</div>
		<?php
	}

	/** The plugin's main settings. */
	private function render_settings(): void {
		$values = Wallet_Plugin::settings();
		$num    = static function ( string $key ) use ( $values ): string {
			return Money::field( (string) $values[ $key ] );
		};

		$this->form_open( 'settings', 'lzp-edit' );
		?>
		<section class="lzp-card">
			<h2 class="lzp-card__title"><?php esc_html_e( 'Top-up and payment', 'lenz-plus' ); ?></h2>
			<?php Ui::toggle( 'is_enable_wallet_topup', __( 'Customers can top up their wallet', 'lenz-plus' ), (bool) $values['is_enable_wallet_topup'] ); ?>
			<div class="lzp-fields-2">
				<?php Ui::input( 'min_topup_amount', __( 'Smallest top-up', 'lenz-plus' ), $num( 'min_topup_amount' ), array( 'inputmode' => 'decimal' ) ); ?>
				<?php Ui::input( 'max_topup_amount', __( 'Largest top-up', 'lenz-plus' ), $num( 'max_topup_amount' ), array( 'inputmode' => 'decimal' ) ); ?>
			</div>
			<?php Ui::toggle( 'is_enable_partial_payment', __( 'Pay part of an order from the wallet', 'lenz-plus' ), (bool) $values['is_enable_partial_payment'] ); ?>
		</section>
		<section class="lzp-card">
			<h2 class="lzp-card__title"><?php esc_html_e( 'Transfers', 'lenz-plus' ); ?></h2>
			<?php Ui::toggle( 'is_enable_wallet_transfer', __( 'Customers can send money to each other', 'lenz-plus' ), (bool) $values['is_enable_wallet_transfer'] ); ?>
			<div class="lzp-fields-2">
				<?php Ui::input( 'min_transfer_amount', __( 'Smallest transfer', 'lenz-plus' ), $num( 'min_transfer_amount' ), array( 'inputmode' => 'decimal' ) ); ?>
				<?php Ui::input( 'max_transfer_amount', __( 'Largest transfer', 'lenz-plus' ), $num( 'max_transfer_amount' ), array( 'inputmode' => 'decimal' ) ); ?>
			</div>
		</section>
		<section class="lzp-card">
			<h2 class="lzp-card__title"><?php esc_html_e( 'Cashback', 'lenz-plus' ); ?></h2>
			<?php Ui::toggle( 'is_enable_cashback_reward_program', __( 'Give cashback on orders', 'lenz-plus' ), (bool) $values['is_enable_cashback_reward_program'] ); ?>
			<div class="lzp-fields-2">
				<?php
				Ui::select(
					'cashback_rule',
					__( 'Cashback is set per', 'lenz-plus' ),
					array(
						'cart'        => __( 'Cart', 'lenz-plus' ),
						'product'     => __( 'Product', 'lenz-plus' ),
						'product_cat' => __( 'Product category', 'lenz-plus' ),
					),
					(string) $values['cashback_rule']
				);
				Ui::select(
					'cashback_type',
					__( 'Cashback type', 'lenz-plus' ),
					array(
						'percent' => __( 'Percentage', 'lenz-plus' ),
						'fixed'   => __( 'Fixed amount', 'lenz-plus' ),
					),
					(string) $values['cashback_type']
				);
				Ui::input( 'cashback_amount', __( 'Cashback amount', 'lenz-plus' ), $num( 'cashback_amount' ), array( 'inputmode' => 'decimal' ) );
				Ui::input( 'min_cart_amount', __( 'Smallest cart for cashback', 'lenz-plus' ), $num( 'min_cart_amount' ), array( 'inputmode' => 'decimal' ) );
				Ui::input( 'max_cashback_amount', __( 'Most cashback per order', 'lenz-plus' ), $num( 'max_cashback_amount' ), array( 'inputmode' => 'decimal' ) );
				?>
			</div>
		</section>
		<div class="lzp-savebar">
			<button type="submit" class="lzp-btn lzp-btn--primary"><?php Ui::the_icon( 'check' ); ?><span><?php esc_html_e( 'Save', 'lenz-plus' ); ?></span></button>
		</div>
		</form>
		<?php
	}

	/**
	 * Credits/debits a wallet or saves the settings.
	 *
	 * @param string $action `adjust` or `settings`.
	 */
	public function handle( string $action ): string {
		if ( 'settings' === $action ) {
			$input = array();
			foreach ( Wallet_Plugin::FIELDS as $fields ) {
				foreach ( array_keys( $fields ) as $key ) {
					$input[ $key ] = self::posted( $key );
				}
			}
			Wallet_Plugin::save_settings( $input );
			Log::add( 'settings', 'wallet', 0, __( 'Wallet settings', 'lenz-plus' ) );

			return $this->done( 'saved', array( 'settings' => 1 ) );
		}

		$user = get_userdata( absint( self::posted( 'user_id' ) ) );
		if ( 'adjust' !== $action || ! $user ) {
			return $this->done( 'error' );
		}

		$back   = array( 'user' => $user->ID );
		$type   = 'debit' === self::posted( 'type' ) ? 'debit' : 'credit';
		$amount = (float) Money::parse( self::posted( 'amount' ) );
		if ( $amount <= 0 ) {
			return $this->done( 'amount', $back );
		}

		$details = sanitize_text_field( self::posted( 'details' ) );
		if ( ! Wallet_Plugin::adjust( $user->ID, $type, $amount, $details ) ) {
			return $this->done( 'wallet', $back );
		}

		Log::add( $type, 'wallet', $user->ID, $user->display_name, array( Watcher::change( 'amount', '', (string) $amount ) ) );

		return $this->done( 'saved', $back );
	}
}
