<?php
/**
 * Copyright line of the mockups' footer: centred small text under a dashed
 * rule.
 *
 * Defaults to the copyright text saved in Lenz's footer settings, else
 * «© {year} {site}». `{year}` and `{site}` are replaced in any text, so the
 * year never goes stale.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Core\Theme_Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * The «Copyright (Lenz+)» widget.
 */
final class Copyright extends Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-copyright';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Copyright', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-footer';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'copyright', 'footer', 'کپی رایت', 'حقوق', 'فوتر' ) );
	}

	/** Text, rule and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Copyright', 'lenz-plus' ) );

		$this->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => '',
				'rows'        => 2,
				'placeholder' => '© {year} {site}',
				'description' => __( 'Empty: the copyright text saved in Lenz\'s footer settings. {year} and {site} become the current year and the site title.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'rule',
			array(
				'label'   => __( 'Dashed line above', 'lenz-plus' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_align_control( 'align', '.lzp-copyright', 'center' );

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Copyright', 'lenz-plus' ) );
		$this->add_text_style( 'text', '.lzp-copyright' );
		$this->end_controls_section();
	}

	/** Prints the line. */
	protected function render(): void {
		$s    = $this->get_settings_for_display();
		$text = trim( (string) $s['text'] );

		if ( '' === $text ) {
			$text = Theme_Bridge::footer_text( 'copyright' );
		}
		if ( '' === $text ) {
			$text = '© {year} {site}';
		}

		$text = strtr(
			$text,
			array(
				'{year}' => wp_date( 'Y' ),
				'{site}' => get_bloginfo( 'name' ),
			)
		);

		printf(
			'<p class="lzp-copyright%1$s">%2$s</p>',
			'yes' === $s['rule'] ? ' lzp-copyright--rule' : '',
			esc_html( self::digits( $text ) )
		);
	}
}
