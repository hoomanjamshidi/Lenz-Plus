<?php
/**
 * The text of the post or project being viewed (a sample one while its
 * template is edited), styled for reading: paragraphs, headings, lists,
 * quotes, tables and pictures in the mockups' type. H2 and H3 get anchors,
 * which the table of contents links to.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Context;
use LenzPlus\Modules\Builder\Elementor\Post_Parts;

defined( 'ABSPATH' ) || exit;

/**
 * The «Post content (Lenz+)» widget.
 */
final class Post_Content extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-post-content';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Post content', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-post-content';
	}

	/** Reads the viewed post: never cached by Elementor. */
	protected function is_dynamic_content(): bool {
		return true;
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'content', 'text', 'post', 'article', 'متن', 'محتوا', 'مقاله' ) );
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Content', 'lenz-plus' ) );

		$this->add_control(
			'note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Shows the text of the post or project being viewed. While its template is edited, the newest one stands in.', 'lenz-plus' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->add_control(
			'size',
			array(
				'label'   => __( 'Text size', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'md',
				'options' => array(
					'md' => __( 'Medium (project pages)', 'lenz-plus' ),
					'lg' => __( 'Large (articles)', 'lenz-plus' ),
				),
			)
		);

		$this->add_responsive_control(
			'max_width',
			array(
				'label'       => __( 'Line length', 'lenz-plus' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px', 'ch' ),
				'range'       => array(
					'px' => array(
						'min' => 360,
						'max' => 1200,
					),
				),
				'description' => __( 'Leave empty to fill the column.', 'lenz-plus' ),
				'selectors'   => array( '{{WRAPPER}} .lzp-prose' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Content', 'lenz-plus' ) );
		$this->add_text_style( 'text', '.lzp-prose' );
		$this->add_text_style( 'headings', '.lzp-prose :is(h2, h3, h4)', array( 'label' => __( 'Headings', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the text. */
	protected function render(): void {
		$s    = $this->get_settings_for_display();
		$post = Context::post();
		if ( ! $post ) {
			$this->editor_hint( __( 'Publish a post or a project to see its text here.', 'lenz-plus' ) );
			return;
		}

		Context::run_post(
			$post,
			static function ( \WP_Post $item ) use ( $s ) {
				echo '<div class="lzp-prose' . ( 'lg' === $s['size'] ? ' lzp-prose--lg' : '' ) . '">';
				echo Post_Parts::content( $item )['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the post content after WordPress's own filters.
				wp_link_pages(
					array(
						'before' => '<nav class="lzp-prose__pages" aria-label="' . esc_attr__( 'Post pages', 'lenz-plus' ) . '">',
						'after'  => '</nav>',
					)
				);
				echo '</div>';
			}
		);
	}
}
