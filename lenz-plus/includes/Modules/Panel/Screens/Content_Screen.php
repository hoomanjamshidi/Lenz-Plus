<?php
/**
 * Base of the screens that list and edit one post type (projects, articles,
 * pages, products): a searchable list with status chips, and an edit page
 * with title, text, cover photo, categories and publishing, plus the fields
 * a subclass adds (gallery, project details, prices…).
 *
 * WordPress's own checks decide what a user may do: the list only shows
 * posts they can edit, and saving or trashing checks `edit_post` /
 * `delete_post` for that post.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Modules\Panel\Editor;
use LenzPlus\Modules\Panel\Ui;
use LenzPlus\Modules\Panel\Uploads;

defined( 'ABSPATH' ) || exit;

/**
 * List and edit pages of one post type.
 */
abstract class Content_Screen extends Screen {

	/** Statuses the lists show (no trash, no auto-drafts). */
	protected const LIST_STATUSES = array( 'publish', 'draft', 'pending', 'private', 'future' );

	/** @var \WP_Post|null|false Post being edited (false = not loaded yet, null = new). */
	private $post = false;

	/** Post type of the screen. */
	abstract protected function post_type(): string;

	/** Label of the "new" button and page, e.g. "New project". */
	abstract protected function new_label(): string;

	/** Title of the edit page, e.g. "Edit project". */
	abstract protected function edit_label(): string;

	/** Message of the empty list. */
	abstract protected function empty_label(): string;

	/** Taxonomy whose terms are chosen on the edit page ('' = none). */
	protected function taxonomy(): string {
		return '';
	}

	/** Label of the terms group. */
	protected function terms_label(): string {
		return __( 'Categories', 'lenz-plus' );
	}

	/** Label of the main text field. */
	protected function content_label(): string {
		return __( 'Text', 'lenz-plus' );
	}

	/** Editing needs the post type's `edit_posts`. */
	public function capability(): string {
		$object = get_post_type_object( $this->post_type() );

		return $object ? (string) $object->cap->edit_posts : 'do_not_allow';
	}

	/** Only while the post type exists (e.g. Lenz registers `portfolio`). */
	public function is_available(): bool {
		return post_type_exists( $this->post_type() );
	}

	/** Whether the page edits a post (or creates one) rather than listing. */
	protected function is_editing(): bool {
		return self::arg_int( 'edit' ) > 0 || '1' === self::arg( 'new' );
	}

	/** The post being edited, null for a new one. */
	protected function edited_post(): ?\WP_Post {
		if ( false === $this->post ) {
			$id = self::arg_int( 'edit' );
			// get_post( 0 ) would return the global post, not "no post".
			$post       = $id > 0 ? get_post( $id ) : null;
			$this->post = $post && $post->post_type === $this->post_type() && current_user_can( 'edit_post', $post->ID ) ? $post : null;
		}

		return $this->post;
	}

	/** "Edit project" or "New project". */
	public function page_title(): string {
		if ( ! $this->is_editing() ) {
			return $this->title();
		}

		return $this->edited_post() ? $this->edit_label() : $this->new_label();
	}

	/** Back to the list from the edit page. */
	public function back_url(): string {
		return $this->is_editing() ? $this->url() : '';
	}

	/** The "new" button on the list. */
	public function primary_action(): array {
		$object = get_post_type_object( $this->post_type() );
		if ( $this->is_editing() || ! $object || ! current_user_can( $object->cap->create_posts ) ) {
			return array();
		}

		return array( $this->url( array( 'new' => 1 ) ), $this->new_label(), 'plus' );
	}

	/** The editor's scripts on the edit page. */
	public function enqueue(): void {
		if ( $this->is_editing() ) {
			$this->module->editor()->enqueue( $this->post_type() );
		}
	}

