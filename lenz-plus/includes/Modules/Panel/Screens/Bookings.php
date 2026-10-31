<?php
/**
 * Bookings screen: project bookings sent through the Project booking form,
 * with their tracking code and status (new, confirmed, done, cancelled).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Modules\Builder\Booking_Requests;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * Bookings list and booking page.
 */
final class Bookings extends Inbox_Screen {

	/** Screen id. */
	public function id(): string {
		return 'bookings';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Bookings', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'calendar';
	}

	/** Settings switch. */
	public function section(): string {
		return 'bookings';
	}

	/** Booking posts. */
	protected function post_type(): string {
		return Booking_Requests::POST_TYPE;
	}

	/** Empty list. */
	protected function empty_label(): string {
		return __( 'No bookings yet. Bookings sent through the Project booking form appear here.', 'lenz-plus' );
	}

	/** New bookings. */
	public function badge(): int {
		return Booking_Requests::count( 'new' );
	}

	/** The tracking code on a booking's page. */
	public function page_title(): string {
		$post = self::arg_int( 'view' ) ? $this->viewed() : null;

		return $post ? Booking_Requests::code_for( (int) $post->ID ) : $this->title();
	}

	/** The status chosen in the chips ('' = all). */
	private function status(): string {
		$status = self::arg( 'status' );

		return in_array( $status, Booking_Requests::STATUSES, true ) ? $status : '';
	}

	/** Status chips. */
	protected function render_filters(): void {
		Ui::chips( $this->url(), 'status', array( '' => __( 'All', 'lenz-plus' ) ) + Booking_Requests::status_labels(), $this->status() );
	}

	/** Filter by status. */
	protected function filter_args(): array {
		if ( '' === $this->status() ) {
			return array();
		}

		if ( 'new' === $this->status() ) {
			// Bookings without a stored status are new as well (Booking_Requests::status_of()).
			return array(
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- paged staff list.
					'relation' => 'OR',
					array(
						'key'   => Booking_Requests::META_STATUS,
						'value' => 'new',
					),
					array(
						'key'     => Booking_Requests::META_STATUS,
						'compare' => 'NOT EXISTS',
					),
				),
			);
		}

		return array(
			'meta_key'   => Booking_Requests::META_STATUS, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- paged staff list.
			'meta_value' => $this->status(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		);
	}

	/** Pages keep the status filter. */
	protected function list_url(): string {
		return $this->url( array_filter( array( 'status' => $this->status() ) ) );
	}

	/**
	 * Rows: code, service, date and status.
	 *
	 * @param \WP_Post $post Booking.
	 */
	protected function row( \WP_Post $post ): array {
		$row     = parent::row( $post );
		$status  = Booking_Requests::status_of( (int) $post->ID );
		$service = (string) get_post_meta( $post->ID, Booking_Requests::META_SERVICE, true );

		$row['title'] = Booking_Requests::code_for( (int) $post->ID ) . ' · ' . $row['title'];
		$row['meta']  = trim( $service . ' · ' . $row['meta'], ' ·' );
		$row['pill']  = Ui::pill( $status, Booking_Requests::status_labels()[ $status ] );

		return $row;
	}

	/**
	 * Status and service at the top of a booking.
	 *
	 * @param \WP_Post $post Booking.
	 */
	protected function render_item_head( \WP_Post $post ): void {
		$status  = Booking_Requests::status_of( (int) $post->ID );
		$service = (string) get_post_meta( $post->ID, Booking_Requests::META_SERVICE, true );
		?>
		<div class="lzp-order-head">
			<div>
				<?php echo Ui::pill( $status, Booking_Requests::status_labels()[ $status ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built by Ui::pill(). ?>
				<p class="lzp-card__note"><?php echo esc_html( Ui::date( (int) get_post_time( 'U', true, $post ), true ) ); ?></p>
			</div>
			<?php if ( '' !== $service ) : ?>
				<strong><?php echo esc_html( $service ); ?></strong>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Status buttons, then delete.
	 *
	 * @param \WP_Post $post Booking.
	 */
	protected function render_item_actions( \WP_Post $post ): void {
		$current = Booking_Requests::status_of( (int) $post->ID );
		?>
		<section class="lzp-card">
			<h2 class="lzp-card__title"><?php esc_html_e( 'Status', 'lenz-plus' ); ?></h2>
			<div class="lzp-actions lzp-actions--stack">
				<?php foreach ( Booking_Requests::status_labels() as $status => $label ) : ?>
					<?php if ( $status !== $current ) : ?>
						<?php
						$this->button_form(
							'status',
							array(
								'post_id' => $post->ID,
								'status'  => $status,
							),
							$label
						);
						?>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
		parent::render_item_actions( $post );
	}

	/**
	 * Status change.
	 *
	 * @param string $action Action name.
	 * @param int    $id     Booking ID.
	 */
	protected function handle_item( string $action, int $id ): string {
		$status = sanitize_key( self::posted( 'status' ) );
		if ( 'status' !== $action || ! in_array( $status, Booking_Requests::STATUSES, true ) ) {
			return $this->done( 'error', array( 'view' => $id ) );
		}

		update_post_meta( $id, Booking_Requests::META_STATUS, $status );

		return $this->done( 'status', array( 'view' => $id ) );
	}
}
