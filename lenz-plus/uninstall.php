<?php
/**
 * Removes everything the plugin created: one `lenz_plus_<module>` option per
 * module. Pages built with the plugin's templates are the site's own content
 * and stay.
 *
 * @package LenzPlus
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Deletes plugin data on the current site.
 */
function lenz_plus_uninstall_site(): void {
	global $wpdb;

	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( 'lenz_plus_' ) . '%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- one-off cleanup.
	wp_cache_flush();
}

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids' ) ) as $lenz_plus_site_id ) {
		switch_to_blog( $lenz_plus_site_id );
		lenz_plus_uninstall_site();
		restore_current_blog();
	}
} else {
	lenz_plus_uninstall_site();
}
