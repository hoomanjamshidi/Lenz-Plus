<?php
/**
 * Dashed rows with an accent icon, a bold name and a muted note at the end
 * (the about page's equipment and certificates lists). With a link per row
 * and the arrow at the end, the same rows become the services page's jump
 * tiles («عکاسی حرفه‌ای ↖»), laid out in a grid.
 *
 * With icons in dashed boxes and no row border they become the home page's
 * promises («رضایت مخاطب در اولویت»).
 *
 * Latin words inside Persian names (Sony a7 IV, RGB) keep their direction
 * thanks to `unicode-bidi: plaintext` on the name.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * The «Icon rows (Lenz+)» widget.
 */
final class Icon_Features extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-icon-features';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Icon rows', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-icon-box';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'features', 'icon', 'equipment', 'tiles', 'anchor', 'تجهیزات', 'ویژگی' ) );
	}

	/** Content (rows, arrows) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Rows', 'lenz-plus' ) );

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
				'default'     => __( 'Item name', 'lenz-plus' ),
				'label_block' => true,
			)
		);
		$repeater->add_control(
			'note',
			array(
				'label'   => __( 'Note at the end', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);
		$repeater->add_control(
			'link',
			array(
				'label'       => __( 'Link', 'lenz-plus' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => array( 'active' => true ),
				'description' => __( 'A link to a section of the page works too, e.g. #packages.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Rows', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'icon'  => 'camera',
						'title' => __( 'Camera body', 'lenz-plus' ),
						'note'  => __( 'Photo and video', 'lenz-plus' ),
					),
					array(
						'icon'  => 'bolt',
						'title' => __( 'Studio flash', 'lenz-plus' ),
						'note'  => __( 'Studio and product', 'lenz-plus' ),
					),
				),
				'title_field' => '{{ title }}',
			)
		);

		$this->add_control(
			'arrow',
			array(
				'label'        => __( 'Arrow at the end of linked rows', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
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
					'lg' => __( 'Large (tiles)', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'look',
			array(
				'label'   => __( 'Look', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'rows',
				'options' => array(
					'rows'  => __( 'Dashed rows', 'lenz-plus' ),
					'boxed' => __( 'Icons in dashed boxes', 'lenz-plus' ),
				),
			)
		);

		$this->add_columns_control( '.lzp-rows', array( 1, 1, 1 ), 4 );
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Rows', 'lenz-plus' ) );
		$this->add_gap_control( '.lzp-rows' );
		$this->add_box_style( 'row', '.lzp-rows__item' );

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .lzp-rows__icon' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_text_style( 'title', '.lzp-rows__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'note', '.lzp-rows__note', array( 'label' => __( 'Note', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the rows. */
	protected function render(): void {
		$s = $this->get_settings_for_display();
		if ( empty( $s['items'] ) ) {
			return;
		}

		$boxed   = 'boxed' === $s['look'];
		$classes = 'lzp-rows lzp-grid lzp-rows--' . ( 'lg' === $s['size'] ? 'lg' : 'md' ) . ( $boxed ? ' lzp-rows--boxed' : '' );
		echo '<ul class="' . esc_attr( $classes ) . '">';
		foreach ( $s['items'] as $index => $item ) {
			$link = $this->link_attrs( 'row-' . $index, $item['link'] ?? array() );
			$icon = '' !== (string) $item['icon'] ? self::icon( (string) $item['icon'], 'lzp-rows__icon' ) : '';
			if ( $boxed && '' !== $icon ) {
				$icon = '<span class="lzp-rows__box">' . $icon . '</span>';
			}

			$body = $icon
				. '<span class="lzp-rows__title">' . esc_html( (string) $item['title'] ) . '</span>'
				. ( '' !== (string) $item['note'] ? '<span class="lzp-rows__note">' . esc_html( (string) $item['note'] ) . '</span>' : '' );

			if ( '' !== $link ) {
				$body = '<a class="lzp-rows__item lzp-rows__item--link"' . $link . '>' . $body
					. ( 'yes' === $s['arrow'] ? self::icon( 'arrow-forward', 'lzp-rows__arrow' ) : '' ) . '</a>';
			} else {
				$body = '<div class="lzp-rows__item">' . $body . '</div>';
			}

			echo '<li class="lzp-rows__cell">' . $body . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		}
		echo '</ul>';
	}
}
