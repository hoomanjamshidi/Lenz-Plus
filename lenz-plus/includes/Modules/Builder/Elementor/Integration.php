<?php
/**
 * Elementor integration: widget category and widgets, and extra container
 * controls (brand surfaces from the mockups, sticky columns).
 *
 * Widgets are classic (v3) Elementor widgets and layouts use Flexbox
 * Containers, which work from Elementor 3.16 through 4.x.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Assets;
use LenzPlus\Modules\Builder\Module;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the «Lenz+» widget category, the widgets and the container controls.
 */
final class Integration {

	public const CATEGORY = 'lenz-plus';

	/** Widget classes (short names inside Elementor\Widgets), in panel order. */
	private const WIDGETS = array(
		// Basics.
		'Heading',
		'Text',
		'Button',
		// Header.
		'Site_Logo',
		'Nav_Menu',
		'Header_Action',
		// Footer.
		'Brand_Box',
		'Social_Links',
		'Link_Column',
		'Contact_Box',
		'Copyright',
		// Page sections.
		'Page_Hero',
		'Service_Cards',
		'Stats',
		'Process_Steps',
		'Card_Grid',
		'Simple_List',
		'Icon_Features',
		'Timeline',
		'Framed_Band',
		'Quote',
		'Service_Detail',
		'Photo_Grid',
		'Photo_Frame',
		'Testimonials',
		'Pricing_Plans',
		'Faq',
		'CTA_Band',
		// Portfolio and single items.
		'Portfolio_Grid',
		'Featured_Projects',
		'Project_Header',
		'Project_Gallery',
		'Checklist',
		'Post_Content',
		'Related_Items',
		'Breadcrumb',
		// Blog.
		'Post_Grid',
		'Featured_Post',
		'Post_Header',
		'Post_Image',
		'Post_Toc',
		'Reading_Progress',
		'Post_Comments',
		'Popular_Posts',
		'Post_Search',
		'Promo_Box',
		// Courses.
		'Course_Grid',
		'Course_Hero',
		'Checklist_Grid',
		'Curriculum',
		'Course_Outcome',
		'Instructor_Box',
		'Course_Buy_Box',
		'Mobile_Buy_Bar',
		// Forms.
		'Request_Form',
		'Newsletter_Form',
	);

	/** @var Module|null */
	private static $module = null;

	/**
	 * @param Module $module Owning module.
	 */
	public function __construct( Module $module ) {
		self::$module = $module;
	}

	/** Module instance, for widgets that read settings (icon pack, digits…). */
	public static function module(): ?Module {
		return self::$module;
	}

	/**
	 * Hooks the category, the widgets, the container controls and the editor preview styles.
	 */
	public function register(): void {
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/element/container/section_layout_additional_options/after_section_end', array( $this, 'container_controls' ) );
		add_action( 'elementor/preview/enqueue_styles', array( $this, 'preview_styles' ) );
	}

	/**
	 * The «Lenz+» group in the widget panel.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elements manager.
	 */
	public function register_category( $elements_manager ): void {
		$elements_manager->add_category(
			self::CATEGORY,
			array(
				'title' => __( 'Lenz+', 'lenz-plus' ),
				'icon'  => 'eicon-apps',
			)
		);
	}

	/**
	 * Registers every widget class that exists.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager.
	 */
	public function register_widgets( $widgets_manager ): void {
		foreach ( self::WIDGETS as $name ) {
			$class = __NAMESPACE__ . '\\Widgets\\' . $name;
			if ( class_exists( $class ) ) {
				$widgets_manager->register( new $class() );
			}
		}
	}

	/**
	 * Container surfaces and sticky helpers live in builder.css, which the
	 * editor needs even before a Lenz+ widget is dropped on the canvas.
	 */
	public function preview_styles(): void {
		wp_enqueue_style( Assets::HANDLE );
	}

	/**
	 * "Lenz+" section in the container's Layout tab.
	 *
	 * @param \Elementor\Element_Base $element Container.
	 */
	public function container_controls( $element ): void {
		$element->start_controls_section(
			'lzp_section',
			array(
				'label' => __( 'Lenz+', 'lenz-plus' ),
				'tab'   => Controls_Manager::TAB_LAYOUT,
			)
		);

		$element->add_control(
			'lzp_surface',
			array(
				'label'        => __( 'Brand surface', 'lenz-plus' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => '',
				'options'      => array(
					''         => __( 'None', 'lenz-plus' ),
					'plain'    => __( 'White', 'lenz-plus' ),
					'card'     => __( 'Card (with border)', 'lenz-plus' ),
					'dashed'   => __( 'Card (dashed border)', 'lenz-plus' ),
					'soft'     => __( 'Soft band', 'lenz-plus' ),
					'chip'     => __( 'Tile', 'lenz-plus' ),
					'ink'      => __( 'Ink band (dark)', 'lenz-plus' ),
					'ink-card' => __( 'Dark card', 'lenz-plus' ),
					'bar'      => __( 'Header bar (translucent)', 'lenz-plus' ),
				),
				'prefix_class' => 'lzp-surface-',
				'description'  => __( 'Uses the brand colours from Lenz+ → Page templates. Dark surfaces switch the text inside to light colours. A background set in the Style tab still wins.', 'lenz-plus' ),
			)
		);

		$element->add_control(
			'lzp_sticky',
			array(
				'label'        => __( 'Sticky while scrolling', 'lenz-plus' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => '',
				'options'      => array(
					''        => __( 'Off', 'lenz-plus' ),
					'desktop' => __( 'Desktop only', 'lenz-plus' ),
					'all'     => __( 'All devices', 'lenz-plus' ),
				),
				'prefix_class' => 'lzp-sticky-',
				'separator'    => 'before',
				'description'  => __( 'Keeps a column (e.g. the buy box) in view inside its parent. The parent must be taller than this container.', 'lenz-plus' ),
			)
		);

		$element->add_control(
			'lzp_sticky_offset',
			array(
				'label'      => __( 'Distance from top', 'lenz-plus' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 24,
				),
				'selectors'  => array(
					'{{WRAPPER}}' => '--lzp-sticky-offset: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array( 'lzp_sticky!' => '' ),
			)
		);

		$element->end_controls_section();
	}
}
