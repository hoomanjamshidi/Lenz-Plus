<?php
/**
 * Project bookings sent through the «Project booking» widget, kept as
 * private `lzp_booking` posts and worked through in Lenz+ → Bookings.
 *
 * A booking holds the chosen service, the answers of the widget's two field
 * groups (project details, then contact details, posted by position like
 * the request form), a tracking code shown to the visitor (`BK-0042`) and a
 * status the studio moves along: new → confirmed → done, or cancelled.
 * The studio gets an email; the visitor gets a confirmation when they gave
 * an address and the widget asks for it.
 *
 * Like every Lenz+ form it works on cached pages: no nonce, a honeypot, a
 * per-visitor rate limit, and options read from the saved widget only.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

use LenzPlus\Admin\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Post type, form handler, statuses, emails and CSV export of the bookings.
 */
final class Booking_Requests extends Public_Form {

	public const POST_TYPE = 'lzp_booking';

	/** Form action (admin-post.php / admin-ajax.php). */
	public const ACTION = 'lzp_booking';

	/** Elementor name of the widget that sends bookings. */
	public const WIDGET = 'lzp-booking-form';

	public const META_FORM    = '_lzp_form';
	public const META_SERVICE = '_lzp_service';
	public const META_FIELDS  = '_lzp_fields';
	public const META_EMAIL   = '_lzp_email';
	public const META_PHONE   = '_lzp_phone';
	public const META_SOURCE  = '_lzp_source';
	public const META_STATUS  = '_lzp_status';

	/** Booking statuses, in the order the studio moves them. */
	public const STATUSES = array( 'new', 'confirmed', 'done', 'cancelled' );

	/** Query argument carrying the tracking code back to a plain (no-JS) form. */
	public const CODE_ARG = 'lzp_code';

	/** Admin action changing a booking's status. */
	public const STATUS_ACTION = 'lzp_booking_status';

	private const EXPORT_ACTION = 'lzp_bookings_export';

	/** Bookings per visitor per window: a booking takes a minute to fill in. */
	private const RATE_LIMIT = 3;

	private const RATE_WINDOW = 600;

	/** Registers the post type, the form handler, status changes and the export. */
	public function register(): void {
		$cap = Admin::CAPABILITY;

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'               => __( 'Bookings', 'lenz-plus' ),
					'singular_name'      => __( 'Booking', 'lenz-plus' ),
					'menu_name'          => __( 'Bookings', 'lenz-plus' ),
					'all_items'          => __( 'Bookings', 'lenz-plus' ),
					'search_items'       => __( 'Search bookings', 'lenz-plus' ),
					'not_found'          => __( 'No bookings yet. Bookings sent through the Project booking widget appear here.', 'lenz-plus' ),
					'not_found_in_trash' => __( 'No bookings in the trash.', 'lenz-plus' ),
				),
				'public'              => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				// Booking_Inbox adds the menu item itself, after the modules.
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'show_in_rest'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title' ),
				// Only administrators read bookings, and nobody writes one in the admin.
				'capability_type'     => array( 'lzp_booking', 'lzp_bookings' ),
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

