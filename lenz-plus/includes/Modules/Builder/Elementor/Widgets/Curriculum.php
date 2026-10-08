<?php
/**
 * «سرفصل‌های ورکشاپ»: the course's sessions as a numbered accordion
 * (number tile, title, what it covers). Built on <details>, so it opens
 * without JavaScript.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Course_Data;

defined( 'ABSPATH' ) || exit;

/**
 * The «Curriculum (Lenz+)» widget.
 */
final class Curriculum extends Course_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-curriculum';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Curriculum', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-accordion';
	}

	/** Content controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Curriculum', 'lenz-plus' ) );

		$this->add_control(
			'open_first',
			array(
				'label'        => __( 'First session open', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Sessions', 'lenz-plus' ) );
		$this->add_box_style( 'item', '.lzp-faq__item' );
		$this->add_text_style( 'title', '.lzp-faq__question', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the sessions. */
	protected function render(): void {
		$s  = $this->get_settings_for_display();
		$id = $this->current_course();
		if ( ! $id ) {
			return;
		}

		$sessions = Course_Data::details( $id )['curriculum'];
		if ( ! $sessions ) {
			$this->editor_hint( __( 'Nothing to list yet: fill in the curriculum in the course\'s details.', 'lenz-plus' ) );
			return;
		}

		echo '<div class="lzp-faq lzp-curriculum">';
		foreach ( array_values( $sessions ) as $index => $session ) {
			printf(
				'<details class="lzp-faq__item"%1$s><summary class="lzp-faq__q"><span class="lzp-curriculum__num">%2$s</span><span class="lzp-faq__question">%3$s</span>%4$s</summary>%5$s</details>',
				0 === $index && 'yes' === $s['open_first'] ? ' open' : '',
				esc_html( self::digits( (string) ( $index + 1 ) ) ),
				esc_html( (string) $session[0] ),
				self::icon( 'chevron-down', 'lzp-faq__sign' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup from the bundled library.
				'' !== (string) $session[1] ? '<div class="lzp-faq__a">' . esc_html( $session[1] ) . '</div>' : ''
			);
		}
		echo '</div>';
	}
}
