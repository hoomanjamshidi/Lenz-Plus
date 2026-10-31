<?php
/**
 * Base of the screens that read what visitors sent through the site's forms
 * (bookings and requests): a list, and a page with the answers and links to
 * call, text or email the sender.
 *
 * The stored posts are private and only administrators reach them in
 * wp-admin; the panel opens them to staff who can edit others' posts (filter
 * `lenz_plus_panel_inbox_capability`), since answering visitors is the
 * studio's daily work.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Core\Persian;
use LenzPlus\Modules\Support_Button\Channels;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * List and page of one kind of visitor posts.
 */
abstract class Inbox_Screen extends Screen {

	/** Post type of the stored posts. */
	abstract protected function post_type(): string;

	/** Message of the empty list. */
	abstract protected function empty_label(): string;

	/** Meta key of the sender's email. */
	protected function email_key(): string {
		return '_lzp_email';
	}

	/** Meta key of the sender's phone. */
	protected function phone_key(): string {
		return '_lzp_phone';
	}

	/** Staff who answer visitors. */
	public function capability(): string {
		return (string) apply_filters( 'lenz_plus_panel_inbox_capability', 'edit_others_posts' );
	}

	/** Only while the Builder stores these posts. */
	public function is_available(): bool {
		return post_type_exists( $this->post_type() );
	}

	/** The post being viewed, or null. */
	protected function viewed(): ?\WP_Post {
		$id   = self::arg_int( 'view' );
		$post = $id > 0 ? get_post( $id ) : null;

		return $post && $post->post_type === $this->post_type() ? $post : null;
	}

	/** Back to the list from a post. */
	public function back_url(): string {
		return self::arg_int( 'view' ) ? $this->url() : '';
	}

	/** Prints the list or one post. */
	public function render(): void {
		if ( ! self::arg_int( 'view' ) ) {
			$this->render_list();
			return;
		}

		$post = $this->viewed();
		if ( ! $post ) {
			Ui::empty_state( __( 'This item does not exist.', 'lenz-plus' ) );
			return;
		}

		$this->render_item( $post );
	}

	/**
	 * Extra list query arguments (status filters).
	 *
	 * @return array<string, mixed>
	 */
	protected function filter_args(): array {
		return array();
	}

	/** Prints the filters above the list. */
	protected function render_filters(): void {}

	/** The list. */
	protected function render_list(): void {
		$this->render_filters();

		$query = new \WP_Query(
			array(
				'post_type'      => $this->post_type(),
				'post_status'    => 'private',
				'posts_per_page' => $this->per_page(),
				'paged'          => self::page_number(),
				'orderby'        => 'date',
				'order'          => 'DESC',
			) + $this->filter_args()
		);

		$rows = array();
		foreach ( $query->posts as $post ) {
			$rows[] = $this->row( $post );
		}

		Ui::rows( $rows, $this->empty_label() );
		Ui::pagination( $this->list_url(), self::page_number(), (int) $query->max_num_pages );
	}

	/** URL of the list with its filters (for the pages). */
	protected function list_url(): string {
		return $this->url();
	}

	/**
	 * One list row.
	 *
	 * @param \WP_Post $post Stored post.
	 */
	protected function row( \WP_Post $post ): array {
		return array(
			'url'   => $this->url( array( 'view' => $post->ID ) ),
			'title' => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
			'meta'  => Ui::date( (int) get_post_time( 'U', true, $post ), true ),
			'icon'  => $this->icon(),
		);
	}

