<?php
/**
 * Decides which template a request uses and builds preview links.
 *
 * Page designs need no decision: pages made from them carry their own
 * Elementor content. Headers and footers get one slot per device; routes
 * (portfolio list, project page…) one template each. Admins can force any
 * header, footer or route template through nonce-protected preview
 * parameters, which is how the admin previews work before anything is saved.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Template choice for the current request, and admin preview URLs.
 */
final class Resolver {

	public const PREVIEW_NONCE = 'lzp_builder_preview';

	/** @var Module */
	private $module;

	/** @var array<string, ?string>|null Memoized preview parameters, by area or route. */
	private $preview = null;

	/**
	 * @param Module $module Owning module.
	 */
	public function __construct( Module $module ) {
		$this->module = $module;
	}

	/**
	 * Resolved header/footer slots for this request.
	 *
	 * @param string $area `header` or `footer`.
	 * @return array{desktop: int|string, mobile: int|string} Template ID, `theme` or `none`.
	 */
	public function slots( string $area ): array {
		$preview = $this->preview()[ $area ];

		if ( null !== $preview ) {
			// A forced preview shows the same template at every width.
			$desktop = $preview;
			$mobile  = 'same';
		} elseif ( $this->module->is_enabled() ) {
			$settings = $this->module->settings()[ $area ];
			$desktop  = $settings['desktop'];
			$mobile   = $settings['mobile'];
		} else {
			$desktop = 'theme';
			$mobile  = 'same';
		}

		$desktop = $this->to_slot( $desktop, $area );

		return array(
			'desktop' => $desktop,
			'mobile'  => 'same' === $mobile ? $desktop : $this->to_slot( $mobile, $area ),
		);
	}

	/**
	 * Route this request belongs to (Schema::ROUTE_TYPES), '' for none.
	 */
	public function route_kind(): string {
		if ( is_singular( 'portfolio' ) ) {
			return 'portfolio';
		}

		if ( is_post_type_archive( 'portfolio' ) || is_tax( array( 'portfolio-cat', 'portfolio-tag' ) ) ) {
			return 'portfolio_archive';
		}

		if ( is_singular( 'post' ) ) {
			return 'post';
		}

		// Only courses: other products keep Lenz's own product page.
		if ( is_singular( 'product' ) && Course_Data::is_course( (int) get_queried_object_id() ) ) {
			return 'course';
		}

		// Searches count only when they are limited to posts (the blog's search box sends post_type=post):
		// site-wide results mix pages, projects and products, which post cards cannot show.
		if ( is_home() || is_category() || is_tag() || is_author() || is_date() || ( is_search() && array( 'post' ) === (array) get_query_var( 'post_type' ) ) ) {
			return 'blog';
		}

		return '';
	}

	/**
	 * Template replacing the theme's layout for this request, 0 to keep it.
	 */
	public function route_template(): int {
		$kind = $this->route_kind();
		if ( '' === $kind ) {
			return 0;
		}

		$preview = $this->preview()[ $kind ];
		if ( null !== $preview ) {
			return $this->to_route_id( $preview, $kind );
		}

		return $this->module->is_enabled() ? $this->to_route_id( (string) $this->module->settings()['routes'][ $kind ], $kind ) : 0;
	}

	/**
	 * @param string $ref  Stored reference (template ID or `theme`).
	 * @param string $kind Route kind.
	 */
	private function to_route_id( string $ref, string $kind ): int {
		$id = is_numeric( $ref ) ? (int) $ref : 0;

		return Template_Post_Type::is_usable( $id, array( $kind ) ) ? $id : 0;
	}

	/**
	 * A page of a route to preview its templates on: the portfolio archive,
	 * or the newest project ('' when there is none yet).
	 *
	 * @param string $kind Route kind.
	 */
	public static function sample_url( string $kind ): string {
		if ( 'portfolio_archive' === $kind ) {
			$url = post_type_exists( 'portfolio' ) ? get_post_type_archive_link( 'portfolio' ) : '';

			return $url ? $url : '';
		}

		if ( 'blog' === $kind ) {
			$page = (int) get_option( 'page_for_posts' );

			return $page ? (string) get_permalink( $page ) : ( 'posts' === get_option( 'show_on_front' ) ? home_url( '/' ) : '' );
		}

		$sample = 'course' === $kind ? Context::sample_course() : Context::sample_id( $kind );

		return $sample ? (string) get_permalink( $sample ) : '';
	}

	/** Whether this request is an admin preview. */
	public function is_preview(): bool {
		return array() !== array_filter(
			$this->preview(),
			static function ( $value ) {
				return null !== $value;
			}
		);
	}

	/**
	 * Where the admin previews a template (or a keyword such as `theme`).
	 *
	 * @param string $type Template kind.
	 * @param string $ref  Template ID or keyword.
	 */
	public function preview_url( string $type, string $ref ): string {
		if ( in_array( $type, Schema::PAGE_TYPES, true ) ) {
			// A page design is a whole page: its own URL shows it between the site's header and footer.
			return is_numeric( $ref ) ? (string) get_permalink( (int) $ref ) : '';
		}

		$args = array(
			'lzp_preview_' . $type => $ref,
			'_lzpnonce'            => wp_create_nonce( self::PREVIEW_NONCE ),
		);

		if ( in_array( $type, Schema::AREAS, true ) ) {
			return add_query_arg( $args, home_url( '/' ) );
		}

		if ( in_array( $type, Schema::ROUTE_TYPES, true ) ) {
			$url = self::sample_url( $type );
			if ( '' !== $url ) {
				return add_query_arg( $args, $url );
			}

			// Nothing to show it on yet: the template itself, with sample content.
			return is_numeric( $ref ) ? (string) get_permalink( (int) $ref ) : '';
		}

		return '';
	}

	/**
	 * @param string $ref  Stored reference.
	 * @param string $area `header` or `footer`.
	 * @return int|string Template ID, `theme` or `none`.
	 */
	private function to_slot( string $ref, string $area ) {
		if ( 'none' === $ref ) {
			return 'none';
		}

		$id = is_numeric( $ref ) ? (int) $ref : 0;

		// A deleted or unpublished template falls back to the theme, never to an empty page top.
		return Template_Post_Type::is_usable( $id, array( $area ) ) ? $id : 'theme';
	}

	/**
	 * Reads the preview parameters once. They only count for users who can
	 * edit templates and carry a valid nonce, so shared links do nothing.
	 *
	 * @return array<string, ?string> Area → forced reference, or null.
	 */
	private function preview(): array {
		if ( null !== $this->preview ) {
			return $this->preview;
		}

		$types         = array_merge( Schema::AREAS, Schema::ROUTE_TYPES );
		$this->preview = array_fill_keys( $types, null );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- verified right below.
		$requested = array();
		foreach ( $types as $area ) {
			if ( isset( $_GET[ 'lzp_preview_' . $area ] ) ) {
				$requested[ $area ] = sanitize_key( wp_unslash( $_GET[ 'lzp_preview_' . $area ] ) );
			}
		}

		$nonce = isset( $_GET['_lzpnonce'] ) ? sanitize_key( wp_unslash( $_GET['_lzpnonce'] ) ) : '';
		// phpcs:enable

		if ( $requested && Module::user_can_manage() && wp_verify_nonce( $nonce, self::PREVIEW_NONCE ) ) {
			$this->preview = array_merge( $this->preview, $requested );
		}

		return $this->preview;
	}
}
