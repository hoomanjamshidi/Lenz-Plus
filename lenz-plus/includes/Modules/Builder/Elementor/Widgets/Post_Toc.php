<?php
/**
 * «در این مقاله»: the article's H2 headings (and optionally H3) as links in
 * a bordered card, for the sticky sidebar. The section being read is marked
 * while scrolling (blog.js). Hidden when the article has too few headings.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Elementor\Post_Parts;

defined( 'ABSPATH' ) || exit;

/**
 * The «Table of contents (Lenz+)» widget.
 */
final class Post_Toc extends Blog_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-post-toc';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Table of contents', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-table-of-contents';
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Table of contents', 'lenz-plus' ) );

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'In this article', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'depth',
			array(
				'label'   => __( 'Headings', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '2',
				'options' => array(
					'2' => __( 'H2 only', 'lenz-plus' ),
					'3' => __( 'H2 and H3', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'minimum',
			array(
				'label'       => __( 'Hide with fewer headings than', 'lenz-plus' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 2,
				'min'         => 1,
				'max'         => 10,
				'description' => __( 'A short post without sections needs no table of contents.', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Table of contents', 'lenz-plus' ) );
		$this->add_box_style( 'box', '.lzp-toc' );
		$this->add_text_style(
			'link',
			'.lzp-toc__link',
			array(
				'label' => __( 'Links', 'lenz-plus' ),
				'hover' => '.lzp-toc__link:hover',
			)
		);
		$this->end_controls_section();
	}

	/** Prints the card. */
	protected function render(): void {
		$s = $this->get_settings_for_display();

		$this->with_post(
			function ( \WP_Post $post ) use ( $s ) {
				$depth    = (int) $s['depth'];
				$headings = array_values(
					array_filter(
						Post_Parts::content( $post )['headings'],
						static function ( $heading ) use ( $depth ) {
							return $heading['level'] <= $depth;
						}
					)
				);

				if ( count( $headings ) < max( 1, (int) $s['minimum'] ) ) {
					$this->editor_hint( __( 'The table of contents appears when the post has enough H2 headings.', 'lenz-plus' ) );
					return;
				}

				$items = '';
				foreach ( $headings as $heading ) {
					$items .= sprintf(
						'<li class="lzp-toc__item lzp-toc__item--h%1$d"><a class="lzp-toc__link" href="#%2$s">%3$s</a></li>',
						$heading['level'],
						esc_attr( $heading['id'] ),
						esc_html( $heading['text'] )
					);
				}

				printf(
					'<nav class="lzp-toc lzp-side-card" data-lzp-toc aria-labelledby="%1$s"><h3 class="lzp-side-card__title" id="%1$s">%2$s</h3><ol class="lzp-toc__list">%3$s</ol></nav>',
					esc_attr( 'lzp-toc-' . $this->get_id() ),
					esc_html( (string) $s['title'] ),
					$items // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
				);
			}
		);
	}
}
