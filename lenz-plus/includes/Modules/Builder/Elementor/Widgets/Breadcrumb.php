<?php
/**
 * Breadcrumb of the project and article pages: home, the list the page
 * belongs to (portfolio, blog…), its category on category pages, and the
 * current title. Muted 13px links separated by slashes, as in the mockups.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Context;

defined( 'ABSPATH' ) || exit;

/**
 * The «Breadcrumb (Lenz+)» widget.
 */
final class Breadcrumb extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-breadcrumb';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Breadcrumb', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-product-breadcrumbs';
	}

	/** Depends on the page being viewed. */
	protected function is_dynamic_content(): bool {
		return true;
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'breadcrumb', 'path', 'مسیر', 'بردکرامب' ) );
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Breadcrumb', 'lenz-plus' ) );

		$this->add_control(
			'home_label',
			array(
				'label'   => __( 'Home label', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Home', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Breadcrumb', 'lenz-plus' ) );
		$this->add_text_style( 'link', '.lzp-crumbs a', array( 'label' => __( 'Links', 'lenz-plus' ) ) );
		$this->add_text_style( 'current', '.lzp-crumbs [aria-current]', array( 'label' => __( 'Current page', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Trail of [label, url] steps; the last one is the current page ('' URL).
	 *
	 * @param string $home Home label.
	 * @return array<int, array{0:string, 1:string}>
	 */
	private static function trail( string $home ): array {
		$trail = array( array( $home, home_url( '/' ) ) );
		$post  = Context::post();

		if ( is_tax() || is_category() || is_tag() ) {
			$term = get_queried_object();
			$tax  = $term instanceof \WP_Term ? get_taxonomy( $term->taxonomy ) : null;
			if ( $tax && ! empty( $tax->object_type ) ) {
				$trail[] = self::list_step( (string) $tax->object_type[0] );
			}
			$trail[] = array( $term instanceof \WP_Term ? $term->name : '', '' );
		} elseif ( is_post_type_archive() || is_home() ) {
			$trail[] = array( is_home() ? self::list_step( 'post' )[0] : post_type_archive_title( '', false ), '' );
		} elseif ( $post ) {
			$trail[] = self::list_step( $post->post_type );
			$trail[] = array( get_the_title( $post ), '' );
		}

		return array_values(
			array_filter(
				$trail,
				static function ( array $step ): bool {
					return '' !== $step[0];
				}
			)
		);
	}

	/**
	 * The list a post type belongs to: its archive, or the posts page.
	 *
	 * @param string $post_type Post type.
	 * @return array{0:string, 1:string}
	 */
	private static function list_step( string $post_type ): array {
		if ( 'post' === $post_type ) {
			$page_id = (int) get_option( 'page_for_posts' );

			return array( $page_id ? get_the_title( $page_id ) : __( 'Blog', 'lenz-plus' ), $page_id ? (string) get_permalink( $page_id ) : home_url( '/' ) );
		}

		$object = get_post_type_object( $post_type );
		$url    = get_post_type_archive_link( $post_type );

		return array( $object ? (string) $object->labels->name : '', $url ? $url : '' );
	}

	/** Prints the trail. */
	protected function render(): void {
		$s     = $this->get_settings_for_display();
		$trail = self::trail( (string) $s['home_label'] );
		$last  = count( $trail ) - 1;

		$items = '';
		foreach ( $trail as $index => $step ) {
			$items .= '<li>' . ( $index === $last || '' === $step[1]
				? '<span' . ( $index === $last ? ' aria-current="page"' : '' ) . '>' . esc_html( $step[0] ) . '</span>'
				: '<a href="' . esc_url( $step[1] ) . '">' . esc_html( $step[0] ) . '</a>' ) . '</li>';
		}

		echo '<nav class="lzp-crumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'lenz-plus' ) . '"><ol>' . $items . '</ol></nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	}
}
