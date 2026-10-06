<?php
/**
 * Section heading of the mockups: a 3px accent bar, an optional index number
 * («۰۱»), the title, an optional Latin subtitle (PROFESSIONAL PHOTOGRAPHY),
 * a muted subtitle line and an optional button at the end of the row.
 *
 * Widgets with their own heading (portfolio grids with filter chips, card
 * grids with a "view all" button) print it through Heading::markup(), so
 * every section title on a page looks the same.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * The «Section heading (Lenz+)» widget and the shared heading markup.
 */
final class Heading extends Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-heading';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Section heading', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-heading';
	}

	/** Static content: Elementor may cache it. */
	protected function is_dynamic_content(): bool {
		return false;
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'heading', 'title', 'عنوان', 'تیتر' ) );
	}

	/**
	 * Section heading markup shared by every widget.
	 *
	 * @param array  $args {
	 *     @type string $title    Title text.
	 *     @type string $tag      h1–h4 or p.
	 *     @type string $index    Optional number before the title («۰۱»).
	 *     @type bool   $bar      Show the accent bar.
	 *     @type string $latin    Optional Latin subtitle (left to right).
	 *     @type string $subtitle Optional muted line under the title.
	 *     @type string $align    `start` or `center`.
	 * }
	 * @param string $end_html Trusted markup for the end of the title row (a button, filter chips…).
	 */
	public static function markup( array $args, string $end_html = '' ): string {
		$args = array_merge(
			array(
				'title'    => '',
				'tag'      => 'h2',
				'index'    => '',
				'bar'      => true,
				'latin'    => '',
				'subtitle' => '',
				'align'    => 'start',
			),
			$args
		);

		$tag   = in_array( $args['tag'], array( 'h1', 'h2', 'h3', 'h4', 'p' ), true ) ? $args['tag'] : 'h2';
		$title = ( $args['bar'] ? '<span class="lzp-heading__bar" aria-hidden="true"></span>' : '' )
			. ( '' !== $args['index'] ? '<span class="lzp-heading__index">' . esc_html( self::digits( $args['index'] ) ) . '</span>' : '' )
			. sprintf( '<%1$s class="lzp-heading__text">%2$s</%1$s>', $tag, esc_html( $args['title'] ) );

		$html  = '<div class="lzp-heading lzp-heading--' . esc_attr( 'center' === $args['align'] ? 'center' : 'start' ) . '">';
		$html .= '<div class="lzp-heading__row"><div class="lzp-heading__title">' . $title . '</div>' . $end_html . '</div>';

		if ( '' !== $args['latin'] ) {
			$html .= '<span class="lzp-heading__latin" dir="ltr">' . esc_html( $args['latin'] ) . '</span>';
		}

		if ( '' !== $args['subtitle'] ) {
			$html .= '<p class="lzp-heading__sub">' . esc_html( $args['subtitle'] ) . '</p>';
		}

		return $html . '</div>';
	}

	/** Content (texts, number, bar, action) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Heading', 'lenz-plus' ) );

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Section title', 'lenz-plus' ),
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'tag',
			array(
				'label'   => __( 'HTML tag', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => array(
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'p'  => 'p',
				),
			)
		);

		$this->add_control(
			'index',
			array(
				'label'       => __( 'Number before the title', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => '01',
			)
		);

		$this->add_control(
			'bar',
			array(
				'label'        => __( 'Accent bar', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'latin',
			array(
				'label'       => __( 'Latin subtitle', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => 'PROFESSIONAL PHOTOGRAPHY',
				'label_block' => true,
			)
		);

		$this->add_control(
			'subtitle',
			array(
				'label'   => __( 'Subtitle', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => '',
				'rows'    => 2,
			)
		);

		$this->add_control(
			'align',
			array(
				'label'   => __( 'Alignment', 'lenz-plus' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'start',
				'toggle'  => false,
				'options' => array(
					'start'  => array(
						'title' => __( 'Start', 'lenz-plus' ),
						'icon'  => 'eicon-text-align-' . self::start_icon(),
					),
					'center' => array(
						'title' => __( 'Center', 'lenz-plus' ),
						'icon'  => 'eicon-text-align-center',
					),
				),
			)
		);

		$this->add_control(
			'action_text',
			array(
				'label'     => __( 'Button text', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'action_link',
			array(
				'label'     => __( 'Button link', 'lenz-plus' ),
				'type'      => Controls_Manager::URL,
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'action_text!' => '' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Heading', 'lenz-plus' ) );
		$this->add_text_style( 'title', '.lzp-heading__text', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'latin', '.lzp-heading__latin', array( 'label' => __( 'Latin subtitle', 'lenz-plus' ) ) );
		$this->add_text_style( 'sub', '.lzp-heading__sub', array( 'label' => __( 'Subtitle', 'lenz-plus' ) ) );

		$this->add_control(
			'bar_color',
			array(
				'label'     => __( 'Accent bar colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .lzp-heading__bar' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'space_below',
			array(
				'label'      => __( 'Space below', 'lenz-plus' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .lzp-heading' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/** Prints the heading. */
	protected function render(): void {
		$s   = $this->get_settings_for_display();
		$end = '';

		if ( '' !== $s['action_text'] ) {
			$attrs = '';
			if ( ! empty( $s['action_link']['url'] ) ) {
				// Elementor's link attributes (href, target, rel, custom attributes) are escaped by Elementor.
				$this->add_link_attributes( 'action', $s['action_link'] );
				$attrs = ' ' . $this->get_render_attribute_string( 'action' );
			}

			$end = Button::markup(
				array(
					'text'    => (string) $s['action_text'],
					'variant' => 'primary',
					'size'    => 'sm',
					'icon'    => 'arrow-forward',
					'attrs'   => $attrs,
				)
			);
		}

		$heading = self::markup(
			array(
				'title'    => (string) $s['title'],
				'tag'      => (string) $s['tag'],
				'index'    => (string) $s['index'],
				'bar'      => 'yes' === $s['bar'],
				'latin'    => (string) $s['latin'],
				'subtitle' => (string) $s['subtitle'],
				'align'    => (string) $s['align'],
			),
			$end
		);

		echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in markup() and Button::markup().
	}
}
