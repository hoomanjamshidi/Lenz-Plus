<?php
/**
 * «در این ورکشاپ چه چیزهایی یاد می‌گیری؟»: ticked items in dashed boxes, as
 * many 240px columns as fit, with an optional intro line («برای کسانی که:»)
 * and a note box under them. Items come from the course (what you learn,
 * who it is for) or are typed in the widget.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use LenzPlus\Modules\Builder\Context;
use LenzPlus\Modules\Builder\Course_Data;

defined( 'ABSPATH' ) || exit;

/**
 * The «Checklist grid (Lenz+)» widget.
 */
final class Checklist_Grid extends Course_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-checklist-grid';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Checklist grid', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-checkbox';
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Items', 'lenz-plus' ) );

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Items', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'learn',
				'options' => array(
					'learn'    => __( 'The course\'s "What you will learn"', 'lenz-plus' ),
					'audience' => __( 'The course\'s "Who it is for"', 'lenz-plus' ),
					'custom'   => __( 'Typed here', 'lenz-plus' ),
				),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Something you will learn', 'lenz-plus' ),
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
					array( 'text' => __( 'Something you will learn', 'lenz-plus' ) ),
					array( 'text' => __( 'Something you will learn', 'lenz-plus' ) ),
				),
				'title_field' => '{{ text }}',
				'condition'   => array( 'source' => 'custom' ),
			)
		);

		$this->add_control(
			'intro',
			array(
				'label'   => __( 'Line above', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);

		$this->add_control(
			'note',
			array(
				'label'       => __( 'Note below', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => '',
				'description' => __( 'With "Who it is for", empty shows the course\'s own note.', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Items', 'lenz-plus' ) );
		$this->add_text_style( 'text', '.lzp-tick__text', array( 'label' => __( 'Text', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the items. */
	protected function render(): void {
		$s    = $this->get_settings_for_display();
		$note = (string) $s['note'];

		if ( 'custom' === $s['source'] ) {
			$lines = wp_list_pluck( (array) $s['items'], 'text' );
		} else {
			$id = Context::current_item( 'product' );
			if ( ! $id || ! Course_Data::is_course( $id ) ) {
				$this->editor_hint( __( 'Shows the course being viewed. Mark a WooCommerce product as a course (Course details box) to see it here.', 'lenz-plus' ) );
				return;
			}
			$d     = Course_Data::details( $id );
			$lines = 'audience' === $s['source'] ? $d['audience'] : $d['learn'];
			if ( 'audience' === $s['source'] && '' === $note ) {
				$note = $d['audience_note'];
			}
		}

		$lines = array_filter( array_map( 'strval', $lines ), 'strlen' );
		if ( ! $lines ) {
			$this->editor_hint( __( 'Nothing to list yet: fill in the lists in the course\'s details.', 'lenz-plus' ) );
			return;
		}

		$items = '';
		foreach ( $lines as $line ) {
			$items .= '<li class="lzp-tick">' . self::icon( 'check-square', 'lzp-tick__icon' ) . '<span class="lzp-tick__text">' . esc_html( $line ) . '</span></li>';
		}

		printf(
			'<div class="lzp-ticks">%1$s<ul class="lzp-ticks__list">%2$s</ul>%3$s</div>',
			'' !== (string) $s['intro'] ? '<p class="lzp-ticks__intro">' . esc_html( $s['intro'] ) . '</p>' : '',
			$items, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			'' !== $note ? '<p class="lzp-ticks__note">' . self::icon( 'mobile', 'lzp-ticks__note-icon' ) . '<span>' . esc_html( $note ) . '</span></p>' : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped here.
		);
	}
}
