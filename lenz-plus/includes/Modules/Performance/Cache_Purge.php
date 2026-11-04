<?php
/**
 * Clears the page cache when something every page shows has changed.
 *
 * Cached pages hold a copy of the bottom bar, the support button, the
 * Builder's header and footer, brand colours and route templates. WP Rocket
 * clears a post's own URL when it is saved, but a header template has no URL
 * of its own, and Lenz+ settings are not posts, so visitors kept seeing the
 * old bar until the cache expired. One purge runs at the end of the request,
 * however many settings or templates changed in it. The admin page also has
 * a «clear now» button (admin-post `lzp_performance_purge`).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Performance;

use LenzPlus\Core\Page_Cache;
use LenzPlus\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Automatic and manual page cache clearing.
 */
final class Cache_Purge {

	/** Admin-post action of the «clear now» button. */
	public const ACTION = 'lzp_performance_purge';

	/** Query arg set on the admin page after a manual purge. */
	public const DONE_ARG = 'lzp_purged';

	/** Modules whose output is part of every cached page. */
	private const WATCHED_MODULES = array( 'bottom_nav', 'support_button', 'builder' );

	/** @var bool A purge is waiting for the end of the request. */
	private $pending = false;

	/** Hooks the «clear now» button (also while the module is off). */
	public static function register_button(): void {
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'purge_now' ) );
	}

	/** Hooks the automatic purge. */
	public function register(): void {
		add_action( 'updated_option', array( $this, 'option_changed' ) );
		add_action( 'added_option', array( $this, 'option_added' ), 10, 2 );
		add_action( 'save_post_lzp_template', array( $this, 'template_saved' ), 10, 2 );
		add_action( 'elementor/document/after_save', array( $this, 'document_saved' ) );
	}

	/**
	 * Schedules a purge when a watched module's settings change.
	 *
	 * @param string $option Option name.
	 */
	public function option_changed( $option ): void {
		if ( in_array( $option, self::watched_options(), true ) ) {
			$this->schedule();
		}
	}

	/**
	 * Same for a new settings row, except the empty one Module::settings()
	 * adds the first time a module runs.
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  Stored value.
	 */
	public function option_added( $option, $value ): void {
		if ( ! empty( $value ) ) {
			$this->option_changed( $option );
		}
	}

	/**
	 * Schedules a purge when a published template is saved.
	 *
	 * @param int      $post_id Template ID.
	 * @param \WP_Post $post    Template.
	 */
	public function template_saved( $post_id, $post ): void {
		if ( ! wp_is_post_revision( $post_id ) && 'publish' === $post->post_status ) {
			$this->schedule();
		}
	}

	/**
	 * Same for a template saved in Elementor's editor (its data is post meta).
	 *
	 * @param \Elementor\Core\Base\Document $document Saved document.
	 */
	public function document_saved( $document ): void {
		$post = is_object( $document ) && method_exists( $document, 'get_post' ) ? $document->get_post() : null;
		if ( $post instanceof \WP_Post && 'lzp_template' === $post->post_type ) {
			$this->template_saved( $post->ID, $post );
		}
	}

	/** The «clear now» button: purges and goes back to the module's page. */
	public static function purge_now(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'lenz-plus' ), 403 );
		}
		check_admin_referer( self::ACTION );

		Page_Cache::purge();

		wp_safe_redirect( add_query_arg( self::DONE_ARG, '1', admin_url( 'admin.php?page=lenz-plus-performance' ) ) );
		exit;
	}

	/** Nonce-protected URL of the «clear now» button. */
	public static function purge_url(): string {
		return wp_nonce_url( admin_url( 'admin-post.php?action=' . self::ACTION ), self::ACTION );
	}

	/** Purges once, at the end of the request. */
	private function schedule(): void {
		if ( ! $this->pending ) {
			$this->pending = true;
			add_action( 'shutdown', array( Page_Cache::class, 'purge' ) );
		}
	}

	/**
	 * Option rows of the watched modules.
	 *
	 * @return string[]
	 */
	private static function watched_options(): array {
		$names = array();
		foreach ( self::WATCHED_MODULES as $id ) {
			$module = Plugin::instance()->module( $id );
			if ( $module ) {
				$names[] = $module->option_name();
			}
		}

		return $names;
	}
}
