<?php
/**
 * Base for the blog widgets (post grid, featured post, article header,
 * table of contents, share, progress, comments, popular posts, search).
 * They add the blog stylesheet and script, so other pages never load them.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use LenzPlus\Modules\Builder\Assets;
use LenzPlus\Modules\Builder\Context;

defined( 'ABSPATH' ) || exit;

/**
 * Assets and the post context shared by the blog widgets.
 */
abstract class Blog_Base extends Section_Base {

	/** Section and blog stylesheets. */
	public function get_style_depends(): array {
		return array( Assets::HANDLE, Assets::SECTIONS_HANDLE, Assets::BLOG_HANDLE );
	}

	/** Share, table of contents and progress script. */
	public function get_script_depends(): array {
		return array( Assets::BLOG_HANDLE );
	}

	/** Posts change: never cached by Elementor. */
	protected function is_dynamic_content(): bool {
		return true;
	}

	/** Panel search terms shared by the blog widgets. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'blog', 'post', 'article', 'وبلاگ', 'مقاله', 'بلاگ' ) );
	}

	/**
	 * Runs a render callback with the post being viewed as the global post
	 * (a sample one while the template is edited); prints an editor hint
	 * when there is none.
	 *
	 * @param callable $callback Receives the post.
	 */
	protected function with_post( callable $callback ): void {
		$post = Context::post();

		if ( $post && 'post' === $post->post_type ) {
			Context::run_post( $post, $callback );
			return;
		}

		$this->editor_hint( __( 'Publish a blog post to see real data here.', 'lenz-plus' ) );
	}
}
