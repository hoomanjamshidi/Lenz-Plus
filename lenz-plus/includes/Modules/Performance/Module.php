<?php
/**
 * Speed module: makes the Lenz theme lighter next to WP Rocket (or another
 * page cache) instead of repeating what the cache plugin already does.
 *
 * Lenz loads every library it has (sliders, galleries, select boxes, a date
 * picker, Font Awesome, two copies of the Bootstrap grid) on every page, and
 * its fonts hide text while they load. WP Rocket can minify, delay and cache
 * those files but cannot know which of them a Lenz page really uses. This
 * module leaves the unused ones out (Asset_Trimmer + Asset_Groups), shows text
 * at once (Fonts), trims WordPress and WooCommerce extras (Site_Tweaks), tells
 * WP Rocket what Lenz adds from JavaScript (Rocket) and clears the page cache
 * when Lenz+ output changes (Cache_Purge).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Performance;

use LenzPlus\Core\Module as Base_Module;
use LenzPlus\Core\Sanitizer;

defined( 'ABSPATH' ) || exit;

/**
 * Settings, admin panel and boot of the speed optimisations.
 */
final class Module extends Base_Module {

	/** Module id: the option name suffix and the admin page slug. */
	public function id(): string {
		return 'performance';
	}

	/** Feature name in the admin menu and on the dashboard. */
	public function title(): string {
		return __( 'Speed', 'lenz-plus' );
	}

	/** One-line summary on the dashboard card. */
	public function description(): string {
		return __( 'Load the theme\'s sliders, galleries and icon fonts only on pages that use them, show text while fonts load and trim WordPress extras. Works next to WP Rocket\'s settings.', 'lenz-plus' );
	}

	/** Semantic icon key for the admin menu and the dashboard card. */
	public function icon(): string {
		return 'bolt';
	}

	/** Default settings (see Schema). */
	public function defaults(): array {
		return Schema::defaults();
	}

	/** Cleans posted settings with the schema. */
	public function sanitize( array $input ): array {
		return Sanitizer::apply( Schema::fields(), $input, Schema::defaults() );
	}

	/** The «clear the cache now» button works while the module is off too. */
	public function register(): void {
		Cache_Purge::register_button();
		parent::register();
	}

	/** Hooks every switched-on optimisation. */
	protected function boot(): void {
		$settings = $this->settings();

		( new Asset_Trimmer( Asset_Groups::for_settings( $settings ) ) )->register();
		( new Fonts( $settings['font_swap'], $settings['font_preload'] ) )->register();
		( new Site_Tweaks( $settings ) )->register();

		if ( $settings['rocket_safe'] && Rocket::is_active() ) {
			Rocket::register();
		}
		if ( $settings['purge_on_save'] ) {
			( new Cache_Purge() )->register();
		}
	}

	/** Prints the settings panel. */
	public function render_admin(): void {
		$module = $this;
		require __DIR__ . '/views/admin.php';
	}
}
