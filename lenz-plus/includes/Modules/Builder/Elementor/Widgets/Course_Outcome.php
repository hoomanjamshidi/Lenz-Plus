<?php
/**
 * «خروجی ورکشاپ»: a bordered card with the course's outcome text beside
 * small soft tiles (duration, level, type, tools).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Course_Data;

defined( 'ABSPATH' ) || exit;

/**
 * The «Course outcome (Lenz+)» widget.
 */
final class Course_Outcome extends Course_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-course-outcome';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Course outcome', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-info-box';
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Outcome', 'lenz-plus' ) );

		$this->add_control(
			'label',
			array(
				'label'   => __( 'Label', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'What you take away', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Card', 'lenz-plus' ) );
		$this->add_box_style( 'card', '.lzp-outcome' );
		$this->add_text_style( 'text', '.lzp-outcome__text', array( 'label' => __( 'Text', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the card. */
	protected function render(): void {
		$s  = $this->get_settings_for_display();
		$id = $this->current_course();
		if ( ! $id ) {
			return;
		}

		$d = Course_Data::details( $id );
		if ( '' === $d['outcome'] ) {
			$this->editor_hint( __( 'Nothing to show yet: fill in the outcome in the course\'s details.', 'lenz-plus' ) );
			return;
		}

		$tiles = '';
		foreach ( array(
			array( __( 'Duration', 'lenz-plus' ), $d['duration'] ),
			array( __( 'Level', 'lenz-plus' ), $d['level'] ),
			array( __( 'Type', 'lenz-plus' ), $d['kind'] ),
			array( __( 'Tools', 'lenz-plus' ), $d['tools'] ),
		) as $tile ) {
			if ( '' !== (string) $tile[1] ) {
				$tiles .= '<li class="lzp-outcome__tile"><span class="lzp-outcome__key">' . esc_html( $tile[0] ) . '</span><span class="lzp-outcome__value">' . esc_html( self::digits( (string) $tile[1] ) ) . '</span></li>';
			}
		}

		printf(
			'<div class="lzp-outcome"><div class="lzp-outcome__body">%1$s<p class="lzp-outcome__text">%2$s</p></div>%3$s</div>',
			'' !== (string) $s['label'] ? '<span class="lzp-outcome__label">' . esc_html( $s['label'] ) . '</span>' : '',
			esc_html( $d['outcome'] ),
			'' !== $tiles ? '<ul class="lzp-outcome__tiles">' . $tiles . '</ul>' : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		);
	}
}
