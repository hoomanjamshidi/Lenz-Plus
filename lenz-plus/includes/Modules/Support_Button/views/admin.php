<?php
/**
 * Settings panel for the floating support button.
 *
 * Every control binds to the settings by path (`data-lzp-bind`), including
 * the channel rows, which are printed here in the saved order.
 * support-button-admin.js adds the live preview, row summaries and
 * drag-to-reorder.
 *
 * @var \LenzPlus\Modules\Support_Button\Module $module
 *
 * @package LenzPlus
 */

use LenzPlus\Admin\Fields;
use LenzPlus\Core\Icon_Library;
use LenzPlus\Core\Site;
use LenzPlus\Core\Theme_Bridge;
use LenzPlus\Modules\Support_Button\Channels;
use LenzPlus\Modules\Support_Button\Schema;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included from a class method, so variables are local.

$ui_icon = static function ( string $key ): string {
	return Icon_Library::svg( 'phosphor-duotone', $key, false, 'lzp-ico' );
};

$settings = $module->settings();
$palette  = Theme_Bridge::palette();
$channels = Channels::all();

$icon_labels = array(
	'chat'    => __( 'Chat', 'lenz-plus' ),
	'support' => __( 'Headset', 'lenz-plus' ),
	'help'    => __( 'Question', 'lenz-plus' ),
	'phone'   => __( 'Phone', 'lenz-plus' ),
	'mail'    => __( 'Email', 'lenz-plus' ),
	'ticket'  => __( 'Ticket', 'lenz-plus' ),
);

// "Start" and "end" follow the site direction, so name the physical side the admin sees.
$sides = Site::is_rtl()
	? array(
		'start' => __( 'Right', 'lenz-plus' ),
		'end'   => __( 'Left', 'lenz-plus' ),
	)
	: array(
		'start' => __( 'Left', 'lenz-plus' ),
		'end'   => __( 'Right', 'lenz-plus' ),
	);

// Lenz's CSS variables and font, recreated so the preview resolves the same defaults as the site.
$preview_vars = Theme_Bridge::preview_vars();

// A number saved in Lenz's mobile-menu support box, offered for the phone channel.
$theme_phone = Theme_Bridge::support_phone();

$panel_tabs = array(
	'channels'   => array( 'chat', __( 'Channels', 'lenz-plus' ) ),
	'appearance' => array( 'palette', __( 'Appearance', 'lenz-plus' ) ),
	'display'    => array( 'settings', __( 'Position & display', 'lenz-plus' ) ),
);

