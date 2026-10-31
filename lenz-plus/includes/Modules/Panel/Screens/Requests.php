<?php
/**
 * Requests screen: messages sent through the Request form. Requests newer
 * than the last visit are new; opening the list marks them seen, sharing
 * the "last seen" time with the wp-admin inbox.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Modules\Builder\Contact_Messages;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * Requests list and request page.
 */
final class Requests extends Inbox_Screen {

	/** Option of the wp-admin inbox (Contact_Inbox) with the last visit time. */
	private const SEEN_OPTION = 'lenz_plus_messages_seen';

	/** @var int Last visit before this one. */
	private $seen_before = -1;

	/** Screen id. */
	public function id(): string {
		return 'requests';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Requests', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'inbox';
	}

	/** Settings switch. */
	public function section(): string {
		return 'requests';
	}

	/** Request posts. */
	protected function post_type(): string {
		return Contact_Messages::POST_TYPE;
	}

	/** Empty list. */
	protected function empty_label(): string {
		return __( 'No requests yet. Messages sent through the Request form appear here.', 'lenz-plus' );
	}

	/** Requests after the last visit. */
	public function badge(): int {
		if ( ! $this->is_available() ) {
			return 0;
		}

		$after = $this->seen_before >= 0 ? $this->seen_before : (int) get_option( self::SEEN_OPTION, 0 );

		return count(
			get_posts(
				array(
					'post_type'      => $this->post_type(),
					'post_status'    => 'private',
					'posts_per_page' => 99,
					'fields'         => 'ids',
					'no_found_rows'  => true,
					'date_query'     => array(
						array(
							'column' => 'post_date_gmt',
							'after'  => gmdate( 'Y-m-d H:i:s', $after ),
						),
					),
				)
			)
		);
	}

	/** Marks the list seen (the rows still show what was new). */
	protected function render_list(): void {
		$this->seen_before = (int) get_option( self::SEEN_OPTION, 0 );
		update_option( self::SEEN_OPTION, time(), false );

		parent::render_list();
	}

	/**
	 * New requests get a pill.
	 *
	 * @param \WP_Post $post Request.
	 */
	protected function row( \WP_Post $post ): array {
		$row = parent::row( $post );
		if ( (int) get_post_time( 'U', true, $post ) > $this->seen_before ) {
			$row['pill'] = Ui::pill( 'new', __( 'New', 'lenz-plus' ) );
		}

		return $row;
	}
}
