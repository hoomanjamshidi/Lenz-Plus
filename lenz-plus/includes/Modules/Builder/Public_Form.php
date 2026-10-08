<?php
/**
 * Shared plumbing for the public forms behind the widgets (request forms,
 * newsletter and waitlist sign-ups): the handler hooks, the saved widget a
 * post belongs to, the honeypot, a per-visitor rate limit, the
 * answer (JSON for the widget scripts, a redirect back to the form without
 * JavaScript) and the admin's CSV download.
 *
 * There is no nonce: the forms sit on cached public pages where a nonce
 * would expire, so the honeypot and the rate limit keep bots out instead.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

use LenzPlus\Admin\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Handler hooks, honeypot, rate limit, responses, saved widget lookup and CSV export.
 */
abstract class Public_Form {

	/** Name of the honeypot field (hidden from people, filled in by bots). */
	public const HONEYPOT = 'lzp_website';

	/** Posts read per query while exporting. */
	private const EXPORT_BATCH = 100;

	/** @var string Form ID to scroll back to after a plain (no-JS) submit. */
	private $anchor = '';

	/** Query argument that carries the result back to the page without JavaScript. */
	abstract protected function result_arg(): string;

	/**
	 * Hooks a form handler for admin-post.php (plain form) and admin-ajax.php
	 * (the widget's script), for visitors and logged-in users alike.
	 *
	 * @param string   $action  Form `action` field.
	 * @param callable $handler Handler.
	 */
	protected function add_handler( string $action, callable $handler ): void {
		foreach ( array( 'admin_post_', 'admin_post_nopriv_', 'wp_ajax_', 'wp_ajax_nopriv_' ) as $hook ) {
			add_action( $hook . $action, $handler );
		}
	}

	/**
	 * Reads the fields every form sends. A filled honeypot ends the request
	 * with a fake success, so bots learn nothing.
	 */
	protected function start(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- public form on cached pages; see the class docblock.
		$this->anchor = isset( $_POST['anchor'] ) ? sanitize_html_class( wp_unslash( $_POST['anchor'] ) ) : '';
		$honeypot     = isset( $_POST[ self::HONEYPOT ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::HONEYPOT ] ) ) : '';
		// phpcs:enable

