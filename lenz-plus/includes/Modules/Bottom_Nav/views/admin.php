<?php
/**
 * Settings panel for the bottom navigation module.
 *
 * Static structure and every translatable label live here; bottom-nav-admin.js
 * adds behaviour: live previews, the sortable item list (cloned from
 * #lzp-item-template) and the icon picker.
 *
 * @var \LenzPlus\Modules\Bottom_Nav\Module $module
 *
 * @package LenzPlus
 */

use LenzPlus\Admin\Fields;
use LenzPlus\Core\Color;
use LenzPlus\Core\Icon_Library;
use LenzPlus\Core\Site;
use LenzPlus\Core\Theme_Bridge;
use LenzPlus\Modules\Bottom_Nav\Item_Types;
use LenzPlus\Modules\Bottom_Nav\Schema;
use LenzPlus\Modules\Bottom_Nav\Styles;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included from a class method, so variables are local.

$ui_icon = static function ( string $key ): string {
	return Icon_Library::svg( 'phosphor-duotone', $key, false, 'lzp-ico' );
};

$palette   = Theme_Bridge::palette();
$catalog   = Icon_Library::catalog();
$theme_on  = Theme_Bridge::is_active();
$pack_demo = array( 'home', 'images', 'calendar', 'search', 'menu' );

$color_labels = array(
	'bg'         => __( 'Background', 'lenz-plus' ),
	'icon'       => __( 'Icons', 'lenz-plus' ),
	'label'      => __( 'Labels', 'lenz-plus' ),
	'active'     => __( 'Active item', 'lenz-plus' ),
	'active-bg'  => __( 'Active highlight', 'lenz-plus' ),
	'badge-bg'   => __( 'Badge', 'lenz-plus' ),
	'badge-text' => __( 'Badge text', 'lenz-plus' ),
	'border'     => __( 'Border', 'lenz-plus' ),
	'fab-bg'     => __( 'Center button', 'lenz-plus' ),
	'fab-icon'   => __( 'Center button icon', 'lenz-plus' ),
);

// What each slot shows when left empty (mirrors the defaults in bottom-nav.css).
$ink           = $palette['--primary-1'];
$surface       = $palette['--secondary-1'];
$slot_defaults = array(
	'bg'         => $surface,
	'icon'       => $palette['--primary-2'],
	'label'      => $palette['--primary-2'],
	'active'     => $ink,
	'active-bg'  => Color::alpha( $ink, 0.10 ),
	'badge-bg'   => $ink,
	'badge-text' => $surface,
	'border'     => Color::alpha( $ink, 0.09 ),
	'fab-bg'     => $ink,
	'fab-icon'   => $surface,
);

// Lenz's CSS variables and font, recreated so previews resolve the same defaults as the site.
$preview_vars = Theme_Bridge::preview_vars();

