<?php
/**
 * Top section of the inner pages: a small label, the page title, a lead
 * paragraph, optional dashed fact chips and two buttons, with a photo at the
 * end (split layout: about, services) or text only (portfolio, blog,
 * courses). Behind it sit the mockups' dashed guide lines and a huge
 * outline word (BEHIND THE CAMERA, LEARN THE LIGHT…).
 *
 * The widget spans the whole width and boxes its own content, so the guide
 * lines and the outline word reach the edges of the screen as in the
 * mockups: place it in a full-width container without padding.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use LenzPlus\Modules\Builder\Elementor\Picture;

defined( 'ABSPATH' ) || exit;

/**
 * The «Page hero (Lenz+)» widget.
 */
final class Page_Hero extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-page-hero';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Page hero', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-header';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'hero', 'banner', 'title', 'هیرو', 'بنر' ) );
	}

	/** Content (texts, chips, buttons, photo, decoration) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Texts', 'lenz-plus' ) );

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'split',
				'options' => array(
					'split' => __( 'Text and photo', 'lenz-plus' ),
					'text'  => __( 'Text only', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'eyebrow',
			array(
				'label'   => __( 'Label above the title', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'About me', 'lenz-plus' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => __( 'A story told with light', 'lenz-plus' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'tag',
			array(
				'label'   => __( 'HTML tag', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h1',
				'options' => array(
					'h1' => 'H1',
					'h2' => 'H2',
				),
			)
		);

		$this->add_control(
			'lead',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 4,
				'default' => __( 'A short paragraph that introduces this page.', 'lenz-plus' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'A short fact', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'chips',
			array(
				'label'       => __( 'Facts (chips)', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(),
				'title_field' => '{{{ text }}}',
			)
		);

		$this->add_buttons_controls(
			array(
				'primary_text' => __( 'Book a session', 'lenz-plus' ),
				'primary_url'  => '#',
			)
		);

		$this->end_controls_section();

		$this->start_content_section(
			'section_photo',
			__( 'Photo', 'lenz-plus' ),
			array( 'condition' => array( 'layout' => 'split' ) )
		);
		$this->add_photo_control( 'image', __( 'Photo', 'lenz-plus' ) );
		$this->add_ratio_control( 'ratio', '4/5' );

		$this->add_control(
			'image_label',
			array(
				'label'       => __( 'Placeholder text', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'Shown in the empty frame until a photo is chosen.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'notch',
			array(
				'label'        => __( 'Corner notch', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_content_section( 'section_decor', __( 'Decoration', 'lenz-plus' ) );

		$this->add_control(
			'watermark',
			array(
				'label'       => __( 'Outline word', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'BEHIND THE CAMERA',
				'label_block' => true,
				'description' => __( 'The large outlined word behind the section. Leave empty to hide it.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'guides',
			array(
				'label'        => __( 'Guide lines', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => __( 'Also follows the global "Guide lines" option of Page templates.', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Texts', 'lenz-plus' ) );
		$this->add_text_style( 'eyebrow', '.lzp-hero__eyebrow', array( 'label' => __( 'Label', 'lenz-plus' ) ) );
		$this->add_text_style( 'title', '.lzp-hero__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'lead', '.lzp-hero__lead', array( 'label' => __( 'Text', 'lenz-plus' ) ) );

		$this->add_responsive_control(
			'pad_y',
			array(
				'label'      => __( 'Space above and below', 'lenz-plus' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'separator'  => 'before',
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 160,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .lzp-hero' => 'padding-block: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/** Prints the hero. */
	protected function render(): void {
		$s     = $this->get_settings_for_display();
		$split = 'split' === $s['layout'];
		$tag   = 'h2' === $s['tag'] ? 'h2' : 'h1';

		$text = '';
		if ( '' !== (string) $s['eyebrow'] ) {
			$text .= '<span class="lzp-hero__eyebrow">' . esc_html( $s['eyebrow'] ) . '</span>';
		}
		$text .= sprintf( '<%1$s class="lzp-hero__title">%2$s</%1$s>', $tag, esc_html( (string) $s['title'] ) );
		if ( '' !== (string) $s['lead'] ) {
			$text .= '<p class="lzp-hero__lead">' . esc_html( $s['lead'] ) . '</p>';
		}

		if ( ! empty( $s['chips'] ) ) {
			$text .= '<ul class="lzp-hero__chips">';
			foreach ( $s['chips'] as $chip ) {
				$text .= '<li class="lzp-chip lzp-chip--dashed">' . esc_html( self::digits( (string) $chip['text'] ) ) . '</li>';
			}
			$text .= '</ul>';
		}

		$text .= $this->buttons_html( $s, array( 'primary', 'secondary' ) );

		$media = '';
		if ( $split ) {
			$media = '<div class="lzp-hero__media' . ( 'yes' === $s['notch'] ? ' lzp-notch lzp-notch--side' : '' ) . '">'
				. Picture::frame(
					(array) $s['image'],
					array(
						'ratio'   => (string) $s['ratio'],
						'label'   => (string) $s['image_label'],
						'alt'     => (string) $s['title'],
						'loading' => 'high',
						'class'   => 'lzp-photo',
					)
				)
				. '</div>';
		}

		$decor = '';
		if ( 'yes' === $s['guides'] ) {
			$decor .= '<span class="lzp-hero__guides" aria-hidden="true"></span>';
		}
		if ( '' !== (string) $s['watermark'] ) {
			$decor .= '<span class="lzp-watermark lzp-hero__watermark" aria-hidden="true">' . esc_html( $s['watermark'] ) . '</span>';
		}

		printf(
			'<div class="lzp-hero lzp-hero--%1$s">%2$s<div class="lzp-hero__inner"><div class="lzp-hero__text">%3$s</div>%4$s</div></div>',
			esc_attr( $split ? 'split' : 'text' ),
			$decor, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			$text, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			$media // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Picture::frame().
		);
	}
}
