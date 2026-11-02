<?php
/**
 * «مدرس دوره»: introduces a course's instructor in one of four looks: the
 * mockups' ink card, a profile card (photo card beside a heading, number
 * tiles and a list of achievements), a large cut-out photo on a coloured
 * shape beside the list, or a compact row for sidebars.
 *
 * The person comes from the course's instructor (Lenz's `expert` post:
 * name, position, bio, photo, career lines); whatever the expert leaves
 * empty is filled from the texts typed in the widget, so the box still
 * shows on courses without an expert. Number tiles are always the widget's.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use LenzPlus\Modules\Builder\Context;
use LenzPlus\Modules\Builder\Course_Data;
use LenzPlus\Modules\Builder\Elementor\Picture;

defined( 'ABSPATH' ) || exit;

/**
 * The «Instructor (Lenz+)» widget.
 */
final class Instructor_Box extends Course_Base {

	/** Photo frame of each look: ratio and `sizes`. */
	private const PHOTOS = array(
		'dark'    => array( '1/1', '150px' ),
		'profile' => array( '1/1', '(max-width: 767px) 100vw, 320px' ),
		'split'   => array( '4/5', '(max-width: 767px) 100vw, 45vw' ),
		'minimal' => array( '1/1', '96px' ),
	);

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-instructor-box';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Instructor', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-person';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'instructor', 'teacher', 'مدرس', 'استاد' ) );
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Instructor', 'lenz-plus' ) );

