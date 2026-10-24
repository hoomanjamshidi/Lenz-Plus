<?php
/**
 * The end of the mockups' header row: an ink call-to-action («رزرو وقت» with
 * a calendar icon), the account button (like Lenz's header: a user icon, plus
 * the member's name once logged in) and a square icon button (the phone).
 *
 * All follow the site's own settings when left empty: the button takes
 * Lenz's header "Reserve" link and text, the account button Lenz's account
 * links (see Site::account_url()), and the icon button calls the site's
 * phone (see phone_url()).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Core\Site;
use LenzPlus\Core\Theme_Bridge;
use LenzPlus\Modules\Support_Button\Channels;
use LenzPlus\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * The «Header buttons (Lenz+)» widget.
 */
final class Header_Action extends Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-header-action';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Header buttons', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-button';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'button', 'booking', 'phone', 'account', 'رزرو', 'تماس', 'حساب کاربری' ) );
	}

	/** Button and icon button controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_cta', __( 'Button', 'lenz-plus' ) );

		$this->add_control(
			'show_cta',
			array(
				'label'   => __( 'Show the button', 'lenz-plus' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'cta_text',
			array(
				'label'       => __( 'Text', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => self::reserve_text(),
				'description' => __( 'Empty: the text of Lenz\'s header "Reserve" button.', 'lenz-plus' ),
				'condition'   => array( 'show_cta' => 'yes' ),
			)
		);

		$this->add_control(
			'cta_link',
			array(
				'label'       => __( 'Link', 'lenz-plus' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => array( 'active' => true ),
				'description' => __( 'Empty: the link of Lenz\'s header "Reserve" button.', 'lenz-plus' ),
				'condition'   => array( 'show_cta' => 'yes' ),
			)
		);

		$this->add_control(
			'cta_icon',
			array(
				'label'     => __( 'Icon', 'lenz-plus' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'calendar',
				'options'   => self::icon_options(),
				'condition' => array( 'show_cta' => 'yes' ),
			)
		);

		$this->end_controls_section();

		$this->register_account_controls();

		$this->start_content_section( 'section_icon', __( 'Icon button', 'lenz-plus' ) );

		$this->add_control(
			'show_icon',
			array(
				'label'   => __( 'Show the icon button', 'lenz-plus' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'icon_key',
			array(
				'label'     => __( 'Icon', 'lenz-plus' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'phone',
				'options'   => self::icon_options( false ),
				'condition' => array( 'show_icon' => 'yes' ),
			)
		);

		$this->add_control(
			'icon_link',
			array(
				'label'       => __( 'Link', 'lenz-plus' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => array( 'active' => true ),
				'description' => __( 'Empty: a call to the number in Lenz\'s mobile-menu support box, the support button\'s phone or the first phone in Lenz\'s footer. Hidden when there is none.', 'lenz-plus' ),
				'condition'   => array( 'show_icon' => 'yes' ),
			)
		);

		$this->add_control(
			'icon_label',
			array(
				'label'       => __( 'Label for screen readers', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => __( 'Call us', 'lenz-plus' ),
				'condition'   => array( 'show_icon' => 'yes' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Buttons', 'lenz-plus' ) );
		$this->add_button_style( 'cta', '.lzp-btn' );
		$this->end_controls_section();
	}

	/** Account button controls. */
	private function register_account_controls(): void {
		$this->start_content_section( 'section_account', __( 'Account button', 'lenz-plus' ) );

		$this->add_control(
			'show_account',
			array(
				'label'   => __( 'Show the account button', 'lenz-plus' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'account_name',
			array(
				'label'       => __( 'Show the user\'s name', 'lenz-plus' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => __( 'Logged-in visitors only, and not on phones. Guests see the icon.', 'lenz-plus' ),
				'condition'   => array( 'show_account' => 'yes' ),
			)
		);

		$this->add_control(
			'account_icon',
			array(
				'label'     => __( 'Icon', 'lenz-plus' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'user',
				'options'   => self::icon_options( false ),
				'condition' => array( 'show_account' => 'yes' ),
			)
		);

		$this->add_control(
			'account_link',
			array(
				'label'       => __( 'Link', 'lenz-plus' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => array( 'active' => true ),
				'description' => __( 'Empty: Lenz\'s header account links (members and guests), else the WooCommerce account page.', 'lenz-plus' ),
				'condition'   => array( 'show_account' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	/** Prints the buttons. */
	protected function render(): void {
		$s    = $this->get_settings_for_display();
		$html = '';

		if ( 'yes' === $s['show_cta'] ) {
			$html .= $this->cta_html( $s );
		}

		if ( 'yes' === $s['show_account'] ) {
			$html .= self::account_html( $s );
		}

		if ( 'yes' === $s['show_icon'] ) {
			$url = ! empty( $s['icon_link']['url'] ) ? (string) $s['icon_link']['url'] : self::phone_url();
			if ( '' !== $url ) {
				$html .= sprintf(
					'<a class="lzp-icon-btn" href="%1$s" aria-label="%2$s">%3$s</a>',
					esc_url( $url, array( 'http', 'https', 'tel', 'mailto' ) ),
					esc_attr( '' !== (string) $s['icon_label'] ? (string) $s['icon_label'] : __( 'Call us', 'lenz-plus' ) ),
					self::icon( (string) $s['icon_key'] )
				);
			}
		}

		if ( '' === $html ) {
			$this->editor_hint( __( 'Nothing to show: set a link for the button or the icon button.', 'lenz-plus' ) );
			return;
		}

		echo '<div class="lzp-actions">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building.
	}

	/**
	 * The call-to-action, with Lenz's "Reserve" button as the default.
	 *
	 * @param array $s Settings.
	 */
	private function cta_html( array $s ): string {
		$attrs = '';
		if ( ! empty( $s['cta_link']['url'] ) ) {
			// Elementor's link attributes (href, target, rel, custom attributes) are escaped by Elementor.
			$this->add_link_attributes( 'cta', $s['cta_link'] );
			$attrs = ' ' . $this->get_render_attribute_string( 'cta' );
		} else {
			$url = Theme_Bridge::reserve_link()['url'];
			if ( '' === $url ) {
				return '';
			}
			$attrs = ' href="' . esc_url( $url ) . '"';
		}

		return Button::markup(
			array(
				'text'    => '' !== (string) $s['cta_text'] ? (string) $s['cta_text'] : self::reserve_text(),
				'variant' => 'primary',
				'size'    => 'sm',
				'icon'    => (string) $s['cta_icon'],
				'attrs'   => $attrs,
			)
		);
	}

	/**
	 * The account button: the icon alone for guests (named «ورود» for screen
	 * readers), the icon and the member's display name once logged in.
	 *
	 * @param array $s Settings.
	 */
	private static function account_html( array $s ): string {
		$logged_in = is_user_logged_in();
		$url       = ! empty( $s['account_link']['url'] ) ? (string) $s['account_link']['url'] : Site::account_url( $logged_in );
		$name      = $logged_in && 'yes' === $s['account_name'] ? wp_get_current_user()->display_name : '';
		$label     = $logged_in ? __( 'My account', 'lenz-plus' ) : __( 'Login', 'lenz-plus' );

		return sprintf(
			'<a class="lzp-icon-btn lzp-account-btn" href="%1$s"%2$s>%3$s%4$s</a>',
			esc_url( $url ),
			'' === $name ? ' aria-label="' . esc_attr( $label ) . '"' : ' title="' . esc_attr( $label ) . '"',
			self::icon( (string) $s['account_icon'] ),
			'' === $name ? '' : '<span class="lzp-account-btn__name">' . esc_html( $name ) . '</span>'
		);
	}

	/** Lenz's "Reserve" button text, else the mockups' «رزرو وقت». */
	private static function reserve_text(): string {
		$text = Theme_Bridge::reserve_link()['text'];

		return '' !== $text ? $text : __( 'Book a session', 'lenz-plus' );
	}

	/**
	 * A tel: link to the site's phone: Lenz's mobile-menu support number, else
	 * the support button's phone channel, else the first branch phone in
	 * Lenz's footer ('' when none is set).
	 */
	public static function phone_url(): string {
		$number = Theme_Bridge::support_phone();

		if ( '' === $number ) {
			$support = Plugin::instance()->module( 'support_button' );
			$number  = $support ? trim( (string) ( $support->settings()['channels']['phone']['value'] ?? '' ) ) : '';
		}

		if ( '' === $number ) {
			$branches = Theme_Bridge::footer_addresses();
			$number   = $branches && ! is_email( $branches[0]['phone'] ) ? $branches[0]['phone'] : '';
		}

		return '' !== $number ? Channels::url( 'phone', array( 'value' => $number ) ) : '';
	}
}
