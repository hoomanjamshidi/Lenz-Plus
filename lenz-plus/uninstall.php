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

	// Page templates (Elementor documents of the `lzp_template` post type), requests and sign-ups; their meta goes with them.
	$lenz_plus_posts = get_posts(
		array(
			'post_type'      => array( 'lzp_template', 'lzp_message', 'lzp_subscriber' ),
			// `any` skips trash and auto-drafts, which may still hold visitors' requests.
			'post_status'    => array_keys( get_post_stati() ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	foreach ( $lenz_plus_posts as $lenz_plus_post_id ) {
		wp_delete_post( $lenz_plus_post_id, true );
	}

	// The design a page was made from, and the details added to Lenz's portfolio items and to course products.
	foreach ( array( '_lzp_design', '_lzp_project', '_lzp_featured', '_lzp_course', '_lzp_is_course' ) as $lenz_plus_meta_key ) {
		delete_post_meta_by_key( $lenz_plus_meta_key );
	}

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
