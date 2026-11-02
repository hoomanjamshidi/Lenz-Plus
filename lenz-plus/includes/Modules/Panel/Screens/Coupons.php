<?php
/**
 * Discount codes screen: WooCommerce coupons with state chips (active,
 * expired, off) and a form for the code, the discount, its limits and its
 * restrictions.
 *
 * Coupons are written through WC_Coupon (never raw meta), so WooCommerce's
 * own checks and lookups stay right. Switching a code off keeps it as a
 * draft. Ported from Daftar's Coupons_Controller.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Core\Persian;
use LenzPlus\Modules\Panel\Shop\Money;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * Coupons list and editor.
 */
final class Coupons extends Screen {

	/** Screen id. */
	public function id(): string {
		return 'coupons';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Discount codes', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'tag';
	}

	/** WooCommerce's coupon capability. */
	public function capability(): string {
		return 'edit_shop_coupons';
	}

	/** Settings switch. */
	public function section(): string {
		return 'coupons';
	}

	/** Needs WooCommerce with coupons on. */
	public function is_available(): bool {
		return class_exists( 'WC_Coupon' ) && post_type_exists( 'shop_coupon' );
	}

	/** Whether the page edits (or creates) a code. */
	private function is_editing(): bool {
		return self::arg_int( 'edit' ) > 0 || '1' === self::arg( 'new' );
	}

	/** The coupon being edited, null for a new one or one the user cannot edit. */
	private function coupon(): ?\WC_Coupon {
		$id = self::arg_int( 'edit' );

		return $id && 'shop_coupon' === get_post_type( $id ) && current_user_can( 'edit_post', $id ) ? new \WC_Coupon( $id ) : null;
	}

	/** "New discount code" or "Edit discount code". */
	public function page_title(): string {
		if ( ! $this->is_editing() ) {
			return $this->title();
		}

		return self::arg_int( 'edit' ) ? __( 'Edit discount code', 'lenz-plus' ) : __( 'New discount code', 'lenz-plus' );
	}

	/** Back to the list. */
	public function back_url(): string {
		return $this->is_editing() ? $this->url() : '';
	}

	/** The "new" button. */
	public function primary_action(): array {
		$type = get_post_type_object( 'shop_coupon' );
		if ( $this->is_editing() || ! $type || ! current_user_can( $type->cap->create_posts ) ) {
			return array();
		}

		return array( $this->url( array( 'new' => 1 ) ), __( 'New discount code', 'lenz-plus' ), 'plus' );
	}

	/** Prints the list or the form. */
	public function render(): void {
		if ( ! $this->is_editing() ) {
			$this->render_list();
			return;
		}

		if ( self::arg_int( 'edit' ) && ! $this->coupon() ) {
			Ui::empty_state( __( 'This item does not exist or you cannot edit it.', 'lenz-plus' ) );
			return;
		}

		$this->render_form( $this->coupon() );
	}

	/** The list with state chips and search. */
	private function render_list(): void {
		$state  = self::arg( 'state' );
		$state  = in_array( $state, array( 'active', 'expired', 'draft' ), true ) ? $state : '';
		$search = self::search_term();
		$query  = new \WP_Query( self::list_args( $state, $search, self::page_number(), $this->per_page() ) );

		Ui::search( $this->url(), $search, __( 'Search codes…', 'lenz-plus' ), ( '' !== $state ? array( 'state' => $state ) : array() ) + $this->plain_args() );
		Ui::chips(
			$this->url( array_filter( array( 's' => $search ) ) ),
			'state',
			array(
				''        => __( 'All', 'lenz-plus' ),
				'active'  => __( 'Active', 'lenz-plus' ),
				'expired' => __( 'Expired', 'lenz-plus' ),
				'draft'   => __( 'Off', 'lenz-plus' ),
			),
			$state
		);

		$rows   = array();
		$labels = self::state_labels();
		foreach ( $query->posts as $post ) {
			$coupon  = new \WC_Coupon( $post->ID );
			$current = self::state( $coupon );
			$rows[]  = array(
				'url'   => $this->url( array( 'edit' => $coupon->get_id() ) ),
				'title' => strtoupper( $coupon->get_code() ),
				'meta'  => self::summary( $coupon ),
				'icon'  => 'tag',
				'pill'  => Ui::pill( 'active' === $current ? 'publish' : ( 'draft' === $current ? 'draft' : 'cancelled' ), $labels[ $current ] ),
				'aside' => self::amount_text( $coupon ),
			);
		}

		Ui::rows( $rows, '' !== $search ? __( 'Nothing matches this search.', 'lenz-plus' ) : __( 'No discount codes yet.', 'lenz-plus' ) );
		Ui::pagination(
			$this->url(
				array_filter(
					array(
						's'     => $search,
						'state' => $state,
					)
				)
			),
			self::page_number(),
			(int) $query->max_num_pages
		);
	}

