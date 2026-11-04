<?php
/**
 * One page per service, each filled with its own content.
 *
 * The single service designs are ordinary page designs, but "Create page"
 * would copy their sample service. Here a design is rebuilt with the copy
 * of each service on the services page (Presets\Service_Content) and saved
 * into a new page that remembers its design (`_lzp_design`, so it is listed
 * with the other pages) and its service (`_lzp_service`).
 *
 * Once a service has a published page, the services page's jump links to
 * that service's band (`#photo`, `#religious`…) lead to the page instead.
 * Only links still pointing at those anchors are changed, in pages made
 * from a services design; a link the owner pointed elsewhere stays.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

use LenzPlus\Modules\Builder\Presets\Catalog;
use LenzPlus\Modules\Builder\Presets\Service_Content;

defined( 'ABSPATH' ) || exit;

/**
 * Creates and lists service pages and links the services page to them.
 */
final class Service_Pages {

	/** Service key (Service_Content) of a page made for one service. */
	public const META_SERVICE = '_lzp_service';

	/** Page statuses that count as "already made". */
	private const MADE_STATUSES = array( 'publish', 'draft', 'pending', 'private', 'future' );

	/**
	 * The services with the pages already made for them, for the Pages tab.
	 *
	 * @return array<int, array{key:string, name:string, lead:string, made:array}>
	 */
	public static function for_admin(): array {
		$list = array();
		foreach ( Service_Content::all() as $key => $service ) {
			$list[] = array(
				'key'  => $key,
				'name' => $service['name'],
				'lead' => $service['lead'],
				'made' => array_map(
					static function ( \WP_Post $page ): array {
						return array(
							'title'   => html_entity_decode( get_the_title( $page ), ENT_QUOTES, 'UTF-8' ),
							'status'  => $page->post_status,
							'editUrl' => Template_Post_Type::edit_url( (int) $page->ID ),
						);
					},
					self::pages_of( $key )
				),
			);
		}

		return $list;
	}

	/**
	 * Preset keys of the single service designs, in admin order.
	 *
	 * @return string[]
	 */
	public static function designs(): array {
		return array_keys(
			array_filter(
				Catalog::all(),
				static function ( array $preset ): bool {
					return 'service' === $preset['type'];
				}
			)
		);
	}

	/**
	 * Makes a page for each service, or for one, in a design. With `$only`
	 * empty, services that already have a page are skipped.
	 *
	 * @param string $preset  Single service preset key.
	 * @param string $only    One service key, '' for every service without a page.
	 * @param bool   $publish Publish the pages at once instead of keeping drafts.
	 * @return int[] IDs of the new pages.
	 */
	public static function create( string $preset, string $only, bool $publish ): array {
		$services = Service_Content::all();
		if ( ! in_array( $preset, self::designs(), true ) || ( '' !== $only && ! isset( $services[ $only ] ) ) ) {
			return array();
		}

		$design_id = Library::preset_template( $preset );
		if ( ! $design_id ) {
			return array();
		}

		$created = array();
		foreach ( $services as $key => $service ) {
			if ( '' !== $only ? $only !== $key : (bool) self::pages_of( $key ) ) {
				continue;
			}

			$page_id = Design_Pages::create( $design_id, $service['name'], $publish ? 'publish' : 'draft', Catalog::build( $preset, array( $key ) ) );
			if ( $page_id ) {
				update_post_meta( $page_id, self::META_SERVICE, $key );
				$created[] = $page_id;
			}
		}

		if ( $created ) {
			self::link_services_pages();
		}

		return $created;
	}

	/**
	 * Points the services page's links to a service's band (`#photo`…) at
	 * that service's published page.
	 *
	 * @return int Number of services pages changed.
	 */
	public static function link_services_pages(): int {
		$targets = array();
		foreach ( array_keys( Service_Content::all() ) as $key ) {
			foreach ( self::pages_of( $key ) as $page ) {
				if ( 'publish' === $page->post_status ) {
					$targets[ '#' . $key ] = (string) get_permalink( $page );
					break;
				}
			}
		}

		if ( ! $targets ) {
			return 0;
		}

		$changed = 0;
		foreach ( Design_Pages::all() as $page ) {
			if ( 'services' !== $page['kind'] || ! current_user_can( 'edit_post', $page['id'] ) ) {
				continue;
			}

			$data = json_decode( (string) get_post_meta( $page['id'], '_elementor_data', true ), true );
			if ( ! is_array( $data ) ) {
				continue;
			}

			$count = 0;
			$data  = self::relink( $data, $targets, $count );
			if ( $count ) {
				Library::set_content( $page['id'], $data );
				++$changed;
			}
		}

		return $changed;
	}

	/**
	 * Pages made for a service, newest first.
	 *
	 * @param string $key Service key.
	 * @return \WP_Post[]
	 */
	private static function pages_of( string $key ): array {
		return get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => self::MADE_STATUSES,
				'posts_per_page' => 10,
				'meta_key'       => self::META_SERVICE, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- admin action, a handful of pages.
				'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- as above.
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
	}

	/**
	 * Replaces URL control values (`['url' => '#photo', …]`) found anywhere
	 * in Elementor data.
	 *
	 * @param array                 $node    Elements, settings or a value inside them.
	 * @param array<string, string> $targets Anchor → page URL.
	 * @param int                   $count   Number of links replaced so far.
	 */
	private static function relink( array $node, array $targets, int &$count ): array {
		if ( isset( $node['url'] ) && is_string( $node['url'] ) && isset( $targets[ $node['url'] ] ) ) {
			$node['url'] = $targets[ $node['url'] ];
			++$count;
		}

		foreach ( $node as $name => $value ) {
			if ( is_array( $value ) ) {
				$node[ $name ] = self::relink( $value, $targets, $count );
			}
		}

		return $node;
	}
}
