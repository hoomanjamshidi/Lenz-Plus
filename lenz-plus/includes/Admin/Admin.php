<?php
/**
 * Admin shell: menu pages, assets and the shared layout every module renders into.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Admin;

use LenzPlus\Core\Icon_Library;
use LenzPlus\Core\Module;
use LenzPlus\Core\Theme_Bridge;
use LenzPlus\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the «Lenz+» menu, renders the shared layout and loads the admin assets.
 */
final class Admin {

	public const CAPABILITY = 'manage_options';
	public const MENU_SLUG  = 'lenz-plus';

	/** Icon pack used for the admin UI's own icons. */
	private const UI_ICON_PACK = 'phosphor-duotone';

	/** @var Plugin */
	private $plugin;

	/** @var string[] Hook suffixes of our admin pages. */
	private $page_hooks = array();

	/**
	 * @param Plugin $plugin Plugin instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Hooks the menu, assets, body class, plugin row link and the AJAX endpoints.
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'admin_body_class', array( $this, 'body_class' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( LENZ_PLUS_FILE ), array( $this, 'action_links' ) );

		( new Ajax_Controller( $this->plugin ) )->register();
	}

	/**
	 * Top-level «Lenz+» menu with the dashboard and one page per module.
	 */
	public function register_menu(): void {
		$this->page_hooks[] = add_menu_page(
			__( 'Lenz Plus', 'lenz-plus' ),
			__( 'Lenz+', 'lenz-plus' ),
			self::CAPABILITY,
			self::MENU_SLUG,
			array( $this, 'render' ),
			$this->menu_icon(),
			59
		);

		$this->page_hooks[] = add_submenu_page(
			self::MENU_SLUG,
			__( 'Lenz Plus', 'lenz-plus' ),
			__( 'Dashboard', 'lenz-plus' ),
			self::CAPABILITY,
			self::MENU_SLUG,
			array( $this, 'render' )
		);

		foreach ( $this->plugin->modules() as $module ) {
			$this->page_hooks[] = add_submenu_page(
				self::MENU_SLUG,
				$module->title(),
				$module->title(),
				self::CAPABILITY,
				$module->admin_slug(),
				array( $this, 'render' )
			);
		}
	}

	/**
	 * Prints the admin shell (views/layout.php) for the dashboard or the current module.
	 */
	public function render(): void {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		$admin   = $this;
		$modules = $this->plugin->modules();
		$module  = $this->current_module();

		require __DIR__ . '/views/layout.php';
	}

	/**
	 * Loads the shell assets and the module's own admin assets on our pages only.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function enqueue_assets( string $hook ): void {
		if ( ! in_array( $hook, $this->page_hooks, true ) ) {
			return;
		}

		$module = $this->current_module();

		wp_enqueue_style( 'lzp-admin', LENZ_PLUS_URL . 'assets/admin/css/admin.css', array(), LENZ_PLUS_VERSION );
		wp_enqueue_script( 'lzp-admin', LENZ_PLUS_URL . 'assets/admin/js/admin.js', array(), LENZ_PLUS_VERSION, true );

		wp_add_inline_script(
			'lzp-admin',
			'window.lzpAdmin = ' . wp_json_encode(
				array(
					'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
					'nonce'      => wp_create_nonce( Ajax_Controller::NONCE_ACTION ),
					'module'     => $module ? $module->id() : '',
					'settings'   => $module ? $module->settings() : null,
					'moduleData' => $module ? $module->admin_script_data() : new \stdClass(),
					'i18n'       => $this->js_strings(),
				)
			) . ';',
			'before'
		);

		if ( $module ) {
			$module->enqueue_admin_assets();
		}
	}

	/**
	 * Adds `lzp-admin-page` to our pages so admin.css can reset WordPress' own spacing.
	 *
	 * @param string $classes Space separated admin body classes.
	 */
	public function body_class( $classes ): string {
		$screen = get_current_screen();
		if ( $screen && in_array( $screen->id, $this->page_hooks, true ) ) {
			$classes .= ' lzp-admin-page';
		}

		return (string) $classes;
	}

