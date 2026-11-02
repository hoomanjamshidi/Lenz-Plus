<?php
/**
 * Activity screen: who changed what and when — order statuses, refunds,
 * notes, tracking codes, product and discount code fields, published or
 * trashed content, moderated comments, booking statuses, wallet changes and
 * exports — from the panel, wp-admin or the site, with filters by kind and
 * by person, and a CSV download.
 *
 * Entries come from Activity\Log (fed by Activity\Watcher and the screens).
 * Ported from Daftar's Activity screen.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Modules\Panel\Activity\Log;
use LenzPlus\Modules\Panel\Shop\Csv;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * Activity log page.
 */
final class Activity extends Screen {

	/** Most entries in one CSV download. */
	private const EXPORT_LIMIT = 5000;

	/** Screen id. */
	public function id(): string {
		return 'activity';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Activity', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'clock';
	}

	/** People who manage others' work. */
	public function capability(): string {
		return 'edit_others_posts';
	}

	/** Settings switch (also switches the logging). */
	public function section(): string {
		return 'activity';
	}

	/**
	 * Object types → label.
	 *
	 * @return array<string, string>
	 */
	private static function types(): array {
		return array(
			'order'     => __( 'Orders', 'lenz-plus' ),
			'product'   => __( 'Products', 'lenz-plus' ),
			'post'      => __( 'Articles', 'lenz-plus' ),
			'portfolio' => __( 'Projects', 'lenz-plus' ),
			'page'      => __( 'Pages', 'lenz-plus' ),
			'coupon'    => __( 'Discount codes', 'lenz-plus' ),
			'customer'  => __( 'Customers', 'lenz-plus' ),
			'wallet'    => __( 'Wallet', 'lenz-plus' ),
			'booking'   => __( 'Bookings', 'lenz-plus' ),
			'comment'   => __( 'Comments', 'lenz-plus' ),
		);
	}

	/**
	 * Action keys → label.
	 *
	 * @return array<string, string>
	 */
	private static function actions(): array {
		return array(
			'status'   => __( 'changed the status of', 'lenz-plus' ),
			'created'  => __( 'created', 'lenz-plus' ),
			'updated'  => __( 'edited', 'lenz-plus' ),
			'trashed'  => __( 'moved to the trash', 'lenz-plus' ),
			'restored' => __( 'restored', 'lenz-plus' ),
			'refunded' => __( 'refunded', 'lenz-plus' ),
			'note'     => __( 'added a note to', 'lenz-plus' ),
			'tracking' => __( 'set the tracking code of', 'lenz-plus' ),
			'invoice'  => __( 'sent the invoice of', 'lenz-plus' ),
			'credit'   => __( 'credited the wallet of', 'lenz-plus' ),
			'debit'    => __( 'debited the wallet of', 'lenz-plus' ),
			'settings' => __( 'changed', 'lenz-plus' ),
			'export'   => __( 'downloaded', 'lenz-plus' ),
			'purge'    => __( 'cleared', 'lenz-plus' ),
		);
	}

	/**
	 * Sources → label.
	 *
	 * @return array<string, string>
	 */
	private static function sources(): array {
		return array(
			'panel' => __( 'in the panel', 'lenz-plus' ),
			'admin' => __( 'in wp-admin', 'lenz-plus' ),
			'site'  => __( 'on the site', 'lenz-plus' ),
		);
	}

	/** The filters asked for. */
	private static function filter(): array {
		$type = self::arg( 'type' );

		return array_filter(
			array(
				'object_type' => isset( self::types()[ $type ] ) ? $type : '',
				'user'        => self::arg_int( 'person' ),
			)
		);
	}

	/** Sends the filtered entries as CSV. */
	public function download(): bool {
		if ( ! $this->is_download() ) {
			return false;
		}

		list( $entries ) = Log::query( self::filter(), 1, self::EXPORT_LIMIT );
		$rows            = array();
		foreach ( $entries as $entry ) {
			$user   = get_userdata( $entry['user_id'] );
			$rows[] = array(
				wp_date( 'Y-m-d H:i', $entry['time'] ),
				$user ? $user->display_name : '',
				$entry['source'],
				$entry['action'],
				$entry['object_type'],
				$entry['label'],
				self::changes_text( $entry['changes'] ),
			);
		}

		Csv::send( 'activity', array( __( 'Date', 'lenz-plus' ), __( 'Person', 'lenz-plus' ), __( 'Where', 'lenz-plus' ), __( 'Action', 'lenz-plus' ), __( 'Kind', 'lenz-plus' ), __( 'Item', 'lenz-plus' ), __( 'Changes', 'lenz-plus' ) ), $rows );

		return true;
	}

