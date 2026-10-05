<?php
/**
 * Content types an "Archive" button can link to, and when it is current.
 *
 * On Lenz the useful ones are its portfolio and video archives, the blog and
 * the shop. The archive URL is looked up at render time, because Lenz
 * registers its post types on `init` after this plugin boots.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Bottom_Nav;

defined( 'ABSPATH' ) || exit;

/**
 * Post types with a list page, and current-page detection for them.
 */
final class Archive_Types {

	/**
	 * Public post types whose list has its own page, for the editor.
	 *
	 * @return array<string, string> Post type name => plural label.
	 */
	public static function choices(): array {
		$types = array();

		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $type ) {
			if ( 'attachment' !== $type->name && get_post_type_archive_link( $type->name ) ) {
				$types[ $type->name ] = $type->labels->name;
			}
		}

		return $types;
	}

	/**
	 * Whether the request shows the type's list, one of its items, or one of
	 * its category/tag pages (e.g. a `portfolio-cat` term).
	 *
	 * @param string $post_type Post type name.
	 */
	public static function is_current( string $post_type ): bool {
		if ( is_singular( $post_type ) || is_post_type_archive( $post_type ) ) {
			return true;
		}

		// The blog's list is the posts page; its terms are categories and tags, which is_tax() skips.
		if ( 'post' === $post_type && ( is_home() || is_category() || is_tag() || is_author() || is_date() ) ) {
			return true;
		}

		$taxonomies = get_object_taxonomies( $post_type );

		return $taxonomies && is_tax( $taxonomies );
	}
}
