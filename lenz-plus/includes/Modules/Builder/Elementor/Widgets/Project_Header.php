<?php
/**
 * Top of a project page: category chips and year, the title, the summary,
 * and dashed fact boxes (client, duration, output, location) at the end of
 * the row, over the mockups' vertical guide lines. Reads the project being
 * viewed (a sample project while the template is edited).
 *
 * Like the page hero it spans the whole width and boxes its own content:
 * place it in a full-width container without padding.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Portfolio_Data;

defined( 'ABSPATH' ) || exit;

/**
 * The «Project header (Lenz+)» widget.
 */
final class Project_Header extends Portfolio_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-project-header';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Project header', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-post-title';
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Project header', 'lenz-plus' ) );

		$this->add_control(
			'facts',
			array(
				'label'        => __( 'Fact boxes', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => __( 'From the Facts field of the project\'s details.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'guides',
			array(
				'label'        => __( 'Guide lines', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Texts', 'lenz-plus' ) );
		$this->add_text_style( 'title', '.lzp-hero__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'lead', '.lzp-hero__lead', array( 'label' => __( 'Summary', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the header. */
	protected function render(): void {
		$s       = $this->get_settings_for_display();
		$post_id = $this->current_project();
		if ( ! $post_id ) {
			return;
		}

		$summary = Portfolio_Data::summary( $post_id );
		$text    = self::meta_html( $post_id )
			. '<h1 class="lzp-hero__title">' . esc_html( get_the_title( $post_id ) ) . '</h1>'
			. ( '' !== $summary ? '<p class="lzp-hero__lead">' . esc_html( $summary ) . '</p>' : '' );

		$facts = '';
		if ( 'yes' === $s['facts'] ) {
			foreach ( Portfolio_Data::details( $post_id )['facts'] as $fact ) {
				$facts .= '<li class="lzp-facts__item">'
					. ( '' !== $fact[0] ? '<span class="lzp-facts__label">' . esc_html( $fact[0] ) . '</span>' : '' )
					. '<span class="lzp-facts__value">' . esc_html( $fact[1] ) . '</span></li>';
			}
		}

		printf(
			'<div class="lzp-hero lzp-hero--text lzp-hero--project">%1$s<div class="lzp-hero__inner"><div class="lzp-hero__text">%2$s</div>%3$s</div></div>',
			'yes' === $s['guides'] ? '<span class="lzp-hero__guides" aria-hidden="true"></span>' : '',
			$text, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			'' !== $facts ? '<ul class="lzp-facts">' . $facts . '</ul>' : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		);
	}
}
