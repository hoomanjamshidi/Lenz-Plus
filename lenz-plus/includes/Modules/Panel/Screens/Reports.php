<?php
/**
 * Sales reports screen: a date range (presets or two dates), the totals
 * against the previous period of the same length, sales over time as bars,
 * the best products, categories, discount codes, payment methods, regions
 * and customers, sales per weekday, and stock alerts. Every table downloads
 * as CSV.
 *
 * Numbers come from Shop\Report (orders summed through WooCommerce's CRUD
 * API, cached for ten minutes and refreshed when orders change). Ported
 * from Daftar's Reports screen.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Core\Persian;
use LenzPlus\Modules\Panel\Shop\Csv;
use LenzPlus\Modules\Panel\Shop\Money;
use LenzPlus\Modules\Panel\Shop\Report;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * Sales report page.
 */
final class Reports extends Screen {

	/** Tables of the report → [ columns of the CSV ]. */
	private const TABLES = array(
		'series'     => array( 'date', 'orders', 'items', 'gross', 'net' ),
		'products'   => array( 'id', 'name', 'qty', 'gross' ),
		'categories' => array( 'id', 'label', 'qty', 'gross' ),
		'coupons'    => array( 'code', 'orders', 'discount', 'gross' ),
		'payments'   => array( 'label', 'orders', 'gross' ),
		'regions'    => array( 'label', 'orders', 'gross' ),
		'customers'  => array( 'name', 'email', 'orders', 'gross' ),
	);

	/** Screen id. */
	public function id(): string {
		return 'reports';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Sales reports', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'chart';
	}

	/** WooCommerce's reports capability. */
	public function capability(): string {
		return 'view_woocommerce_reports';
	}

	/** Settings switch. */
	public function section(): string {
		return 'reports';
	}

	/** Needs WooCommerce. */
	public function is_available(): bool {
		return function_exists( 'wc_get_orders' );
	}

	/**
	 * Range presets → label.
	 *
	 * @return array<string, string>
	 */
	private static function presets(): array {
		return array(
			'today' => __( 'Today', 'lenz-plus' ),
			'7d'    => __( '7 days', 'lenz-plus' ),
			''      => __( '30 days', 'lenz-plus' ),
			'month' => __( 'This month', 'lenz-plus' ),
			'90d'   => __( '90 days', 'lenz-plus' ),
			'year'  => __( 'This year', 'lenz-plus' ),
		);
	}

	/** The asked range (Report::range()). */
	private static function range(): array {
		$preset = self::arg( 'range' );
		$from   = self::date_arg( 'from' );
		$to     = self::date_arg( 'to' );
		if ( '' !== $from && '' !== $to ) {
			$preset = 'custom';
		}

		return Report::range( $preset, $from, $to );
	}

