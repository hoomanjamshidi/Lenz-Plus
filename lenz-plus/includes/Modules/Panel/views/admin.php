<?php
/**
 * Settings panel of the studio panel module (wp-admin).
 *
 * Every control binds to the settings by path (`data-lzp-bind`); the admin
 * shell saves them. The first card shows the panel's address and how to give
 * a staff member access (the «Studio manager» role).
 *
 * @var \LenzPlus\Modules\Panel\Module $module
 *
 * @package LenzPlus
 */

use LenzPlus\Admin\Fields;
use LenzPlus\Core\Icon_Library;
use LenzPlus\Modules\Panel\Access;
use LenzPlus\Modules\Panel\Schema;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included from a class method, so variables are local.

$ui_icon = static function ( string $key ): string {
	return Icon_Library::svg( 'phosphor-duotone', $key, false, 'lzp-ico' );
};

$slug      = $module->settings()['slug'];
$panel_url = get_option( 'permalink_structure' ) ? home_url( user_trailingslashit( $slug ) ) : add_query_arg( 'lzp_panel', 'home', home_url( '/' ) );

$panel_tabs = array(
	'general'  => array( 'mobile', __( 'Address', 'lenz-plus' ) ),
	'access'   => array( 'lock', __( 'Who can use it', 'lenz-plus' ) ),
	'sections' => array( 'grid', __( 'Sections', 'lenz-plus' ) ),
	'editors'  => array( 'notes', __( 'Text editor', 'lenz-plus' ) ),
);

$section_labels = array(
	'portfolio' => __( 'Portfolio (Lenz\'s projects with their gallery and project details)', 'lenz-plus' ),
	'posts'     => __( 'Articles', 'lenz-plus' ),
	'pages'     => __( 'Pages', 'lenz-plus' ),
	'courses'   => __( 'Courses (WooCommerce products marked as courses)', 'lenz-plus' ),
	'products'  => __( 'Products', 'lenz-plus' ),
	'orders'    => __( 'Orders', 'lenz-plus' ),
	'bookings'  => __( 'Project bookings', 'lenz-plus' ),
	'requests'  => __( 'Requests (Request form)', 'lenz-plus' ),
	'comments'  => __( 'Comments and reviews', 'lenz-plus' ),
	'media'     => __( 'Media (photos and videos)', 'lenz-plus' ),
);

$editor_labels = array(
	'post'      => __( 'Articles', 'lenz-plus' ),
	'page'      => __( 'Pages', 'lenz-plus' ),
	'portfolio' => __( 'Projects', 'lenz-plus' ),
	'product'   => __( 'Products and courses', 'lenz-plus' ),
);

