<?php
/**
 * Prints Elementor templates outside the post content (headers and footers,
 * later portfolio, blog and course layouts).
 *
 * Elementor normally discovers its CSS while rendering, which for content
 * printed after `wp_head` means late styles and a flash of unstyled content.
 * enqueue() is therefore called during `wp_enqueue_scripts` for every
 * template the request will print.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Asset loading and markup of templates printed by the plugin.
 */
final class Renderer {

	/** @var int[] Templates already enqueued. */
	private static $enqueued = array();

	/** Whether Elementor's front end is loaded and can render documents. */
	public static function elementor_ready(): bool {
		return did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend );
	}

	/**
	 * Enqueues Elementor's frontend styles, the template's generated CSS and
	 * the builder's stylesheet and script. Must run on `wp_enqueue_scripts`.
	 *
	 * @param int $template_id Template ID.
	 */
	public static function enqueue( int $template_id ): void {
		if ( ! self::elementor_ready() || in_array( $template_id, self::$enqueued, true ) ) {
			return;
		}

		self::$enqueued[] = $template_id;

		\Elementor\Plugin::$instance->frontend->enqueue_styles();

		if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			\Elementor\Core\Files\CSS\Post::create( $template_id )->enqueue();
		}

		wp_enqueue_style( Assets::HANDLE );
		wp_enqueue_script( Assets::HANDLE );
	}

	/**
	 * Rendered template markup, wrapped for styling hooks ('' when empty).
	 *
	 * @param int $template_id Template ID.
	 */
	public static function render( int $template_id ): string {
		if ( ! self::elementor_ready() ) {
			return '';
		}

		$content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id );

		if ( '' === trim( (string) $content ) ) {
			return '';
		}

		return sprintf(
			'<div class="lzp-tpl lzp-tpl--%1$s" data-lzp-template="%2$d">%3$s</div>',
			esc_attr( Template_Post_Type::type_of( $template_id ) ),
			$template_id,
			$content
		);
	}
}
