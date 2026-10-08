<?php
/**
 * Built-in lists behind the Sign-up widget: the newsletter of the blog
 * pages («هر ماه یک یادداشت») and the courses waitlist («خبرم کن»).
 *
 * Each sign-up is a private `lzp_subscriber` post (title = the email or the
 * mobile number) with the list it joined and an optional topic, so the
 * lists work on any site without an email or SMS service. Admins download
 * them as CSV to import elsewhere, and WordPress's privacy tools can export
 * and erase an address. Which contact the form accepts and the list name
 * come from the saved widget (Public_Form::saved_widget()).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

use LenzPlus\Core\Persian;

defined( 'ABSPATH' ) || exit;

/**
 * Post type, handler, CSV export and privacy tools of the sign-up lists.
 */
final class Newsletter extends Public_Form {

	public const POST_TYPE = 'lzp_subscriber';

	/** Form and AJAX action. */
	public const ACTION = 'lzp_subscribe';

	/** Elementor widget name of the sign-up form. */
	public const WIDGET = 'lzp-newsletter-form';

	/** Name of the list joined (newsletter, a waitlist…). */
	public const META_LIST = '_lzp_list';

	/** Topic chosen in the form, if it offers one. */
	public const META_TOPIC = '_lzp_topic';

	/** Page the sign-up came from. */
	public const META_SOURCE = '_lzp_source';

	private const EXPORT_ACTION = 'lzp_subscribers_export';

	/** Sign-ups allowed per visitor in RATE_WINDOW seconds. */
	private const RATE_LIMIT = 5;

	private const RATE_WINDOW = 900;

	private const MAX_TOPIC = 150;