		$this->add_control(
			'look',
			array(
				'label'   => __( 'Look', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'dark',
				'options' => array(
					'dark'    => __( 'Ink card', 'lenz-plus' ),
					'profile' => __( 'Profile card with numbers', 'lenz-plus' ),
					'split'   => __( 'Large photo on a shape', 'lenz-plus' ),
					'minimal' => __( 'Compact row', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'source',
			array(
				'label'       => __( 'Instructor', 'lenz-plus' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'course',
				'options'     => array(
					'course' => __( 'The course\'s instructor (Lenz expert)', 'lenz-plus' ),
					'custom' => __( 'Typed here', 'lenz-plus' ),
				),
				'description' => __( 'With the course\'s instructor, the texts below fill in whatever the expert leaves empty.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'label',
			array(
				'label'   => __( 'Label', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Your instructor', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Heading', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'placeholder' => __( 'Who is the instructor?', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'name',
			array(
				'label'     => __( 'Name', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'role',
			array(
				'label'   => __( 'Position', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);

		$this->add_control(
			'bio',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 3,
				'default' => '',
			)
		);

		$this->add_photo_control( 'photo', __( 'Photo', 'lenz-plus' ), array( 'description' => __( 'For the large photo look, a cut-out photo (transparent PNG or WebP) sits best on the shape.', 'lenz-plus' ) ) );

		$this->add_control(
			'points',
			array(
				'label'       => __( 'Achievements', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 5,
				'default'     => '',
				'description' => __( 'One per line. Empty: the career lines of the Lenz expert.', 'lenz-plus' ),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'value',
			array(
				'label'   => __( 'Number', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);
		$repeater->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);

		$this->add_control(
			'stats',
			array(
				'label'         => __( 'Number tiles', 'lenz-plus' ),
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $repeater->get_controls(),
				'default'       => array(),
				'prevent_empty' => false,
				'title_field'   => '{{{ value }}} {{{ text }}}',
			)
		);

		$this->add_buttons_controls(
			array(
				'primary_text' => __( 'Read more about me', 'lenz-plus' ),
				'primary_url'  => '',
			)
		);

		$this->add_photo_tone_control();

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Card', 'lenz-plus' ) );

		$this->add_control(
			'accent',
			array(
				'label'       => __( 'Accent colour', 'lenz-plus' ),
				'type'        => Controls_Manager::COLOR,
				'description' => __( 'Heading, name and the shape behind the large photo.', 'lenz-plus' ),
				'selectors'   => array( '{{WRAPPER}} .lzp-instructor' => '--lzp-ins-accent: {{VALUE}};' ),
			)
		);

		$this->add_box_style( 'card', '.lzp-instructor' );
		$this->add_text_style( 'title', '.lzp-instructor__title', array( 'label' => __( 'Heading', 'lenz-plus' ) ) );
		$this->add_text_style( 'name', '.lzp-instructor__name', array( 'label' => __( 'Name', 'lenz-plus' ) ) );
		$this->add_text_style( 'bio', '.lzp-instructor__bio', array( 'label' => __( 'Text', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/**
	 * The person to show: the course's expert, its blanks filled from the
	 * typed texts (only the typed texts with source `custom`).
	 *
	 * @param array $s Settings.
	 * @return array{name:string, role:string, bio:string, photo:array, points:string[]}
	 */
	private static function person( array $s ): array {
		$typed  = array_values( array_filter( array_map( 'trim', preg_split( '/\R/u', (string) $s['points'] ) ), 'strlen' ) );
		$person = array(
			'name'   => trim( (string) $s['name'] ),
			'role'   => trim( (string) $s['role'] ),
			'bio'    => trim( (string) $s['bio'] ),
			'photo'  => (array) $s['photo'],
			'points' => $typed,
		);

		$id     = 'course' === $s['source'] ? Context::current_item( 'product' ) : 0;
		$expert = $id ? Course_Data::instructor( $id ) : null;
		if ( ! $expert ) {
			return $person;
		}

		foreach ( array( 'name', 'role', 'bio' ) as $key ) {
			if ( '' !== $expert[ $key ] ) {
				$person[ $key ] = $expert[ $key ];
			}
		}
		if ( $expert['photo'] ) {
			$person['photo'] = array( 'id' => $expert['photo'] );
		}
		if ( ! $typed ) {
			$person['points'] = $expert['careers'];
		}

		return $person;
	}

	/** Prints the box. */
	protected function render(): void {
		$s      = $this->get_settings_for_display();
		$look   = isset( self::PHOTOS[ $s['look'] ] ) ? (string) $s['look'] : 'dark';
		$person = self::person( $s );

		if ( '' === $person['name'] ) {
			$this->editor_hint( __( 'Choose the course\'s instructor in its Course details box (from Lenz\'s experts), or type a name here.', 'lenz-plus' ) );
			return;
		}

		$title    = trim( (string) $s['title'] );
		$name_tag = '' !== $title ? 'p' : 'h3';
		$identity = sprintf( '<%1$s class="lzp-instructor__name">%2$s</%1$s>', $name_tag, esc_html( $person['name'] ) )
			. ( '' !== $person['role'] ? '<p class="lzp-instructor__role">' . esc_html( $person['role'] ) . '</p>' : '' );

		$media = ( 'split' === $look ? '<span class="lzp-instructor__shape" aria-hidden="true"></span>' : '' )
			. Picture::frame(
				$person['photo'],
				array(
					'ratio' => self::PHOTOS[ $look ][0],
					'label' => __( 'Instructor photo', 'lenz-plus' ),
					'alt'   => $person['name'],
					'class' => 'lzp-photo lzp-instructor__photo',
					'sizes' => self::PHOTOS[ $look ][1],
				)
			)
			. ( 'profile' === $look ? '<div class="lzp-instructor__id">' . $identity . '</div>' : '' );

		$body = ( '' !== (string) $s['label'] ? '<span class="lzp-instructor__label">' . esc_html( $s['label'] ) . '</span>' : '' )
			. ( '' !== $title ? '<h3 class="lzp-instructor__title">' . esc_html( $title ) . '</h3>' : '' )
			. ( 'profile' !== $look ? '<div class="lzp-instructor__id">' . $identity . '</div>' : '' )
			. ( '' !== $person['bio'] ? '<p class="lzp-instructor__bio">' . esc_html( $person['bio'] ) . '</p>' : '' )
			. self::stats_html( (array) $s['stats'] )
			. self::points_html( $person['points'] )
			. $this->buttons_html( $s, array( 'primary', 'secondary' ) );

		printf(
			'<div class="lzp-instructor lzp-instructor--%1$s%2$s"><div class="lzp-instructor__media">%3$s</div><div class="lzp-instructor__body">%4$s</div></div>',
			esc_attr( $look ),
			'dark' === $look ? ' lzp-dark' : '',
			$media, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building and in Picture::frame().
			$body // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building and in buttons_html().
		);
	}

	/**
	 * Number tiles («+۹۰ محصول آموزشی»).
	 *
	 * @param array $rows Repeater rows (`value`, `text`).
	 */
	private static function stats_html( array $rows ): string {
		$html = '';
		foreach ( $rows as $row ) {
			$value = trim( (string) ( $row['value'] ?? '' ) );
			$text  = trim( (string) ( $row['text'] ?? '' ) );
			if ( '' === $value && '' === $text ) {
				continue;
			}
			$html .= '<li class="lzp-instructor__stat">'
				. ( '' !== $value ? '<span class="lzp-instructor__stat-value">' . esc_html( self::digits( $value ) ) . '</span>' : '' )
				. ( '' !== $text ? '<span class="lzp-instructor__stat-text">' . esc_html( self::digits( $text ) ) . '</span>' : '' )
				. '</li>';
		}

		return '' !== $html ? '<ul class="lzp-instructor__stats">' . $html . '</ul>' : '';
	}

	/**
	 * Achievement list.
	 *
	 * @param string[] $points Lines.
	 */
	private static function points_html( array $points ): string {
		$html = '';
		foreach ( $points as $point ) {
			$html .= '<li>' . esc_html( self::digits( $point ) ) . '</li>';
		}

		return '' !== $html ? '<ul class="lzp-instructor__points">' . $html . '</ul>' : '';
	}
}