		if ( '' !== $honeypot ) {
			$this->respond( 'ok' );
		}
	}

	/**
	 * Settings of the saved widget a post comes from, or null when the
	 * request does not point at one. Forms read their options (fields, lists,
	 * where to email) from here, never from the request, so a crafted post
	 * cannot change what is accepted or turn the form into a mail relay.
	 *
	 * @param int    $document_id Elementor document holding the widget (page or template).
	 * @param string $element_id  Widget element ID.
	 * @param string $widget      Elementor widget name the element must be.
	 */
	protected static function saved_widget( int $document_id, string $element_id, string $widget ): ?array {
		// Published documents only: a draft by a lower-privileged editor must not be able to send mail.
		if ( ! $document_id || '' === $element_id || ! Library::elementor_active() || 'publish' !== get_post_status( $document_id ) ) {
			return null;
		}

		$document = \Elementor\Plugin::$instance->documents->get( $document_id );
		$element  = $document ? self::find_widget( (array) $document->get_elements_data(), $element_id ) : null;

		if ( null === $element || ( $element['widgetType'] ?? '' ) !== $widget ) {
			return null;
		}

		return is_array( $element['settings'] ?? null ) ? $element['settings'] : array();
	}

	/**
	 * Depth-first search for an element by ID.
	 *
	 * @param array  $elements   Elementor elements.
	 * @param string $element_id Element ID.
	 */
	private static function find_widget( array $elements, string $element_id ): ?array {
		foreach ( $elements as $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}
			if ( ( $element['id'] ?? '' ) === $element_id ) {
				return $element;
			}
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$found = self::find_widget( $element['elements'], $element_id );
				if ( null !== $found ) {
					return $found;
				}
			}
		}

		return null;
	}

	/**
	 * The Elementor document a widget is being rendered in: the page, or the
	 * template (header, footer…) that holds it. Forms post it back so the
	 * handler can find their saved settings.
	 */
	public static function current_document_id(): int {
		$document = class_exists( '\Elementor\Plugin' ) ? \Elementor\Plugin::$instance->documents->get_current() : null;

		return $document ? (int) $document->get_main_id() : (int) get_the_ID();
	}

	/**
	 * Hidden fields every form sends: the handler action, where the widget is
	 * saved, the page it is on, the anchor to return to and the honeypot.
	 *
	 * @param string $action    Handler action.
	 * @param string $widget_id Elementor element ID.
	 * @param string $anchor    HTML id of the form.
	 */
	public static function hidden_fields( string $action, string $widget_id, string $anchor ): string {
		$fields = array(
			'action'   => $action,
			'document' => (string) self::current_document_id(),
			'form'     => $widget_id,
			'source'   => (string) get_queried_object_id(),
			'anchor'   => $anchor,
		);

		$html = '';
		foreach ( $fields as $name => $value ) {
			$html .= '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
		}

		// Bots fill every field; people never see this one.
		return $html . '<span class="lzp-form__trap" aria-hidden="true"><input type="text" name="' . esc_attr( self::HONEYPOT ) . '" tabindex="-1" autocomplete="off"></span>';
	}

	/**
	 * Result of a plain (no-JS) post, read back from the URL, '' unless it
	 * belongs to this form.
	 *
	 * @param string $arg     Query argument.
	 * @param string $form_id HTML id of the form asking.
	 */
	public static function plain_result( string $arg, string $form_id ): string {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- display only: the result of a plain form post.
		$result = isset( $_GET[ $arg ] ) ? sanitize_key( wp_unslash( $_GET[ $arg ] ) ) : '';
		$sender = isset( $_GET['lzp_form'] ) ? sanitize_html_class( wp_unslash( $_GET['lzp_form'] ) ) : '';
		// phpcs:enable

		return '' === $sender || $sender === $form_id ? $result : '';
	}

	/**
	 * What the forms say when a post fails, by status. Widgets show them
	 * after a plain (no-JS) post and forms.js after a background one.
	 *
	 * @return array<string, string>
	 */
	public static function error_messages(): array {
		return array(
			'invalid' => __( 'Please check the highlighted fields and try again.', 'lenz-plus' ),
			'busy'    => __( 'Too many requests in a short time. Please try again in a few minutes.', 'lenz-plus' ),
			'error'   => __( 'Your request could not be sent. Please try again.', 'lenz-plus' ),
		);
	}

	/**
	 * Counts this visitor's submissions; false once they exceed the limit.
	 *
	 * @param string $bucket Short name of the counter (one per form type).
	 * @param int    $limit  Submissions allowed per window.
	 * @param int    $window Window in seconds.
	 */
	protected function within_rate_limit( string $bucket, int $limit, int $window ): bool {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		/**
		 * The visitor's address for the form rate limit. Behind a proxy or CDN that does not
		 * restore REMOTE_ADDR, every visitor shares one address: return the real one here.
		 *
		 * @param string $ip REMOTE_ADDR.
		 */
		$ip  = (string) apply_filters( 'lenz_plus_client_ip', $ip );
		$key = 'lzp_' . $bucket . '_' . md5( $ip . wp_salt( 'nonce' ) );

		$count = (int) get_transient( $key );
		if ( $count >= $limit ) {
			return false;
		}

		set_transient( $key, $count + 1, $window );

		return true;
	}

	/**
	 * JSON for the widget's script; a redirect back to the form otherwise.
	 *
	 * @param string $status `ok`, or an error status the widget has a message for.
	 * @param array  $extra  More data for the script (e.g. the invalid fields).
	 */
	protected function respond( string $status, array $extra = array() ): void {
		if ( wp_doing_ajax() ) {
			$data = array_merge( $extra, array( 'status' => $status ) );
			if ( 'ok' === $status ) {
				wp_send_json_success( $data );
			}
			$codes = array(
				'invalid' => 400,
				'busy'    => 429,
			);
			wp_send_json_error( $data, $codes[ $status ] ?? 500 );
		}

		$arg  = $this->result_arg();
		$back = wp_get_referer();
		// The form's id comes back too, so only that form shows the result when a page has several.
		$back = add_query_arg(
			array(
				$arg       => $status,
				'lzp_form' => $this->anchor,
			),
			remove_query_arg( array( $arg, 'lzp_form' ), $back ? $back : home_url( '/' ) )
		);

		wp_safe_redirect( '' !== $this->anchor ? $back . '#' . $this->anchor : $back );
		exit;
	}

	/**
	 * Streams every private post of a type as a CSV download, oldest first.
	 *
	 * @param string   $post_type Post type.
	 * @param string   $name      File name prefix (the date is added).
	 * @param string[] $header    Column names.
	 * @param callable $row       Receives a WP_Post, returns its cells.
	 */
	protected static function export_csv( string $post_type, string $name, array $header, callable $row ): void {
		if ( ! current_user_can( Admin::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to change these settings.', 'lenz-plus' ), 403 );
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $name . '-' . gmdate( 'Y-m-d' ) . '.csv' );

		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- streaming a download.
		// A byte order mark lets Excel read the file as UTF-8.
		fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		// An explicit, empty escape character: RFC 4180 quoting only (PHP 8.4 deprecates the default).
		// Visitor text starting with = + - @ would run as a formula in Excel: a leading quote keeps it text.
		$put = static function ( array $cells ) use ( $out ): void {
			foreach ( $cells as $i => $cell ) {
				if ( is_string( $cell ) && preg_match( '/^[=+\-@\t\r]/u', $cell ) ) {
					$cells[ $i ] = "'" . $cell;
				}
			}
			fputcsv( $out, $cells, ',', '"', '' );
		};

		$put( $header );

		$page = 1;
		do {
			$posts = get_posts(
				array(
					'post_type'      => $post_type,
					'post_status'    => 'private',
					'posts_per_page' => self::EXPORT_BATCH,
					'paged'          => $page,
					'orderby'        => 'date',
					'order'          => 'ASC',
					'no_found_rows'  => true,
				)
			);

			foreach ( $posts as $post ) {
				$put( $row( $post ) );
			}

			++$page;
			$more = count( $posts ) === self::EXPORT_BATCH;
		} while ( $more );

		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}

	/**
	 * Nonce-protected admin-post.php link.
	 *
	 * @param string $action Admin-post action (also the nonce action).
	 */
	protected static function admin_action_url( string $action ): string {
		return wp_nonce_url( admin_url( 'admin-post.php?action=' . $action ), $action );
	}

	/** URL the forms post to without JavaScript. */
	public static function form_url(): string {
		return admin_url( 'admin-post.php' );
	}
}
