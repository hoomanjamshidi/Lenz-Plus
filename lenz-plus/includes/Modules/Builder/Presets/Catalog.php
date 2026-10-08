<?php
/**
 * Registry of the ready-made designs (presets).
 *
 * Each entry names its template kind, admin label and description, and the
 * builder method that returns its Elementor data (written with El::box() /
 * El::w()). Library installs them as editable templates and refreshes the
 * unedited ones when the plugin version changes.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Presets;

defined( 'ABSPATH' ) || exit;

/**
 * Preset keys → kind, label, description and builder.
 */
final class Catalog {

	/**
	 * Every preset, in admin order.
	 *
	 * @return array<string, array{type:string, label:string, description:string, build:callable}>
	 */
	public static function all(): array {
		return array(
			'header'         => array(
				'type'        => 'header',
				'label'       => __( 'Header', 'lenz-plus' ),
				'description' => __( 'Logo and name, the menu, a booking button and the phone, on a translucent bar; a compact bar with the menu button on phones.', 'lenz-plus' ),
				'build'       => array( Header::class, 'main' ),
			),
			'footer'         => array(
				'type'        => 'footer',
				'label'       => __( 'Footer', 'lenz-plus' ),
				'description' => __( 'Brand box with a description and social buttons, both footer menus and the contact card, on the ink band.', 'lenz-plus' ),
				'build'       => array( Footer::class, 'full' ),
			),
			'footer-compact' => array(
				'type'        => 'footer',
				'label'       => __( 'Compact footer', 'lenz-plus' ),
				'description' => __( 'Brand box, quick links and the contact card, as on the about and project pages.', 'lenz-plus' ),
				'build'       => array( Footer::class, 'compact' ),
			),
			'about'          => array(
				'type'        => 'about',
				'label'       => __( 'About me', 'lenz-plus' ),
				'description' => __( 'Portrait hero, key numbers, story and career path, a framed motto, working steps, equipment, awards, collaborations and a call to action.', 'lenz-plus' ),
				'build'       => array( About::class, 'page' ),
			),
			'services'       => array(
				'type'        => 'services',
				'label'       => __( 'Services', 'lenz-plus' ),
				'description' => __( 'Hero, jump tiles, four services with photos (one on the ink band), price cards with add-ons, project steps, FAQ and a call to action.', 'lenz-plus' ),
				'build'       => array( Services::class, 'page' ),
			),
		);
	}

	/**
	 * Every preset key.
	 *
	 * @return string[]
	 */
	public static function keys(): array {
		return array_keys( self::all() );
	}

	/**
	 * One preset, or null for an unknown key.
	 *
	 * @param string $key Preset key.
	 */
	public static function get( string $key ): ?array {
		return self::all()[ $key ] ?? null;
	}

	/**
	 * Elementor data for a preset.
	 *
	 * @param string $key Preset key.
	 */
	public static function build( string $key ): array {
		$preset = self::get( $key );

		return $preset ? El::finalize( call_user_func( $preset['build'] ) ) : array();
	}
}
