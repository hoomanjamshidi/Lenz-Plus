<?php
/**
 * A client's words on a soft card: Lenz's quote glyph, the quotation and
 * the person's name and role (the project page's testimonial).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * The «Quote (Lenz+)» widget.
 */
final class Quote extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-quote';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Quote', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-testimonial';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'quote', 'testimonial', 'review', 'نقل قول', 'نظر' ) );
	}

	/** Content (quotation, person) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Quote', 'lenz-plus' ) );

		$this->add_control(
			'text',
			array(
				'label'   => __( 'Quotation', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 4,
				'default' => __( 'Delivery was on time and the photos made a real difference for our brand.', 'lenz-plus' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'name',
			array(
				'label'   => __( 'Name', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Client name', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'role',
			array(
				'label'   => __( 'Role', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Brand manager', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Quote', 'lenz-plus' ) );
		$this->add_box_style( 'card', '.lzp-quote' );
		$this->add_text_style( 'text', '.lzp-quote__text', array( 'label' => __( 'Quotation', 'lenz-plus' ) ) );
		$this->add_text_style( 'name', '.lzp-quote__name', array( 'label' => __( 'Name', 'lenz-plus' ) ) );
		$this->add_text_style( 'role', '.lzp-quote__role', array( 'label' => __( 'Role', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the quote. */
	protected function render(): void {
		$s = $this->get_settings_for_display();
		if ( '' === (string) $s['text'] ) {
			return;
		}

		$caption = '';
		if ( '' !== (string) $s['name'] || '' !== (string) $s['role'] ) {
			$caption = '<figcaption class="lzp-quote__by">'
				. ( '' !== (string) $s['name'] ? '<span class="lzp-quote__name">' . esc_html( $s['name'] ) . '</span>' : '' )
				. ( '' !== (string) $s['role'] ? '<span class="lzp-quote__role">' . esc_html( $s['role'] ) . '</span>' : '' )
				. '</figcaption>';
		}

		printf(
			'<figure class="lzp-quote">%1$s<blockquote class="lzp-quote__text">%2$s</blockquote>%3$s</figure>',
			self::icon( 'quote', 'lzp-quote__mark' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup from the bundled library.
			esc_html( (string) $s['text'] ),
			$caption // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		);
	}
}
