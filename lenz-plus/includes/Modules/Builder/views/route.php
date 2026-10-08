<?php
/**
 * A route page (portfolio or post list, project or article) rendered with a Lenz+
 * Elementor template, between Lenz's header and footer.
 *
 * Loaded through `template_include`, so it runs in the global scope. A single
 * item runs the main loop, so the template's widgets see the project as the
 * current post. A list leaves the loop to the grid widget ("items of the
 * page being viewed").
 *
 * @package LenzPlus
 */

use LenzPlus\Modules\Builder\Page_Routes;
use LenzPlus\Modules\Builder\Renderer;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template file.

$lzp_routes      = Page_Routes::current();
$lzp_template_id = $lzp_routes ? $lzp_routes->template_id() : 0;

get_header();

// Like Elementor's full-width page template: no `#page-body`, whose boxed width and header padding belong to Lenz's own layouts.
echo '<main class="lzp-route-body">';

if ( is_singular() ) {
	while ( have_posts() ) :
		the_post();

		if ( post_password_required() ) {
			echo '<div class="lzp-route-locked">' . get_the_password_form() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core markup.
			continue;
		}

		echo Renderer::render( $lzp_template_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor output.
	endwhile;
} else {
	echo Renderer::render( $lzp_template_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor output.
}

echo '</main>';

get_footer();
