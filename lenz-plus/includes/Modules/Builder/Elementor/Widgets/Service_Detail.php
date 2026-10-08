<?php
/**
 * One service of the services page, beside its photos: the numbered section
 * heading with a Latin subtitle («۰۱ عکاسی حرفه‌ای — PROFESSIONAL
 * PHOTOGRAPHY»), a paragraph, a «مناسب برای» row of chips and optional
 * buttons. The photos are one frame, a staggered pair, or a narrow pair of
 * tall frames (reels); they sit at either end of the row, and come after
 * the text on phones.
 *
 * On an ink container the heading, text, chips and buttons switch to their
 * light versions through the surface tokens.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use LenzPlus\Modules\Builder\Elementor\Picture;

defined( 'ABSPATH' ) || exit;

/**
 * The «Service (Lenz+)» widget.
 */
final class Service_Detail extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-service-detail';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Service', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-image-box';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'service', 'feature', 'خدمت', 'سرویس' ) );
	}

	/** Content (texts, chips, buttons, photos) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Texts', 'lenz-plus' ) );

		$this->add_control(
			'index',
			array(
				'label'   => __( 'Number', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '01',
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Professional photography', 'lenz-plus' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'latin',
			array(
				'label'       => __( 'Latin subtitle', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'PROFESSIONAL PHOTOGRAPHY',
				'label_block' => true,
			)
		);

		$this->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 4,
				'default' => __( 'What this service covers and how it is done.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'chips_title',
			array(
				'label'     => __( 'Chips title', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Good for', 'lenz-plus' ),
				'separator' => 'before',
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Portraits', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'chips',
			array(
				'label'       => __( 'Chips', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'text' => __( 'Portraits', 'lenz-plus' ) ),
					array( 'text' => __( 'Products', 'lenz-plus' ) ),
					array( 'text' => __( 'Brands', 'lenz-plus' ) ),
				),
				'title_field' => '{{{ text }}}',
			)
		);

		$this->add_buttons_controls( array() );
		$this->end_controls_section();

		$this->start_content_section( 'section_photos', __( 'Photos', 'lenz-plus' ) );

		$this->add_control(
			'media',
			array(
				'label'   => __( 'Photos', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'pair',
				'options' => array(
					'single' => __( 'One photo', 'lenz-plus' ),
					'pair'   => __( 'Two staggered photos', 'lenz-plus' ),
					'tall'   => __( 'Two tall photos (reels)', 'lenz-plus' ),
					'none'   => __( 'No photos', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'media_side',
			array(
				'label'     => __( 'Photos at the', 'lenz-plus' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'end',
				'options'   => array(
					'end'   => __( 'End', 'lenz-plus' ),
					'start' => __( 'Start', 'lenz-plus' ),
				),
				'condition' => array( 'media!' => 'none' ),
			)
		);

		foreach ( array( 1, 2 ) as $n ) {
			$condition = 1 === $n ? array( 'media!' => 'none' ) : array( 'media' => array( 'pair', 'tall' ) );

			$this->add_photo_control(
				'image_' . $n,
				/* translators: %d: photo number. */
				sprintf( __( 'Photo %d', 'lenz-plus' ), $n ),
				array(
					'condition' => $condition,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'label_' . $n,
				array(
					'label'     => __( 'Placeholder text', 'lenz-plus' ),
					'type'      => Controls_Manager::TEXT,
					'default'   => '',
					'condition' => $condition,
				)
			);
		}

		$this->add_ratio_control(
			'ratio',
			'4/3',
			array(
				'condition'   => array( 'media' => 'single' ),
				'description' => __( 'Two photos use 3:4, tall photos 9:16.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'watermark',
			array(
				'label'       => __( 'Outline word', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'separator'   => 'before',
				'description' => __( 'A large outlined word above the section, as on the dark band of the mockups.', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Texts', 'lenz-plus' ) );
		$this->add_text_style( 'title', '.lzp-heading__text', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'text', '.lzp-svc__text', array( 'label' => __( 'Text', 'lenz-plus' ) ) );
		$this->add_text_style( 'chips_title', '.lzp-svc__chips-title', array( 'label' => __( 'Chips title', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the service. */
	protected function render(): void {
		$s = $this->get_settings_for_display();

		$text = Heading::markup(
			array(
				'title' => (string) $s['title'],
				'index' => (string) $s['index'],
				'latin' => (string) $s['latin'],
			)
		);

		if ( '' !== (string) $s['text'] ) {
			$text .= '<p class="lzp-svc__text">' . esc_html( $s['text'] ) . '</p>';
		}

		if ( ! empty( $s['chips'] ) ) {
			$text .= '<div class="lzp-svc__chips">';
			if ( '' !== (string) $s['chips_title'] ) {
				$text .= '<span class="lzp-svc__chips-title">' . esc_html( $s['chips_title'] ) . '</span>';
			}
			$text .= '<ul class="lzp-svc__chip-list">';
			foreach ( $s['chips'] as $chip ) {
				$text .= '<li class="lzp-chip lzp-chip--outline">' . esc_html( (string) $chip['text'] ) . '</li>';
			}
			$text .= '</ul></div>';
		}

		$text .= $this->buttons_html( $s, array( 'primary', 'secondary' ) );

		$media = $this->media_html( $s );
		$decor = '' !== (string) $s['watermark'] ? '<span class="lzp-watermark lzp-svc__watermark" aria-hidden="true">' . esc_html( $s['watermark'] ) . '</span>' : '';

		printf(
			'<div class="lzp-svc%1$s%2$s">%3$s<div class="lzp-svc__body">%4$s</div>%5$s</div>',
			'' === $media ? ' lzp-svc--text' : '',
			'start' === $s['media_side'] ? ' lzp-svc--media-start' : '',
			$decor, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			$text, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			$media // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Picture::frame().
		);
	}

	/**
	 * The photo column.
	 *
	 * @param array $s Settings.
	 */
	private function media_html( array $s ): string {
		$mode = (string) $s['media'];
		if ( ! in_array( $mode, array( 'single', 'pair', 'tall' ), true ) ) {
			return '';
		}

		$count  = 'single' === $mode ? 1 : 2;
		$ratio  = 'single' === $mode ? (string) $s['ratio'] : ( 'tall' === $mode ? '9/16' : '3/4' );
		$frames = '';

		for ( $n = 1; $n <= $count; $n++ ) {
			$frames .= Picture::frame(
				(array) $s[ 'image_' . $n ],
				array(
					'ratio' => $ratio,
					'label' => (string) $s[ 'label_' . $n ],
					'alt'   => (string) $s['title'],
					'class' => 'lzp-photo',
					'sizes' => 1 === $count ? '(max-width: 767px) 100vw, 50vw' : '(max-width: 767px) 50vw, 25vw',
				)
			);
		}

		return '<div class="lzp-svc__media lzp-svc__media--' . esc_attr( $mode ) . '">' . $frames . '</div>';
	}
}