	/**
	 * Prints one post: contact buttons, answers, actions.
	 *
	 * @param \WP_Post $post Stored post.
	 */
	protected function render_item( \WP_Post $post ): void {
		$email  = (string) get_post_meta( $post->ID, $this->email_key(), true );
		$phone  = Persian::latin_digits( (string) get_post_meta( $post->ID, $this->phone_key(), true ) );
		$source = (string) get_post_meta( $post->ID, '_lzp_source', true );
		$pairs  = get_post_meta( $post->ID, '_lzp_fields', true );
		?>
		<div class="lzp-edit__grid">
			<div class="lzp-edit__main">
				<section class="lzp-card">
					<?php $this->render_item_head( $post ); ?>
					<dl class="lzp-answers">
						<?php foreach ( is_array( $pairs ) ? $pairs : array() as $pair ) : ?>
							<?php if ( is_array( $pair ) && 2 === count( $pair ) && '' !== trim( (string) $pair[1] ) ) : ?>
								<div>
									<dt><?php echo esc_html( (string) $pair[0] ); ?></dt>
									<dd><?php echo nl2br( esc_html( (string) $pair[1] ) ); ?></dd>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</dl>
					<?php if ( '' !== $source ) : ?>
						<p class="lzp-card__note">
							<?php esc_html_e( 'Sent from:', 'lenz-plus' ); ?>
							<a href="<?php echo esc_url( $source ); ?>" target="_blank" rel="noopener"><?php echo esc_html( rawurldecode( (string) wp_parse_url( $source, PHP_URL_PATH ) ) ); ?></a>
						</p>
					<?php endif; ?>
				</section>
			</div>
			<aside class="lzp-edit__side">
				<?php if ( '' !== $phone || '' !== $email ) : ?>
					<section class="lzp-card">
						<h2 class="lzp-card__title"><?php esc_html_e( 'Reply', 'lenz-plus' ); ?></h2>
						<div class="lzp-actions lzp-actions--stack">
							<?php if ( '' !== $phone ) : ?>
								<?php $tel = (string) preg_replace( '/[^\d+]/', '', $phone ); ?>
								<a class="lzp-btn lzp-btn--primary" href="<?php echo esc_url( 'tel:' . $tel ); ?>"><?php Ui::the_icon( 'phone' ); ?><span><?php esc_html_e( 'Call', 'lenz-plus' ); ?></span></a>
								<a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( 'sms:' . $tel ); ?>"><?php Ui::the_icon( 'chat' ); ?><span><?php esc_html_e( 'Text message', 'lenz-plus' ); ?></span></a>
								<?php if ( strlen( $tel ) >= 8 ) : ?>
									<a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( 'https://wa.me/' . Channels::international( $tel ) ); ?>" target="_blank" rel="noopener"><?php Ui::the_icon( 'whatsapp' ); ?><span><?php esc_html_e( 'WhatsApp', 'lenz-plus' ); ?></span></a>
								<?php endif; ?>
							<?php endif; ?>
							<?php if ( '' !== $email ) : ?>
								<a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php Ui::the_icon( 'mail' ); ?><span dir="ltr"><?php echo esc_html( $email ); ?></span></a>
							<?php endif; ?>
						</div>
					</section>
				<?php endif; ?>
				<?php $this->render_item_actions( $post ); ?>
			</aside>
		</div>
		<?php
	}

	/**
	 * Heading of a post's page.
	 *
	 * @param \WP_Post $post Stored post.
	 */
	protected function render_item_head( \WP_Post $post ): void {
		?>
		<p class="lzp-card__note"><?php echo esc_html( Ui::date( (int) get_post_time( 'U', true, $post ), true ) ); ?></p>
		<?php
	}

	/**
	 * Action cards of a post's page (status, delete).
	 *
	 * @param \WP_Post $post Stored post.
	 */
	protected function render_item_actions( \WP_Post $post ): void {
		echo '<section class="lzp-card lzp-card--danger">';
		$this->button_form( 'delete', array( 'post_id' => $post->ID ), __( 'Delete', 'lenz-plus' ), 'lzp-btn lzp-btn--danger', __( 'Delete this for good? It cannot be undone.', 'lenz-plus' ) );
		echo '</section>';
	}

	/**
	 * Deletes a post (subclasses add their own actions).
	 *
	 * @param string $action Action name.
	 */
	public function handle( string $action ): string {
		$id = absint( self::posted( 'post_id' ) );
		if ( get_post_type( $id ) !== $this->post_type() ) {
			return $this->done( 'error' );
		}

		if ( 'delete' === $action ) {
			wp_delete_post( $id, true );

			return $this->done( 'deleted' );
		}

		return $this->handle_item( $action, $id );
	}

	/**
	 * Subclass actions on one post.
	 *
	 * @param string $action Action name.
	 * @param int    $id     Post ID.
	 */
	protected function handle_item( string $action, int $id ): string {
		unset( $action );

		return $this->url( array( 'view' => $id ) );
	}
}
