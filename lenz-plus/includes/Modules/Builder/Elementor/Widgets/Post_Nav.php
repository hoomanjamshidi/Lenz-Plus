<?php
/**
 * Previous and next article under a post: two cards with a small photo, a
 * «نوشته قبلی» / «نوشته بعدی» label and the title. Keeps readers on the
 * site and links every article to its neighbours (internal links), in the
 * same category when chosen.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Elementor\Picture;

defined( 'ABSPATH' ) || exit;

/**
 * The «Previous and next post (Lenz+)» widget.
 */
final class Post_Nav extends Blog_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-post-nav';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Previous and next post', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-post-navigation';
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Previous and next post', 'lenz-plus' ) );

		$this->add_control(
			'same_category',
			array(
				'label'        => __( 'From the same category', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'prev_label',
			array(
				'label'   => __( '"Previous" label', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Previous post', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'next_label',
			array(
				'label'   => __( '"Next" label', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Next post', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Cards', 'lenz-plus' ) );
		$this->add_box_style( 'card', '.lzp-post-nav__link' );
		$this->add_text_style( 'title', '.lzp-post-nav__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the two cards. */
	protected function render(): void {
		$s = $this->get_settings_for_display();

		$this->with_post(
			function () use ( $s ) {
				$same = 'yes' === $s['same_category'];
				$prev = get_previous_post( $same );
				$next = get_next_post( $same );

				if ( ! $prev && ! $next ) {
					$this->editor_hint( __( 'Publish more posts to link to the previous and next one.', 'lenz-plus' ) );
					return;
				}

				printf(
					'<nav class="lzp-post-nav" aria-label="%1$s">%2$s%3$s</nav>',
					esc_attr__( 'More articles', 'lenz-plus' ),
					$prev ? self::card_html( $prev, (string) $s['prev_label'], 'prev' ) : '<span></span>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in card_html().
					$next ? self::card_html( $next, (string) $s['next_label'], 'next' ) : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in card_html().
				);
			}
		);
	}

	/**
	 * One card.
	 *
	 * @param \WP_Post $post  Linked post.
	 * @param string   $label Label above the title.
	 * @param string   $side  `prev` or `next`.
	 */
	private static function card_html( \WP_Post $post, string $label, string $side ): string {
		return sprintf(
			'<a class="lzp-post-nav__link lzp-post-nav__link--%1$s" href="%2$s" rel="%1$s">%3$s<span class="lzp-post-nav__text"><span class="lzp-post-nav__label">%4$s</span><span class="lzp-post-nav__title">%5$s</span></span></a>',
			esc_attr( $side ),
			esc_url( (string) get_permalink( $post ) ),
			Picture::frame(
				array( 'id' => (int) get_post_thumbnail_id( $post ) ),
				array(
					'ratio' => '1/1',
					'class' => 'lzp-photo lzp-post-nav__pic',
					'sizes' => '88px',
				)
			),
			esc_html( $label ),
			esc_html( get_the_title( $post ) )
		);
	}
}
