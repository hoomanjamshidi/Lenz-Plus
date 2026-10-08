<?php
/**
 * Request context for the widgets: whether they render inside the Elementor
 * editor, where hints are shown and samples stand in for real content, and
 * which item a single-item widget (project header, gallery…) shows.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Static helpers about the current rendering context.
 */
final class Context {

	/** @var array<string, int> Sample item per post type, for this request. */
	private static $samples = array();

	/** Whether we are inside the Elementor editor or its preview iframe. */
	public static function is_editor(): bool {
		if ( ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance ) ) {
			return false;
		}

		$elementor = \Elementor\Plugin::$instance;

		return ( isset( $elementor->editor ) && $elementor->editor->is_edit_mode() )
			|| ( isset( $elementor->preview ) && $elementor->preview->is_preview_mode() );
	}

	/**
	 * The item that best shows a template while it is edited or previewed:
	 * the newest one with the most to show (project details, a gallery, a
	 * picture), else the newest. 0 when there is none.
	 *
	 * @param string $post_type Post type.
	 */
	public static function sample_id( string $post_type ): int {
		if ( isset( self::$samples[ $post_type ] ) ) {
			return self::$samples[ $post_type ];
		}

		$args = array(
			'post_type'           => $post_type,
			'post_status'         => 'publish',
			'posts_per_page'      => 1,
			'fields'              => 'ids',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		);

		$ids = array();
		foreach ( array( '_lzp_project', '_gallery', '_thumbnail_id', '' ) as $meta_key ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one lookup per request, editor and previews only.
			$ids = get_posts( '' !== $meta_key ? $args + array( 'meta_key' => $meta_key ) : $args );
			if ( $ids ) {
				break;
			}
		}

		self::$samples[ $post_type ] = $ids ? (int) $ids[0] : 0;

		return self::$samples[ $post_type ];
	}

	/** Post types whose single pages the item widgets (content, project header…) read. */
	public const ITEM_TYPES = array( 'post', 'portfolio' );

	/**
	 * The post the item widgets show: the post or project being viewed, the
	 * current one in a loop, or, while a template is edited or previewed on
	 * its own, the newest item of the template's kind. Pages are never
	 * returned: a content widget on a page would print itself.
	 */
	public static function post(): ?\WP_Post {
		if ( is_singular( self::ITEM_TYPES ) ) {
			$viewed = get_post( get_queried_object_id() );

			return $viewed instanceof \WP_Post ? $viewed : null;
		}

		$current = $GLOBALS['post'] ?? null;
		if ( in_the_loop() && $current instanceof \WP_Post && in_array( $current->post_type, self::ITEM_TYPES, true ) ) {
			return $current;
		}

		$kind = Template_Post_Type::type_of( self::current_template_id() );
		if ( '' === $kind ) {
			return null;
		}

		$sample = self::sample_id( in_array( $kind, array( 'portfolio', 'portfolio_archive' ), true ) ? 'portfolio' : 'post' );

		return $sample ? get_post( $sample ) : null;
	}

	/**
	 * ID of the item a single-item widget shows when it is of a type, else 0.
	 *
	 * @param string $post_type Post type.
	 */
	public static function current_item( string $post_type ): int {
		$post = self::post();

		return $post && $post->post_type === $post_type ? (int) $post->ID : 0;
	}

	/**
	 * Runs a callback with a post set up as the global post, so template
	 * tags (the content, comments) work in the editor too. Always restores
	 * the previous global post.
	 *
	 * @param \WP_Post $target   Post.
	 * @param callable $callback Receives the post.
	 * @return mixed Callback result.
	 */
	public static function run_post( \WP_Post $target, callable $callback ) {
		global $post;

		$previous = $post;
		$switch   = ! $previous instanceof \WP_Post || (int) $previous->ID !== (int) $target->ID;

		if ( $switch ) {
			$post = $target; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restored below.
			setup_postdata( $post );
		}

		try {
			return $callback( $target );
		} finally {
			if ( $switch ) {
				$post = $previous; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				if ( $post instanceof \WP_Post ) {
					setup_postdata( $post );
				}
			}
		}
	}

	/** The template being edited or rendered on its own (0 for none). */
	private static function current_template_id(): int {
		$id = 0;

		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->documents ) ) {
			$document = \Elementor\Plugin::$instance->documents->get_current();
			if ( $document ) {
				$id = (int) $document->get_main_id();
			}
		}

		if ( ! $id || Template_Post_Type::POST_TYPE !== get_post_type( $id ) ) {
			$id = is_singular( Template_Post_Type::POST_TYPE ) ? (int) get_queried_object_id() : 0;
		}

		return Template_Post_Type::POST_TYPE === get_post_type( $id ) ? $id : 0;
	}
}
