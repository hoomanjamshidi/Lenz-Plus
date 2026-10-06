<?php
/**
 * Removes everything the plugin created: one `lenz_plus_<module>` option per
 * module and the page templates. Pages made from the page designs are the
 * site's own content and stay.
 *
 * @package LenzPlus
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Deletes plugin data on the current site.
 */
function lenz_plus_uninstall_site(): void {
	global $wpdb;

	// Page templates (Elementor documents of the `lzp_template` post type); their meta goes with them.
	$lenz_plus_posts = get_posts(
		array(
			'post_type'      => array( 'lzp_template' ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	foreach ( $lenz_plus_posts as $lenz_plus_post_id ) {
		wp_delete_post( $lenz_plus_post_id, true );
	}

	// The design a page was made from.
	delete_post_meta_by_key( '_lzp_design' );

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
