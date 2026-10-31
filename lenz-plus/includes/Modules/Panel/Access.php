<?php
/**
 * Who may open the studio panel, and the ways in.
 *
 * The panel is part of WordPress, not a separate system: people log in with
 * their WordPress account, and WordPress itself carries the way in — the
 * first item of the wp-admin menu, a box at the top of the dashboard, the
 * admin bar and WooCommerce's account menu. Optionally (settings) staff who
 * do not manage the site are kept out of wp-admin and land in the panel
 * after logging in. What each person may change inside the panel still comes
 * from their WordPress capabilities, checked by every screen.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel;

defined( 'ABSPATH' ) || exit;

/**
 * Role, access checks, wp-admin redirect and entry links.
 */
final class Access {

	/** The role the plugin adds for the studio's staff. */
	public const ROLE = 'lzp_studio_manager';

	/** Stores the plugin version the role was last built for. */
	private const ROLE_VERSION_OPTION = 'lenz_plus_panel_role';

	/** WooCommerce account menu key of the panel link. */
	private const ACCOUNT_ITEM = 'lzp-panel';

	/** wp-admin files the panel itself relies on (uploads, form posts, AJAX). */
	private const ADMIN_FILES = array( 'admin-ajax.php', 'admin-post.php', 'async-upload.php' );

	/** @var Module */
	private $module;

	/**
	 * @param Module $module Panel module (settings).
	 */
	public function __construct( Module $module ) {
		$this->module = $module;
	}

	/** Hooks the role, the redirects and the entry links. */
	public function register(): void {
		$settings = $this->module->settings();

		if ( $settings['hide_admin'] ) {
			add_action( 'admin_init', array( $this, 'keep_out_of_admin' ), 1 );
			add_filter( 'show_admin_bar', array( $this, 'show_admin_bar' ) );
		}

		if ( $settings['login_redirect'] ) {
			add_filter( 'login_redirect', array( $this, 'login_redirect' ), 20, 3 );
			add_filter( 'woocommerce_login_redirect', array( $this, 'woocommerce_login_redirect' ), 20, 2 );
		}

		if ( $settings['account_link'] ) {
			add_filter( 'woocommerce_account_menu_items', array( $this, 'account_menu_items' ) );
			add_filter( 'woocommerce_get_endpoint_url', array( $this, 'account_menu_url' ), 10, 2 );
		}

		add_action( 'admin_menu', array( $this, 'admin_menu_item' ), 1 );
		add_action( 'wp_dashboard_setup', array( $this, 'dashboard_box' ) );

		if ( $settings['admin_bar_link'] ) {
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_node' ), 80 );
		}
	}

	/**
	 * Adds (or refreshes) the «Studio manager» role once per plugin version:
	 * WooCommerce's shop manager capabilities (orders, products, coupons, plus
	 * an editor's posts and pages), or an editor's without WooCommerce.
	 */
	public static function install_role(): void {
		if ( get_option( self::ROLE_VERSION_OPTION ) === LENZ_PLUS_VERSION ) {
			return;
		}

		$base = get_role( 'shop_manager' );
		$base = $base ? $base : get_role( 'editor' );
		$caps = $base ? $base->capabilities : array( 'read' => true );

		remove_role( self::ROLE );
		add_role( self::ROLE, __( 'Studio manager', 'lenz-plus' ), $caps );
		update_option( self::ROLE_VERSION_OPTION, LENZ_PLUS_VERSION, false );
	}

	/**
	 * Whether a user may open the panel (a role switched on in the settings).
	 *
	 * @param \WP_User|null $user User; the current one when null.
	 */
	public function can_use( $user = null ): bool {
		$user = $user ? $user : wp_get_current_user();
		if ( ! $user || ! $user->exists() ) {
			return false;
		}

		$roles = array_keys( array_filter( $this->module->settings()['roles'] ) );

		return is_super_admin( $user->ID ) || array() !== array_intersect( $roles, (array) $user->roles );
	}

	/**
	 * Whether the panel replaces wp-admin for this user: panel users who do
	 * not manage the site's settings.
	 *
	 * @param \WP_User|null $user User; the current one when null.
	 */
	public function is_panel_only( $user = null ): bool {
		$user = $user ? $user : wp_get_current_user();

		return $this->can_use( $user ) && ! user_can( $user, 'manage_options' );
	}

	/** Sends panel-only users from wp-admin to the panel. */
	public function keep_out_of_admin(): void {
		if ( wp_doing_ajax() || ! $this->is_panel_only() ) {
			return;
		}

		global $pagenow;
		if ( in_array( $pagenow, self::ADMIN_FILES, true ) || $this->is_allowed_editor_screen( (string) $pagenow ) ) {
			return;
		}

		wp_safe_redirect( Router::url() );
		exit;
	}

