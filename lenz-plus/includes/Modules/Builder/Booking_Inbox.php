<?php
/**
 * The bookings screen («رزروها»): project bookings with their tracking code,
 * project, answers and status. The status is changed from the row (one link
 * per next status) and the list can be narrowed to one status with the
 * links above it, like WordPress's own post statuses.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Columns, status filter and status actions of the bookings list.
 */
final class Booking_Inbox extends Inbox {

	/** Query argument narrowing the list to one status. */
	private const FILTER_ARG = 'lzp_status';

	/** Hooks the list plus its status filter. */
	public function register(): void {
		parent::register();

		add_filter( 'views_edit-' . Booking_Requests::POST_TYPE, array( $this, 'status_views' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_by_status' ) );
	}

	/** The bookings post type. */
	protected function post_type(): string {
		return Booking_Requests::POST_TYPE;
	}

	/** «Bookings». */
	protected function menu_title(): string {
		return __( 'Bookings', 'lenz-plus' );
	}

	/** Time an admin last opened the list. */
	protected function seen_option(): string {
		return 'lenz_plus_bookings_seen';
	}

	/**
	 * Customer, booking, status, page and time columns.
	 *
	 * @param string[] $columns Default columns.
	 * @return string[]
	 */
	public function columns( $columns ): array {
		return array(
			'cb'       => $columns['cb'] ?? '<input type="checkbox">',
			'sender'   => __( 'Customer', 'lenz-plus' ),
			'booking'  => __( 'Booking', 'lenz-plus' ),
			'status'   => __( 'Status', 'lenz-plus' ),
			'page'     => __( 'Page', 'lenz-plus' ),
			'received' => __( 'Received', 'lenz-plus' ),
		);
	}

	/**
	 * Prints one cell.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Booking ID.
	 */
	public function render_column( $column, $post_id ): void {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		switch ( $column ) {
			case 'sender':
				$this->render_sender( $post, (string) get_post_meta( $post->ID, Booking_Requests::META_EMAIL, true ), (string) get_post_meta( $post->ID, Booking_Requests::META_PHONE, true ) );
				break;

			case 'booking':
				$service = (string) get_post_meta( $post->ID, Booking_Requests::META_SERVICE, true );
				printf(
					'<code dir="ltr">%1$s</code>%2$s<br>%3$s',
					esc_html( Booking_Requests::code_for( (int) $post->ID ) ),
					'' !== $service ? ' <strong>' . esc_html( $service ) . '</strong>' : '',
					nl2br( esc_html( implode( "\n", Booking_Requests::answer_lines( (int) $post->ID ) ) ) )
				);
				break;

			case 'status':
				$status = Booking_Requests::status_of( (int) $post->ID );
				printf( '<span class="lzp-booking-status lzp-booking-status--%1$s">%2$s</span>', esc_attr( $status ), esc_html( Booking_Requests::status_labels()[ $status ] ) );
				break;

			case 'page':
				$this->render_page( (int) get_post_meta( $post->ID, Booking_Requests::META_SOURCE, true ) );
				break;

			case 'received':
				$this->render_received( $post );
				break;
		}
	}

	/**
	 * Reply by email and a link to each other status.
	 *
	 * @param \WP_Post $post Booking.
	 * @return string[]
	 */
	protected function actions( \WP_Post $post ): array {
		$actions = array(
			'reply' => self::reply_link( (string) get_post_meta( $post->ID, Booking_Requests::META_EMAIL, true ), Booking_Requests::code_for( (int) $post->ID ) ),
		);

		$current = Booking_Requests::status_of( (int) $post->ID );
		foreach ( Booking_Requests::status_labels() as $status => $label ) {
			if ( $status !== $current ) {
				$actions[ 'status-' . $status ] = sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( Booking_Requests::status_url( (int) $post->ID, $status ) ),
					/* translators: %s: booking status, e.g. "Confirmed". */
					esc_html( sprintf( __( 'Mark as %s', 'lenz-plus' ), $label ) )
				);
			}
		}

		return $actions;
	}

	/**
	 * One link per status above the list, with its count.
	 *
	 * @param string[] $views WordPress's views (All, Trash…).
	 * @return string[]
	 */
	public function status_views( $views ): array {
		$current = self::current_filter();
		$base    = admin_url( 'edit.php?post_type=' . Booking_Requests::POST_TYPE );

		// Every booking is a private post: WordPress's "Private" view would repeat "All".
		unset( $views['private'] );

		foreach ( Booking_Requests::status_labels() as $status => $label ) {
			$views[ 'lzp-' . $status ] = sprintf(
				'<a href="%1$s"%2$s>%3$s <span class="count">(%4$s)</span></a>',
				esc_url( add_query_arg( self::FILTER_ARG, $status, $base ) ),
				$status === $current ? ' class="current" aria-current="page"' : '',
				esc_html( $label ),
				esc_html( number_format_i18n( Booking_Requests::count( $status ) ) )
			);
		}

		// "All" is not current while a status is chosen.
		if ( '' !== $current && isset( $views['all'] ) ) {
			$views['all'] = str_replace( array( ' class="current"', ' aria-current="page"' ), '', $views['all'] );
		}

		return $views;
	}

	/**
	 * Narrows the list to the chosen status.
	 *
	 * @param \WP_Query $query Query.
	 */
	public function filter_by_status( $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() || ! $this->is_list() ) {
			return;
		}

		$status = self::current_filter();
		if ( '' === $status ) {
			return;
		}

		$query->set(
			'meta_query',
			array(
				array(
					'key'   => Booking_Requests::META_STATUS,
					'value' => $status,
				),
			)
		);
	}

	/** The status chosen above the list ('' for all). */
	private static function current_filter(): string {
		$status = isset( $_GET[ self::FILTER_ARG ] ) ? sanitize_key( wp_unslash( $_GET[ self::FILTER_ARG ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- list filter.

		return in_array( $status, Booking_Requests::STATUSES, true ) ? $status : '';
	}

	/** Readable answers and coloured status labels. */
	protected function styles(): string {
		return '.post-type-lzp_booking .column-booking{width:40%;white-space:normal}.post-type-lzp_booking .column-status{width:10%}.post-type-lzp_booking .column-page,.post-type-lzp_booking .column-received{width:12%}'
			. '.lzp-booking-status{display:inline-block;padding:2px 10px;border-radius:12px;font-size:12px;font-weight:600;line-height:20px}'
			. '.lzp-booking-status--new{background:#fcf0e1;color:#8a4b00}.lzp-booking-status--confirmed{background:#e5f0fa;color:#0a4b78}.lzp-booking-status--done{background:#e6f4ea;color:#1e6b34}.lzp-booking-status--cancelled{background:#f0f0f1;color:#50575e}';
	}
}
