<?php
/**
 * Products screen: WooCommerce products other than courses, with price,
 * stock, gallery and categories.
 *
 * Products are written through WooCommerce's product objects (never raw
 * meta), so prices, stock status and lookup tables stay right. Prices and
 * stock accept Persian digits and thousands separators. Only simple products
 * get the price and stock fields; variable and other products keep those in
 * WooCommerce's editor, and the panel says so.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Core\Persian;
use LenzPlus\Modules\Builder\Course_Data;
use LenzPlus\Modules\Panel\Editor;
use LenzPlus\Modules\Panel\Ui;
use LenzPlus\Modules\Panel\Uploads;

defined( 'ABSPATH' ) || exit;

/**
 * Products list and editor (Courses extends it).
 */
class Products extends Content_Screen {

	/** Screen id. */
	public function id(): string {
		return 'products';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Products', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'bag';
	}

	/** Settings switch. */
	public function section(): string {
		return 'products';
	}

	/** Needs WooCommerce. */
	public function is_available(): bool {
		return class_exists( 'WooCommerce' ) && parent::is_available();
	}

	/** WooCommerce products. */
	protected function post_type(): string {
		return 'product';
	}

	/** Product categories. */
	protected function taxonomy(): string {
		return 'product_cat';
	}

	/** "New" label. */
	protected function new_label(): string {
		return __( 'New product', 'lenz-plus' );
	}

	/** Edit page title. */
	protected function edit_label(): string {
		return __( 'Edit product', 'lenz-plus' );
	}

	/** Empty list. */
	protected function empty_label(): string {
		return __( 'No products yet.', 'lenz-plus' );
	}

	/** Main text label. */
	protected function content_label(): string {
		return __( 'Description', 'lenz-plus' );
	}

	/** Whether this screen lists courses (else the other products). */
	protected function lists_courses(): bool {
		return false;
	}

	/**
	 * Only products on the right side of the course flag.
	 *
	 * @param string $status Status filter.
	 * @param string $search Search term.
	 */
	protected function list_query_args( string $status, string $search ): array {
		$args               = parent::list_query_args( $status, $search );
		$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- one flag, paged list.
			$this->lists_courses()
				? array(
					'key'   => Course_Data::META_FLAG,
					'value' => '1',
				)
				: array(
					'key'     => Course_Data::META_FLAG,
					'compare' => 'NOT EXISTS',
				),
		);

