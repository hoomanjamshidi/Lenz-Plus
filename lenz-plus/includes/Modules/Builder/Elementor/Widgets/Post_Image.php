<?php
/**
 * The featured image of the post or project being viewed in a large
 * rounded frame (16:9 in the mockups), loaded first as the page's largest
 * picture. Nothing shows when it has none (the striped frame in the editor).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Context;
use LenzPlus\Modules\Builder\Elementor\Picture;

defined( 'ABSPATH' ) || exit;

/**
 * The «Featured image (Lenz+)» widget.
 */
final class Post_Image extends Blog_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-post-image';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Featured image', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-featured-image';
	}

	/** Content controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Featured image', 'lenz-plus' ) );
		$this->add_ratio_control( 'ratio', '16/9' );

		$this->add_control(
			'caption',
			array(
				'label'        => __( 'Caption', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => __( 'The image\'s caption from the media library.', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();
	}

	/** Prints the frame. */
	protected function render(): void {
		$s     = $this->get_settings_for_display();
		$post  = Context::post();
		$image = $post ? (int) get_post_thumbnail_id( $post ) : 0;

		if ( ! $image && ! $this->in_editor() ) {
			return;
		}

		$caption = $image && 'yes' === $s['caption'] ? wp_get_attachment_caption( $image ) : '';

		$photo = Picture::frame(
			array( 'id' => $image ),
			array(
				'ratio'   => (string) $s['ratio'],
				'alt'     => $post ? get_the_title( $post ) : '',
				'class'   => 'lzp-photo',
				'loading' => 'high',
				'sizes'   => '(max-width: 1080px) 100vw, 1000px',
			)
		);

		printf(
			'<figure class="lzp-post-image">%1$s%2$s</figure>',
			$photo, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Picture::frame().
			$caption ? '<figcaption class="lzp-post-image__caption">' . esc_html( $caption ) . '</figcaption>' : ''
		);
	}
}
