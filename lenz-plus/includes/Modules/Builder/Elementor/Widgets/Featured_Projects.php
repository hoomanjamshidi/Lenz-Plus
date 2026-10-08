<?php
/**
 * Featured projects of the portfolio page: wide cards with the cover photo
 * beside the category, year, title, summary, the first facts and a "view
 * project" link. Projects marked as featured (Project details box) come
 * first; the newest fill the rest.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Elementor\Picture;
use LenzPlus\Modules\Builder\Portfolio_Data;

defined( 'ABSPATH' ) || exit;

/**
 * The «Featured projects (Lenz+)» widget.
 */
final class Featured_Projects extends Portfolio_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-featured-projects';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Featured projects', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-image-box';
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Projects', 'lenz-plus' ) );

		$this->add_control(
			'count',
			array(
				'label'   => __( 'Number of projects', 'lenz-plus' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 3,
				'min'     => 1,
				'max'     => 12,
			)
		);

		$this->add_control(
			'alternate',
			array(
				'label'        => __( 'Alternate photo sides', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'link_text',
			array(
				'label'   => __( 'Link text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'View project', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Cards', 'lenz-plus' ) );
		$this->add_box_style( 'card', '.lzp-feat__item', array( 'padding' => false ) );
		$this->add_text_style( 'title', '.lzp-feat__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'text', '.lzp-feat__text', array( 'label' => __( 'Summary', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Featured projects first, then the newest.
	 *
	 * @param int $count Projects wanted.
	 * @return int[]
	 */
	private static function project_ids( int $count ): array {
		$base = array(
			'post_type'      => Portfolio_Data::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => $count,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		);

		$ids = get_posts(
			$base + array(
				'meta_key'   => Portfolio_Data::META_FEATURED, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- a few projects carry the flag.
				'meta_value' => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		if ( count( $ids ) < $count ) {
			$more = get_posts( array_merge( $base, array( 'post__not_in' => $ids ? $ids : array( 0 ) ) ) );
			$ids  = array_merge( $ids, array_slice( $more, 0, $count - count( $ids ) ) );
		}

		return array_map( 'intval', $ids );
	}

	/** Prints the cards. */
	protected function render(): void {
		$s   = $this->get_settings_for_display();
		$ids = self::project_ids( max( 1, (int) $s['count'] ) );

		if ( ! $ids ) {
			$this->editor_hint( __( 'No projects yet. Add portfolio items in Lenz → Portfolios.', 'lenz-plus' ) );
			return;
		}

		echo '<div class="lzp-feat' . ( 'yes' === $s['alternate'] ? ' lzp-feat--alternate' : '' ) . '">';
		foreach ( $ids as $post_id ) {
			$this->card_html( $post_id, (string) $s['link_text'] );
		}
		echo '</div>';
	}

	/**
	 * One card.
	 *
	 * @param int    $post_id   Item ID.
	 * @param string $link_text Link text.
	 */
	private function card_html( int $post_id, string $link_text ): void {
		$url   = (string) get_permalink( $post_id );
		$title = get_the_title( $post_id );
		$text  = Portfolio_Data::summary( $post_id );
		$facts = array_slice( Portfolio_Data::details( $post_id )['facts'], 0, 3 );

		$foot = '';
		foreach ( $facts as $fact ) {
			$foot .= '<span class="lzp-feat__fact">' . esc_html( $fact[1] ) . '</span>';
		}
		$foot .= '<a class="lzp-feat__more" href="' . esc_url( $url ) . '"><span>' . esc_html( $link_text ) . '</span>' . self::icon( 'arrow-forward' ) . '</a>';

		$photo = Picture::frame(
			array( 'id' => (int) get_post_thumbnail_id( $post_id ) ),
			array(
				'ratio' => '',
				'label' => $title,
				'alt'   => $title,
				'class' => 'lzp-photo',
			)
		);

		printf(
			'<article class="lzp-feat__item"><div class="lzp-feat__media">%1$s</div><div class="lzp-feat__body">%2$s<h3 class="lzp-feat__title"><a href="%3$s">%4$s</a></h3>%5$s<div class="lzp-feat__foot">%6$s</div></div></article>',
			$photo, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Picture::frame().
			self::meta_html( $post_id, 1 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in meta_html().
			esc_url( $url ),
			esc_html( $title ),
			'' !== $text ? '<p class="lzp-feat__text">' . esc_html( $text ) . '</p>' : '',
			$foot // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		);
	}
}