	/** Prints the list or the edit page. */
	public function render(): void {
		if ( ! $this->is_editing() ) {
			$this->render_list();
			return;
		}

		if ( self::arg_int( 'edit' ) > 0 && ! $this->edited_post() ) {
			Ui::empty_state( __( 'This item does not exist or you cannot edit it.', 'lenz-plus' ) );
			return;
		}

		$this->render_editor( $this->edited_post() );
	}

	/* ------------------------------------------------------------------
	 * List
	 * ---------------------------------------------------------------- */

	/** Prints the search, the chips, the rows and the pages. */
	protected function render_list(): void {
		$status = self::arg( 'status' );
		$status = in_array( $status, self::LIST_STATUSES, true ) ? $status : '';
		$search = self::search_term();

		$query = new \WP_Query( $this->list_query_args( $status, $search ) );
		$base  = $this->url(
			array_filter(
				array(
					's'      => $search,
					'status' => $status,
				)
			)
		);

		$keep = '' !== $status ? array( 'status' => $status ) : array();
		Ui::search( $this->url(), $search, __( 'Search…', 'lenz-plus' ), $keep + $this->plain_args() );
		Ui::chips(
			$this->url( array_filter( array( 's' => $search ) ) ),
			'status',
			array(
				''        => __( 'All', 'lenz-plus' ),
				'publish' => __( 'Published', 'lenz-plus' ),
				'draft'   => __( 'Drafts', 'lenz-plus' ),
			),
			$status
		);

		$rows = array();
		foreach ( $query->posts as $post ) {
			$rows[] = $this->row( $post );
		}

		Ui::rows( $rows, '' !== $search ? __( 'Nothing matches this search.', 'lenz-plus' ) : $this->empty_label() );
		Ui::pagination( $base, self::page_number(), (int) $query->max_num_pages );
	}

	/**
	 * Query of the list.
	 *
	 * @param string $status Status filter ('' = all).
	 * @param string $search Search term.
	 */
	protected function list_query_args( string $status, string $search ): array {
		return array(
			'post_type'      => $this->post_type(),
			'post_status'    => '' !== $status ? $status : self::LIST_STATUSES,
			's'              => $search,
			'posts_per_page' => $this->per_page(),
			'paged'          => self::page_number(),
			'perm'           => 'editable',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => false,
		);
	}

	/**
	 * One list row.
	 *
	 * @param \WP_Post $post Post.
	 */
	protected function row( \WP_Post $post ): array {
		$meta = array( Ui::date( (int) get_post_time( 'U', true, $post ) ) );
		if ( '' !== $this->taxonomy() ) {
			$names = wp_get_post_terms( $post->ID, $this->taxonomy(), array( 'fields' => 'names' ) );
			if ( is_array( $names ) && $names ) {
				$meta[] = implode( '، ', array_slice( $names, 0, 2 ) );
			}
		}

		return array(
			'url'   => $this->url( array( 'edit' => $post->ID ) ),
			'title' => $this->post_title( $post ),
			'meta'  => implode( ' · ', $meta ),
			'thumb' => (string) get_the_post_thumbnail_url( $post, 'thumbnail' ),
			'icon'  => $this->icon(),
			'pill'  => 'publish' === $post->post_status ? '' : Ui::pill( $post->post_status, self::status_label( $post->post_status ) ),
		);
	}

	/**
	 * The title, or a stand-in for untitled posts.
	 *
	 * @param \WP_Post $post Post.
	 */
	protected function post_title( \WP_Post $post ): string {
		$title = trim( wp_strip_all_tags( get_the_title( $post ) ) );

		return '' !== $title ? html_entity_decode( $title, ENT_QUOTES, 'UTF-8' ) : __( '(no title)', 'lenz-plus' );
	}

	/**
	 * Label of a post status.
	 *
	 * @param string $status Status.
	 */
	protected static function status_label( string $status ): string {
		$object = get_post_status_object( $status );

		return $object ? (string) $object->label : $status;
	}

