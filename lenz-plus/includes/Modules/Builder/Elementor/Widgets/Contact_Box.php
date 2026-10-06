<?php
/**
 * Contact column of the mockups' footer: a title and a tinted card with icon
 * rows (address, phone, website).
 *
 * Rows come from the branches saved in Lenz's footer settings (address and
 * phone per branch) or from a list typed here. Phone numbers and other Latin
 * values are kept left-to-right, so «021- 258 69 32» does not reorder in a
 * right-to-left line.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use LenzPlus\Core\Theme_Bridge;
use LenzPlus\Modules\Support_Button\Channels;

defined( 'ABSPATH' ) || exit;

/**
 * The «Contact box (Lenz+)» widget.
 */
final class Contact_Box extends Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-contact-box';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Contact box', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-email-field';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'contact', 'address', 'phone', 'footer', 'تماس', 'آدرس', 'تلفن' ) );
	}

	/** Title, rows and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Contact', 'lenz-plus' ) );

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Contact', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Rows from', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'theme',
				'options' => array(
					'theme'  => __( 'The addresses saved in Lenz\'s footer', 'lenz-plus' ),
					'custom' => __( 'This list', 'lenz-plus' ),
				),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'map-pin',
				'options' => self::icon_options( false ),
			)
		);
		$repeater->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			)
		);
		$repeater->add_control(
			'link',
			array(
				'label'       => __( 'Link', 'lenz-plus' ),
				'type'        => Controls_Manager::URL,
				'description' => __( 'Optional: a map, tel:, mailto: or website link.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'rows',
			array(
				'label'       => __( 'Rows', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(),
				'title_field' => '{{{ text }}}',
				'condition'   => array( 'source' => 'custom' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Contact', 'lenz-plus' ) );
		$this->add_text_style( 'title', '.lzp-col-title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_box_style( 'card', '.lzp-contact', array( 'label' => __( 'Card', 'lenz-plus' ) ) );
		$this->add_text_style( 'row', '.lzp-contact__text', array( 'label' => __( 'Rows', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the column. */
	protected function render(): void {
		$s    = $this->get_settings_for_display();
		$rows = 'custom' === $s['source'] ? self::custom_rows( (array) $s['rows'] ) : self::theme_rows();

		if ( ! $rows ) {
			$this->editor_hint( __( 'No rows yet: save addresses in Lenz\'s footer settings, or add rows here.', 'lenz-plus' ) );
			return;
		}

		$items = '';
		foreach ( $rows as $row ) {
			$items .= self::row_html( $row );
		}

		printf(
			'<div class="lzp-contact-col">%1$s<ul class="lzp-contact">%2$s</ul></div>',
			'' !== (string) $s['title'] ? '<h3 class="lzp-col-title">' . esc_html( $s['title'] ) . '</h3>' : '',
			$items // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in row_html().
		);
	}

	/**
	 * One row: a branch title, or an icon with a text that may be a link.
	 *
	 * @param array $row `type` (`branch`|`item`), `icon`, `text`, `url`.
	 */
	private static function row_html( array $row ): string {
		if ( 'branch' === $row['type'] ) {
			return '<li class="lzp-contact__branch">' . esc_html( $row['text'] ) . '</li>';
		}

		$dir  = self::is_ltr( $row['text'], $row['url'] ) ? ' dir="ltr"' : '';
		$text = '' !== $row['url']
			? sprintf( '<a class="lzp-contact__text" href="%1$s"%2$s>%3$s</a>', esc_url( $row['url'], array( 'http', 'https', 'tel', 'mailto', 'geo' ) ), $dir, esc_html( $row['text'] ) )
			: sprintf( '<span class="lzp-contact__text"%1$s>%2$s</span>', $dir, esc_html( $row['text'] ) );

		return '<li class="lzp-contact__row">' . self::icon( $row['icon'], 'lzp-contact__icon' ) . $text . '</li>';
	}

	/**
	 * Whether a value reads left to right: phone and email links, numbers,
	 * and text without Persian or Arabic letters (a website).
	 *
	 * @param string $text Row text.
	 * @param string $url  Row link.
	 */
	private static function is_ltr( string $text, string $url ): bool {
		if ( 0 === strpos( $url, 'tel:' ) || 0 === strpos( $url, 'mailto:' ) ) {
			return true;
		}

		// Letters only: Persian digits are in the Arabic block too, and a number reads left to right either way.
		return ! preg_match( '/[\x{0621}-\x{064A}\x{067E}-\x{06D3}]/u', $text );
	}

	/**
	 * Rows from the branches saved in Lenz's footer. The branch name heads
	 * its rows only when there is more than one branch.
	 *
	 * @return array<int, array{type:string, icon:string, text:string, url:string}>
	 */
	private static function theme_rows(): array {
		$branches = Theme_Bridge::footer_addresses();
		$rows     = array();

		foreach ( $branches as $branch ) {
			if ( count( $branches ) > 1 && '' !== $branch['title'] ) {
				$rows[] = self::row( 'branch', '', $branch['title'] );
			}
			if ( '' !== $branch['address'] ) {
				$rows[] = self::row( 'item', 'map-pin', $branch['address'], '#' !== $branch['link'] ? $branch['link'] : '' );
			}
			if ( '' !== $branch['phone'] ) {
				$link   = is_email( $branch['phone'] ) ? 'mailto:' . $branch['phone'] : Channels::url( 'phone', array( 'value' => $branch['phone'] ) );
				$rows[] = self::row( 'item', is_email( $branch['phone'] ) ? 'mail' : 'phone', $branch['phone'], $link );
			}
		}

		return $rows;
	}

	/**
	 * Rows typed in the widget.
	 *
	 * @param array $items Repeater rows.
	 */
	private static function custom_rows( array $items ): array {
		$rows = array();
		foreach ( $items as $item ) {
			if ( '' !== trim( (string) $item['text'] ) ) {
				$rows[] = self::row( 'item', (string) $item['icon'], (string) $item['text'], (string) ( $item['link']['url'] ?? '' ) );
			}
		}

		return $rows;
	}

	/**
	 * @param string $type `branch` or `item`.
	 * @param string $icon Icon key.
	 * @param string $text Text.
	 * @param string $url  Link ('' for none).
	 */
	private static function row( string $type, string $icon, string $text, string $url = '' ): array {
		return array(
			'type' => $type,
			'icon' => $icon,
			'text' => $text,
			'url'  => $url,
		);
	}
}