$designs = array(
	'card'    => array( __( 'Card', 'lenz-plus' ), __( 'A small window with a heading and one row per channel.', 'lenz-plus' ) ),
	'bubbles' => array( __( 'Bubbles', 'lenz-plus' ), __( 'Round brand buttons that pop up above the main button.', 'lenz-plus' ) ),
);
?>
<div class="lzp-module" data-lzp-module="support_button" style="<?php echo esc_attr( $preview_vars ); ?>">

	<section class="lzp-module-head">
		<span class="lzp-module-head__icon" aria-hidden="true"><?php echo $ui_icon( 'support' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG. ?></span>
		<div class="lzp-module-head__text">
			<h1><?php echo esc_html( $module->title() ); ?></h1>
			<p><?php echo esc_html( $module->description() ); ?></p>
		</div>
		<label class="lzp-module-head__switch">
			<span data-lzp-show-if="enabled"><?php esc_html_e( 'Active', 'lenz-plus' ); ?></span>
			<span data-lzp-show-if="!enabled"><?php esc_html_e( 'Inactive', 'lenz-plus' ); ?></span>
			<span class="lzp-switch lzp-switch--lg">
				<input type="checkbox" data-lzp-bind="enabled" aria-label="<?php esc_attr_e( 'Enable the support button', 'lenz-plus' ); ?>">
				<span class="lzp-switch__track" aria-hidden="true"></span>
			</span>
		</label>
	</section>

	<div class="lzp-module-body">
		<div class="lzp-settings">

			<div class="lzp-tabs" role="tablist" data-lzp-tabs="support_button">
				<?php foreach ( $panel_tabs as $tab_id => $panel_tab ) : ?>
					<button type="button" class="lzp-tab" role="tab" id="lzp-tab-<?php echo esc_attr( $tab_id ); ?>" aria-controls="lzp-panel-<?php echo esc_attr( $tab_id ); ?>" aria-selected="false" data-lzp-tab="<?php echo esc_attr( $tab_id ); ?>">
						<?php echo $ui_icon( $panel_tab[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_html( $panel_tab[1] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>

			<?php /* ------------------------------------------------------- Channels */ ?>
			<section class="lzp-panel" role="tabpanel" id="lzp-panel-channels" aria-labelledby="lzp-tab-channels" data-lzp-panel="channels">
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Channels', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Switch on the ways visitors can reach you, fill in your ID or number, and drag to reorder. A channel without an ID stays hidden.', 'lenz-plus' ); ?></p>
					</header>

					<p class="lzp-inline-note lzp-inline-note--warn" data-lzp-none-ready hidden>
						<?php echo $ui_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'No channel is ready yet, so the button is not shown on your site. Fill in at least one switched-on channel.', 'lenz-plus' ); ?>
					</p>

					<ul class="lzp-items" data-lzp-channels>
						<?php foreach ( $settings['order'] as $channel_id ) : ?>
							<?php
							$channel = $channels[ $channel_id ];
							$bind    = 'channels.' . $channel_id . '.';
							$field   = 'lzp-sb-' . $channel_id . '-';
							?>
							<li class="lzp-item" data-channel="<?php echo esc_attr( $channel_id ); ?>">
								<div class="lzp-item__row">
									<span class="lzp-item__handle" aria-hidden="true" title="<?php esc_attr_e( 'Drag to reorder', 'lenz-plus' ); ?>"><?php echo Icon_Library::svg( 'phosphor', 'grip' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									<span class="lzp-item__icon lzp-channel-icon" data-channel-icon aria-hidden="true" style="--lzp-sb-brand:<?php echo esc_attr( '' !== $channel['color'] ? $channel['color'] : 'var(--primary-1)' ); ?>"></span>
									<button type="button" class="lzp-item__summary" data-item-toggle aria-expanded="false" aria-controls="<?php echo esc_attr( $field . 'editor' ); ?>">
										<span class="lzp-item__label" data-channel-label><?php echo esc_html( $channel['label'] ); ?></span>
										<span class="lzp-item__chips">
											<span class="lzp-chip" data-channel-value dir="ltr" hidden></span>
											<span class="lzp-chip lzp-chip--warn" data-channel-warning hidden></span>
										</span>
									</button>
									<span class="lzp-item__tools">
										<button type="button" class="lzp-move" data-channel-move="up" aria-label="<?php esc_attr_e( 'Move up', 'lenz-plus' ); ?>">▲</button>
										<button type="button" class="lzp-move" data-channel-move="down" aria-label="<?php esc_attr_e( 'Move down', 'lenz-plus' ); ?>">▼</button>
										<label class="lzp-switch lzp-switch--sm" title="<?php esc_attr_e( 'Show this channel', 'lenz-plus' ); ?>">
											<input type="checkbox" data-lzp-bind="<?php echo esc_attr( $bind . 'enabled' ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: channel name, e.g. Telegram. */ __( 'Show %s', 'lenz-plus' ), $channel['label'] ) ); ?>">
											<span class="lzp-switch__track" aria-hidden="true"></span>
										</label>
										<button type="button" class="lzp-icon-btn lzp-item__chevron" data-item-toggle tabindex="-1" aria-hidden="true"><?php echo Icon_Library::svg( 'phosphor', 'chevron-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
									</span>
								</div>

								<div class="lzp-item__editor" id="<?php echo esc_attr( $field . 'editor' ); ?>" hidden>
									<div class="lzp-fields lzp-fields--2">
										<div class="lzp-field lzp-field--full">
											<label class="lzp-field__label" for="<?php echo esc_attr( $field . 'value' ); ?>">
												<?php echo esc_html( 'link' === $channel_id ? __( 'Link (URL)', 'lenz-plus' ) : __( 'ID, number or link', 'lenz-plus' ) ); ?>
											</label>
											<input type="text" class="lzp-input" id="<?php echo esc_attr( $field . 'value' ); ?>" data-lzp-bind="<?php echo esc_attr( $bind . 'value' ); ?>" dir="ltr" placeholder="<?php echo esc_attr( $channel['placeholder'] ); ?>" autocomplete="off" spellcheck="false">
											<p class="lzp-field__help"><?php echo esc_html( $channel['help'] ); ?></p>
											<?php if ( 'phone' === $channel_id && '' !== $theme_phone ) : ?>
												<p class="lzp-field__help">
													<?php esc_html_e( 'Number in the Lenz mobile menu:', 'lenz-plus' ); ?>
													<bdi dir="ltr"><?php echo esc_html( $theme_phone ); ?></bdi>
													<button type="button" class="lzp-btn lzp-btn--soft lzp-btn--sm" data-lzp-fill="<?php echo esc_attr( $bind . 'value' ); ?>" data-lzp-fill-value="<?php echo esc_attr( $theme_phone ); ?>" data-lzp-fill-enable="<?php echo esc_attr( $bind . 'enabled' ); ?>"><?php esc_html_e( 'Use it', 'lenz-plus' ); ?></button>
												</p>
											<?php endif; ?>
											<p class="lzp-channel-url" data-channel-url hidden></p>
										</div>

										<div class="lzp-field">
											<label class="lzp-field__label" for="<?php echo esc_attr( $field . 'label' ); ?>"><?php esc_html_e( 'Label', 'lenz-plus' ); ?></label>
											<input type="text" class="lzp-input" id="<?php echo esc_attr( $field . 'label' ); ?>" data-lzp-bind="<?php echo esc_attr( $bind . 'label' ); ?>" maxlength="30" placeholder="<?php echo esc_attr( $channel['label'] ); ?>">
										</div>

										<div class="lzp-field">
											<label class="lzp-field__label" for="<?php echo esc_attr( $field . 'note' ); ?>"><?php esc_html_e( 'Note under the label', 'lenz-plus' ); ?></label>
											<input type="text" class="lzp-input" id="<?php echo esc_attr( $field . 'note' ); ?>" data-lzp-bind="<?php echo esc_attr( $bind . 'note' ); ?>" maxlength="60" placeholder="<?php esc_attr_e( 'e.g. Replies within an hour', 'lenz-plus' ); ?>">
										</div>

										<?php if ( 'whatsapp' === $channel_id ) : ?>
											<div class="lzp-field lzp-field--full">
												<label class="lzp-field__label" for="<?php echo esc_attr( $field . 'message' ); ?>"><?php esc_html_e( 'Ready-made first message', 'lenz-plus' ); ?></label>
												<input type="text" class="lzp-input" id="<?php echo esc_attr( $field . 'message' ); ?>" data-lzp-bind="<?php echo esc_attr( $bind . 'message' ); ?>" maxlength="200" placeholder="<?php esc_attr_e( 'Hello, I have a question about…', 'lenz-plus' ); ?>">
												<p class="lzp-field__help"><?php esc_html_e( 'Optional. WhatsApp opens with this text typed in; the visitor can edit it before sending.', 'lenz-plus' ); ?></p>
											</div>
										<?php endif; ?>

										<?php if ( 'link' === $channel_id ) : ?>
											<div class="lzp-field">
												<label class="lzp-field__label" for="<?php echo esc_attr( $field . 'icon' ); ?>"><?php esc_html_e( 'Icon', 'lenz-plus' ); ?></label>
												<div class="lzp-select">
													<select id="<?php echo esc_attr( $field . 'icon' ); ?>" data-lzp-bind="<?php echo esc_attr( $bind . 'icon' ); ?>">
														<?php foreach ( $icon_labels as $icon_key => $icon_label ) : ?>
															<option value="<?php echo esc_attr( $icon_key ); ?>"><?php echo esc_html( $icon_label ); ?></option>
														<?php endforeach; ?>
													</select>
												</div>
											</div>
										<?php endif; ?>
									</div>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</section>

			<?php /* ----------------------------------------------------- Appearance */ ?>
			<section class="lzp-panel" role="tabpanel" id="lzp-panel-appearance" aria-labelledby="lzp-tab-appearance" data-lzp-panel="appearance" hidden>
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Design', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'How the channels appear when the button is tapped. The previews use your real channels.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-style-grid" role="radiogroup" aria-label="<?php esc_attr_e( 'Design', 'lenz-plus' ); ?>">
						<?php foreach ( $designs as $design_id => $design ) : ?>
							<label class="lzp-style-card">
								<input type="radio" name="lzp-sb-design" value="<?php echo esc_attr( $design_id ); ?>" data-lzp-bind="design">
								<span class="lzp-style-card__stage lzp-stage lzp-sb-stage" data-lzp-design-stage="<?php echo esc_attr( $design_id ); ?>" dir="<?php echo esc_attr( Site::direction() ); ?>" aria-hidden="true"></span>
								<span class="lzp-style-card__body">
									<span class="lzp-style-card__title"><?php echo esc_html( $design[0] ); ?> <i class="lzp-style-card__check" aria-hidden="true"><?php echo Icon_Library::svg( 'tabler', 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></i></span>
									<span class="lzp-style-card__desc"><?php echo esc_html( $design[1] ); ?></span>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Main button', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'With a single channel switched on, the button opens it directly and shows its logo.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields">
						<div class="lzp-field lzp-field--full">
							<span class="lzp-field__label" id="lzp-sb-icon-label"><?php esc_html_e( 'Icon', 'lenz-plus' ); ?></span>
							<div class="lzp-icon-radios" role="radiogroup" aria-labelledby="lzp-sb-icon-label">
								<?php foreach ( $icon_labels as $icon_key => $icon_label ) : ?>
									<label class="lzp-icon-radio" title="<?php echo esc_attr( $icon_label ); ?>">
										<input type="radio" name="lzp-sb-icon" value="<?php echo esc_attr( $icon_key ); ?>" data-lzp-bind="button.icon" aria-label="<?php echo esc_attr( $icon_label ); ?>">
										<span aria-hidden="true"><?php echo Icon_Library::svg( 'phosphor', $icon_key, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
					<div class="lzp-fields lzp-fields--2">
						<?php
						Fields::text(
							'button.label',
							__( 'Button text', 'lenz-plus' ),
							array(
								'placeholder' => __( 'Support', 'lenz-plus' ),
								'help'        => __( 'Also read out by screen readers when the text is hidden.', 'lenz-plus' ),
							)
						);
						Fields::range( 'button.size', __( 'Button size', 'lenz-plus' ), 48, 72 );
						?>
					</div>
					<div class="lzp-fields">
						<?php
						Fields::segmented(
							'button.label_mode',
							__( 'Show the text', 'lenz-plus' ),
							array(
								'always'  => __( 'Always', 'lenz-plus' ),
								'desktop' => __( 'On computers only', 'lenz-plus' ),
								'never'   => __( 'Icon only', 'lenz-plus' ),
							),
							array( 'help' => __( 'On phones a round icon button takes the least room.', 'lenz-plus' ) )
						);
						Fields::toggle( 'button.pulse', __( 'Attention pulse', 'lenz-plus' ), array( 'help' => __( 'A soft ring pulses three times after the page loads. Visitors who ask for reduced motion never see it.', 'lenz-plus' ) ) );
						?>
					</div>
				</div>

				<div class="lzp-card" data-lzp-show-if="design=card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Card heading', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Leave both empty to show the channels only.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields">
						<?php
						Fields::text( 'header.title', __( 'Title', 'lenz-plus' ) );
						Fields::text( 'header.subtitle', __( 'Subtitle', 'lenz-plus' ) );
						?>
					</div>
				</div>

				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Colours', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'Leave a colour empty to follow your Lenz theme colours (its primary button) automatically, including its dark demo palette.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields">
						<?php Fields::toggle( 'colors.brand', __( 'Brand colours for channels', 'lenz-plus' ), array( 'help' => __( 'Telegram blue, WhatsApp green and so on. Off: every channel uses the button colour.', 'lenz-plus' ) ) ); ?>
					</div>
					<div class="lzp-color-grid">
						<?php
						Fields::color( 'colors.button_bg', __( 'Button', 'lenz-plus' ), $palette['--primary-1'] );
						Fields::color( 'colors.button_icon', __( 'Button icon and text', 'lenz-plus' ), $palette['--secondary-1'] );
						?>
					</div>
				</div>
			</section>

			<?php /* ------------------------------------------------ Position & display */ ?>
			<section class="lzp-panel" role="tabpanel" id="lzp-panel-display" aria-labelledby="lzp-tab-display" data-lzp-panel="display" hidden>
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Position', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'The button rises automatically above the bottom navigation, sticky buy bars and the theme\'s "back to top" button.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields">
						<?php Fields::segmented( 'position.side', __( 'Corner', 'lenz-plus' ), $sides ); ?>
					</div>
					<div class="lzp-fields lzp-fields--2">
						<?php
						Fields::range( 'position.offset_x', __( 'Distance from the side', 'lenz-plus' ), 8, 80 );
						Fields::range( 'position.offset_y', __( 'Distance from the bottom', 'lenz-plus' ), 8, 160 );
						?>
					</div>
				</div>

				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Greeting', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'A short message beside the button, shown once per visit. Tapping it opens the channels.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields">
						<?php
						Fields::toggle( 'greeting.enabled', __( 'Show a greeting', 'lenz-plus' ) );
						Fields::text( 'greeting.text', __( 'Message', 'lenz-plus' ), array( 'show_if' => 'greeting.enabled' ) );
						Fields::range( 'greeting.delay', __( 'Appears after', 'lenz-plus' ), 0, 60, 1, _x( 's', 'unit after a number of seconds', 'lenz-plus' ), array( 'show_if' => 'greeting.enabled' ) );
						?>
					</div>
				</div>

				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Where to show', 'lenz-plus' ); ?></h2>
					</header>
					<div class="lzp-fields">
						<?php
						Fields::segmented(
							'display.devices',
							__( 'Devices', 'lenz-plus' ),
							array(
								'all'     => __( 'All', 'lenz-plus' ),
								'desktop' => __( 'Computers and tablets', 'lenz-plus' ),
								'mobile'  => __( 'Phones only', 'lenz-plus' ),
							),
							array( 'help' => __( 'Phones are screens narrower than 768px.', 'lenz-plus' ) )
						);
						Fields::toggle( 'display.hide_on_checkout', __( 'Hide on the checkout page', 'lenz-plus' ), array( 'help' => __( 'Keeps buyers focused on completing the order.', 'lenz-plus' ) ) );
						Fields::toggle( 'display.hide_on_cart', __( 'Hide on the cart page', 'lenz-plus' ) );
						Fields::text(
							'display.hide_for_ids',
							__( 'Hide on these pages or posts (IDs)', 'lenz-plus' ),
							array(
								'placeholder' => '12, 345',
								'dir'         => 'ltr',
								'help'        => __( 'Comma separated IDs, e.g. landing pages that have their own call to action.', 'lenz-plus' ),
							)
						);
						Fields::text(
							'display.z_index',
							__( 'Stacking order (z-index)', 'lenz-plus' ),
							array(
								'type' => 'number',
								'dir'  => 'ltr',
								'help' => __( 'Raise it if a popup or chat widget covers the button.', 'lenz-plus' ),
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
					<div class="lzp-segmented lzp-segmented--sm" role="radiogroup" aria-label="<?php esc_attr_e( 'Preview state', 'lenz-plus' ); ?>">
						<label class="lzp-segmented__option"><input type="radio" name="lzp-sb-preview-state" value="open" data-lzp-preview-state checked><span><?php esc_html_e( 'Open', 'lenz-plus' ); ?></span></label>
						<label class="lzp-segmented__option"><input type="radio" name="lzp-sb-preview-state" value="closed" data-lzp-preview-state><span><?php esc_html_e( 'Closed', 'lenz-plus' ); ?></span></label>
					</div>
				</div>

				<div class="lzp-phone">
					<div class="lzp-phone__notch" aria-hidden="true"></div>
					<div class="lzp-phone__screen lzp-stage lzp-sb-stage" data-lzp-preview-screen dir="<?php echo esc_attr( Site::direction() ); ?>">
						<div class="lzp-mock" aria-hidden="true">
							<div class="lzp-mock__header"><span></span><i></i></div>
							<div class="lzp-mock__hero"></div>
							<div class="lzp-mock__row"><span></span><span></span></div>
							<div class="lzp-mock__card"></div>
							<div class="lzp-mock__card"></div>
						</div>
						<div data-lzp-preview-host></div>
						<p class="lzp-sb-stage__note" data-lzp-preview-empty hidden><?php esc_html_e( 'Fill in a channel to see the button.', 'lenz-plus' ); ?></p>
						<p class="lzp-sb-stage__note" data-lzp-preview-hidden hidden><?php esc_html_e( 'Hidden on phones (Position & display tab).', 'lenz-plus' ); ?></p>
					</div>
				</div>

				<p class="lzp-preview__caption">
					<?php esc_html_e( 'Links are not opened in the preview. Colours and font follow your Lenz settings.', 'lenz-plus' ); ?>
				</p>
			</div>
		</aside>
	</div>
</div>
