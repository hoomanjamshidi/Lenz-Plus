<?php
/**
 * Base for the page-section widgets (heroes, numbers, steps, cards, lists,
 * pricing, FAQ, call-to-action bands). They add the sections stylesheet, so
 * pages built only from basic widgets and the header/footer never load it.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Assets;
use LenzPlus\Modules\Builder\Elementor\Picture;

defined( 'ABSPATH' ) || exit;

/**
 * Stylesheet and shared controls of the section widgets.
 */
abstract class Section_Base extends Base {

	/** The shared builder stylesheet and the sections stylesheet. */
	public function get_style_depends(): array {
		return array( Assets::HANDLE, Assets::SECTIONS_HANDLE );
	}

	/** Static content by default: Elementor may cache it. */
	protected function is_dynamic_content(): bool {
		return false;
	}

	/** Panel search terms shared by the section widgets. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'section', 'سکشن', 'بخش' ) );
	}

	/**
	 * MEDIA control for a photo, empty by default so the mockups' placeholder shows.
	 *
	 * @param string $name  Control name.
	 * @param string $label Label.
	 * @param array  $extra More control arguments.
	 */
	protected function add_photo_control( string $name, string $label, array $extra = array() ): void {
		$this->add_control(
			$name,
			array_merge(
				array(
					'label'   => $label,
					'type'    => Controls_Manager::MEDIA,
					'dynamic' => array( 'active' => true ),
					'default' => array(
						'url' => '',
						'id'  => '',
					),
				),
				$extra
			)
		);
	}

	/**
	 * "Shape" select for a photo frame.
	 *
	 * @param string $name          Control name.
	 * @param string $default_value Default ratio.
	 * @param array  $extra         More control arguments.
	 */
	protected function add_ratio_control( string $name, string $default_value, array $extra = array() ): void {
		$this->add_control(
			$name,
			array_merge(
				array(
					'label'   => __( 'Shape', 'lenz-plus' ),
					'type'    => Controls_Manager::SELECT,
					'default' => $default_value,
					'options' => Picture::ratio_options(),
				),
				$extra
			)
		);
	}

	/**
	 * Optional small title printed above a list or grid («حوزه‌های کار»,
	 * «خدمات قابل افزودن»), lighter than a section heading.
	 */
	protected function add_list_title_control(): void {
		$this->add_control(
			'list_title',
			array(
				'label'       => __( 'Small title above', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);
	}

	/**
	 * The small title, '' when empty.
	 *
	 * @param array $s Settings.
	 */
	protected static function list_title_html( array $s ): string {
		$title = (string) ( $s['list_title'] ?? '' );

		return '' !== $title ? '<h3 class="lzp-list-title">' . esc_html( $title ) . '</h3>' : '';
	}

	/**
	 * Settings of an Elementor URL control as escaped link attributes ('' without a URL).
	 *
	 * @param string $key  Render attribute key (unique per link).
	 * @param mixed  $link URL control value.
	 */
	protected function link_attrs( string $key, $link ): string {
		if ( ! is_array( $link ) || empty( $link['url'] ) ) {
			return '';
		}

		// Elementor escapes href, target, rel and custom attributes.
		$this->add_link_attributes( $key, $link );

		return ' ' . $this->get_render_attribute_string( $key );
	}

	/**
	 * Two buttons (primary and secondary) from `<prefix>_text`/`_link` settings.
	 *
	 * @param array  $s        Settings.
	 * @param array  $variants Variant of the first and second button.
	 * @param string $icon     Icon of the first button.
	 */
	protected function buttons_html( array $s, array $variants, string $icon = 'arrow-forward' ): string {
		$html = '';

		foreach ( array( 'primary', 'secondary' ) as $index => $prefix ) {
			$text = (string) ( $s[ $prefix . '_text' ] ?? '' );
			if ( '' === $text ) {
				continue;
			}

			$html .= Button::markup(
				array(
					'text'    => $text,
					'variant' => $variants[ $index ],
					'size'    => 'lg',
					'icon'    => 0 === $index ? (string) ( $s[ $prefix . '_icon' ] ?? $icon ) : '',
					'attrs'   => $this->link_attrs( $prefix . '_link', $s[ $prefix . '_link' ] ?? array() ),
				)
			);
		}

		return '' !== $html ? '<div class="lzp-btn-row">' . $html . '</div>' : '';
	}

	/**
	 * Text, link and icon controls for the two buttons buttons_html() prints.
	 *
	 * @param array $defaults `primary_text`, `primary_url`, `secondary_text`, `secondary_url`.
	 */
	protected function add_buttons_controls( array $defaults ): void {
		foreach ( array( 'primary', 'secondary' ) as $prefix ) {
			$this->add_control(
				$prefix . '_text',
				array(
					'label'     => 'primary' === $prefix ? __( 'First button', 'lenz-plus' ) : __( 'Second button', 'lenz-plus' ),
					'type'      => Controls_Manager::TEXT,
					'default'   => $defaults[ $prefix . '_text' ] ?? '',
					'separator' => 'before',
				)
			);

			$this->add_control(
				$prefix . '_link',
				array(
					'label'     => __( 'Link', 'lenz-plus' ),
					'type'      => Controls_Manager::URL,
					'dynamic'   => array( 'active' => true ),
					'default'   => array( 'url' => $defaults[ $prefix . '_url' ] ?? '' ),
					'condition' => array( $prefix . '_text!' => '' ),
				)
			);

			if ( 'primary' === $prefix ) {
				$this->add_control(
					'primary_icon',
					array(
						'label'     => __( 'Icon', 'lenz-plus' ),
						'type'      => Controls_Manager::SELECT,
						'default'   => $defaults['primary_icon'] ?? 'arrow-forward',
						'options'   => self::icon_options(),
						'condition' => array( 'primary_text!' => '' ),
					)
				);
			}
		}
	}
}
