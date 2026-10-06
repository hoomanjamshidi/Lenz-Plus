<?php
/**
 * Button in the variants of the mockups: ink, white with a border, white on
 * dark areas, outlined on dark areas, and a text link. Other widgets (section
 * headings, heroes, call-to-action bands) print the same markup through
 * Button::markup(), so every button on a page shares one look.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * The «Button (Lenz+)» widget and the shared button markup.
 */
final class Button extends Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-button';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Button', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-button';
	}

	/** Static content: Elementor may cache it. */
	protected function is_dynamic_content(): bool {
		return false;
	}

	/**
	 * Visual variants, as in the mockups.
	 *
	 * @return array<string, string>
	 */
	public static function variants(): array {
		return array(
			'primary'      => __( 'Ink', 'lenz-plus' ),
			'secondary'    => __( 'White with border', 'lenz-plus' ),
			'light'        => __( 'White (for dark areas)', 'lenz-plus' ),
			'outline-dark' => __( 'Outline (for dark areas)', 'lenz-plus' ),
			'link'         => __( 'Text link', 'lenz-plus' ),
		);
	}

	/**
	 * Sizes, as in the mockups (hero buttons, band buttons, heading actions).
	 *
	 * @return array<string, string>
	 */
	public static function sizes(): array {
		return array(
			'sm' => __( 'Small', 'lenz-plus' ),
			'md' => __( 'Medium', 'lenz-plus' ),
			'lg' => __( 'Large', 'lenz-plus' ),
		);
	}

	/**
	 * Button markup shared by every widget.
	 *
	 * @param array $args {
	 *     @type string $text    Label.
	 *     @type string $url     Link ('' prints a <button>).
	 *     @type string $variant One of variants().
	 *     @type string $size    One of sizes().
	 *     @type string $icon    Semantic icon key ('' for none), printed after the text.
	 *     @type string $attrs   Extra attributes, already escaped (e.g. from Elementor's link attributes).
	 * }
	 */
	public static function markup( array $args ): string {
		$args = array_merge(
			array(
				'text'    => '',
				'url'     => '',
				'variant' => 'primary',
				'size'    => 'md',
				'icon'    => '',
				'attrs'   => '',
			),
			$args
		);

		$classes = 'lzp-btn lzp-btn--' . sanitize_html_class( $args['variant'] ) . ' lzp-btn--' . sanitize_html_class( $args['size'] );
		$body    = '<span>' . esc_html( $args['text'] ) . '</span>' . ( '' !== $args['icon'] ? self::icon( $args['icon'] ) : '' );

		if ( '' === $args['url'] && '' === $args['attrs'] ) {
			return '<button type="button" class="' . esc_attr( $classes ) . '">' . $body . '</button>';
		}

		$href = '' !== $args['url'] ? ' href="' . esc_url( $args['url'] ) . '"' : '';

		return '<a class="' . esc_attr( $classes ) . '"' . $href . $args['attrs'] . '>' . $body . '</a>';
	}

	/** Content (text, link, variant, size, icon, alignment) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Button', 'lenz-plus' ) );

		$this->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Book a session', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'link',
			array(
				'label'   => __( 'Link', 'lenz-plus' ),
				'type'    => Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
				'default' => array( 'url' => '#' ),
			)
		);

		$this->add_control(
			'variant',
			array(
				'label'   => __( 'Style', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'primary',
				'options' => self::variants(),
			)
		);

		$this->add_control(
			'size',
			array(
				'label'     => __( 'Size', 'lenz-plus' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'lg',
				'options'   => self::sizes(),
				'condition' => array( 'variant!' => 'link' ),
			)
		);

		$this->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'arrow-forward',
				'options' => self::icon_options(),
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'                => __( 'Alignment', 'lenz-plus' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => array(
					'flex-start' => array(
						'title' => __( 'Start', 'lenz-plus' ),
						'icon'  => 'eicon-text-align-' . self::start_icon(),
					),
					'center'     => array(
						'title' => __( 'Center', 'lenz-plus' ),
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'End', 'lenz-plus' ),
						'icon'  => 'eicon-text-align-' . self::end_icon(),
					),
					'stretch'    => array(
						'title' => __( 'Full width', 'lenz-plus' ),
						'icon'  => 'eicon-text-align-justify',
					),
				),
				'selectors_dictionary' => array(
					'flex-start' => 'justify-content: flex-start; --lzp-btn-grow: 0',
					'center'     => 'justify-content: center; --lzp-btn-grow: 0',
					'flex-end'   => 'justify-content: flex-end; --lzp-btn-grow: 0',
					'stretch'    => 'justify-content: flex-start; --lzp-btn-grow: 1',
				),
				'selectors'            => array( '{{WRAPPER}} .lzp-btn-wrap' => '{{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Button', 'lenz-plus' ) );
		$this->add_button_style( 'btn', '.lzp-btn' );
		$this->end_controls_section();
	}

	/** Prints the button. */
	protected function render(): void {
		$s     = $this->get_settings_for_display();
		$attrs = '';

		if ( ! empty( $s['link']['url'] ) ) {
			// Elementor's link attributes (href, target, rel, custom attributes) are escaped by Elementor.
			$this->add_link_attributes( 'link', $s['link'] );
			$attrs = ' ' . $this->get_render_attribute_string( 'link' );
		}

		$button = self::markup(
			array(
				'text'    => (string) $s['text'],
				'variant' => (string) $s['variant'],
				'size'    => (string) $s['size'],
				'icon'    => (string) $s['icon'],
				'attrs'   => $attrs,
			)
		);

		echo '<div class="lzp-btn-wrap">' . $button . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in markup().
	}
}
