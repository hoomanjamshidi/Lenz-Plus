<?php
/**
 * Decides which template a request uses and builds preview links.
 *
 * Page designs need no decision: pages made from them carry their own
 * Elementor content. Template kinds that replace a part of the theme
 * (header, footer, portfolio, blog and course pages) add their rules here.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Template choice for the current request, and admin preview URLs.
 */
final class Resolver {

	/**
	 * Where the admin previews a template.
	 *
	 * @param string $type Template kind.
	 * @param string $ref  Template ID (or a keyword such as `theme`).
	 */
	public function preview_url( string $type, string $ref ): string {
		if ( in_array( $type, Schema::PAGE_TYPES, true ) && is_numeric( $ref ) ) {
			// A page design is a whole page: its own URL shows it between the site's header and footer.
			return (string) get_permalink( (int) $ref );
		}

		return '';
	}
}
