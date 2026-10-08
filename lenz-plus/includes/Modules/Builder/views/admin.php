<?php
/**
 * Settings panel for the page templates module.
 *
 * Static structure lives here; builder-admin.js renders the template library,
 * the page designs and the pages made from them from `lzpAdmin.moduleData`.
 *
 * @var \LenzPlus\Modules\Builder\Module $module
 *
 * @package LenzPlus
 */

use LenzPlus\Admin\Fields;
use LenzPlus\Core\Icon_Library;
use LenzPlus\Modules\Builder\Contact_Messages;
use LenzPlus\Modules\Builder\Library;
use LenzPlus\Modules\Builder\Newsletter;
use LenzPlus\Modules\Builder\Schema;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included from a class method, so variables are local.

$ui_icon = static function ( string $key ): string {
	return Icon_Library::svg( 'phosphor-duotone', $key, false, 'lzp-ico' );
};

$elementor = Library::elementor_status();

$panel_tabs = array(
	'pages'   => array( 'home', __( 'Pages', 'lenz-plus' ) ),
	'parts'   => array( 'sliders', __( 'Header & footer', 'lenz-plus' ) ),
	'routes'  => array( 'images', __( 'Site pages', 'lenz-plus' ) ),
	'library' => array( 'grid', __( 'Templates', 'lenz-plus' ) ),
	'forms'   => array( 'chat', __( 'Forms', 'lenz-plus' ) ),
	'brand'   => array( 'palette', __( 'Colours & options', 'lenz-plus' ) ),
);

$sticky_options = array(
	'none'      => __( 'Off', 'lenz-plus' ),
	'always'    => __( 'Always', 'lenz-plus' ),
	'scroll_up' => __( 'Smart', 'lenz-plus' ),
);

$area_titles = array(
	'header' => array( __( 'Header on desktop', 'lenz-plus' ), __( 'Header on phones & tablets', 'lenz-plus' ) ),
	'footer' => array( __( 'Footer on desktop', 'lenz-plus' ), __( 'Footer on phones & tablets', 'lenz-plus' ) ),
);

$route_titles = array(
	'portfolio_archive' => array( __( 'Portfolio list', 'lenz-plus' ), __( 'The portfolio archive and its category pages.', 'lenz-plus' ) ),
	'portfolio'         => array( __( 'Project page', 'lenz-plus' ), __( 'Every portfolio item. Its title, text, photos and the Project details box below Lenz\'s gallery fill the design.', 'lenz-plus' ) ),
	'blog'              => array( __( 'Post list', 'lenz-plus' ), __( 'The posts page, categories, tags, authors, date archives and searches from the blog\'s search box.', 'lenz-plus' ) ),
	'post'              => array( __( 'Article', 'lenz-plus' ), __( 'Every blog post: its title, excerpt, featured image, text, headings (for the table of contents) and comments fill the design.', 'lenz-plus' ) ),
	'course'            => array( __( 'Course page', 'lenz-plus' ), __( 'WooCommerce products marked as courses (Course details box on the product editor). Other products keep Lenz\'s product page.', 'lenz-plus' ) ),
);

$brand_labels = array(
	'ink'           => __( 'Ink (headings, primary buttons, dark bands)', 'lenz-plus' ),
	'accent'        => __( 'Accent (hover, accent bars, icons)', 'lenz-plus' ),
	'text'          => __( 'Body text', 'lenz-plus' ),
	'sub'           => __( 'Secondary text', 'lenz-plus' ),
	'muted'         => __( 'Muted text', 'lenz-plus' ),
	'bg'            => __( 'Page and card background', 'lenz-plus' ),
	'soft'          => __( 'Soft band background', 'lenz-plus' ),
	'chip'          => __( 'Chips and tiles', 'lenz-plus' ),
	'line'          => __( 'Borders', 'lenz-plus' ),
	'line_strong'   => __( 'Strong borders (white buttons)', 'lenz-plus' ),
	'dashed'        => __( 'Dashed borders', 'lenz-plus' ),
	'field_line'    => __( 'Form field borders', 'lenz-plus' ),
	'dark_surface'  => __( 'Cards on dark bands', 'lenz-plus' ),
	'on_dark'       => __( 'Text on dark bands', 'lenz-plus' ),
	'on_dark_muted' => __( 'Muted text on dark bands', 'lenz-plus' ),
	'on_dark_line'  => __( 'Borders on dark bands', 'lenz-plus' ),
	'on_dark_hover' => __( 'Hover on dark bands', 'lenz-plus' ),
	'success'       => __( 'Success', 'lenz-plus' ),
	'danger'        => __( 'Warning', 'lenz-plus' ),
);

