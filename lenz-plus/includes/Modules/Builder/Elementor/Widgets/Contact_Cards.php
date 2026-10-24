<?php
/**
 * Ways to reach the studio on the contact page: phone, email, address,
 * working hours, messengers… each with an icon, a title, the value and an
 * optional note («پاسخ‌گویی در ساعات کاری»).
 *
 * Looks: `tiles` (cards in a grid, the icon in a tinted box), `list` (rows
 * inside one panel, made for the dark surface of a split layout) and `big`
 * (large values, for a minimal page). A value with a link is a real link
 * (tel:, mailto:, a map); phone numbers, emails and Latin values keep
 * left-to-right order inside the right-to-left line.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use LenzPlus\Modules\Support_Button\Channels;

defined( 'ABSPATH' ) || exit;

/**
 * The «Contact cards (Lenz+)» widget.
 */
final class Contact_Cards extends Section_Base {

	/** Looks offered by the widget. */
	private const LOOKS = array( 'tiles', 'list', 'big' );

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-contact-cards';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Contact cards', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-call-to-action';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'contact', 'phone', 'email', 'address', 'hours', 'تماس', 'تلفن', 'ایمیل', 'آدرس', 'ساعت کاری' ) );
	}

	/** Cards, look and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Contact cards', 'lenz-plus' ) );

		$this->add_list_title_control();

		$repeater = new Repeater();
		$repeater->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'phone',
				'options' => self::icon_options( false ),
			)
		);
		$repeater->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Phone', 'lenz-plus' ),
			)
		);
		$repeater->add_control(
			'value',
			array(
				'label'       => __( 'Value', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => '',
				'description' => __( 'A number, an address, an email… One per line for several.', 'lenz-plus' ),
			)
		);
		$repeater->add_control(
			'link',
			array(
				'label'       => __( 'Link', 'lenz-plus' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => array( 'active' => true ),
				'description' => __( 'Empty: phone numbers and emails get their tel: / mailto: link by themselves.', 'lenz-plus' ),
			)
		);
		$repeater->add_control(
			'note',
			array(
				'label'   => __( 'Note', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Cards', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'icon'  => 'phone',
						'title' => __( 'Phone', 'lenz-plus' ),
						'value' => '',
					),
					array(
						'icon'  => 'mail',
						'title' => __( 'Email', 'lenz-plus' ),
						'value' => '',
					),
					array(
						'icon'  => 'map-pin',
						'title' => __( 'Address', 'lenz-plus' ),
						'value' => '',
					),
				),
				'title_field' => '{{{ title }}}',
			)
		);

		$this->add_control(
			'look',
			array(
				'label'   => __( 'Look', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'tiles',
				'options' => array(
					'tiles' => __( 'Cards', 'lenz-plus' ),
					'list'  => __( 'Rows in a panel', 'lenz-plus' ),
					'big'   => __( 'Large values', 'lenz-plus' ),
				),
			)
		);

		$this->add_columns_control( '.lzp-reach', array( 4, 2, 1 ), 4, array( 'condition' => array( 'look!' => 'list' ) ) );
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Contact cards', 'lenz-plus' ) );
		$this->add_gap_control( '.lzp-reach' );
		$this->add_box_style( 'card', '.lzp-reach__item', array( 'label' => __( 'Card', 'lenz-plus' ) ) );
		$this->add_text_style( 'title', '.lzp-reach__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'value', '.lzp-reach__value', array( 'label' => __( 'Value', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the cards. */
	protected function render(): void {
		$s     = $this->get_settings_for_display();
		$look  = in_array( $s['look'], self::LOOKS, true ) ? (string) $s['look'] : 'tiles';
		$cards = '';

		foreach ( (array) $s['items'] as $item ) {
			$cards .= self::card_html( $item );
		}

		if ( '' === $cards ) {
			$this->editor_hint( __( 'Add your phone, email or address.', 'lenz-plus' ) );
			return;
		}

		printf(
			'<div class="lzp-reach-wrap">%1$s<ul class="lzp-reach lzp-reach--%2$s%3$s">%4$s</ul></div>',
			self::list_title_html( $s ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in list_title_html().
			esc_attr( $look ),
			'list' === $look ? '' : ' lzp-grid',
			$cards // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in card_html().
		);
	}

	/**
	 * One card, '' without a value.
	 *
	 * @param array $item Repeater row.
	 */
	private static function card_html( array $item ): string {
		$lines = array_filter( array_map( 'trim', preg_split( '/\R/u', (string) $item['value'] ) ) );
		if ( ! $lines ) {
			return '';
		}

		$link   = (string) ( $item['link']['url'] ?? '' );
		$values = '';
		$single = 1 === count( $lines );
		foreach ( $lines as $line ) {
			$values .= self::value_html( $line, $single ? $link : '' );
		}

		return sprintf(
			'<li class="lzp-reach__item"><span class="lzp-reach__icon">%1$s</span><div class="lzp-reach__body">%2$s<div class="lzp-reach__values">%3$s</div>%4$s</div></li>',
			self::icon( (string) $item['icon'] ),
			'' !== (string) $item['title'] ? '<p class="lzp-reach__title">' . esc_html( $item['title'] ) . '</p>' : '',
			$values,
			'' !== (string) $item['note'] ? '<p class="lzp-reach__note">' . esc_html( $item['note'] ) . '</p>' : ''
		);
	}

	/**
	 * One value: a link when there is one (or the line is a phone or an email).
	 *
	 * @param string $text Line.
	 * @param string $url  Link of the card ('' to detect).
	 */
	private static function value_html( string $text, string $url ): string {
		if ( '' === $url ) {
			if ( is_email( $text ) ) {
				$url = 'mailto:' . $text;
			} elseif ( preg_match( '/^[+\d\x{06F0}-\x{06F9}\x{0660}-\x{0669}][\d\x{06F0}-\x{06F9}\x{0660}-\x{0669}\s\-()]{6,}$/u', $text ) ) {
				$url = Channels::url( 'phone', array( 'value' => $text ) );
			}
		}

		// Letters only: a number reads left to right either way.
		$dir = ! preg_match( '/[\x{0621}-\x{064A}\x{067E}-\x{06D3}]/u', $text ) ? ' dir="ltr"' : '';

		return '' !== $url
			? sprintf( '<a class="lzp-reach__value" href="%1$s"%2$s>%3$s</a>', esc_url( $url, array( 'http', 'https', 'tel', 'mailto', 'geo', 'sms' ) ), $dir, esc_html( $text ) )
			: sprintf( '<span class="lzp-reach__value"%1$s>%2$s</span>', $dir, esc_html( $text ) );
	}
}
