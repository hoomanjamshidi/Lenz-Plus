<?php
/**
 * Settings panel of the checkout form module.
 *
 * Every control binds to the settings by path (`data-lzp-bind`); the admin
 * shell saves them. The compatibility tab reports what it finds on the site
 * (checkout block or shortcode, Persian WooCommerce, PWS), since the field
 * rules only reach the classic checkout.
 *
 * @var \LenzPlus\Modules\Checkout\Module $module
 *
 * @package LenzPlus
 */

use LenzPlus\Admin\Fields;
use LenzPlus\Core\Icon_Library;
use LenzPlus\Modules\Builder\Shop_Pages;
use LenzPlus\Modules\Checkout\Schema;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included from a class method, so variables are local.

$ui_icon = static function ( string $key ): string {
	return Icon_Library::svg( 'phosphor-duotone', $key, false, 'lzp-ico' );
};

$woocommerce = class_exists( 'WooCommerce' );
$checkout_id = $woocommerce ? (int) wc_get_page_id( 'checkout' ) : 0;
$block_form  = $checkout_id > 0 && has_block( 'woocommerce/checkout', $checkout_id );

$field_labels = array(
	'first_name' => __( 'First name', 'lenz-plus' ),
	'last_name'  => __( 'Last name', 'lenz-plus' ),
	'company'    => __( 'Company name', 'lenz-plus' ),
	'country'    => __( 'Country / Region', 'lenz-plus' ),
	'state'      => __( 'Province', 'lenz-plus' ),
	'city'       => __( 'City', 'lenz-plus' ),
	'address_1'  => __( 'Street address', 'lenz-plus' ),
	'address_2'  => __( 'Apartment, suite, unit', 'lenz-plus' ),
	'postcode'   => __( 'Postcode', 'lenz-plus' ),
	'phone'      => __( 'Phone', 'lenz-plus' ),
	'email'      => __( 'Email address', 'lenz-plus' ),
	'notes'      => __( 'Order notes', 'lenz-plus' ),
);

$modes = array(
	'default'  => __( 'Default (as WooCommerce sets it)', 'lenz-plus' ),
	'required' => __( 'Required', 'lenz-plus' ),
	'optional' => __( 'Optional', 'lenz-plus' ),
	'hidden'   => __( 'Hidden', 'lenz-plus' ),
);

$panel_tabs = array(
	'fields' => array( 'list', __( 'Billing fields', 'lenz-plus' ) ),
	'quick'  => array( 'download', __( 'Quick checkout', 'lenz-plus' ) ),
	'thanks' => array( 'check', __( 'Order received page', 'lenz-plus' ) ),
	'digits' => array( 'settings', __( 'Digits & compatibility', 'lenz-plus' ) ),
);

// The built-in texts show as placeholders: an empty field keeps them.
$thanks_defaults = Shop_Pages::default_thanks_texts();
$thanks_labels   = array(
	'title'    => array( __( 'Heading', 'lenz-plus' ), __( 'Empty: WooCommerce\'s «Thank you. Your order has been received.»', 'lenz-plus' ) ),
	'course'   => array( __( 'Course paid', 'lenz-plus' ), __( 'The order holds a course and its payment went through.', 'lenz-plus' ) ),
	'download' => array( __( 'Files ready', 'lenz-plus' ), __( 'Paid order with downloadable files (the page also shows a "Download the files" button).', 'lenz-plus' ) ),
	'paid'     => array( __( 'Other paid orders', 'lenz-plus' ), __( 'Paid orders without courses or files, such as physical products.', 'lenz-plus' ) ),
	'on_hold'  => array( __( 'Waiting for payment confirmation', 'lenz-plus' ), __( 'Bank transfer, card to card or cheque: the order waits until you confirm the payment.', 'lenz-plus' ) ),
	'pending'  => array( __( 'Not paid', 'lenz-plus' ), __( 'The payment was not completed (for example the customer left the bank page).', 'lenz-plus' ) ),
	'email'    => array( __( 'Email line', 'lenz-plus' ), __( 'Added after the text above when the order has an email address. Write - to show no email line.', 'lenz-plus' ) ),
);

