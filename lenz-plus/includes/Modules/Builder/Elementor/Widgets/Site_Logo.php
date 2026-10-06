<?php
/**
 * Site logo of the mockups: a logo mark next to a two-line wordmark (the name
 * in heavy type, a small tracked Latin line under it), linked to the home page.
 *
 * The image defaults to the logo saved in Lenz (header or white footer
 * version), so a fresh header looks like the site's own; the name defaults to
 * the site title. Brand_Box prints the same markup through markup().
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Core\Theme_Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * The «Logo and name (Lenz+)» widget and the shared logo markup.
 */
final class Site_Logo extends Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-site-logo';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Logo and name', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-site-logo';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'logo', 'brand', 'لوگو', 'نام سایت' ) );
	}

	/**
	 * Logo link markup shared by the logo and brand box widgets.
	 *
	 * @param array $args {
	 *     @type string $image    Trusted `<img>` markup ('' for none).
	 *     @type string $name     Name line ('' hides the wordmark).
	 *     @type string $sub      Small Latin line under the name.
	 *     @type string $url      Link ('' for no link).
	 *     @type string $class    Extra class on the wrapper.
	 * }
	 */
	public static function markup( array $args ): string {
		$args = array_merge(
			array(
				'image' => '',
				'name'  => '',
				'sub'   => '',
				'url'   => '',
				'class' => '',
			),
			$args
		);

		// Never an empty link: without an image the name is always shown.
		if ( '' === $args['image'] && '' === $args['name'] ) {
			$args['name'] = get_bloginfo( 'name' );
		}

		$inner = '' !== $args['image'] ? '<span class="lzp-logo__mark">' . $args['image'] . '</span>' : '';
		if ( '' !== $args['name'] ) {
			$inner .= '<span class="lzp-logo__text"><span class="lzp-logo__name">' . esc_html( $args['name'] ) . '</span>'
				. ( '' !== $args['sub'] ? '<span class="lzp-logo__sub" dir="ltr">' . esc_html( $args['sub'] ) . '</span>' : '' )
				. '</span>';
		}

		$class = trim( 'lzp-logo ' . $args['class'] );

		return '' !== $args['url']
			? '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $args['url'] ) . '" rel="home">' . $inner . '</a>'
			: '<span class="' . esc_attr( $class ) . '">' . $inner . '</span>';
	}

	/**
	 * Image markup for the logo source controls (shared with Brand_Box).
	 *
	 * @param array  $s    Widget settings (`logo`, `image`).
	 * @param string $area Which theme logo to use: `header` or `footer`.
	 */
	public static function image_html( array $s, string $area ): string {
		if ( 'custom' === $s['logo'] ) {
			$id = (int) ( $s['image']['id'] ?? 0 );
			if ( $id ) {
				return (string) wp_get_attachment_image( $id, 'medium', false, array( 'alt' => get_bloginfo( 'name' ) ) );
			}

			$url = (string) ( $s['image']['url'] ?? '' );

			return '' !== $url ? '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '">' : '';
		}

		return 'theme' === $s['logo'] ? Theme_Bridge::logo_html( $area ) : '';
	}

	/**
	 * Logo source, wordmark and link controls (shared with Brand_Box).
	 *
	 * @param Base   $widget       Widget being built.
	 * @param string $default_area Default theme logo: `header` or `footer`.
	 */
	public static function add_logo_controls( Base $widget, string $default_area ): void {
		$widget->add_control(
			'logo',
			array(
				'label'   => __( 'Logo image', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'theme',
				'options' => array(
					'theme'  => __( 'The logo saved in Lenz', 'lenz-plus' ),
					'custom' => __( 'Another image', 'lenz-plus' ),
					'none'   => __( 'No image', 'lenz-plus' ),
				),
			)
		);

		$widget->add_control(
			'theme_logo',
			array(
				'label'     => __( 'Version', 'lenz-plus' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => $default_area,
				'options'   => array(
					'header' => __( 'Header logo', 'lenz-plus' ),
					'footer' => __( 'Footer logo (for dark backgrounds)', 'lenz-plus' ),
				),
				'condition' => array( 'logo' => 'theme' ),
			)
		);

		$widget->add_control(
			'image',
			array(
				'label'     => __( 'Image', 'lenz-plus' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array( 'logo' => 'custom' ),
			)
		);

		$widget->add_control(
			'wordmark',
			array(
				'label'     => __( 'Name next to the logo', 'lenz-plus' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			)
		);

		$widget->add_control(
			'name',
			array(
				'label'       => __( 'Name', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => get_bloginfo( 'name' ),
				'description' => __( 'Empty: the site title.', 'lenz-plus' ),
				'condition'   => array( 'wordmark' => 'yes' ),
			)
		);

		$widget->add_control(
			'sub',
			array(
				'label'       => __( 'Latin line under the name', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => 'PHOTO & VIDEO',
				'condition'   => array( 'wordmark' => 'yes' ),
			)
		);

		$widget->add_control(
			'link',
			array(
				'label'       => __( 'Link', 'lenz-plus' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => home_url( '/' ),
				'description' => __( 'Empty: the home page.', 'lenz-plus' ),
				'dynamic'     => array( 'active' => true ),
			)
		);
	}

	/**
	 * Arguments for markup() from the shared controls.
	 *
	 * @param array $s Widget settings.
	 */
	public static function markup_args( array $s ): array {
		$wordmark = 'yes' === $s['wordmark'];

		return array(
			'image' => self::image_html( $s, (string) $s['theme_logo'] ),
			'name'  => $wordmark ? ( '' !== (string) $s['name'] ? (string) $s['name'] : get_bloginfo( 'name' ) ) : '',
			'sub'   => $wordmark ? (string) $s['sub'] : '',
			'url'   => ! empty( $s['link']['url'] ) ? (string) $s['link']['url'] : home_url( '/' ),
		);
	}

	/** Logo, name and link controls, then sizes and colours. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Logo', 'lenz-plus' ) );
		self::add_logo_controls( $this, 'header' );
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Logo', 'lenz-plus' ) );

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
		$this->add_text_style( 'sub', '.lzp-logo__sub', array( 'label' => __( 'Latin line', 'lenz-plus' ) ) );

		$this->end_controls_section();
	}

	/** Prints the logo. */
	protected function render(): void {
		echo self::markup( self::markup_args( $this->get_settings_for_display() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in markup(); images from WordPress.
	}
}