	/**
	 * The post editor stays open when the panel hands a text to WordPress's
	 * editor or to Elementor, and for the media library it opens there.
	 *
	 * @param string $file Current wp-admin file.
	 */
	private function is_allowed_editor_screen( string $file ): bool {
		if ( ! in_array( $file, array( 'post.php', 'post-new.php', 'upload.php', 'media-new.php' ), true ) ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read only, to choose a redirect.
		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
		if ( 'elementor' === $action || 'upload.php' === $file || 'media-new.php' === $file ) {
			return true;
		}

		return in_array( 'WordPress', $this->module->settings()['editor'], true );
	}

	/**
	 * No admin bar for panel-only users: the panel has its own top bar.
	 *
	 * @param bool $show Whether WordPress would show it.
	 */
	public function show_admin_bar( $show ) {
		return $this->is_panel_only() ? false : $show;
	}

	/**
	 * Panel users land in the panel after logging in, unless they were sent
	 * to the login page from somewhere specific.
	 *
	 * @param string            $redirect  Where WordPress would go.
	 * @param string            $requested The `redirect_to` asked for.
	 * @param \WP_User|\WP_Error $user      Who logged in.
	 */
	public function login_redirect( $redirect, $requested, $user ) {
		if ( ! $user instanceof \WP_User || ! $this->is_panel_only( $user ) ) {
			return $redirect;
		}

		$asked = (string) $requested;

		return ( '' === $asked || false !== strpos( $asked, '/wp-admin' ) ) ? Router::url() : $redirect;
	}

	/**
	 * Same for WooCommerce's account login form.
	 *
	 * @param string   $redirect Where WooCommerce would go.
	 * @param \WP_User $user     Who logged in.
	 */
	public function woocommerce_login_redirect( $redirect, $user ) {
		return $user instanceof \WP_User && $this->is_panel_only( $user ) ? Router::url() : $redirect;
	}

	/**
	 * Adds «Studio panel» to the account menu of panel users, before log out.
	 *
	 * @param array<string, string> $items Endpoint => label.
	 * @return array<string, string>
	 */
	public function account_menu_items( $items ) {
		if ( ! $this->can_use() ) {
			return $items;
		}

		$logout = isset( $items['customer-logout'] ) ? array( 'customer-logout' => $items['customer-logout'] ) : array();
		unset( $items['customer-logout'] );

		return array( self::ACCOUNT_ITEM => $this->module->panel_title() ) + $items + $logout;
	}

	/**
	 * Points the account menu item at the panel.
	 *
	 * @param string $url      Endpoint URL.
	 * @param string $endpoint Endpoint key.
	 */
	public function account_menu_url( $url, $endpoint ) {
		return self::ACCOUNT_ITEM === $endpoint ? Router::url() : $url;
	}

	/**
	 * «Studio panel» as the first item of the wp-admin menu. The menu slug
	 * is the panel's URL, which WordPress turns into a plain link.
	 */
	public function admin_menu_item(): void {
		if ( ! $this->can_use() ) {
			return;
		}

		add_menu_page( $this->module->panel_title(), $this->module->panel_title(), 'read', Router::url(), '', 'dashicons-smartphone', 1 );
	}

	/** A box with the panel's button at the top of the wp-admin dashboard. */
	public function dashboard_box(): void {
		if ( ! $this->can_use() ) {
			return;
		}

		wp_add_dashboard_widget( 'lzp_panel', $this->module->panel_title(), array( $this, 'render_dashboard_box' ), null, null, 'normal', 'high' );
	}

	/** Prints the dashboard box. */
	public function render_dashboard_box(): void {
		printf(
			'<p>%1$s</p><p><a class="button button-primary button-hero" href="%2$s">%3$s</a></p>',
			esc_html__( 'Projects, articles, courses, orders and bookings in one simple panel that works well on phones.', 'lenz-plus' ),
			esc_url( Router::url() ),
			esc_html__( 'Open the studio panel', 'lenz-plus' )
		);
	}

	/**
	 * Admin bar link next to the site's name.
	 *
	 * @param \WP_Admin_Bar $bar Admin bar.
	 */
	public function admin_bar_node( $bar ): void {
		if ( ! $this->can_use() ) {
			return;
		}

		$bar->add_node(
			array(
				'id'    => 'lzp-panel',
				'title' => esc_html( $this->module->panel_title() ),
				'href'  => Router::url(),
				'meta'  => array( 'class' => 'lzp-panel-node' ),
			)
		);
	}
}
