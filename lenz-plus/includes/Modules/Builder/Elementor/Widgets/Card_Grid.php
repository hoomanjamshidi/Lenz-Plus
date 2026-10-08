<?php
/**
 * Grid of small cards: awards (trophy icon, a tag such as «ملی», the rank
 * and the festival), collaborations (name and role) and areas of work
 * (title and text on dark cards). Every part except the title is optional,
 * so one widget covers the mockups' card rows; dark cards come from the
 * container's dark surface.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * The «Cards (Lenz+)» widget.
 */
final class Card_Grid extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-card-grid';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Cards', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-gallery-grid';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'cards', 'awards', 'clients', 'کارت', 'جوایز', 'افتخارات', 'همکاری' ) );
	}

	/** Content (cards, look) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Cards', 'lenz-plus' ) );

		$this->add_list_title_control();

		$repeater = new Repeater();
		$repeater->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => self::icon_options(),
			)
		);
		$repeater->add_control(
			'tag',
			array(
				'label'   => __( 'Tag', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);
		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Card title', 'lenz-plus' ),
				'label_block' => true,
			)
		);
		$repeater->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => '',
			)
		);
		$repeater->add_control(
			'link',
			array(
				'label'   => __( 'Link', 'lenz-plus' ),
				'type'    => Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Cards', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'icon'  => 'trophy',
						'tag'   => __( 'International', 'lenz-plus' ),
						'title' => __( 'First place', 'lenz-plus' ),
						'text'  => __( 'Name of the festival', 'lenz-plus' ),
					),
					array(
						'icon'  => 'trophy',
						'tag'   => __( 'National', 'lenz-plus' ),
						'title' => __( 'Second place', 'lenz-plus' ),
						'text'  => __( 'Name of the festival', 'lenz-plus' ),
					),
					array(
						'icon'  => 'trophy',
						'tag'   => __( 'National', 'lenz-plus' ),
						'title' => __( 'Selected work', 'lenz-plus' ),
						'text'  => __( 'Name of the festival', 'lenz-plus' ),
					),
				),
				'title_field' => '{{ title }}',
			)
		);

		$this->add_control(
			'size',
			array(
				'label'   => __( 'Size', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'md',
				'options' => array(
					'md' => __( 'Medium', 'lenz-plus' ),
					'sm' => __( 'Small', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'   => __( 'Title tag', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => array(
					'h3' => 'H3',
					'h4' => 'H4',
					'p'  => 'p',
				),
			)
		);

		$this->add_columns_control( '.lzp-cards', array( 4, 2, 1 ) );
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Cards', 'lenz-plus' ) );
		$this->add_gap_control( '.lzp-cards' );
		$this->add_box_style( 'card', '.lzp-cards__item' );

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .lzp-cards__icon' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_text_style( 'tag', '.lzp-cards__tag', array( 'label' => __( 'Tag', 'lenz-plus' ) ) );
		$this->add_text_style( 'title', '.lzp-cards__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'text', '.lzp-cards__text', array( 'label' => __( 'Text', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the cards. */
	protected function render(): void {
		$s = $this->get_settings_for_display();
		if ( empty( $s['items'] ) ) {
			return;
		}

		$tag = in_array( $s['title_tag'], array( 'h3', 'h4', 'p' ), true ) ? $s['title_tag'] : 'h3';

		echo self::list_title_html( $s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in list_title_html().
		echo '<ul class="lzp-cards lzp-grid lzp-cards--' . esc_attr( 'sm' === $s['size'] ? 'sm' : 'md' ) . '">';
		foreach ( $s['items'] as $index => $item ) {
			$top = '';
			if ( '' !== (string) $item['icon'] || '' !== (string) $item['tag'] ) {
				$top = '<div class="lzp-cards__top">'
					. ( '' !== (string) $item['icon'] ? self::icon( (string) $item['icon'], 'lzp-cards__icon' ) : '' )
					. ( '' !== (string) $item['tag'] ? '<span class="lzp-cards__tag">' . esc_html( self::digits( (string) $item['tag'] ) ) . '</span>' : '' )
					. '</div>';
			}

			$title = esc_html( (string) $item['title'] );
			$link  = $this->link_attrs( 'card-' . $index, $item['link'] ?? array() );
			if ( '' !== $link ) {
				$title = '<a class="lzp-cards__link"' . $link . '>' . $title . '</a>';
			}

			printf(
				'<li class="lzp-cards__item">%1$s<%2$s class="lzp-cards__title">%3$s</%2$s>%4$s</li>',
				$top, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
				esc_attr( $tag ),
				$title, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
				'' !== (string) $item['text'] ? '<p class="lzp-cards__text">' . esc_html( (string) $item['text'] ) . '</p>' : ''
			);
		}
		echo '</ul>';
	}
}