	/* ------------------------------------------------------------------
	 * Edit page
	 * ---------------------------------------------------------------- */

	/**
	 * Prints the edit form.
	 *
	 * @param \WP_Post|null $post Post, null for a new one.
	 */
	protected function render_editor( ?\WP_Post $post ): void {
		$id = $post ? (int) $post->ID : 0;
		$this->form_open( 'save', 'lzp-edit', true );
		?>
		<div class="lzp-edit__grid">
			<div class="lzp-edit__main">
				<section class="lzp-card">
					<div class="lzp-field">
						<label class="lzp-field__label" for="lzp-f-title"><?php esc_html_e( 'Title', 'lenz-plus' ); ?></label>
						<input class="lzp-input lzp-input--title" id="lzp-f-title" name="title" value="<?php echo esc_attr( $post ? $post->post_title : '' ); ?>" required>
					</div>
					<?php $this->module->editor()->render( $this->post_type(), $id, $post ? $post->post_content : '', $this->content_label() ); ?>
					<?php if ( post_type_supports( $this->post_type(), 'excerpt' ) ) : ?>
						<details class="lzp-more"<?php echo $post && '' !== $post->post_excerpt ? ' open' : ''; ?>>
							<summary><?php esc_html_e( 'Short summary', 'lenz-plus' ); ?></summary>
							<?php Ui::textarea( 'excerpt', __( 'Summary shown on cards and lists', 'lenz-plus' ), $post ? $post->post_excerpt : '', array( 'rows' => 3 ) ); ?>
						</details>
					<?php endif; ?>
				</section>
				<?php $this->render_fields( $post ); ?>
			</div>
			<aside class="lzp-edit__side">
				<?php $this->render_publish_card( $post ); ?>
				<?php if ( post_type_supports( $this->post_type(), 'thumbnail' ) ) : ?>
					<section class="lzp-card">
						<?php Uploads::field( 'thumbnail', __( 'Cover photo', 'lenz-plus' ), $post ? array( (int) get_post_thumbnail_id( $post ) ) : array() ); ?>
					</section>
				<?php endif; ?>
				<?php if ( '' !== $this->taxonomy() ) : ?>
					<section class="lzp-card">
						<?php
						Ui::terms(
							$this->taxonomy(),
							$this->terms_label(),
							$post ? array_map( 'intval', wp_get_post_terms( $post->ID, $this->taxonomy(), array( 'fields' => 'ids' ) ) ) : array(),
							array( 'new_label' => __( 'New category (separate several with commas)', 'lenz-plus' ) )
						);
						?>
					</section>
				<?php endif; ?>
				<?php $this->render_side_fields( $post ); ?>
			</aside>
		</div>
		<div class="lzp-savebar">
			<button type="submit" class="lzp-btn lzp-btn--primary" data-lzp-saving="<?php esc_attr_e( 'Saving…', 'lenz-plus' ); ?>"><?php Ui::the_icon( 'check' ); ?><span><?php esc_html_e( 'Save', 'lenz-plus' ); ?></span></button>
			<?php if ( $post ) : ?>
				<a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( 'publish' === $post->post_status ? (string) get_permalink( $post ) : (string) get_preview_post_link( $post ) ); ?>" target="_blank" rel="noopener"><?php Ui::the_icon( 'eye' ); ?><span><?php echo 'publish' === $post->post_status ? esc_html__( 'View', 'lenz-plus' ) : esc_html__( 'Preview', 'lenz-plus' ); ?></span></a>
			<?php endif; ?>
		</div>
		</form>
		<?php
		if ( $post && current_user_can( 'delete_post', $post->ID ) ) {
			echo '<section class="lzp-card lzp-card--danger">';
			$this->button_form( 'trash', array( 'post_id' => $post->ID ), __( 'Move to trash', 'lenz-plus' ), 'lzp-btn lzp-btn--danger', __( 'Move this item to the trash? You can restore it from WordPress\'s trash.', 'lenz-plus' ) );
			echo '</section>';
		}
	}

