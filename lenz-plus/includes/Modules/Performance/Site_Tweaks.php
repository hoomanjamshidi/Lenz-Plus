<?php
/**
 * Small, safe trims of what WordPress, Lenz and WooCommerce load on every
 * page: emojis, head links, dashicons, embeds, jQuery Migrate, Heartbeat,
 * block styles, revisions, Lenz's doubled Bootstrap grid and WooCommerce's
 * cart fragments request.
 *
 * Each one is decided by enqueue hooks, so it works the same with or without
 * a page cache. WP Rocket has its own switches for emojis, embeds and
 * Heartbeat; both can be on (the second simply finds nothing left to do).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Performance;

use LenzPlus\Core\Theme_Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress, Lenz and WooCommerce trims.
 */
final class Site_Tweaks {

	/** Heartbeat interval (seconds) in the admin with the `slow` setting (WordPress: 15–60). */
	private const SLOW_HEARTBEAT = 60;

	/** @var array Module settings. */
	private $settings;

	/**
	 * @param array $settings Module settings.
	 */
	public function __construct( array $settings ) {
		$this->settings = $settings;
	}

	/** Hooks every switched-on trim. */
	public function register(): void {
		$s = $this->settings;

		if ( $s['emojis'] ) {
			$this->remove_emojis();
		}
		if ( $s['head_cleanup'] ) {
			foreach ( array( 'rsd_link', 'wlwmanifest_link', 'wp_generator', 'wp_shortlink_wp_head' ) as $callback ) {
				remove_action( 'wp_head', $callback );
			}
		}
		if ( $s['embeds'] ) {
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		}
		if ( $s['block_styles'] ) {
			// Core block styles only for the blocks a page shows (Elementor pages show none).
			add_filter( 'should_load_separate_core_block_assets', '__return_true' );
		}
		if ( 'default' !== $s['heartbeat'] ) {
			add_filter( 'heartbeat_settings', array( $this, 'slow_heartbeat' ) );
		}
		if ( $s['revisions'] > 0 ) {
			add_filter( 'wp_revisions_to_keep', array( $this, 'revisions_to_keep' ) );
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue' ), 100 );
		if ( $s['cart_fragments'] ) {
			// WooCommerce prints it deferred in <head>; widgets rendered later can still add it for the footer.
			add_action( 'wp_enqueue_scripts', array( $this, 'drop_cart_fragments' ), 100 );
			add_action( 'wp_print_footer_scripts', array( $this, 'drop_cart_fragments' ), 1 );
		}
	}

	/** Front-end files left out after everything is enqueued. */
	public function dequeue(): void {
		$s = $this->settings;

		if ( $s['dashicons'] && ! is_admin_bar_showing() ) {
			wp_dequeue_style( 'dashicons' );
		}
		if ( $s['embeds'] ) {
			wp_dequeue_script( 'wp-embed' );
		}
		if ( 'off_front' === $s['heartbeat'] ) {
			wp_deregister_script( 'heartbeat' );
		}
		if ( $s['jquery_migrate'] ) {
			$this->drop_jquery_migrate();
		}
		if ( $s['bootstrap_rtl'] && is_rtl() && Theme_Bridge::is_active() && wp_style_is( 'lenz-bootstrap-rtl' ) ) {
			// Lenz loads Bootstrap's LTR grid and then its complete RTL copy; the RTL file alone is enough.
			wp_dequeue_style( 'lenz-bootstrap' );
		}
	}

	/**
	 * Leaves out WooCommerce's cart fragments request while the visitor's cart
	 * is empty. It is an uncached AJAX call on every page view; AJAX
	 * add-to-cart answers carry the fragments themselves, and the next page
	 * view (with the cart cookie) loads the script again.
	 */
	public function drop_cart_fragments(): void {
		if ( empty( $_COOKIE['woocommerce_items_in_cart'] ) ) {
			wp_dequeue_script( 'wc-cart-fragments' );
		}
	}

	/** jQuery without jQuery Migrate (the `jquery` handle is only a list of dependencies). */
	private function drop_jquery_migrate(): void {
		$scripts = wp_scripts();
		if ( isset( $scripts->registered['jquery'] ) ) {
			$jquery       = $scripts->registered['jquery'];
			$jquery->deps = array_values( array_diff( $jquery->deps, array( 'jquery-migrate' ) ) );
		}
	}

	/**
	 * A slower Heartbeat (autosave and post locks still work).
	 *
	 * @param array $settings Heartbeat settings.
	 * @return array
	 */
	public function slow_heartbeat( $settings ) {
		$settings             = (array) $settings;
		$settings['interval'] = self::SLOW_HEARTBEAT;

		return $settings;
	}

	/** How many revisions each post keeps. */
	public function revisions_to_keep(): int {
		return (int) $this->settings['revisions'];
	}

	/** Removes the emoji script, styles and the DNS hint to s.w.org. */
	private function remove_emojis(): void {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		add_filter( 'emoji_svg_url', '__return_false' );
	}
}
