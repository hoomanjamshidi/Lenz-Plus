<?php
/**
 * Logos of the brands and clients the photographer has worked with.
 *
 * Looks: `grid` (cells divided by hairlines), `cards` (soft tiles),
 * `marquee` (a strip that scrolls by itself, paused on hover and focus) and
 * `inline` (a centred row that wraps). Logos are grey until hovered by
 * default, so mismatched brand colours sit calmly together. A brand without
 * a logo shows its name as a word mark, so a page made from a preset reads
 * well before the logos are uploaded.
 *
 * The marquee is pure CSS: the list is printed twice and the pair slides by
 * half its width. The copy is hidden from screen readers and its links are
 * out of the tab order; with reduced motion the strip becomes the inline row.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * The «Brand logos (Lenz+)» widget.
 */
final class Brand_Logos extends Section_Base {

	/** Looks offered by the widget. */
	private const LOOKS = array( 'grid', 'cards', 'marquee', 'inline' );

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-brand-logos';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Brand logos', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-logo';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'logo', 'brands', 'clients', 'partners', 'لوگو', 'برند', 'مشتریان', 'همکاران' ) );
	}

	/** Content (logos, look) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Logos', 'lenz-plus' ) );

		$this->add_list_title_control();

		$repeater = new Repeater();
		$repeater->add_control(
			'logo',
			array(
				'label'       => __( 'Logo', 'lenz-plus' ),
				'type'        => Controls_Manager::MEDIA,
				'default'     => array(
					'url' => '',
					'id'  => '',
				),
				'description' => __( 'A transparent PNG or SVG works best. Empty: the name is shown as a word mark.', 'lenz-plus' ),
			)
		);
		$repeater->add_control(
			'name',
			array(
				'label'       => __( 'Brand name', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Brand', 'lenz-plus' ),
				'label_block' => true,
				'description' => __( 'Also the logo\'s alternative text.', 'lenz-plus' ),
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
				'label'       => __( 'Logos', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'name' => 'NOVA' ),
					array( 'name' => 'LAM CAFÉ' ),
					array( 'name' => 'AVA' ),
					array( 'name' => 'ROSHAN' ),
					array( 'name' => 'PARSIAN' ),
					array( 'name' => 'MAHAN' ),
				),
				'title_field' => '{{{ name }}}',
			)
		);

		$this->add_control(
			'look',
			array(
				'label'   => __( 'Look', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => array(
					'grid'    => __( 'Grid with lines', 'lenz-plus' ),
					'cards'   => __( 'Tiles', 'lenz-plus' ),
					'marquee' => __( 'Scrolling strip', 'lenz-plus' ),
					'inline'  => __( 'Row', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'tone',
			array(
				'label'   => __( 'Colours', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'mono',
				'options' => array(
					'mono'  => __( 'Grey, in colour on hover', 'lenz-plus' ),
					'color' => __( 'Always in colour', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'speed',
			array(
				'label'     => __( 'One round (seconds)', 'lenz-plus' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 30,
				'min'       => 8,
				'max'       => 120,
				'condition' => array( 'look' => 'marquee' ),
				'selectors' => array( '{{WRAPPER}} .lzp-logos' => '--lzp-logos-dur: {{VALUE}}s;' ),
			)
		);

		$this->add_columns_control( '.lzp-logos__list', array( 6, 4, 2 ), 8, array( 'condition' => array( 'look' => array( 'grid', 'cards' ) ) ) );
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Logos', 'lenz-plus' ) );
		$this->add_responsive_control(
			'logo_height',
			array(
				'label'      => __( 'Logo height', 'lenz-plus' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 16,
						'max' => 120,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .lzp-logos' => '--lzp-logo-h: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_gap_control( '.lzp-logos__list' );
		$this->add_box_style( 'cell', '.lzp-logos__logo', array( 'label' => __( 'Logo box', 'lenz-plus' ) ) );
		$this->add_text_style( 'name', '.lzp-logos__name', array( 'label' => __( 'Word mark', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the logos. */
	protected function render(): void {
		$s     = $this->get_settings_for_display();
		$look  = in_array( $s['look'], self::LOOKS, true ) ? (string) $s['look'] : 'grid';
		$items = array_filter(
			(array) $s['items'],
			static function ( $item ): bool {
				return '' !== trim( (string) $item['name'] ) || ! empty( $item['logo']['url'] );
			}
		);

		if ( ! $items ) {
			$this->editor_hint( __( 'Add the brands you have worked with.', 'lenz-plus' ) );
			return;
		}

		$list = '';
		$copy = '';
		foreach ( array_values( $items ) as $index => $item ) {
			$list .= $this->item_html( $item, $index, false );
			if ( 'marquee' === $look ) {
				$copy .= $this->item_html( $item, $index, true );
			}
		}

		$label = '' !== (string) $s['list_title'] ? (string) $s['list_title'] : __( 'Brands we have worked with', 'lenz-plus' );

		printf(
			'<div class="lzp-logos lzp-logos--%1$s lzp-logos--%2$s">%3$s%4$s</div>',
			esc_attr( $look ),
			'color' === $s['tone'] ? 'color' : 'mono',
			self::list_title_html( $s ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in list_title_html().
			'marquee' === $look
				? '<div class="lzp-logos__viewport"><div class="lzp-logos__track"><ul class="lzp-logos__list" aria-label="' . esc_attr( $label ) . '">' . $list . '</ul><ul class="lzp-logos__list" aria-hidden="true">' . $copy . '</ul></div></div>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in item_html().
				: '<ul class="lzp-logos__list lzp-grid" aria-label="' . esc_attr( $label ) . '">' . $list . '</ul>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in item_html().
		);
	}

	/**
	 * One logo: the image (or the word mark), linked when a link is set.
	 *
	 * @param array $item  Repeater row.
	 * @param int   $index Row index (render attribute keys).
	 * @param bool  $copy  Whether it is the marquee's hidden copy.
	 */
	private function item_html( array $item, int $index, bool $copy ): string {
		$name  = trim( (string) $item['name'] );
		$inner = self::logo_html( (array) $item['logo'], $name );
		$attrs = $this->link_attrs( 'logo-' . $index . ( $copy ? '-copy' : '' ), $item['link'] ?? array() );

		if ( '' !== $attrs ) {
			$tag   = 'a';
			$attrs = $attrs . ( $copy ? ' tabindex="-1"' : '' ) . ( '' !== $name ? ' title="' . esc_attr( $name ) . '"' : '' );
		} else {
			$tag = 'span';
		}

		return '<li class="lzp-logos__item"><' . $tag . ' class="lzp-logos__logo"' . $attrs . '>' . $inner . '</' . $tag . '></li>';
	}

	/**
	 * The logo image with its size attributes (no layout shift), or the name.
	 *
	 * @param array  $media MEDIA value.
	 * @param string $name  Brand name.
	 */
	private static function logo_html( array $media, string $name ): string {
		$id = (int) ( $media['id'] ?? 0 );
		if ( $id ) {
			$img = wp_get_attachment_image(
				$id,
				'medium',
				false,
				array(
					'class'    => 'lzp-logos__img',
					'alt'      => $name,
					'loading'  => 'lazy',
					'decoding' => 'async',
					'sizes'    => '200px',
				)
			);
			if ( '' !== $img ) {
				return $img;
			}
		}

		if ( ! empty( $media['url'] ) ) {
			return '<img class="lzp-logos__img" src="' . esc_url( (string) $media['url'] ) . '" alt="' . esc_attr( $name ) . '" loading="lazy" decoding="async">';
		}

		return '<span class="lzp-logos__name">' . esc_html( $name ) . '</span>';
	}
}
