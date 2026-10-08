<?php
/**
 * Post cards of the blog page: a 4:3 photo, the category chip with the date
 * and reading time, the title and the excerpt, with optional category chips
 * above and page numbers below.
 *
 * On a post list (posts page, category, tag, author, date, blog search) it
 * lists the page being viewed and the chips link to the category pages.
 * Elsewhere it lists the newest posts; when they all fit on the page,
 * filter.js filters them in place instead of following the chip links.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Assets;
use LenzPlus\Modules\Builder\Elementor\Picture;
use LenzPlus\Modules\Builder\Elementor\Post_Parts;

defined( 'ABSPATH' ) || exit;

/**
 * The «Post grid (Lenz+)» widget.
 */
final class Post_Grid extends Blog_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-post-grid';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Post grid', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-posts-grid';
	}

	/** The in-place filter script. */
	public function get_script_depends(): array {
		return array( Assets::FILTER_HANDLE );
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Posts', 'lenz-plus' ) );

		$this->add_control(
			'source',
			array(
				'label'       => __( 'Posts', 'lenz-plus' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'current',
				'options'     => array(
					'current' => __( 'Of the page being viewed', 'lenz-plus' ),
					'latest'  => __( 'Newest', 'lenz-plus' ),
				),
				'description' => __( 'On a post list (blog page, category, search…) it shows that page\'s posts with page numbers; anywhere else the newest posts.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'count',
			array(
				'label'   => __( 'Number of posts', 'lenz-plus' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 6,
				'min'     => 1,
				'max'     => 48,
			)
		);

		$this->add_control(
			'skip_featured',
			array(
				'label'        => __( 'Leave out the featured post', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => __( 'For a page that also shows the Featured post widget (newest posts only).', 'lenz-plus' ),
				'condition'    => array( 'source' => 'latest' ),
			)
		);

		$this->add_control(
			'filters',
			array(
				'label'        => __( 'Category chips', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'all_label',
			array(
				'label'     => __( '"All" chip', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'All', 'lenz-plus' ),
				'condition' => array( 'filters' => 'yes' ),
			)
		);

		$this->add_control(
			'excerpt',
			array(
				'label'        => __( 'Excerpt', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'empty_text',
			array(
				'label'   => __( 'When there are no posts', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'No articles have been published in this category yet.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'look',
			array(
				'label'   => __( 'Look', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'plain',
				'options' => array(
					'plain'  => __( 'Plain', 'lenz-plus' ),
					'dashed' => __( 'Dashed cards (home page)', 'lenz-plus' ),
				),
			)
		);

		$this->add_ratio_control( 'ratio', '4/3' );
		$this->add_columns_control( '.lzp-posts', array( 2, 2, 1 ) );
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Cards', 'lenz-plus' ) );
		$this->add_gap_control( '.lzp-posts' );
		$this->add_text_style( 'title', '.lzp-post-card__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'excerpt', '.lzp-post-card__excerpt', array( 'label' => __( 'Excerpt', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Whether the main query is a post list this grid should show.
	 */
	private static function is_post_list(): bool {
		return is_home() || is_category() || is_tag() || is_author() || is_date() || is_search();
	}

	/** Prints the chips, the cards and the page numbers. */
	protected function render(): void {
		$s       = $this->get_settings_for_display();
		$archive = 'current' === $s['source'] && self::is_post_list();
		$args    = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, (int) $s['count'] ),
			'ignore_sticky_posts' => true,
		);

		if ( ! $archive && 'yes' === $s['skip_featured'] ) {
			$args['post__not_in'] = array( Featured_Post::featured_id() );
		}

		$query = $archive ? $GLOBALS['wp_query'] : new \WP_Query( $args );

		// All posts on one page: the chips can filter in place.
		$in_place = ! $archive && (int) $query->found_posts <= count( $query->posts );

		echo '<div class="lzp-post-list"' . ( $in_place ? ' data-lzp-filter' : '' ) . '>';

		if ( 'yes' === $s['filters'] ) {
			$page_id = (int) get_option( 'page_for_posts' );

			$bar = self::filter_bar_html(
				array(
					'taxonomy'  => 'category',
					'exclude'   => array( (int) get_option( 'default_category' ) ),
					'all_label' => (string) $s['all_label'],
					'all_url'   => $page_id ? (string) get_permalink( $page_id ) : home_url( '/' ),
					'current'   => $archive && is_category() ? (int) get_queried_object_id() : 0,
					'total'     => $archive ? (int) $query->found_posts : count( $query->posts ),
					/* translators: %s: number of articles. */
					'one'       => __( '%s article', 'lenz-plus' ),
					/* translators: %s: number of articles. */
					'many'      => __( '%s articles', 'lenz-plus' ),
					'label'     => __( 'Blog categories', 'lenz-plus' ),
				)
			);
			echo $bar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in filter_bar_html().
		}

		echo '<ul class="lzp-posts lzp-grid' . ( 'dashed' === $s['look'] ? ' lzp-posts--dashed' : '' ) . '">';
		foreach ( $query->posts as $post ) {
			echo self::card_html( $post, (string) $s['ratio'], 'yes' === $s['excerpt'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in card_html().
		}
		echo '</ul>';

		printf(
			'<p class="lzp-posts__empty" data-lzp-empty%1$s>%2$s</p>',
			$query->posts ? ' hidden' : '',
			esc_html( (string) $s['empty_text'] )
		);

		if ( $archive && $query->max_num_pages > 1 ) {
			$links = paginate_links(
				array(
					'total'     => (int) $query->max_num_pages,
					'current'   => max( 1, (int) get_query_var( 'paged' ) ),
					'prev_text' => self::icon( 'chevron-down', 'lzp-pager__prev' ) . '<span class="screen-reader-text">' . esc_html__( 'Previous page', 'lenz-plus' ) . '</span>',
					'next_text' => self::icon( 'chevron-down', 'lzp-pager__next' ) . '<span class="screen-reader-text">' . esc_html__( 'Next page', 'lenz-plus' ) . '</span>',
				)
			);
			if ( $links ) {
				echo '<nav class="lzp-pager" aria-label="' . esc_attr__( 'Pages', 'lenz-plus' ) . '">' . self::digits_html( $links ) . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core pagination markup.
			}
		}

		echo '</div>';
	}

	/**
	 * Category chip and «date · minutes».
	 *
	 * @param \WP_Post $post  Post.
	 * @param bool     $chip  Whether the first category is a chip.
	 * @param bool     $long  «۹ دقیقه مطالعه» instead of «۹ دقیقه».
	 */
	public static function meta_html( \WP_Post $post, bool $chip = true, bool $long = false ): string {
		$categories = Post_Parts::categories( $post );

		return '<div class="lzp-post-meta">'
			. ( $chip && $categories ? '<span class="lzp-chip">' . esc_html( $categories[0]->name ) . '</span>' : '' )
			. '<span class="lzp-post-meta__line">' . Post_Parts::time_html( $post ) . ' · ' . esc_html( Post_Parts::reading_label( $post, $long ) ) . '</span>'
			. '</div>';
	}

	/**
	 * One card.
	 *
	 * @param \WP_Post $post    Post.
	 * @param string   $ratio   Photo ratio.
	 * @param bool     $excerpt Whether the excerpt shows.
	 */
	private static function card_html( \WP_Post $post, string $ratio, bool $excerpt ): string {
		$title = get_the_title( $post );
		$cats  = wp_list_pluck( Post_Parts::categories( $post ), 'slug' );
		$text  = $excerpt ? wp_strip_all_tags( get_the_excerpt( $post ) ) : '';

		return sprintf(
			'<li class="lzp-post-card" data-lzp-cats="%1$s"><a class="lzp-post-card__link" href="%2$s">%3$s%4$s<span class="lzp-post-card__title">%5$s</span>%6$s</a></li>',
			esc_attr( implode( ' ', $cats ) ),
			esc_url( (string) get_permalink( $post ) ),
			Picture::frame(
				array( 'id' => (int) get_post_thumbnail_id( $post ) ),
				array(
					'ratio' => $ratio,
					'alt'   => $title,
					'class' => 'lzp-photo',
					'sizes' => '(max-width: 767px) 100vw, 33vw',
				)
			),
			self::meta_html( $post ),
			esc_html( $title ),
			'' !== $text ? '<span class="lzp-post-card__excerpt">' . esc_html( $text ) . '</span>' : ''
		);
	}
}
