<?php
/**
 * «پرخواننده‌ترین‌ها»: the most read posts as a numbered list of titles in a
 * bordered sidebar card. Lenz counts views in each post's `_views` meta;
 * posts without views fall back to the newest.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * The «Popular posts (Lenz+)» widget.
 */
final class Popular_Posts extends Blog_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-popular-posts';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Popular posts', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-number-field';
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Popular posts', 'lenz-plus' ) );

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Most read', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'count',
			array(
				'label'   => __( 'Number of posts', 'lenz-plus' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 4,
				'min'     => 1,
				'max'     => 10,
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Card', 'lenz-plus' ) );
		$this->add_box_style( 'box', '.lzp-popular' );
		$this->add_text_style(
			'link',
			'.lzp-popular__title',
			array(
				'label' => __( 'Titles', 'lenz-plus' ),
				'hover' => '.lzp-popular__link:hover .lzp-popular__title',
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Most viewed posts, topped up with the newest.
	 *
	 * @param int $count Posts wanted.
	 * @return int[]
	 */
	private static function post_ids( int $count ): array {
		$base = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $count,
			'fields'              => 'ids',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		);

		$ids = get_posts(
			$base + array(
				'meta_key'   => '_views', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Lenz's view counter; a handful of posts.
				'orderby'    => 'meta_value_num',
				'order'      => 'DESC',
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_views',
						'value'   => 0,
						'compare' => '>',
						'type'    => 'NUMERIC',
					),
				),
			)
		);

		if ( count( $ids ) < $count ) {
			$base['post__not_in'] = $ids;
			$ids                  = array_merge( $ids, get_posts( $base ) );
		}

		return array_slice( array_map( 'intval', $ids ), 0, $count );
	}

	/** Prints the list. */
	protected function render(): void {
		$s   = $this->get_settings_for_display();
		$ids = self::post_ids( max( 1, (int) $s['count'] ) );

		if ( ! $ids ) {
			$this->editor_hint( __( 'Publish a blog post to see real data here.', 'lenz-plus' ) );
			return;
		}

		$items = '';
		foreach ( $ids as $index => $id ) {
			$items .= sprintf(
				'<li><a class="lzp-popular__link" href="%1$s"><span class="lzp-popular__num" aria-hidden="true">%2$s</span><span class="lzp-popular__title">%3$s</span></a></li>',
				esc_url( (string) get_permalink( $id ) ),
				esc_html( self::digits( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ) ),
				esc_html( get_the_title( $id ) )
			);
		}

		printf(
			'<div class="lzp-popular lzp-side-card">%1$s<ol class="lzp-popular__list">%2$s</ol></div>',
			'' !== (string) $s['title'] ? '<h3 class="lzp-side-card__title">' . esc_html( $s['title'] ) . '</h3>' : '',
			$items // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		);
	}
}
