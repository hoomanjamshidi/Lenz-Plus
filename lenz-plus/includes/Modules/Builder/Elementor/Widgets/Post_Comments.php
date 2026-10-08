<?php
/**
 * The comments and the comment form of the post being viewed, printed by
 * Lenz's own comment template (comments.php), so its styles and scripts
 * apply as on Lenz's article page.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * The «Comments (Lenz+)» widget.
 */
final class Post_Comments extends Blog_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-post-comments';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Comments', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-comments';
	}

	/** Content controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Comments', 'lenz-plus' ) );

		$this->add_control(
			'note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Shows the comments and the comment form of the post being viewed, as Lenz prints them. Comments are switched on or off per post and in Settings → Discussion.', 'lenz-plus' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->end_controls_section();
	}

	/** Prints the comments (a hint in the editor). */
	protected function render(): void {
		if ( $this->in_editor() || ! is_singular() ) {
			$this->editor_hint( __( 'The comments and the comment form appear here on the post.', 'lenz-plus' ) );
			return;
		}

		if ( ! comments_open() && ! get_comments_number() ) {
			return;
		}

		echo '<div class="lzp-comments">';
		comments_template();
		echo '</div>';
	}
}
