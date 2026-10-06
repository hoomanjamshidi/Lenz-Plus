<?php
/**
 * Row of square social buttons (the mockups' footer: 42px bordered squares).
 *
 * By default the accounts come from the support button's channels
 * (Instagram, Telegram, WhatsApp, Bale, Eitaa), so each one is typed once;
 * a custom list is available for anything else. Brand_Box prints the same
 * row through markup().
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use LenzPlus\Core\Icon_Library;
use LenzPlus\Modules\Support_Button\Channels;
use LenzPlus\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * The «Social links (Lenz+)» widget and the shared row markup.
 */
final class Social_Links extends Base {

	/** Support button channels that are social accounts (phone, email and the custom link are not). */
	private const SOCIAL_CHANNELS = array( 'instagram', 'telegram', 'whatsapp', 'bale', 'eitaa' );

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-social-links';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Social links', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-social-icons';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'social', 'instagram', 'telegram', 'شبکه اجتماعی', 'اینستاگرام' ) );
	}

	/**
	 * The links of the support button's social channels that are switched on.
	 *
	 * @return array<int, array{url:string, label:string, icon:string}>
	 */
	public static function from_support_button(): array {
		$module = Plugin::instance()->module( 'support_button' );
		if ( ! $module ) {
			return array();
		}

		$links = array();
		foreach ( Channels::ready( $module->settings() ) as $channel ) {
			if ( in_array( $channel['id'], self::SOCIAL_CHANNELS, true ) ) {
				$links[] = array(
					'url'   => $channel['url'],
					'label' => $channel['label'],
					// The chosen icon pack's glyph where it has one (as in the mockups), else the brand glyph.
					'icon'  => in_array( $channel['id'], Icon_Library::icon_keys(), true ) ? self::icon( $channel['id'] ) : $channel['glyph'],
				);
			}
		}

		return $links;
	}

	/**
	 * The row of buttons ('' when there are no links).
	 *
	 * @param array $links Links (`url`, `label`, trusted `icon` markup).
	 */
	public static function markup( array $links ): string {
		if ( ! $links ) {
			return '';
		}

		$items = '';
		foreach ( $links as $link ) {
			$items .= sprintf(
				'<li><a class="lzp-socials__link" href="%1$s" target="_blank" rel="noopener" aria-label="%2$s">%3$s</a></li>',
				esc_url( $link['url'] ),
				esc_attr( $link['label'] ),
				$link['icon']
			);
		}

		return '<ul class="lzp-socials">' . $items . '</ul>';
	}

	/** Source, custom links and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Links', 'lenz-plus' ) );

		$this->add_control(
			'source',
			array(
				'label'       => __( 'Accounts from', 'lenz-plus' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'support',
				'options'     => array(
					'support' => __( 'The support button\'s channels', 'lenz-plus' ),
					'custom'  => __( 'This list', 'lenz-plus' ),
				),
				'description' => __( 'Instagram, Telegram, WhatsApp, Bale and Eitaa channels switched on in Lenz+ → Support button.', 'lenz-plus' ),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'instagram',
				'options' => self::icon_options( false ),
			)
		);
		$repeater->add_control(
			'label',
			array(
				'label'   => __( 'Name', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'Instagram',
			)
		);
		$repeater->add_control(
			'url',
			array(
				'label' => __( 'Link', 'lenz-plus' ),
				'type'  => Controls_Manager::URL,
			)
		);

		$this->add_control(
			'links',
			array(
				'label'       => __( 'Links', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(),
				'title_field' => '{{{ label }}}',
				'condition'   => array( 'source' => 'custom' ),
			)
		);

		$this->add_align_control( 'align', '.lzp-socials' );

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Buttons', 'lenz-plus' ) );

		$this->add_control(
			'color',
			array(
				'label'     => __( 'Icon colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lzp-socials__link' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'border_color',
			array(
				'label'     => __( 'Border colour', 'lenz-plus' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .lzp-socials__link' => 'border-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/** Prints the row. */
	protected function render(): void {
		$s     = $this->get_settings_for_display();
		$links = 'custom' === $s['source'] ? $this->custom_links( (array) $s['links'] ) : self::from_support_button();

		if ( ! $links ) {
			$this->editor_hint( __( 'No accounts yet: switch on Instagram, Telegram, WhatsApp, Bale or Eitaa in Lenz+ → Support button, or add links here.', 'lenz-plus' ) );
			return;
		}

		echo self::markup( $links ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in markup(); bundled icons.
	}

	/**
	 * Links of the custom list that have a URL.
	 *
	 * @param array $rows Repeater rows.
	 */
	private function custom_links( array $rows ): array {
		$links = array();
		foreach ( $rows as $row ) {
			if ( ! empty( $row['url']['url'] ) ) {
				$links[] = array(
					'url'   => (string) $row['url']['url'],
					'label' => (string) $row['label'],
					'icon'  => self::icon( (string) $row['icon'] ),
				);
			}
		}

		return $links;
	}
}
