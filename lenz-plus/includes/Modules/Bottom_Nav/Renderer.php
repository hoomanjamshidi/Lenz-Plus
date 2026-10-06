<?php
/**
 * Renders the navigation bar and its bottom sheets.
 *
 * MARKUP CONTRACT: the structure produced by views/nav.php is mirrored by the
 * admin live preview (assets/modules/bottom-nav/js/bottom-nav-admin.js →
 * `renderNav`). Keep both in sync when changing classes or nesting.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Bottom_Nav;

use LenzPlus\Core\Icon_Library;

defined( 'ABSPATH' ) || exit;

/** Turns resolved items into the bar and sheet markup. */
final class Renderer {

	/** @var array Module settings. */
	private $settings;

	/** @var array<int, array> Item view models from Item_Resolver. */
	private $items;

	/**
	 * @param array $settings Module settings.
	 * @param array $items    Item view models.
	 */
	public function __construct( array $settings, array $items ) {
		$this->settings = $settings;
		$this->items    = $items;
	}

	/** Markup of the bar and its sheets. */
	public function render(): string {
		ob_start();

		$renderer = $this;
		$items    = $this->items;
		require __DIR__ . '/views/nav.php';

		foreach ( $this->items as $item ) {
			if ( null !== $item['sheet'] ) {
				$sheet = $item['sheet'];
				require __DIR__ . '/views/sheet.php';
			}
		}

		return (string) ob_get_clean();
	}

	/** Class list of the <nav> element. */
	public function nav_classes(): string {
		$style   = $this->settings['style'];
		$labels  = Styles::supports_label_modes( $style ) ? $this->settings['label_mode'] : 'auto';
		$classes = array(
			'lzp-bn',
			'lzp-bn--style-' . $style,
			'lzp-bn--labels-' . $labels,
			'lzp-bn--motion-' . $this->settings['layout']['motion'],
			'lzp-bn--shadow-' . $this->settings['layout']['shadow'],
		);

		if ( $this->index_where( 'current' ) >= 0 ) {
			$classes[] = 'has-active';
		}
		if ( $this->settings['layout']['glass'] ) {
			$classes[] = 'lzp-bn--glass';
		}
		if ( $this->settings['behavior']['hide_on_scroll'] ) {
			$classes[] = 'lzp-bn--autohide';
		}

		return implode( ' ', $classes );
	}

	/**
	 * Per-instance layout variables: item count, active and featured indexes.
	 * `--lzp-bn-active: -1` hides the sliding indicator.
	 */
	public function nav_style(): string {
		return sprintf(
			'--lzp-bn-count:%d;--lzp-bn-active:%d;--lzp-bn-featured:%d',
			count( $this->items ),
			$this->index_where( 'current' ),
			$this->index_where( 'featured' )
		);
	}

	/**
	 * @param array $item View model.
	 */
	public function item_classes( array $item ): string {
		$classes = array( 'lzp-bn__item', 'lzp-bn__item--' . $item['type'] );

		if ( $item['current'] ) {
			$classes[] = 'is-active';
		}
		if ( $item['featured'] ) {
			$classes[] = 'is-featured';
		}
		if ( '' !== $item['icon_active'] ) {
			$classes[] = 'has-active-icon';
		}

		return implode( ' ', $classes );
	}

	/** Whether the item renders as <a> (has a URL) or <button>. */
	public function item_tag( array $item ): string {
		return '' !== $item['href'] ? 'a' : 'button';
	}

	/**
	 * Escaped attribute string for the item's link/button.
	 *
	 * @param array $item View model.
	 */
	public function item_attributes( array $item ): string {
		$attrs = array( 'class' => 'lzp-bn__link' );

		if ( 'a' === $this->item_tag( $item ) ) {
			$attrs['href'] = esc_url( $item['href'] );
			if ( '_blank' === $item['target'] ) {
				$attrs['target'] = '_blank';
				$attrs['rel']    = 'noopener noreferrer';
			}
		} else {
			$attrs['type'] = 'button';
		}

		if ( $item['current'] ) {
			$attrs['aria-current'] = 'page';
		}

		if ( '' !== $item['action'] ) {
			$attrs['data-lzp-action'] = $item['action'];
		}

		if ( null !== $item['sheet'] ) {
			$attrs['aria-controls'] = $this->sheet_id( $item );
			$attrs['aria-haspopup'] = 'dialog';
			$attrs['aria-expanded'] = 'false';
		}

		foreach ( $item['data'] as $key => $value ) {
			$attrs[ 'data-lzp-' . $key ] = $value;
		}

		$html = '';
		foreach ( $attrs as $name => $value ) {
			$html .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
		}

		return $html;
	}