		return $args;
	}

	/**
	 * Rows show the price and an out-of-stock pill.
	 *
	 * @param \WP_Post $post Product.
	 */
	protected function row( \WP_Post $post ): array {
		$row     = parent::row( $post );
		$product = wc_get_product( $post->ID );
		if ( ! $product ) {
			return $row;
		}

		$row['aside'] = self::price_text( (string) $product->get_price() );
		if ( '' === $row['pill'] && ! $product->is_in_stock() ) {
			$row['pill'] = Ui::pill( 'cancelled', __( 'Out of stock', 'lenz-plus' ) );
		}

		return $row;
	}

	/**
	 * A price as plain text in the store's currency ('' when empty).
	 *
	 * @param string $price Price.
	 */
	protected static function price_text( string $price ): string {
		if ( '' === $price ) {
			return '';
		}

		return html_entity_decode( wp_strip_all_tags( wc_price( (float) $price ) ), ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Price, stock and gallery cards.
	 *
	 * @param \WP_Post|null $post Product, null for a new one.
	 */
	protected function render_fields( ?\WP_Post $post ): void {
		$product = $post ? wc_get_product( $post->ID ) : null;
		$simple  = ! $product || $product->is_type( 'simple' );
		$symbol  = html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' );
		?>
		<section class="lzp-card">
			<h2 class="lzp-card__title"><?php esc_html_e( 'Price and stock', 'lenz-plus' ); ?></h2>
			<?php if ( ! $simple ) : ?>
				<p class="lzp-card__note"><?php esc_html_e( 'This product has variations. Its prices and stock are set in WooCommerce\'s product editor.', 'lenz-plus' ); ?></p>
				<a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( (string) get_edit_post_link( $post->ID, 'url' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open WooCommerce\'s editor', 'lenz-plus' ); ?></a>
			<?php else : ?>
				<div class="lzp-fields-2">
					<?php
					/* translators: %s: currency symbol. */
					Ui::input( 'regular_price', sprintf( __( 'Price (%s)', 'lenz-plus' ), $symbol ), $product ? self::local_number( (string) $product->get_regular_price() ) : '', array( 'inputmode' => 'decimal' ) );
					Ui::input(
						'sale_price',
						/* translators: %s: currency symbol. */
						sprintf( __( 'Sale price (%s)', 'lenz-plus' ), $symbol ),
						$product ? self::local_number( (string) $product->get_sale_price() ) : '',
						array(
							'inputmode' => 'decimal',
							'help'      => __( 'Empty: no discount.', 'lenz-plus' ),
						)
					);
					?>
				</div>
				<?php $this->render_stock_fields( $product ); ?>
			<?php endif; ?>
		</section>
		<section class="lzp-card">
			<h2 class="lzp-card__title"><?php esc_html_e( 'Gallery', 'lenz-plus' ); ?></h2>
			<?php Uploads::field( 'gallery', __( 'More photos', 'lenz-plus' ), $product ? array_map( 'intval', $product->get_gallery_image_ids() ) : array(), array( 'multiple' => true ) ); ?>
		</section>
		<?php
	}

	/**
	 * Stock switch and quantity.
	 *
	 * @param \WC_Product|null $product Product, null for a new one.
	 */
	protected function render_stock_fields( $product ): void {
		Ui::toggle( 'manage_stock', __( 'Count the stock', 'lenz-plus' ), $product && $product->get_manage_stock() );
		Ui::input( 'stock', __( 'In stock', 'lenz-plus' ), $product && null !== $product->get_stock_quantity() ? (string) $product->get_stock_quantity() : '', array( 'inputmode' => 'numeric' ) );
		Ui::toggle( 'virtual', __( 'Nothing to ship', 'lenz-plus' ), $product && $product->is_virtual(), array( 'help' => __( 'For services and files: the checkout asks for no address.', 'lenz-plus' ) ) );
	}

	/**
	 * A stored number shown with the site's digits ('' stays empty).
	 *
	 * @param string $number Number.
	 */
	private static function local_number( string $number ): string {
		return '' === $number ? '' : ( Persian::is_site_persian() ? Persian::digits( $number ) : $number );
	}

	/**
	 * A typed number with Latin digits and no separators ('' when empty).
	 *
	 * @param string $key Field name.
	 */
	protected static function posted_number( string $key ): string {
		$text = Persian::latin_digits( trim( self::posted( $key ) ) );
		$text = str_replace( array( ',', '٬', ' ', '٫' ), array( '', '', '', '.' ), $text );

		return '' === $text ? '' : wc_format_decimal( $text );
	}

	/**
	 * Writes the product through WooCommerce.
	 *
	 * @param \WP_Post|null $post Existing product, null for a new one.
	 * @return int|\WP_Error Product ID.
	 */
	protected function save_post( ?\WP_Post $post ) {
		$product = $post ? wc_get_product( $post->ID ) : new \WC_Product_Simple();
		if ( ! $product ) {
			return new \WP_Error( 'lzp_no_product', __( 'The product could not be loaded.', 'lenz-plus' ) );
		}

		$product->set_name( sanitize_text_field( self::posted( 'title' ) ) );
		$product->set_status( $this->posted_status() );
		$product->set_short_description( wp_kses_post( self::posted( 'excerpt' ) ) );
		if ( $this->module->editor()->posts_content( 'product', $post ? (int) $post->ID : 0 ) ) {
			$product->set_description( Editor::posted() );
		}

		if ( $product->is_type( 'simple' ) ) {
			$this->set_price_and_stock( $product );
		}

		$this->prepare_product( $product );

		return $product->save();
	}

	/**
	 * Prices, stock and shipping of a simple product.
	 *
	 * @param \WC_Product $product Product.
	 */
	private function set_price_and_stock( $product ): void {
		$regular = self::posted_number( 'regular_price' );
		$sale    = self::posted_number( 'sale_price' );

		$product->set_regular_price( $regular );
		// A sale price above the price would show a "discount" that costs more.
		$product->set_sale_price( '' !== $sale && ( '' === $regular || (float) $sale < (float) $regular ) ? $sale : '' );

		$manage = '1' === self::posted( 'manage_stock' );
		$product->set_manage_stock( $manage );
		if ( $manage ) {
			$stock = self::posted_number( 'stock' );
			$product->set_stock_quantity( '' === $stock ? 0 : (int) $stock );
		}

		$product->set_virtual( '1' === self::posted( 'virtual' ) );
	}

	/**
	 * Last changes before the product is saved (Courses sets its own).
	 *
	 * @param \WC_Product $product Product.
	 */
	protected function prepare_product( $product ): void {
		unset( $product );
	}

	/**
	 * Saves the gallery.
	 *
	 * @param int $id Product ID.
	 */
	protected function save_fields( int $id ): void {
		$product = wc_get_product( $id );
		if ( $product ) {
			$product->set_gallery_image_ids( Uploads::posted_ids( 'gallery', $id ) );
			$product->save();
		}
	}
}
