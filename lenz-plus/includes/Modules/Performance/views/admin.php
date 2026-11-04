<?php
/**
 * Settings panel of the speed module.
 *
 * Every control binds to the settings by path (`data-lzp-bind`); the admin
 * shell saves them. The cache tab reports WP Rocket's own settings, so the
 * two can be set side by side, and has the «clear the cache now» button.
 *
 * @var \LenzPlus\Modules\Performance\Module $module
 *
 * @package LenzPlus
 */

use LenzPlus\Admin\Fields;
use LenzPlus\Core\Icon_Library;
use LenzPlus\Core\Page_Cache;
use LenzPlus\Core\Theme_Bridge;
use LenzPlus\Modules\Performance\Cache_Purge;
use LenzPlus\Modules\Performance\Rocket;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included from a class method, so variables are local.

$ui_icon = static function ( string $key ): string {
	return Icon_Library::svg( 'phosphor-duotone', $key, false, 'lzp-ico' );
};

$lenz_active   = Theme_Bridge::is_active();
$rocket_status = Rocket::status();
$cache_plugins = Page_Cache::purgeable();
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a read-only flag set by Cache_Purge's redirect.
$just_purged = ! empty( $_GET[ Cache_Purge::DONE_ARG ] );

$panel_tabs = array(
	'theme'     => array( 'palette', __( 'Lenz theme', 'lenz-plus' ) ),
	'wordpress' => array( 'wrench', __( 'WordPress', 'lenz-plus' ) ),
	'shop'      => array( 'cart', __( 'WooCommerce', 'lenz-plus' ) ),
	'cache'     => array( 'bolt', __( 'WP Rocket and cache', 'lenz-plus' ) ),
);
?>
<div class="lzp-module" data-lzp-module="performance">

	<section class="lzp-module-head">
		<span class="lzp-module-head__icon" aria-hidden="true"><?php echo $ui_icon( 'bolt' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG. ?></span>
		<div class="lzp-module-head__text">
			<h1><?php echo esc_html( $module->title() ); ?></h1>
			<p><?php echo esc_html( $module->description() ); ?></p>
		</div>
		<label class="lzp-module-head__switch">
			<span data-lzp-show-if="enabled"><?php esc_html_e( 'Active', 'lenz-plus' ); ?></span>
			<span data-lzp-show-if="!enabled"><?php esc_html_e( 'Inactive', 'lenz-plus' ); ?></span>
			<span class="lzp-switch lzp-switch--lg">
				<input type="checkbox" data-lzp-bind="enabled" aria-label="<?php esc_attr_e( 'Enable the speed optimisations', 'lenz-plus' ); ?>">
				<span class="lzp-switch__track" aria-hidden="true"></span>
			</span>
		</label>
	</section>

	<?php if ( $just_purged ) : ?>
		<div class="lzp-notice">
			<?php echo $ui_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p><?php esc_html_e( 'The page cache was cleared. Visitors get fresh pages from now on.', 'lenz-plus' ); ?></p>
		</div>
	<?php endif; ?>

	<div class="lzp-module-body lzp-module-body--wide">
		<div class="lzp-settings">

			<div class="lzp-tabs" role="tablist" data-lzp-tabs="performance">
				<?php foreach ( $panel_tabs as $tab_id => $panel_tab ) : ?>
					<button type="button" class="lzp-tab" role="tab" id="lzp-tab-<?php echo esc_attr( $tab_id ); ?>" aria-controls="lzp-panel-<?php echo esc_attr( $tab_id ); ?>" aria-selected="false" data-lzp-tab="<?php echo esc_attr( $tab_id ); ?>">
						<?php echo $ui_icon( $panel_tab[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_html( $panel_tab[1] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-theme" aria-labelledby="lzp-tab-theme">
				<?php if ( ! $lenz_active ) : ?>
					<div class="lzp-notice">
						<?php echo $ui_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<p><?php esc_html_e( 'The Lenz theme is not active, so these settings do nothing for now.', 'lenz-plus' ); ?></p>
					</div>
				<?php endif; ?>

				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Theme files', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Lenz loads all of its libraries on every page: the slider, the masonry grid, the photo gallery, select boxes, the date picker and the scripts of its widgets, about 700 KB. These settings keep them only on pages that show something that needs them, before WP Rocket minifies and caches the page.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields">
						<?php
						Fields::toggle(
							'lenz_assets',
							__( 'Load sliders, galleries and widget scripts only where they are used', 'lenz-plus' ),
							array( 'help' => __( 'Each page is checked for Lenz\'s sliders, portfolio grids, galleries, sort box, booking form and filters; files nothing on the page uses are left out.', 'lenz-plus' ) )
						);
						Fields::segmented(
							'font_awesome',
							__( 'Font Awesome icons (96 KB of CSS and its font)', 'lenz-plus' ),
							array(
								'auto'   => __( 'Only on pages with an fa icon', 'lenz-plus' ),
								'always' => __( 'On every page', 'lenz-plus' ),
							),
							array( 'help' => __( 'Lenz\'s own icons use its lenz-icon font. Font Awesome is only needed where a menu, widget or text uses an fa-… icon.', 'lenz-plus' ) )
						);
						Fields::toggle(
							'bootstrap_rtl',
							__( 'Load the Bootstrap grid once', 'lenz-plus' ),
							array( 'help' => __( 'On Persian sites Lenz loads the grid twice (left-to-right, then the complete right-to-left copy). The right-to-left copy alone is enough.', 'lenz-plus' ) )
						);
						?>
					</div>
				</div>

				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Fonts', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Lenz\'s font files tell the browser to hide text until the font arrives, which can leave a page blank for up to three seconds on a slow mobile connection.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields">
						<?php
						Fields::toggle(
							'font_swap',
							__( 'Show text while the font loads', 'lenz-plus' ),
							array( 'help' => __( 'Text appears at once in a system font and switches to the site font when it has loaded. The small font stylesheet is also placed inside the page, saving one request.', 'lenz-plus' ) )
						);
						Fields::toggle(
							'font_preload',
							__( 'Download the site font first', 'lenz-plus' ),
							array( 'help' => __( 'The regular weight of the font chosen in Lenz\'s typography settings starts downloading together with the page.', 'lenz-plus' ) )
						);
						?>
					</div>
				</div>
			</section>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-wordpress" aria-labelledby="lzp-tab-wordpress" hidden>
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Extras WordPress loads', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'WP Rocket has switches for some of these too; having both on is fine.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields">
						<?php
						Fields::toggle(
							'emojis',
							__( 'Remove the emoji script', 'lenz-plus' ),
							array( 'help' => __( 'Browsers show emojis themselves; WordPress\'s script only replaces them with images.', 'lenz-plus' ) )
						);
						Fields::toggle(
							'block_styles',
							__( 'Block editor styles only for the blocks a page uses', 'lenz-plus' ),
							array( 'help' => __( 'Pages built with Elementor skip WordPress\'s block stylesheet entirely; articles get only the styles of their own blocks.', 'lenz-plus' ) )
						);
						Fields::toggle(
							'dashicons',
							__( 'No admin icon font for visitors', 'lenz-plus' ),
							array( 'help' => __( 'Dashicons stay for logged-in users who see the admin bar.', 'lenz-plus' ) )
						);
						Fields::toggle(
							'head_cleanup',
							__( 'Remove unneeded links from the page head', 'lenz-plus' ),
							array( 'help' => __( 'The WordPress version, the short link and the links for old blog editors.', 'lenz-plus' ) )
						);
						Fields::toggle(
							'embeds',
							__( 'Turn off WordPress embeds of this site', 'lenz-plus' ),
							array( 'help' => __( 'Other WordPress sites can no longer show your posts as cards. Aparat and YouTube videos in your own pages keep working.', 'lenz-plus' ) )
						);
						Fields::toggle(
							'jquery_migrate',
							__( 'Remove jQuery Migrate', 'lenz-plus' ),
							array(
								'help' => __( 'Lenz works without it, but some old plugins still need it. Check the site after switching it on.', 'lenz-plus' ),
								'chip' => array( __( 'Check after enabling', 'lenz-plus' ), 'warn' ),
							)
						);
						?>
					</div>
				</div>

				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Server load', 'lenz-plus' ); ?></h2>
					</header>
					<div class="lzp-fields">
						<?php
						Fields::select(
							'heartbeat',
							__( 'Heartbeat (background requests of the admin)', 'lenz-plus' ),
							array(
								'default'   => __( 'As WordPress runs it', 'lenz-plus' ),
								'slow'      => __( 'Once a minute', 'lenz-plus' ),
								'off_front' => __( 'Once a minute, and off on the site', 'lenz-plus' ),
							),
							array( 'help' => __( 'Every open editor tab asks the server for news every 15 seconds. Once a minute is enough for autosave and edit locks.', 'lenz-plus' ) )
						);
						Fields::range(
							'revisions',
							__( 'Revisions kept per post', 'lenz-plus' ),
							0,
							50,
							1,
							'',
							array( 'help' => __( '0 keeps them all, as WordPress does. Fewer revisions keep the database small.', 'lenz-plus' ) )
						);
						?>
					</div>
				</div>
			</section>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-shop" aria-labelledby="lzp-tab-shop" hidden>
				<?php if ( ! class_exists( 'WooCommerce' ) ) : ?>
					<div class="lzp-notice">
						<?php echo $ui_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<p><?php esc_html_e( 'WooCommerce is not active. These settings apply once it is.', 'lenz-plus' ); ?></p>
					</div>
				<?php endif; ?>
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'WooCommerce', 'lenz-plus' ); ?></h2>
					</header>
					<div class="lzp-fields">
						<?php
						Fields::toggle(
							'cart_fragments',
							__( 'Refresh the cart only for visitors who have something in it', 'lenz-plus' ),
							array( 'help' => __( 'WooCommerce asks the server for the cart on every page view, a request no cache can save. With an empty cart it is skipped; adding a product still updates the cart badge at once.', 'lenz-plus' ) )
						);
						Fields::toggle(
							'wc_blocks',
							__( 'WooCommerce block styles only where its blocks are used', 'lenz-plus' ),
							array( 'help' => __( 'Lenz\'s shop uses the classic pages, so most pages have no WooCommerce block.', 'lenz-plus' ) )
						);
						?>
					</div>
				</div>
			</section>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-cache" aria-labelledby="lzp-tab-cache" hidden>
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Working with WP Rocket', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'WP Rocket caches, minifies and delays files; this module decides which theme files a page needs at all. Keep both on.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields">
						<?php
						Fields::toggle(
							'rocket_safe',
							__( 'Make WP Rocket\'s optimisations safe for Lenz', 'lenz-plus' ),
							array(
								'help' => __( '«Remove unused CSS» keeps the styles of menus, sliders, galleries and the bottom bar that Lenz and Lenz+ switch on with JavaScript, and «LazyLoad» does not hold back the first large photo of a page.', 'lenz-plus' ),
								'chip' => Rocket::is_active() ? array( __( 'WP Rocket active', 'lenz-plus' ), 'accent' ) : array( __( 'WP Rocket not installed', 'lenz-plus' ), '' ),
							)
						);
						Fields::toggle(
							'purge_on_save',
							__( 'Clear the page cache after Lenz+ changes', 'lenz-plus' ),
							array( 'help' => __( 'Saving the bottom bar, the support button, page template settings or a header or footer template clears the cache, so visitors do not keep seeing the old version.', 'lenz-plus' ) )
						);
						?>
					</div>
				</div>

				<?php if ( $rocket_status ) : ?>
					<div class="lzp-card">
						<header class="lzp-card__head">
							<h2><?php esc_html_e( 'WP Rocket\'s settings', 'lenz-plus' ); ?></h2>
							<p><?php esc_html_e( 'Set these in Settings → WP Rocket. All of them can be on with Lenz.', 'lenz-plus' ); ?></p>
						</header>
						<ul class="lzp-checklist">
							<?php foreach ( $rocket_status as $rocket_option ) : ?>
								<li>
									<span class="lzp-status-dot<?php echo $rocket_option['on'] ? ' is-on' : ''; ?>" aria-hidden="true"></span>
									<strong><?php echo esc_html( $rocket_option['label'] ); ?></strong>
									<span class="lzp-chip<?php echo $rocket_option['on'] ? ' lzp-chip--accent' : ''; ?>"><?php echo $rocket_option['on'] ? esc_html__( 'On', 'lenz-plus' ) : esc_html__( 'Off', 'lenz-plus' ); ?></span>
									<?php if ( '' !== $rocket_option['note'] ) : ?>
										<p><?php echo esc_html( $rocket_option['note'] ); ?></p>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Clear the cache', 'lenz-plus' ); ?></h2>
						<p>
							<?php
							echo esc_html(
								$cache_plugins
									/* translators: %s: cache plugin names. */
									? sprintf( __( 'Clears the saved pages and files of %s.', 'lenz-plus' ), implode( '، ', $cache_plugins ) )
									: __( 'No page cache plugin was found. WP Rocket, LiteSpeed Cache, W3 Total Cache and WP Super Cache are supported.', 'lenz-plus' )
							);
							?>
						</p>
					</header>
					<?php if ( $cache_plugins ) : ?>
						<a class="lzp-btn lzp-btn--soft" href="<?php echo esc_url( Cache_Purge::purge_url() ); ?>"><?php esc_html_e( 'Clear the cache now', 'lenz-plus' ); ?></a>
					<?php endif; ?>
				</div>
			</section>

		</div>
	</div>
</div>