	/**
	 * @param array $sheet Sheet definition.
	 */
	public function sheet_classes( array $sheet ): string {
		$classes = array( 'lzp-sheet', 'lzp-sheet--' . $sheet['kind'] );
		if ( 'search' === $sheet['kind'] && $sheet['live'] ) {
			$classes[] = 'lzp-sheet--live-search';
		}

		return implode( ' ', $classes );
	}

	/**
	 * Whether the sheet participates in browser history (so the Android back
	 * button closes it). Only sheets whose links we control opt in; arbitrary
	 * builder content may run its own link handlers.
	 *
	 * @param array $sheet Sheet definition.
	 */
	public function sheet_uses_history( array $sheet ): bool {
		return 'content' !== $sheet['kind'];
	}

	/**
	 * @param array $item View model.
	 */
	public function sheet_id( array $item ): string {
		return 'lzp-sheet-' . $item['id'];
	}

	/** Close icon for sheets, from the active pack (or Tabler for Font Awesome). */
	public function close_icon(): string {
		return $this->pack_svg( 'close' );
	}

	/** Search icon for the search field. */
	public function search_icon(): string {
		return $this->pack_svg( 'search' );
	}

	/**
	 * Icon from the active pack for sheet content (placeholders, states).
	 *
	 * @param string $key Semantic icon key.
	 */
	public function icon( string $key ): string {
		return $this->pack_svg( $key );
	}

	/**
	 * Live search results markup, returned by Live_Search over AJAX.
	 *
	 * @param array $view View data: `results`, `term`, `count` and `all_url`.
	 */
	public function search_results( array $view ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- read by the included view.
		ob_start();

		$renderer = $this;
		require __DIR__ . '/views/search-results.php';

		return (string) ob_get_clean();
	}

	/**
	 * Prints the body of a sheet according to its kind.
	 *
	 * @param array $sheet Sheet definition from the item view model.
	 */
	public function sheet_body( array $sheet ): void {
		switch ( $sheet['kind'] ) {
			case 'search':
				$renderer = $this;
				require __DIR__ . '/views/sheet-search.php';
				break;

			case 'cart':
				echo '<div class="widget_shopping_cart_content">';
				woocommerce_mini_cart();
				echo '</div>';
				break;

			case 'menu':
				$this->menu( $sheet );
				break;

			case 'content':
				$this->content( $sheet );
				break;
		}
	}

	/**
	 * @param array $sheet Menu sheet definition.
	 */
	private function menu( array $sheet ): void {
		$args = array(
			'container'       => 'nav',
			'container_class' => 'lzp-menu',
			'menu_class'      => 'lzp-menu__list',
			'depth'           => 3,
			'fallback_cb'     => false,
		);

		if ( $sheet['menu'] ) {
			$args['menu'] = $sheet['menu'];
		} else {
			$args['theme_location'] = $sheet['location'];
		}

		wp_nav_menu( $args );
	}

	/**
	 * @param array $sheet Content sheet definition.
	 */
	private function content( array $sheet ): void {
		if ( 'elementor' === $sheet['source'] && class_exists( '\Elementor\Plugin' ) ) {
			// Respect WPML translations of the template.
			$post_id = (int) apply_filters( 'wpml_object_id', $sheet['content_id'], get_post_type( $sheet['content_id'] ), true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WPML's public filter.

			echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $post_id, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor output.
			return;
		}

		echo do_shortcode( wp_kses_post( $sheet['html'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses-filtered, then shortcodes.
	}

	/**
	 * @param string $key Semantic icon key.
	 */
	private function pack_svg( string $key ): string {
		$pack = $this->settings['icon_pack'];
		if ( Icon_Library::FONT_AWESOME === $pack ) {
			$pack = 'tabler';
		}

		return Icon_Library::svg( $pack, $key );
	}

	/**
	 * Index of the first item with a truthy flag, or -1.
	 *
	 * @param string $flag View model key.
	 */
	private function index_where( string $flag ): int {
		foreach ( $this->items as $index => $item ) {
			if ( ! empty( $item[ $flag ] ) ) {
				return $index;
			}
		}

		return -1;
	}
}