	/**
	 * The publishing card: published or draft.
	 *
	 * @param \WP_Post|null $post Post, null for a new one.
	 */
	protected function render_publish_card( ?\WP_Post $post ): void {
		$object  = get_post_type_object( $this->post_type() );
		$publish = $object && current_user_can( $object->cap->publish_posts );
		$current = $post ? $post->post_status : 'publish';
		$current = in_array( $current, array( 'publish', 'future', 'private' ), true ) ? 'publish' : 'draft';

		$choices = array(
			'publish' => $publish ? __( 'Published', 'lenz-plus' ) : __( 'Send for review', 'lenz-plus' ),
			'draft'   => __( 'Draft', 'lenz-plus' ),
		);
		?>
		<section class="lzp-card">
			<?php Ui::choices( 'status', __( 'Visibility', 'lenz-plus' ), $choices, $current, array( 'help' => __( 'Drafts are saved but visitors do not see them.', 'lenz-plus' ) ) ); ?>
			<?php if ( $post ) : ?>
				<p class="lzp-card__note">
					<?php
					/* translators: %s: date. */
					echo esc_html( sprintf( __( 'Last change: %s', 'lenz-plus' ), Ui::date( (int) get_post_modified_time( 'U', true, $post ), true ) ) );
					?>
				</p>
			<?php endif; ?>
		</section>
		<?php
	}

	/**
	 * Extra cards in the main column (galleries, details…).
	 *
	 * @param \WP_Post|null $post Post, null for a new one.
	 */
	protected function render_fields( ?\WP_Post $post ): void {
		unset( $post );
	}

	/**
	 * Extra cards in the side column.
	 *
	 * @param \WP_Post|null $post Post, null for a new one.
	 */
	protected function render_side_fields( ?\WP_Post $post ): void {
		unset( $post );
	}

	/* ------------------------------------------------------------------
	 * Saving
	 * ---------------------------------------------------------------- */

	/**
	 * Saves or trashes a post.
	 *
	 * @param string $action `save` or `trash`.
	 */
	public function handle( string $action ): string {
		if ( 'trash' === $action ) {
			$id = absint( self::posted( 'post_id' ) );
			if ( get_post_type( $id ) === $this->post_type() && current_user_can( 'delete_post', $id ) && wp_trash_post( $id ) ) {
				return $this->done( 'trashed' );
			}

			return $this->done( 'error' );
		}

		if ( 'save' !== $action ) {
			return $this->url();
		}

		$post = $this->edited_post_for_save();
		if ( false === $post ) {
			return $this->done( 'denied' );
		}

		$id = $this->save_post( $post );
		if ( is_wp_error( $id ) || ! $id ) {
			return $this->done( 'error', $post ? array( 'edit' => $post->ID ) : array( 'new' => 1 ) );
		}

		$this->save_media_and_terms( $id );
		$this->save_fields( $id );

		return $this->done( $post ? 'saved' : 'created', array( 'edit' => $id ) );
	}

	/**
	 * The post a save targets: the one posted (when the user may edit it),
	 * null for a new one (when they may create), false when not allowed.
	 *
	 * @return \WP_Post|null|false
	 */
	private function edited_post_for_save() {
		$id = absint( self::posted( 'post_id' ) );
		if ( 0 === $id ) {
			$id = self::arg_int( 'edit' );
		}

		if ( $id > 0 ) {
			$post = get_post( $id );

			return $post && $post->post_type === $this->post_type() && current_user_can( 'edit_post', $id ) ? $post : false;
		}

		$object = get_post_type_object( $this->post_type() );

		return $object && current_user_can( $object->cap->create_posts ) ? null : false;
	}

