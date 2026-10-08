<?php
/**
 * Price cards of the services page: a name (with an optional badge such as
 * «پرطرفدار»), the price and its unit, a hairline, the included items and a
 * button at the bottom. A highlighted plan is printed on ink. Cards in a
 * row share the same height, so their buttons line up.
 *
 * Prices are plain text («از ۱۸٫۰۰۰٫۰۰۰», «توافقی»): these are service
 * packages discussed with each client, not WooCommerce products.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * The «Pricing plans (Lenz+)» widget.
 */
final class Pricing_Plans extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-pricing-plans';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Pricing plans', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-price-table';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'pricing', 'plans', 'packages', 'تعرفه', 'قیمت', 'پکیج' ) );
	}

	/** Content (plans) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Plans', 'lenz-plus' ) );

		$repeater = new Repeater();
		$repeater->add_control(
			'name',
			array(
				'label'       => __( 'Name', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Plan name', 'lenz-plus' ),
				'label_block' => true,
			)
		);
		$repeater->add_control(
			'badge',
			array(
				'label'   => __( 'Badge', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);
		$repeater->add_control(
			'price',
			array(
				'label'   => __( 'Price', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '1,000,000',
			)
		);
		$repeater->add_control(
			'unit',
			array(
				'label'   => __( 'Unit', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Toman', 'lenz-plus' ),
			)
		);
		$repeater->add_control(
			'features',
			array(
				'label'       => __( 'Included (one per line)', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 5,
				'default'     => __( "First item\nSecond item\nThird item", 'lenz-plus' ),
				'label_block' => true,
			)
		);
		$repeater->add_control(
			'button',
			array(
				'label'   => __( 'Button text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Ask for a quote', 'lenz-plus' ),
			)
		);
		$repeater->add_control(
			'link',
			array(
				'label'   => __( 'Button link', 'lenz-plus' ),
				'type'    => Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
				'default' => array( 'url' => '#' ),
			)
		);
		$repeater->add_control(
			'featured',
			array(
				'label'        => __( 'Highlight', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Plans', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'name'     => __( 'Plan name', 'lenz-plus' ),
						'badge'    => __( 'Popular', 'lenz-plus' ),
						'featured' => 'yes',
					),
					array( 'name' => __( 'Plan name', 'lenz-plus' ) ),
					array( 'name' => __( 'Plan name', 'lenz-plus' ) ),
				),
				'title_field' => '{{{ name }}}',
			)
		);

		$this->add_columns_control( '.lzp-plans', array( 4, 2, 1 ) );
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Cards', 'lenz-plus' ) );
		$this->add_gap_control( '.lzp-plans' );
		$this->add_box_style( 'card', '.lzp-plans__item:not(.is-featured)' );
		$this->add_text_style( 'name', '.lzp-plans__name', array( 'label' => __( 'Name', 'lenz-plus' ) ) );
		$this->add_text_style( 'price', '.lzp-plans__price', array( 'label' => __( 'Price', 'lenz-plus' ) ) );
		$this->add_text_style( 'feature', '.lzp-plans__features', array( 'label' => __( 'Included', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the cards. */
	protected function render(): void {
		$s = $this->get_settings_for_display();
		if ( empty( $s['items'] ) ) {
			return;
		}

		echo '<ul class="lzp-plans lzp-grid">';
		foreach ( $s['items'] as $index => $item ) {
			$featured = 'yes' === $item['featured'];
			$features = array_filter( array_map( 'trim', preg_split( '/\R/u', (string) $item['features'] ) ) );

			$head = '<div class="lzp-plans__head"><h3 class="lzp-plans__name">' . esc_html( (string) $item['name'] ) . '</h3>'
				. ( '' !== (string) $item['badge'] ? '<span class="lzp-plans__badge">' . esc_html( $item['badge'] ) . '</span>' : '' )
				. '</div>';

			$price = '<p class="lzp-plans__price">' . esc_html( self::digits( (string) $item['price'] ) )
				. ( '' !== (string) $item['unit'] ? ' <span class="lzp-plans__unit">' . esc_html( self::digits( (string) $item['unit'] ) ) . '</span>' : '' )
				. '</p>';

			$list = '';
			if ( $features ) {
				$list = '<ul class="lzp-plans__features">';
				foreach ( $features as $feature ) {
					$list .= '<li>' . esc_html( $feature ) . '</li>';
				}
				$list .= '</ul>';
			}

			$button = '' !== (string) $item['button'] ? Button::markup(
				array(
					'text'    => (string) $item['button'],
					'variant' => $featured ? 'light' : 'secondary',
					'size'    => 'md',
					'attrs'   => $this->link_attrs( 'plan-' . $index, $item['link'] ?? array() ),
				)
			) : '';

			printf(
				'<li class="lzp-plans__item%1$s">%2$s%3$s<span class="lzp-plans__rule" aria-hidden="true"></span>%4$s%5$s</li>',
				$featured ? ' is-featured lzp-dark' : '',
				$head, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
				$price, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
				$list, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
				$button // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Button::markup().
			);
		}
		echo '</ul>';
	}
}
