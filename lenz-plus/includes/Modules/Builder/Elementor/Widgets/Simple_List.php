<?php
/**
 * Lines separated by dashed rules, in the three shapes of the mockups:
 * - `rows`: a short muted label before the text («پذیرش», «شایسته تقدیر»),
 *   or an automatic number («۰۱») for exhibitions;
 * - `prices`: the text with a value at the end (add-on services and prices);
 * - `stacked`: a bold title over a line of text (honours of a service).
 * Rows can be split into columns.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * The «Simple list (Lenz+)» widget.
 */
final class Simple_List extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-simple-list';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Simple list', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-bullet-list';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'list', 'prices', 'exhibitions', 'لیست', 'فهرست', 'قیمت' ) );
	}

	/** Content (lines, shape, numbers) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Lines', 'lenz-plus' ) );

		$this->add_list_title_control();

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Shape', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'rows',
				'options' => array(
					'rows'    => __( 'Label and text', 'lenz-plus' ),
					'prices'  => __( 'Text and value', 'lenz-plus' ),
					'stacked' => __( 'Title over text', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'numbered',
			array(
				'label'        => __( 'Numbers instead of labels', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'condition'    => array( 'layout' => 'rows' ),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'label',
			array(
				'label'       => __( 'Label / title', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'The label before the text, or the title above it.', 'lenz-plus' ),
			)
		);
		$repeater->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'A line of text', 'lenz-plus' ),
				'label_block' => true,
			)
		);
		$repeater->add_control(
			'value',
			array(
				'label'       => __( 'Value', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'Shown at the end of the line in the "Text and value" shape.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Lines', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'label' => __( 'Accepted', 'lenz-plus' ),
						'text'  => __( 'A line of text', 'lenz-plus' ),
					),
					array(
						'label' => __( 'Accepted', 'lenz-plus' ),
						'text'  => __( 'A line of text', 'lenz-plus' ),
					),
				),
				'title_field' => '{{{ label }}} {{{ text }}}',
			)
		);

		$this->add_columns_control( '.lzp-list', array( 1, 1, 1 ), 4 );
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Lines', 'lenz-plus' ) );

		$this->add_control(
			'rule_color',
			array(
				'label'     => __( 'Rule colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lzp-list' => '--lzp-list-rule: {{VALUE}};' ),
			)
		);

		$this->add_text_style( 'label', '.lzp-list__label', array( 'label' => __( 'Label / title', 'lenz-plus' ) ) );
		$this->add_text_style( 'text', '.lzp-list__text', array( 'label' => __( 'Text', 'lenz-plus' ) ) );
		$this->add_text_style( 'value', '.lzp-list__value', array( 'label' => __( 'Value', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the lines. */
	protected function render(): void {
		$s = $this->get_settings_for_display();
		if ( empty( $s['items'] ) ) {
			return;
		}

		$layout   = in_array( $s['layout'], array( 'rows', 'prices', 'stacked' ), true ) ? $s['layout'] : 'rows';
		$numbered = 'rows' === $layout && 'yes' === $s['numbered'];

		echo self::list_title_html( $s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in list_title_html().
		echo '<ul class="lzp-list lzp-grid lzp-list--' . esc_attr( $layout ) . ( $numbered ? ' lzp-list--numbered' : '' ) . '">';
		foreach ( $s['items'] as $index => $item ) {
			$label = $numbered ? str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) : (string) $item['label'];
			$html  = '';

			if ( 'prices' !== $layout && '' !== $label ) {
				$html .= '<span class="lzp-list__label">' . esc_html( self::digits( $label ) ) . '</span>';
			}

			$html .= '<span class="lzp-list__text">' . esc_html( (string) $item['text'] ) . '</span>';

			if ( 'prices' === $layout && '' !== (string) $item['value'] ) {
				$html .= '<span class="lzp-list__value">' . esc_html( self::digits( (string) $item['value'] ) ) . '</span>';
			}

			echo '<li class="lzp-list__item">' . $html . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		}
		echo '</ul>';
	}
}
