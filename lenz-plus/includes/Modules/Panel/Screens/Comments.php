<?php
/**
 * Comments screen: comments and product reviews waiting for approval or
 * already shown, with approve, hide, spam, trash and reply.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * Comment moderation.
 */
final class Comments extends Screen {

	/** List filters → get_comments() status. */
	private const FILTERS = array(
		'hold'    => 'hold',
		'approve' => 'approve',
		'spam'    => 'spam',
	);

	/** Screen id. */
	public function id(): string {
		return 'comments';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Comments', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'chat';
	}

	/** Moderators. */
	public function capability(): string {
		return 'moderate_comments';
	}

	/** Settings switch. */
	public function section(): string {
		return 'comments';
	}

	/** Comments waiting for approval. */
	public function badge(): int {
		return (int) wp_count_comments()->moderated;
	}

	/** Prints the filters and the comments. */
	public function render(): void {
		$filter = self::arg( 'status' );
		$filter = isset( self::FILTERS[ $filter ] ) ? $filter : 'hold';

		Ui::chips(
			$this->url(),
			'status',
			array(
				'hold'    => __( 'Waiting', 'lenz-plus' ),
				'approve' => __( 'Approved', 'lenz-plus' ),
				'spam'    => __( 'Spam', 'lenz-plus' ),
			),
			$filter
		);

		$args     = array(
			'status' => self::FILTERS[ $filter ],
			'number' => $this->per_page(),
			'offset' => ( self::page_number() - 1 ) * $this->per_page(),
			'type'   => array( 'comment', 'review' ),
		);
		$comments = get_comments( $args );
		$total    = (int) get_comments( array( 'count' => true ) + $args + array( 'offset' => 0 ) );

		if ( ! $comments ) {
			Ui::empty_state( 'hold' === $filter ? __( 'No comments are waiting. All caught up!', 'lenz-plus' ) : __( 'No comments here.', 'lenz-plus' ), 'chat' );
			return;
		}

		echo '<ul class="lzp-comments">';
		foreach ( $comments as $comment ) {
			$this->render_comment( $comment, $filter );
		}
		echo '</ul>';

		Ui::pagination( $this->url( array( 'status' => $filter ) ), self::page_number(), (int) ceil( $total / $this->per_page() ) );
	}

	/**
	 * One comment card with its actions.
	 *
	 * @param \WP_Comment $comment Comment.
	 * @param string      $filter  Current filter.
	 */
	private function render_comment( \WP_Comment $comment, string $filter ): void {
		$id   = (int) $comment->comment_ID;
		$post = get_post( (int) $comment->comment_post_ID );
		?>
		<li class="lzp-card lzp-comment">
			<header class="lzp-comment__head">
				<?php echo get_avatar( $comment, 40, '', '', array( 'class' => 'lzp-avatar' ) ); ?>
				<div>
					<strong><?php echo esc_html( get_comment_author( $comment ) ); ?></strong>
					<span class="lzp-card__note">
						<?php echo esc_html( Ui::date( (int) strtotime( $comment->comment_date_gmt . ' UTC' ), true ) ); ?>
						<?php if ( $post ) : ?>
							· <a href="<?php echo esc_url( (string) get_comment_link( $comment ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( get_the_title( $post ) ); ?></a>
						<?php endif; ?>
					</span>
				</div>
			</header>
			<div class="lzp-comment__text"><?php echo wp_kses_post( wpautop( $comment->comment_content ) ); ?></div>
			<div class="lzp-actions">
				<?php
				$fields = array(
					'comment_id' => $id,
					'filter'     => $filter,
				);
				if ( 'approve' !== $filter ) {
					$this->button_form( 'approve', $fields, __( 'Approve', 'lenz-plus' ), 'lzp-btn lzp-btn--primary' );
				} else {
					$this->button_form( 'hold', $fields, __( 'Hide', 'lenz-plus' ) );
				}
				if ( 'spam' !== $filter ) {
					$this->button_form( 'spam', $fields, __( 'Spam', 'lenz-plus' ) );
				}
				$this->button_form( 'trash', $fields, __( 'Trash', 'lenz-plus' ), 'lzp-btn lzp-btn--ghost lzp-btn--danger-text', __( 'Move this comment to the trash?', 'lenz-plus' ) );
				?>
			</div>
			<?php if ( 'spam' !== $filter ) : ?>
				<details class="lzp-more">
					<summary><?php esc_html_e( 'Reply', 'lenz-plus' ); ?></summary>
					<?php $this->form_open( 'reply' ); ?>
						<input type="hidden" name="comment_id" value="<?php echo esc_attr( (string) $id ); ?>">
						<input type="hidden" name="filter" value="<?php echo esc_attr( $filter ); ?>">
						<?php Ui::textarea( 'reply', __( 'Your reply', 'lenz-plus' ), '', array( 'rows' => 3 ) ); ?>
						<button type="submit" class="lzp-btn lzp-btn--primary"><?php esc_html_e( 'Send reply', 'lenz-plus' ); ?></button>
					</form>
				</details>
			<?php endif; ?>
		</li>
		<?php
	}

	/**
	 * Moderation actions and replies.
	 *
	 * @param string $action Action name.
	 */
	public function handle( string $action ): string {
		$id      = absint( self::posted( 'comment_id' ) );
		$comment = $id > 0 ? get_comment( $id ) : null;
		$filter  = sanitize_key( self::posted( 'filter' ) );
		$back    = array( 'status' => isset( self::FILTERS[ $filter ] ) ? $filter : 'hold' );

		if ( ! $comment instanceof \WP_Comment || ! current_user_can( 'edit_comment', (int) $comment->comment_ID ) ) {
			return $this->done( 'denied', $back );
		}

		switch ( $action ) {
			case 'approve':
			case 'hold':
				wp_set_comment_status( $id, $action );
				return $this->done( 'status', $back );

			case 'spam':
				wp_spam_comment( $id );
				return $this->done( 'status', $back );

			case 'trash':
				wp_trash_comment( $id );
				return $this->done( 'trashed', $back );

			case 'reply':
				return $this->done( $this->reply( $comment ) ? 'reply' : 'empty', $back );
		}

		return $this->url( $back );
	}

	/**
	 * Posts the current user's reply (approving the comment it answers).
	 *
	 * @param \WP_Comment $comment Comment answered.
	 */
	private function reply( \WP_Comment $comment ): bool {
		$text = trim( wp_kses_post( self::posted( 'reply' ) ) );
		if ( '' === $text ) {
			return false;
		}

		$user = wp_get_current_user();
		if ( '1' !== (string) $comment->comment_approved ) {
			wp_set_comment_status( (int) $comment->comment_ID, 'approve' );
		}

		return (bool) wp_insert_comment(
			wp_slash(
				array(
					'comment_post_ID'      => (int) $comment->comment_post_ID,
					'comment_parent'       => (int) $comment->comment_ID,
					'comment_content'      => $text,
					'comment_type'         => 'review' === $comment->comment_type ? 'comment' : $comment->comment_type,
					'user_id'              => $user->ID,
					'comment_author'       => $user->display_name,
					'comment_author_email' => $user->user_email,
					'comment_author_url'   => $user->user_url,
					'comment_approved'     => 1,
				)
			)
		);
	}
}
