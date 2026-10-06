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
		return array();
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
