<?php
/**
 * Settings schema and defaults for the speed module.
 *
 * Every switch is one optimisation that WP Rocket does not do (or does not
 * know Lenz well enough to do), so all of them can stay on next to WP
 * Rocket's own settings. Risky ones (jQuery Migrate, embeds) start off.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Performance;

defined( 'ABSPATH' ) || exit;

/**
 * Settings shape and defaults.
 */
final class Schema {

	/** Font Awesome: always (as Lenz loads it) or only on pages that show an fa-* icon. */
	public const FONT_AWESOME = array( 'always', 'auto' );

	/** Heartbeat: as WordPress runs it, slower in the admin, or also off for the site's visitors. */
	public const HEARTBEAT = array( 'default', 'slow', 'off_front' );

	/** Every setting's default. */
	public static function defaults(): array {
		return array(
			'enabled'        => true,
			// Lenz theme.
			'lenz_assets'    => true,
			'font_awesome'   => 'auto',
			'bootstrap_rtl'  => true,
			'font_swap'      => true,
			'font_preload'   => true,
			// WordPress.
			'emojis'         => true,
			'block_styles'   => true,
			'head_cleanup'   => true,
			'dashicons'      => true,
			'embeds'         => false,
			'jquery_migrate' => false,
			'heartbeat'      => 'slow',
			// 0 = as WordPress keeps them (no limit).
			'revisions'      => 0,
			// WooCommerce.
			'cart_fragments' => true,
			'wc_blocks'      => true,
			// WP Rocket and other page caches.
			'rocket_safe'    => true,
			'purge_on_save'  => true,
		);
	}

	/**
	 * Sanitizer schema with the same shape as defaults().
	 */
	public static function fields(): array {
		$bool = array( 'type' => 'bool' );

		return array(
			'enabled'        => $bool,
			'lenz_assets'    => $bool,
			'font_awesome'   => array(
				'type'    => 'enum',
				'options' => self::FONT_AWESOME,
			),
			'bootstrap_rtl'  => $bool,
			'font_swap'      => $bool,
			'font_preload'   => $bool,
			'emojis'         => $bool,
			'block_styles'   => $bool,
			'head_cleanup'   => $bool,
			'dashicons'      => $bool,
			'embeds'         => $bool,
			'jquery_migrate' => $bool,
			'heartbeat'      => array(
				'type'    => 'enum',
				'options' => self::HEARTBEAT,
			),
			'revisions'      => array(
				'type' => 'int',
				'min'  => 0,
				'max'  => 50,
			),
			'cart_fragments' => $bool,
			'wc_blocks'      => $bool,
			'rocket_safe'    => $bool,
			'purge_on_save'  => $bool,
		);
	}
}
