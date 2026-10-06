<?php
/**
 * Navigation menu of the mockups' header: a row of links (the current page in
 * bold ink), dropdowns for sub-items, and a menu button on small screens.
 *
 * The button opens Lenz's own mobile menu by default (the theme prints it on
 * every page, with its support box), so the site keeps one phone menu.
 * Without Lenz, or when chosen, it opens the plugin's slide-in drawer.
 * Items come from a WordPress menu (or the theme's main menu location).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use LenzPlus\Core\Theme_Bridge;
use LenzPlus\Modules\Builder\Assets;

defined( 'ABSPATH' ) || exit;

/**
 * The «Navigation menu (Lenz+)» widget.
 */
final class Nav_Menu extends Base {

	/** Menu locations tried, in order, when no menu is chosen (Lenz's first). */
	private const LOCATIONS = array( 'main-menu', 'primary', 'main', 'header' );

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-nav-menu';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Navigation menu', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-nav-menu';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'menu', 'nav', 'منو', 'فهرست' ) );
	}

	/** Dropdowns, the menu button and the drawer need builder.js. */
	public function get_script_depends(): array {
		return array( Assets::HANDLE );
	}

	/**
	 * Menus for the SELECT control.
	 *
	 * @return array<string, string>
	 */
	private static function menu_options(): array {
		$options = array( '' => __( 'Automatic (the main menu)', 'lenz-plus' ) );
		foreach ( wp_get_nav_menus() as $menu ) {
			$options[ (string) $menu->term_id ] = $menu->name;
		}

		return $options;
	}

	/** Menu, layout, mobile button and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Menu', 'lenz-plus' ) );

		$this->add_control(
			'menu',
			array(
				'label'       => __( 'Menu', 'lenz-plus' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => self::menu_options(),
				'description' => sprintf(
					/* translators: %s: link to the menus screen. */
					__( 'Edit menus in %s.', 'lenz-plus' ),
					'<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '" target="_blank">' . esc_html__( 'Appearance → Menus', 'lenz-plus' ) . '</a>'
				),
			)
		);

		$this->add_control(
			'depth',
			array(
				'label'   => __( 'Levels', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '2',
				'options' => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
				),
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'horizontal',
				'options' => array(
					'horizontal' => __( 'Horizontal', 'lenz-plus' ),
					'vertical'   => __( 'Vertical', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'pointer',
			array(
				'label'     => __( 'Hover & current effect', 'lenz-plus' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => array(
					'none'      => __( 'Colour and weight (like the designs)', 'lenz-plus' ),
					'underline' => __( 'Underline', 'lenz-plus' ),
				),
				'condition' => array( 'layout' => 'horizontal' ),
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'     => __( 'Alignment', 'lenz-plus' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Start', 'lenz-plus' ),
						'icon'  => 'eicon-text-align-' . self::start_icon(),
					),
					'center'     => array(
						'title' => __( 'Center', 'lenz-plus' ),
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'End', 'lenz-plus' ),
						'icon'  => 'eicon-text-align-' . self::end_icon(),
					),
				),
				'selectors' => array( '{{WRAPPER}} .lzp-nav' => '--lzp-justify: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_content_section( 'section_mobile', __( 'Menu button', 'lenz-plus' ) );

		$this->add_control(
			'toggle',
			array(
				'label'   => __( 'Show a menu button instead of the links', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'tablet',
				'options' => array(
					'tablet' => __( 'On tablets and phones', 'lenz-plus' ),
					'mobile' => __( 'On phones only', 'lenz-plus' ),
					'always' => __( 'Always (button only)', 'lenz-plus' ),
					'never'  => __( 'Never', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'opens',
			array(
				'label'       => __( 'The button opens', 'lenz-plus' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'theme',
				'options'     => array(
					'theme'  => __( 'Lenz\'s mobile menu', 'lenz-plus' ),
					'drawer' => __( 'A drawer with this menu', 'lenz-plus' ),
				),
				'description' => __( 'Lenz\'s mobile menu shows the menu set for its mobile location, with the theme\'s support box. Without Lenz the drawer is used.', 'lenz-plus' ),
				'condition'   => array( 'toggle!' => 'never' ),
			)
		);

		$this->add_control(
			'toggle_label',
			array(
				'label'     => __( 'Button text', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '',
				'condition' => array( 'toggle!' => 'never' ),
			)
		);

		$this->add_control(
			'drawer_side',
			array(
				'label'     => __( 'Drawer opens from', 'lenz-plus' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'start',
				'options'   => array(
					'start' => __( 'Start side', 'lenz-plus' ),
					'end'   => __( 'End side', 'lenz-plus' ),
				),
				'condition' => array(
					'toggle!' => 'never',
					'opens'   => 'drawer',
				),
			)
		);

		$this->add_control(
			'drawer_title',
			array(
				'label'       => __( 'Drawer title', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => get_bloginfo( 'name' ),
				'condition'   => array(
					'toggle!' => 'never',
					'opens'   => 'drawer',
				),
			)
		);

		$this->add_control(
			'drawer_cta',
			array(
				'label'     => __( 'Button in the drawer', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '',
				'condition' => array(
					'toggle!' => 'never',
					'opens'   => 'drawer',
				),
			)
		);

		$this->add_control(
			'drawer_cta_link',
			array(
				'label'     => __( 'Button link', 'lenz-plus' ),
				'type'      => Controls_Manager::URL,
				'dynamic'   => array( 'active' => true ),
				'condition' => array(
					'toggle!'     => 'never',
					'opens'       => 'drawer',
					'drawer_cta!' => '',
				),
			)
		);

		$this->end_controls_section();

		$this->register_style_controls();
	}

	/** Link, dropdown and button colours. */
	private function register_style_controls(): void {
		$this->start_style_section( 'section_style', __( 'Menu', 'lenz-plus' ) );

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'link_typography',
				'selector' => '{{WRAPPER}} .lzp-nav > .lzp-menu > .lzp-menu__item > .lzp-menu__row > .lzp-menu__link',
			)
		);

		$this->add_control(
			'link_color',
			array(
				'label'     => __( 'Link colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lzp-nav' => '--lzp-nav-link: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'link_active_color',
			array(
				'label'     => __( 'Hover & current colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lzp-nav, .lzp-drawer[data-owner="{{ID}}"]' => '--lzp-nav-active: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'item_gap',
			array(
				'label'      => __( 'Space between items', 'lenz-plus' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .lzp-nav' => '--lzp-nav-gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'toggle_color',
			array(
				'label'     => __( 'Menu button colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .lzp-nav__toggle' => 'color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/** Prints the menu, its button and (when used) the drawer. */
	protected function render(): void {
		$s     = $this->get_settings_for_display();
		$depth = max( 1, min( 3, (int) $s['depth'] ) );
		$tree  = $this->menu_tree( (string) $s['menu'] );

		if ( ! $tree ) {
			$this->editor_hint( __( 'No menu found. Create one in Appearance → Menus.', 'lenz-plus' ) );
			return;
		}

		$toggle = in_array( $s['toggle'], array( 'tablet', 'mobile', 'always', 'never' ), true ) ? $s['toggle'] : 'tablet';
		// Lenz prints its mobile menu on every page; without the theme the drawer stands in.
		$drawer    = 'never' !== $toggle && ( 'drawer' === $s['opens'] || ! Theme_Bridge::is_active() );
		$drawer_id = 'lzp-drawer-' . $this->get_id();

		printf(
			'<nav class="lzp-nav lzp-nav--%1$s lzp-nav--pointer-%2$s lzp-nav--toggle-%3$s" aria-label="%4$s">',
			esc_attr( 'vertical' === $s['layout'] ? 'vertical' : 'horizontal' ),
			esc_attr( 'underline' === $s['pointer'] ? 'underline' : 'none' ),
			esc_attr( $toggle ),
			esc_attr__( 'Menu', 'lenz-plus' )
		);

		echo '<ul class="lzp-menu">' . $this->items_html( $tree, 0, $depth ) . '</ul>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building.

		if ( 'never' !== $toggle ) {
			$this->render_toggle( $drawer ? $drawer_id : '', (string) $s['toggle_label'] );
		}

		echo '</nav>';

		if ( $drawer ) {
			$this->render_drawer( $drawer_id, $tree, $depth, $s );
		}
	}

	/**
	 * The menu button: opens our drawer, or Lenz's mobile menu.
	 *
	 * @param string $drawer_id Drawer element id ('' for Lenz's menu).
	 * @param string $label     Visible button text ('' for icon only).
	 */
	private function render_toggle( string $drawer_id, string $label ): void {
		$action = '' !== $drawer_id
			? sprintf( 'aria-controls="%1$s" data-lzp-open="%1$s"', esc_attr( $drawer_id ) )
			: 'aria-controls="mobile-menu" data-lzp-theme-menu';

		printf(
			'<button type="button" class="lzp-nav__toggle" aria-expanded="false" %1$s>%2$s%3$s<span class="screen-reader-text">%4$s</span></button>',
			$action, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			self::icon( 'menu' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled icon.
			'' !== $label ? '<span class="lzp-nav__toggle-text">' . esc_html( $label ) . '</span>' : '',
			esc_html__( 'Open menu', 'lenz-plus' )
		);
	}

	/**
	 * The slide-in drawer (a modal dialog moved to <body> by builder.js).
	 *
	 * @param string $drawer_id Drawer element id.
	 * @param array  $tree      Menu tree.
	 * @param int    $depth     Levels.
	 * @param array  $s         Settings.
	 */
	private function render_drawer( string $drawer_id, array $tree, int $depth, array $s ): void {
		$title = '' !== (string) $s['drawer_title'] ? (string) $s['drawer_title'] : get_bloginfo( 'name' );

		printf(
			'<div class="lzp-drawer lzp-drawer--%1$s" id="%2$s" data-owner="%3$s" hidden>',
			esc_attr( 'end' === $s['drawer_side'] ? 'end' : 'start' ),
			esc_attr( $drawer_id ),
			esc_attr( $this->get_id() )
		);
		echo '<div class="lzp-drawer__backdrop" data-lzp-close></div>';
		printf( '<div class="lzp-drawer__panel" role="dialog" aria-modal="true" aria-label="%s">', esc_attr( $title ) );
		printf(
			'<div class="lzp-drawer__head"><span class="lzp-drawer__title">%1$s</span><button type="button" class="lzp-drawer__close" data-lzp-close aria-label="%2$s">%3$s</button></div>',
			esc_html( $title ),
			esc_attr__( 'Close', 'lenz-plus' ),
			self::icon( 'close' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled icon.
		);

		echo '<ul class="lzp-menu lzp-menu--drawer">' . $this->items_html( $tree, 0, $depth ) . '</ul>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building.

		if ( '' !== (string) $s['drawer_cta'] && ! empty( $s['drawer_cta_link']['url'] ) ) {
			$cta = Button::markup(
				array(
					'text'    => (string) $s['drawer_cta'],
					'url'     => (string) $s['drawer_cta_link']['url'],
					'variant' => 'primary',
					'size'    => 'md',
					'icon'    => '',
				)
			);
			echo $cta; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Button::markup().
		}

		echo '</div></div>';
	}

	/**
	 * List items of a menu tree.
	 *
	 * @param array $nodes Nodes (`title`, `url`, `target`, `current`, `ancestor`, `children`).
	 * @param int   $level Current level (0-based).
	 * @param int   $depth Max levels.
	 */
	private function items_html( array $nodes, int $level, int $depth ): string {
		$html = '';

		foreach ( $nodes as $node ) {
			$children = $level + 1 < $depth ? $node['children'] : array();
			$classes  = array( 'lzp-menu__item' );
			if ( $children ) {
				$classes[] = 'has-children';
			}
			if ( $node['current'] ) {
				$classes[] = 'is-current';
			}
			if ( $node['ancestor'] ) {
				$classes[] = 'is-ancestor';
			}

			$html .= '<li class="' . esc_attr( implode( ' ', $classes ) ) . '"><div class="lzp-menu__row">';
			$html .= sprintf(
				'<a class="lzp-menu__link" href="%1$s"%2$s%3$s>%4$s</a>',
				esc_url( $node['url'] ),
				$node['current'] ? ' aria-current="page"' : '',
				$node['target'] ? ' target="_blank" rel="noopener"' : '',
				esc_html( $node['title'] )
			);

			if ( $children ) {
				$html .= sprintf(
					'<button type="button" class="lzp-menu__caret" aria-expanded="false" aria-label="%1$s">%2$s</button>',
					/* translators: %s: menu item title. */
					esc_attr( sprintf( __( 'Show the submenu of %s', 'lenz-plus' ), $node['title'] ) ),
					self::icon( 'chevron-down' )
				);
			}

			$html .= '</div>';

			if ( $children ) {
				$html .= '<ul class="lzp-menu__sub">' . $this->items_html( $children, $level + 1, $depth ) . '</ul>';
			}

			$html .= '</li>';
		}

		return $html;
	}

	/**
	 * Tree of the chosen menu, else of the menu in the main location, else of
	 * the first menu.
	 *
	 * @param string $menu_id Chosen menu ('' = automatic).
	 */
	private function menu_tree( string $menu_id ): array {
		$menu = $menu_id ? wp_get_nav_menu_object( (int) $menu_id ) : null;

		if ( ! $menu ) {
			$locations = get_nav_menu_locations();
			foreach ( self::LOCATIONS as $location ) {
				if ( ! empty( $locations[ $location ] ) ) {
					$menu = wp_get_nav_menu_object( $locations[ $location ] );
					break;
				}
			}
		}

		if ( ! $menu ) {
			$menus = wp_get_nav_menus();
			$menu  = $menus ? $menus[0] : null;
		}

		return $menu ? self::menu_items_tree( $menu ) : array();
	}

	/**
	 * Nested items of a menu with the current-page flags wp_nav_menu() sets.
	 *
	 * @param \WP_Term $menu Menu.
	 */
	public static function menu_items_tree( \WP_Term $menu ): array {
		$items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );
		if ( ! $items ) {
			return array();
		}

		// Adds current/ancestor flags exactly like wp_nav_menu() does.
		_wp_menu_item_classes_by_context( $items );

		$by_parent = array();
		foreach ( $items as $item ) {
			$by_parent[ (int) $item->menu_item_parent ][] = $item;
		}

		$build = static function ( int $parent_id ) use ( &$build, $by_parent ): array {
			$nodes = array();
			foreach ( $by_parent[ $parent_id ] ?? array() as $item ) {
				$nodes[] = array(
					'title'    => wp_strip_all_tags( (string) apply_filters( 'the_title', $item->title, $item->ID ) ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core hook.
					'url'      => (string) $item->url,
					'target'   => '_blank' === $item->target,
					'current'  => ! empty( $item->current ),
					'ancestor' => ! empty( $item->current_item_ancestor ),
					'children' => $build( (int) $item->ID ),
				);
			}
			return $nodes;
		};

		return $build( 0 );
	}
}
