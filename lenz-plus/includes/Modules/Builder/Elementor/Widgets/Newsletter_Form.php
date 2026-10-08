<?php
/**
 * Sign-up band of the mockups: an ink card with a title and a line of text
 * beside a short form (one contact field, an optional topic and a white
 * button). It is the blog's newsletter («هر ماه یک یادداشت، بدون تبلیغ»)
 * and the courses waitlist («دوره‌ی بعدی را از دست نده»), which accepts a
 * mobile number too.
 *
 * Sign-ups are stored per list in Lenz+ → Page templates → Forms
 * (Newsletter). The form posts to admin-post.php and works without
 * JavaScript; forms.js sends it in the background and answers in place.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Contact_Messages;
use LenzPlus\Modules\Builder\Newsletter;

defined( 'ABSPATH' ) || exit;

/**
 * The «Sign-up (Lenz+)» widget.
 */
final class Newsletter_Form extends Form_Base {

	/** Elementor widget id (also Newsletter::WIDGET). */
	public function get_name(): string {
		return Newsletter::WIDGET;
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Sign-up', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-mail';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'newsletter', 'subscribe', 'waitlist', 'خبرنامه', 'عضویت', 'انتظار' ) );
	}

	/** Content (texts, list, fields) and style controls. */
	protected function register_controls(): void {
		$defaults = Newsletter::defaults();

		$this->start_content_section( 'section_content', __( 'Sign-up', 'lenz-plus' ) );

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'band',
				'options' => array(
					'band' => __( 'Ink band with texts', 'lenz-plus' ),
					'form' => __( 'Form only', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'     => __( 'Title', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXTAREA,
				'rows'      => 2,
				'default'   => __( 'One note a month, no ads', 'lenz-plus' ),
				'condition' => array( 'layout' => 'band' ),
			)
		);

		$this->add_control(
			'text',
			array(
				'label'     => __( 'Text', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXTAREA,
				'rows'      => 2,
				'default'   => __( 'Leave your email and I will send you new posts and photography exercises.', 'lenz-plus' ),
				'condition' => array( 'layout' => 'band' ),
			)
		);

		$this->add_control(
			'list_name',
			array(
				'label'       => __( 'List', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $defaults['list_name'],
				'separator'   => 'before',
				'description' => __( 'Sign-ups are kept per list (e.g. Newsletter, Course waitlist) and downloaded together as CSV.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'contact',
			array(
				'label'   => __( 'Accept', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $defaults['contact'],
				'options' => array(
					'email'       => __( 'Email', 'lenz-plus' ),
					'email_phone' => __( 'Email or mobile number', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'placeholder',
			array(
				'label'   => __( 'Field text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Your email', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'topics',
			array(
				'label'       => __( 'Topics (one per line)', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'default'     => '',
				'description' => __( 'Optional: a list visitors pick what interests them from.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'topic_placeholder',
			array(
				'label'     => __( 'Topic question', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Which topic matters to you?', 'lenz-plus' ),
				'condition' => array( 'topics!' => '' ),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => __( 'Button text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Subscribe', 'lenz-plus' ),
			)
		);

		$this->add_success_control( __( 'Done. The first note is on its way soon.', 'lenz-plus' ) );
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Band', 'lenz-plus' ) );
		$this->add_box_style( 'band', '.lzp-signup' );
		$this->add_text_style( 'title', '.lzp-signup__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'text', '.lzp-signup__text', array( 'label' => __( 'Text', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the band and its form. */
	protected function render(): void {
		$s      = $this->get_settings_for_display();
		$id     = 'lzp-form-' . $this->get_id();
		$result = Newsletter::plain_result( 'lzp_signup', $id );
		$phone  = 'email_phone' === $s['contact'];
		$topics = Contact_Messages::option_list( (string) $s['topics'] );

		$fields = sprintf(
			'<p class="lzp-form__field"><label class="screen-reader-text" for="%1$s-contact">%2$s</label><input class="lzp-form__input" id="%1$s-contact" name="contact" type="%3$s" required placeholder="%2$s"%4$s></p>',
			esc_attr( $id ),
			esc_attr( (string) $s['placeholder'] ),
			$phone ? 'text' : 'email',
			$phone ? ' maxlength="100"' : ' inputmode="email" autocomplete="email"'
		);

		if ( $topics ) {
			$options = '<option value="">' . esc_html( (string) $s['topic_placeholder'] ) . '</option>';
			foreach ( $topics as $topic ) {
				$options .= sprintf( '<option value="%1$s">%1$s</option>', esc_attr( $topic ) );
			}
			$fields .= sprintf(
				'<p class="lzp-form__field"><label class="screen-reader-text" for="%1$s-topic">%2$s</label><select class="lzp-form__input" id="%1$s-topic" name="topic">%3$s</select></p>',
				esc_attr( $id ),
				esc_html( (string) $s['topic_placeholder'] ),
				$options
			);
		}

		$button = str_replace(
			'<button type="button"',
			'<button type="submit"',
			Button::markup(
				array(
					'text'    => (string) $s['button_text'],
					// White on the ink band through the dark surface tokens.
					'variant' => 'primary',
					'size'    => 'lg',
					'icon'    => 'arrow-forward',
				)
			)
		);

		$form = sprintf(
			'<form class="lzp-form lzp-form--stack" id="%1$s" action="%2$s" method="post" data-lzp-form%3$s>%4$s<div class="lzp-form__fields">%5$s%6$s</div>%7$s</form>',
			esc_attr( $id ),
			esc_url( Newsletter::form_url() ),
			'ok' === $result ? ' data-lzp-sent' : '',
			Newsletter::hidden_fields( Newsletter::ACTION, $this->get_id(), $id ),
			$fields,
			$button,
			self::result_html( $result, (string) $s['success_text'] )
		);

		if ( 'form' === $s['layout'] ) {
			echo '<div class="lzp-signup lzp-signup--form">' . $form . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building.
			return;
		}

		$texts = '<div class="lzp-signup__texts">'
			. ( '' !== (string) $s['title'] ? '<h2 class="lzp-signup__title">' . esc_html( $s['title'] ) . '</h2>' : '' )
			. ( '' !== (string) $s['text'] ? '<p class="lzp-signup__text">' . esc_html( $s['text'] ) . '</p>' : '' )
			. '</div>';

		echo '<div class="lzp-signup lzp-dark">' . $texts . $form . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building.
	}
}
