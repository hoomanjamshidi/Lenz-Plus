<?php
/**
 * Base of the admin screens that list what visitors send (requests,
 * bookings): WordPress's own list table for a private post type (search,
 * paging, trash for free), a menu item under Lenz+ with a count of the
 * posts that arrived since an admin last opened it, and the shared cells
 * (sender with ways to answer, time received). Posts are read in the list
 * itself, so the edit screen is never used.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

use LenzPlus\Admin\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Columns, row actions, menu item and "new" badge of a visitor posts list.
 */
abstract class Inbox {

	/** Most posts counted for the menu badge. */
	private const BADGE_CAP = 99;

	/** @var int Last visit before this one: posts after it are new. */
	private $seen_before = 0;

	/** The listed post type. */
	abstract protected function post_type(): string;

	/** Menu and screen title. */
	abstract protected function menu_title(): string;

	/** Option holding the time an admin last opened the list (Unix time). */
	abstract protected function seen_option(): string;

	/**
	 * The list's columns.
	 *
	 * @param string[] $columns Default columns.
	 * @return string[]
	 */
	abstract public function columns( $columns ): array;

	/**
	 * Prints one cell.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	abstract public function render_column( $column, $post_id ): void;

	/**
	 * Row actions besides trash/delete (reply, status changes…).
	 *
	 * @param \WP_Post $post Post.
	 * @return string[]
	 */
	abstract protected function actions( \WP_Post $post ): array;

	/** Styles of the list screen. */
	abstract protected function styles(): string;

	/** Hooks the list table, the menu item and the badge. */
	public function register(): void {
		$type = $this->post_type();

		add_filter( 'manage_' . $type . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . $type . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_filter( 'list_table_primary_column', array( $this, 'primary_column' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'row_actions' ), 10, 2 );
		add_filter( 'bulk_actions-edit-' . $type, array( $this, 'bulk_actions' ) );
		add_action( 'load-edit.php', array( $this, 'mark_seen' ) );
		add_action( 'load-post.php', array( $this, 'redirect_edit_screen' ) );
		// After Admin::register_menu(): a post type's own menu item would come before the dashboard.
		add_action( 'admin_menu', array( $this, 'add_menu' ), 20 );
		// admin_head runs after load-edit.php, so the badge already counts this visit.
		add_action( 'admin_head', array( $this, 'menu_badge' ) );
	}

	/** The list under the Lenz+ menu. */
	public function add_menu(): void {
		add_submenu_page( Admin::MENU_SLUG, $this->menu_title(), $this->menu_title(), Admin::CAPABILITY, $this->list_slug() );
	}

	/** Admin page slug of the list. */
	private function list_slug(): string {
		return 'edit.php?post_type=' . $this->post_type();
	}

	/**
	 * Name, "New" badge and the ways to answer.
	 *
	 * @param \WP_Post $post  Post.
	 * @param string   $email Sender's email.
	 * @param string   $phone Sender's phone.
	 */
	protected function render_sender( \WP_Post $post, string $email, string $phone ): void {
		printf( '<strong>%s</strong>', esc_html( $post->post_title ) );
		if ( (int) get_post_time( 'U', true, $post ) > $this->seen_before ) {
			printf( ' <span class="lzp-inbox-new">%s</span>', esc_html__( 'New', 'lenz-plus' ) );
		}

		// Addresses and numbers are left-to-right even on Persian screens.
		if ( '' !== $email ) {
			printf( '<br><a href="mailto:%1$s" dir="ltr">%2$s</a>', esc_attr( $email ), esc_html( $email ) );
		}
		if ( '' !== $phone ) {
			printf( '<br><a href="tel:%1$s" dir="ltr">%2$s</a>', esc_attr( (string) preg_replace( '/[^\d+]/', '', $phone ) ), esc_html( $phone ) );
		}
	}

	/**
	 * When the post arrived ("5 minutes ago", the full date on hover).
	 *
	 * @param \WP_Post $post Post.
	 */
	protected function render_received( \WP_Post $post ): void {
		$time = (int) get_post_time( 'U', true, $post );
		printf(
			'<time datetime="%1$s" title="%2$s">%3$s</time>',
			esc_attr( (string) get_post_time( 'c', true, $post ) ),
			esc_attr( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $time ) ),
			/* translators: %s: time since the request arrived, e.g. "5 minutes". */
			esc_html( sprintf( __( '%s ago', 'lenz-plus' ), human_time_diff( $time ) ) )
		);
	}

