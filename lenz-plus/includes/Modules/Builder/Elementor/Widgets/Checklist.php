<?php
/**
 * A checklist with Lenz's ticked-square glyph («آنچه انجام شد»): the
 * project's "What was done" lines, or items typed in the widget.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use LenzPlus\Modules\Builder\Context;
use LenzPlus\Modules\Builder\Portfolio_Data;

defined( 'ABSPATH' ) || exit;

/**
 * The «Checklist (Lenz+)» widget.
 */
final class Checklist extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-checklist';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Checklist', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-checkbox';
	}

	/** The project source reads the viewed post. */
	protected function is_dynamic_content(): bool {
		return true;
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'checklist', 'list', 'check', 'چک لیست', 'فهرست' ) );
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Items', 'lenz-plus' ) );

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Items', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'custom',
				'options' => array(
					'custom'  => __( 'Typed here', 'lenz-plus' ),
					'project' => __( 'The project\'s "What was done"', 'lenz-plus' ),
				),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'A finished step', 'lenz-plus' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Items', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'text' => __( 'A finished step', 'lenz-plus' ) ),
					array( 'text' => __( 'A finished step', 'lenz-plus' ) ),
				),
				'title_field' => '{{{ text }}}',
				'condition'   => array( 'source' => 'custom' ),
			)
		);

		$this->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'check-square',
				'options' => self::icon_options(),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Items', 'lenz-plus' ) );
		$this->add_text_style( 'text', '.lzp-check__item', array( 'label' => __( 'Text', 'lenz-plus' ) ) );

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lzp-check__icon' => 'color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/** Prints the list. */
	protected function render(): void {
		$s = $this->get_settings_for_display();

		if ( 'project' === $s['source'] ) {
			$post_id = Context::current_item( Portfolio_Data::POST_TYPE );
			$lines   = $post_id ? Portfolio_Data::details( $post_id )['done'] : array();
		} else {
			$lines = wp_list_pluck( (array) $s['items'], 'text' );
		}

		$lines = array_filter( array_map( 'strval', $lines ), 'strlen' );
		if ( ! $lines ) {
			$this->editor_hint( __( 'Nothing to list: fill in "What was done" in the project\'s details.', 'lenz-plus' ) );
			return;
		}

		echo '<ul class="lzp-check">';
		foreach ( $lines as $line ) {
			printf(
				'<li class="lzp-check__item">%1$s<span>%2$s</span></li>',
				'' !== (string) $s['icon'] ? self::icon( (string) $s['icon'], 'lzp-check__icon' ) : '', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup from the bundled library.
				esc_html( $line )
			);
		}
		echo '</ul>';
	}
}
