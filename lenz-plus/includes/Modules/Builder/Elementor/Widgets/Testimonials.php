<?php
/**
 * Client testimonials («نظر مشتری‌ها»): white cards with a quote mark, the
 * client's words, their name and the kind of project, and a small 3:4 photo
 * at the end of the card. Several cards fit in a row and stack on phones.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use LenzPlus\Modules\Builder\Elementor\Picture;

defined( 'ABSPATH' ) || exit;

/**
 * The «Testimonials (Lenz+)» widget.
 */
final class Testimonials extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-testimonials';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Testimonials', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-testimonial';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'testimonials', 'reviews', 'clients', 'نظر', 'مشتری', 'رضایت' ) );
	}

	/** Content (testimonials) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Testimonials', 'lenz-plus' ) );

		$repeater = new Repeater();
		$repeater->add_control(
			'text',
			array(
				'label'   => __( 'What they said', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 4,
				'default' => __( 'A few sentences from a happy client.', 'lenz-plus' ),
			)
		);
		$repeater->add_control(
			'name',
			array(
				'label'   => __( 'Name', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Client name', 'lenz-plus' ),
			)
		);
		$repeater->add_control(
			'role',
			array(
				'label'   => __( 'Project', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);
		$repeater->add_control(
			'image',
			array(
				'label'   => __( 'Photo', 'lenz-plus' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
				'default' => array(
					'url' => '',
					'id'  => '',
				),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Testimonials', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array( array(), array(), array() ),
				'title_field' => '{{{ name }}}',
			)
		);

		$this->add_control(
			'photos',
			array(
				'label'        => __( 'Photos', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => __( 'Without a chosen photo the card shows an empty frame.', 'lenz-plus' ),
			)
		);

		$this->add_columns_control( '.lzp-testimonials', array( 3, 2, 1 ) );
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Cards', 'lenz-plus' ) );
		$this->add_gap_control( '.lzp-testimonials' );
		$this->add_box_style( 'card', '.lzp-testimonial' );
		$this->add_text_style( 'text', '.lzp-testimonial__text', array( 'label' => __( 'Text', 'lenz-plus' ) ) );
		$this->add_text_style( 'name', '.lzp-testimonial__name', array( 'label' => __( 'Name', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the cards. */
	protected function render(): void {
		$s = $this->get_settings_for_display();
		if ( empty( $s['items'] ) ) {
			return;
		}

		echo '<ul class="lzp-testimonials lzp-grid">';
		foreach ( $s['items'] as $item ) {
			$photo = '';
			if ( 'yes' === $s['photos'] ) {
				$photo = Picture::frame(
					(array) $item['image'],
					array(
						'ratio' => '3/4',
						'alt'   => (string) $item['name'],
						'class' => 'lzp-photo lzp-testimonial__photo',
					)
				);
			}

			printf(
				'<li class="lzp-testimonial-cell"><figure class="lzp-testimonial%1$s"><div class="lzp-testimonial__body">%2$s<blockquote class="lzp-testimonial__text">%3$s</blockquote><figcaption class="lzp-testimonial__by"><span class="lzp-testimonial__name">%4$s</span>%5$s</figcaption></div>%6$s</figure></li>',
				'' !== $photo ? ' has-photo' : '',
				self::icon( 'quote', 'lzp-testimonial__mark' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup from the bundled library.
				esc_html( (string) $item['text'] ),
				esc_html( (string) $item['name'] ),
				'' !== (string) $item['role'] ? '<span class="lzp-testimonial__role">' . esc_html( (string) $item['role'] ) . '</span>' : '', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped here.
				$photo // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Picture::frame().
			);
		}
		echo '</ul>';
	}
}
