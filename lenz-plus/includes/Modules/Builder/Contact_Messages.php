<?php
/**
 * Requests sent through the Request form widget (booking, workshop
 * registration, contact).
 *
 * The widget's fields are configurable (name, phone, email, text, choice,
 * date, long text), so a request is stored as a private `lzp_message` post
 * with its answers as an ordered list of label/value pairs: nothing is lost
 * on hosts where email does not work, and a copy goes by email to the
 * address set on the widget. Which fields exist, which are required and
 * where the email goes are read from the saved widget, never from the
 * request (see Public_Form::saved_widget()). Contact_Inbox lists the
 * requests in the admin.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

use LenzPlus\Admin\Admin;
use LenzPlus\Core\Persian;

defined( 'ABSPATH' ) || exit;

/**
 * Post type, handler, notification, CSV export and privacy tools of the requests.
 */
final class Contact_Messages extends Public_Form {

	public const POST_TYPE = 'lzp_message';

	/** Form and AJAX action. */
	public const ACTION = 'lzp_request';

	/** Elementor widget name of the request form. */
	public const WIDGET = 'lzp-request-form';

	/** Name of the form the request came from («رزرو وقت»). */
	public const META_FORM = '_lzp_form';

	/** Answers: list of [label, value] in the form's order. */
	public const META_FIELDS = '_lzp_fields';

	public const META_EMAIL = '_lzp_email';

	public const META_PHONE = '_lzp_phone';

	/** Page the request was sent from. */
	public const META_SOURCE = '_lzp_source';

	/** Field types the form offers. */
	public const FIELD_TYPES = array( 'name', 'phone', 'email', 'text', 'select', 'date', 'textarea' );

	private const EXPORT_ACTION = 'lzp_requests_export';

	/** Requests allowed per visitor in RATE_WINDOW seconds. */
	private const RATE_LIMIT = 3;

	private const RATE_WINDOW = 600;

	/** Longest value kept per field type. */
	private const MAX_LENGTH = array(
		'name'     => 100,
		'textarea' => 5000,
	);

	private const MAX_DEFAULT = 200;

