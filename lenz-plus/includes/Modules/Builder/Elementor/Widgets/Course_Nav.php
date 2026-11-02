<?php
/**
 * «منوی دوره»: links to the sections of a course page (about, curriculum,
 * instructor…), each with an icon. As a side card it goes in a column set
 * to stay in view while scrolling; as a bar it sticks under the header
 * itself (tablets and phones). courses.js marks the section on screen
 * (`is-current`, `aria-current`) and scrolls below the sticky header on
 * click; without JavaScript the links are plain jumps to the section ids.
 * The side card can end with the course's facts (duration, sessions,
 * format).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use LenzPlus\Modules\Builder\Context;
use LenzPlus\Modules\Builder\Course_Data;

defined( 'ABSPATH' ) || exit;

/**
 * The «Course menu (Lenz+)» widget.
 */
final class Course_Nav extends Course_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-course-nav';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Course menu', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-nav-menu';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'menu', 'toc', 'anchor', 'منو', 'فهرست' ) );
	}

	/**
	 * Default rows: the section ids of the course page presets.
	 *
	 * @return array<int, array>
	 */
	private static function default_items(): array {
		$row = static function ( string $label, string $target, string $icon ): array {
			return array(
				'label'  => $label,
				'target' => $target,
				'icon'   => array(
					'value'   => $icon,
					'library' => 'fa-solid',
				),
			);
		};

		return array(
			$row( __( 'About the course', 'lenz-plus' ), 'about', 'fas fa-info-circle' ),
			$row( __( 'What you will learn', 'lenz-plus' ), 'learn', 'fas fa-lightbulb' ),
			$row( __( 'Curriculum', 'lenz-plus' ), 'curriculum', 'fas fa-list-ul' ),
			$row( __( 'Instructor', 'lenz-plus' ), 'instructor', 'fas fa-chalkboard-teacher' ),
			$row( __( 'Outcome', 'lenz-plus' ), 'outcome', 'fas fa-trophy' ),
			$row( __( 'Who it is for', 'lenz-plus' ), 'audience', 'fas fa-users' ),
			$row( __( 'Questions', 'lenz-plus' ), 'faq', 'fas fa-question-circle' ),
		);
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Course menu', 'lenz-plus' ) );

		$this->add_control(
			'layout',
			array(
				'label'        => __( 'Layout', 'lenz-plus' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'side',
				'options'      => array(
					'side' => __( 'Side card (put it in a sticky column)', 'lenz-plus' ),
					'bar'  => __( 'Bar that sticks under the header', 'lenz-plus' ),
				),
				'prefix_class' => 'lzp-cnav-',
				'render_type'  => 'template',
			)
		);

		$this->add_control(
			'title',
			array(
				'label'     => __( 'Title', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '',
				'condition' => array( 'layout' => 'side' ),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'label',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);
		$repeater->add_control(
			'target',
			array(
				'label'       => __( 'Section ID', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'The CSS ID of the section (Advanced tab), without #.', 'lenz-plus' ),
			)
		);
		$repeater->add_control(
			'icon',
			array(
				'label' => __( 'Icon', 'lenz-plus' ),
				'type'  => Controls_Manager::ICONS,
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Links', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => self::default_items(),
				'title_field' => '{{{ label }}}',
				'description' => __( 'Links whose section is missing on the page are left out.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'facts',
			array(
				'label'        => __( 'Course facts at the bottom', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'layout' => 'side' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Menu', 'lenz-plus' ) );

		$this->add_control(
			'accent',
			array(
				'label'     => __( 'Current item colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lzp-cnav' => '--lzp-cnav-accent: {{VALUE}};' ),
			)
		);

		$this->add_box_style( 'menu', '.lzp-cnav' );
		$this->add_text_style( 'link', '.lzp-cnav__link', array( 'label' => __( 'Links', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Facts at the bottom of the side card: [value, label].
	 *
	 * @return array<int, array{0:string, 1:string}>
	 */
	private static function facts(): array {
		$id = Context::current_item( 'product' );
		if ( ! $id || ! Course_Data::is_course( $id ) ) {
			return array();
		}

		$d     = Course_Data::details( $id );
		$facts = array(
			array( self::digits( $d['duration'] ), __( 'Duration', 'lenz-plus' ) ),
			array( $d['curriculum'] ? self::num( count( $d['curriculum'] ) ) : '', __( 'Sessions', 'lenz-plus' ) ),
			array( 'online' === $d['format'] ? __( 'Online', 'lenz-plus' ) : __( 'In person', 'lenz-plus' ), __( 'Format', 'lenz-plus' ) ),
		);

		return array_values(
			array_filter(
				$facts,
				static function ( array $fact ): bool {
					return '' !== $fact[0];
				}
			)
		);
	}

	/** Prints the menu. */
	protected function render(): void {
		$s    = $this->get_settings_for_display();
		$side = 'bar' !== $s['layout'];

		$links = '';
		foreach ( (array) $s['items'] as $item ) {
			$target = sanitize_html_class( ltrim( (string) ( $item['target'] ?? '' ), '#' ) );
			$label  = trim( (string) ( $item['label'] ?? '' ) );
			if ( '' === $target || '' === $label ) {
				continue;
			}
			$icon   = self::elementor_icon_html( $item['icon'] ?? null );
			$links .= sprintf(
				'<li><a class="lzp-cnav__link" href="#%1$s">%2$s<span class="lzp-cnav__text">%3$s</span></a></li>',
				esc_attr( $target ),
				'' !== $icon ? '<span class="lzp-cnav__icon">' . $icon . '</span>' : '',
				esc_html( $label )
			);
		}

		if ( '' === $links ) {
			$this->editor_hint( __( 'Add links to the sections of the page (their CSS IDs).', 'lenz-plus' ) );
			return;
		}

		$facts = '';
		if ( $side && 'yes' === $s['facts'] ) {
			foreach ( self::facts() as $fact ) {
				$facts .= '<li><span class="lzp-cnav__fact-value">' . esc_html( $fact[0] ) . '</span><span class="lzp-cnav__fact-label">' . esc_html( $fact[1] ) . '</span></li>';
			}
		}

		printf(
			'<nav class="lzp-cnav lzp-cnav--%1$s" aria-label="%2$s" data-lzp-course-nav>%3$s<ul class="lzp-cnav__list">%4$s</ul>%5$s</nav>',
			$side ? 'side' : 'bar',
			esc_attr__( 'Course sections', 'lenz-plus' ),
			$side && '' !== (string) $s['title'] ? '<p class="lzp-cnav__title">' . esc_html( $s['title'] ) . '</p>' : '',
			$links, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building (icons by Elementor).
			'' !== $facts ? '<ul class="lzp-cnav__facts">' . $facts . '</ul>' : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		);
	}
}
