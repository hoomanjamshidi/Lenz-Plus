<?php
/**
 * A thin ink bar at the very top of the screen that fills while the article
 * is read (blog.js). Without JavaScript nothing shows.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * The «Reading progress (Lenz+)» widget.
 */
final class Reading_Progress extends Blog_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-reading-progress';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Reading progress', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-progress-tracker';
	}

	/** Static markup. */
	protected function is_dynamic_content(): bool {
		return false;
	}

	/** Content controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Reading progress', 'lenz-plus' ) );

		$this->add_control(
			'note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'A thin bar at the top of the screen that fills while the post is read. Place it anywhere in the template.', 'lenz-plus' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->add_control(
			'color',
			array(
				'label'     => __( 'Colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lzp-progress__bar' => 'background: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/** Prints the bar (a hint in the editor, where it would cover the toolbar). */
	protected function render(): void {
		if ( $this->in_editor() ) {
			$this->editor_hint( __( 'Reading progress bar (shown at the top of the screen on the site).', 'lenz-plus' ) );
			return;
		}

		echo '<div class="lzp-progress" data-lzp-progress aria-hidden="true"><span class="lzp-progress__bar"></span></div>';
	}
}
