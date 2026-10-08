<?php
/**
 * The home page's service cards («خدمات من»): dashed cards with an accent
 * icon, the service name, its Latin name (Professional Photography), a rule,
 * a short text and a square arrow button that links to the service.
 *
 * The whole card is clickable through the arrow link's stretched hit area,
 * so the markup keeps a single link per card for screen readers.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * The «Service cards (Lenz+)» widget.
 */
final class Service_Cards extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-service-cards';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Service cards', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-info-box';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'services', 'cards', 'خدمات', 'کارت' ) );
	}

	/** Content (cards) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Services', 'lenz-plus' ) );

		$repeater = new Repeater();
		$repeater->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'camera',
				'options' => self::icon_options(),
			)
		);
		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Service name', 'lenz-plus' ),
				'label_block' => true,
			)
		);
		$repeater->add_control(
			'latin',
			array(
				'label'       => __( 'Latin name', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);
		$repeater->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 3,
				'default' => __( 'A short description of the service.', 'lenz-plus' ),
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
				'label'       => __( 'Services', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'icon' => 'camera' ),
					array( 'icon' => 'video' ),
					array( 'icon' => 'stadium' ),
				),
				'title_field' => '{{{ title }}}',
			)
		);

		$this->add_columns_control( '.lzp-services', array( 4, 2, 1 ) );
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Cards', 'lenz-plus' ) );
		$this->add_gap_control( '.lzp-services' );
		$this->add_box_style( 'card', '.lzp-service' );

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .lzp-service__icon' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_text_style( 'title', '.lzp-service__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'text', '.lzp-service__text', array( 'label' => __( 'Text', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the cards. */
	protected function render(): void {
		$s = $this->get_settings_for_display();
		if ( empty( $s['items'] ) ) {
			return;
		}

		echo '<ul class="lzp-services lzp-grid">';
		foreach ( $s['items'] as $index => $item ) {
			$link  = $this->link_attrs( 'service-' . $index, $item['link'] ?? array() );
			$title = esc_html( (string) $item['title'] );

			$html = ( '' !== (string) $item['icon'] ? self::icon( (string) $item['icon'], 'lzp-service__icon' ) : '' )
				. '<div class="lzp-service__names"><h3 class="lzp-service__title">' . $title . '</h3>'
				. ( '' !== (string) $item['latin'] ? '<span class="lzp-service__latin" dir="ltr">' . esc_html( (string) $item['latin'] ) . '</span>' : '' )
				. '</div>'
				. ( '' !== (string) $item['text'] ? '<p class="lzp-service__text">' . esc_html( (string) $item['text'] ) . '</p>' : '' );

			if ( '' !== $link ) {
				$html .= '<a class="lzp-service__more"' . $link . '><span class="screen-reader-text">' . $title . '</span>' . self::icon( 'arrow-forward' ) . '</a>';
			}

			echo '<li class="lzp-service' . ( '' !== $link ? ' has-link' : '' ) . '">' . $html . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		}
		echo '</ul>';
	}
}
