<?php
/**
 * Request form of the mockups: booking on the home page (inside an ink
 * frame), workshop registration on a course page (bordered card), or any
 * contact form. Fields are configurable (name, mobile, email, text, choice,
 * date, long text), each full width or sharing the row with the next one;
 * a note and the button close the form.
 *
 * Requests are stored in Lenz+ → Requests and emailed (Contact_Messages).
 * The form posts to admin-post.php and works without JavaScript; forms.js
 * sends it in the background and answers in place.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Contact_Messages;

defined( 'ABSPATH' ) || exit;

/**
 * The «Request form (Lenz+)» widget.
 */
final class Request_Form extends Form_Base {

	/** Elementor widget id (also Contact_Messages::WIDGET). */
	public function get_name(): string {
		return Contact_Messages::WIDGET;
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Request form', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-form-horizontal';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'booking', 'contact', 'request', 'رزرو', 'تماس', 'درخواست', 'ثبت نام' ) );
	}

	/** Content (fields, texts), sending and style controls. */
	protected function register_controls(): void {
		$defaults = Contact_Messages::defaults();

		$this->start_content_section( 'section_fields', __( 'Fields', 'lenz-plus' ) );

		$this->add_control(
			'form_name',
			array(
				'label'       => __( 'Form name', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $defaults['form_name'],
				'description' => __( 'Shown with each request in Lenz+ → Requests and in the email subject.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'fields',
			array(
				'label'       => __( 'Fields', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => self::field_repeater( Contact_Messages::FIELD_TYPES, __( 'Shown inside the empty field (and read by screen readers).', 'lenz-plus' ) ),
				'default'     => $defaults['fields'],
				'title_field' => '{{ label }}',
			)
		);

		$this->add_control(
			'note',
			array(
				'label'     => __( 'Note beside the button', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Your details are only used to arrange this session.', 'lenz-plus' ),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => __( 'Button text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Book now', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'button_icon',
			array(
				'label'   => __( 'Button icon', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'arrow-forward',
				'options' => self::icon_options(),
			)
		);

		$this->add_success_control( __( 'Your request was received. I will call you within one working day.', 'lenz-plus' ) );
		$this->end_controls_section();

		$this->start_content_section( 'section_sending', __( 'Sending', 'lenz-plus' ) );

		$this->add_sending_controls( $defaults, __( 'Requests are always kept in Lenz+ → Requests, even when email does not work on your host.', 'lenz-plus' ) );

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Form', 'lenz-plus' ) );

		$this->add_control(
			'frame',
			array(
				'label'   => __( 'Frame', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'ink',
				'options' => array(
					'ink'  => __( 'Ink frame (home page booking)', 'lenz-plus' ),
					'card' => __( 'Card with border', 'lenz-plus' ),
					'none' => __( 'None', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'field_width',
			array(
				'label'       => __( 'Smallest field width', 'lenz-plus' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min' => 140,
						'max' => 480,
					),
				),
				'description' => __( 'Short fields share a row while each can be at least this wide.', 'lenz-plus' ),
				'selectors'   => array( '{{WRAPPER}} .lzp-form__fields' => '--lzp-field-min: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_box_style( 'field', '.lzp-form__input', array( 'label' => __( 'Fields', 'lenz-plus' ) ) );
		$this->add_text_style( 'note', '.lzp-form__note', array( 'label' => __( 'Note', 'lenz-plus' ) ) );

		$this->add_control(
			'button_heading',
			array(
				'label'     => __( 'Button', 'lenz-plus' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->add_button_style( 'btn', '.lzp-form .lzp-btn' );
		$this->end_controls_section();
	}

	/** Prints the form. */
	protected function render(): void {
		$s      = $this->get_settings_for_display();
		$fields = ! empty( $s['fields'] ) ? $s['fields'] : Contact_Messages::default_fields();
		$id     = 'lzp-form-' . $this->get_id();
		$frame  = in_array( $s['frame'], array( 'ink', 'card', 'none' ), true ) ? $s['frame'] : 'ink';
		$result = Contact_Messages::plain_result( 'lzp_request', $id );

		$inputs = '';
		foreach ( array_values( $fields ) as $index => $field ) {
			$inputs .= self::field_html( $id, $index, $field, Contact_Messages::FIELD_TYPES );
		}

		$button = Button::markup(
			array(
				'text'    => (string) $s['button_text'],
				'variant' => 'primary',
				'size'    => 'lg',
				'icon'    => (string) $s['button_icon'],
			)
		);
		// Button::markup() prints a plain button; this one submits the form.
		$button = str_replace( '<button type="button"', '<button type="submit"', $button );

		$foot = '<div class="lzp-form__foot">'
			. ( '' !== (string) $s['note'] ? '<span class="lzp-form__note">' . esc_html( $s['note'] ) . '</span>' : '' )
			. $button . '</div>';

		printf(
			'<div class="lzp-form-wrap lzp-form-wrap--%1$s"><form class="lzp-form" id="%2$s" action="%3$s" method="post" data-lzp-form%4$s>%5$s<div class="lzp-form__fields">%6$s%7$s</div>%8$s</form></div>',
			esc_attr( $frame ),
			esc_attr( $id ),
			esc_url( Contact_Messages::form_url() ),
			'ok' === $result ? ' data-lzp-sent' : '',
			Contact_Messages::hidden_fields( Contact_Messages::ACTION, $this->get_id(), $id ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in hidden_fields().
			$inputs, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in field_html().
			$foot, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			self::form_result_html( $result, (string) $s['success_text'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in form_result_html().
		);
	}
}