$panel_tabs = array(
	'style'    => array( 'palette', __( 'Style', 'lenz-plus' ) ),
	'items'    => array( 'grid', __( 'Buttons', 'lenz-plus' ) ),
	'colors'   => array( 'sun', __( 'Colours', 'lenz-plus' ) ),
	'layout'   => array( 'sliders', __( 'Size & font', 'lenz-plus' ) ),
	'behavior' => array( 'settings', __( 'Behaviour', 'lenz-plus' ) ),
);
?>
<div class="lzp-module" data-lzp-module="bottom_nav" style="<?php echo esc_attr( $preview_vars ); ?>">

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
				<input type="checkbox" data-lzp-bind="enabled" aria-label="<?php esc_attr_e( 'Enable the bottom navigation', 'lenz-plus' ); ?>">
				<span class="lzp-switch__track" aria-hidden="true"></span>
			</span>
		</label>
	</section>

	<div class="lzp-module-body">
		<div class="lzp-settings">

			<div class="lzp-tabs" role="tablist" data-lzp-tabs="bottom_nav">
				<?php foreach ( $panel_tabs as $tab_id => $panel_tab ) : ?>
					<button type="button" class="lzp-tab" role="tab" id="lzp-tab-<?php echo esc_attr( $tab_id ); ?>" aria-controls="lzp-panel-<?php echo esc_attr( $tab_id ); ?>" aria-selected="false" data-lzp-tab="<?php echo esc_attr( $tab_id ); ?>">
						<?php echo $ui_icon( $panel_tab[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_html( $panel_tab[1] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>

			<?php /* ---------------------------------------------------------- Style */ ?>
			<section class="lzp-panel" role="tabpanel" id="lzp-panel-style" aria-labelledby="lzp-tab-style" data-lzp-panel="style">
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Choose a style', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Every style works with any number of buttons. The previews use your real buttons.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-style-grid" role="radiogroup" aria-label="<?php esc_attr_e( 'Style', 'lenz-plus' ); ?>">
						<?php foreach ( Styles::all() as $style_id => $style ) : ?>
							<label class="lzp-style-card">
								<input type="radio" name="lzp-style" value="<?php echo esc_attr( $style_id ); ?>" data-lzp-bind="style">
								<span class="lzp-style-card__stage lzp-stage" data-lzp-style-stage="<?php echo esc_attr( $style_id ); ?>" dir="<?php echo esc_attr( Site::direction() ); ?>" aria-hidden="true"></span>
								<span class="lzp-style-card__body">
									<span class="lzp-style-card__title"><?php echo esc_html( $style['label'] ); ?> <i class="lzp-style-card__check" aria-hidden="true"><?php echo Icon_Library::svg( 'tabler', 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></i></span>
									<span class="lzp-style-card__desc"><?php echo esc_html( $style['description'] ); ?></span>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Icon pack', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Switching packs re-draws every button. You can still give any button its own icon, image or SVG.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-pack-grid" role="radiogroup" aria-label="<?php esc_attr_e( 'Icon pack', 'lenz-plus' ); ?>">
						<?php foreach ( $catalog['packs'] as $pack_id => $pack ) : ?>
							<?php $pack_ready = Icon_Library::is_available( $pack_id ); ?>
							<label class="lzp-pack-card<?php echo $pack_ready ? '' : ' is-disabled'; ?>">
								<input type="radio" name="lzp-icon-pack" value="<?php echo esc_attr( $pack_id ); ?>" data-lzp-bind="icon_pack" <?php disabled( ! $pack_ready ); ?>>
								<span class="lzp-pack-card__icons" aria-hidden="true">
									<?php foreach ( $pack_demo as $key ) : ?>
										<?php
										$glyph = '';
										if ( Icon_Library::FONT_AWESOME === $pack_id ) {
											$glyph = Icon_Library::fa_class( $key, 'far' );
										} elseif ( Icon_Library::LENZ === $pack_id ) {
											$glyph = Icon_Library::lenz_class( $key );
										}
										?>
										<?php if ( '' !== $glyph && $pack_ready ) : ?>
											<i class="<?php echo esc_attr( $glyph ); ?>"></i>
										<?php else : ?>
											<?php echo Icon_Library::svg( $pack_id, $key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										<?php endif; ?>
									<?php endforeach; ?>
								</span>
								<span class="lzp-pack-card__name"><?php echo esc_html( $pack['label'] ); ?></span>
								<span class="lzp-pack-card__meta"><?php echo esc_html( $pack_ready ? $pack['license'] : __( 'Needs the Lenz theme', 'lenz-plus' ) ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>

					<div class="lzp-fields">
						<?php
						Fields::segmented(
							'fa_weight',
							__( 'Font Awesome weight', 'lenz-plus' ),
							array(
								'far' => __( 'Regular', 'lenz-plus' ),
								'fas' => __( 'Solid', 'lenz-plus' ),
							),
							array(
								'show_if' => 'icon_pack=fontawesome',
								'help'    => __( 'Lenz ships Font Awesome Free: fewer icons have a Regular (outline) version than a Solid one.', 'lenz-plus' ),
							)
						);
						Fields::range(
							'icon_stroke',
							__( 'Line thickness', 'lenz-plus' ),
							1,
							2.5,
							0.25,
							'',
							array( 'show_if' => 'icon_pack=lucide|tabler|heroicons' )
						);
						Fields::toggle(
							'active_filled',
							__( 'Filled icon for the active button', 'lenz-plus' ),
							array( 'help' => __( 'Packs with filled variants (Phosphor, Heroicons, Bootstrap, Tabler, Font Awesome, and the Lenz cart and heart icons) swap to the solid icon on the current page.', 'lenz-plus' ) )
						);
						?>
					</div>
				</div>

				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Labels', 'lenz-plus' ); ?></h2>
					</header>
					<div class="lzp-fields">
						<?php
						Fields::segmented(
							'label_mode',
							__( 'Show labels', 'lenz-plus' ),
							array(
								'always' => __( 'Always', 'lenz-plus' ),
								'active' => __( 'Active only', 'lenz-plus' ),
								'never'  => __( 'Icons only', 'lenz-plus' ),
							),
							array(
								'show_if' => 'style=classic|floating|notch',
								'help'    => __( 'Hidden labels stay available to screen readers.', 'lenz-plus' ),
							)
						);
						?>
						<p class="lzp-inline-note" data-lzp-show-if="style=bubble|pill">
							<?php echo $ui_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php esc_html_e( 'This style shows the label of the active button only, by design.', 'lenz-plus' ); ?>
						</p>
					</div>
				</div>
			</section>

			<?php /* -------------------------------------------------------- Buttons */ ?>
			<section class="lzp-panel" role="tabpanel" id="lzp-panel-items" aria-labelledby="lzp-tab-items" data-lzp-panel="items" hidden>
				<div class="lzp-card">
					<header class="lzp-card__head lzp-card__head--split">
						<div>
							<h2>
								<?php esc_html_e( 'Buttons', 'lenz-plus' ); ?>
								<span class="lzp-counter" data-lzp-item-count></span>
							</h2>
							<p><?php esc_html_e( 'Drag to reorder, tap a button to edit it. 3 to 5 buttons feel best on phones.', 'lenz-plus' ); ?></p>
						</div>
						<div class="lzp-add">
							<button type="button" class="lzp-btn lzp-btn--primary" data-lzp-add-toggle aria-expanded="false" aria-controls="lzp-add-menu">
								<?php echo Icon_Library::svg( 'tabler', 'plus', false, 'lzp-ico' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php esc_html_e( 'Add button', 'lenz-plus' ); ?>
							</button>
							<div class="lzp-popover" id="lzp-add-menu" role="menu" hidden>
								<?php foreach ( Item_Types::all() as $type_id => $item_type ) : ?>
									<?php $available = Item_Types::is_available( $type_id ); ?>
									<button type="button" class="lzp-popover__item" role="menuitem" data-lzp-add-type="<?php echo esc_attr( $type_id ); ?>" <?php disabled( ! $available ); ?>>
										<span class="lzp-popover__icon" aria-hidden="true"><?php echo Icon_Library::svg( 'phosphor', $item_type['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
										<span>
											<strong><?php echo esc_html( $item_type['label'] ); ?></strong>
											<small><?php echo esc_html( $available ? $item_type['description'] : __( 'Requires WooCommerce', 'lenz-plus' ) ); ?></small>
										</span>
									</button>
								<?php endforeach; ?>
							</div>
						</div>
					</header>

					<ul class="lzp-items" data-lzp-items></ul>

					<div class="lzp-empty" data-lzp-items-empty hidden>
						<?php echo $ui_icon( 'grid' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<p><?php esc_html_e( 'No buttons yet. Add your first one.', 'lenz-plus' ); ?></p>
					</div>

					<p class="lzp-inline-note lzp-inline-note--warn" data-lzp-items-many hidden>
						<?php echo $ui_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'More than 5 buttons still fit, but labels get smaller. Consider moving extras into a menu or content sheet.', 'lenz-plus' ); ?>
					</p>
				</div>
			</section>

			<?php /* -------------------------------------------------------- Colours */ ?>
			<section class="lzp-panel" role="tabpanel" id="lzp-panel-colors" aria-labelledby="lzp-tab-colors" data-lzp-panel="colors" hidden>
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Colours', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Leave a colour empty to follow your Lenz theme colours automatically, including its dark demo palette.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-color-grid">
						<?php
						foreach ( Schema::COLOR_SLOTS as $slot ) {
							Fields::color( 'colors.' . $slot, $color_labels[ $slot ], $slot_defaults[ $slot ] );
						}
						?>
					</div>
				</div>
			</section>

			<?php /* ---------------------------------------------------- Size & font */ ?>
			<section class="lzp-panel" role="tabpanel" id="lzp-panel-layout" aria-labelledby="lzp-tab-layout" data-lzp-panel="layout" hidden>
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Size and shape', 'lenz-plus' ); ?></h2>
					</header>
					<div class="lzp-fields lzp-fields--2">
						<?php
						Fields::range( 'layout.height', __( 'Bar height', 'lenz-plus' ), 52, 88 );
						Fields::range( 'layout.icon_size', __( 'Icon size', 'lenz-plus' ), 18, 32 );
						Fields::range( 'layout.radius', __( 'Corner radius', 'lenz-plus' ), 0, 40, 1, 'px', array( 'show_if' => 'style=classic|floating|notch|pill' ) );
						Fields::range( 'layout.offset', __( 'Distance from screen edges', 'lenz-plus' ), 0, 32, 1, 'px', array( 'show_if' => 'style=floating|pill' ) );
						Fields::range( 'layout.max_width', __( 'Maximum width (tablets)', 'lenz-plus' ), 320, 1000, 10, 'px', array( 'show_if' => 'style=floating|pill' ) );
						Fields::range(
							'layout.breakpoint',
							__( 'Show on screens narrower than', 'lenz-plus' ),
							360,
							1400,
							1,
							'px',
							array( 'help' => __( '768px covers phones; raise it to include tablets.', 'lenz-plus' ) )
						);
						?>
					</div>
					<div class="lzp-fields">
						<?php
						Fields::segmented(
							'layout.shadow',
							__( 'Shadow', 'lenz-plus' ),
							array(
								'none'   => __( 'None', 'lenz-plus' ),
								'soft'   => __( 'Soft', 'lenz-plus' ),
								'medium' => __( 'Medium', 'lenz-plus' ),
								'strong' => __( 'Strong', 'lenz-plus' ),
							)
						);
						Fields::segmented(
							'layout.motion',
							__( 'Animation', 'lenz-plus' ),
							array(
								'spring' => __( 'Springy', 'lenz-plus' ),
								'smooth' => __( 'Smooth', 'lenz-plus' ),
								'none'   => __( 'None', 'lenz-plus' ),
							),
							array( 'help' => __( 'Visitors who ask their device for reduced motion never see animations.', 'lenz-plus' ) )
						);
						Fields::toggle(
							'layout.glass',
							__( 'Frosted glass background', 'lenz-plus' ),
							array(
								'show_if' => 'style=classic|floating|pill',
								'help'    => __( 'Slightly translucent with a blur of the page behind it.', 'lenz-plus' ),
							)
						);
						?>
					</div>
				</div>

				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Font', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'By default labels use the main font you picked in Lenz → Typography.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields lzp-fields--2">
						<?php
						Fields::select(
							'typography.font_source',
							__( 'Font family', 'lenz-plus' ),
							array(
								/* translators: %s: font family name. */
								'theme'   => sprintf( __( 'Theme font (%s)', 'lenz-plus' ), Theme_Bridge::font() ),
								'inherit' => __( 'Inherit from the page', 'lenz-plus' ),
								'custom'  => __( 'Custom…', 'lenz-plus' ),
							)
						);
						Fields::text(
							'typography.font_family',
							__( 'Custom font family', 'lenz-plus' ),
							array(
								'show_if'     => 'typography.font_source=custom',
								'placeholder' => 'Vazirmatn, Tahoma',
								'dir'         => 'ltr',
								'help'        => __( 'The font must already be loaded on your site.', 'lenz-plus' ),
							)
						);
						Fields::range( 'typography.font_size', __( 'Label size', 'lenz-plus' ), 9, 15 );
						Fields::segmented(
							'typography.font_weight',
							__( 'Label weight', 'lenz-plus' ),
							array(
								'400' => __( 'Regular', 'lenz-plus' ),
								'500' => __( 'Medium', 'lenz-plus' ),
								'600' => __( 'Semi-bold', 'lenz-plus' ),
								'700' => __( 'Bold', 'lenz-plus' ),
							)
						);
						?>
					</div>
				</div>
			</section>

			<?php /* ------------------------------------------------------- Behaviour */ ?>
			<section class="lzp-panel" role="tabpanel" id="lzp-panel-behavior" aria-labelledby="lzp-tab-behavior" data-lzp-panel="behavior" hidden>
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Behaviour', 'lenz-plus' ); ?></h2>
					</header>
					<div class="lzp-fields">
						<?php
						Fields::toggle( 'behavior.hide_on_scroll', __( 'Hide while scrolling down', 'lenz-plus' ), array( 'help' => __( 'Gives more room to content; the bar returns as soon as the visitor scrolls up.', 'lenz-plus' ) ) );
						Fields::toggle( 'behavior.haptic', __( 'Haptic feedback', 'lenz-plus' ), array( 'help' => __( 'A tiny vibration on tap (Android devices that support it).', 'lenz-plus' ) ) );
						Fields::text(
							'behavior.z_index',
							__( 'Stacking order (z-index)', 'lenz-plus' ),
							array(
								'type' => 'number',
								'dir'  => 'ltr',
								'help' => __( 'Raise it if a chat widget covers the bar. The default (990) keeps it above the Lenz sticky header and below its full-screen video player.', 'lenz-plus' ),
							)
						);
						?>
					</div>
				</div>

				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Where to show', 'lenz-plus' ); ?></h2>
					</header>
					<div class="lzp-fields">
						<?php
						Fields::toggle( 'visibility.hide_on_checkout', __( 'Hide on the checkout page', 'lenz-plus' ), array( 'help' => __( 'Keeps buyers focused on completing the order.', 'lenz-plus' ) ) );
						Fields::toggle( 'visibility.hide_on_cart', __( 'Hide on the cart page', 'lenz-plus' ) );
						Fields::toggle( 'visibility.hide_on_product', __( 'Hide on single product pages', 'lenz-plus' ), array( 'help' => __( 'Leaves the bottom of the screen to a product page\'s own buy bar.', 'lenz-plus' ) ) );
						Fields::text(
							'visibility.hide_for_ids',
							__( 'Hide on these pages or posts (IDs)', 'lenz-plus' ),
							array(
								'placeholder' => '12, 345',
								'dir'         => 'ltr',
								'help'        => __( 'Comma separated IDs, e.g. landing pages that have their own call to action.', 'lenz-plus' ),
							)
						);
						?>
					</div>
				</div>
			</section>
		</div>

		<?php /* ----------------------------------------------------------- Preview */ ?>
		<aside class="lzp-preview-col" aria-label="<?php esc_attr_e( 'Live preview', 'lenz-plus' ); ?>">
			<div class="lzp-preview">
				<div class="lzp-preview__toolbar">
					<div class="lzp-segmented lzp-segmented--sm" role="radiogroup" aria-label="<?php esc_attr_e( 'Preview visitor', 'lenz-plus' ); ?>">
						<label class="lzp-segmented__option"><input type="radio" name="lzp-preview-user" value="member" data-lzp-preview-user checked><span><?php esc_html_e( 'Member', 'lenz-plus' ); ?></span></label>
						<label class="lzp-segmented__option"><input type="radio" name="lzp-preview-user" value="guest" data-lzp-preview-user><span><?php esc_html_e( 'Guest', 'lenz-plus' ); ?></span></label>
					</div>
				</div>

				<div class="lzp-phone">
					<div class="lzp-phone__notch" aria-hidden="true"></div>
					<div class="lzp-phone__screen lzp-stage" data-lzp-preview-screen dir="<?php echo esc_attr( Site::direction() ); ?>">
						<div class="lzp-mock" aria-hidden="true">
							<div class="lzp-mock__header"><span></span><i></i></div>
							<div class="lzp-mock__hero"></div>
							<div class="lzp-mock__row"><span></span><span></span></div>
							<div class="lzp-mock__card"></div>
							<div class="lzp-mock__card"></div>
							<div class="lzp-mock__row"><span></span><span></span></div>
						</div>
						<div data-lzp-preview-nav></div>
					</div>
				</div>

				<p class="lzp-preview__caption">
					<?php esc_html_e( 'Tap the buttons to try the animation. Colours and font follow your Lenz settings.', 'lenz-plus' ); ?>
				</p>
			</div>
		</aside>
	</div>

	<?php /* ------------------------------------------------ Item row template */ ?>
	<template id="lzp-item-template">
		<li class="lzp-item">
			<div class="lzp-item__row">
				<span class="lzp-item__handle" aria-hidden="true" title="<?php esc_attr_e( 'Drag to reorder', 'lenz-plus' ); ?>"><?php echo Icon_Library::svg( 'phosphor', 'grip' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="lzp-item__icon" data-item-icon aria-hidden="true"></span>
				<button type="button" class="lzp-item__summary" data-item-toggle aria-expanded="false">
					<span class="lzp-item__label" data-item-label></span>
					<span class="lzp-item__chips">
						<span class="lzp-chip" data-item-type></span>
						<span class="lzp-chip lzp-chip--accent" data-item-featured hidden><?php esc_html_e( 'Center', 'lenz-plus' ); ?></span>
						<span class="lzp-chip" data-item-audience hidden></span>
						<span class="lzp-chip lzp-chip--warn" data-item-warning hidden></span>
					</span>
				</button>
				<span class="lzp-item__tools">
					<button type="button" class="lzp-move" data-item-action="up" aria-label="<?php esc_attr_e( 'Move up', 'lenz-plus' ); ?>">▲</button>
					<button type="button" class="lzp-move" data-item-action="down" aria-label="<?php esc_attr_e( 'Move down', 'lenz-plus' ); ?>">▼</button>
					<label class="lzp-switch lzp-switch--sm" title="<?php esc_attr_e( 'Show this button', 'lenz-plus' ); ?>">
						<input type="checkbox" data-item-bind="enabled" aria-label="<?php esc_attr_e( 'Show this button', 'lenz-plus' ); ?>">
						<span class="lzp-switch__track" aria-hidden="true"></span>
					</label>
					<button type="button" class="lzp-icon-btn" data-item-action="duplicate" title="<?php esc_attr_e( 'Duplicate', 'lenz-plus' ); ?>" aria-label="<?php esc_attr_e( 'Duplicate', 'lenz-plus' ); ?>"><?php echo Icon_Library::svg( 'phosphor', 'copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
					<button type="button" class="lzp-icon-btn lzp-icon-btn--danger" data-item-action="delete" title="<?php esc_attr_e( 'Delete', 'lenz-plus' ); ?>" aria-label="<?php esc_attr_e( 'Delete', 'lenz-plus' ); ?>"><?php echo Icon_Library::svg( 'phosphor', 'trash' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
					<button type="button" class="lzp-icon-btn lzp-item__chevron" data-item-toggle tabindex="-1" aria-hidden="true"><?php echo Icon_Library::svg( 'phosphor', 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
				</span>
			</div>

			<div class="lzp-item__editor" hidden>
				<div class="lzp-fields lzp-fields--2">
					<div class="lzp-field">
						<label class="lzp-field__label"><?php esc_html_e( 'Button type', 'lenz-plus' ); ?></label>
						<div class="lzp-select">
							<select data-item-bind="type">
								<?php foreach ( Item_Types::all() as $type_id => $item_type ) : ?>
									<option value="<?php echo esc_attr( $type_id ); ?>" <?php disabled( ! Item_Types::is_available( $type_id ) ); ?>><?php echo esc_html( $item_type['label'] ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<p class="lzp-field__help" data-item-type-help></p>
					</div>

					<div class="lzp-field">
						<label class="lzp-field__label"><?php esc_html_e( 'Label', 'lenz-plus' ); ?></label>
						<input type="text" class="lzp-input" data-item-bind="label" maxlength="40">
					</div>

					<div class="lzp-field lzp-field--full">
						<span class="lzp-field__label"><?php esc_html_e( 'Icon', 'lenz-plus' ); ?></span>
						<div class="lzp-segmented">
							<label class="lzp-segmented__option"><input type="radio" value="pack" data-item-bind="icon_source"><span><?php esc_html_e( 'From pack', 'lenz-plus' ); ?></span></label>
							<label class="lzp-segmented__option" <?php echo $theme_on ? '' : 'hidden'; ?>><input type="radio" value="fontawesome" data-item-bind="icon_source"><span>Font Awesome</span></label>
							<label class="lzp-segmented__option"><input type="radio" value="image" data-item-bind="icon_source"><span><?php esc_html_e( 'Image', 'lenz-plus' ); ?></span></label>
							<label class="lzp-segmented__option"><input type="radio" value="svg" data-item-bind="icon_source"><span>SVG</span></label>
						</div>

						<div class="lzp-icon-choice" data-item-show-if="icon_source=pack">
							<button type="button" class="lzp-btn lzp-btn--soft" data-item-action="pick-icon">
								<span class="lzp-icon-choice__preview" data-item-icon-preview aria-hidden="true"></span>
								<?php esc_html_e( 'Choose icon', 'lenz-plus' ); ?>
							</button>
						</div>
						<div data-item-show-if="icon_source=fontawesome">
							<input type="text" class="lzp-input" data-item-bind="icon_fa" dir="ltr" placeholder="fas fa-camera">
							<p class="lzp-field__help"><?php esc_html_e( 'Any Font Awesome 6 Free class (far, fas or fab) loaded by Lenz.', 'lenz-plus' ); ?></p>
						</div>
						<div class="lzp-media-field" data-item-show-if="icon_source=image">
							<input type="url" class="lzp-input" data-item-bind="icon_image" dir="ltr" placeholder="https://">
							<button type="button" class="lzp-btn lzp-btn--soft" data-item-action="pick-image"><?php esc_html_e( 'Media library', 'lenz-plus' ); ?></button>
							<p class="lzp-field__help"><?php esc_html_e( 'Square PNG, WebP or SVG files look best.', 'lenz-plus' ); ?></p>
						</div>
						<div data-item-show-if="icon_source=svg">
							<textarea class="lzp-input lzp-input--code" rows="4" dir="ltr" data-item-bind="icon_svg" placeholder="<svg viewBox=&quot;0 0 24 24&quot;>…</svg>"></textarea>
							<p class="lzp-field__help"><?php esc_html_e( 'Use fill="currentColor" or stroke="currentColor" so the icon follows the bar colours. Scripts are removed on save.', 'lenz-plus' ); ?></p>
						</div>
					</div>

					<div class="lzp-field lzp-field--full" data-item-show-if="type=link|reserve|account|selector">
						<label class="lzp-field__label"><?php esc_html_e( 'Link (URL)', 'lenz-plus' ); ?></label>
						<input type="text" class="lzp-input" data-item-bind="url" dir="ltr" placeholder="https://" list="lzp-url-suggestions">
						<p class="lzp-field__help" data-item-show-if="type=link"><?php esc_html_e( 'Pages, categories, tel:+98…, https://t.me/… — anything goes.', 'lenz-plus' ); ?></p>
						<p class="lzp-field__help" data-item-show-if="type=reserve"><?php esc_html_e( 'Optional. Leave empty to use the link of the Lenz header "Reserve" button.', 'lenz-plus' ); ?></p>
						<p class="lzp-field__help" data-item-show-if="type=account"><?php esc_html_e( 'Optional. Leave empty to use the Lenz header account link, else the WooCommerce "My account" page.', 'lenz-plus' ); ?></p>
						<p class="lzp-field__help" data-item-show-if="type=selector"><?php esc_html_e( 'Optional fallback, used when the element is not on the page.', 'lenz-plus' ); ?></p>
					</div>

					<div class="lzp-field lzp-field--full" data-item-show-if="type=link|reserve">
						<label class="lzp-switch-row">
							<span class="lzp-field__label"><?php esc_html_e( 'Open in a new tab', 'lenz-plus' ); ?></span>
							<span class="lzp-switch"><input type="checkbox" data-item-bind="new_tab"><span class="lzp-switch__track" aria-hidden="true"></span></span>
						</label>
					</div>

					<div class="lzp-field lzp-field--full" data-item-show-if="type=archive">
						<label class="lzp-field__label"><?php esc_html_e( 'Content type', 'lenz-plus' ); ?></label>
						<div class="lzp-select"><select data-item-bind="archive_type" data-lzp-options="archiveTypes"></select></div>
						<p class="lzp-field__help"><?php esc_html_e( 'Leave the label empty to show the content type\'s own name.', 'lenz-plus' ); ?></p>
					</div>

					<fieldset class="lzp-field lzp-field--full" data-item-show-if="type=search">
						<legend class="lzp-field__label"><?php esc_html_e( 'Search in', 'lenz-plus' ); ?></legend>
						<div class="lzp-checks" data-item-bind-list="search_post_types" data-lzp-options="postTypes"></div>
						<p class="lzp-field__help"><?php esc_html_e( 'Leave all unchecked to search everything.', 'lenz-plus' ); ?></p>
					</fieldset>

					<div class="lzp-field lzp-field--full" data-item-show-if="type=search">
						<label class="lzp-switch-row">
							<span class="lzp-switch-row__text">
								<span class="lzp-field__label"><?php esc_html_e( 'Live results while typing (AJAX)', 'lenz-plus' ); ?></span>
								<span class="lzp-field__help"><?php esc_html_e( 'Matching results appear in the sheet without leaving the page. Pressing Search still opens the full results page.', 'lenz-plus' ); ?></span>
							</span>
							<span class="lzp-switch"><input type="checkbox" data-item-bind="search_live"><span class="lzp-switch__track" aria-hidden="true"></span></span>
						</label>
					</div>

					<div class="lzp-field" data-item-show-if="type=cart">
						<label class="lzp-field__label"><?php esc_html_e( 'On tap', 'lenz-plus' ); ?></label>
						<div class="lzp-select">
							<select data-item-bind="cart_action">
								<option value="sheet"><?php esc_html_e( 'Open the cart sheet', 'lenz-plus' ); ?></option>
								<option value="page"><?php esc_html_e( 'Go to the cart page', 'lenz-plus' ); ?></option>
								<option value="checkout"><?php esc_html_e( 'Go to checkout', 'lenz-plus' ); ?></option>
							</select>
						</div>
					</div>

					<div class="lzp-field" data-item-show-if="type=cart">
						<label class="lzp-switch-row">
							<span class="lzp-field__label"><?php esc_html_e( 'Hide the count when the cart is empty', 'lenz-plus' ); ?></span>
							<span class="lzp-switch"><input type="checkbox" data-item-bind="hide_empty_badge"><span class="lzp-switch__track" aria-hidden="true"></span></span>
						</label>
					</div>

					<div class="lzp-field" data-item-show-if="type=account">
						<label class="lzp-field__label"><?php esc_html_e( 'Label for guests', 'lenz-plus' ); ?></label>
						<input type="text" class="lzp-input" data-item-bind="guest_label" maxlength="40" placeholder="<?php esc_attr_e( 'Login', 'lenz-plus' ); ?>">
					</div>

					<div class="lzp-field lzp-field--full" data-item-show-if="type=account">
						<label class="lzp-switch-row">
							<span class="lzp-field__label"><?php esc_html_e( 'Show the user\'s avatar instead of the icon', 'lenz-plus' ); ?></span>
							<span class="lzp-switch"><input type="checkbox" data-item-bind="show_avatar"><span class="lzp-switch__track" aria-hidden="true"></span></span>
						</label>
					</div>

					<div class="lzp-field" data-item-show-if="type=menu">
						<label class="lzp-field__label"><?php esc_html_e( 'Menu', 'lenz-plus' ); ?></label>
						<div class="lzp-select">
							<select data-item-bind="menu_source">
								<option value="theme"><?php esc_html_e( 'Lenz mobile menu', 'lenz-plus' ); ?></option>
								<option value="wp_menu"><?php esc_html_e( 'A WordPress menu in a sheet', 'lenz-plus' ); ?></option>
							</select>
						</div>
					</div>

					<div class="lzp-field" data-item-show-if="type=menu;menu_source=wp_menu">
						<label class="lzp-field__label"><?php esc_html_e( 'Which menu', 'lenz-plus' ); ?></label>
						<div class="lzp-select"><select data-item-bind="menu_id" data-lzp-type="number" data-lzp-options="menus"></select></div>
					</div>

					<div class="lzp-field lzp-field--full" data-item-show-if="type=content">
						<span class="lzp-field__label"><?php esc_html_e( 'Content', 'lenz-plus' ); ?></span>
						<div class="lzp-segmented">
							<label class="lzp-segmented__option"><input type="radio" value="shortcode" data-item-bind="content_source"><span><?php esc_html_e( 'Text / shortcode', 'lenz-plus' ); ?></span></label>
							<label class="lzp-segmented__option"><input type="radio" value="elementor" data-item-bind="content_source"><span><?php esc_html_e( 'Elementor template', 'lenz-plus' ); ?></span></label>
						</div>
					</div>

					<div class="lzp-field lzp-field--full" data-item-show-if="type=content;content_source=elementor">
						<label class="lzp-field__label"><?php esc_html_e( 'Template or page', 'lenz-plus' ); ?></label>
						<div class="lzp-select"><select data-item-bind="content_id" data-lzp-type="number" data-lzp-options="templates"></select></div>
					</div>

					<div class="lzp-field lzp-field--full" data-item-show-if="type=content;content_source=shortcode">
						<label class="lzp-field__label"><?php esc_html_e( 'Text, HTML or shortcode', 'lenz-plus' ); ?></label>
						<textarea class="lzp-input" rows="4" data-item-bind="content_html"></textarea>
					</div>

					<div class="lzp-field lzp-field--full" data-item-show-if="type=selector">
						<label class="lzp-field__label"><?php esc_html_e( 'CSS selector of the element to click', 'lenz-plus' ); ?></label>
						<input type="text" class="lzp-input" data-item-bind="selector" dir="ltr" placeholder=".lenz-account-btn">
						<p class="lzp-field__help"><?php esc_html_e( 'For example .lenz-account-btn taps the Lenz header account button.', 'lenz-plus' ); ?></p>
					</div>

					<div class="lzp-field" data-item-show-if="type=search|cart|menu|content">
						<label class="lzp-field__label"><?php esc_html_e( 'Sheet title', 'lenz-plus' ); ?></label>
						<input type="text" class="lzp-input" data-item-bind="sheet_title" maxlength="60" data-item-placeholder="label">
					</div>

					<div class="lzp-field">
						<label class="lzp-field__label"><?php esc_html_e( 'Show to', 'lenz-plus' ); ?></label>
						<div class="lzp-select">
							<select data-item-bind="visibility">
								<option value="all"><?php esc_html_e( 'Everyone', 'lenz-plus' ); ?></option>
								<option value="guests"><?php esc_html_e( 'Guests only', 'lenz-plus' ); ?></option>
								<option value="members"><?php esc_html_e( 'Logged-in users only', 'lenz-plus' ); ?></option>
							</select>
						</div>
					</div>

					<div class="lzp-field">
						<label class="lzp-field__label"><?php esc_html_e( 'Badge text', 'lenz-plus' ); ?></label>
						<input type="text" class="lzp-input" data-item-bind="badge" maxlength="12" placeholder="<?php esc_attr_e( 'e.g. New', 'lenz-plus' ); ?>">
					</div>

					<div class="lzp-field lzp-field--full">
						<label class="lzp-switch-row">
							<span class="lzp-switch-row__text">
								<span class="lzp-field__label"><?php esc_html_e( 'Make it the center button', 'lenz-plus' ); ?></span>
								<span class="lzp-field__help"><?php esc_html_e( 'Used by the "Center button" style. Without a choice, the middle button is used.', 'lenz-plus' ); ?></span>
							</span>
							<span class="lzp-switch"><input type="checkbox" data-item-bind="featured"><span class="lzp-switch__track" aria-hidden="true"></span></span>
						</label>
					</div>
				</div>
			</div>
		</li>
	</template>

	<datalist id="lzp-url-suggestions">
		<option value="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'lenz-plus' ); ?></option>
		<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
			<option value="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Shop / courses', 'lenz-plus' ); ?></option>
			<option value="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'My account', 'lenz-plus' ); ?></option>
			<option value="<?php echo esc_url( wc_get_page_permalink( 'cart' ) ); ?>"><?php esc_html_e( 'Cart', 'lenz-plus' ); ?></option>
		<?php endif; ?>
		<?php
		foreach ( get_pages(
			array(
				'number'      => 60,
				'sort_column' => 'menu_order,post_title',
			)
		) as $page_post ) :
			?>
			<option value="<?php echo esc_url( get_permalink( $page_post ) ); ?>"><?php echo esc_html( get_the_title( $page_post ) ); ?></option>
		<?php endforeach; ?>
	</datalist>

	<?php /* ------------------------------------------------------ Icon picker */ ?>
	<dialog class="lzp-dialog lzp-icon-picker" id="lzp-icon-picker" aria-labelledby="lzp-icon-picker-title">
		<div class="lzp-dialog__head">
			<h2 id="lzp-icon-picker-title"><?php esc_html_e( 'Choose an icon', 'lenz-plus' ); ?></h2>
			<button type="button" class="lzp-icon-btn" data-lzp-dialog-close aria-label="<?php esc_attr_e( 'Close', 'lenz-plus' ); ?>"><?php echo Icon_Library::svg( 'phosphor', 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
		</div>
		<div class="lzp-icon-picker__search">
			<?php echo Icon_Library::svg( 'phosphor', 'search', false, 'lzp-ico' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<input type="search" class="lzp-input" data-lzp-icon-search placeholder="<?php esc_attr_e( 'Search icons… (e.g. cart, سبد)', 'lenz-plus' ); ?>">
		</div>
		<div class="lzp-icon-picker__grid" data-lzp-icon-grid role="listbox" aria-label="<?php esc_attr_e( 'Icons', 'lenz-plus' ); ?>"></div>
	</dialog>
</div>
