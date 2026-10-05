<?php
/**
 * Plugin Name: Lenz Plus local test helpers
 * Description: Test site only (never shipped). Quiets third-party PHP 8.4 deprecations and
 *              stands in for Lenz's compiled colour file, which the licensed theme writes on save.
 */

defined( 'ABSPATH' ) || exit;

// WooCommerce, Elementor and the wp-cli phar emit PHP 8.4 deprecations on every request;
// they hide real problems in debug.log. PHPCompatibility (phpcs) guards our own code.
error_reporting( E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED ); // phpcs:ignore

// Elementor's logger reads error_get_last() on shutdown (even for masked errors) and prints it
// in WP-CLI. Shutdown functions run in registration order, and mu-plugins load first.
register_shutdown_function(
	static function () {
		$last = error_get_last();
		if ( $last && in_array( $last['type'], array( E_DEPRECATED, E_USER_DEPRECATED ), true ) ) {
			error_clear_last();
		}
	}
);

/*
 * Lenz's Redux compiler (encrypted Redux/Save.php) writes uploads/lenz.css when the options are
 * saved, but the options panel is hidden on this unlicensed copy. When the `lenz` option holds
 * colours (e.g. the dark demo: `bin/lwp option update lenz --format=json < redux_options.json`),
 * print the same :root variables so palettes can be tested. Delete the option for the light demo.
 */
add_action(
	'wp_enqueue_scripts',
	static function () {
		$options = get_option( 'lenz' );
		if ( ! is_array( $options ) ) {
			return;
		}
		$map = array(
			'body_color'        => '--body',
			'text_main_color'   => '--text-main',
			'primary_color_1'   => '--primary-1',
			'primary_color_2'   => '--primary-2',
			'secondary_color_1' => '--secondary-1',
			'secondary_color_2' => '--secondary-2',
			'secondary_color_3' => '--secondary-3',
			'gray_color_1'      => '--gray-1',
			'gray_color_2'      => '--gray-2',
			'gray_color_3'      => '--gray-3',
			'text_color_1'      => '--text-1',
			'text_color_2'      => '--text-2',
			'text_color_3'      => '--text-3',
			'text_color_4'      => '--text-4',
		);
		$css = '';
		foreach ( $map as $key => $var ) {
			$value = isset( $options[ $key ] ) ? $options[ $key ] : '';
			if ( is_array( $value ) ) {
				$value = isset( $value['color'] ) ? $value['color'] : '';
			}
			if ( is_string( $value ) && preg_match( '/^#[0-9a-fA-F]{3,8}$/', $value ) ) {
				$css .= $var . ':' . $value . ';';
			}
		}
		if ( $css ) {
			wp_add_inline_style( 'lenz', ':root{' . $css . '}' );
		}
	},
	20
);
