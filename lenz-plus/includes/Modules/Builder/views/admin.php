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
use LenzPlus\Modules\Builder\Library;
use LenzPlus\Modules\Builder\Schema;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included from a class method, so variables are local.

$ui_icon = static function ( string $key ): string {
	return Icon_Library::svg( 'phosphor-duotone', $key, false, 'lzp-ico' );
};

$elementor = Library::elementor_status();

$panel_tabs = array(
	'pages'   => array( 'home', __( 'Pages', 'lenz-plus' ) ),
	'library' => array( 'grid', __( 'Templates', 'lenz-plus' ) ),
	'brand'   => array( 'palette', __( 'Colours & options', 'lenz-plus' ) ),
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
