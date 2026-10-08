<?php
/**
 * Numbered steps of the mockups («۰۱ گفت‌وگوی اول», «۰۲ برنامه و
 * قرارداد»…): white cards with a muted number, a title and a short text,
 * four in a row on wide screens. Numbers come from the order, so steps can
 * be reordered freely.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * The «Process steps (Lenz+)» widget.
 */
final class Process_Steps extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-process-steps';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Process steps', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-number-field';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'steps', 'process', 'how', 'مراحل', 'قدم' ) );
	}

	/** Content (steps) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Steps', 'lenz-plus' ) );

		$repeater = new Repeater();
		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Step title', 'lenz-plus' ),
				'label_block' => true,
			)
		);
		$repeater->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 3,
				'default' => __( 'What happens in this step.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Steps', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'title' => __( 'First conversation', 'lenz-plus' ) ),
					array( 'title' => __( 'Plan and contract', 'lenz-plus' ) ),
					array( 'title' => __( 'Shooting day', 'lenz-plus' ) ),
					array( 'title' => __( 'Selection and delivery', 'lenz-plus' ) ),
				),
				'title_field' => '{{ title }}',
			)
		);

		$this->add_control(
			'numbers',
			array(
				'label'        => __( 'Numbers', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'   => __( 'Title tag', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => array(
					'h3' => 'H3',
					'h4' => 'H4',
					'p'  => 'p',
				),
			)
		);

		$this->add_columns_control( '.lzp-steps', array( 4, 2, 1 ) );
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Cards', 'lenz-plus' ) );
		$this->add_gap_control( '.lzp-steps' );
		$this->add_box_style( 'card', '.lzp-steps__item' );
		$this->add_text_style( 'num', '.lzp-steps__num', array( 'label' => __( 'Number', 'lenz-plus' ) ) );
		$this->add_text_style( 'title', '.lzp-steps__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'text', '.lzp-steps__text', array( 'label' => __( 'Text', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the steps. */
	protected function render(): void {
		$s = $this->get_settings_for_display();
		if ( empty( $s['items'] ) ) {
			return;
		}

		$tag = in_array( $s['title_tag'], array( 'h3', 'h4', 'p' ), true ) ? $s['title_tag'] : 'h3';

		echo '<ol class="lzp-steps lzp-grid">';
		foreach ( $s['items'] as $index => $item ) {
			$number = 'yes' === $s['numbers'] ? '<span class="lzp-steps__num" aria-hidden="true">' . esc_html( self::digits( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ) ) . '</span>' : '';

			printf(
				'<li class="lzp-steps__item">%1$s<%2$s class="lzp-steps__title">%3$s</%2$s>%4$s</li>',
				$number, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
				esc_attr( $tag ),
				esc_html( (string) $item['title'] ),
				'' !== (string) $item['text'] ? '<p class="lzp-steps__text">' . esc_html( $item['text'] ) . '</p>' : ''
			);
		}
		echo '</ol>';
	}
}