	/** Prints the filters and the entries. */
	public function render(): void {
		$filter = self::filter();
		$keep   = array_filter(
			array(
				'type'   => $filter['object_type'] ?? '',
				'person' => $filter['user'] ?? 0,
			)
		);

		Ui::chips( $this->url( array_diff_key( $keep, array( 'type' => 1 ) ) ), 'type', array( '' => __( 'Everything', 'lenz-plus' ) ) + self::types(), $keep['type'] ?? '' );

		$people = Log::people();
		if ( count( $people ) > 1 ) {
			$choices = array( '' => __( 'Everyone', 'lenz-plus' ) );
			foreach ( $people as $id => $name ) {
				$choices[ (string) $id ] = $name;
			}
			Ui::chips( $this->url( array_diff_key( $keep, array( 'person' => 1 ) ) ), 'person', $choices, (string) ( $keep['person'] ?? '' ) );
		}

		list( $entries, $total ) = Log::query( $filter, self::page_number(), $this->per_page() );

		$rows = array();
		foreach ( $entries as $entry ) {
			$rows[] = $this->row( $entry );
		}
		Ui::rows( $rows, __( 'Nothing recorded yet. Changes made from now on show up here.', 'lenz-plus' ) );
		Ui::pagination( $this->url( $keep ), self::page_number(), (int) ceil( $total / $this->per_page() ) );

		if ( $rows ) {
			printf( '<p><a class="lzp-btn lzp-btn--ghost" href="%1$s" data-lzp-noprefetch>%2$s<span>%3$s</span></a></p>', esc_url( $this->download_url( $keep ) ), Ui::icon( 'download' ), esc_html__( 'Download as CSV (Excel)', 'lenz-plus' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG.
		}
	}

	/**
	 * One entry as a list row.
	 *
	 * @param array $entry Log entry.
	 */
	private function row( array $entry ): array {
		$user    = get_userdata( $entry['user_id'] );
		$actions = self::actions();
		$sources = self::sources();
		$meta    = array( Ui::date( $entry['time'], true ), $sources[ $entry['source'] ] ?? '' );
		$changes = self::changes_text( $entry['changes'] );
		if ( '' !== $changes ) {
			$meta[] = $changes;
		}

		return array(
			'url'   => $this->object_url( $entry ),
			/* translators: 1: person, 2: what they did («edited»), 3: the item. */
			'title' => sprintf( __( '%1$s %2$s %3$s', 'lenz-plus' ), $user ? $user->display_name : '—', $actions[ $entry['action'] ] ?? $entry['action'], $entry['label'] ),
			'meta'  => implode( ' · ', array_filter( $meta ) ),
			'thumb' => $user ? (string) get_avatar_url( $user->ID, array( 'size' => 96 ) ) : '',
			'icon'  => 'clock',
		);
	}

	/**
	 * The changes of an entry as short text («status: draft → publish»).
	 *
	 * @param array $changes Changes.
	 */
	private static function changes_text( array $changes ): string {
		$parts = array();
		foreach ( array_slice( $changes, 0, 3 ) as $change ) {
			if ( ! is_array( $change ) || ! isset( $change['field'] ) ) {
				continue;
			}
			$from    = (string) ( $change['from'] ?? '' );
			$to      = (string) ( $change['to'] ?? '' );
			$parts[] = $change['field'] . ': ' . ( '' !== $from ? $from . ' → ' : '' ) . $to;
		}

		return implode( '، ', $parts );
	}

	/**
	 * Where an entry's item opens in the panel ('' when it has no page there).
	 *
	 * @param array $entry Log entry.
	 */
	private function object_url( array $entry ): string {
		$app = $this->module->app();
		$id  = $entry['object_id'];
		$map = array(
			'order'     => array( 'orders', array( 'view' => $id ) ),
			'product'   => array( 'products', array( 'edit' => $id ) ),
			'post'      => array( 'posts', array( 'edit' => $id ) ),
			'portfolio' => array( 'portfolio', array( 'edit' => $id ) ),
			'page'      => array( 'pages', array( 'edit' => $id ) ),
			'coupon'    => array( 'coupons', array( 'edit' => $id ) ),
			'customer'  => array( 'customers', array( 'user' => $id ) ),
			'wallet'    => array( 'wallet', array( 'user' => $id ) ),
			'booking'   => array( 'bookings', array( 'view' => $id ) ),
			'students'  => array( 'students', array( 'course' => $id ) ),
		);
		if ( ! $id || ! isset( $map[ $entry['object_type'] ] ) ) {
			return '';
		}

		$screen = $app->screen( $map[ $entry['object_type'] ][0] );

		return $screen ? $screen->url( $map[ $entry['object_type'] ][1] ) : '';
	}
}
