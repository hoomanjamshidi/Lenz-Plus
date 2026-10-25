<?php
/**
 * «رزرو پروژه»: a booking form that takes a project in steps — the kind of
 * project (cards), its details, the visitor's contact details, then a
 * review — and stores it in Lenz+ → Bookings with a tracking code.
 *
 * Looks:
 * - `steps`: numbered steps over a white card, one step at a time.
 * - `split`: the steps listed on an ink panel beside the form, with a live
 *   summary of the answers.
 * - `focus`: one large step at a time under a thin progress line, nothing
 *   else on screen.
 * - `plain`: every field on one page, an ordinary form.
 *
 * The markup is a complete form with one `<fieldset>` per step, so it posts
 * without JavaScript (all steps visible, the server checks every field).
 * forms.js turns it into steps (`data-lzp-booking`), checks each step before
 * moving on, fills the review and sends it in the background.
 *
 * Fields are posted by position: the details fields first, then the
 * contact fields (Booking_Requests reads them in the same order).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use LenzPlus\Modules\Builder\Booking_Requests;
use LenzPlus\Modules\Builder\Form_Fields;

defined( 'ABSPATH' ) || exit;

/**
 * The «Project booking (Lenz+)» widget.
 */
final class Booking_Form extends Form_Base {

	/** Looks offered by the widget. */
	private const LOOKS = array( 'steps', 'split', 'focus', 'plain' );

