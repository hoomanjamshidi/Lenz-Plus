<?php
/**
 * The studio panel as an app: its screens, who sees which, the posted forms
 * and the document every screen is printed in. Visitors who are not logged
 * in go to WordPress's own login page and come back here.
 *
 * The document is the panel's own (views/shell.php): it prints only the
 * panel's styles and scripts (plus WordPress's editor when the classic
 * editor is chosen), so the theme, Elementor and other plugins never change
 * how it looks or slow it down. Forms are checked here (nonce per screen,
 * capability of the screen) before the screen runs them.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel;

use LenzPlus\Core\Asset;
use LenzPlus\Core\Site;
use LenzPlus\Core\Theme_Bridge;
use LenzPlus\Modules\Builder\Schema as Builder_Schema;
use LenzPlus\Modules\Panel\Screens\Content_Screen;
use LenzPlus\Modules\Panel\Screens\Screen;
use LenzPlus\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Screens, form handling and the page shell.
 */
final class App {

	/** Style and script handle of the panel. */
	public const HANDLE = 'lzp-panel';

	/** Phone tab bar: candidates for each slot, the first one open to the user wins. */
	private const TABS = array(
		array( 'home' ),
		// Articles are what the studio writes most often, so they get the first slot.
		array( 'posts', 'portfolio', 'courses', 'products' ),
		array( 'create' ),
		array( 'portfolio', 'courses', 'orders', 'bookings', 'requests' ),
		array( 'menu' ),
	);

	/** @var Module */
	private $module;

	/** @var Screen[]|null Screens open to the current user, keyed by id. */
	private $screens = null;

	/**
	 * @param Module $module Panel module.
	 */
	public function __construct( Module $module ) {
		$this->module = $module;
	}

	/**
	 * Screen classes in menu order (filter `lenz_plus_panel_screens` adds or
	 * removes screens; each must extend Screens\Screen).
	 *
	 * @return array<int, class-string<Screen>>
	 */
	private static function screen_classes(): array {
		return array(
			Screens\Dashboard::class,
			Screens\Posts::class,
			Screens\Portfolio::class,
			Screens\Courses::class,
			Screens\Products::class,
			Screens\Orders::class,
			Screens\Bookings::class,
			Screens\Requests::class,
			Screens\Comments::class,
			Screens\Pages::class,
			Screens\Media::class,
			Screens\Account::class,
			Screens\Create::class,
			Screens\Menu::class,
		);
	}

	/**
	 * Screens the current user can open, keyed by id.
	 *
	 * @return array<string, Screen>
	 */
	public function screens(): array {
		if ( null !== $this->screens ) {
			return $this->screens;
		}

		$sections      = $this->module->settings()['sections'];
		$this->screens = array();

		foreach ( (array) apply_filters( 'lenz_plus_panel_screens', self::screen_classes() ) as $class ) {
			if ( ! is_string( $class ) || ! is_subclass_of( $class, Screen::class ) ) {
				continue;
			}

			/** @var Screen $screen */
			$screen  = new $class( $this->module );
			$section = $screen->section();
			if ( ( '' === $section || ! empty( $sections[ $section ] ) ) && $screen->is_available() && $screen->can_open() ) {
				$this->screens[ $screen->id() ] = $screen;
			}
		}

		return $this->screens;
	}

	/**
	 * One open screen, or null.
	 *
	 * @param string $id Screen id.
	 */
	public function screen( string $id ): ?Screen {
		return $this->screens()[ $id ] ?? null;
	}

	/**
	 * Screens listed in the sidebar and the "More" tiles.
	 *
	 * @return Screen[]
	 */
	public function menu_screens(): array {
		return array_filter(
			$this->screens(),
			static function ( Screen $screen ): bool {
				return $screen->in_menu();
			}
		);
	}

	/**
	 * What the user can add: [url, label, icon] per content type, plus uploads.
	 *
	 * @return array<int, string[]>
	 */
	public function create_actions(): array {
		$actions = array();
		foreach ( $this->screens() as $screen ) {
			if ( $screen instanceof Content_Screen && 'pages' !== $screen->id() ) {
				$action = $screen->primary_action();
				if ( $action ) {
					$actions[] = array( $action[0], $action[1], $screen->icon() );
				}
			}
		}

		$media = $this->screen( 'media' );
		if ( $media ) {
			$actions[] = array( $media->url(), __( 'Upload photos', 'lenz-plus' ), 'upload' );
		}

		return $actions;
	}