	/**
	 * A Y-m-d query argument ('' when missing or malformed).
	 *
	 * @param string $key Argument name.
	 */
	private static function date_arg( string $key ): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter.
		$value = isset( $_GET[ $key ] ) && is_scalar( $_GET[ $key ] ) ? Persian::latin_digits( sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) ) : '';

		return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : '';
	}

	/** The range arguments kept on links (CSV, chips). */
	private static function range_args(): array {
		return array_filter(
			array(
				'range' => self::arg( 'range' ),
				'from'  => self::date_arg( 'from' ),
				'to'    => self::date_arg( 'to' ),
			)
		);
	}

	/** Sends one table of the report as CSV. */
	public function download(): bool {
		$table = self::arg( 'table' );
		if ( ! $this->is_download() || ! isset( self::TABLES[ $table ] ) ) {
			return false;
		}

		$range  = self::range();
		$report = Report::build( $range['start'], $range['end'], $range['interval'] );
		$rows   = array();
		foreach ( $report[ $table ] ?? array() as $row ) {
			$cells = array();
			foreach ( self::TABLES[ $table ] as $column ) {
				$cells[] = $row[ $column ] ?? '';
			}
			$rows[] = $cells;
		}

		Csv::send( 'sales-' . $table . '-' . $range['start']->format( 'Ymd' ) . '-' . $range['end']->format( 'Ymd' ), self::TABLES[ $table ], $rows );

		return true;
	}

	/** Prints the report. */
	public function render(): void {
		$range    = self::range();
		$current  = Report::build( $range['start'], $range['end'], $range['interval'] );
		$previous = Report::build( $range['prev_start'], $range['prev_end'], $range['interval'], false );

		$this->render_range_picker( $range );
		$this->render_totals( $current['totals'], $previous['totals'] );

		if ( $current['partial'] ) {
			echo '<p class="lzp-card__note">' . esc_html__( 'This range has too many orders: the figures cover the first 20,000.', 'lenz-plus' ) . '</p>';
		}

		$this->render_series( $current['series'], $current['interval'] );
		?>
		<div class="lzp-report-grid">
			<?php
			$this->render_table( 'products', __( 'Best-selling products', 'lenz-plus' ), $current, 'name', 'gross', 'qty' );
			$this->render_table( 'categories', __( 'Categories', 'lenz-plus' ), $current, 'label', 'gross', 'qty' );
			$this->render_table( 'customers', __( 'Top customers', 'lenz-plus' ), $current, 'name', 'gross', 'orders' );
			$this->render_table( 'coupons', __( 'Discount codes used', 'lenz-plus' ), $current, 'code', 'discount', 'orders' );
			$this->render_table( 'payments', __( 'Payment methods', 'lenz-plus' ), $current, 'label', 'gross', 'orders' );
			$this->render_table( 'regions', __( 'Where orders come from', 'lenz-plus' ), $current, 'label', 'gross', 'orders' );
			$this->render_weekdays( $current['weekdays'] );
			$this->render_stock();
			?>
		</div>
		<?php
	}

	/**
	 * Preset chips and the two-date form.
	 *
	 * @param array $range Range.
	 */
	private function render_range_picker( array $range ): void {
		$custom = '' !== self::date_arg( 'from' ) && '' !== self::date_arg( 'to' );
		Ui::chips( $this->url(), 'range', self::presets(), $custom ? 'custom' : self::arg( 'range' ) );
		?>
		<details class="lzp-more"<?php echo $custom ? ' open' : ''; ?>>
			<summary><?php esc_html_e( 'Choose dates', 'lenz-plus' ); ?></summary>
			<form class="lzp-form" method="get" action="<?php echo esc_url( $this->url() ); ?>">
				<?php foreach ( $this->plain_args() as $name => $value ) : ?>
					<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
				<?php endforeach; ?>
				<div class="lzp-fields-2">
					<?php Ui::input( 'from', __( 'From', 'lenz-plus' ), $range['start']->format( 'Y-m-d' ), array( 'type' => 'date' ) ); ?>
					<?php Ui::input( 'to', __( 'To', 'lenz-plus' ), $range['end']->format( 'Y-m-d' ), array( 'type' => 'date' ) ); ?>
				</div>
				<button type="submit" class="lzp-btn lzp-btn--ghost"><?php esc_html_e( 'Show', 'lenz-plus' ); ?></button>
			</form>
		</details>
		<p class="lzp-card__note">
			<?php
			/* translators: 1: first day, 2: last day. */
			echo esc_html( sprintf( __( '%1$s to %2$s, compared with the period before.', 'lenz-plus' ), Ui::date( $range['start']->getTimestamp() ), Ui::date( $range['end']->getTimestamp() ) ) );
			?>
		</p>
		<?php
	}

	/**
	 * Total tiles with the change against the previous period.
	 *
	 * @param array $now  Totals.
	 * @param array $then Previous totals.
	 */
	private function render_totals( array $now, array $then ): void {
		$tiles = array(
			array( 'gross', __( 'Sales', 'lenz-plus' ), 'chart', true ),
			array( 'net', __( 'Net sales', 'lenz-plus' ), 'wallet', true ),
			array( 'orders', __( 'Orders', 'lenz-plus' ), 'receipt', false ),
			array( 'avg', __( 'Average order', 'lenz-plus' ), 'bag', true ),
			array( 'items', __( 'Items sold', 'lenz-plus' ), 'list', false ),
			array( 'customers', __( 'Customers', 'lenz-plus' ), 'users', false ),
			array( 'discount', __( 'Discounts', 'lenz-plus' ), 'tag', true ),
			array( 'refunds', __( 'Refunds', 'lenz-plus' ), 'undo', true ),
		);

		$stats = array();
		foreach ( $tiles as $tile ) {
			list( $key, $label, $icon, $money ) = $tile;
			$value                              = (float) ( $now[ $key ] ?? 0 );
			$before                             = (float) ( $then[ $key ] ?? 0 );
			$change                             = $before > 0 ? ( $value - $before ) / $before * 100 : null;
			$label                             .= null === $change ? '' : ' · ' . ( $change >= 0 ? '+' : '−' ) . Persian::number( abs( $change ), abs( $change ) < 10 ? 1 : 0 ) . '٪';
			$stats[]                            = array( $label, $money ? Money::text( $value ) : Ui::number( $value ), $icon );
		}

		if ( isset( $now['new_customers'] ) ) {
			/* translators: 1: new customers, 2: returning customers. */
			$stats[] = array( __( 'New / returning customers', 'lenz-plus' ), sprintf( __( '%1$s / %2$s', 'lenz-plus' ), Ui::number( $now['new_customers'] ), Ui::number( $now['returning_customers'] ) ), 'user-check' );
		}

		Ui::stats( $stats );
	}

	/**
	 * Sales over time as bars (with the amounts for screen readers).
	 *
	 * @param array  $series   Buckets: date, gross, orders.
	 * @param string $interval day, week or month.
	 */
	private function render_series( array $series, string $interval ): void {
		$max = 0.0;
		foreach ( $series as $point ) {
			$max = max( $max, (float) $point['gross'] );
		}
		$labels = array(
			'day'   => __( 'Sales per day', 'lenz-plus' ),
			'week'  => __( 'Sales per week', 'lenz-plus' ),
			'month' => __( 'Sales per month', 'lenz-plus' ),
		);
		$every  = max( 1, (int) ceil( count( $series ) / 8 ) );
		?>
		<section class="lzp-card">
			<div class="lzp-section__head">
				<h2 class="lzp-card__title"><?php echo esc_html( $labels[ $interval ] ?? $labels['day'] ); ?></h2>
				<a href="<?php echo esc_url( $this->download_url( array( 'table' => 'series' ) + self::range_args() ) ); ?>" data-lzp-noprefetch><?php esc_html_e( 'CSV', 'lenz-plus' ); ?></a>
			</div>
			<?php if ( $max <= 0 ) : ?>
				<?php Ui::empty_state( __( 'No sales in this range.', 'lenz-plus' ), 'chart' ); ?>
			<?php else : ?>
				<ol class="lzp-bars" style="--lzp-bars: <?php echo esc_attr( (string) count( $series ) ); ?>">
					<?php foreach ( array_values( $series ) as $i => $point ) : ?>
						<?php
						$time = (int) strtotime( $point['date'] . ' 12:00:00' );
						$text = Ui::date( $time ) . ': ' . Money::text( (float) $point['gross'] );
						?>
						<li style="--v: <?php echo esc_attr( (string) round( (float) $point['gross'] / $max, 4 ) ); ?>" title="<?php echo esc_attr( $text ); ?>">
							<span class="lzp-bars__bar" aria-hidden="true"></span>
							<span class="screen-reader-text"><?php echo esc_html( $text ); ?></span>
							<?php if ( 0 === $i % $every ) : ?>
								<span class="lzp-bars__label" aria-hidden="true"><?php echo esc_html( self::short_date( $time, $interval ) ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
		</section>
		<?php
	}

	/**
	 * A short axis label («۱۲ آبان», «آبان»).
	 *
	 * @param int    $time     Timestamp.
	 * @param string $interval day, week or month.
	 */
	private static function short_date( int $time, string $interval ): string {
		$full  = Ui::date( $time );
		$parts = preg_split( '/\s+/u', $full );

		if ( Persian::is_site_persian() && is_array( $parts ) && count( $parts ) >= 3 ) {
			// Jalali dates read «day month year».
			return 'month' === $interval ? $parts[1] : $parts[0] . ' ' . $parts[1];
		}

		return wp_date( 'month' === $interval ? 'M' : 'M j', $time );
	}

	/**
	 * One ranked table as lines, with its CSV link.
	 *
	 * @param string $table  Table key.
	 * @param string $title  Card title.
	 * @param array  $report Report.
	 * @param string $name   Field naming the row.
	 * @param string $amount Money field.
	 * @param string $count  Count field shown after the name.
	 */
	private function render_table( string $table, string $title, array $report, string $name, string $amount, string $count ): void {
		$rows = array_slice( (array) ( $report[ $table ] ?? array() ), 0, 8 );
		if ( ! $rows ) {
			return;
		}

		$lines = array();
		foreach ( $rows as $row ) {
			$label   = 'code' === $name ? strtoupper( (string) $row['code'] ) : (string) $row[ $name ];
			$lines[] = array( $label . ' × ' . Ui::number( (float) $row[ $count ] ), Money::text( (float) $row[ $amount ] ) );
		}
		?>
		<section class="lzp-card">
			<div class="lzp-section__head">
				<h2 class="lzp-card__title"><?php echo esc_html( $title ); ?></h2>
				<a href="<?php echo esc_url( $this->download_url( array( 'table' => $table ) + self::range_args() ) ); ?>" data-lzp-noprefetch><?php esc_html_e( 'CSV', 'lenz-plus' ); ?></a>
			</div>
			<?php Ui::lines( $lines ); ?>
		</section>
		<?php
	}

	/**
	 * Sales per weekday (starting on the site's first day of the week).
	 *
	 * @param array $weekdays Seven buckets: orders, gross (0 = Sunday).
	 */
	private function render_weekdays( array $weekdays ): void {
		global $wp_locale;

		$total = 0;
		foreach ( $weekdays as $day ) {
			$total += (int) $day['orders'];
		}
		if ( ! $total ) {
			return;
		}

		$start = (int) get_option( 'start_of_week', 6 );
		$lines = array();
		for ( $i = 0; $i < 7; $i++ ) {
			$day     = ( $start + $i ) % 7;
			$share   = (int) $weekdays[ $day ]['orders'] / $total * 100;
			$lines[] = array( $wp_locale->get_weekday( $day ), Persian::number( $share, 0 ) . '٪ · ' . Money::text( (float) $weekdays[ $day ]['gross'] ) );
		}
		?>
		<section class="lzp-card">
			<h2 class="lzp-card__title"><?php esc_html_e( 'Orders per weekday', 'lenz-plus' ); ?></h2>
			<?php Ui::lines( $lines ); ?>
		</section>
		<?php
	}

	/** Out-of-stock and low-stock products. */
	private function render_stock(): void {
		$stock = Report::stock();
		$items = array_merge( $stock['out'], $stock['low'] );
		if ( ! $items ) {
			return;
		}

		$products = $this->module->app()->screen( 'products' );
		$rows     = array();
		foreach ( array_slice( $items, 0, 10 ) as $item ) {
			$out    = 'outofstock' === $item['status'];
			$rows[] = array(
				'url'   => $products ? $products->url( array( 'edit' => $item['id'] ) ) : '',
				'title' => $item['name'],
				'thumb' => $item['image'],
				'icon'  => 'bag',
				'pill'  => Ui::pill( $out ? 'cancelled' : 'pending', $out ? __( 'Out of stock', 'lenz-plus' ) : __( 'Low stock', 'lenz-plus' ) ),
				'aside' => null === $item['stock'] ? '' : Ui::number( (int) $item['stock'] ),
			);
		}
		?>
		<section class="lzp-card">
			<h2 class="lzp-card__title">
				<?php
				/* translators: 1: out of stock count, 2: low stock count. */
				echo esc_html( sprintf( __( 'Stock: %1$s out, %2$s low', 'lenz-plus' ), Ui::number( $stock['counts']['outofstock'] ), Ui::number( $stock['counts']['low'] ) ) );
				?>
			</h2>
			<?php Ui::rows( $rows, '' ); ?>
		</section>
		<?php
	}
}
