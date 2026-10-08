<?php
/**
 * Fixed buy bar for phones on a course page: the price and the main button
 * (same action as the buy box), shown once the buy box scrolls out of view
 * (courses.js). It sits above the Lenz+ bottom navigation, and the support
 * button lifts over it (support-button.css watches `.lzp-buybar--mobile.is-visible`).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Course_Data;

defined( 'ABSPATH' ) || exit;

/**
 * The «Mobile buy bar (Lenz+)» widget.
 */
final class Mobile_Buy_Bar extends Course_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-mobile-buy-bar';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Mobile buy bar', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-cart-solid';
	}

	/** Content controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Buy bar', 'lenz-plus' ) );

		$this->add_control(
			'info',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Fixed to the bottom of the screen on phones (and tablets if chosen), once the buy box scrolls away. In the editor it is shown in place.', 'lenz-plus' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->add_control(
			'devices',
			array(
				'label'   => __( 'Show on', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'mobile',
				'options' => array(
					'mobile' => __( 'Phones', 'lenz-plus' ),
					'tablet' => __( 'Phones and tablets', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => __( 'Button text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Register', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();
	}

	/** Prints the bar (only for courses open for registration). */
	protected function render(): void {
		$s  = $this->get_settings_for_display();
		$id = $this->current_course();
		if ( ! $id || 'open' !== Course_Data::details( $id )['status'] ) {
			return;
		}

		$action = Course_Data::action( $id );
		if ( 'none' === $action['type'] ) {
			return;
		}

		printf(
			'<div class="lzp-buybar lzp-buybar--%1$s%2$s" data-lzp-buybar><span class="lzp-buybar__price">%3$s</span>%4$s</div>',
			esc_attr( 'tablet' === $s['devices'] ? 'tablet' : 'mobile' ),
			$this->in_editor() ? ' is-editor' : '',
			Course_Data::price_html( $id ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce's price markup.
			Course_Data::button_html( $action, 'lzp-btn lzp-btn--primary lzp-buybar__btn', (string) $s['button_text'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in button_html().
		);
	}
}
