<?php
/**
 * Reads a portfolio item (Lenz's `portfolio` post type) for the widgets:
 * Lenz's own gallery (`_gallery` attachment IDs and `_external_links`), its
 * categories, and the project details the plugin adds (Project_Meta_Box):
 * summary, year, facts, the "what was done" list, the client's quote and the
 * "featured" flag.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

use LenzPlus\Core\Persian;

defined( 'ABSPATH' ) || exit;

/**
 * Static readers of portfolio items.
 */
final class Portfolio_Data {

	public const POST_TYPE = 'portfolio';

	public const TAXONOMY = 'portfolio-cat';

	/** The plugin's project details (one array). */
	public const META = '_lzp_project';

	/** Featured flag, a separate key so lists can query it. */
	public const META_FEATURED = '_lzp_featured';

	/**
	 * Empty project details.
	 *
	 * @return array{summary:string, year:string, facts:array, done:array, quote:string, quote_name:string, quote_role:string}
	 */
	public static function empty_details(): array {
		return array(
			'summary'    => '',
			'year'       => '',
			// [label, value] pairs («کارفرما» → «برند نُوا»).
			'facts'      => array(),
			// Lines of the "what was done" checklist.
			'done'       => array(),
			'quote'      => '',
			'quote_name' => '',
			'quote_role' => '',
		);
	}

	/**
	 * Project details of an item.
	 *
	 * @param int $post_id Item ID.
	 */
	public static function details( int $post_id ): array {
		$saved = get_post_meta( $post_id, self::META, true );

		return array_merge( self::empty_details(), is_array( $saved ) ? array_intersect_key( $saved, self::empty_details() ) : array() );
	}

	/**
	 * The summary under the title: the project's own, else the excerpt.
	 *
	 * @param int $post_id Item ID.
	 */
	public static function summary( int $post_id ): string {
		$summary = self::details( $post_id )['summary'];
		if ( '' !== $summary ) {
			return $summary;
		}

		$post = get_post( $post_id );

		return $post && '' !== $post->post_excerpt ? wp_strip_all_tags( $post->post_excerpt ) : '';
	}

	/**
	 * The year shown beside the categories: the project's own, else the
	 * year it was published (Persian calendar on Persian sites).
	 *
	 * @param int $post_id Item ID.
	 */
	public static function year( int $post_id ): string {
		$year = self::details( $post_id )['year'];
		if ( '' !== $year ) {
			return $year;
		}

		$time = (int) get_post_time( 'U', true, $post_id );
		if ( ! $time ) {
			return '';
		}

		if ( Persian::is_site_persian() ) {
			$date = Persian::gregorian_to_jalali( (int) gmdate( 'Y', $time ), (int) gmdate( 'n', $time ), (int) gmdate( 'j', $time ) );

			return (string) $date[0];
		}

		return gmdate( 'Y', $time );
	}

	/**
	 * Categories of an item.
	 *
	 * @param int $post_id Item ID.
	 * @return \WP_Term[]
	 */
	public static function terms( int $post_id ): array {
		$terms = get_the_terms( $post_id, self::TAXONOMY );

		return is_array( $terms ) ? $terms : array();
	}

	/**
	 * Gallery of an item in Lenz's order: uploaded images and videos, with
	 * the external links put back at their positions.
	 *
	 * @param int $post_id Item ID.
	 * @return array<int, array{id:int, url:string, type:string}> Type `image` or `video`.
	 */
	public static function gallery( int $post_id ): array {
		$items = array();

		foreach ( (array) get_post_meta( $post_id, '_gallery', true ) as $attachment_id ) {
			$attachment_id = (int) $attachment_id;
			$url           = $attachment_id ? (string) wp_get_attachment_url( $attachment_id ) : '';
			if ( '' === $url ) {
				continue;
			}

			$items[] = array(
				'id'   => $attachment_id,
				'url'  => $url,
				'type' => 0 === strpos( (string) get_post_mime_type( $attachment_id ), 'video' ) ? 'video' : 'image',
			);
		}

		$links = get_post_meta( $post_id, '_external_links', true );
		if ( is_array( $links ) ) {
			usort(
				$links,
				static function ( $a, $b ): int {
					return (int) ( $a['position'] ?? 0 ) <=> (int) ( $b['position'] ?? 0 );
				}
			);

			foreach ( $links as $link ) {
				if ( empty( $link['url'] ) ) {
					continue;
				}

				$item = array(
					'id'   => 0,
					'url'  => (string) $link['url'],
					'type' => 'video' === ( $link['type'] ?? '' ) ? 'video' : 'image',
				);
				// Lenz's positions start at 1.
				array_splice( $items, max( 0, (int) ( $link['position'] ?? count( $items ) + 1 ) - 1 ), 0, array( $item ) );
			}
		}

		return $items;
	}

	/**
	 * Whether an item is a video project: its gallery opens with a video.
	 *
	 * @param int $post_id Item ID.
	 */
	public static function is_video( int $post_id ): bool {
		$gallery = self::gallery( $post_id );

		return $gallery && 'video' === $gallery[0]['type'];
	}

	/**
	 * Whether an item is marked as featured.
	 *
	 * @param int $post_id Item ID.
	 */
	public static function is_featured( int $post_id ): bool {
		return '1' === get_post_meta( $post_id, self::META_FEATURED, true );
	}
}
