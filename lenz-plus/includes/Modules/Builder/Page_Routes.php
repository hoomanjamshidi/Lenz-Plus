<?php
/**
 * Renders groups of pages with an Elementor template instead of Lenz's own
 * layout: the portfolio list (archive and categories) and project pages.
 * Each route has one template (Page templates → Site pages); `theme` keeps
 * Lenz's layout. The theme header and footer stay in place.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Template choice per request, its assets, and the `template_include` swap.
 */
final class Page_Routes {

	/** @var Module */
	private $module;

	/** @var int Template for this request (0 = theme layout). */
	private $template_id = 0;

	/** @var Page_Routes|null The instance serving this request, for the route view. */
	private static $current = null;

	/**
	 * @param Module $module Owning module.
	 */
	public function __construct( Module $module ) {
		$this->module  = $module;
		self::$current = $this;
	}

	/** Hooks the setup (once the main query is known) and the template swap. */
	public function register(): void {
		add_action( 'wp', array( $this, 'setup' ) );
		add_filter( 'template_include', array( $this, 'template_include' ), 99 );
	}

	/** The instance serving this request (null before the module boots). */
	public static function current(): ?Page_Routes {
		return self::$current;
	}

	/** Resolves the template and loads its assets. */
	public function setup(): void {
		if ( is_admin() || is_feed() || is_embed() || ! Renderer::elementor_ready() ) {
			return;
		}

		$this->template_id = $this->module->resolver()->route_template();
		if ( ! $this->template_id ) {
			return;
		}

		add_action(
			'wp_enqueue_scripts',
			function () {
				Renderer::enqueue( $this->template_id );
			},
			20
		);

		add_filter( 'body_class', array( $this, 'body_class' ) );

		if ( $this->module->resolver()->is_preview() ) {
			nocache_headers();
			add_filter( 'wp_robots', 'wp_robots_no_robots' );
		}
	}

	/**
	 * Swaps the theme's template file for the route view.
	 *
	 * @param string $template Template file chosen by WordPress.
	 */
	public function template_include( $template ) {
		return $this->template_id ? __DIR__ . '/views/route.php' : $template;
	}

	/**
	 * Marks the page with the template kind, for styling hooks.
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public function body_class( array $classes ): array {
		$classes[] = 'lzp-route';
		$classes[] = 'lzp-route--' . Template_Post_Type::type_of( $this->template_id );

		return $classes;
	}

	/** Template for this request (0 = theme layout). */
	public function template_id(): int {
		return $this->template_id;
	}
}
