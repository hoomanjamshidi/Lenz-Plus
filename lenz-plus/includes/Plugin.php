<?php
/**
 * Plugin bootstrap: loads translations, instantiates feature modules and the admin UI.
 *
 * @package LenzPlus
 */

namespace LenzPlus;

use LenzPlus\Admin\Admin;
use LenzPlus\Core\Module;
use LenzPlus\Modules\Bottom_Nav\Module as Bottom_Nav_Module;

defined( 'ABSPATH' ) || exit;

/**
 * Singleton that owns the module instances for the request.
 */
final class Plugin {

	public const TEXT_DOMAIN = 'lenz-plus';

	/** @var Plugin|null */
	private static $instance = null;

	/** @var array<string, Module> Registered modules keyed by module id. */
	private $modules = array();

	/**
	 * Hooked on `init` (see the main plugin file) so translations are loaded
	 * before any module touches translatable strings (WP 6.7+ requirement).
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		load_plugin_textdomain( self::TEXT_DOMAIN, false, dirname( plugin_basename( LENZ_PLUS_FILE ) ) . '/languages' );

		$this->register_modules();

		if ( is_admin() ) {
			( new Admin( $this ) )->register();
		}
	}

	/**
	 * Instantiates every module class. Third parties can add their own module
	 * (a subclass of Core\Module) through the `lenz_plus_modules` filter.
	 */
	private function register_modules(): void {
		$classes = apply_filters( 'lenz_plus_modules', self::default_modules() );

		foreach ( (array) $classes as $class ) {
			if ( ! is_string( $class ) || ! is_subclass_of( $class, Module::class ) ) {
				continue;
			}

			/** @var Module $module */
			$module                         = new $class();
			$this->modules[ $module->id() ] = $module;
			$module->register();
		}
	}

	/**
	 * The plugin's own modules, in admin menu order.
	 *
	 * @return array<int, class-string<Module>>
	 */
	private static function default_modules(): array {
		return array( Bottom_Nav_Module::class );
	}

	/**
	 * Every registered module, keyed by module id.
	 *
	 * @return array<string, Module>
	 */
	public function modules(): array {
		return $this->modules;
	}

	/**
	 * One module by id, or null when it is not registered.
	 *
	 * @param string $id Module id (e.g. `bottom_nav`).
	 */
	public function module( string $id ): ?Module {
		return $this->modules[ $id ] ?? null;
	}
}
