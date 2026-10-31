<?php
/**
 * Opening of every panel document (shell, no access): doctype, head
 * with only the panel's styles and head scripts, and the body tag.
 *
 * @var \LenzPlus\Modules\Panel\App $app
 * @var string                      $doc_title  Title of the page.
 * @var string                      $body_class Body classes.
 *
 * @package LenzPlus
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included from a class method, so variables are local.
?>
<!doctype html>
<html lang="<?php echo esc_attr( str_replace( '_', '-', get_locale() ) ); ?>" dir="<?php echo esc_attr( $app->direction() ); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="robots" content="noindex, nofollow">
	<meta name="theme-color" content="<?php echo esc_attr( $app->theme_color() ); ?>">
	<title><?php echo esc_html( $doc_title . ' – ' . $app->title() ); ?></title>
	<?php
	wp_site_icon();
	wp_print_styles();
	wp_print_head_scripts();
	?>
</head>
<body class="<?php echo esc_attr( $body_class ); ?>">