	/**
	 * Puts a «Settings» link first on the plugin's row in Plugins.
	 *
	 * @param string[] $links Plugin row links.
	 * @return string[]
	 */
	public function action_links( array $links ): array {
		array_unshift(
			$links,
			sprintf( '<a href="%s">%s</a>', esc_url( $this->page_url() ), esc_html__( 'Settings', 'lenz-plus' ) )
		);

		return $links;
	}

	/** The module whose page is being viewed, or null on the dashboard. */
	public function current_module(): ?Module {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only routing.

		foreach ( $this->plugin->modules() as $module ) {
			if ( $module->admin_slug() === $page ) {
				return $module;
			}
		}

		return null;
	}

	/**
	 * Admin URL of a module's page, or of the dashboard.
	 *
	 * @param Module|null $module Module, or null for the dashboard.
	 */
	public function page_url( ?Module $module = null ): string {
		return admin_url( 'admin.php?page=' . ( $module ? $module->admin_slug() : self::MENU_SLUG ) );
	}

	/**
	 * Lenz's icon fonts (Font Awesome, `lenz-icon`) and the font chosen in
	 * Lenz → Typography, for module previews: wp-admin does not load the
	 * theme's stylesheets.
	 */
	public static function enqueue_theme_preview_assets(): void {
		if ( ! Theme_Bridge::is_active() ) {
			return;
		}

		$theme_url = get_template_directory_uri();
		wp_enqueue_style( 'lzp-theme-fontawesome', $theme_url . '/assets/libs/fontawesome/css/fa.min.css', array(), LENZ_PLUS_VERSION );
		wp_enqueue_style( 'lzp-theme-icons', $theme_url . '/assets/css/lenz-icons.min.css', array(), LENZ_PLUS_VERSION );

		// The font name is sanitized (no dots or slashes), so it cannot leave the fonts folder.
		$font = Theme_Bridge::font();
		if ( is_readable( get_template_directory() . '/assets/css/fonts/' . $font . '.min.css' ) ) {
			wp_enqueue_style( 'lzp-theme-font', $theme_url . '/assets/css/fonts/' . rawurlencode( $font ) . '.min.css', array(), LENZ_PLUS_VERSION );
		}
	}

	/**
	 * Inline SVG icon for the admin UI.
	 *
	 * @param string $key Semantic icon key.
	 */
	public function icon( string $key ): string {
		return Icon_Library::svg( self::UI_ICON_PACK, $key, false, 'lzp-ico' );
	}

	/** Strings used by admin.js (module scripts add their own). */
	private function js_strings(): array {
		return array(
			'saved'        => __( 'Settings saved.', 'lenz-plus' ),
			'saveFailed'   => __( 'Saving failed. Please try again.', 'lenz-plus' ),
			'saving'       => __( 'Saving…', 'lenz-plus' ),
			'save'         => __( 'Save changes', 'lenz-plus' ),
			'resetDone'    => __( 'Settings were reset to defaults.', 'lenz-plus' ),
			'resetTitle'   => __( 'Reset all settings?', 'lenz-plus' ),
			'resetMessage' => __( 'Every option of this feature goes back to its default. This cannot be undone.', 'lenz-plus' ),
			'resetConfirm' => __( 'Yes, reset', 'lenz-plus' ),
			'cancel'       => __( 'Cancel', 'lenz-plus' ),
			'unsavedLeave' => __( 'You have unsaved changes. Leave anyway?', 'lenz-plus' ),
			'enabled'      => __( 'Feature enabled.', 'lenz-plus' ),
			'disabled'     => __( 'Feature disabled.', 'lenz-plus' ),
			'themeDefault' => __( 'Theme default', 'lenz-plus' ),
			'resetColor'   => __( 'Use theme default', 'lenz-plus' ),
		);
	}

	/** Base64 SVG for the admin menu (WordPress recolours it to match the admin scheme). */
	private function menu_icon(): string {
		// A camera: Lenz is a photography theme.
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="black" fill-rule="evenodd" d="M9 4 7.5 6H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-3.5L15 4zm3 4.5a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9zm0 2a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5z"/></svg>';

		return 'data:image/svg+xml;base64,' . base64_encode( $svg ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- required by add_menu_page().
	}
}