$editors = array(
	'simple'    => __( 'Simple editor of the panel (recommended for phones)', 'lenz-plus' ),
	'classic'   => __( 'WordPress\'s classic editor, inside the panel', 'lenz-plus' ),
	'plain'     => __( 'Plain text box (HTML allowed)', 'lenz-plus' ),
	'wordpress' => __( 'WordPress\'s own editor (opens wp-admin)', 'lenz-plus' ),
);
?>
<div class="lzp-module" data-lzp-module="panel">

	<section class="lzp-module-head">
		<span class="lzp-module-head__icon" aria-hidden="true"><?php echo $ui_icon( 'mobile' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG. ?></span>
		<div class="lzp-module-head__text">
			<h1><?php echo esc_html( $module->title() ); ?></h1>
			<p><?php echo esc_html( $module->description() ); ?></p>
		</div>
		<label class="lzp-module-head__switch">
			<span data-lzp-show-if="enabled"><?php esc_html_e( 'Active', 'lenz-plus' ); ?></span>
			<span data-lzp-show-if="!enabled"><?php esc_html_e( 'Inactive', 'lenz-plus' ); ?></span>
			<span class="lzp-switch lzp-switch--lg">
				<input type="checkbox" data-lzp-bind="enabled" aria-label="<?php esc_attr_e( 'Enable the studio panel', 'lenz-plus' ); ?>">
				<span class="lzp-switch__track" aria-hidden="true"></span>
			</span>
		</label>
	</section>

	<div class="lzp-module-body lzp-module-body--wide">
		<div class="lzp-settings">

			<div class="lzp-tabs" role="tablist" data-lzp-tabs="panel">
				<?php foreach ( $panel_tabs as $tab_id => $panel_tab ) : ?>
					<button type="button" class="lzp-tab" role="tab" id="lzp-tab-<?php echo esc_attr( $tab_id ); ?>" aria-controls="lzp-panel-<?php echo esc_attr( $tab_id ); ?>" aria-selected="false" data-lzp-tab="<?php echo esc_attr( $tab_id ); ?>">
						<?php echo $ui_icon( $panel_tab[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_html( $panel_tab[1] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-general" aria-labelledby="lzp-tab-general">
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'The panel\'s address', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'The panel opens from WordPress itself («Studio panel» at the top of the wp-admin menu). Staff can also keep this address on their phone\'s home screen; it asks them to log in with their WordPress account.', 'lenz-plus' ); ?></p>
					</header>
					<p>
						<a class="lzp-btn lzp-btn--primary" href="<?php echo esc_url( $panel_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open the panel', 'lenz-plus' ); ?></a>
						<code dir="ltr"><?php echo esc_html( $panel_url ); ?></code>
					</p>
					<div class="lzp-fields lzp-fields--2">
						<?php
						Fields::text(
							'slug',
							__( 'Address', 'lenz-plus' ),
							array(
								'dir'         => 'ltr',
								'placeholder' => Schema::DEFAULT_SLUG,
								'help'        => __( 'Latin letters, digits and dashes, e.g. panel or studio. The address above changes after saving.', 'lenz-plus' ),
							)
						);
						Fields::text( 'title', __( 'Panel name', 'lenz-plus' ), array( 'placeholder' => $module->title() ) );
						Fields::text(
							'per_page',
							__( 'Items per list page', 'lenz-plus' ),
							array( 'type' => 'number' )
						);
						?>
					</div>
				</div>
			</section>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-access" aria-labelledby="lzp-tab-access" hidden>
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Roles that open the panel', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Give your staff the «Studio manager» role (Users → edit the user → Role): it can manage content, products and orders but not the site\'s settings. Inside the panel each person still only sees what their role allows.', 'lenz-plus' ); ?></p>
					</header>
					<p><a class="lzp-btn lzp-btn--soft" href="<?php echo esc_url( admin_url( 'user-new.php' ) ); ?>"><?php esc_html_e( 'Add a user', 'lenz-plus' ); ?></a></p>
					<div class="lzp-fields lzp-fields--2">
						<?php foreach ( wp_roles()->get_names() as $role_key => $role_name ) : ?>
							<?php
							Fields::toggle(
								'roles.' . $role_key,
								translate_user_role( $role_name ),
								Access::ROLE === $role_key ? array( 'chip' => array( __( 'Lenz+', 'lenz-plus' ), 'accent' ) ) : array()
							);
							?>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'The way in', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Panel users log in with their WordPress account as usual. The first item of the wp-admin menu and a box at the top of the dashboard open the panel.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields">
						<?php
						Fields::toggle( 'hide_admin', __( 'Keep staff out of wp-admin', 'lenz-plus' ), array( 'help' => __( 'Panel users who cannot manage the site\'s settings are sent from wp-admin to the panel and do not see the admin bar. Administrators keep wp-admin.', 'lenz-plus' ) ) );
						Fields::toggle( 'login_redirect', __( 'Open the panel after logging in', 'lenz-plus' ), array( 'help' => __( 'For panel users who cannot manage the site\'s settings, on WordPress\'s and WooCommerce\'s login forms.', 'lenz-plus' ) ) );
						Fields::toggle( 'account_link', __( 'Link in the account menu', 'lenz-plus' ), array( 'help' => __( 'Adds the panel to WooCommerce\'s My account menu of panel users.', 'lenz-plus' ) ) );
						Fields::toggle( 'admin_bar_link', __( 'Link in the admin bar', 'lenz-plus' ) );
						?>
					</div>
				</div>
			</section>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-sections" aria-labelledby="lzp-tab-sections" hidden>
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Sections of the panel', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Switch off what the studio does not use, so the panel stays short. Sections that need WooCommerce or the page templates show only while those are active.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields lzp-fields--2">
						<?php foreach ( Schema::SECTIONS as $section ) : ?>
							<?php Fields::toggle( 'sections.' . $section, $section_labels[ $section ] ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-editors" aria-labelledby="lzp-tab-editors" hidden>
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Text editor', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Which editor writes the text of each content type in the panel. The simple editor has headings, bold, lists, quotes, links and photos and works well on phones. Texts built with Elementor always open in Elementor.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields lzp-fields--2">
						<?php foreach ( Schema::EDITOR_TYPES as $editor_type ) : ?>
							<?php Fields::select( 'editor.' . $editor_type, $editor_labels[ $editor_type ], $editors ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>

		</div>
	</div>
</div>
