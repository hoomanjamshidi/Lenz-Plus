<?php
/**
 * The requests screen («درخواست‌ها»): the `lzp_message` posts sent through
 * Request form widgets, with the form's answers, the page it was on and a
 * reply link.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Columns and actions of the requests list.
 */
final class Contact_Inbox extends Inbox {

	/** The requests post type. */
	protected function post_type(): string {
		return Contact_Messages::POST_TYPE;
	}

	/** «Requests». */
	protected function menu_title(): string {
		return __( 'Requests', 'lenz-plus' );
	}

	/** Time an admin last opened the list. */
	protected function seen_option(): string {
		return 'lenz_plus_messages_seen';
	}

	/**
	 * Sender, request, page and time columns.
	 *
	 * @param string[] $columns Default columns.
	 * @return string[]
	 */
	public function columns( $columns ): array {
		return array(
			'cb'       => $columns['cb'] ?? '<input type="checkbox">',
			'sender'   => __( 'From', 'lenz-plus' ),
			'message'  => __( 'Request', 'lenz-plus' ),
			'page'     => __( 'Page', 'lenz-plus' ),
			'received' => __( 'Received', 'lenz-plus' ),
		);
	}

	/**
	 * Prints one cell.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Request ID.
	 */
	public function render_column( $column, $post_id ): void {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		switch ( $column ) {
			case 'sender':
				$this->render_sender( $post, (string) get_post_meta( $post->ID, Contact_Messages::META_EMAIL, true ), (string) get_post_meta( $post->ID, Contact_Messages::META_PHONE, true ) );
				break;

			case 'message':
				$form = (string) get_post_meta( $post->ID, Contact_Messages::META_FORM, true );
				if ( '' !== $form ) {
					printf( '<strong>%s</strong><br>', esc_html( $form ) );
				}
				echo nl2br( esc_html( implode( "\n", Contact_Messages::answer_lines( (int) $post->ID ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped before nl2br().
				break;

			case 'page':
				$this->render_page( (int) get_post_meta( $post->ID, Contact_Messages::META_SOURCE, true ) );
				break;

			case 'received':
				$this->render_received( $post );
				break;
		}
	}

	/**
	 * Reply by email.
	 *
	 * @param \WP_Post $post Request.
	 * @return string[]
	 */
	protected function actions( \WP_Post $post ): array {
		return array(
			'reply' => self::reply_link( (string) get_post_meta( $post->ID, Contact_Messages::META_EMAIL, true ), (string) get_post_meta( $post->ID, Contact_Messages::META_FORM, true ) ),
		);
	}

	/** Readable answers. */
	protected function styles(): string {
		return '.post-type-lzp_message .column-message{width:45%;white-space:normal}.post-type-lzp_message .column-page,.post-type-lzp_message .column-received{width:14%}';
	}
}
