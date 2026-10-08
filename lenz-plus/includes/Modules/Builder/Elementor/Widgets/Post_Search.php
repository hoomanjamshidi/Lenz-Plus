<?php
/**
 * «جست‌وجو در مقالات»: a search box for the blog in a bordered sidebar card.
 * It sends `post_type=post`, so results open in the post list template
 * (Page templates → Site pages → Post list) instead of a site-wide search.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * The «Blog search (Lenz+)» widget.
 */
final class Post_Search extends Blog_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-post-search';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Blog search', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-search';
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Blog search', 'lenz-plus' ) );

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Search the articles', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'placeholder',
			array(
				'label'   => __( 'Field text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'e.g. lighting', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Card', 'lenz-plus' ) );
		$this->add_box_style( 'box', '.lzp-blog-search' );
		$this->end_controls_section();
	}

	/** Prints the form. */
	protected function render(): void {
		$s  = $this->get_settings_for_display();
		$id = 'lzp-search-' . $this->get_id();

		printf(
			'<div class="lzp-blog-search lzp-side-card">%1$s<form class="lzp-blog-search__form" role="search" action="%2$s" method="get">%3$s<label class="screen-reader-text" for="%4$s">%5$s</label><input class="lzp-blog-search__input" id="%4$s" type="search" name="s" value="%6$s" placeholder="%7$s"><input type="hidden" name="post_type" value="post"><button class="lzp-blog-search__submit" type="submit"><span class="screen-reader-text">%8$s</span></button></form></div>',
			'' !== (string) $s['title'] ? '<h3 class="lzp-side-card__title">' . esc_html( $s['title'] ) . '</h3>' : '',
			esc_url( home_url( '/' ) ),
			self::icon( 'search', 'lzp-blog-search__icon' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup from the bundled library.
			esc_attr( $id ),
			esc_html__( 'Search the articles', 'lenz-plus' ),
			esc_attr( get_search_query() ),
			esc_attr( (string) $s['placeholder'] ),
			esc_html__( 'Search', 'lenz-plus' )
		);
	}
}
