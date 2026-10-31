<?php
/**
 * Articles screen: the blog's posts with their categories and tags.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * Articles list and editor.
 */
final class Posts extends Content_Screen {

	/** Screen id. */
	public function id(): string {
		return 'posts';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Articles', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'news';
	}

	/** Settings switch. */
	public function section(): string {
		return 'posts';
	}

	/** WordPress posts. */
	protected function post_type(): string {
		return 'post';
	}

	/** Post categories. */
	protected function taxonomy(): string {
		return 'category';
	}

	/** "New" label. */
	protected function new_label(): string {
		return __( 'New article', 'lenz-plus' );
	}

	/** Edit page title. */
	protected function edit_label(): string {
		return __( 'Edit article', 'lenz-plus' );
	}

	/** Empty list. */
	protected function empty_label(): string {
		return __( 'No articles yet. Write your first one with the + button.', 'lenz-plus' );
	}

	/**
	 * Tags and comments in the side column.
	 *
	 * @param \WP_Post|null $post Article, null for a new one.
	 */
	protected function render_side_fields( ?\WP_Post $post ): void {
		$tags = $post ? wp_get_post_terms( $post->ID, 'post_tag', array( 'fields' => 'names' ) ) : array();
		echo '<section class="lzp-card">';
		Ui::input( 'tags', __( 'Tags', 'lenz-plus' ), is_array( $tags ) ? implode( '، ', $tags ) : '', array( 'help' => __( 'Separate them with commas.', 'lenz-plus' ) ) );
		Ui::toggle( 'comments_open', __( 'Allow comments', 'lenz-plus' ), $post ? 'open' === $post->comment_status : 'open' === get_default_comment_status() );
		echo '</section>';
	}

	/** The comment switch. */
	protected function extra_post_data(): array {
		return array( 'comment_status' => '1' === self::posted( 'comments_open' ) ? 'open' : 'closed' );
	}

	/**
	 * Saves the tags.
	 *
	 * @param int $id Article ID.
	 */
	protected function save_fields( int $id ): void {
		$tags = array_filter( array_map( 'trim', preg_split( '/[,،]/u', sanitize_text_field( self::posted( 'tags' ) ) ) ) );
		wp_set_post_terms( $id, $tags, 'post_tag' );
	}
}
