<?php
/**
 * A footer column of links with small square bullets (the mockups' «خدمات»,
 * «دسترسی سریع», «دسته‌بندی‌ها» and «موضوع‌ها» columns).
 *
 * Links come from one of Lenz's two footer menus (with the column title saved
 * in Lenz), any WordPress menu, or the terms of a taxonomy (blog categories,
 * portfolio categories, product categories). Only the top level is listed.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Core\Theme_Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * The «Link column (Lenz+)» widget.
 */
final class Link_Column extends Base {

	/** Taxonomies offered as a source, when they exist on the site. */
	private const TAXONOMIES = array( 'category', 'portfolio-cat', 'video-cat', 'product_cat' );

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-link-column';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Link column', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-bullet-list';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'footer', 'links', 'menu', 'فوتر', 'لینک', 'دسترسی سریع' ) );
	}

	/**
	 * Sources for the SELECT control.
	 *
	 * @return array<string, string>
	 */
	private static function source_options(): array {
		$options = array(
			'footer-1' => __( 'Lenz\'s first footer menu', 'lenz-plus' ),
			'footer-2' => __( 'Lenz\'s second footer menu', 'lenz-plus' ),
			'menu'     => __( 'A WordPress menu', 'lenz-plus' ),
		);

		foreach ( self::TAXONOMIES as $taxonomy ) {
			$object = get_taxonomy( $taxonomy );
			if ( $object ) {
				/* translators: %s: taxonomy name, e.g. "Categories". */
				$options[ 'tax:' . $taxonomy ] = sprintf( __( 'Terms: %s', 'lenz-plus' ), $object->labels->name );
			}
		}

		return $options;
	}

	/**
	 * Menus for the SELECT control.
	 *
	 * @return array<string, string>
	 */
	private static function menu_options(): array {
		$options = array( '' => __( '— Choose a menu —', 'lenz-plus' ) );
		foreach ( wp_get_nav_menus() as $menu ) {
			$options[ (string) $menu->term_id ] = $menu->name;
		}

		return $options;
	}

	/** Title, source and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Links', 'lenz-plus' ) );

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'Empty: the title saved in Lenz for that footer menu, the menu\'s name or the taxonomy\'s name.', 'lenz-plus' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Links from', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'footer-1',
				'options' => self::source_options(),
			)
		);

		$this->add_control(
			'menu',
			array(
				'label'     => __( 'Menu', 'lenz-plus' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => self::menu_options(),
				'condition' => array( 'source' => 'menu' ),
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'   => __( 'At most', 'lenz-plus' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 6,
				'min'     => 1,
				'max'     => 20,
			)
		);

		$this->add_control(
			'bullets',
			array(
				'label'   => __( 'Square bullets', 'lenz-plus' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Links', 'lenz-plus' ) );
		$this->add_text_style( 'title', '.lzp-col-title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style(
			'link',
			'.lzp-links__link',
			array(
				'label' => __( 'Links', 'lenz-plus' ),
				'hover' => '.lzp-links__link:hover',
			)
		);
		$this->end_controls_section();
	}

	/** Prints the column. */
	protected function render(): void {
		$s     = $this->get_settings_for_display();
		$limit = max( 1, min( 20, (int) $s['limit'] ) );
		$found = $this->links( (string) $s['source'], (string) $s['menu'], $limit );

		if ( ! $found['links'] ) {
			$this->editor_hint( __( 'No links yet: assign a menu to this footer location (Appearance → Menus) or choose another source.', 'lenz-plus' ) );
			return;
		}

		$title = '' !== (string) $s['title'] ? (string) $s['title'] : $found['title'];
		$items = '';
		foreach ( $found['links'] as $link ) {
			$items .= sprintf(
				'<li><a class="lzp-links__link" href="%1$s"%2$s>%3$s</a></li>',
				esc_url( $link['url'] ),
				$link['current'] ? ' aria-current="page"' : '',
				esc_html( $link['title'] )
			);
		}

		printf(
			'<div class="lzp-links%1$s">%2$s<ul class="lzp-links__list">%3$s</ul></div>',
			'yes' === $s['bullets'] ? ' lzp-links--bullets' : '',
			'' !== $title ? '<h3 class="lzp-col-title">' . esc_html( $title ) . '</h3>' : '',
			$items // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building.
		);
	}

	/**
	 * Links and the default title of a source.
	 *
	 * @param string $source  Source key.
	 * @param string $menu_id Chosen menu (for `menu`).
	 * @param int    $limit   Maximum number of links.
	 * @return array{title:string, links:array<int, array{title:string, url:string, current:bool}>}
	 */
	private function links( string $source, string $menu_id, int $limit ): array {
		if ( 0 === strpos( $source, 'tax:' ) ) {
			return self::term_links( substr( $source, 4 ), $limit );
		}

		if ( 'menu' === $source ) {
			$menu = $menu_id ? wp_get_nav_menu_object( (int) $menu_id ) : null;

			return array(
				'title' => $menu ? $menu->name : '',
				'links' => $menu ? self::menu_links( $menu, $limit ) : array(),
			);
		}

		$number    = 'footer-2' === $source ? 2 : 1;
		$footer    = Theme_Bridge::footer_menu( $number );
		$locations = get_nav_menu_locations();
		$menu      = ! empty( $locations[ $footer['location'] ] ) ? wp_get_nav_menu_object( $locations[ $footer['location'] ] ) : null;
		$title     = '' !== $footer['title'] ? $footer['title'] : ( 2 === $number ? __( 'Quick access', 'lenz-plus' ) : __( 'Our services', 'lenz-plus' ) );

		return array(
			'title' => $title,
			'links' => $menu ? self::menu_links( $menu, $limit ) : array(),
		);
	}

	/**
	 * Top-level items of a menu.
	 *
	 * @param \WP_Term $menu  Menu.
	 * @param int      $limit Maximum number of links.
	 */
	private static function menu_links( \WP_Term $menu, int $limit ): array {
		$links = array();
		foreach ( Nav_Menu::menu_items_tree( $menu ) as $node ) {
			$links[] = array(
				'title'   => $node['title'],
				'url'     => $node['url'],
				'current' => $node['current'],
			);
		}

		return array_slice( $links, 0, $limit );
	}

	/**
	 * Top-level terms of a taxonomy, the most used first.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param int    $limit    Maximum number of links.
	 */
	private static function term_links( string $taxonomy, int $limit ): array {
		$object = get_taxonomy( $taxonomy );
		$terms  = $object ? get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'parent'     => 0,
				'hide_empty' => true,
				'orderby'    => 'count',
				'order'      => 'DESC',
				'number'     => $limit,
			)
		) : array();

		$current = is_tax( $taxonomy ) || ( 'category' === $taxonomy && is_category() ) ? (int) get_queried_object_id() : 0;
		$links   = array();
		foreach ( is_array( $terms ) ? $terms : array() as $term ) {
			if ( 'uncategorized' === $term->slug ) {
				continue;
			}
			$links[] = array(
				'title'   => html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ),
				'url'     => (string) get_term_link( $term ),
				'current' => $current === (int) $term->term_id,
			);
		}

		return array(
			'title' => $object ? (string) $object->labels->name : '',
			'links' => $links,
		);
	}
}