	/** Registers the post type, the form handler, the export and the privacy tools. */
	public function register(): void {
		$cap = Admin::CAPABILITY;

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'               => __( 'Requests', 'lenz-plus' ),
					'singular_name'      => __( 'Request', 'lenz-plus' ),
					'menu_name'          => __( 'Requests', 'lenz-plus' ),
					'all_items'          => __( 'Requests', 'lenz-plus' ),
					'search_items'       => __( 'Search requests', 'lenz-plus' ),
					'not_found'          => __( 'No requests yet. Requests sent through the Request form widget appear here.', 'lenz-plus' ),
					'not_found_in_trash' => __( 'No requests in the trash.', 'lenz-plus' ),
				),
				'public'              => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				// Contact_Inbox adds the menu item itself, after the modules.
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'show_in_rest'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title' ),
				// Only administrators read requests, and nobody writes one in the admin.
				'capability_type'     => array( 'lzp_message', 'lzp_messages' ),
				'map_meta_cap'        => true,
				'capabilities'        => array(
					'create_posts'           => 'do_not_allow',
					'edit_posts'             => $cap,
					'edit_others_posts'      => $cap,
					'edit_private_posts'     => $cap,
					'edit_published_posts'   => $cap,
					'delete_posts'           => $cap,
					'delete_others_posts'    => $cap,
					'delete_private_posts'   => $cap,
					'delete_published_posts' => $cap,
					'read_private_posts'     => $cap,
					'publish_posts'          => $cap,
				),
			)
		);

		$this->add_handler( self::ACTION, array( $this, 'handle' ) );

		add_action( 'admin_post_' . self::EXPORT_ACTION, array( $this, 'export' ) );
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_eraser' ) );
	}

	/**
	 * Options the handler reads from the saved widget; the widget's controls
	 * use them as their defaults (Elementor does not save untouched defaults).
	 *
	 * @return array{form_name:string, fields:array, notify:string, email_to:string}
	 */
	public static function defaults(): array {
		return array(
			'form_name' => __( 'Booking', 'lenz-plus' ),
			'fields'    => self::default_fields(),
			'notify'    => 'yes',
			'email_to'  => '',
		);
	}

	/**
	 * The booking form of the mockups: name, subject, mobile, city, a
	 * suggested time and details.
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function default_fields(): array {
		$field = static function ( string $type, string $label, bool $required, bool $wide = false, string $options = '' ): array {
			return array(
				'type'     => $type,
				'label'    => $label,
				'required' => $required ? 'yes' : '',
				'wide'     => $wide ? 'yes' : '',
				'options'  => $options,
			);
		};

		return array(
			$field( 'name', __( 'Full name', 'lenz-plus' ), true ),
			$field( 'select', __( 'Choose a subject', 'lenz-plus' ), true, false, implode( "\n", array( __( 'Professional photography', 'lenz-plus' ), __( 'Event coverage', 'lenz-plus' ), __( 'Courses', 'lenz-plus' ) ) ) ),
			$field( 'phone', __( 'Mobile number', 'lenz-plus' ), true ),
			$field( 'text', __( 'City / location', 'lenz-plus' ), false ),
			$field( 'date', __( 'Suggested date and time (optional)', 'lenz-plus' ), false, true ),
			$field( 'textarea', __( 'Details (optional)', 'lenz-plus' ), false, true ),
		);
	}

	/**
	 * Choices of a select field (one per line).
	 *
	 * @param string $options Options setting.
	 * @return string[]
	 */
	public static function option_list( string $options ): array {
		return array_values( array_filter( array_map( 'trim', preg_split( '/\R/u', $options ) ), 'strlen' ) );
	}

	/** Number of stored requests. */
	public static function count(): int {
		$counts = wp_count_posts( self::POST_TYPE );

		return isset( $counts->private ) ? (int) $counts->private : 0;
	}

	/** Admin list of the requests. */
	public static function inbox_url(): string {
		return admin_url( 'edit.php?post_type=' . self::POST_TYPE );
	}

	/** Nonce-protected download link for the admin. */
	public static function export_url(): string {
		return self::admin_action_url( self::EXPORT_ACTION );
	}

	/** A request from the widget's form. */
	public function handle(): void {
		$this->start();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- public form on cached pages; see Public_Form.
		$document = isset( $_POST['document'] ) ? absint( $_POST['document'] ) : 0;
		$form     = isset( $_POST['form'] ) ? sanitize_key( wp_unslash( $_POST['form'] ) ) : '';
		$source   = isset( $_POST['source'] ) ? absint( $_POST['source'] ) : 0;
		$posted   = isset( $_POST['f'] ) && is_array( $_POST['f'] ) ? map_deep( wp_unslash( $_POST['f'] ), 'strval' ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- every value is sanitized by its field type in clean().
		// phpcs:enable

		$settings = self::form_settings( $document, $form );
		if ( null === $settings ) {
			$this->respond( 'error' );
		}

		$answers = self::clean( $posted, $settings['fields'] );
		$invalid = self::invalid_fields( $answers );
		if ( $invalid ) {
			$this->respond( 'invalid', array( 'fields' => $invalid ) );
		}

		if ( ! $this->within_rate_limit( 'request', self::RATE_LIMIT, self::RATE_WINDOW ) ) {
			$this->respond( 'busy' );
		}

		$source  = $source && get_post( $source ) ? $source : 0;
		$request = self::summarize( $answers, (string) $settings['form_name'] );
		$post_id = wp_insert_post(
			array(
				'post_type'    => self::POST_TYPE,
				'post_status'  => 'private',
				'post_title'   => $request['name'],
				'post_content' => $request['message'],
				'meta_input'   => array(
					self::META_FORM   => (string) $settings['form_name'],
					self::META_FIELDS => $request['pairs'],
					self::META_EMAIL  => $request['email'],
					self::META_PHONE  => $request['phone'],
					self::META_SOURCE => $source,
				),
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			$this->respond( 'error' );
		}

		if ( 'yes' === $settings['notify'] ) {
			self::notify( $request, (string) $settings['form_name'], $source, (string) $settings['email_to'] );
		}

		$this->respond( 'ok' );
	}

	/**
	 * Options of a saved request form, with defaults for what Elementor did
	 * not save, or null when the request does not point at one.
	 *
	 * @param int    $document_id Elementor document holding the widget.
	 * @param string $element_id  Widget element ID.
	 */
	public static function form_settings( int $document_id, string $element_id ): ?array {
		$saved = self::saved_widget( $document_id, $element_id, self::WIDGET );
		if ( null === $saved ) {
			return null;
		}

		$settings = array_merge( self::defaults(), array_intersect_key( $saved, self::defaults() ) );
		if ( ! is_array( $settings['fields'] ) || ! $settings['fields'] ) {
			$settings['fields'] = self::default_fields();
		}

		return $settings;
	}

	/**
	 * Each configured field with the visitor's sanitized answer. Fields are
	 * posted by position (`f[0]`, `f[1]`…), as the widget prints them.
	 *
	 * @param array $posted Raw posted values.
	 * @param array $fields Field settings.
	 * @return array<int, array{type:string, label:string, required:bool, value:string}>
	 */
	private static function clean( array $posted, array $fields ): array {
		$answers = array();

		foreach ( array_values( $fields ) as $index => $field ) {
			$type  = in_array( $field['type'] ?? '', self::FIELD_TYPES, true ) ? $field['type'] : 'text';
			$raw   = isset( $posted[ $index ] ) && is_string( $posted[ $index ] ) ? $posted[ $index ] : '';
			$value = self::sanitize( $type, $raw );

			$answers[ $index ] = array(
				'type'     => $type,
				'label'    => (string) ( $field['label'] ?? '' ),
				'required' => 'yes' === ( $field['required'] ?? '' ),
				'value'    => mb_substr( $value, 0, self::MAX_LENGTH[ $type ] ?? self::MAX_DEFAULT ),
			);
		}

		return $answers;
	}

	/**
	 * One answer, cleaned for its field type.
	 *
	 * @param string $type Field type.
	 * @param string $raw  Posted value.
	 */
	private static function sanitize( string $type, string $raw ): string {
		switch ( $type ) {
			case 'email':
				return sanitize_email( $raw );
			case 'phone':
				// Visitors type numbers with Persian digits; keep digits, + and the usual separators.
				return trim( (string) preg_replace( '/[^\d+()\s-]/', '', Persian::latin_digits( sanitize_text_field( $raw ) ) ) );
			case 'textarea':
				return trim( sanitize_textarea_field( $raw ) );
			default:
				// A chosen option is kept as sent, not matched against the list: a cached page can
				// show options edited since, and a valid choice would be rejected.
				return trim( sanitize_text_field( $raw ) );
		}
	}

	/**
	 * Positions of the fields that need fixing.
	 *
	 * @param array $answers Cleaned answers.
	 * @return int[]
	 */
	private static function invalid_fields( array $answers ): array {
		$invalid = array();

		foreach ( $answers as $index => $answer ) {
			$value = $answer['value'];

			if ( '' === $value ) {
				if ( $answer['required'] ) {
					$invalid[] = $index;
				}
				continue;
			}

			if ( ( 'email' === $answer['type'] && ! is_email( $value ) )
				|| ( 'phone' === $answer['type'] && strlen( (string) preg_replace( '/\D/', '', $value ) ) < 7 ) ) {
				$invalid[] = $index;
			}
		}

		return $invalid;
	}

	/**
	 * The parts a request is stored and emailed as.
	 *
	 * @param array  $answers   Cleaned answers.
	 * @param string $form_name Form name (fallback title).
	 * @return array{name:string, email:string, phone:string, message:string, pairs:array}
	 */
	private static function summarize( array $answers, string $form_name ): array {
		$out  = array(
			'name'    => '',
			'email'   => '',
			'phone'   => '',
			'message' => '',
			'pairs'   => array(),
		);
		$long = array();

		foreach ( $answers as $answer ) {
			if ( '' === $answer['value'] ) {
				continue;
			}

			$out['pairs'][] = array( $answer['label'], $answer['value'] );

			if ( 'name' === $answer['type'] && '' === $out['name'] ) {
				$out['name'] = $answer['value'];
			} elseif ( in_array( $answer['type'], array( 'email', 'phone' ), true ) && '' === $out[ $answer['type'] ] ) {
				$out[ $answer['type'] ] = $answer['value'];
			} elseif ( 'textarea' === $answer['type'] ) {
				$long[] = $answer['value'];
			}
		}

		$out['message'] = implode( "\n\n", $long );
		if ( '' === $out['name'] ) {
			$out['name'] = '' !== $out['phone'] ? $out['phone'] : ( '' !== $out['email'] ? $out['email'] : $form_name );
		}

		return $out;
	}

	/**
	 * Emails a copy of the request; the sender's address is the Reply-To, so
	 * answering is one click.
	 *
	 * @param array  $request   Summarized request.
	 * @param string $form_name Form name.
	 * @param int    $source    Page the form was on.
	 * @param string $email_to  Comma-separated addresses ('' = the site's admin email).
	 */
	private static function notify( array $request, string $form_name, int $source, string $email_to ): void {
		$to = array_values( array_filter( array_map( 'trim', explode( ',', $email_to ) ), 'is_email' ) );
		if ( ! $to ) {
			$to = array( (string) get_option( 'admin_email' ) );
		}

		$site  = wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES );
		$lines = array(
			/* translators: 1: form name, 2: site name. */
			sprintf( __( 'New request from the "%1$s" form on %2$s.', 'lenz-plus' ), $form_name, $site ),
			'',
		);

		foreach ( $request['pairs'] as $pair ) {
			$lines[] = $pair[0] . ': ' . $pair[1];
		}
		if ( $source ) {
			$lines[] = __( 'Page', 'lenz-plus' ) . ': ' . get_permalink( $source );
		}
		$lines[] = '';
		$lines[] = '—';
		/* translators: %s: link to the requests in the admin. */
		$lines[] = sprintf( __( 'All requests: %s', 'lenz-plus' ), self::inbox_url() );

		$headers = array();
		if ( '' !== $request['email'] ) {
			// Names can hold characters that break a header; the address alone is enough.
			$headers[] = 'Reply-To: ' . $request['email'];
		}

		/* translators: 1: site name, 2: form name, 3: sender's name. */
		$subject = sprintf( __( '[%1$s] %2$s: %3$s', 'lenz-plus' ), $site, $form_name, $request['name'] );
		$body    = implode( "\n", $lines );

		// wp_mail() only catches PHPMailer's own exceptions. On hosts that disable
		// PHP's mail(), PHPMailer throws an Error instead (a fatal error on PHP 8),
		// and the visitor would be told a request that is already saved was not sent.
		try {
			wp_mail( $to, $subject, $body, $headers );
		} catch ( \Throwable $error ) {
			// Core's failure hook, so mail log plugins record it like any other failed email.
			do_action(
				'wp_mail_failed', // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core hook.
				new \WP_Error(
					'wp_mail_failed',
					$error->getMessage(),
					array(
						'to'          => $to,
						'subject'     => $subject,
						'message'     => $body,
						'headers'     => $headers,
						'attachments' => array(),
					)
				)
			);
		}
	}

	/**
	 * Answers of a stored request as "label: value" lines.
	 *
	 * @param int $post_id Request ID.
	 * @return string[]
	 */
	public static function answer_lines( int $post_id ): array {
		$pairs = get_post_meta( $post_id, self::META_FIELDS, true );
		$lines = array();

		foreach ( is_array( $pairs ) ? $pairs : array() as $pair ) {
			if ( is_array( $pair ) && 2 === count( $pair ) ) {
				$lines[] = $pair[0] . ': ' . $pair[1];
			}
		}

		return $lines;
	}

	/** Streams every request as a CSV file. */
	public function export(): void {
		check_admin_referer( self::EXPORT_ACTION );

		self::export_csv(
			self::POST_TYPE,
			'requests',
			array( 'date', 'form', 'name', 'phone', 'email', 'answers', 'page' ),
			static function ( \WP_Post $post ): array {
				$source = (int) get_post_meta( $post->ID, self::META_SOURCE, true );

				return array(
					$post->post_date,
					(string) get_post_meta( $post->ID, self::META_FORM, true ),
					$post->post_title,
					(string) get_post_meta( $post->ID, self::META_PHONE, true ),
					(string) get_post_meta( $post->ID, self::META_EMAIL, true ),
					implode( "\n", self::answer_lines( (int) $post->ID ) ),
					$source ? get_permalink( $source ) : '',
				);
			}
		);
	}

	/**
	 * Adds the requests to WordPress's personal data export.
	 *
	 * @param array $exporters Registered exporters.
	 */
	public function register_exporter( $exporters ) {
		$exporters['lenz-plus-requests'] = array(
			'exporter_friendly_name' => __( 'Requests (Lenz+)', 'lenz-plus' ),
			'callback'               => array( $this, 'export_personal_data' ),
		);

		return $exporters;
	}

	/**
	 * Adds the requests to WordPress's personal data erasure.
	 *
	 * @param array $erasers Registered erasers.
	 */
	public function register_eraser( $erasers ) {
		$erasers['lenz-plus-requests'] = array(
			'eraser_friendly_name' => __( 'Requests (Lenz+)', 'lenz-plus' ),
			'callback'             => array( $this, 'erase_personal_data' ),
		);

		return $erasers;
	}

	/**
	 * The requests sent with an email address, for a personal data export.
	 *
	 * @param string $email Address being exported.
	 */
	public function export_personal_data( $email ): array {
		$data = array();

		foreach ( self::find_by_email( (string) $email ) as $post ) {
			$data[] = array(
				'group_id'    => 'lenz-plus-requests',
				'group_label' => __( 'Requests', 'lenz-plus' ),
				'item_id'     => 'request-' . $post->ID,
				'data'        => array(
					array(
						'name'  => __( 'Form', 'lenz-plus' ),
						'value' => (string) get_post_meta( $post->ID, self::META_FORM, true ),
					),
					array(
						'name'  => __( 'Answers', 'lenz-plus' ),
						'value' => implode( "\n", self::answer_lines( (int) $post->ID ) ),
					),
					array(
						'name'  => __( 'Sent on', 'lenz-plus' ),
						'value' => $post->post_date,
					),
				),
			);
		}

		return array(
			'data' => $data,
			'done' => true,
		);
	}

	/**
	 * Deletes the requests sent with an email address.
	 *
	 * @param string $email Address being erased.
	 */
	public function erase_personal_data( $email ): array {
		$removed = false;

		foreach ( self::find_by_email( (string) $email ) as $post ) {
			$removed = (bool) wp_delete_post( $post->ID, true ) || $removed;
		}

		return array(
			'items_removed'  => $removed,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}

	/**
	 * Requests sent with an email address.
	 *
	 * @param string $email Address.
	 * @return \WP_Post[]
	 */
	private static function find_by_email( string $email ): array {
		$email = trim( $email );
		if ( ! is_email( $email ) ) {
			return array();
		}

		return get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => array( 'private', 'trash' ),
				'posts_per_page' => 200, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- one person's requests; a safety cap.
				'no_found_rows'  => true,
				'meta_key'       => self::META_EMAIL, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- privacy request, runs rarely.
				'meta_value'     => $email, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
	}

	/** Query argument with the result of a plain (no-JS) post. */
	protected function result_arg(): string {
		return 'lzp_request';
	}
}
