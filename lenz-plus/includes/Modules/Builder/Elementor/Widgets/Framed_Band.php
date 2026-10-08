<?php
/**
 * A short statement in a frame: dashed rules above and below, two corner
 * brackets, a muted label and one big line («زندگی من آن‌چیزی است که برایش
 * می‌جنگم», «بگو سیـب» with a camera). The film-strip ticks along the rules
 * follow the global "Guide lines" option.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * The «Framed statement (Lenz+)» widget.
 */
final class Framed_Band extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-framed-band';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Framed statement', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-blockquote';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'quote', 'statement', 'motto', 'frame', 'جمله', 'شعار' ) );
	}

	/** Content (label, statement, icon, ticks) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Statement', 'lenz-plus' ) );

		$this->add_control(
			'label',
			array(
				'label'   => __( 'Label', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Life as I see it', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'text',
			array(
				'label'   => __( 'Statement', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => __( 'My life is what I fight for', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'icon',
			array(
				'label'   => __( 'Icon after the statement', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => self::icon_options(),
			)
		);

		$this->add_control(
			'size',
			array(
				'label'   => __( 'Size', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'md',
				'options' => array(
					'md' => __( 'Medium', 'lenz-plus' ),
					'lg' => __( 'Large (one line)', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'ticks',
			array(
				'label'        => __( 'Film-strip ticks', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Statement', 'lenz-plus' ) );
		$this->add_text_style( 'label', '.lzp-framed__label', array( 'label' => __( 'Label', 'lenz-plus' ) ) );
		$this->add_text_style( 'text', '.lzp-framed__text', array( 'label' => __( 'Statement', 'lenz-plus' ) ) );

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .lzp-framed__icon' => 'color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/** Prints the framed statement. */
	protected function render(): void {
		$s = $this->get_settings_for_display();

		$inner = '<span class="lzp-framed__corner lzp-framed__corner--start" aria-hidden="true"></span><span class="lzp-framed__corner lzp-framed__corner--end" aria-hidden="true"></span>';
		if ( '' !== (string) $s['label'] ) {
			$inner .= '<span class="lzp-framed__label">' . esc_html( $s['label'] ) . '</span>';
		}

		$line = '<blockquote class="lzp-framed__text">' . esc_html( (string) $s['text'] ) . '</blockquote>';
		if ( '' !== (string) $s['icon'] ) {
			$line = '<div class="lzp-framed__line">' . $line . self::icon( (string) $s['icon'], 'lzp-framed__icon' ) . '</div>';
		}

		printf(
			'<div class="lzp-framed lzp-framed--%1$s%2$s"><div class="lzp-framed__box">%3$s</div></div>',
			esc_attr( 'lg' === $s['size'] ? 'lg' : 'md' ),
			'yes' === $s['ticks'] ? ' lzp-framed--ticks' : '',
			$inner . $line // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		);
	}
}