$iran_plugins = array(
	array( __( 'Persian WooCommerce', 'lenz-plus' ), defined( 'PW_VERSION' ), __( 'Its Iranian provinces and its own phone and postcode fixes keep working.', 'lenz-plus' ) ),
	array( __( 'Persian WooCommerce Shipping', 'lenz-plus' ), defined( 'PWS_VERSION' ), __( 'Its province, city and district lists stay as they are; the district field follows the city field\'s setting.', 'lenz-plus' ) ),
);
?>
<div class="lzp-module" data-lzp-module="checkout">

	<section class="lzp-module-head">
		<span class="lzp-module-head__icon" aria-hidden="true"><?php echo $ui_icon( 'receipt' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG. ?></span>
		<div class="lzp-module-head__text">
			<h1><?php echo esc_html( $module->title() ); ?></h1>
			<p><?php echo esc_html( $module->description() ); ?></p>
		</div>
		<label class="lzp-module-head__switch">
			<span data-lzp-show-if="enabled"><?php esc_html_e( 'Active', 'lenz-plus' ); ?></span>
			<span data-lzp-show-if="!enabled"><?php esc_html_e( 'Inactive', 'lenz-plus' ); ?></span>
			<span class="lzp-switch lzp-switch--lg">
				<input type="checkbox" data-lzp-bind="enabled" aria-label="<?php esc_attr_e( 'Enable the checkout form rules', 'lenz-plus' ); ?>">
				<span class="lzp-switch__track" aria-hidden="true"></span>
			</span>
		</label>
	</section>

	<?php if ( ! $woocommerce ) : ?>
		<div class="lzp-notice">
			<?php echo $ui_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p><?php esc_html_e( 'WooCommerce is not active. These settings apply once it is.', 'lenz-plus' ); ?></p>
		</div>
	<?php elseif ( $block_form ) : ?>
		<div class="lzp-notice">
			<?php echo $ui_icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p>
				<?php esc_html_e( 'Your checkout page uses the Checkout block. The field settings and the short course form apply to the classic checkout form, which Lenz, Persian WooCommerce Shipping and most Iranian payment gateways also need. Open the page, select the Checkout block and choose "Switch to classic checkout" in its settings. Persian digits are converted on both.', 'lenz-plus' ); ?>
				<a href="<?php echo esc_url( (string) get_edit_post_link( $checkout_id ) ); ?>"><?php esc_html_e( 'Edit the checkout page', 'lenz-plus' ); ?></a>
			</p>
		</div>
	<?php endif; ?>

	<div class="lzp-module-body lzp-module-body--wide">
		<div class="lzp-settings">

			<div class="lzp-tabs" role="tablist" data-lzp-tabs="checkout">
				<?php foreach ( $panel_tabs as $tab_id => $panel_tab ) : ?>
					<button type="button" class="lzp-tab" role="tab" id="lzp-tab-<?php echo esc_attr( $tab_id ); ?>" aria-controls="lzp-panel-<?php echo esc_attr( $tab_id ); ?>" aria-selected="false" data-lzp-tab="<?php echo esc_attr( $tab_id ); ?>">
						<?php echo $ui_icon( $panel_tab[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_html( $panel_tab[1] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-fields" aria-labelledby="lzp-tab-fields">
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Billing fields', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'What the checkout asks for on ordinary orders. "Default" leaves a field to WooCommerce and to plugins such as Persian WooCommerce Shipping; hidden fields are left out of the form. Keep the phone or the email: the order needs a way to reach the customer, and most Iranian gateways send the phone to the bank.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields lzp-fields--2">
						<?php foreach ( Schema::FIELDS as $field_key ) : ?>
							<?php Fields::select( 'fields.' . $field_key, $field_labels[ $field_key ], $modes ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-quick" aria-labelledby="lzp-tab-quick" hidden>
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Quick checkout when nothing is shipped', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'When everything in the cart is downloadable or virtual (files, services, courses), there is no parcel to send, so the checkout uses this shorter form and the customer pays in a few seconds. Carts with a physical product use the billing fields of the first tab.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields">
						<?php Fields::toggle( 'quick', __( 'Short form for download and virtual orders', 'lenz-plus' ) ); ?>
					</div>
					<div class="lzp-fields lzp-fields--2" data-lzp-show-if="quick">
						<?php foreach ( Schema::FIELDS as $field_key ) : ?>
							<?php Fields::select( 'quick_fields.' . $field_key, $field_labels[ $field_key ], $modes ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-thanks" aria-labelledby="lzp-tab-thanks" hidden>
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Order received page', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'What the page says after an order, for each situation. Leave a field empty to keep the text shown in it. You can use {name} (first name), {order} (order number) and {email}. The page\'s design comes with the shop pages style of the page templates.', 'lenz-plus' ); ?></p>
					</header>
					<div class="lzp-fields">
						<?php foreach ( Schema::THANKS_TEXTS as $text_key ) : ?>
							<?php
							Fields::text(
								'thanks.' . $text_key,
								$thanks_labels[ $text_key ][0],
								array(
									'placeholder' => $thanks_defaults[ $text_key ],
									'help'        => $thanks_labels[ $text_key ][1],
								)
							);
							?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>

			<section class="lzp-panel" role="tabpanel" id="lzp-panel-digits" aria-labelledby="lzp-tab-digits" hidden>
				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Persian digits', 'lenz-plus' ); ?></h2>
					</header>
					<div class="lzp-fields">
						<?php
						Fields::toggle(
							'latin_digits',
							__( 'Turn Persian digits into English digits', 'lenz-plus' ),
							array( 'help' => __( 'Phone numbers, postcodes and emails typed as ۰۹۱۲… are saved as 0912…, as WooCommerce\'s checks and the payment gateways expect. Works on the checkout (classic and block) and on the account\'s address and details forms.', 'lenz-plus' ) )
						);
						?>
					</div>
				</div>

				<div class="lzp-card">
					<header class="lzp-card__head">
						<h2><?php esc_html_e( 'Iranian plugins', 'lenz-plus' ); ?></h2>
						<p><?php esc_html_e( 'The field rules run after these plugins and only hide fields or change whether they are required.', 'lenz-plus' ); ?></p>
					</header>
					<ul class="lzp-checklist">
						<?php foreach ( $iran_plugins as $iran_plugin ) : ?>
							<li>
								<span class="lzp-status-dot<?php echo $iran_plugin[1] ? ' is-on' : ''; ?>" aria-hidden="true"></span>
								<strong><?php echo esc_html( $iran_plugin[0] ); ?></strong>
								<span class="lzp-chip<?php echo $iran_plugin[1] ? ' lzp-chip--accent' : ''; ?>"><?php echo $iran_plugin[1] ? esc_html__( 'Active', 'lenz-plus' ) : esc_html__( 'Not installed', 'lenz-plus' ); ?></span>
								<p><?php echo esc_html( $iran_plugin[2] ); ?></p>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</section>

		</div>
	</div>
</div>
