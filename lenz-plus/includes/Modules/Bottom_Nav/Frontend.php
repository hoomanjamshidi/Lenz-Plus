<?php
/**
 * Front-end integration: assets, rendering and WooCommerce cart fragments.
 *
 * The bar is always rendered and hidden above the breakpoint with CSS, so it
 * stays compatible with full-page caching (no `wp_is_mobile()` sniffing).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Bottom_Nav;

use LenzPlus\Core\Asset;
use LenzPlus\Core\Search_Query;
use LenzPlus\Core\Theme_Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * Hooks the bar into the front end and decides where it shows.
 */
final class Frontend {

	private const HANDLE = 'lzp-bottom-nav';

	/** @var Module */
	private $module;

	/** @var array<int, array>|null Memoized item view models. */
	private $items = null;

	/** @var bool|null Memoized render decision. */
	private $should_render = null;

	/**
	 * @param Module $module Owning module.
	 */
	public function __construct( Module $module ) {
		$this->module = $module;
	}

	/**
	 * Hooks assets, the footer render, the body class and cart fragments.
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'render' ), 20 );
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_fragments' ) );
	}

	/**
	 * Styles (with the inline settings variables) and the deferred script.
	 */
	public function enqueue_assets(): void {
		if ( ! $this->should_render() ) {
			return;
		}

		$settings = $this->settings();

		// Lenz's icon font ("Lenz icons" pack); Lenz enqueues it on every page, the dependency keeps the order explicit.
		$style_deps = Theme_Bridge::is_active() ? array( 'lenz-icons' ) : array();

		wp_enqueue_style( self::HANDLE, Asset::url( 'assets/modules/bottom-nav/css/bottom-nav.css' ), $style_deps, LENZ_PLUS_VERSION );
		wp_add_inline_style( self::HANDLE, Style_Vars::inline_css( $settings ) );

		wp_enqueue_script(
			self::HANDLE,
			Asset::url( 'assets/modules/bottom-nav/js/bottom-nav.js' ),
			array(),
			LENZ_PLUS_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		wp_add_inline_script( self::HANDLE, 'window.lzpBottomNav = ' . wp_json_encode( $this->script_config() ) . ';', 'before' );

		if ( $this->has_item_type( 'cart' ) ) {
			// Keeps the badge in sync after AJAX add-to-cart (WooCommerce 7.8+ no longer loads it everywhere).
			wp_enqueue_script( 'wc-cart-fragments' );
		}
	}

	/**
	 * Prints the bar and its sheets on `wp_footer`.
	 */
	public function render(): void {
		if ( ! $this->should_render() ) {
			return;
		}

		echo ( new Renderer( $this->settings(), $this->items() ) )->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the views.
	}

	/**
	 * Adds `lzp-bn-on`, which reserves room at the bottom and lifts other fixed elements.
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public function body_class( array $classes ): array {
		if ( $this->should_render() ) {
			$classes[] = 'lzp-bn-on';
		}

		return $classes;
	}

	/**
	 * Adds our cart badge to WooCommerce's AJAX cart fragments.
	 *
	 * @param array $fragments Selector => HTML.
	 */
	public function cart_fragments( $fragments ): array {
		$fragments = is_array( $fragments ) ? $fragments : array();

		foreach ( $this->settings()['items'] as $item ) {
			if ( 'cart' === $item['type'] && $item['enabled'] ) {
				$fragments['span.lzp-bn-cart-count'] = Item_Resolver::cart_badge_html( (bool) $item['hide_empty_badge'] );
				break;
			}
		}

		return $fragments;
	}

	/**
	 * Decides once per request whether the bar is shown. Filterable through
	 * `lenz_plus_bottom_nav_should_render`.
	 */
	private function should_render(): bool {
		if ( null === $this->should_render ) {
			$this->should_render = (bool) apply_filters(
				'lenz_plus_bottom_nav_should_render',
				$this->passes_visibility_rules() && array() !== $this->items()
			);
		}

		return $this->should_render;
	}

	private function passes_visibility_rules(): bool {
		if ( is_admin() || is_feed() || is_embed() || wp_doing_ajax() || $this->is_page_builder_preview() ) {
			return false;
		}

		$rules = $this->settings()['visibility'];

		if ( $rules['hide_on_checkout'] && function_exists( 'is_checkout' ) && is_checkout() ) {
			return false;
		}

		if ( $rules['hide_on_cart'] && function_exists( 'is_cart' ) && is_cart() ) {
			return false;
		}

		// Single product pages often have their own fixed buy bar.
		if ( $rules['hide_on_product'] && function_exists( 'is_product' ) && is_product() ) {
			return false;
		}

		if ( '' !== $rules['hide_for_ids'] && is_singular() ) {
			$hidden_ids = array_map( 'intval', explode( ',', $rules['hide_for_ids'] ) );
			if ( in_array( (int) get_queried_object_id(), $hidden_ids, true ) ) {
				return false;
			}
		}

		return true;
	}

	/** The bar would cover the editing canvas inside Elementor's preview. */
	private function is_page_builder_preview(): bool {
		return class_exists( '\Elementor\Plugin' )
			&& isset( \Elementor\Plugin::$instance->preview )
			&& \Elementor\Plugin::$instance->preview->is_preview_mode();
	}

	/** @return array<int, array> */
	private function items(): array {
		if ( null === $this->items ) {
			$this->items = ( new Item_Resolver( $this->settings() ) )->resolve();
		}

		return $this->items;
	}

	/**
	 * @param string $type Item type id.
	 */
	private function has_item_type( string $type ): bool {
		foreach ( $this->items() as $item ) {
			if ( $type === $item['type'] ) {
				return true;
			}
		}

		return false;
	}

	/** Runtime options for bottom-nav.js. */
	private function script_config(): array {
		$settings = $this->settings();

		return array(
			'hideOnScroll' => (bool) $settings['behavior']['hide_on_scroll'],
			'haptic'       => (bool) $settings['behavior']['haptic'],
			'breakpoint'   => (int) $settings['layout']['breakpoint'],
			'search'       => array(
				'url'      => Live_Search::url(),
				'minChars' => Search_Query::MIN_CHARS,
			),
			'i18n'         => array(
				'toggleSubmenu' => __( 'Toggle submenu', 'lenz-plus' ),
				'searchFailed'  => __( 'Live results are unavailable right now. Press Search to see all results.', 'lenz-plus' ),
			),
		);
	}

	/** The module's saved settings. */
	private function settings(): array {
		return $this->module->settings();
	}
}
