<?php
/**
 * Top of a course page: the breadcrumb, badges (type on ink, «topic ·
 * level», the price), the title, the short description and icon facts
 * (duration, format, tools, date, capacity), beside the cover (16:10). When
 * the course has an intro video, a play button on the cover opens it in a
 * <dialog> (courses.js); without JavaScript it links to the video.
 *
 * Like the page hero it spans the whole width and boxes its own content.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Course_Data;
use LenzPlus\Modules\Builder\Elementor\Picture;

defined( 'ABSPATH' ) || exit;

/**
 * The «Course hero (Lenz+)» widget.
 */
final class Course_Hero extends Course_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-course-hero';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Course hero', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-header';
	}

	/** Content controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Course hero', 'lenz-plus' ) );

		$this->add_control(
			'crumbs',
			array(
				'label'        => __( 'Breadcrumb', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'home_label',
			array(
				'label'     => __( 'Home label', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Home', 'lenz-plus' ),
				'condition' => array( 'crumbs' => 'yes' ),
			)
		);

		$this->add_control(
			'watermark',
			array(
				'label'       => __( 'Outline word', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);

		$this->add_control(
			'guides',
			array(
				'label'        => __( 'Guide lines', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Texts', 'lenz-plus' ) );
		$this->add_text_style( 'title', '.lzp-hero__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'lead', '.lzp-hero__lead', array( 'label' => __( 'Text', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the hero. */
	protected function render(): void {
		$s  = $this->get_settings_for_display();
		$id = $this->current_course();
		if ( ! $id ) {
			return;
		}

		$d     = Course_Data::details( $id );
		$title = get_the_title( $id );
		$lead  = wp_strip_all_tags( get_the_excerpt( $id ) );
		$price = 'open' === $d['status'] ? Course_Data::price_html( $id ) : '';
		$topic = implode( ' · ', array_filter( array( $d['topic'], $d['level'] ) ) );

		$badges = ( '' !== $d['kind'] ? '<span class="lzp-chip lzp-chip--ink">' . esc_html( $d['kind'] ) . '</span>' : '' )
			. ( '' !== $topic ? '<span class="lzp-chip">' . esc_html( $topic ) . '</span>' : '' )
			. ( '' !== $price ? '<span class="lzp-chip lzp-chip--line">' . wp_kses_post( $price ) . '</span>' : '' );

		$text = ( '' !== $badges ? '<div class="lzp-pmeta">' . $badges . '</div>' : '' )
			. '<h1 class="lzp-hero__title">' . esc_html( $title ) . '</h1>'
			. ( '' !== $lead ? '<p class="lzp-hero__lead">' . esc_html( $lead ) . '</p>' : '' )
			. self::facts_html( $d );

		$crumbs = 'yes' === $s['crumbs'] ? '<div class="lzp-course-hero__crumbs">' . Breadcrumb::html( (string) $s['home_label'] ) . '</div>' : '';
		$decor  = ( 'yes' === $s['guides'] ? '<span class="lzp-hero__guides" aria-hidden="true"></span>' : '' )
			. ( '' !== (string) $s['watermark'] ? '<span class="lzp-watermark lzp-hero__watermark" aria-hidden="true">' . esc_html( $s['watermark'] ) . '</span>' : '' );

		printf(
			'<div class="lzp-hero lzp-hero--text lzp-course-hero">%1$s<div class="lzp-hero__inner">%2$s<div class="lzp-course-hero__grid"><div class="lzp-hero__text">%3$s</div>%4$s</div></div></div>',
			$decor, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			$crumbs, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Breadcrumb::html().
			$text, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building.
			self::media_html( $id, $d['video'], $title ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in media_html().
		);
	}

	/**
	 * Icon facts: duration, format, tools, date and capacity (those that are set).
	 *
	 * @param array $d Course details.
	 */
	private static function facts_html( array $d ): string {
		$facts = array(
			array( 'clock', __( 'Duration', 'lenz-plus' ), $d['duration'] ),
			array( 'presentation', __( 'Format', 'lenz-plus' ), Course_Data::format_label( $d ) ),
			array( 'mobile', __( 'Tools', 'lenz-plus' ), $d['tools'] ),
			array( 'calendar', __( 'Date', 'lenz-plus' ), 'archive' === $d['status'] ? $d['year'] : $d['schedule'] ),
			/* translators: %s: number of people. */
			array( 'users', __( 'Capacity', 'lenz-plus' ), $d['capacity'] ? sprintf( __( '%s people', 'lenz-plus' ), self::num( (int) $d['capacity'] ) ) : '' ),
		);

		$html = '';
		foreach ( $facts as $fact ) {
			if ( '' === (string) $fact[2] ) {
				continue;
			}
			$html .= sprintf(
				'<li class="lzp-ifacts__item"><span class="lzp-ifacts__icon">%1$s</span><span class="lzp-ifacts__text"><span class="lzp-ifacts__label">%2$s</span><span class="lzp-ifacts__value">%3$s</span></span></li>',
				self::icon( $fact[0] ),
				esc_html( $fact[1] ),
				esc_html( self::digits( (string) $fact[2] ) )
			);
		}

		return '' !== $html ? '<ul class="lzp-ifacts">' . $html . '</ul>' : '';
	}

	/**
	 * The cover, with a play button when there is an intro video.
	 *
	 * @param int    $id    Course ID.
	 * @param string $video Video URL.
	 * @param string $title Course title.
	 */
	private static function media_html( int $id, string $video, string $title ): string {
		$html = Picture::frame(
			array( 'id' => (int) get_post_thumbnail_id( $id ) ),
			array(
				'ratio'   => '16/10',
				'alt'     => $title,
				'class'   => 'lzp-photo',
				'loading' => 'high',
			)
		);

		if ( '' !== $video ) {
			$html .= sprintf(
				'<a class="lzp-play" href="%1$s" data-lzp-video="%1$s" data-title="%2$s" target="_blank" rel="noopener"><span class="screen-reader-text">%3$s</span>%4$s</a>',
				esc_url( $video ),
				esc_attr( $title ),
				esc_html__( 'Play the intro video', 'lenz-plus' ),
				self::icon( 'play' )
			);
		}

		return '<div class="lzp-course-hero__media">' . $html . '</div>';
	}
}