		add_action( 'admin_post_' . self::STATUS_ACTION, array( $this, 'change_status' ) );
		add_action( 'admin_post_' . self::EXPORT_ACTION, array( $this, 'export' ) );
	}

	/**
	 * Status → label.
	 *
	 * @return array<string, string>
	 */
	public static function status_labels(): array {
		return array(
			'new'       => __( 'New', 'lenz-plus' ),
			'confirmed' => __( 'Confirmed', 'lenz-plus' ),
			'done'      => __( 'Done', 'lenz-plus' ),
			'cancelled' => __( 'Cancelled', 'lenz-plus' ),
		);
	}

	/**
	 * Options the handler reads from the saved widget; the widget's controls
	 * use them as their defaults (Elementor does not save untouched defaults).
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults(): array {
		return array(
			'form_name'      => __( 'Project booking', 'lenz-plus' ),
			'services'       => self::default_services(),
			'details_fields' => self::default_details(),
			'contact_fields' => self::default_contact(),
			'notify'         => 'yes',
			'email_to'       => '',
			'confirm'        => 'yes',
		);
	}

	/**
	 * Project types to choose from in the first step.
	 *
	 * @return array<int, array{icon:string, title:string, text:string}>
	 */
	public static function default_services(): array {
		$service = static function ( string $icon, string $title, string $text ): array {
			return array(
				'icon'  => $icon,
				'title' => $title,
				'text'  => $text,
			);
		};

		return array(
			$service( 'camera', __( 'Portrait and studio', 'lenz-plus' ), __( 'Personal, family or professional portraits.', 'lenz-plus' ) ),
			$service( 'store', __( 'Product and advertising', 'lenz-plus' ), __( 'Product photos for a shop, catalogue or campaign.', 'lenz-plus' ) ),
			$service( 'video', __( 'Video and teaser', 'lenz-plus' ), __( 'Teasers, reels and brand films.', 'lenz-plus' ) ),
			$service( 'users', __( 'Event coverage', 'lenz-plus' ), __( 'Ceremonies, conferences and launches.', 'lenz-plus' ) ),
		);
	}

	/**
	 * Fields of the project details step.
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function default_details(): array {
		return array(
			self::field( 'date', __( 'Preferred date', 'lenz-plus' ), true ),
			self::field( 'text', __( 'City / location', 'lenz-plus' ), true ),
			self::field( 'choice', __( 'How long', 'lenz-plus' ), false, implode( "\n", array( __( 'One or two hours', 'lenz-plus' ), __( 'Half a day', 'lenz-plus' ), __( 'A whole day', 'lenz-plus' ), __( 'Several days', 'lenz-plus' ) ) ) ),
			self::field( 'choice', __( 'Budget', 'lenz-plus' ), false, implode( "\n", array( __( 'Under 10 million tomans', 'lenz-plus' ), __( '10 to 30 million', 'lenz-plus' ), __( 'Over 30 million', 'lenz-plus' ), __( 'Not sure yet', 'lenz-plus' ) ) ) ),
			self::field( 'textarea', __( 'Tell me about the project', 'lenz-plus' ), false ),
		);
	}

	/**
	 * Fields of the contact details step.
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function default_contact(): array {
		return array(
			self::field( 'name', __( 'Full name', 'lenz-plus' ), true ),
			self::field( 'phone', __( 'Mobile number', 'lenz-plus' ), true ),
			self::field( 'email', __( 'Email', 'lenz-plus' ), false ),
			self::field( 'choice', __( 'Best way to reach you', 'lenz-plus' ), false, implode( "\n", array( __( 'Phone call', 'lenz-plus' ), __( 'WhatsApp', 'lenz-plus' ), __( 'Telegram', 'lenz-plus' ), __( 'Email', 'lenz-plus' ) ) ) ),
		);
	}

	/**
	 * A field row.
	 *
	 * @param string $type     Field type.
	 * @param string $label    Label.
	 * @param bool   $required Required.
	 * @param string $options  Choices, one per line.
	 */
	private static function field( string $type, string $label, bool $required, string $options = '' ): array {
		return array(
			'type'     => $type,
			'label'    => $label,
			'required' => $required ? 'yes' : '',
			'wide'     => '',
			'options'  => $options,
		);
	}

	/** Number of stored bookings with a status ('' for all). */
	public static function count( string $status = '' ): int {
		if ( '' === $status ) {
			$counts = wp_count_posts( self::POST_TYPE );

			return isset( $counts->private ) ? (int) $counts->private : 0;
		}

		return count(
			get_posts(
				array(
					'post_type'      => self::POST_TYPE,
					'post_status'    => 'private',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'no_found_rows'  => true,
					'meta_key'       => self::META_STATUS, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- admin counts only.
					'meta_value'     => $status, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			)
		);
	}

	/** Admin list of the bookings. */
	public static function inbox_url(): string {
		return admin_url( 'edit.php?post_type=' . self::POST_TYPE );
	}

	/** Nonce-protected download link for the admin. */
	public static function export_url(): string {
		return self::admin_action_url( self::EXPORT_ACTION );
	}

	/**
	 * Nonce-protected link moving a booking to a status.
	 *
	 * @param int    $post_id Booking ID.
	 * @param string $status  New status.
	 */
	public static function status_url( int $post_id, string $status ): string {
		return wp_nonce_url(
			admin_url( 'admin-post.php?action=' . self::STATUS_ACTION . '&post=' . $post_id . '&status=' . $status ),
			self::STATUS_ACTION . '_' . $post_id
		);
	}

	/**
	 * A booking's status ('new' for anything unknown).
	 *
	 * @param int $post_id Booking ID.
	 */
	public static function status_of( int $post_id ): string {
		$status = (string) get_post_meta( $post_id, self::META_STATUS, true );

		return in_array( $status, self::STATUSES, true ) ? $status : 'new';
	}

	/**
	 * Tracking code shown to the visitor and in the admin.
	 *
	 * @param int $post_id Booking ID.
	 */
	public static function code_for( int $post_id ): string {
		return 'BK-' . str_pad( (string) $post_id, 4, '0', STR_PAD_LEFT );
	}

	/**
	 * Tracking code of a plain (no-JS) post, read back from the URL.
	 */
	public static function plain_code(): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only: the result of a plain form post.
		$code = isset( $_GET[ self::CODE_ARG ] ) ? sanitize_text_field( wp_unslash( $_GET[ self::CODE_ARG ] ) ) : '';

		return preg_match( '/^BK-\d+$/', $code ) ? $code : '';
	}

	/** A booking from the widget's form. */
	public function handle(): void {
		$this->start();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- public form on cached pages; see Public_Form.
		$document = isset( $_POST['document'] ) ? absint( $_POST['document'] ) : 0;
		$form     = isset( $_POST['form'] ) ? sanitize_key( wp_unslash( $_POST['form'] ) ) : '';
		$source   = isset( $_POST['source'] ) ? absint( $_POST['source'] ) : 0;
		$service  = isset( $_POST['service'] ) ? mb_substr( sanitize_text_field( wp_unslash( $_POST['service'] ) ), 0, 120 ) : '';
		$posted   = isset( $_POST['f'] ) && is_array( $_POST['f'] ) ? map_deep( wp_unslash( $_POST['f'] ), 'strval' ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- every value is sanitized by its field type in Form_Fields::clean().
		// phpcs:enable

		$settings = self::form_settings( $document, $form );
		if ( null === $settings ) {
			$this->respond( 'error' );
		}

		$details = Form_Fields::clean( $posted, $settings['details_fields'], Form_Fields::TYPES );
		$contact = Form_Fields::clean( $posted, $settings['contact_fields'], Form_Fields::TYPES, count( $details ) );
		$answers = $details + $contact;
		$invalid = Form_Fields::invalid( $answers );

		if ( '' === $service && $settings['services'] ) {
			$invalid[] = 'service';
		}
		if ( $invalid ) {
			$this->respond( 'invalid', array( 'fields' => $invalid ) );
		}

		if ( ! $this->within_rate_limit( 'booking', self::RATE_LIMIT, self::RATE_WINDOW ) ) {
			$this->respond( 'busy' );
		}

		$booking = array(
			'form'    => (string) $settings['form_name'],
			'service' => $service,
			'name'    => Form_Fields::first_of( $answers, 'name' ),
			'email'   => Form_Fields::first_of( $answers, 'email' ),
			'phone'   => Form_Fields::first_of( $answers, 'phone' ),
			'pairs'   => Form_Fields::pairs( $answers ),
			'source'  => $source && get_post( $source ) ? $source : 0,
		);

		$post_id = wp_insert_post(
			array(
				'post_type'   => self::POST_TYPE,
				'post_status' => 'private',
				'post_title'  => '' !== $booking['name'] ? $booking['name'] : ( '' !== $booking['phone'] ? $booking['phone'] : $booking['form'] ),
				'meta_input'  => array(
					self::META_FORM    => $booking['form'],
					self::META_SERVICE => $booking['service'],
					self::META_FIELDS  => $booking['pairs'],
					self::META_EMAIL   => $booking['email'],
					self::META_PHONE   => $booking['phone'],
					self::META_SOURCE  => $booking['source'],
					self::META_STATUS  => 'new',
				),
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			$this->respond( 'error' );
		}

		$booking['code'] = self::code_for( (int) $post_id );

		if ( 'yes' === $settings['notify'] ) {
			self::notify_studio( $booking, (string) $settings['email_to'] );
		}
		if ( 'yes' === $settings['confirm'] && '' !== $booking['email'] ) {
			self::confirm_to_visitor( $booking );
		}

		$this->respond( 'ok', array( 'code' => $booking['code'] ), array( self::CODE_ARG => $booking['code'] ) );
	}

	/**
	 * Options of a saved booking widget, with defaults for what Elementor
	 * did not save, or null when the request does not point at one.
	 *
	 * @param int    $document_id Elementor document holding the widget.
	 * @param string $element_id  Widget element ID.
	 */
	public static function form_settings( int $document_id, string $element_id ): ?array {
		$saved = self::saved_widget( $document_id, $element_id, self::WIDGET );
		if ( null === $saved ) {
			return null;
		}

		return self::with_defaults( $saved );
	}

	/**
	 * Saved or displayed settings completed with the defaults. A repeater
	 * left empty in the editor falls back to its defaults, except the
	 * services, which a form may do without.
	 *
	 * @param array $settings Widget settings.
	 */
	public static function with_defaults( array $settings ): array {
		$defaults = self::defaults();
		$settings = array_merge( $defaults, array_intersect_key( $settings, $defaults ) );

		foreach ( array( 'details_fields', 'contact_fields' ) as $group ) {
			if ( ! is_array( $settings[ $group ] ) || ! $settings[ $group ] ) {
				$settings[ $group ] = $defaults[ $group ];
			}
		}
		$settings['services'] = is_array( $settings['services'] ) ? array_values( $settings['services'] ) : array();

		return $settings;
	}

	/**
	 * The booking as plain-text lines: code, service, then the answers.
	 *
	 * @param array $booking Booking parts.
	 * @return string[]
	 */
	private static function summary_lines( array $booking ): array {
		$lines = array( __( 'Tracking code', 'lenz-plus' ) . ': ' . $booking['code'] );
		if ( '' !== $booking['service'] ) {
			$lines[] = __( 'Project', 'lenz-plus' ) . ': ' . $booking['service'];
		}

		return array_merge( $lines, Form_Fields::lines( $booking['pairs'] ) );
	}

	/**
	 * Emails the booking to the studio; the visitor's address is the Reply-To.
	 *
	 * @param array  $booking  Booking parts.
	 * @param string $email_to Comma-separated addresses ('' = the site's admin email).
	 */
	private static function notify_studio( array $booking, string $email_to ): void {
		$site  = wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES );
		$lines = array_merge(
			/* translators: 1: form name, 2: site name. */
			array( sprintf( __( 'New booking from the "%1$s" form on %2$s.', 'lenz-plus' ), $booking['form'], $site ), '' ),
			self::summary_lines( $booking )
		);

		if ( $booking['source'] ) {
			$lines[] = __( 'Page', 'lenz-plus' ) . ': ' . get_permalink( $booking['source'] );
		}
		/* translators: %s: link to the bookings in the admin. */
		$lines = array_merge( $lines, array( '', '—', sprintf( __( 'All bookings: %s', 'lenz-plus' ), self::inbox_url() ) ) );

		self::send_mail(
			self::recipients( $email_to ),
			/* translators: 1: site name, 2: tracking code, 3: visitor's name. */
			sprintf( __( '[%1$s] New booking %2$s: %3$s', 'lenz-plus' ), $site, $booking['code'], $booking['name'] ),
			implode( "\n", $lines ),
			'' !== $booking['email'] ? array( 'Reply-To: ' . $booking['email'] ) : array()
		);
	}

	/**
	 * Confirms to the visitor that the booking arrived, with its summary.
	 *
	 * @param array $booking Booking parts.
	 */
	private static function confirm_to_visitor( array $booking ): void {
		$site  = wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES );
		$lines = array_merge(
			array(
				/* translators: %s: visitor's name. */
				'' !== $booking['name'] ? sprintf( __( 'Hello %s,', 'lenz-plus' ), $booking['name'] ) : __( 'Hello,', 'lenz-plus' ),
				'',
				__( 'Your booking request has arrived. I will review the details and contact you soon to confirm the date and the price.', 'lenz-plus' ),
				'',
			),
			self::summary_lines( $booking ),
			array( '', $site, home_url( '/' ) )
		);

		self::send_mail(
			array( $booking['email'] ),
			/* translators: 1: site name, 2: tracking code. */
			sprintf( __( '[%1$s] Your booking request %2$s', 'lenz-plus' ), $site, $booking['code'] ),
			implode( "\n", $lines )
		);
	}

	/** Moves a booking to another status from the admin list. */
	public function change_status(): void {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		$status  = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';

		check_admin_referer( self::STATUS_ACTION . '_' . $post_id );
		if ( ! current_user_can( Admin::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to change these settings.', 'lenz-plus' ), 403 );
		}

		if ( self::POST_TYPE === get_post_type( $post_id ) && in_array( $status, self::STATUSES, true ) ) {
			update_post_meta( $post_id, self::META_STATUS, $status );
		}

		$back = wp_get_referer();
		wp_safe_redirect( $back ? $back : self::inbox_url() );
		exit;
	}

	/**
	 * Answers of a stored booking as "label: value" lines.
	 *
	 * @param int $post_id Booking ID.
	 * @return string[]
	 */
	public static function answer_lines( int $post_id ): array {
		return Form_Fields::lines( get_post_meta( $post_id, self::META_FIELDS, true ) );
	}

	/** Streams every booking as a CSV file. */
	public function export(): void {
		check_admin_referer( self::EXPORT_ACTION );

		$labels = self::status_labels();
		self::export_csv(
			self::POST_TYPE,
			'bookings',
			array( 'code', 'date', 'status', 'project', 'name', 'phone', 'email', 'answers', 'page' ),
			static function ( \WP_Post $post ) use ( $labels ): array {
				$source = (int) get_post_meta( $post->ID, self::META_SOURCE, true );

				return array(
					self::code_for( (int) $post->ID ),
					$post->post_date,
					$labels[ self::status_of( (int) $post->ID ) ],
					(string) get_post_meta( $post->ID, self::META_SERVICE, true ),
					$post->post_title,
					(string) get_post_meta( $post->ID, self::META_PHONE, true ),
					(string) get_post_meta( $post->ID, self::META_EMAIL, true ),
					implode( "\n", self::answer_lines( (int) $post->ID ) ),
					$source ? get_permalink( $source ) : '',
				);
			}
		);
	}

	/** Query argument with the result of a plain (no-JS) post. */
	protected function result_arg(): string {
		return 'lzp_booking';
	}
}
