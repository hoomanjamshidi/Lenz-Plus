<?php
/**
 * A row of photos with short captions (the services page's ritual photos:
 * «مراسم عزاداری», «حرم رضوی»…), as many 170px frames as fit in a row.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use LenzPlus\Modules\Builder\Elementor\Picture;

defined( 'ABSPATH' ) || exit;

/**
 * The «Photo row (Lenz+)» widget.
 */
final class Photo_Grid extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-photo-grid';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Photo row', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-gallery-grid';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'photos', 'gallery', 'images', 'عکس', 'گالری' ) );
	}

	/** Content (photos, shape) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Photos', 'lenz-plus' ) );

		$repeater = new Repeater();
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
		$repeater->add_control(
			'caption',
			array(
				'label'   => __( 'Caption', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Photos', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'caption' => __( 'Caption', 'lenz-plus' ) ),
					array( 'caption' => __( 'Caption', 'lenz-plus' ) ),
					array( 'caption' => __( 'Caption', 'lenz-plus' ) ),
					array( 'caption' => __( 'Caption', 'lenz-plus' ) ),
				),
				'title_field' => '{{ caption }}',
			)
		);

		$this->add_ratio_control( 'ratio', '3/4' );
		$this->add_columns_control( '.lzp-photos', array( 4, 2, 2 ) );
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Photos', 'lenz-plus' ) );
		$this->add_gap_control( '.lzp-photos' );
		$this->add_text_style( 'caption', '.lzp-photos__caption', array( 'label' => __( 'Caption', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the photos. */
	protected function render(): void {
		$s = $this->get_settings_for_display();
		if ( empty( $s['items'] ) ) {
			return;
		}

		echo '<ul class="lzp-photos lzp-grid">';
		foreach ( $s['items'] as $item ) {
			$caption = (string) $item['caption'];
			$frame   = Picture::frame(
				(array) $item['image'],
				array(
					'ratio' => (string) $s['ratio'],
					'alt'   => $caption,
					'class' => 'lzp-photo',
					'sizes' => '(max-width: 767px) 50vw, 25vw',
				)
			);

			printf(
				'<li class="lzp-photos__item"><figure class="lzp-photos__figure">%1$s%2$s</figure></li>',
				$frame, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Picture::frame().
				'' !== $caption ? '<figcaption class="lzp-photos__caption">' . esc_html( $caption ) . '</figcaption>' : ''
			);
		}
		echo '</ul>';
	}
}
