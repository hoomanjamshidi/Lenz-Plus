<?php
/**
 * «مدرس ورکشاپ»: an ink card with the instructor's photo, name, a short
 * bio and a button (to the about page). Reads the course's instructor
 * (Lenz's `expert` posts), or the texts typed in the widget.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Context;
use LenzPlus\Modules\Builder\Course_Data;
use LenzPlus\Modules\Builder\Elementor\Picture;

defined( 'ABSPATH' ) || exit;

/**
 * The «Instructor (Lenz+)» widget.
 */
final class Instructor_Box extends Course_Base {

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

	/** Content controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Instructor', 'lenz-plus' ) );

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Instructor', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'course',
				'options' => array(
					'course' => __( 'The course\'s instructor (Lenz expert)', 'lenz-plus' ),
					'custom' => __( 'Typed here', 'lenz-plus' ),
				),
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
			'name',
			array(
				'label'     => __( 'Name', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '',
				'condition' => array( 'source' => 'custom' ),
			)
		);

		$this->add_control(
			'bio',
			array(
				'label'     => __( 'Text', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXTAREA,
				'rows'      => 3,
				'default'   => '',
				'condition' => array( 'source' => 'custom' ),
			)
		);

		$this->add_photo_control( 'photo', __( 'Photo', 'lenz-plus' ), array( 'condition' => array( 'source' => 'custom' ) ) );

		$this->add_buttons_controls(
			array(
				'primary_text' => __( 'Read more about me', 'lenz-plus' ),
				'primary_url'  => '',
			)
		);

		$this->end_controls_section();
	}

	/** Prints the card. */
	protected function render(): void {
		$s = $this->get_settings_for_display();

		if ( 'custom' === $s['source'] ) {
			$person = array(
				'name'  => (string) $s['name'],
				'bio'   => (string) $s['bio'],
				'photo' => (array) $s['photo'],
			);
		} else {
			$id     = Context::current_item( 'product' );
			$expert = $id ? Course_Data::instructor( $id ) : null;
			if ( ! $expert ) {
				$this->editor_hint( __( 'Choose the course\'s instructor in its Course details box (from Lenz\'s experts), or type one here.', 'lenz-plus' ) );
				return;
			}
			$person = array(
				'name'  => $expert['name'],
				'bio'   => '' !== $expert['bio'] ? $expert['bio'] : $expert['role'],
				'photo' => array( 'id' => $expert['photo'] ),
			);
		}

		if ( '' === $person['name'] ) {
			return;
		}

		$photo = Picture::frame(
			$person['photo'],
			array(
				'ratio' => '1/1',
				'label' => __( 'Instructor photo', 'lenz-plus' ),
				'alt'   => $person['name'],
				'class' => 'lzp-photo lzp-instructor__photo',
				'sizes' => '150px',
			)
		);

		printf(
			'<div class="lzp-instructor lzp-dark">%1$s<div class="lzp-instructor__body">%2$s<h3 class="lzp-instructor__name">%3$s</h3>%4$s%5$s</div></div>',
			$photo, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Picture::frame().
			'' !== (string) $s['label'] ? '<span class="lzp-instructor__label">' . esc_html( $s['label'] ) . '</span>' : '',
			esc_html( $person['name'] ),
			'' !== $person['bio'] ? '<p class="lzp-instructor__bio">' . esc_html( $person['bio'] ) . '</p>' : '',
			$this->buttons_html( $s, array( 'primary', 'secondary' ) ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in buttons_html().
		);
	}
}
