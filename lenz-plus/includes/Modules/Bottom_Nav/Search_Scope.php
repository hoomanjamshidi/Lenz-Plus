<?php
/**
 * Which post types a search button covers.
 *
 * Saved post types are re-checked on every request, because the plugin that
 * registered one may since have been deactivated. An empty list means
 * "everything WordPress search normally includes".
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Bottom_Nav;

use LenzPlus\Core\Search_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Validates a search button's post types for live and full results.
 */
final class Search_Scope {

	/**
	 * Post types an admin can search in.
	 *
	 * @return array<string, string> Post type name => plural label.
	 */
	public static function choices(): array {
		return Search_Query::searchable_types();
	}

	/**
	 * The item's saved post types that can still be searched.
	 *
	 * @param array $item Saved search item.
	 * @return string[] Empty when the item searches everything.
	 */
	public static function post_types( array $item ): array {
		return array_values( array_intersect( $item['search_post_types'], array_keys( self::choices() ) ) );
	}

	/**
	 * The `post_type` sent to the classic results page (`/?s=`), or '' for
	 * everything.
	 *
	 * Only a single type can be sent: Lenz's search template reads
	 * `post_type` from the query as one string (`Utils::get_archive_post_type()`
	 * passes it to `convert_chars()`), so an array breaks the page. With
	 * several types the results page searches everything, a superset, so
	 * nothing the admin chose goes missing; the live results stay exact.
	 *
	 * @param string[] $post_types Validated post types.
	 */
	public static function for_results_page( array $post_types ): string {
		return 1 === count( $post_types ) ? $post_types[0] : '';
	}
}
