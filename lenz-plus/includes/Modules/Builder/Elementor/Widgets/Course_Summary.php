<?php
/**
 * «خلاصه دوره»: one card with the course's cover, its title, ticked facts
 * (instructor, format, duration…) and, behind a divider, the price with the
 * main button. It opens the course page with the side menu and closes it
 * again as the final buy card (`#buy`). The button's box is `data-lzp-buy`,
 * so the buy bar steps aside while it is on screen.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Course_Data;
use LenzPlus\Modules\Builder\Elementor\Picture;

defined( 'ABSPATH' ) || exit;

/**
 * The «Course summary (Lenz+)» widget.
 */
final class Course_Summary extends Course_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-course-summary';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Course summary', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-call-to-action';
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Course summary', 'lenz-plus' ) );

		$this->add_control(
			'label',
			array(
				'label'   => __( 'Label', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'       => __( 'Title tag', 'lenz-plus' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'h1',
				'options'     => array(
					'h1' => 'H1',
					'h2' => 'H2',
					'p'  => 'P',
				),
				'description' => __( 'H1 at the top of the page; H2 for the card repeated further down.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'facts',
			array(
				'label'       => __( 'Facts', 'lenz-plus' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'default'     => array( 'instructor', 'format', 'duration' ),
				'options'     => self::fact_labels(),
			)
		);

		$this->add_control(
			'cover',
			array(
				'label'        => __( 'Cover', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'       => __( 'Button text', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Join this course', 'lenz-plus' ),
				'description' => __( 'Used while registration is open; the other states have their own text.', 'lenz-plus' ),
			)
		);

		$this->add_photo_tone_control();

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Card', 'lenz-plus' ) );
		$this->add_box_style( 'card', '.lzp-csum' );
		$this->add_text_style( 'title', '.lzp-csum__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'price', '.lzp-csum__price', array( 'label' => __( 'Price', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Fact key → label (also the label printed before each value).
	 *
	 * @return array<string, string>
	 */
	private static function fact_labels(): array {
		return array(
			'instructor' => __( 'Instructor', 'lenz-plus' ),
			'format'     => __( 'Format', 'lenz-plus' ),
			'duration'   => __( 'Duration', 'lenz-plus' ),
			'sessions'   => __( 'Sessions', 'lenz-plus' ),
			'schedule'   => __( 'Date and time', 'lenz-plus' ),
			'level'      => __( 'Level', 'lenz-plus' ),
			'tools'      => __( 'Tools', 'lenz-plus' ),
			'capacity'   => __( 'Capacity', 'lenz-plus' ),
		);
	}

	/**
	 * Value of one fact ('' when the course has none).
	 *
	 * @param string $key Fact key.
	 * @param int    $id  Course ID.
	 * @param array  $d   Course details.
	 */
	private static function fact_value( string $key, int $id, array $d ): string {
		switch ( $key ) {
			case 'instructor':
				$person = Course_Data::instructor( $id );
				return $person ? $person['name'] : '';
			case 'format':
				return Course_Data::format_label( $d );
			case 'sessions':
				/* translators: %s: number of sessions. */
				return $d['curriculum'] ? sprintf( __( '%s sessions', 'lenz-plus' ), self::num( count( $d['curriculum'] ) ) ) : '';
			case 'capacity':
				/* translators: %s: number of people. */
				return $d['capacity'] ? sprintf( __( '%s people', 'lenz-plus' ), self::num( (int) $d['capacity'] ) ) : '';
			default:
				return isset( $d[ $key ] ) && is_string( $d[ $key ] ) ? self::digits( $d[ $key ] ) : '';
		}
	}

	/** Prints the card. */
	protected function render(): void {
		$s  = $this->get_settings_for_display();
		$id = $this->current_course();
		if ( ! $id ) {
			return;
		}

		$d      = Course_Data::details( $id );
		$labels = self::fact_labels();
		$facts  = '';
		foreach ( (array) $s['facts'] as $key ) {
			$value = isset( $labels[ $key ] ) ? self::fact_value( (string) $key, $id, $d ) : '';
			if ( '' === $value ) {
				continue;
			}
			$facts .= sprintf(
				'<li class="lzp-csum__fact">%1$s<span class="lzp-csum__key">%2$s</span><span class="lzp-csum__value">%3$s</span></li>',
				self::icon( 'check', 'lzp-csum__tick' ),
				/* translators: %s: fact label («مدرس»). */
				esc_html( sprintf( __( '%s:', 'lenz-plus' ), $labels[ $key ] ) ),
				esc_html( $value )
			);
		}

		$title_tag = in_array( $s['title_tag'], array( 'h1', 'h2', 'p' ), true ) ? $s['title_tag'] : 'h2';
		$title     = get_the_title( $id );
		$price     = 'open' === $d['status'] ? Course_Data::price_html( $id ) : '';
		$badge     = Course_Data::badge( $id );

		$cover = 'yes' === $s['cover'] ? '<div class="lzp-csum__cover">' . Picture::frame(
			array( 'id' => (int) get_post_thumbnail_id( $id ) ),
			array(
				'ratio'   => '4/3',
				'alt'     => $title,
				'class'   => 'lzp-photo',
				'sizes'   => '(max-width: 767px) 100vw, 320px',
				// The card with the page's H1 is at the top: its cover is the first paint.
				'loading' => 'h1' === $title_tag ? 'high' : 'lazy',
			)
		) . '</div>' : '';

		$info = ( '' !== (string) $s['label'] ? '<span class="lzp-csum__label">' . esc_html( $s['label'] ) . '</span>' : '' )
			. sprintf( '<%1$s class="lzp-csum__title">%2$s</%1$s>', $title_tag, esc_html( $title ) )
			. ( '' !== $facts ? '<ul class="lzp-csum__facts">' . $facts . '</ul>' : '' );

		$buy = '<span class="lzp-csum__badge">' . esc_html( $badge ) . '</span>'
			. ( '' !== $price ? '<span class="lzp-csum__price">' . $price . '</span>' : '' )
			. Course_Data::button_html( Course_Data::action( $id ), 'lzp-btn lzp-btn--primary lzp-btn--lg lzp-csum__btn', (string) $s['button_text'], self::icon( 'arrow-forward' ) );

		printf(
			'<div class="lzp-csum%1$s">%2$s<div class="lzp-csum__info">%3$s</div><div class="lzp-csum__buy" data-lzp-buy>%4$s</div></div>',
			'' === $cover ? ' lzp-csum--no-cover' : '',
			$cover, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Picture::frame().
			$info, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building.
			$buy // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building (price markup is our own).
		);
	}
}
