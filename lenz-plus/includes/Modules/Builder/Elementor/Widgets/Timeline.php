<?php
/**
 * Career path of the about page: a thin vertical line with ink dots, each
 * stop showing a muted date or label («۱۳۸۶», «امروز»), a bold title and a
 * line of text.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * The «Timeline (Lenz+)» widget.
 */
final class Timeline extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-timeline';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Timeline', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-time-line';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'timeline', 'history', 'career', 'مسیر', 'سوابق', 'تاریخچه' ) );
	}

	/** Content (stops) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Stops', 'lenz-plus' ) );

		$repeater = new Repeater();
		$repeater->add_control(
			'date',
			array(
				'label'   => __( 'Date or label', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '1399',
			)
		);
		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'A milestone', 'lenz-plus' ),
				'label_block' => true,
			)
		);
		$repeater->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => __( 'What happened then.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Stops', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'date' => '1386' ),
					array( 'date' => '1399' ),
					array( 'date' => __( 'Today', 'lenz-plus' ) ),
				),
				'title_field' => '{{ date }} — {{ title }}',
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Timeline', 'lenz-plus' ) );

		$this->add_control(
			'line_color',
			array(
				'label'     => __( 'Line colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lzp-timeline' => '--lzp-tl-line: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'dot_color',
			array(
				'label'     => __( 'Dot colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lzp-timeline' => '--lzp-tl-dot: {{VALUE}};' ),
			)
		);

		$this->add_text_style( 'date', '.lzp-timeline__date', array( 'label' => __( 'Date', 'lenz-plus' ) ) );
		$this->add_text_style( 'title', '.lzp-timeline__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'text', '.lzp-timeline__text', array( 'label' => __( 'Text', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the timeline. */
	protected function render(): void {
		$s = $this->get_settings_for_display();
		if ( empty( $s['items'] ) ) {
			return;
		}

		echo '<ol class="lzp-timeline">';
		foreach ( $s['items'] as $item ) {
			printf(
				'<li class="lzp-timeline__item"><span class="lzp-timeline__date">%1$s</span><h3 class="lzp-timeline__title">%2$s</h3>%3$s</li>',
				esc_html( self::digits( (string) $item['date'] ) ),
				esc_html( (string) $item['title'] ),
				'' !== (string) $item['text'] ? '<p class="lzp-timeline__text">' . esc_html( $item['text'] ) . '</p>' : ''
			);
		}
		echo '</ol>';
	}
}
