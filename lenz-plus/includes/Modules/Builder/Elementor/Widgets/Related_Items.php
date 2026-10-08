<?php
/**
 * «پروژه‌های مرتبط»: a section heading with an "all projects" button, then
 * cards of other items of the same kind (4:3 photo, title, category and
 * year). Items sharing a category with the one being viewed come first; the
 * newest fill the rest. Works for projects and blog posts.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Context;
use LenzPlus\Modules\Builder\Elementor\Picture;
use LenzPlus\Modules\Builder\Elementor\Post_Parts;
use LenzPlus\Modules\Builder\Portfolio_Data;

defined( 'ABSPATH' ) || exit;

/**
 * The «Related items (Lenz+)» widget.
 */
final class Related_Items extends Portfolio_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-related-items';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Related items', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-posts-grid';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'related', 'posts', 'مرتبط' ) );
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Items', 'lenz-plus' ) );

		$this->add_control(
			'post_type',
			array(
				'label'   => __( 'Items', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'portfolio',
				'options' => array(
					'portfolio' => __( 'Projects', 'lenz-plus' ),
					'post'      => __( 'Blog posts', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Related projects', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'       => __( 'Button text', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'All projects', 'lenz-plus' ),
				'description' => __( 'Links to the full list. Leave empty to hide it.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'count',
			array(
				'label'   => __( 'Number of items', 'lenz-plus' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 3,
				'min'     => 1,
				'max'     => 12,
			)
		);

		$this->add_control(
			'look',
			array(
				'label'   => __( 'Cards', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'plain',
				'options' => array(
					'plain' => __( 'Photo with text under it', 'lenz-plus' ),
					'card'  => __( 'White card with a border', 'lenz-plus' ),
				),
			)
		);

		$this->add_ratio_control( 'ratio', '4/3' );
		$this->add_columns_control( '.lzp-related__grid', array( 3, 2, 1 ) );
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Cards', 'lenz-plus' ) );
		$this->add_gap_control( '.lzp-related__grid' );
		$this->add_text_style( 'title', '.lzp-related__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'meta', '.lzp-related__meta', array( 'label' => __( 'Details', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Items sharing a category with the current one first, then the newest.
	 *
	 * @param string $post_type Post type.
	 * @param int    $count     Items wanted.
	 * @return int[]
	 */
	private static function item_ids( string $post_type, int $count ): array {
		$current  = Context::current_item( $post_type );
		$taxonomy = 'portfolio' === $post_type ? Portfolio_Data::TAXONOMY : 'category';
		$base     = array(
			'post_type'           => $post_type,
			'post_status'         => 'publish',
			'posts_per_page'      => $count,
			'post__not_in'        => array( $current ),
			'fields'              => 'ids',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		);

		$ids   = array();
		$terms = $current ? wp_get_post_terms( $current, $taxonomy, array( 'fields' => 'ids' ) ) : array();
		if ( is_array( $terms ) && $terms ) {
			$ids = get_posts(
				$base + array(
					'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- a few related items.
						array(
							'taxonomy' => $taxonomy,
							'terms'    => $terms,
						),
					),
				)
			);
		}

		if ( count( $ids ) < $count ) {
			$base['post__not_in'] = array_merge( array( $current ), $ids );
			$ids                  = array_merge( $ids, get_posts( $base ) );
		}

		return array_slice( array_map( 'intval', $ids ), 0, $count );
	}

	/** Prints the heading and the cards. */
	protected function render(): void {
		$s         = $this->get_settings_for_display();
		$post_type = 'post' === $s['post_type'] ? 'post' : 'portfolio';
		$ids       = self::item_ids( $post_type, max( 1, (int) $s['count'] ) );

		if ( ! $ids ) {
			$this->editor_hint( __( 'Nothing to show yet: there are no other items of this kind.', 'lenz-plus' ) );
			return;
		}

		$action = '';
		if ( '' !== (string) $s['button_text'] ) {
			$page_id = 'post' === $post_type ? (int) get_option( 'page_for_posts' ) : 0;
			$url     = 'post' === $post_type ? ( $page_id ? get_permalink( $page_id ) : home_url( '/' ) ) : get_post_type_archive_link( $post_type );
			$action  = Button::markup(
				array(
					'text'    => (string) $s['button_text'],
					'url'     => (string) $url,
					'variant' => 'primary',
					'size'    => 'sm',
					'icon'    => 'arrow-forward',
				)
			);
		}

		$cards = '';
		foreach ( $ids as $id ) {
			$cards .= self::card_html( $id, $post_type, (string) $s['ratio'] );
		}

		printf(
			'<div class="lzp-related lzp-related--%3$s">%1$s<ul class="lzp-related__grid lzp-grid">%2$s</ul></div>',
			'' !== (string) $s['title'] ? Heading::markup( array( 'title' => (string) $s['title'] ), $action ) : '', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Heading::markup().
			$cards, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in card_html().
			esc_attr( 'card' === $s['look'] ? 'card' : 'plain' )
		);
	}

	/**
	 * One card.
	 *
	 * @param int    $post_id   Item ID.
	 * @param string $post_type Post type.
	 * @param string $ratio     Photo ratio.
	 */
	private static function card_html( int $post_id, string $post_type, string $ratio ): string {
		$title = get_the_title( $post_id );

		if ( 'portfolio' === $post_type ) {
			$terms = Portfolio_Data::terms( $post_id );
			$meta  = array_filter( array( $terms ? $terms[0]->name : '', self::digits( Portfolio_Data::year( $post_id ) ) ) );
		} else {
			$post = get_post( $post_id );
			$cats = $post ? Post_Parts::categories( $post ) : array();
			$meta = array_filter( array( $cats ? $cats[0]->name : '', $post ? Post_Parts::reading_label( $post ) : '' ) );
		}

		return sprintf(
			'<li><a class="lzp-related__card" href="%1$s"><span class="lzp-related__media">%2$s%3$s</span><span class="lzp-related__body"><span class="lzp-related__meta">%5$s</span><span class="lzp-related__title">%4$s</span></span></a></li>',
			esc_url( (string) get_permalink( $post_id ) ),
			Picture::frame(
				array( 'id' => (int) get_post_thumbnail_id( $post_id ) ),
				array(
					'ratio' => $ratio,
					'alt'   => $title,
					'class' => 'lzp-photo',
					'sizes' => '(max-width: 767px) 100vw, 33vw',
				)
			),
			'portfolio' === $post_type && Portfolio_Data::is_video( $post_id ) ? self::icon( 'play-circle', 'lzp-related__play' ) : '',
			esc_html( $title ),
			esc_html( implode( ' · ', $meta ) )
		);
	}
}
