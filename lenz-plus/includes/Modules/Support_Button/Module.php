<?php
/**
 * Floating support button module: a corner button that opens the site's
 * contact channels (Telegram, WhatsApp, Bale, Eitaa, …).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Support_Button;

use LenzPlus\Admin\Admin;
use LenzPlus\Core\Icon_Library;
use LenzPlus\Core\Module as Base_Module;
use LenzPlus\Core\Sanitizer;

defined( 'ABSPATH' ) || exit;

/**
 * Settings, admin panel and boot of the floating support button.
 */
final class Module extends Base_Module {

	public function id(): string {
		return 'support_button';
	}

	public function title(): string {
		return __( 'Floating support button', 'lenz-plus' );
	}

	public function description(): string {
		return __( 'A corner button that opens Telegram, WhatsApp, Bale, Eitaa and more, and moves out of the way of the bottom navigation.', 'lenz-plus' );
	}

	public function icon(): string {
		return 'support';
	}

	public function defaults(): array {
		return Schema::defaults();
	}

	public function sanitize( array $input ): array {
		$clean          = Sanitizer::apply( Schema::fields(), $input, Schema::defaults() );
		$clean['order'] = Schema::complete_order( $clean['order'] );

		return $clean;
	}

	protected function boot(): void {
		( new Frontend( $this ) )->register();
	}

	/**
	 * Adds channels introduced in newer versions to a saved order.
	 *
	 * @param array $settings Settings merged with defaults.
	 */
	protected function normalize( array $settings ): array {
		$settings['order'] = Schema::complete_order( is_array( $settings['order'] ) ? $settings['order'] : array() );

		return $settings;
	}

	public function render_admin(): void {
		$module = $this;
		require __DIR__ . '/views/admin.php';
	}

	public function enqueue_admin_assets(): void {
		// The preview renders with the real front-end stylesheet, in Lenz's font.
		wp_enqueue_style( 'lzp-support-button', LENZ_PLUS_URL . 'assets/modules/support-button/css/support-button.css', array(), LENZ_PLUS_VERSION );
		Admin::enqueue_theme_preview_assets();

		wp_enqueue_script(
			'lzp-support-button-admin',
			LENZ_PLUS_URL . 'assets/modules/support-button/js/support-button-admin.js',
			array( 'lzp-admin', 'jquery-ui-sortable' ),
			LENZ_PLUS_VERSION,
			true
		);
	}

	public function admin_script_data(): array {
		$channels = array();
		foreach ( Channels::all() as $id => $channel ) {
			$channels[ $id ] = $channel + array( 'glyph' => Channels::glyph( $id ) );
		}

		$icons = array();
		foreach ( Schema::ICONS as $key ) {
			$icons[ $key ] = Icon_Library::svg( 'phosphor', $key, true );
		}

		return array(
			'channels'  => $channels,
			'icons'     => $icons,
			'closeIcon' => Icon_Library::svg( 'phosphor', 'close' ),
			'i18n'      => array(
				'notSet'  => __( 'Not set', 'lenz-plus' ),
				'invalid' => __( 'Check the value', 'lenz-plus' ),
				'off'     => __( 'Off', 'lenz-plus' ),
				'opens'   => __( 'Opens:', 'lenz-plus' ),
				'support' => __( 'Support', 'lenz-plus' ),
				'close'   => __( 'Close', 'lenz-plus' ),
			),
		);
	}
}
