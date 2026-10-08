<?php
/**
 * «مشخصات دوره»: the buy card of a course page. The title, the price and
 * the main button (pay online through the checkout, the request form on the
 * page, the waitlist, or "fully booked"), a phone line for questions, and
 * the spec rows (duration, level, type, tools, the extra specs, format,
 * date and seats left). Wide as in the mockups, or narrow for a sticky
 * sidebar. The mobile buy bar appears once this card scrolls away.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Core\Persian;
use LenzPlus\Core\Theme_Bridge;
use LenzPlus\Modules\Builder\Course_Data;

defined( 'ABSPATH' ) || exit;

/**
 * The «Course buy box (Lenz+)» widget.
 */
final class Course_Buy_Box extends Course_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-course-buy-box';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Course buy box', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-price-table';
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Buy box', 'lenz-plus' ) );

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'wide',
				'options' => array(
					'wide'   => __( 'Wide (specs beside the price)', 'lenz-plus' ),
					'narrow' => __( 'Narrow (sidebar)', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'label',
			array(
				'label'   => __( 'Label', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Course details', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'       => __( 'Button text', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Register for the course', 'lenz-plus' ),
				'description' => __( 'Used while registration is open; the other states have their own text.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'phone',
			array(
				'label'       => __( 'Phone for questions', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => Theme_Bridge::support_phone(),
				'description' => __( 'Empty: the number from Lenz\'s mobile menu support box. Clear both to hide the line.', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Card', 'lenz-plus' ) );
		$this->add_box_style( 'card', '.lzp-buybox' );
		$this->add_text_style( 'title', '.lzp-buybox__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'price', '.lzp-buybox__price', array( 'label' => __( 'Price', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Spec rows of a course: [label, value].
	 *
	 * @param int $id Course ID.
	 * @return array<int, array{0:string, 1:string}>
	 */
	public static function spec_rows( int $id ): array {
		$d    = Course_Data::details( $id );
		$left = Course_Data::seats_left( $id );
		$rows = array(
			array( __( 'Duration', 'lenz-plus' ), $d['duration'] ),
			array( __( 'Level', 'lenz-plus' ), $d['level'] ),
			array( __( 'Type', 'lenz-plus' ), $d['kind'] ),
			array( __( 'Tools', 'lenz-plus' ), $d['tools'] ),
		);

		foreach ( $d['specs'] as $spec ) {
			$rows[] = array( (string) $spec[0], (string) $spec[1] );
		}

		$rows[] = array( __( 'Format', 'lenz-plus' ), Course_Data::format_label( $d ) );
		$rows[] = array( __( 'Date and time', 'lenz-plus' ), $d['schedule'] );

		if ( 'open' === $d['status'] && null !== $left ) {
			$rows[] = array(
				__( 'Seats left', 'lenz-plus' ),
				$d['capacity']
					/* translators: 1: seats left, 2: capacity. */
					? sprintf( __( '%1$s of %2$s people', 'lenz-plus' ), self::num( $left ), self::num( (int) $d['capacity'] ) )
					/* translators: %s: number of people. */
					: sprintf( __( '%s people', 'lenz-plus' ), self::num( $left ) ),
			);
		}

		return array_values(
			array_filter(
				$rows,
				static function ( array $row ): bool {
					return '' !== $row[0] && '' !== $row[1];
				}
			)
		);
	}

	/** Prints the card. */
	protected function render(): void {
		$s  = $this->get_settings_for_display();
		$id = $this->current_course();
		if ( ! $id ) {
			return;
		}

		$d      = Course_Data::details( $id );
		$price  = 'open' === $d['status'] ? Course_Data::price_html( $id ) : '';
		$phone  = '' !== (string) $s['phone'] ? (string) $s['phone'] : Theme_Bridge::support_phone();
		$button = Course_Data::button_html( Course_Data::action( $id ), 'lzp-btn lzp-btn--primary lzp-btn--lg lzp-buybox__btn', (string) $s['button_text'], self::icon( 'arrow-forward' ) );

		$rows = '';
		foreach ( self::spec_rows( $id ) as $row ) {
			$rows .= '<li class="lzp-buybox__row"><span class="lzp-buybox__key">' . esc_html( $row[0] ) . '</span><span class="lzp-buybox__value">' . esc_html( self::digits( $row[1] ) ) . '</span></li>';
		}

		$main = ( '' !== (string) $s['label'] ? '<span class="lzp-buybox__label">' . esc_html( $s['label'] ) . '</span>' : '' )
			. '<span class="lzp-buybox__title">' . esc_html( get_the_title( $id ) ) . '</span>'
			. ( '' !== $price ? '<span class="lzp-buybox__price">' . $price . '</span>' : '' )
			. '<div class="lzp-buybox__action" data-lzp-buy>' . $button . '</div>';

		if ( '' !== $phone ) {
			$main .= sprintf(
				'<p class="lzp-buybox__phone">%1$s<span>%2$s <a href="tel:%3$s" dir="ltr">%4$s</a></span></p>',
				self::icon( 'phone' ),
				esc_html__( 'Questions? Call before you register:', 'lenz-plus' ),
				esc_attr( (string) preg_replace( '/[^\d+]/', '', Persian::latin_digits( $phone ) ) ),
				esc_html( self::digits( $phone ) )
			);
		}

		printf(
			'<div class="lzp-buybox lzp-buybox--%1$s" id="lzp-buy"><div class="lzp-buybox__main">%2$s</div>%3$s</div>',
			esc_attr( 'narrow' === $s['layout'] ? 'narrow' : 'wide' ),
			$main, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building (price markup is WooCommerce's).
			'' !== $rows ? '<ul class="lzp-buybox__rows">' . $rows . '</ul>' : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		);
	}
}
