<?php
/**
 * Base of every panel screen (dashboard, portfolio, orders…).
 *
 * A screen declares who may open it (a WordPress capability), whether the
 * site can show it (e.g. WooCommerce active) and which settings switch it
 * off, prints its page, and handles the forms it prints. Forms post back to
 * the screen's own URL with a nonce for that screen; App checks the nonce and
 * the capability before handle() runs and redirects to the URL it returns
 * (post/redirect/get), so a reload never sends a form twice.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Modules\Panel\Module;
use LenzPlus\Modules\Panel\Router;

defined( 'ABSPATH' ) || exit;

/**
 * Identity, access, page and form handling of one screen.
 */
abstract class Screen {

	/** Field carrying the form's action. */
	public const ACTION_FIELD = 'lzp_do';

	/** Field carrying the form's nonce. */
	public const NONCE_FIELD = '_lzpnonce';

	/** Query argument with the key of the message shown after a redirect. */
	public const MESSAGE_ARG = 'lzp_msg';

	/** @var Module */
	protected $module;

	/**
	 * @param Module $module Panel module (settings).
	 */
	public function __construct( Module $module ) {
		$this->module = $module;
	}

	/** Screen id: the URL segment. */
	abstract public function id(): string;

	/** Menu label. */
	abstract public function title(): string;

	/** Menu icon key. */
	abstract public function icon(): string;

	/** WordPress capability needed to open it. */
	abstract public function capability(): string;

	/** Prints the page. */
	abstract public function render(): void;

	/** Section key of Schema::SECTIONS that switches it off, or '' (always there). */
	public function section(): string {
		return '';
	}

	/** Whether the site can show the screen (plugins it needs are active). */
	public function is_available(): bool {
		return true;
	}

	/** Whether it is listed in the menus (sub screens such as "create" are not). */
	public function in_menu(): bool {
		return true;
	}

	/** Number shown next to the menu item (new items), 0 for none. */
	public function badge(): int {
		return 0;
	}

	/** Title of the current page in the top bar (e.g. "Edit project"). */
	public function page_title(): string {
		return $this->title();
	}

	/** Where the top bar's back button leads, '' on the screen's main page. */
	public function back_url(): string {
		return '';
	}

	/**
	 * The top bar's main action on this page: [url, label, icon] or empty.
	 *
	 * @return string[]
	 */
	public function primary_action(): array {
		return array();
	}

	/** Enqueues what the page needs before the document head is printed. */
	public function enqueue(): void {}

	/**
	 * Runs a posted form and returns where to go next.
	 *
	 * @param string $action Action name from the form.
	 */
	public function handle( string $action ): string {
		unset( $action );

		return $this->url();
	}

	/** Whether the current user may open the screen. */
	public function can_open(): bool {
		return current_user_can( $this->capability() );
	}

	/**
	 * URL of this screen.
	 *
	 * @param array<string, mixed> $args Query arguments.
	 */
	public function url( array $args = array() ): string {
		return Router::url( $this->id(), $args );
	}

	/**
	 * URL with a message to show after the redirect.
	 *
	 * @param string               $message Message key (App::messages()).
	 * @param array<string, mixed> $args    Query arguments.
	 */
	protected function done( string $message, array $args = array() ): string {
		return $this->url( $args + array( self::MESSAGE_ARG => $message ) );
	}

	/** Nonce action of the screen's forms. */
	public function nonce_action(): string {
		return 'lzp_panel_' . $this->id();
	}

	/**
	 * Opens a form that posts back to the current page.
	 *
	 * @param string $action  Action name.
	 * @param string $classes Extra classes.
	 * @param bool   $files   Whether the form uploads files.
	 */
	protected function form_open( string $action, string $classes = '', bool $files = false ): void {
		printf(
			'<form method="post" class="%1$s"%2$s>',
			esc_attr( trim( 'lzp-form ' . $classes ) ),
			$files ? ' enctype="multipart/form-data"' : ''
		);
		wp_nonce_field( $this->nonce_action(), self::NONCE_FIELD, false );
		printf( '<input type="hidden" name="%1$s" value="%2$s">', esc_attr( self::ACTION_FIELD ), esc_attr( $action ) );
	}

	/**
	 * A one-button form (status change, delete…).
	 *
	 * @param string               $action  Action name.
	 * @param array<string, mixed> $fields  Hidden fields.
	 * @param string               $label   Button text.
	 * @param string               $classes Button classes.
	 * @param string               $confirm Question asked before sending ('' = none).
	 */
	protected function button_form( string $action, array $fields, string $label, string $classes = 'lzp-btn lzp-btn--ghost', string $confirm = '' ): void {
		$this->form_open( $action, 'lzp-inline-form' );
		foreach ( $fields as $name => $value ) {
			printf( '<input type="hidden" name="%1$s" value="%2$s">', esc_attr( $name ), esc_attr( (string) $value ) );
		}
		printf(
			'<button type="submit" class="%1$s"%2$s>%3$s</button></form>',
			esc_attr( $classes ),
			'' !== $confirm ? ' data-lzp-confirm="' . esc_attr( $confirm ) . '"' : '',
			esc_html( $label )
		);
	}

	/**
	 * A posted text value, unslashed (not sanitized: callers choose how).
	 *
	 * @param string $key Field name.
	 */
	protected static function posted( string $key ): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- App verified the nonce; callers sanitize.
		return isset( $_POST[ $key ] ) && is_scalar( $_POST[ $key ] ) ? (string) wp_unslash( $_POST[ $key ] ) : '';
	}

	/**
	 * A posted array, unslashed (callers sanitize each value).
	 *
	 * @param string $key Field name.
	 */
	protected static function posted_array( string $key ): array {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- App verified the nonce; callers sanitize.
		return isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ? (array) wp_unslash( $_POST[ $key ] ) : array();
	}

	/**
	 * A query argument as a key.
	 *
	 * @param string $key Argument name.
	 */
	protected static function arg( string $key ): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only navigation.
		return isset( $_GET[ $key ] ) && is_scalar( $_GET[ $key ] ) ? sanitize_key( wp_unslash( $_GET[ $key ] ) ) : '';
	}

	/**
	 * A query argument as a positive number (0 when missing).
	 *
	 * @param string $key Argument name.
	 */
	protected static function arg_int( string $key ): int {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only navigation.
		return isset( $_GET[ $key ] ) && is_scalar( $_GET[ $key ] ) ? absint( $_GET[ $key ] ) : 0;
	}

	/** The search typed in a list. */
	protected static function search_term(): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only search.
		return isset( $_GET['s'] ) && is_scalar( $_GET['s'] ) ? trim( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) : '';
	}

	/**
	 * Query arguments carried by GET forms when the site has plain permalinks
	 * (the screen id travels as a query argument there).
	 *
	 * @return array<string, string>
	 */
	protected function plain_args(): array {
		return get_option( 'permalink_structure' ) ? array() : array( Router::QUERY_VAR => $this->id() );
	}

	/** Current list page (1-based). */
	protected static function page_number(): int {
		return max( 1, self::arg_int( 'paged' ) );
	}

	/** Items per list page (settings). */
	protected function per_page(): int {
		return (int) $this->module->settings()['per_page'];
	}
}