$packs = array();
foreach ( Icon_Library::catalog()['packs'] as $pack_id => $pack ) {
	if ( Icon_Library::FONT_AWESOME !== $pack_id ) {
		$packs[ $pack_id ] = $pack['label'];
	}
}
?>
<div class="lzp-module lzp-builder" data-lzp-module="builder">

	<section class="lzp-module-head">
		<span class="lzp-module-head__icon" aria-hidden="true"><?php echo $ui_icon( 'palette' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG. ?></span>
		<div class="lzp-module-head__text">
			<h1><?php echo esc_html( $module->title() ); ?></h1>
			<p><?php echo esc_html( $module->description() ); ?></p>
		</div>
		<label class="lzp-module-head__switch">
			<span data-lzp-show-if="enabled"><?php esc_html_e( 'Active', 'lenz-plus' ); ?></span>
			<span data-lzp-show-if="!enabled"><?php esc_html_e( 'Inactive', 'lenz-plus' ); ?></span>
			<span class="lzp-switch lzp-switch--lg">
				<input type="checkbox" data-lzp-bind="enabled" aria-label="<?php esc_attr_e( 'Enable page templates', 'lenz-plus' ); ?>">
				<span class="lzp-switch__track" aria-hidden="true"></span>
			</span>
		</label>
	</section>

	<?php if ( ! $elementor['active'] ) : ?>
		<div class="lzp-notice">
			<?php echo $ui_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p>
				<?php esc_html_e( 'Elementor is not active. The designs are Elementor templates, so install and activate the free Elementor plugin first.', 'lenz-plus' ); ?>
				<a href="<?php echo esc_url( $elementor['installUrl'] ); ?>"><?php esc_html_e( 'Install Elementor', 'lenz-plus' ); ?></a>
			</p>
		</div>
	<?php elseif ( ! $elementor['container'] ) : ?>
		<div class="lzp-notice" data-lzp-container-notice>
			<?php echo $ui_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p><?php esc_html_e( 'The designs use Elementor\'s Flexbox Containers, which are switched off on this site. Turn them on to display and edit the templates.', 'lenz-plus' ); ?></p>
			<button type="button" class="lzp-btn lzp-btn--primary" data-lzp-activate-container><?php esc_html_e( 'Turn on containers', 'lenz-plus' ); ?></button>
		</div>
	<?php endif; ?>

	<div class="lzp-module-body lzp-module-body--wide">
		<div class="lzp-settings">

			<div class="lzp-tabs" role="tablist" data-lzp-tabs="builder">
				<?php foreach ( $panel_tabs as $tab_id => $panel_tab ) : ?>
					<button type="button" class="lzp-tab" role="tab" id="lzp-tab-<?php echo esc_attr( $tab_id ); ?>" aria-controls="lzp-panel-<?php echo esc_attr( $tab_id ); ?>" aria-selected="false" data-lzp-tab="<?php echo esc_attr( $tab_id ); ?>">
						<?php echo $ui_icon( $panel_tab[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_html( $panel_tab[1] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-pages" aria-labelledby="lzp-tab-pages">
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Ready-made pages', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Home, about, services and courses pages. Pick a design and create a new page from it. The page is a normal Elementor page: change the texts and pictures, move or remove sections, add your own. Portfolio items, posts and courses come from your site automatically.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-page-designs" data-lzp-home-designs></div>
				</div>

				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Pages made from these designs', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Editing a page never changes the design it came from, so you can make as many pages as you like.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-home-pages" data-lzp-home-pages></div>
				</div>
			</section>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-parts" aria-labelledby="lzp-tab-parts">
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Replace Lenz\'s header and footer', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Pick a design for each device. Nothing changes for visitors until the module is switched on (top of this page) and the settings are saved; the eye button previews any choice right away. Lenz\'s mobile menu, its scripts and the rest of the page stay as they are.', 'lenz-plus' ); ?></p>
					</header>
				</div>

				<?php foreach ( $area_titles as $area => $titles ) : ?>
					<div class="lzp-card">
						<header class="lzp-card__head">
							<h2 class="lzp-device-title"><?php echo $ui_icon( 'grid' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( $titles[0] ); ?></h2>
							<p><?php esc_html_e( 'Shown on screens wider than the breakpoint below.', 'lenz-plus' ); ?></p>
						</header>
						<div class="lzp-tpl-grid" data-lzp-picker="<?php echo esc_attr( $area ); ?>" data-path="<?php echo esc_attr( $area ); ?>.desktop" data-keywords="theme,none"></div>
					</div>

					<div class="lzp-card">
						<header class="lzp-card__head">
							<h2 class="lzp-device-title"><?php echo $ui_icon( 'mobile' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( $titles[1] ); ?></h2>
							<p><?php esc_html_e( 'Each device gets only its own version: the same design, a different one, Lenz\'s, or nothing.', 'lenz-plus' ); ?></p>
						</header>
						<div class="lzp-tpl-grid" data-lzp-picker="<?php echo esc_attr( $area ); ?>" data-path="<?php echo esc_attr( $area ); ?>.mobile" data-keywords="same,theme,none"></div>
					</div>
				<?php endforeach; ?>

				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Sticky header', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Keeps the Lenz+ header at the top of the screen, as in the designs. Smart hides it while visitors scroll down and brings it back as soon as they scroll up. Lenz\'s own header follows the theme\'s setting.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields lzp-fields--2">
						<?php
						Fields::segmented( 'header.sticky_desktop', __( 'Desktop', 'lenz-plus' ), $sticky_options );
						Fields::segmented( 'header.sticky_mobile', __( 'Phones & tablets', 'lenz-plus' ), $sticky_options );
						?>
					</div>
					<div class="lzp-fields lzp-fields--after-grid">
						<?php Fields::range( 'breakpoint', __( 'Phone & tablet breakpoint', 'lenz-plus' ), 600, 1440, 1, 'px', array( 'help' => __( 'At this width and below, the phone header and footer are shown. 1024 matches Elementor\'s tablet breakpoint.', 'lenz-plus' ) ) ); ?>
					</div>
				</div>
			</section>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-routes" aria-labelledby="lzp-tab-routes">
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Replace Lenz\'s page layouts', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Pick a design for each group of pages. The design fills itself from each page\'s own content. Nothing changes for visitors until the module is switched on and the settings are saved; the eye button previews any choice on a real page.', 'lenz-plus' ); ?></p>
					</header>
				</div>

				<?php foreach ( $route_titles as $route => $route_title ) : ?>
					<div class="lzp-card">
						<header class="lzp-card__head">
							<h2><?php echo esc_html( $route_title[0] ); ?></h2>
							<p><?php echo esc_html( $route_title[1] ); ?></p>
						</header>
						<div class="lzp-tpl-grid" data-lzp-picker="<?php echo esc_attr( $route ); ?>" data-path="routes.<?php echo esc_attr( $route ); ?>" data-keywords="theme"></div>
					</div>
				<?php endforeach; ?>
			</section>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-library" aria-labelledby="lzp-tab-library">
				<div class="lzp-card">
					<header class="lzp-card__head lzp-card__head--split">
						<div>
							<h2><?php esc_html_e( 'Template library', 'lenz-plus' ); ?></h2>
							<p><?php esc_html_e( 'Every design is a normal Elementor template: edit it, duplicate it to try variations, or restore a ready-made design to its original state.', 'lenz-plus' ); ?></p>
						</div>
						<div class="lzp-inline-actions">
							<button type="button" class="lzp-btn lzp-btn--ghost" data-lzp-install-presets>
								<?php echo $ui_icon( 'download' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span><?php esc_html_e( 'Reinstall missing designs', 'lenz-plus' ); ?></span>
							</button>
							<button type="button" class="lzp-btn lzp-btn--primary" data-lzp-new-template>
								<?php echo $ui_icon( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span><?php esc_html_e( 'New template', 'lenz-plus' ); ?></span>
							</button>
						</div>
					</header>
					<div class="lzp-library" data-lzp-library></div>
				</div>
			</section>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-forms" aria-labelledby="lzp-tab-forms">
				<div class="lzp-card">
					<header class="lzp-card__head lzp-card__head--split">
						<div>
							<h2><?php esc_html_e( 'Requests', 'lenz-plus' ); ?> <span class="lzp-counter"><?php echo esc_html( number_format_i18n( Contact_Messages::count() ) ); ?></span></h2>
							<p><?php esc_html_e( 'Bookings, registrations and messages sent through the Request form widget. They are kept here even when email does not work on your host, and each one can also be emailed to you (set the address on the widget).', 'lenz-plus' ); ?></p>
						</div>
						<div class="lzp-inline-actions">
							<a class="lzp-btn lzp-btn--primary" href="<?php echo esc_url( Contact_Messages::inbox_url() ); ?>">
								<?php echo $ui_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span><?php esc_html_e( 'Read the requests', 'lenz-plus' ); ?></span>
							</a>
							<a class="lzp-btn lzp-btn--soft" href="<?php echo esc_url( Contact_Messages::export_url() ); ?>">
								<?php echo $ui_icon( 'download' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span><?php esc_html_e( 'Download CSV', 'lenz-plus' ); ?></span>
							</a>
						</div>
					</header>
				</div>

				<div class="lzp-card">
					<header class="lzp-card__head lzp-card__head--split">
						<div>
							<h2><?php esc_html_e( 'Newsletter and waitlists', 'lenz-plus' ); ?> <span class="lzp-counter"><?php echo esc_html( number_format_i18n( Newsletter::count() ) ); ?></span></h2>
							<p><?php esc_html_e( 'Emails and mobile numbers left in the Sign-up widget, with the list each one joined. Download them to import into your email or SMS service.', 'lenz-plus' ); ?></p>
						</div>
						<div class="lzp-inline-actions">
							<a class="lzp-btn lzp-btn--soft" href="<?php echo esc_url( Newsletter::export_url() ); ?>">
								<?php echo $ui_icon( 'download' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span><?php esc_html_e( 'Download CSV', 'lenz-plus' ); ?></span>
							</a>
						</div>
					</header>
				</div>
			</section>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-brand" aria-labelledby="lzp-tab-brand">
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Look of the designs', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Applied to every design at once, like the options of the original mockups.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields lzp-fields--2">
						<?php
						Fields::segmented(
							'options.photo_tone',
							__( 'Photos', 'lenz-plus' ),
							array(
								'grayscale' => __( 'Black and white', 'lenz-plus' ),
								'color'     => __( 'Colour', 'lenz-plus' ),
							)
						);
						Fields::toggle(
							'options.guides',
							__( 'Guide lines behind the heroes', 'lenz-plus' ),
							array( 'help' => __( 'The thin dashed lines and rule-of-thirds grids of the designs.', 'lenz-plus' ) )
						);
						Fields::select( 'options.icon_pack', __( 'Icon style in the designs', 'lenz-plus' ), $packs, array( 'help' => __( 'Lenz icons use the theme\'s own glyphs where it has them, like the designs.', 'lenz-plus' ) ) );
						Fields::toggle(
							'options.persian_digits',
							__( 'Persian digits', 'lenz-plus' ),
							array( 'help' => __( 'Show prices, counts and years as ۱۲۳ with Persian separators on Persian sites.', 'lenz-plus' ) )
						);
						?>
					</div>
				</div>

				<div class="lzp-card">
					<header class="lzp-card__head lzp-card__head--split">
						<div>
							<h2><?php esc_html_e( 'Brand colours', 'lenz-plus' ); ?></h2>
							<p><?php esc_html_e( 'Every design uses these colours; the defaults are the designs\' own. Anything you set on a widget in Elementor overrides them.', 'lenz-plus' ); ?></p>
						</div>
						<div class="lzp-inline-actions">
							<?php if ( $elementor['active'] ) : ?>
								<button type="button" class="lzp-btn lzp-btn--soft" data-lzp-sync-colors><?php esc_html_e( 'Add to Elementor global colours', 'lenz-plus' ); ?></button>
							<?php endif; ?>
						</div>
					</header>
					<div class="lzp-color-grid">
						<?php foreach ( Schema::BRAND_DEFAULTS as $key => $fallback ) : ?>
							<?php Fields::color( 'brand.' . $key, $brand_labels[ $key ], $fallback, array( 'default_label' => __( 'Design default', 'lenz-plus' ) ) ); ?>
						<?php endforeach; ?>
					</div>
					<div class="lzp-fields lzp-fields--after-grid">
						<?php Fields::range( 'brand.radius', __( 'Corner roundness (cards)', 'lenz-plus' ), 0, 40, 1, 'px' ); ?>
					</div>
				</div>
			</section>

		</div>
	</div>
</div>
