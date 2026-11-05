<?php
/**
 * «در این ورکشاپ چه چیزهایی یاد می‌گیری؟»: ticked items in one of several
 * looks (soft cards, accent cards, lined rows, chips, dashed boxes or a plain
 * list), with an optional intro line («برای کسانی که:») and a note box under
 * them. Items come from the course (what you learn, who it is and is not for)
 * or are typed in the widget; the mark is a tick, a cross for «who it is not
 * for», or an Elementor icon, which every item can override.
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
					'not_for'  => __( 'The course\'s "Who it is not for"', 'lenz-plus' ),
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
		$repeater->add_control(
			'icon',
			array(
				'label'       => __( 'Own icon', 'lenz-plus' ),
				'type'        => Controls_Manager::ICONS,
				'description' => __( 'Optional. Empty uses the mark chosen below.', 'lenz-plus' ),
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
			'look',
			array(
				'label'   => __( 'Look', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'soft',
				'options' => self::looks(),
			)
		);

		$this->add_control(
			'mark',
			array(
				'label'   => __( 'Mark', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'  => __( 'Automatic (a cross for "Who it is not for")', 'lenz-plus' ),
					'check' => __( 'Tick', 'lenz-plus' ),
					'cross' => __( 'Cross', 'lenz-plus' ),
					'icon'  => __( 'Icon', 'lenz-plus' ),
				),
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
				'condition'   => array( 'mark' => 'icon' ),
			)
		);

		$icon_row = new Repeater();
		$icon_row->add_control(
			'icon',
			array(
				'label' => __( 'Icon', 'lenz-plus' ),
				'type'  => Controls_Manager::ICONS,
			)
		);

		// Course lists are typed in the product, so their icons go by position.
		$this->add_control(
			'icons',
			array(
				'label'         => __( 'Icon per item', 'lenz-plus' ),
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $icon_row->get_controls(),
				'default'       => array(),
				'prevent_empty' => false,
				'title_field'   => '{{{ elementor.helpers.renderIcon( this, icon, {}, "i", "panel" ) || "—" }}}',
				'description'   => __( 'Optional. The first row is the first item\'s icon, and so on; items without a row use the mark above.', 'lenz-plus' ),
				'condition'     => array( 'source!' => 'custom' ),
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

		$this->add_control(
			'mark_color',
			array(
				'label'     => __( 'Marker colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lzp-ticks' => '--lzp-mark: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'mark_ink',
			array(
				'label'       => __( 'Icon colour on the tile', 'lenz-plus' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array( '{{WRAPPER}} .lzp-ticks' => '--lzp-mark-ink: {{VALUE}};' ),
				'description' => __( 'For the looks whose marker sits on a filled tile.', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Look id → label.
	 *
	 * @return array<string, string>
	 */
	private static function looks(): array {
		return array(
			'soft'   => __( 'Soft cards with an icon tile', 'lenz-plus' ),
			'accent' => __( 'Cards with a coloured edge', 'lenz-plus' ),
			'lined'  => __( 'Rows with dividing lines', 'lenz-plus' ),
			'chips'  => __( 'Chips', 'lenz-plus' ),
			'boxes'  => __( 'Dashed boxes in columns', 'lenz-plus' ),
			'list'   => __( 'Simple list', 'lenz-plus' ),
		);
	}

	/**
	 * The marker before an item: its own icon, the icon for its position, the
	 * widget's icon, or the built-in tick/cross.
	 *
	 * @param array  $s     Settings.
	 * @param int    $index Item index (0-based).
	 * @param mixed  $own   The item's own ICONS value (typed items).
	 * @param string $key   Built-in glyph key for the look.
	 */
	private static function mark_html( array $s, int $index, $own, string $key ): string {
		$icons = is_array( $s['icons'] ?? null ) ? array_values( $s['icons'] ) : array();
		$html  = self::elementor_icon_html( $own );
		if ( '' === $html && 'custom' !== $s['source'] ) {
			$html = self::elementor_icon_html( $icons[ $index ]['icon'] ?? null );
		}
		if ( '' === $html && 'icon' === $s['mark'] ) {
			$html = self::elementor_icon_html( $s['icon'] );
		}
		if ( '' === $html ) {
			$html = self::icon( $key, 'lzp-tick__icon' );
		}

		return '<span class="lzp-tick__mark" aria-hidden="true">' . $html . '</span>';
	}

	/** Prints the items. */
	protected function render(): void {
		$s    = $this->get_settings_for_display();
		$note = (string) $s['note'];

		$own = array();
		if ( 'custom' === $s['source'] ) {
			$rows  = array_values( (array) $s['items'] );
			$lines = wp_list_pluck( $rows, 'text' );
			$own   = wp_list_pluck( $rows, 'icon' );
		} else {
			$id = Context::current_item( 'product' );
			if ( ! $id || ! Course_Data::is_course( $id ) ) {
				$this->editor_hint( __( 'Shows the course being viewed. Mark a WooCommerce product as a course (Course details box) to see it here.', 'lenz-plus' ) );
				return;
			}
			$d     = Course_Data::details( $id );
			$lines = isset( $d[ $s['source'] ] ) && is_array( $d[ $s['source'] ] ) ? $d[ $s['source'] ] : $d['learn'];
			if ( 'audience' === $s['source'] && '' === $note ) {
				$note = $d['audience_note'];
			}
		}

		// Keys survive the filter, so typed items keep their own icons.
		$lines = array_filter( array_map( 'strval', $lines ), 'strlen' );
		if ( ! $lines ) {
			$this->editor_hint( __( 'Nothing to list yet: fill in the lists in the course\'s details.', 'lenz-plus' ) );
			return;
		}

		$look  = isset( self::looks()[ $s['look'] ] ) ? $s['look'] : 'soft';
		$cross = 'cross' === $s['mark'] || ( 'auto' === $s['mark'] && 'not_for' === $s['source'] );
		$key   = 'boxes' === $look ? ( $cross ? 'close' : 'check-square' ) : ( $cross ? 'x' : 'check' );

		$items    = '';
		$position = 0;
		foreach ( $lines as $index => $line ) {
			$items .= '<li class="lzp-tick">' . self::mark_html( $s, $position++, $own[ $index ] ?? null, $key ) . '<span class="lzp-tick__text">' . esc_html( $line ) . '</span></li>';
		}

		printf(
			'<div class="lzp-ticks%4$s">%1$s<ul class="lzp-ticks__list">%2$s</ul>%3$s</div>',
			'' !== (string) $s['intro'] ? '<p class="lzp-ticks__intro">' . esc_html( $s['intro'] ) . '</p>' : '',
			$items, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above (icons by Elementor).
			'' !== $note ? '<p class="lzp-ticks__note">' . self::icon( 'mobile', 'lzp-ticks__note-icon' ) . '<span>' . esc_html( $note ) . '</span></p>' : '', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped here.
			esc_attr( ' lzp-ticks--' . $look . ( $cross ? ' lzp-ticks--cross' : '' ) )
		);
	}
}