	/**
	 * The phone's tab bar: up to five screens.
	 *
	 * @return Screen[]
	 */
	public function tabs(): array {
		$tabs = array();
		foreach ( self::TABS as $candidates ) {
			foreach ( $candidates as $id ) {
				$screen = $this->screen( $id );
				if ( $screen && ! isset( $tabs[ $id ] ) && ( 'create' !== $id || $this->create_actions() ) ) {
					$tabs[ $id ] = $screen;
					break;
				}
			}
		}

		return array_values( $tabs );
	}

	/**
	 * Answers a panel request: login redirect, no access, a posted form or a screen.
	 *
	 * @param string $id Requested screen id.
	 */
	public function run( string $id ): void {
		if ( ! is_user_logged_in() ) {
			// The panel is part of WordPress: people log in on WordPress's own login page.
			wp_safe_redirect( wp_login_url( $this->current_url() ) );
			return;
		}

		if ( ! $this->module->access()->can_use() ) {
			$this->document( 'denied' );
			return;
		}

		$screen = $this->screen( $id );
		if ( ! $screen ) {
			wp_safe_redirect( Router::url() );
			return;
		}

		if ( $this->is_post() ) {
			wp_safe_redirect( $this->handle_post( $screen ) );
			return;
		}

		$screen->enqueue();
		$this->document( 'shell', $screen );
	}

	/** Whether the request posts a form. */
	private function is_post(): bool {
		return isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Checks and runs a posted form; returns where to go next.
	 *
	 * @param Screen $screen Screen the form was posted to.
	 */
	private function handle_post( Screen $screen ): string {
		$nonce = isset( $_POST[ Screen::NONCE_FIELD ] ) ? sanitize_text_field( wp_unslash( $_POST[ Screen::NONCE_FIELD ] ) ) : '';

		if ( '' === $nonce && ! empty( $_SERVER['CONTENT_LENGTH'] ) ) {
			// PHP drops the whole form when it is larger than post_max_size (big videos).
			return add_query_arg( Screen::MESSAGE_ARG, 'too_big', $this->current_url() );
		}

		if ( ! wp_verify_nonce( $nonce, $screen->nonce_action() ) ) {
			return add_query_arg( Screen::MESSAGE_ARG, 'expired', $this->current_url() );
		}

		$action = isset( $_POST[ Screen::ACTION_FIELD ] ) ? sanitize_key( wp_unslash( $_POST[ Screen::ACTION_FIELD ] ) ) : '';

		return $screen->handle( $action );
	}

	/** The requested URL (for redirects back to the same page). */
	private function current_url(): string {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';

		return remove_query_arg( Screen::MESSAGE_ARG, home_url( $uri ) );
	}

	/**
	 * Prints a whole document.
	 *
	 * @param string      $view   `shell` or `denied`.
	 * @param Screen|null $screen Screen of the shell.
	 */
	private function document( string $view, ?Screen $screen = null ): void {
		$this->enqueue_assets();

		$app     = $this;
		$module  = $this->module;
		$message = $this->message();
		$content = '';

		if ( $screen ) {
			// The page first, so what it marks (seen requests) shows in the menu badges.
			ob_start();
			$screen->render();
			$content = (string) ob_get_clean();
		}

		require __DIR__ . '/views/' . $view . '.php';
	}

	/** Registers and enqueues the panel's styles and script. */
	private function enqueue_assets(): void {
		$deps = array();
		if ( Theme_Bridge::is_active() ) {
			$font = Theme_Bridge::font();
			if ( is_readable( get_template_directory() . '/assets/css/fonts/' . $font . '.min.css' ) ) {
				wp_register_style( 'lzp-panel-font', get_template_directory_uri() . '/assets/css/fonts/' . rawurlencode( $font ) . '.min.css', array(), LENZ_PLUS_VERSION );
				$deps[] = 'lzp-panel-font';
			}
		}

		wp_enqueue_style( self::HANDLE, Asset::url( 'assets/modules/panel/css/panel.css' ), $deps, LENZ_PLUS_VERSION );
		wp_add_inline_style( self::HANDLE, $this->tokens_css() );

		wp_enqueue_script( self::HANDLE, Asset::url( 'assets/modules/panel/js/panel.js' ), array(), LENZ_PLUS_VERSION, array( 'in_footer' => true ) );
		wp_localize_script(
			self::HANDLE,
			'lzpPanel',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'uploadNonce' => wp_create_nonce( Uploads::AJAX_ACTION ),
				'i18n'        => array(
					'uploading'   => __( 'Uploading…', 'lenz-plus' ),
					'uploadError' => __( 'The file could not be uploaded. Use a photo or a video.', 'lenz-plus' ),
					'linkPrompt'  => __( 'Link address (https://…)', 'lenz-plus' ),
					'copied'      => __( 'Link copied.', 'lenz-plus' ),
					'unsaved'     => __( 'You have unsaved changes. Leave anyway?', 'lenz-plus' ),
				),
			)
		);
	}

