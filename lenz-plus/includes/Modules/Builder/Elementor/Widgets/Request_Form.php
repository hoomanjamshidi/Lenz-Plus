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
use Elementor\Repeater;
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

		$repeater = new Repeater();
		$repeater->add_control(
			'type',
			array(
				'label'   => __( 'Type', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'text',
				'options' => array(
					'name'     => __( 'Name', 'lenz-plus' ),
					'phone'    => __( 'Mobile / phone', 'lenz-plus' ),
					'email'    => __( 'Email', 'lenz-plus' ),
					'text'     => __( 'Short text', 'lenz-plus' ),
					'select'   => __( 'Choice', 'lenz-plus' ),
					'date'     => __( 'Date or time', 'lenz-plus' ),
					'textarea' => __( 'Long text', 'lenz-plus' ),
				),
			)
		);
		$repeater->add_control(
			'label',
			array(
				'label'       => __( 'Label', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Field', 'lenz-plus' ),
				'label_block' => true,
				'description' => __( 'Shown inside the empty field (and read by screen readers).', 'lenz-plus' ),
			)
		);
		$repeater->add_control(
			'options',
			array(
				'label'       => __( 'Choices (one per line)', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 5,
				'default'     => '',
				'condition'   => array( 'type' => 'select' ),
				'description' => __( 'The label is shown as the first, empty choice.', 'lenz-plus' ),
			)
		);
		$repeater->add_control(
			'required',
			array(
				'label'        => __( 'Required', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);
		$repeater->add_control(
			'wide',
			array(
				'label'        => __( 'Full width', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'fields',
			array(
				'label'       => __( 'Fields', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => $defaults['fields'],
				'title_field' => '{{{ label }}}',
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

		$this->add_control(
			'notify',
			array(
				'label'        => __( 'Email a copy of each request', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => $defaults['notify'],
				'return_value' => 'yes',
				'description'  => __( 'Requests are always kept in Lenz+ → Requests, even when email does not work on your host.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'email_to',
			array(
				'label'       => __( 'Send to', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $defaults['email_to'],
				'placeholder' => get_option( 'admin_email' ),
				'description' => __( 'One or more addresses, separated by commas. Empty: the site\'s admin email.', 'lenz-plus' ),
				'condition'   => array( 'notify' => 'yes' ),
			)
		);

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
			$inputs .= $this->field_html( $id, $index, $field );
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

	/**
	 * One field with its screen-reader label. Fields are posted by position
	 * (`f[0]`…), which is how Contact_Messages reads them.
	 *
	 * @param string $form_id Form HTML id.
	 * @param int    $index   Position.
	 * @param array  $field   Field settings.
	 */
	private function field_html( string $form_id, int $index, array $field ): string {
		$type     = in_array( $field['type'] ?? '', Contact_Messages::FIELD_TYPES, true ) ? $field['type'] : 'text';
		$label    = (string) ( $field['label'] ?? '' );
		$required = 'yes' === ( $field['required'] ?? '' );
		$field_id = $form_id . '-' . $index;

		$attrs = sprintf( ' class="lzp-form__input" id="%1$s" name="f[%2$d]"%3$s', esc_attr( $field_id ), $index, $required ? ' required' : '' );

		switch ( $type ) {
			case 'select':
				$options = '<option value="">' . esc_html( $label ) . '</option>';
				foreach ( Contact_Messages::option_list( (string) ( $field['options'] ?? '' ) ) as $option ) {
					$options .= sprintf( '<option value="%1$s">%1$s</option>', esc_attr( $option ) );
				}
				$control = '<select' . $attrs . '>' . $options . '</select>';
				break;

			case 'textarea':
				$control = '<textarea' . $attrs . ' rows="4" maxlength="5000" placeholder="' . esc_attr( $label ) . '"></textarea>';
				break;

			default:
				$extra   = array(
					'name'  => ' type="text" maxlength="100" autocomplete="name"',
					'phone' => ' type="tel" inputmode="tel" maxlength="20" autocomplete="tel"',
					'email' => ' type="email" inputmode="email" autocomplete="email"',
				);
				$control = '<input' . $attrs . ( $extra[ $type ] ?? ' type="text" maxlength="200"' ) . ' placeholder="' . esc_attr( $label ) . '">';
		}

		$wide = 'yes' === ( $field['wide'] ?? '' ) || 'textarea' === $type;

		return sprintf(
			'<p class="lzp-form__field%1$s"><label class="screen-reader-text" for="%2$s">%3$s</label>%4$s</p>',
			$wide ? ' lzp-form__field--wide' : '',
			esc_attr( $field_id ),
			esc_html( $label ),
			$control
		);
	}
}