	/**
	 * Query of the list.
	 *
	 * @param string $state    State filter ('' = all).
	 * @param string $search   Search term.
	 * @param int    $page     Page.
	 * @param int    $per_page Page size.
	 */
	private static function list_args( string $state, string $search, int $page, int $per_page ): array {
		$args = array(
			'post_type'      => 'shop_coupon',
			'post_status'    => 'draft' === $state ? 'draft' : array( 'publish', 'draft', 'pending', 'future' ),
			'posts_per_page' => $per_page,
			'paged'          => $page,
			's'              => $search,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$now = time();
		if ( 'expired' === $state ) {
			$args['post_status'] = 'publish';
			$args['meta_query']  = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- one key, paged list.
				array(
					'key'     => 'date_expires',
					'value'   => $now,
					'compare' => '<',
					'type'    => 'NUMERIC',
				),
			);
		} elseif ( 'active' === $state ) {
			$args['post_status'] = 'publish';
			$args['meta_query']  = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- one key, paged list.
				'relation' => 'OR',
				array(
					'key'     => 'date_expires',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'   => 'date_expires',
					'value' => '',
				),
				array(
					'key'     => 'date_expires',
					'value'   => $now,
					'compare' => '>=',
					'type'    => 'NUMERIC',
				),
			);
		}

