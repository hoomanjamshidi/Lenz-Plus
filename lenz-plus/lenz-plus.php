<?php
/**
 * Plugin Name:       Lenz Plus
 * Description:       Extra features for the Lenz photography theme: a customizable mobile bottom navigation, a floating support button, and Elementor page templates for the home, about, services, portfolio, courses and blog pages with their header and footer.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Hooman Jamshidi
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       lenz-plus
 * Domain Path:       /languages
 *
 * @package LenzPlus
 */

defined( 'ABSPATH' ) || exit;

define( 'LENZ_PLUS_VERSION', '1.1.0' );
define( 'LENZ_PLUS_FILE', __FILE__ );
define( 'LENZ_PLUS_DIR', plugin_dir_path( __FILE__ ) );
define( 'LENZ_PLUS_URL', plugin_dir_url( __FILE__ ) );
define( 'LENZ_PLUS_MIN_PHP', '7.4' );

// Bail out gracefully on unsupported PHP instead of fatalling on newer syntax.
if ( version_compare( PHP_VERSION, LENZ_PLUS_MIN_PHP, '<' ) ) {
	add_action(
		'admin_notices',
		static function () {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: %s: minimum PHP version. */
						__( 'Lenz Plus requires PHP %s or newer.', 'lenz-plus' ),
						LENZ_PLUS_MIN_PHP
					)
				)
			);
		}
	);
	return;
}

require_once LENZ_PLUS_DIR . 'includes/Autoloader.php';
\LenzPlus\Autoloader::register();

// Boot on `init` (early priority) so translations are available to every module.
add_action( 'init', array( \LenzPlus\Plugin::class, 'instance' ), 1 );