	/**
	 * The posted status the user may set: publish (or pending for users who
	 * cannot publish) or draft.
	 */
	protected function posted_status(): string {
		$object = get_post_type_object( $this->post_type() );
		if ( 'draft' === self::posted( 'status' ) ) {
			return 'draft';
		}

		return $object && current_user_can( $object->cap->publish_posts ) ? 'publish' : 'pending';
	}

	/**
	 * Writes the post's own fields (title, text, excerpt, status).
	 *
	 * @param \WP_Post|null $post Existing post, null for a new one.
	 * @return int|\WP_Error Post ID.
	 */
	protected function save_post( ?\WP_Post $post ) {
		$data = array(
			'post_type'   => $this->post_type(),
			'post_title'  => sanitize_text_field( self::posted( 'title' ) ),
			'post_status' => $this->posted_status(),
		);

		if ( $post && 'future' === $post->post_status && 'publish' === $data['post_status'] ) {
			// A scheduled post stays scheduled; "published" here means "not a draft".
			$data['post_status'] = 'future';
		}

		if ( $this->module->editor()->posts_content( $this->post_type(), $post ? (int) $post->ID : 0 ) ) {
			$data['post_content'] = Editor::posted();
		}

		if ( post_type_supports( $this->post_type(), 'excerpt' ) ) {
			$data['post_excerpt'] = sanitize_textarea_field( self::posted( 'excerpt' ) );
		}

		$data = $this->extra_post_data() + $data;

		if ( $post ) {
			$data['ID'] = $post->ID;

			return wp_update_post( wp_slash( $data ), true );
		}

		return wp_insert_post( wp_slash( $data ), true );
	}

	/**
	 * Post fields a subclass adds to the save (e.g. the comment switch).
	 *
	 * @return array<string, mixed>
	 */
	protected function extra_post_data(): array {
		return array();
	}

	/**
	 * Cover photo and terms.
	 *
	 * @param int $id Post ID.
	 */
	private function save_media_and_terms( int $id ): void {
		if ( post_type_supports( $this->post_type(), 'thumbnail' ) ) {
			$ids = Uploads::posted_ids( 'thumbnail', $id );
			if ( $ids ) {
				set_post_thumbnail( $id, (int) end( $ids ) );
			} else {
				delete_post_thumbnail( $id );
			}
		}

		$taxonomy = $this->taxonomy();
		if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		$posted = self::posted_array( 'terms' );
		$ids    = isset( $posted[ $taxonomy ] ) ? array_map( 'absint', (array) $posted[ $taxonomy ] ) : array();
		$ids    = array_merge( $ids, $this->new_terms( $taxonomy ) );

		wp_set_object_terms( $id, array_values( array_unique( array_filter( $ids ) ) ), $taxonomy );
	}

	/**
	 * Terms typed in the "new category" box (created when missing).
	 *
	 * @param string $taxonomy Taxonomy.
	 * @return int[] Term IDs.
	 */
	private function new_terms( string $taxonomy ): array {
		$posted = self::posted_array( 'new_terms' );
		$text   = isset( $posted[ $taxonomy ] ) && is_string( $posted[ $taxonomy ] ) ? sanitize_text_field( $posted[ $taxonomy ] ) : '';
		if ( '' === $text || ! current_user_can( get_taxonomy( $taxonomy )->cap->manage_terms ) ) {
			return array();
		}

		$ids = array();
		foreach ( preg_split( '/[,،]/u', $text ) as $name ) {
			$name = trim( $name );
			if ( '' === $name ) {
				continue;
			}

			$term = term_exists( $name, $taxonomy );
			$term = $term ? $term : wp_insert_term( $name, $taxonomy );
			if ( is_array( $term ) ) {
				$ids[] = (int) $term['term_id'];
			}
		}

		return $ids;
	}

	/**
	 * Saves the fields a subclass added.
	 *
	 * @param int $id Post ID.
	 */
	protected function save_fields( int $id ): void {
		unset( $id );
	}
}