	/** Registers the post type, the form handler, the export and the privacy tools. */
	public function register(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'label'           => __( 'Sign-ups', 'lenz-plus' ),
				'public'          => false,
				'show_ui'         => false,
				'show_in_rest'    => false,
				'rewrite'         => false,
				'query_var'       => false,
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
			)
		);

		$this->add_handler( self::ACTION, array( $this, 'handle' ) );

		add_action( 'admin_post_' . self::EXPORT_ACTION, array( $this, 'export' ) );
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_eraser' ) );
	}

	/**
	 * Options the handler reads from the saved widget; the widget's controls
	 * use them as their defaults.
	 *
	 * @return array{contact:string, list_name:string}
	 */
	public static function defaults(): array {
		return array(
			// email | email_phone.
			'contact'   => 'email',
			'list_name' => __( 'Newsletter', 'lenz-plus' ),
		);
	}

	/** Number of stored sign-ups. */
	public static function count(): int {
		$counts = wp_count_posts( self::POST_TYPE );

		return isset( $counts->private ) ? (int) $counts->private : 0;
	}

	/** Nonce-protected download link for the admin. */
	public static function export_url(): string {
		return self::admin_action_url( self::EXPORT_ACTION );
	}

	/** Sign-up from the widget's form. */
	public function handle(): void {
		$this->start();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- public form on cached pages; see Public_Form.
		$document = isset( $_POST['document'] ) ? absint( $_POST['document'] ) : 0;
		$form     = isset( $_POST['form'] ) ? sanitize_key( wp_unslash( $_POST['form'] ) ) : '';
		$source   = isset( $_POST['source'] ) ? absint( $_POST['source'] ) : 0;
		$contact  = isset( $_POST['contact'] ) ? sanitize_text_field( wp_unslash( $_POST['contact'] ) ) : '';
		$topic    = isset( $_POST['topic'] ) ? sanitize_text_field( wp_unslash( $_POST['topic'] ) ) : '';
		// phpcs:enable

		$settings = self::form_settings( $document, $form );
		if ( null === $settings ) {
			$this->respond( 'error' );
		}

		$contact = self::normalize_contact( $contact, 'email_phone' === $settings['contact'] );
		if ( '' === $contact ) {
			$this->respond( 'invalid', array( 'fields' => array( 'contact' ) ) );
		}

		if ( ! $this->within_rate_limit( 'signup', self::RATE_LIMIT, self::RATE_WINDOW ) ) {
			$this->respond( 'busy' );
		}

		$list = (string) $settings['list_name'];
		if ( ! self::find( $contact, $list ) ) {
			$meta = array(
				self::META_LIST  => $list,
				self::META_TOPIC => mb_substr( trim( $topic ), 0, self::MAX_TOPIC ),
			);
			if ( $source && get_post( $source ) ) {
				$meta[ self::META_SOURCE ] = $source;
			}

			$saved_id = wp_insert_post(
				array(
					'post_type'   => self::POST_TYPE,
					'post_status' => 'private',
					'post_title'  => $contact,
					'meta_input'  => $meta,
				),
				true
			);

			if ( is_wp_error( $saved_id ) ) {
				$this->respond( 'error' );
			}
		}

		// Someone already on the list gets the same answer: the visitor learns nothing about others.
		$this->respond( 'ok' );
	}

	/**
	 * A valid email (lower case) or, when allowed, a mobile number (Latin
	 * digits, at least seven); '' otherwise.
	 *
	 * @param string $contact     Posted value.
	 * @param bool   $allow_phone Whether a phone number is accepted.
	 */
	private static function normalize_contact( string $contact, bool $allow_phone ): string {
		$contact = trim( $contact );

		if ( is_email( $contact ) ) {
			return strtolower( $contact );
		}

		if ( $allow_phone ) {
			$digits = (string) preg_replace( '/[^\d+]/', '', Persian::latin_digits( $contact ) );
			if ( strlen( (string) preg_replace( '/\D/', '', $digits ) ) >= 7 ) {
				return $digits;
			}
		}

		return '';
	}

	/**
	 * Options of the saved form a sign-up comes from: a Sign-up widget, or
	 * the waitlist form of a coming-soon card in a Course grid (email or
	 * mobile, the grid's waitlist). Null when the request points at neither.
	 *
	 * @param int    $document_id Elementor document holding the widget.
	 * @param string $element_id  Widget element ID.
	 * @return array{contact:string, list_name:string}|null
	 */
	private static function form_settings( int $document_id, string $element_id ): ?array {
		$saved = self::saved_widget( $document_id, $element_id, self::WIDGET );
		if ( null !== $saved ) {
			return array_merge( self::defaults(), array_intersect_key( $saved, self::defaults() ) );
		}

		$grid = self::saved_widget( $document_id, $element_id, 'lzp-course-grid' );
		if ( null === $grid ) {
			return null;
		}

		$list = isset( $grid['waitlist_list'] ) && '' !== $grid['waitlist_list'] ? (string) $grid['waitlist_list'] : __( 'Course waitlist', 'lenz-plus' );

		return array(
			'contact'   => 'email_phone',
			'list_name' => $list,
		);
	}

	/** Streams every sign-up as a CSV file. */
	public function export(): void {
		check_admin_referer( self::EXPORT_ACTION );

		self::export_csv(
			self::POST_TYPE,
			'sign-ups',
			array( 'contact', 'list', 'topic', 'date', 'page' ),
			static function ( \WP_Post $post ): array {
				$source = (int) get_post_meta( $post->ID, self::META_SOURCE, true );

				return array(
					$post->post_title,
					(string) get_post_meta( $post->ID, self::META_LIST, true ),
					(string) get_post_meta( $post->ID, self::META_TOPIC, true ),
					$post->post_date,
					$source ? get_permalink( $source ) : '',
				);
			}
		);
	}

	/**
	 * Adds the sign-ups to WordPress's personal data export.
	 *
	 * @param array $exporters Registered exporters.
	 */
	public function register_exporter( $exporters ) {
		$exporters['lenz-plus-signups'] = array(
			'exporter_friendly_name' => __( 'Newsletter and waitlists (Lenz+)', 'lenz-plus' ),
			'callback'               => array( $this, 'export_personal_data' ),
		);

		return $exporters;
	}

	/**
	 * Adds the sign-ups to WordPress's personal data erasure.
	 *
	 * @param array $erasers Registered erasers.
	 */
	public function register_eraser( $erasers ) {
		$erasers['lenz-plus-signups'] = array(
			'eraser_friendly_name' => __( 'Newsletter and waitlists (Lenz+)', 'lenz-plus' ),
			'callback'             => array( $this, 'erase_personal_data' ),
		);

		return $erasers;
	}

	/**
	 * Every list an address joined, for a personal data export.
	 *
	 * @param string $email Address being exported.
	 */
	public function export_personal_data( $email ): array {
		$data = array();

		foreach ( self::find_all( (string) $email ) as $post ) {
			$data[] = array(
				'group_id'    => 'lenz-plus-signups',
				'group_label' => __( 'Newsletter and waitlists', 'lenz-plus' ),
				'item_id'     => 'subscriber-' . $post->ID,
				'data'        => array(
					array(
						'name'  => __( 'Email', 'lenz-plus' ),
						'value' => $post->post_title,
					),
					array(
						'name'  => __( 'List', 'lenz-plus' ),
						'value' => (string) get_post_meta( $post->ID, self::META_LIST, true ),
					),
					array(
						'name'  => __( 'Subscribed on', 'lenz-plus' ),
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
	 * Removes an address from every list.
	 *
	 * @param string $email Address being erased.
	 */
	public function erase_personal_data( $email ): array {
		$removed = false;

		foreach ( self::find_all( (string) $email ) as $post ) {
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
	 * The sign-up of a contact on one list.
	 *
	 * @param string $contact   Normalized email or number.
	 * @param string $list_name List name.
	 */
	private static function find( string $contact, string $list_name ): ?\WP_Post {
		foreach ( self::find_all( $contact ) as $post ) {
			if ( (string) get_post_meta( $post->ID, self::META_LIST, true ) === $list_name ) {
				return $post;
			}
		}

		return null;
	}

	/**
	 * Every sign-up of a contact.
	 *
	 * @param string $contact Email or number.
	 * @return \WP_Post[]
	 */
	private static function find_all( string $contact ): array {
		$contact = strtolower( trim( $contact ) );
		if ( '' === $contact ) {
			return array();
		}

		return get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'private',
				'title'          => $contact,
				'posts_per_page' => 50,
				'no_found_rows'  => true,
			)
		);
	}

	/** Query argument with the sign-up result. */
	protected function result_arg(): string {
		return 'lzp_signup';
	}
}
