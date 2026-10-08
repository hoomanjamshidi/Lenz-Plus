<?php
/**
 * Key numbers of the mockups («۶ سال تجربه حرفه‌ای», «۲۰۰+ پروژه»): tiles
 * with an accent icon, a large number and a label, in a grid that fits as
 * many 170px tiles as the row allows. Used on the home and about pages.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * The «Key numbers (Lenz+)» widget.
 */
final class Stats extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-stats';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Key numbers', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-counter';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'stats', 'numbers', 'counter', 'آمار', 'عدد' ) );
	}

	/** Content (numbers) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Numbers', 'lenz-plus' ) );

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
			'value',
			array(
				'label'   => __( 'Number', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '10',
			)
		);
		$repeater->add_control(
			'label',
			array(
				'label'   => __( 'Label', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Years of experience', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Numbers', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'icon'  => 'camera',
						'value' => '6',
						'label' => __( 'Years of experience', 'lenz-plus' ),
					),
					array(
						'icon'  => 'images',
						'value' => '200+',
						'label' => __( 'Projects', 'lenz-plus' ),
					),
					array(
						'icon'  => 'trophy',
						'value' => '6',
						'label' => __( 'Awards', 'lenz-plus' ),
					),
				),
				'title_field' => '{{ value }} {{ label }}',
			)
		);

		$this->add_control(
			'min_width',
			array(
				'label'       => __( 'Smallest tile width', 'lenz-plus' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min' => 100,
						'max' => 360,
					),
				),
				'description' => __( 'Tiles share the row and wrap when they would get narrower than this.', 'lenz-plus' ),
				'selectors'   => array( '{{WRAPPER}} .lzp-stats' => '--lzp-min: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Tiles', 'lenz-plus' ) );
		$this->add_gap_control( '.lzp-stats' );
		$this->add_box_style( 'tile', '.lzp-stats__item' );

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .lzp-stats__icon' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_text_style( 'value', '.lzp-stats__value', array( 'label' => __( 'Number', 'lenz-plus' ) ) );
		$this->add_text_style( 'label', '.lzp-stats__label', array( 'label' => __( 'Label', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the tiles. */
	protected function render(): void {
		$s = $this->get_settings_for_display();
		if ( empty( $s['items'] ) ) {
			return;
		}

		echo '<ul class="lzp-stats">';
		foreach ( $s['items'] as $item ) {
			printf(
				'<li class="lzp-stats__item">%1$s<span class="lzp-stats__value">%2$s</span><span class="lzp-stats__label">%3$s</span></li>',
				'' !== $item['icon'] ? self::icon( (string) $item['icon'], 'lzp-stats__icon' ) : '', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup from the bundled library.
				esc_html( self::digits( (string) $item['value'] ) ),
				esc_html( (string) $item['label'] )
			);
		}
		echo '</ul>';
	}
}
