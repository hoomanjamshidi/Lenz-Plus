<?php
/**
 * A small promotion card for sidebars and the end of articles: a soft card
 * with a title, a line of text and a full-width button («دوره‌ها», «جلسه
 * پرتره»), or a dashed card with a muted label above («مرتبط با این نوشته»).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * The «Promo box (Lenz+)» widget.
 */
final class Promo_Box extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-promo-box';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Promo box', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-call-to-action';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'promo', 'sidebar', 'banner', 'تبلیغ', 'سایدبار' ) );
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Promo box', 'lenz-plus' ) );

		$this->add_control(
			'look',
			array(
				'label'   => __( 'Look', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'soft',
				'options' => array(
					'soft'   => __( 'Soft card', 'lenz-plus' ),
					'dashed' => __( 'Dashed card', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'label',
			array(
				'label'   => __( 'Label above', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Courses', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 3,
				'default' => __( 'If these notes helped you, see the full courses too.', 'lenz-plus' ),
			)
		);

		$this->add_buttons_controls(
			array(
				'primary_text' => __( 'See the courses', 'lenz-plus' ),
				'primary_url'  => '#',
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Card', 'lenz-plus' ) );
		$this->add_box_style( 'box', '.lzp-promo' );
		$this->add_text_style( 'title', '.lzp-promo__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'text', '.lzp-promo__text', array( 'label' => __( 'Text', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the card. */
	protected function render(): void {
		$s    = $this->get_settings_for_display();
		$look = 'dashed' === $s['look'] ? 'dashed' : 'soft';

		printf(
			'<div class="lzp-promo lzp-side-card lzp-promo--%1$s">%2$s%3$s%4$s%5$s</div>',
			esc_attr( $look ),
			'' !== (string) $s['label'] ? '<span class="lzp-promo__label">' . esc_html( $s['label'] ) . '</span>' : '',
			'' !== (string) $s['title'] ? '<h3 class="lzp-promo__title">' . esc_html( $s['title'] ) . '</h3>' : '',
			'' !== (string) $s['text'] ? '<p class="lzp-promo__text">' . esc_html( $s['text'] ) . '</p>' : '',
			$this->buttons_html( $s, array( 'primary', 'secondary' ) ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in buttons_html().
		);
	}
}