	/**
	 * The panel's colour and font tokens: the Builder's brand palette (the
	 * colours of the site's designs), and Lenz's font when the theme is on.
	 */
	private function tokens_css(): string {
		$builder = Plugin::instance()->module( 'builder' );
		$brand   = $builder ? (array) ( $builder->settings()['brand'] ?? array() ) : array();

		$vars = array();
		foreach ( array( 'ink', 'accent', 'text', 'sub', 'muted', 'soft', 'chip', 'line' ) as $key ) {
			$value  = isset( $brand[ $key ] ) && '' !== $brand[ $key ] ? $brand[ $key ] : Builder_Schema::BRAND_DEFAULTS[ $key ];
			$vars[] = '--lzp-p-' . $key . ':' . $value;
		}

		$font   = Theme_Bridge::is_active() ? Theme_Bridge::font() : '';
		$vars[] = '--lzp-p-font:' . ( '' !== $font ? '"' . $font . '",' : '' ) . '"Vazirmatn",Tahoma,system-ui,sans-serif';

		return ':root{' . implode( ';', $vars ) . '}';
	}

	/**
	 * The message asked for by the redirect after a form, as [text, tone].
	 *
	 * @return string[]
	 */
	private function message(): array {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a message key, looked up in a fixed list.
		$key = isset( $_GET[ Screen::MESSAGE_ARG ] ) ? sanitize_key( wp_unslash( $_GET[ Screen::MESSAGE_ARG ] ) ) : '';

		return self::messages()[ $key ] ?? array();
	}

	/**
	 * Every message key → [text, tone].
	 *
	 * @return array<string, string[]>
	 */
	private static function messages(): array {
		return array(
			'saved'       => array( __( 'Saved.', 'lenz-plus' ), 'ok' ),
			'created'     => array( __( 'Created. You can keep editing it here.', 'lenz-plus' ), 'ok' ),
			'trashed'     => array( __( 'Moved to the trash.', 'lenz-plus' ), 'ok' ),
			'deleted'     => array( __( 'Deleted.', 'lenz-plus' ), 'ok' ),
			'status'      => array( __( 'Status changed.', 'lenz-plus' ), 'ok' ),
			'note'        => array( __( 'Note added.', 'lenz-plus' ), 'ok' ),
			'reply'       => array( __( 'Reply posted.', 'lenz-plus' ), 'ok' ),
			'uploaded'    => array( __( 'Uploaded.', 'lenz-plus' ), 'ok' ),
			'empty'       => array( __( 'Write something first.', 'lenz-plus' ), 'warn' ),
			'email'       => array( __( 'That email address is not valid.', 'lenz-plus' ), 'bad' ),
			'email_taken' => array( __( 'Another account already uses that email address.', 'lenz-plus' ), 'bad' ),
			'password'    => array( __( 'The two passwords do not match.', 'lenz-plus' ), 'bad' ),
			'denied'      => array( __( 'You are not allowed to do that.', 'lenz-plus' ), 'bad' ),
			'error'       => array( __( 'That did not work. Please try again.', 'lenz-plus' ), 'bad' ),
			'expired'     => array( __( 'The page was open too long. Please try again.', 'lenz-plus' ), 'warn' ),
			'too_big'     => array( __( 'The files were too large for the server. Send fewer or smaller files at a time.', 'lenz-plus' ), 'bad' ),
		);
	}

	/** Title of the panel (settings, else «Studio panel»). */
	public function title(): string {
		return $this->module->panel_title();
	}

	/** The site's logo markup for the panel's header ('' = the site name is shown). */
	public function logo_html(): string {
		return Theme_Bridge::logo_html( 'header' );
	}

	/** `rtl` or `ltr`. */
	public function direction(): string {
		return Site::direction();
	}

	/** Brand ink colour (browser bar colour on phones). */
	public function theme_color(): string {
		$builder = Plugin::instance()->module( 'builder' );
		$ink     = $builder ? (string) ( $builder->settings()['brand']['ink'] ?? '' ) : '';

		return '' !== $ink ? $ink : Builder_Schema::BRAND_DEFAULTS['ink'];
	}
}
