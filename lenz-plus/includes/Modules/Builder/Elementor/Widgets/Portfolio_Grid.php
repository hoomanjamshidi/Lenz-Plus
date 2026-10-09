<?php
/**
 * Portfolio grid of the mockups: category chips with the number of
 * projects, then the projects as a masonry of photos in their own shapes,
 * each with its title written vertically along the end edge, the side
 * notch, and a play mark on video projects.
 *
 * On the portfolio archive (and its categories) it lists the page being
 * viewed, with page numbers, and the chips link to the category pages.
 * Elsewhere it lists the newest projects; when they all fit on the page,
 * filter.js filters them in place instead of following the chip links.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Assets;
use LenzPlus\Modules\Builder\Elementor\Picture;
use LenzPlus\Modules\Builder\Portfolio_Data;

defined( 'ABSPATH' ) || exit;

/**
 * The «Portfolio grid (Lenz+)» widget.
 */
final class Portfolio_Grid extends Portfolio_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-portfolio-grid';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Portfolio grid', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-gallery-masonry';
	}

	/** The in-place filter script. */
	public function get_script_depends(): array {
		return array( Assets::FILTER_HANDLE );
	}

	/** Content (source, filters) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Projects', 'lenz-plus' ) );

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Section title', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'description' => __( 'With a title, the category chips sit at the end of the title row (home page).', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'source',
			array(
				'label'       => __( 'Projects', 'lenz-plus' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'current',
				'options'     => array(
					'current' => __( 'Of the page being viewed', 'lenz-plus' ),
					'latest'  => __( 'Newest', 'lenz-plus' ),
				),
				'description' => __( '"Of the page being viewed" lists the portfolio archive or category page it is on, with page numbers; anywhere else it shows the newest projects.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'count',
			array(
				'label'       => __( 'Number of projects', 'lenz-plus' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 12,
				'min'         => 1,
				'max'         => 48,
				'description' => __( 'On archive pages, WordPress\'s "Blog pages show at most" setting decides.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'filters',
			array(
				'label'        => __( 'Category chips', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_count',
			array(
				'label'        => __( 'Number of projects beside the chips', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'filters' => 'yes' ),
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
			'notch',
			array(
				'label'        => __( 'Corner notch', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'column_width',
			array(
				'label'       => __( 'Column width', 'lenz-plus' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min' => 160,
						'max' => 480,
					),
				),
				'description' => __( 'Photos keep their own shapes in as many columns of about this width as fit.', 'lenz-plus' ),
				'selectors'   => array( '{{WRAPPER}} .lzp-pf__grid' => 'column-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Grid', 'lenz-plus' ) );
		$this->add_text_style( 'title', '.lzp-pf__title', array( 'label' => __( 'Project title', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the chips, the grid and the page numbers. */
	protected function render(): void {
		$s       = $this->get_settings_for_display();
		$archive = 'current' === $s['source'] && ( is_post_type_archive( Portfolio_Data::POST_TYPE ) || is_tax( Portfolio_Data::TAXONOMY ) );
		$query   = $archive ? $GLOBALS['wp_query'] : new \WP_Query(
			array(
				'post_type'           => Portfolio_Data::POST_TYPE,
				'post_status'         => 'publish',
				'posts_per_page'      => max( 1, (int) $s['count'] ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => false,
			)
		);

		if ( ! $query->have_posts() ) {
			$this->editor_hint( __( 'No projects yet. Add portfolio items in Lenz → Portfolios.', 'lenz-plus' ) );
			return;
		}

		// All projects on one page: the chips can filter in place.
		$in_place = ! $archive && (int) $query->found_posts <= count( $query->posts );

		echo '<div class="lzp-pf"' . ( $in_place ? ' data-lzp-filter' : '' ) . '>';

		$bar = '';
		if ( 'yes' === $s['filters'] ) {
			$all_url = get_post_type_archive_link( Portfolio_Data::POST_TYPE );
			$total   = $archive ? (int) $query->found_posts : count( $query->posts );

			$bar = self::filter_bar_html(
				array(
					'taxonomy'  => Portfolio_Data::TAXONOMY,
					'all_label' => (string) $s['all_label'],
					'all_url'   => $all_url ? $all_url : home_url( '/' ),
					'current'   => $archive && is_tax( Portfolio_Data::TAXONOMY ) ? (int) get_queried_object_id() : 0,
					'total'     => 'yes' === $s['show_count'] ? $total : -1,
					/* translators: %s: number of projects. */
					'one'       => __( '%s project', 'lenz-plus' ),
					/* translators: %s: number of projects. */
					'many'      => __( '%s projects', 'lenz-plus' ),
					'label'     => __( 'Portfolio categories', 'lenz-plus' ),
				)
			);
		}

		if ( '' !== (string) $s['title'] ) {
			echo Heading::markup( array( 'title' => (string) $s['title'] ), $bar ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Heading::markup() and filter_bar_html().
		} else {
			echo $bar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in filter_bar_html().
		}

		echo '<ul class="lzp-pf__grid">';
		foreach ( $query->posts as $post ) {
			$this->item_html( (int) $post->ID, 'yes' === $s['notch'] );
		}
		echo '</ul>';

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
	 * One project: its photo in its own shape, linked to the project.
	 *
	 * @param int  $post_id Item ID.
	 * @param bool $notch   Whether the side notch is drawn.
	 */
	private function item_html( int $post_id, bool $notch ): void {
		$title = get_the_title( $post_id );
		$cats  = wp_list_pluck( Portfolio_Data::terms( $post_id ), 'slug' );
		$photo = Picture::frame(
			array( 'id' => (int) get_post_thumbnail_id( $post_id ) ),
			array(
				// Without a featured image the placeholder takes the mockups' 4:5 frame.
				'ratio' => has_post_thumbnail( $post_id ) ? '' : '4/5',
				'alt'   => $title,
				'class' => 'lzp-photo',
				'sizes' => '(max-width: 767px) 50vw, 300px',
			)
		);

		$play = 'video' === Portfolio_Data::kind( $post_id ) ? self::icon( 'play-circle', 'lzp-pf__play' ) : '';

		printf(
			'<li class="lzp-pf__item" data-lzp-cats="%1$s"><a class="lzp-pf__link%2$s" href="%3$s">%4$s%5$s<span class="lzp-pf__title">%6$s</span></a></li>',
			esc_attr( implode( ' ', $cats ) ),
			$notch ? ' lzp-notch lzp-notch--side' : '',
			esc_url( (string) get_permalink( $post_id ) ),
			$photo, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Picture::frame().
			$play, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup from the bundled library.
			esc_html( $title )
		);
	}
}