	/** Elementor widget id (also Booking_Requests::WIDGET). */
	public function get_name(): string {
		return Booking_Requests::WIDGET;
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Project booking', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-form-vertical';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'booking', 'steps', 'wizard', 'reserve', 'رزرو', 'پروژه', 'مرحله', 'نوبت' ) );
	}

	/** Content (steps, texts), sending and style controls. */
	protected function register_controls(): void {
		$defaults = Booking_Requests::defaults();

		$this->start_content_section( 'section_form', __( 'Form', 'lenz-plus' ) );

		$this->add_control(
			'look',
			array(
				'label'   => __( 'Look', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'steps',
				'options' => array(
					'steps' => __( 'Steps on a card', 'lenz-plus' ),
					'split' => __( 'Steps beside an ink panel', 'lenz-plus' ),
					'focus' => __( 'One question at a time', 'lenz-plus' ),
					'plain' => __( 'Ordinary form (one page)', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'form_name',
			array(
				'label'       => __( 'Form name', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $defaults['form_name'],
				'description' => __( 'Shown with each booking in Lenz+ → Bookings and in the email subject.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'panel_title',
			array(
				'label'     => __( 'Panel title', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Book your project', 'lenz-plus' ),
				'condition' => array( 'look' => 'split' ),
			)
		);

		$this->add_control(
			'panel_text',
			array(
				'label'     => __( 'Panel text', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXTAREA,
				'rows'      => 3,
				'default'   => __( 'Four short steps; I will call you within one working day to confirm the date and the price.', 'lenz-plus' ),
				'condition' => array( 'look' => 'split' ),
			)
		);

		$this->end_controls_section();

		$this->add_step_controls( 'services', __( 'Step 1: project', 'lenz-plus' ), __( 'What is the project?', 'lenz-plus' ), __( 'Choose the closest one; the details come next.', 'lenz-plus' ) );
		$this->add_services_control( $defaults['services'] );
		$this->end_controls_section();

		$this->add_step_controls( 'details', __( 'Step 2: details', 'lenz-plus' ), __( 'Project details', 'lenz-plus' ), __( 'The date, the place and what you have in mind.', 'lenz-plus' ) );
		$this->add_fields_control( 'details_fields', $defaults['details_fields'] );
		$this->end_controls_section();

		$this->add_step_controls( 'contact', __( 'Step 3: contact', 'lenz-plus' ), __( 'How can I reach you?', 'lenz-plus' ), __( 'Your details are only used for this booking.', 'lenz-plus' ) );
		$this->add_fields_control( 'contact_fields', $defaults['contact_fields'] );
		$this->end_controls_section();

		$this->add_step_controls( 'review', __( 'Step 4: review', 'lenz-plus' ), __( 'Review and send', 'lenz-plus' ), __( 'Check the details; you can go back and change anything.', 'lenz-plus' ) );
		$this->add_control(
			'consent',
			array(
				'label'       => __( 'Agreement checkbox', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'I agree to be contacted about this booking.', 'lenz-plus' ),
				'label_block' => true,
				'description' => __( 'Empty: no checkbox.', 'lenz-plus' ),
			)
		);
		$this->end_controls_section();

		$this->start_content_section( 'section_buttons', __( 'Buttons and messages', 'lenz-plus' ) );
		foreach ( array(
			'next_text'   => array( __( '"Next" button', 'lenz-plus' ), __( 'Next step', 'lenz-plus' ) ),
			'back_text'   => array( __( '"Back" button', 'lenz-plus' ), __( 'Back', 'lenz-plus' ) ),
			'button_text' => array( __( 'Send button', 'lenz-plus' ), __( 'Send booking request', 'lenz-plus' ) ),
		) as $key => $control ) {
			$this->add_control(
				$key,
				array(
					'label'   => $control[0],
					'type'    => Controls_Manager::TEXT,
					'default' => $control[1],
				)
			);
		}
		$this->add_success_control( __( 'Your booking request was received. I will call you within one working day to confirm the date.', 'lenz-plus' ) );
		$this->end_controls_section();

		$this->start_content_section( 'section_sending', __( 'Sending', 'lenz-plus' ) );
		$this->add_sending_controls( $defaults, __( 'Bookings are always kept in Lenz+ → Bookings, even when email does not work on your host.', 'lenz-plus' ) );
		$this->add_control(
			'confirm',
			array(
				'label'        => __( 'Email the customer a confirmation', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => $defaults['confirm'],
				'return_value' => 'yes',
				'description'  => __( 'When they give an email address: the tracking code and a summary of the booking.', 'lenz-plus' ),
			)
		);
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Form', 'lenz-plus' ) );
		$this->add_box_style( 'card', '.lzp-book__main', array( 'label' => __( 'Card', 'lenz-plus' ) ) );
		$this->add_box_style( 'field', '.lzp-form__input', array( 'label' => __( 'Fields', 'lenz-plus' ) ) );
		$this->add_text_style( 'step_title', '.lzp-book__legend', array( 'label' => __( 'Step titles', 'lenz-plus' ) ) );
		$this->add_button_style( 'btn', '.lzp-book .lzp-btn.lzp-btn--primary' );
		$this->end_controls_section();
	}

	/**
	 * Opens a step's section with its title and hint controls (`<prefix>_title`, `<prefix>_text`).
	 *
	 * @param string $prefix Step key.
	 * @param string $label  Section label.
	 * @param string $title  Default title.
	 * @param string $text   Default hint.
	 */
	private function add_step_controls( string $prefix, string $label, string $title, string $text ): void {
		$this->start_content_section( 'section_' . $prefix, $label );
		$this->add_control(
			$prefix . '_title',
			array(
				'label'       => __( 'Title', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $title,
				'label_block' => true,
			)
		);
		$this->add_control(
			$prefix . '_text',
			array(
				'label'   => __( 'Hint', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => $text,
			)
		);
	}

	/**
	 * The project types repeater.
	 *
	 * @param array $defaults Default rows.
	 */
	private function add_services_control( array $defaults ): void {
		$repeater = new Repeater();
		$repeater->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'camera',
				'options' => self::icon_options(),
			)
		);
		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Project type', 'lenz-plus' ),
				'label_block' => true,
			)
		);
		$repeater->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);

		$this->add_control(
			'services',
			array(
				'label'       => __( 'Project types', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => $defaults,
				'title_field' => '{{{ title }}}',
				'description' => __( 'Empty list: the step is skipped.', 'lenz-plus' ),
			)
		);
	}

	/**
	 * A field group repeater.
	 *
	 * @param string $name     Control name.
	 * @param array  $defaults Default rows.
	 */
	private function add_fields_control( string $name, array $defaults ): void {
		$this->add_control(
			$name,
			array(
				'label'       => __( 'Fields', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => self::field_repeater( Form_Fields::TYPES, __( 'Shown above the field.', 'lenz-plus' ) ),
				'default'     => $defaults,
				'title_field' => '{{{ label }}}',
			)
		);
	}

	/** Prints the form. */
	protected function render(): void {
		$s      = $this->get_settings_for_display();
		$config = Booking_Requests::with_defaults( $s );
		$look   = in_array( $s['look'], self::LOOKS, true ) ? (string) $s['look'] : 'steps';
		$id     = 'lzp-book-' . $this->get_id();
		$result = Booking_Requests::plain_result( 'lzp_booking', $id );

		$steps = array();
		if ( $config['services'] ) {
			$steps[] = array( 'services', $this->services_html( $config['services'] ) );
		}
		$steps[] = array( 'details', $this->fields_html( $id, $config['details_fields'], 0 ) );
		$steps[] = array( 'contact', $this->fields_html( $id, $config['contact_fields'], count( $config['details_fields'] ) ) );
		// The one-page form has nothing to review: the step stays only for the agreement checkbox.
		if ( 'plain' !== $look || '' !== (string) $s['consent'] ) {
			$steps[] = array( 'review', $this->review_html( $id, (string) $s['consent'], 'plain' === $look ) );
		}

		$fieldsets = '';
		foreach ( $steps as $index => $step ) {
			$fieldsets .= $this->step_html( $step[0], $step[1], $index, count( $steps ), $s );
		}

		$main = ( 'plain' === $look ? '' : $this->progress_html( $steps, $s, $look ) )
			. '<div class="lzp-book__steps">' . $fieldsets . '</div>'
			. $this->nav_html( $s, 'plain' === $look );

		$aside = 'split' === $look ? $this->panel_html( $s, $steps ) : '';

		printf(
			'<div class="lzp-book lzp-book--%1$s"><form class="lzp-form lzp-book__form" id="%2$s" action="%3$s" method="post" data-lzp-form%4$s%5$s>%6$s<div class="lzp-form__fields lzp-book__body">%7$s<div class="lzp-book__main">%8$s</div></div>%9$s</form></div>',
			esc_attr( $look ),
			esc_attr( $id ),
			esc_url( Booking_Requests::form_url() ),
			'plain' === $look ? '' : ' data-lzp-booking',
			'ok' === $result ? ' data-lzp-sent' : '',
			Booking_Requests::hidden_fields( Booking_Requests::ACTION, $this->get_id(), $id ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in hidden_fields().
			$aside, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building.
			$main, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building.
			$this->result_html( $result, (string) $s['success_text'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in result_html().
		);
	}

	/**
	 * One step: a fieldset with its numbered legend and hint.
	 *
	 * @param string $key   Step key.
	 * @param string $body  Step content.
	 * @param int    $index Position.
	 * @param int    $total Number of steps.
	 * @param array  $s     Settings.
	 */
	private function step_html( string $key, string $body, int $index, int $total, array $s ): string {
		$hint = (string) ( $s[ $key . '_text' ] ?? '' );

		return sprintf(
			'<fieldset class="lzp-book__step lzp-book__step--%1$s" data-lzp-step="%2$d"><legend class="lzp-book__legend" tabindex="-1"><span class="lzp-book__num" aria-hidden="true">%3$s</span><span class="lzp-book__title">%4$s</span><span class="screen-reader-text"> (%5$s)</span></legend>%6$s%7$s</fieldset>',
			esc_attr( $key ),
			$index,
			esc_html( self::num( $index + 1 ) ),
			esc_html( (string) ( $s[ $key . '_title' ] ?? '' ) ),
			/* translators: 1: step number, 2: number of steps. */
			esc_html( sprintf( __( 'step %1$s of %2$s', 'lenz-plus' ), self::num( $index + 1 ), self::num( $total ) ) ),
			'' !== $hint ? '<p class="lzp-book__hint">' . esc_html( $hint ) . '</p>' : '',
			$body
		);
	}

	/**
	 * Project type cards (radio buttons named `service`).
	 *
	 * @param array $services Service rows.
	 */
	private function services_html( array $services ): string {
		$cards = '';
		foreach ( $services as $service ) {
			$title = trim( (string) ( $service['title'] ?? '' ) );
			if ( '' === $title ) {
				continue;
			}

			$cards .= sprintf(
				'<label class="lzp-book__service"><input class="lzp-form__choice lzp-book__radio" type="radio" name="service" value="%1$s" data-label="%2$s" required><span class="lzp-book__card">%3$s<span class="lzp-book__card-title">%4$s</span>%5$s<span class="lzp-book__tick" aria-hidden="true">%6$s</span></span></label>',
				esc_attr( $title ),
				esc_attr__( 'Project', 'lenz-plus' ),
				'' !== ( $service['icon'] ?? '' ) ? self::icon( (string) $service['icon'], 'lzp-book__card-icon' ) : '',
				esc_html( $title ),
				'' !== trim( (string) ( $service['text'] ?? '' ) ) ? '<span class="lzp-book__card-text">' . esc_html( (string) $service['text'] ) . '</span>' : '',
				self::icon( 'check' )
			);
		}

		return '<div class="lzp-book__services">' . $cards . '</div>';
	}

	/**
	 * A field group, numbered on from `$offset`.
	 *
	 * @param string $form_id Form HTML id.
	 * @param array  $fields  Field rows.
	 * @param int    $offset  Position of the first field.
	 */
	private function fields_html( string $form_id, array $fields, int $offset ): string {
		$html = '';
		foreach ( array_values( $fields ) as $index => $field ) {
			$html .= self::field_html( $form_id, $offset + $index, $field, Form_Fields::TYPES, true );
		}

		return '<div class="lzp-book__grid">' . $html . '</div>';
	}

	/**
	 * The review: forms.js fills the summary; the consent checkbox is posted
	 * but not stored (the browser requires it before sending).
	 *
	 * @param string $form_id Form HTML id.
	 * @param string $consent Checkbox text ('' for none).
	 * @param bool   $plain   Whether the form is one page (no summary).
	 */
	private function review_html( string $form_id, string $consent, bool $plain ): string {
		$html = $plain ? '' : '<dl class="lzp-book__review" data-lzp-review></dl>';

		if ( '' !== $consent ) {
			$html .= sprintf(
				'<label class="lzp-book__consent" for="%1$s"><input type="checkbox" id="%1$s" required><span>%2$s</span></label>',
				esc_attr( $form_id . '-consent' ),
				esc_html( $consent )
			);
		}

		return $html;
	}

	/**
	 * Numbered step list over the form (or a thin line in the focus look).
	 *
	 * @param array  $steps Steps.
	 * @param array  $s     Settings.
	 * @param string $look  Look.
	 */
	private function progress_html( array $steps, array $s, string $look ): string {
		if ( 'split' === $look ) {
			return '<div class="lzp-book__bar" aria-hidden="true"><span></span></div>';
		}

		$items = '';
		foreach ( $steps as $index => $step ) {
			$items .= sprintf(
				'<li class="lzp-book__dot" data-lzp-step-dot="%1$d"><span class="lzp-book__dot-num">%2$s</span><span class="lzp-book__dot-label">%3$s</span></li>',
				$index,
				esc_html( self::num( $index + 1 ) ),
				esc_html( (string) ( $s[ $step[0] . '_title' ] ?? '' ) )
			);
		}

		// Shown only once forms.js runs: without it every step is on the page.
		return '<ol class="lzp-book__progress" aria-hidden="true" hidden>' . $items . '</ol>'
			. ( 'focus' === $look ? '<div class="lzp-book__bar" aria-hidden="true" hidden><span></span></div>' : '' );
	}

	/**
	 * The ink panel of the split look: title, text, the steps and a live summary.
	 *
	 * @param array $s     Settings.
	 * @param array $steps Steps.
	 */
	private function panel_html( array $s, array $steps ): string {
		$items = '';
		foreach ( $steps as $index => $step ) {
			$items .= sprintf(
				'<li class="lzp-book__dot" data-lzp-step-dot="%1$d"><span class="lzp-book__dot-num">%2$s</span><span class="lzp-book__dot-label">%3$s</span></li>',
				$index,
				esc_html( self::num( $index + 1 ) ),
				esc_html( (string) ( $s[ $step[0] . '_title' ] ?? '' ) )
			);
		}

		return sprintf(
			'<aside class="lzp-book__panel lzp-dark">%1$s%2$s<ol class="lzp-book__progress lzp-book__progress--panel" aria-hidden="true">%3$s</ol><dl class="lzp-book__summary" data-lzp-summary hidden></dl></aside>',
			'' !== (string) $s['panel_title'] ? '<p class="lzp-book__panel-title">' . esc_html( $s['panel_title'] ) . '</p>' : '',
			'' !== (string) $s['panel_text'] ? '<p class="lzp-book__panel-text">' . esc_html( $s['panel_text'] ) . '</p>' : '',
			$items
		);
	}

	/**
	 * Back / next / send. Back and next appear once forms.js runs.
	 *
	 * @param array $s     Settings.
	 * @param bool  $plain Whether the form is one page.
	 */
	private function nav_html( array $s, bool $plain ): string {
		$send = str_replace(
			'<button type="button" class="',
			'<button type="submit" class="lzp-book__send ',
			Button::markup(
				array(
					'text'    => (string) $s['button_text'],
					'variant' => 'primary',
					'size'    => 'lg',
					'icon'    => 'check',
				)
			)
		);

		if ( $plain ) {
			return '<div class="lzp-book__nav">' . $send . '</div>';
		}

		return sprintf(
			'<div class="lzp-book__nav"><button type="button" class="lzp-btn lzp-btn--secondary lzp-btn--lg lzp-book__back" data-lzp-step-back hidden><span>%1$s</span></button><span class="lzp-book__count" data-lzp-step-count aria-live="polite"></span><button type="button" class="lzp-btn lzp-btn--primary lzp-btn--lg lzp-book__next" data-lzp-step-next hidden><span>%2$s</span>%3$s</button>%4$s</div>',
			esc_html( (string) $s['back_text'] ),
			esc_html( (string) $s['next_text'] ),
			self::icon( 'arrow-forward' ),
			$send
		);
	}

	/**
	 * Status line and the "done" panel with the tracking code.
	 *
	 * @param string $result  Result of a plain post.
	 * @param string $success Success message.
	 */
	private function result_html( string $result, string $success ): string {
		$code = 'ok' === $result ? Booking_Requests::plain_code() : '';

		return self::form_result_html(
			$result,
			$success,
			'<span class="lzp-book__code"' . ( '' === $code ? ' hidden' : '' ) . '>' . esc_html__( 'Tracking code', 'lenz-plus' ) . ': <strong dir="ltr" data-lzp-form-code>' . esc_html( $code ) . '</strong></span>'
		);
	}
}
