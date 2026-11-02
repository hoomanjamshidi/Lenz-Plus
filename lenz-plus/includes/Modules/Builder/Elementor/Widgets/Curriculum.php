<?php
/**
 * «سرفصل‌های ورکشاپ»: the course's sessions as an accordion (marker tile,
 * title, what it covers). The tile holds the session number, an icon (from
 * Elementor's library or an uploaded SVG; one for all sessions or one per
 * session) or nothing. Built on <details>, so it opens without JavaScript.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use LenzPlus\Modules\Builder\Course_Data;

defined( 'ABSPATH' ) || exit;

/**
 * The «Curriculum (Lenz+)» widget.
 */
final class Curriculum extends Course_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-curriculum';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Curriculum', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-accordion';
	}

	/** Content controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Curriculum', 'lenz-plus' ) );

		$this->add_control(
			'open_first',
			array(
				'label'        => __( 'First session open', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'marker',
			array(
				'label'     => __( 'Before each title', 'lenz-plus' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'number',
				'options'   => array(
					'number' => __( 'Session number', 'lenz-plus' ),
					'icon'   => __( 'Icon', 'lenz-plus' ),
					'none'   => __( 'Nothing', 'lenz-plus' ),
				),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'icon',
			array(
				'label'       => __( 'Icon', 'lenz-plus' ),
				'type'        => Controls_Manager::ICONS,
				'default'     => array(
					'value'   => 'fas fa-check',
					'library' => 'fa-solid',
				),
				'description' => __( 'From Elementor\'s icon library, or upload your own SVG.', 'lenz-plus' ),
				'condition'   => array( 'marker' => 'icon' ),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'icon',
			array(
				'label' => __( 'Icon', 'lenz-plus' ),
				'type'  => Controls_Manager::ICONS,
			)
		);

		$this->add_control(
			'icons',
			array(
				'label'         => __( 'Icon per session', 'lenz-plus' ),
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $repeater->get_controls(),
				'default'       => array(),
				'prevent_empty' => false,
				'title_field'   => '{{{ elementor.helpers.renderIcon( this, icon, {}, "i", "panel" ) || "—" }}}',
				'description'   => __( 'Optional. The first row is the first session\'s icon, and so on; sessions without a row use the icon above.', 'lenz-plus' ),
				'condition'     => array( 'marker' => 'icon' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Sessions', 'lenz-plus' ) );
		$this->add_box_style( 'item', '.lzp-faq__item' );
		$this->add_text_style( 'title', '.lzp-faq__question', array( 'label' => __( 'Title', 'lenz-plus' ) ) );

		$this->add_control(
			'marker_color',
			array(
				'label'     => __( 'Marker colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lzp-curriculum__num' => 'color: {{VALUE}};' ),
				'condition' => array( 'marker!' => 'none' ),
			)
		);

		$this->add_control(
			'marker_bg',
			array(
				'label'     => __( 'Marker background', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lzp-curriculum__num' => 'background: {{VALUE}};' ),
				'condition' => array( 'marker!' => 'none' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * The tile before a session's title: its number, its icon, or nothing.
	 *
	 * @param array $s     Settings.
	 * @param int   $index Session index (0-based).
	 */
	private static function marker_html( array $s, int $index ): string {
		if ( 'none' === $s['marker'] ) {
			return '';
		}

		if ( 'icon' === $s['marker'] ) {
			$icons = is_array( $s['icons'] ) ? array_values( $s['icons'] ) : array();
			$icon  = self::elementor_icon_html( $icons[ $index ]['icon'] ?? null );
			if ( '' === $icon ) {
				$icon = self::elementor_icon_html( $s['icon'] );
			}

			return '' !== $icon ? '<span class="lzp-curriculum__num lzp-curriculum__num--icon">' . $icon . '</span>' : '';
		}

		return '<span class="lzp-curriculum__num">' . esc_html( self::digits( (string) ( $index + 1 ) ) ) . '</span>';
	}

	/** Prints the sessions. */
	protected function render(): void {
		$s  = $this->get_settings_for_display();
		$id = $this->current_course();
		if ( ! $id ) {
			return;
		}

		$sessions = Course_Data::details( $id )['curriculum'];
		if ( ! $sessions ) {
			$this->editor_hint( __( 'Nothing to list yet: fill in the curriculum in the course\'s details.', 'lenz-plus' ) );
			return;
		}

		echo '<div class="lzp-faq lzp-curriculum">';
		foreach ( array_values( $sessions ) as $index => $session ) {
			printf(
				'<details class="lzp-faq__item"%1$s><summary class="lzp-faq__q">%2$s<span class="lzp-faq__question">%3$s</span>%4$s</summary>%5$s</details>',
				0 === $index && 'yes' === $s['open_first'] ? ' open' : '',
				self::marker_html( $s, $index ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in marker_html() (icons by Elementor).
				esc_html( (string) $session[0] ),
				self::icon( 'chevron-down', 'lzp-faq__sign' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup from the bundled library.
				'' !== (string) $session[1] ? '<div class="lzp-faq__a">' . esc_html( $session[1] ) . '</div>' : ''
			);
		}
		echo '</div>';
	}
}
