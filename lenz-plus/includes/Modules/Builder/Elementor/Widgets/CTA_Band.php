<?php
/**
 * Closing call to action of the mockups: an ink card with a large white
 * title, a light line of text, a white and an outlined button, and a photo
 * (16:9) at the end. Without a photo the text takes the whole card.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Elementor\Picture;

defined( 'ABSPATH' ) || exit;

/**
 * The «Call to action (Lenz+)» widget.
 */
final class CTA_Band extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-cta-band';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Call to action', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-call-to-action';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'cta', 'call to action', 'banner', 'booking', 'رزرو', 'دعوت' ) );
	}

	/** Content (texts, buttons, photo) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Texts', 'lenz-plus' ) );

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => __( 'Let\'s talk about your project', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => __( 'The first consultation is free.', 'lenz-plus' ),
			)
		);

		$this->add_buttons_controls(
			array(
				'primary_text'   => __( 'Book a session', 'lenz-plus' ),
				'primary_url'    => '#',
				'primary_icon'   => 'calendar',
				'secondary_text' => __( 'See the portfolio', 'lenz-plus' ),
				'secondary_url'  => '#',
			)
		);

		$this->end_controls_section();

		$this->start_content_section( 'section_photo', __( 'Photo', 'lenz-plus' ) );

		$this->add_control(
			'show_photo',
			array(
				'label'        => __( 'Photo', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_photo_control( 'image', __( 'Photo', 'lenz-plus' ), array( 'condition' => array( 'show_photo' => 'yes' ) ) );
		$this->add_ratio_control( 'ratio', '16/9', array( 'condition' => array( 'show_photo' => 'yes' ) ) );

		$this->add_control(
			'image_label',
			array(
				'label'     => __( 'Placeholder text', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '',
				'condition' => array( 'show_photo' => 'yes' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Card', 'lenz-plus' ) );
		$this->add_box_style( 'card', '.lzp-cta' );
		$this->add_text_style( 'title', '.lzp-cta__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'text', '.lzp-cta__text', array( 'label' => __( 'Text', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the card. */
	protected function render(): void {
		$s     = $this->get_settings_for_display();
		$photo = 'yes' === $s['show_photo'];

		$text = '<h2 class="lzp-cta__title">' . esc_html( (string) $s['title'] ) . '</h2>';
		if ( '' !== (string) $s['text'] ) {
			$text .= '<p class="lzp-cta__text">' . esc_html( $s['text'] ) . '</p>';
		}
		$text .= $this->buttons_html( $s, array( 'light', 'outline-dark' ), 'calendar' );

		$media = $photo ? Picture::frame(
			(array) $s['image'],
			array(
				'ratio' => (string) $s['ratio'],
				'label' => (string) $s['image_label'],
				'alt'   => (string) $s['title'],
				'class' => 'lzp-photo lzp-cta__media',
			)
		) : '';

		printf(
			'<div class="lzp-cta lzp-dark%1$s"><div class="lzp-cta__body">%2$s</div>%3$s</div>',
			$photo ? '' : ' lzp-cta--text',
			$text, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			$media // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Picture::frame().
		);
	}
}
