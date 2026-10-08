<?php
/**
 * Featured article of the blog page: one wide linked card with the cover
 * beside an ink «مقاله شاخص» chip, the date and reading time, the title, the
 * excerpt and a "read the article" line. Shows the newest sticky post, else
 * the newest post.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Elementor\Picture;
use LenzPlus\Modules\Builder\Elementor\Post_Parts;

defined( 'ABSPATH' ) || exit;

/**
 * The «Featured post (Lenz+)» widget.
 */
final class Featured_Post extends Blog_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-featured-post';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Featured post', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-featured-image';
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Featured post', 'lenz-plus' ) );

		$this->add_control(
			'note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Shows the newest sticky post (Posts → Edit → "Stick to the top of the blog"), else the newest post.', 'lenz-plus' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->add_control(
			'first_page_only',
			array(
				'label'        => __( 'Only on the first page of the blog', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => __( 'In a post list template, hides it on categories, searches and later pages.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'badge',
			array(
				'label'   => __( 'Badge', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Featured article', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'link_text',
			array(
				'label'   => __( 'Link text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Read the article', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Card', 'lenz-plus' ) );
		$this->add_box_style( 'card', '.lzp-featured-post', array( 'padding' => false ) );
		$this->add_text_style( 'title', '.lzp-featured-post__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** The newest sticky post, else the newest post (0 when there is none). */
	public static function featured_id(): int {
		$sticky = array_map( 'intval', (array) get_option( 'sticky_posts', array() ) );
		$args   = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 1,
			'fields'              => 'ids',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		);

		$ids = $sticky ? get_posts( $args + array( 'post__in' => $sticky ) ) : array();
		if ( ! $ids ) {
			$ids = get_posts( $args );
		}

		return $ids ? (int) $ids[0] : 0;
	}

	/** Prints the card. */
	protected function render(): void {
		$s = $this->get_settings_for_display();

		// On a post list other than the blog's first page, the featured article would repeat or miss the topic.
		$list = is_home() || is_category() || is_tag() || is_author() || is_date() || is_search();
		if ( 'yes' === $s['first_page_only'] && $list && ( ! is_home() || is_paged() ) && ! $this->in_editor() ) {
			return;
		}

		$post = get_post( self::featured_id() );

		if ( ! $post ) {
			$this->editor_hint( __( 'Publish a blog post to see real data here.', 'lenz-plus' ) );
			return;
		}

		$title   = get_the_title( $post );
		$excerpt = wp_strip_all_tags( get_the_excerpt( $post ) );

		$photo = Picture::frame(
			array( 'id' => (int) get_post_thumbnail_id( $post ) ),
			array(
				'ratio'   => '',
				'label'   => $title,
				'alt'     => $title,
				'class'   => 'lzp-photo',
				'loading' => 'high',
			)
		);

		printf(
			'<a class="lzp-featured-post" href="%1$s"><span class="lzp-featured-post__media">%2$s</span><span class="lzp-featured-post__body"><span class="lzp-post-meta">%3$s<span class="lzp-post-meta__line">%4$s · %5$s</span></span><h2 class="lzp-featured-post__title">%6$s</h2>%7$s<span class="lzp-featured-post__more"><span>%8$s</span>%9$s</span></span></a>',
			esc_url( (string) get_permalink( $post ) ),
			$photo, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Picture::frame().
			'' !== (string) $s['badge'] ? '<span class="lzp-chip lzp-chip--ink">' . esc_html( $s['badge'] ) . '</span>' : '',
			Post_Parts::time_html( $post ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in time_html().
			esc_html( Post_Parts::reading_label( $post, true ) ),
			esc_html( $title ),
			'' !== $excerpt ? '<span class="lzp-featured-post__excerpt">' . esc_html( $excerpt ) . '</span>' : '',
			esc_html( (string) $s['link_text'] ),
			self::icon( 'arrow-forward' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup from the bundled library.
		);
	}
}
