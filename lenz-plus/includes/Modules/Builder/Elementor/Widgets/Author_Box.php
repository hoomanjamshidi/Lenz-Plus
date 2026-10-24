<?php
/**
 * «درباره نویسنده» under an article: the author's picture, name, role and
 * biographical info, with a link to all of their posts. Reads the author of
 * the post being viewed; hidden when the author has no bio and the widget
 * is set to need one.
 *
 * A visible author with a bio is also what search engines look for to
 * judge who wrote a page (experience and expertise signals).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Elementor\Post_Parts;

defined( 'ABSPATH' ) || exit;

/**
 * The «Author box (Lenz+)» widget.
 */
final class Author_Box extends Blog_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-author-box';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Author box', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-person';
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Author box', 'lenz-plus' ) );

		$this->add_control(
			'label',
			array(
				'label'   => __( 'Label', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'About the author', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'link_text',
			array(
				'label'       => __( '"All posts" text', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'All posts', 'lenz-plus' ),
				'description' => __( 'Empty: no link.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'needs_bio',
			array(
				'label'        => __( 'Hide when the author has no bio', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Author box', 'lenz-plus' ) );
		$this->add_box_style( 'box', '.lzp-author' );
		$this->add_text_style( 'bio', '.lzp-author__bio', array( 'label' => __( 'Bio', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the box. */
	protected function render(): void {
		$s = $this->get_settings_for_display();

		$this->with_post(
			function ( \WP_Post $post ) use ( $s ) {
				$author = (int) $post->post_author;
				$bio    = trim( (string) get_the_author_meta( 'description', $author ) );

				if ( '' === $bio && 'yes' === $s['needs_bio'] ) {
					$this->editor_hint( __( 'Write a biographical info in the author\'s profile (Users → Profile) to show this box.', 'lenz-plus' ) );
					return;
				}

				$url  = get_author_posts_url( $author );
				$link = '' !== (string) $s['link_text']
					? '<a class="lzp-author__more" href="' . esc_url( $url ) . '">' . esc_html( $s['link_text'] ) . self::icon( 'arrow-forward' ) . '</a>'
					: '';

				printf(
					'<section class="lzp-author" aria-label="%1$s">%2$s<div class="lzp-author__body">%3$s<p class="lzp-author__name"><a href="%4$s" rel="author">%5$s</a></p>%6$s%7$s</div></section>',
					esc_attr( (string) $s['label'] ),
					Post_Parts::avatar( $author, 80, 'lzp-author__avatar' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core avatar markup.
					'' !== (string) $s['label'] ? '<span class="lzp-author__label">' . esc_html( $s['label'] ) . '</span>' : '',
					esc_url( $url ),
					esc_html( get_the_author_meta( 'display_name', $author ) ),
					'' !== $bio ? '<p class="lzp-author__bio">' . nl2br( esc_html( $bio ) ) . '</p>' : '',
					$link // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
				);
			}
		);
	}
}
