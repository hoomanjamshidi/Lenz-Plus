<?php
/**
 * Rich text block. Inherits the theme font.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * The «Text (Lenz+)» widget: rich text in the body, lead or muted tone.
 */
final class Text extends Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-text';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Text', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-text';
	}

	/** Static content: Elementor may cache it. */
	protected function is_dynamic_content(): bool {
		return false;
	}

	/** Content (text, tone, alignment, width) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Text', 'lenz-plus' ) );

		$this->add_control(
			'content',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::WYSIWYG,
				'default' => '<p>' . __( 'Write your text here.', 'lenz-plus' ) . '</p>',
			)
		);

		$this->add_control(
			'tone',
			array(
				'label'   => __( 'Tone', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'body',
				'options' => array(
					'body'  => __( 'Body text', 'lenz-plus' ),
					'lead'  => __( 'Lead (larger)', 'lenz-plus' ),
					'muted' => __( 'Muted (smaller)', 'lenz-plus' ),
				),
			)
		);

		$this->add_align_control( 'align', '.lzp-text' );

		$this->add_responsive_control(
			'max_width',
			array(
				'label'      => __( 'Max width', 'lenz-plus' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'ch', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 200,
						'max' => 1200,
					),
					'ch' => array(
						'min' => 20,
						'max' => 120,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .lzp-text' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Text', 'lenz-plus' ) );
		$this->add_text_style( 'text', '.lzp-text' );

		$this->add_control(
			'link_color',
			array(
				'label'     => __( 'Link colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lzp-text a' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	/** Prints the text. */
	protected function render(): void {
		$s = $this->get_settings_for_display();

		printf(
			'<div class="lzp-text lzp-text--%1$s">%2$s</div>',
			esc_attr( $s['tone'] ),
			wp_kses_post( $this->parse_text_editor( (string) $s['content'] ) )
		);
	}
}