		return $args;
	}

	/**
	 * State of a coupon: active, expired, used (limit reached) or draft (off).
	 *
	 * @param \WC_Coupon $coupon Coupon.
	 */
	private static function state( \WC_Coupon $coupon ): string {
		if ( 'publish' !== get_post_status( $coupon->get_id() ) ) {
			return 'draft';
		}

		$expires = $coupon->get_date_expires();
		if ( $expires && $expires->getTimestamp() < time() ) {
			return 'expired';
		}

		return $coupon->get_usage_limit() && $coupon->get_usage_count() >= $coupon->get_usage_limit() ? 'used' : 'active';
	}

	/**
	 * State labels.
	 *
	 * @return array<string, string>
	 */
	private static function state_labels(): array {
		return array(
			'active'  => __( 'Active', 'lenz-plus' ),
			'expired' => __( 'Expired', 'lenz-plus' ),
			'used'    => __( 'Used up', 'lenz-plus' ),
			'draft'   => __( 'Off', 'lenz-plus' ),
		);
	}

	/**
	 * The discount as text («۲۰٪» or an amount).
	 *
	 * @param \WC_Coupon $coupon Coupon.
	 */
	private static function amount_text( \WC_Coupon $coupon ): string {
		$amount = (float) $coupon->get_amount();

		return 'percent' === $coupon->get_discount_type() ? Persian::number( $amount, floor( $amount ) === $amount ? 0 : 1 ) . '٪' : Money::text( $amount );
	}

	/**
	 * The row's second line: uses, then the expiry date.
	 *
	 * @param \WC_Coupon $coupon Coupon.
	 */
	private static function summary( \WC_Coupon $coupon ): string {
		$limit = (int) $coupon->get_usage_limit();
		$used  = $limit
			/* translators: 1: times used, 2: usage limit. */
			? sprintf( __( 'Used %1$s of %2$s', 'lenz-plus' ), Ui::number( $coupon->get_usage_count() ), Ui::number( $limit ) )
			/* translators: %s: times used. */
			: sprintf( __( 'Used %s times', 'lenz-plus' ), Ui::number( $coupon->get_usage_count() ) );

		$expires = $coupon->get_date_expires();
		if ( $expires ) {
			/* translators: %s: expiry date. */
			$used .= ' · ' . sprintf( __( 'until %s', 'lenz-plus' ), Ui::date( $expires->getTimestamp() ) );
		}

		return $used;
	}

	/**
	 * The coupon form.
	 *
	 * @param \WC_Coupon|null $coupon Coupon, null for a new one.
	 */
	private function render_form( ?\WC_Coupon $coupon ): void {
		$num     = static function ( $value ): string {
			return '' === (string) $value || null === $value ? '' : Money::field( (string) (float) $value );
		};
		$expires = $coupon ? $coupon->get_date_expires() : null;
		$symbol  = html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' );

		$this->form_open( 'save', 'lzp-edit' );
		if ( $coupon ) {
			printf( '<input type="hidden" name="coupon_id" value="%d">', (int) $coupon->get_id() );
		}
		?>
		<div class="lzp-edit__grid">
			<div class="lzp-edit__main">
				<section class="lzp-card">
					<?php
					Ui::input(
						'code',
						__( 'Code', 'lenz-plus' ),
						$coupon ? $coupon->get_code() : '',
						array(
							'dir'      => 'ltr',
							'required' => true,
							'help'     => __( 'What customers type at checkout, e.g. NOROOZ20.', 'lenz-plus' ),
						)
					);
					?>
					<div class="lzp-fields-2">
						<?php
						Ui::select( 'type', __( 'Discount type', 'lenz-plus' ), wc_get_coupon_types(), $coupon ? $coupon->get_discount_type() : 'percent' );
						Ui::input(
							'amount',
							__( 'Amount', 'lenz-plus' ),
							$coupon ? $num( $coupon->get_amount() ) : '',
							array(
								'inputmode' => 'decimal',
								/* translators: %s: currency symbol. */
								'help'      => sprintf( __( 'A percentage, or an amount in %s.', 'lenz-plus' ), $symbol ),
							)
						);
						?>
					</div>
					<?php Ui::textarea( 'description', __( 'Note for the staff', 'lenz-plus' ), $coupon ? $coupon->get_description() : '', array( 'rows' => 2 ) ); ?>
				</section>

				<section class="lzp-card">
					<h2 class="lzp-card__title"><?php esc_html_e( 'Limits', 'lenz-plus' ); ?></h2>
					<div class="lzp-fields-2">
						<?php
						Ui::input(
							'expires',
							__( 'Last day', 'lenz-plus' ),
							$expires ? $expires->date( 'Y-m-d' ) : '',
							array(
								'type' => 'date',
								'help' => __( 'Empty: never expires.', 'lenz-plus' ),
							)
						);
						Ui::input( 'usage_limit', __( 'Uses in total', 'lenz-plus' ), $coupon && $coupon->get_usage_limit() ? Money::field( (string) $coupon->get_usage_limit() ) : '', array( 'inputmode' => 'numeric', 'help' => __( 'Empty: no limit.', 'lenz-plus' ) ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
						Ui::input( 'usage_limit_user', __( 'Uses per customer', 'lenz-plus' ), $coupon && $coupon->get_usage_limit_per_user() ? Money::field( (string) $coupon->get_usage_limit_per_user() ) : '', array( 'inputmode' => 'numeric' ) );
						/* translators: %s: currency symbol. */
						Ui::input( 'minimum', sprintf( __( 'Smallest cart (%s)', 'lenz-plus' ), $symbol ), $coupon ? $num( $coupon->get_minimum_amount() ) : '', array( 'inputmode' => 'decimal' ) );
						/* translators: %s: currency symbol. */
						Ui::input( 'maximum', sprintf( __( 'Largest cart (%s)', 'lenz-plus' ), $symbol ), $coupon ? $num( $coupon->get_maximum_amount() ) : '', array( 'inputmode' => 'decimal' ) );
						?>
					</div>
					<?php
					Ui::input(
						'emails',
						__( 'Only for these emails', 'lenz-plus' ),
						$coupon ? implode( ', ', $coupon->get_email_restrictions() ) : '',
						array(
							'dir'  => 'ltr',
							'help' => __( 'Separate several with commas. Empty: everyone.', 'lenz-plus' ),
						)
					);
					?>
				</section>

				<section class="lzp-card">
					<h2 class="lzp-card__title"><?php esc_html_e( 'Products', 'lenz-plus' ); ?></h2>
					<?php
					Ui::terms( 'product_cat', __( 'Only these categories (none chosen: every product)', 'lenz-plus' ), $coupon ? array_map( 'intval', $coupon->get_product_categories() ) : array() );
					Ui::toggle( 'exclude_sale', __( 'Not on products already on sale', 'lenz-plus' ), $coupon && $coupon->get_exclude_sale_items() );
					Ui::toggle( 'individual_use', __( 'Cannot be combined with other codes', 'lenz-plus' ), $coupon && $coupon->get_individual_use() );
					Ui::toggle( 'free_shipping', __( 'Free shipping', 'lenz-plus' ), $coupon && $coupon->get_free_shipping(), array( 'help' => __( 'Needs a free shipping method that accepts discount codes.', 'lenz-plus' ) ) );
					?>
				</section>
			</div>
			<aside class="lzp-edit__side">
				<section class="lzp-card">
					<?php Ui::toggle( 'enabled', __( 'Code is active', 'lenz-plus' ), ! $coupon || 'publish' === get_post_status( $coupon->get_id() ) ); ?>
					<?php if ( $coupon ) : ?>
						<p class="lzp-card__note"><?php echo esc_html( self::summary( $coupon ) ); ?></p>
					<?php endif; ?>
				</section>
			</aside>
		</div>
		<div class="lzp-savebar">
			<button type="submit" class="lzp-btn lzp-btn--primary" data-lzp-saving="<?php esc_attr_e( 'Saving…', 'lenz-plus' ); ?>"><?php Ui::the_icon( 'check' ); ?><span><?php esc_html_e( 'Save', 'lenz-plus' ); ?></span></button>
		</div>
		</form>
		<?php
		if ( $coupon && current_user_can( 'delete_post', $coupon->get_id() ) ) {
			echo '<section class="lzp-card lzp-card--danger">';
			$this->button_form( 'trash', array( 'coupon_id' => $coupon->get_id() ), __( 'Move to trash', 'lenz-plus' ), 'lzp-btn lzp-btn--danger', __( 'Move this item to the trash? You can restore it from WordPress\'s trash.', 'lenz-plus' ) );
			echo '</section>';
		}
	}

	/**
	 * Saves or trashes a coupon.
	 *
	 * @param string $action `save` or `trash`.
	 */
	public function handle( string $action ): string {
		$id = absint( self::posted( 'coupon_id' ) );
		if ( $id && ( 'shop_coupon' !== get_post_type( $id ) || ! current_user_can( 'trash' === $action ? 'delete_post' : 'edit_post', $id ) ) ) {
			return $this->done( 'denied' );
		}

		if ( 'trash' === $action ) {
			return $this->done( $id && wp_trash_post( $id ) ? 'trashed' : 'error' );
		}

		if ( 'save' !== $action ) {
			return $this->url();
		}

		$type = get_post_type_object( 'shop_coupon' );
		if ( ! $id && ( ! $type || ! current_user_can( $type->cap->create_posts ) ) ) {
			return $this->done( 'denied' );
		}

		$coupon = new \WC_Coupon( $id );
		$error  = self::fill( $coupon );
		$back   = $id ? array( 'edit' => $id ) : array( 'new' => 1 );
		if ( '' !== $error ) {
			return $this->done( $error, $back );
		}

		$coupon->save();
		wp_update_post(
			array(
				'ID'          => $coupon->get_id(),
				'post_status' => '1' === self::posted( 'enabled' ) ? 'publish' : 'draft',
			)
		);

		return $this->done( $id ? 'saved' : 'created', array( 'edit' => $coupon->get_id() ) );
	}

	/**
	 * Copies the posted fields onto the coupon; returns an error message key or ''.
	 *
	 * @param \WC_Coupon $coupon Coupon.
	 */
	private static function fill( \WC_Coupon $coupon ): string {
		$code = wc_format_coupon_code( Persian::latin_digits( self::posted( 'code' ) ) );
		if ( '' === $code ) {
			return 'no_code';
		}
		if ( wc_get_coupon_id_by_code( $code, $coupon->get_id() ) ) {
			return 'code_taken';
		}
		$coupon->set_code( $code );

		$type = sanitize_key( self::posted( 'type' ) );
		if ( array_key_exists( $type, wc_get_coupon_types() ) ) {
			$coupon->set_discount_type( $type );
		}

		$amount = (float) Money::parse( self::posted( 'amount' ) );
		if ( 'percent' === $coupon->get_discount_type() && $amount > 100 ) {
			return 'percent';
		}
		$coupon->set_amount( max( 0, $amount ) );
		$coupon->set_description( sanitize_textarea_field( self::posted( 'description' ) ) );

		$expires = sanitize_text_field( Persian::latin_digits( self::posted( 'expires' ) ) );
		$coupon->set_date_expires( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $expires ) ? $expires . ' 23:59:59' : null );

		$coupon->set_minimum_amount( Money::parse( self::posted( 'minimum' ) ) );
		$coupon->set_maximum_amount( Money::parse( self::posted( 'maximum' ) ) );
		foreach ( array(
			'usage_limit'      => 'set_usage_limit',
			'usage_limit_user' => 'set_usage_limit_per_user',
		) as $field => $setter ) {
			$limit = absint( Money::parse( self::posted( $field ) ) );
			$coupon->{$setter}( $limit ? $limit : null );
		}

		$coupon->set_free_shipping( '1' === self::posted( 'free_shipping' ) );
		$coupon->set_individual_use( '1' === self::posted( 'individual_use' ) );
		$coupon->set_exclude_sale_items( '1' === self::posted( 'exclude_sale' ) );

		$terms = self::posted_array( 'terms' );
		$coupon->set_product_categories( array_values( array_filter( array_map( 'absint', (array) ( $terms['product_cat'] ?? array() ) ) ) ) );

		$emails = preg_split( '/[,،\s]+/u', self::posted( 'emails' ) );
		$coupon->set_email_restrictions( array_values( array_filter( array_map( 'sanitize_email', (array) $emails ) ) ) );

		return '';
	}
}
