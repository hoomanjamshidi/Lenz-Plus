<?php
/**
 * Studio panel module: a simple, phone-first management panel on the site's
 * own address (site.com/panel/) for the studio's staff — projects, articles,
 * courses, products, orders, bookings, requests, comments and media — in the
 * site's brand colours, so they never need wp-admin.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel;

use LenzPlus\Core\Module as Base_Module;
use LenzPlus\Core\Sanitizer;

defined( 'ABSPATH' ) || exit;

/**
 * Settings, admin panel and boot of the studio panel.
 */
final class Module extends Base_Module {

	/** @var Access|null */
	private $access = null;

	/** @var App|null */
	private $app = null;

	/** @var Editor|null */
	private $editor = null;

	/** Module id: the option name suffix and the admin page slug. */
	public function id(): string {
		return 'panel';
	}

	/** Feature name in the admin menu and on the dashboard. */
	public function title(): string {
		return __( 'Studio panel', 'lenz-plus' );
	}

	/** One-line summary on the dashboard card. */
	public function description(): string {
		return __( 'A simple management panel for phones on your site\'s own address: projects, articles, courses, products, orders, bookings and requests in your brand\'s look, without wp-admin.', 'lenz-plus' );
	}

	/** Semantic icon key for the admin menu and the dashboard card. */
	public function icon(): string {
		return 'mobile';
	}

	/** Default settings (see Schema). */
	public function defaults(): array {
		return Schema::defaults();
	}

	/**
	 * Cleans posted settings; a changed address flushes the rewrite rules on
	 * the next request.
	 *
	 * @param array $input Raw settings.
	 */
	public function sanitize( array $input ): array {
		$clean = Sanitizer::apply( Schema::fields(), $input, Schema::defaults() );
		if ( '' === $clean['slug'] ) {
			$clean['slug'] = Schema::DEFAULT_SLUG;
		}

		if ( $clean['slug'] !== $this->settings()['slug'] ) {
			Router::forget_rules();
		}

		return $clean;
	}

	/** Role, access rules, address and uploads. */
	protected function boot(): void {
		Access::install_role();

		$this->access()->register();
		( new Router( $this->settings()['slug'], $this->app() ) )->register();
		( new Uploads() )->register();
	}

	/** Who may use the panel. */
	public function access(): Access {
		if ( null === $this->access ) {
			$this->access = new Access( $this );
		}

		return $this->access;
	}

	/** Screens and the page shell. */
	public function app(): App {
		if ( null === $this->app ) {
			$this->app = new App( $this );
		}

		return $this->app;
	}

	/** The content editor chosen per post type. */
	public function editor(): Editor {
		if ( null === $this->editor ) {
			$this->editor = new Editor( $this );
		}

		return $this->editor;
	}

	/** Name of the panel (settings, else «Studio panel»). */
	public function panel_title(): string {
		$title = trim( (string) $this->settings()['title'] );

		return '' !== $title ? $title : $this->title();
	}

	/** Prints the settings panel. */
	public function render_admin(): void {
		$module = $this;
		require __DIR__ . '/views/admin.php';
	}
}
