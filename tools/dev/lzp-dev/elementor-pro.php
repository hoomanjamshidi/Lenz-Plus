<?php
/**
 * Test site only (never shipped): stands in for the two Elementor Pro classes
 * Lenz uses.
 *
 * Lenz's "posts archive" widget (inc/ElementorControls.php) calls
 * ElementorPro\Core\Utils::get_public_post_types(), reads the constants of
 * ElementorPro\Modules\QueryControl\Module and adds Pro's `query` control
 * without checking that Elementor Pro is active. Lenz requires Pro, but the test site cannot have it, so every
 * Elementor request that builds that widget's controls (editor, global-classes
 * usage scan) would die with a fatal error and hide real ones. Loaded by
 * lzp-dev.php only when nothing else defines these classes.
 */

namespace ElementorPro\Core {

	defined( 'ABSPATH' ) || exit;

	/** The one method Lenz uses. */
	final class Utils {

		/**
		 * Public post types as name => label, like Elementor Pro's.
		 *
		 * @param array $args Extra get_post_types() arguments.
		 */
		public static function get_public_post_types( $args = array() ) {
			$types = get_post_types( array_merge( array( 'public' => true ), $args ), 'objects' );
			unset( $types['elementor_library'], $types['attachment'] );

			return wp_list_pluck( $types, 'label', 'name' );
		}
	}
}

namespace ElementorPro\Modules\QueryControl {

	/** The constants Lenz reads (Pro's own values). */
	final class Module {
		const QUERY_CONTROL_ID    = 'query';
		const QUERY_OBJECT_POST   = 'post';
		const QUERY_OBJECT_TAX    = 'tax';
		const QUERY_OBJECT_AUTHOR = 'author';
	}
}

namespace ElementorPro\Modules\QueryControl\Controls {

	/** Pro's `query` control (an AJAX select2), as a plain select2. */
	final class Query extends \Elementor\Control_Select2 {

		/** Control type id. */
		public function get_type() {
			return 'query';
		}
	}
}
