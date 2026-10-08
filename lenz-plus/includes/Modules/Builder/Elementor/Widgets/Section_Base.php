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
use LenzPlus\Modules\Builder\Public_Form;

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

	/**
	 * Category chips (links to the category pages) and the number of items,
	 * shared by the portfolio, post and course grids. filter.js turns the
	 * chips into in-place filters when the grid holds all its items.
	 *
	 * @param array $args {
	 *     @type string $taxonomy  Taxonomy of the chips ('' when `chips` lists them).
	 *     @type array  $chips     Slug => [label, url] instead of a taxonomy's terms.
	 *     @type int[]  $exclude   Term IDs left out (e.g. "Uncategorized").
	 *     @type string $all_label Label of the "All" chip.
	 *     @type string $all_url   Link of the "All" chip.
	 *     @type mixed  $current   Term ID or chip slug being viewed (0 or '' = "All").
	 *     @type int    $total     Number of items listed (-1 hides the count).
	 *     @type string $one       Count text for one item (`%s` = number).
	 *     @type string $many      Count text for more items.
	 *     @type string $label     Accessible name of the chip list.
	 * }
	 */
	protected static function filter_bar_html( array $args ): string {
		$items = array();

		if ( ! empty( $args['chips'] ) ) {
			foreach ( $args['chips'] as $slug => $chip ) {
				$items[] = array( (string) $slug, $chip[0], $chip[1], (string) $args['current'] === (string) $slug );
			}
		} else {
			$terms = get_terms(
				array(
					'taxonomy'   => $args['taxonomy'],
					'hide_empty' => true,
					'exclude'    => $args['exclude'] ?? array(),
				)
			);
			foreach ( is_array( $terms ) ? $terms : array() as $term ) {
				$items[] = array( $term->slug, $term->name, (string) get_term_link( $term ), (int) $args['current'] === (int) $term->term_id );
			}
		}

		$chips = sprintf(
			'<a class="lzp-filter-chip" href="%1$s" data-lzp-cat=""%2$s>%3$s</a>',
			esc_url( $args['all_url'] ),
			empty( $args['current'] ) ? ' aria-current="page"' : '',
			esc_html( $args['all_label'] )
		);

		foreach ( $items as $item ) {
			$chips .= sprintf(
				'<a class="lzp-filter-chip" href="%1$s" data-lzp-cat="%2$s"%3$s>%4$s</a>',
				esc_url( $item[2] ),
				esc_attr( $item[0] ),
				$item[3] ? ' aria-current="page"' : '',
				esc_html( $item[1] )
			);
		}

		$count = '';
		if ( $args['total'] >= 0 ) {
			$count = sprintf(
				'<span class="lzp-filter-bar__count" data-lzp-count data-one="%2$s" data-many="%3$s" aria-live="polite">%1$s</span>',
				esc_html( sprintf( 1 === (int) $args['total'] ? $args['one'] : $args['many'], self::num( (int) $args['total'] ) ) ),
				esc_attr( $args['one'] ),
				esc_attr( $args['many'] )
			);
		}

		return sprintf(
			'<div class="lzp-filter-bar"><nav class="lzp-filter-bar__chips" aria-label="%1$s">%2$s</nav>%3$s</div>',
			esc_attr( $args['label'] ),
			$chips,
			$count
		);
	}

	/**
	 * The status line and the "done" panel of a public form (request,
	 * sign-up, waitlist). After a plain post the result arrives in the URL;
	 * with JavaScript forms.js fills the same elements.
	 *
	 * @param string $result  Result of a plain post ('' when none).
	 * @param string $success Success message.
	 */
	protected static function form_result_html( string $result, string $success ): string {
		$errors = Public_Form::error_messages();
		$error  = $errors[ $result ] ?? '';

		return '<p class="lzp-form__status' . ( '' !== $error ? ' is-error' : '' ) . '" role="status" aria-live="polite">' . esc_html( $error ) . '</p>'
			. '<div class="lzp-form__done" data-lzp-form-done tabindex="-1"' . ( 'ok' === $result ? '' : ' hidden' ) . '>'
			. self::icon( 'check', 'lzp-form__done-icon' )
			. '<span>' . esc_html( $success ) . '</span></div>';
	}
}
