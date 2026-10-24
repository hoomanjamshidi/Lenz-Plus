<?php
/**
 * A map of the studio that loads only when the visitor asks for it.
 *
 * Map embeds (Google Maps, Neshan, Balad…) pull megabytes of scripts and
 * set third-party cookies; loading one with the page would cost the contact
 * page its speed score. The widget prints a light placeholder with the
 * address and a "show the map" button; map.js swaps in the iframe on click.
 * Without JavaScript the button is a link that opens the map in a new tab.
 *
 * The embed accepts the iframe code a map service hands out or its URL;
 * only an https `src` is kept.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Assets;
use LenzPlus\Modules\Builder\Elementor\Picture;

defined( 'ABSPATH' ) || exit;

/**
 * The «Map (Lenz+)» widget.
 */
final class Map_Embed extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-map';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Map', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-google-maps';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'map', 'location', 'neshan', 'balad', 'google', 'نقشه', 'نشان', 'بلد', 'لوکیشن' ) );
	}

	/** The click-to-load script. */
	public function get_script_depends(): array {
		return array( Assets::MAP_HANDLE );
	}

	/** Map, address and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Map', 'lenz-plus' ) );

		$this->add_control(
			'embed',
			array(
				'label'       => __( 'Map embed', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => '',
				'description' => __( 'Paste the embed code (iframe) or its address from Google Maps, Neshan or Balad.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'link',
			array(
				'label'       => __( 'Link to the map', 'lenz-plus' ),
				'type'        => Controls_Manager::URL,
				'description' => __( 'Opened by the "Directions" button, and by "Show the map" when JavaScript is off.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Place name', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Our studio', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'address',
			array(
				'label'   => __( 'Address', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => '',
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => __( '"Show the map" text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Show the map', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'route_text',
			array(
				'label'   => __( '"Directions" text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Directions', 'lenz-plus' ),
			)
		);

		$this->add_ratio_control( 'ratio', '16/9' );
		$this->add_responsive_control(
			'height',
			array(
				'label'       => __( 'Height', 'lenz-plus' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min' => 200,
						'max' => 800,
					),
				),
				'description' => __( 'Overrides the shape.', 'lenz-plus' ),
				'selectors'   => array( '{{WRAPPER}} .lzp-map' => '--lzp-map-ratio: auto; height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Map', 'lenz-plus' ) );
		$this->add_box_style( 'frame', '.lzp-map', array( 'label' => __( 'Frame', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/**
	 * The https `src` of an embed code or URL, '' when there is none.
	 *
	 * @param string $embed Pasted code or URL.
	 */
	public static function embed_src( string $embed ): string {
		$embed = trim( $embed );
		if ( preg_match( '/\ssrc\s*=\s*(["\'])(.*?)\1/iu', $embed, $m ) ) {
			$embed = html_entity_decode( $m[2], ENT_QUOTES, 'UTF-8' );
		}

		return 0 === strpos( $embed, 'https://' ) ? esc_url_raw( $embed, array( 'https' ) ) : '';
	}

	/** Prints the placeholder (and the map on click). */
	protected function render(): void {
		$s     = $this->get_settings_for_display();
		$src   = self::embed_src( (string) $s['embed'] );
		$link  = (string) ( $s['link']['url'] ?? '' );
		$open  = '' !== $link ? $link : $src;
		$title = (string) $s['title'];
		$ratio = in_array( $s['ratio'], Picture::RATIOS, true ) ? str_replace( '/', ' / ', (string) $s['ratio'] ) : '16 / 9';

		if ( '' === $open && '' === (string) $s['address'] ) {
			$this->editor_hint( __( 'Paste the map\'s embed code, or at least an address.', 'lenz-plus' ) );
			return;
		}

		$button = '';
		if ( '' !== $open ) {
			$attrs = ' target="_blank" rel="noopener"';
			if ( '' !== $src ) {
				$attrs .= ' data-lzp-map="' . esc_url( $src ) . '" data-title="' . esc_attr( '' !== $title ? $title : __( 'Map', 'lenz-plus' ) ) . '"';
			}
			$button = Button::markup(
				array(
					'text'    => (string) $s['button_text'],
					'url'     => $open,
					'variant' => 'primary',
					'size'    => 'sm',
					'icon'    => 'map-pin',
					'attrs'   => $attrs,
				)
			);
		}

		if ( '' !== $link && '' !== (string) $s['route_text'] ) {
			$button .= Button::markup(
				array(
					'text'    => (string) $s['route_text'],
					'url'     => $link,
					'variant' => 'secondary',
					'size'    => 'sm',
					'icon'    => 'compass',
					'attrs'   => ' target="_blank" rel="noopener"',
				)
			);
		}

		printf(
			'<div class="lzp-map" style="--lzp-map-ratio:%1$s"><div class="lzp-map__cover"><span class="lzp-map__pin" aria-hidden="true">%2$s</span>%3$s%4$s%5$s</div></div>',
			esc_attr( $ratio ),
			self::icon( 'map-pin' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup.
			'' !== $title ? '<p class="lzp-map__title">' . esc_html( $title ) . '</p>' : '',
			'' !== (string) $s['address'] ? '<p class="lzp-map__address">' . nl2br( esc_html( (string) $s['address'] ) ) . '</p>' : '',
			'' !== $button ? '<div class="lzp-map__actions">' . $button . '</div>' : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building.
		);
	}
}
