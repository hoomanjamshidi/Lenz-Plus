<?php
/**
 * End of every panel document: the footer scripts (the panel's script, and
 * WordPress's editor and media templates when the classic editor is used).
 *
 * @package LenzPlus
 */

defined( 'ABSPATH' ) || exit;

wp_print_footer_scripts();
if ( did_action( 'wp_enqueue_media' ) ) {
	wp_print_media_templates();
}
?>
</body>
</html>
