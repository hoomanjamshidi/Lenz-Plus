<?php
/**
 * First column of the mockups' footer: the logo and name in a bordered box,
 * a short description and the social buttons.
 *
 * Defaults follow the site: Lenz's white footer logo, the "about" text saved
 * in Lenz's footer settings and the support button's social channels.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Core\Theme_Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * The «Brand box (Lenz+)» widget.
 */
final class Brand_Box extends Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-brand-box';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Brand box', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-logo';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'footer', 'logo', 'about', 'فوتر', 'لوگو', 'درباره' ) );
	}

	/** Logo, description and social controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_logo', __( 'Logo', 'lenz-plus' ) );
		Site_Logo::add_logo_controls( $this, 'footer' );

		$this->add_control(
			'boxed',
			array(
				'label'     => __( 'Frame around the logo', 'lenz-plus' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			)
		);

		$this->end_controls_section();

		$this->start_content_section( 'section_about', __( 'Description and links', 'lenz-plus' ) );

		$this->add_control(
			'show_about',
			array(
				'label'   => __( 'Description', 'lenz-plus' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'about',
			array(
				'label'       => __( 'Text', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => '',
				'rows'        => 4,
				'description' => __( 'Empty: the "about" text saved in Lenz\'s footer settings.', 'lenz-plus' ),
				'condition'   => array( 'show_about' => 'yes' ),
			)
		);

		$this->add_control(
			'socials',
			array(
				'label'       => __( 'Social buttons', 'lenz-plus' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => __( 'The Instagram, Telegram, WhatsApp, Bale and Eitaa channels switched on in Lenz+ → Support button.', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Brand box', 'lenz-plus' ) );

		$this->add_responsive_control(
			'height',
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
				'selectors'  => array( '{{WRAPPER}} .lzp-logo' => '--lzp-logo-h: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_text_style( 'name', '.lzp-logo__name', array( 'label' => __( 'Name', 'lenz-plus' ) ) );
		$this->add_text_style( 'about', '.lzp-brand__about', array( 'label' => __( 'Description', 'lenz-plus' ) ) );

		$this->end_controls_section();
	}

	/** Prints the box. */
	protected function render(): void {
		$s    = $this->get_settings_for_display();
		$args = Site_Logo::markup_args( $s );

		$args['class'] = 'yes' === $s['boxed'] ? 'lzp-logo--boxed' : '';

		$html = Site_Logo::markup( $args );

		if ( 'yes' === $s['show_about'] ) {
			$about = '' !== trim( (string) $s['about'] ) ? (string) $s['about'] : Theme_Bridge::footer_text( 'about' );
			if ( '' !== $about ) {
				$html .= '<p class="lzp-brand__about">' . nl2br( esc_html( $about ) ) . '</p>';
			}
		}

		if ( 'yes' === $s['socials'] ) {
			$html .= Social_Links::markup( Social_Links::from_support_button() );
		}

		echo '<div class="lzp-brand">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building.
	}
}