	/**
	 * Link to the page the form was on, or a dash.
	 *
	 * @param int $source Page ID.
	 */
	protected function render_page( int $source ): void {
		if ( $source && get_post( $source ) ) {
			printf( '<a href="%1$s" target="_blank" rel="noopener">%2$s</a>', esc_url( (string) get_permalink( $source ) ), esc_html( get_the_title( $source ) ) );
		} else {
			echo '—';
		}
	}

	/**
	 * "Reply by email" link, '' without an address.
	 *
	 * @param string $email Sender's email.
	 * @param string $topic What is being answered.
	 */
	protected static function reply_link( string $email, string $topic ): string {
		if ( '' === $email ) {
			return '';
		}

		/* translators: %s: name of the form being answered. */
		$subject = '' !== $topic ? sprintf( __( 'Re: %s', 'lenz-plus' ), $topic ) : get_bloginfo( 'name' );

		return sprintf( '<a href="%1$s">%2$s</a>', esc_url( 'mailto:' . $email . '?subject=' . rawurlencode( $subject ) ), esc_html__( 'Reply by email', 'lenz-plus' ) );
	}

	/**
	 * The sender column is the primary one (row actions go there).
	 *
	 * @param string $primary Default primary column.
	 * @param string $screen  Screen ID.
	 */
	public function primary_column( $primary, $screen ) {
		return 'edit-' . $this->post_type() === $screen ? 'sender' : $primary;
	}

	/**
	 * The list's own actions instead of edit, quick edit and view.
	 *
	 * @param string[] $actions Row actions.
	 * @param \WP_Post $post    Post.
	 * @return string[]
	 */
	public function row_actions( $actions, $post ) {
		if ( ! $post instanceof \WP_Post || $this->post_type() !== $post->post_type ) {
			return $actions;
		}

		$keep = array_intersect_key( $actions, array_flip( array( 'trash', 'untrash', 'delete' ) ) );

		return ( 'trash' === $post->post_status ? array() : array_filter( $this->actions( $post ) ) ) + $keep;
	}

	/**
	 * No bulk editing: posts are read, answered and trashed.
	 *
	 * @param string[] $actions Bulk actions.
	 * @return string[]
	 */
	public function bulk_actions( $actions ): array {
		unset( $actions['edit'] );

		return $actions;
	}

	/** Remembers the previous visit (for the "New" badges) and records this one. */
	public function mark_seen(): void {
		if ( ! $this->is_list() ) {
			return;
		}

		$this->seen_before = (int) get_option( $this->seen_option(), 0 );
		update_option( $this->seen_option(), time(), false );

		add_action( 'admin_head', array( $this, 'print_styles' ) );
	}

	/** Posts are read in the list; opening one goes back there. */
	public function redirect_edit_screen(): void {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- routing only.

		if ( $this->post_type() === get_post_type( $post_id ) ) {
			wp_safe_redirect( admin_url( $this->list_slug() ) );
			exit;
		}
	}

	/** Adds the number of new posts to the menu item, like WordPress's comment count. */
	public function menu_badge(): void {
		global $submenu;

		if ( ! current_user_can( Admin::CAPABILITY ) || empty( $submenu[ Admin::MENU_SLUG ] ) ) {
			return;
		}

		$new = count(
			get_posts(
				array(
					'post_type'      => $this->post_type(),
					'post_status'    => 'private',
					'posts_per_page' => self::BADGE_CAP,
					'fields'         => 'ids',
					'no_found_rows'  => true,
					'date_query'     => array(
						array(
							'column' => 'post_date_gmt',
							'after'  => gmdate( 'Y-m-d H:i:s', (int) get_option( $this->seen_option(), 0 ) ),
						),
					),
				)
			)
		);

		if ( ! $new ) {
			return;
		}

		foreach ( $submenu[ Admin::MENU_SLUG ] as $index => $item ) {
			if ( $this->list_slug() === $item[2] ) {
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- adding a count badge is the documented way.
				$submenu[ Admin::MENU_SLUG ][ $index ][0] .= sprintf( ' <span class="awaiting-mod"><span class="pending-count">%s</span></span>', esc_html( number_format_i18n( $new ) ) );
			}
		}
	}

	/** Prints the list's styles (the "New" badge plus the list's own). */
	public function print_styles(): void {
		echo '<style>.lzp-inbox-new{display:inline-block;padding:0 7px;border-radius:9px;background:#d63638;color:#fff;font-size:11px;line-height:18px;vertical-align:middle}' . $this->styles() . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static CSS.
	}

	/** Whether the current screen is this list. */
	protected function is_list(): bool {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		return $screen && 'edit-' . $this->post_type() === $screen->id;
	}
}
