<?php
/**
 * Success hero of the order-received page (replaces `checkout/order-received.php`
 * while the shop pages are styled, see Shop_Pages::templates()).
 *
 * The heading keeps WooCommerce's classes and its
 * `woocommerce_thankyou_order_received_text` filter, so plugins that change
 * the message still do, unless a heading is set in the checkout module. WooCommerce prints the order overview, payment
 * details and order table after it. Included by wc_get_template() with
 * `$order` (a WC_Order, or false when the order could not be loaded).
 *
 * @package LenzPlus
 */

use LenzPlus\Modules\Builder\Shop_Pages;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included by wc_get_template(), so variables are local to it.

$lzp_order  = isset( $order ) ? $order : false;
$lzp_thanks = Shop_Pages::thanks( $lzp_order );
$lzp_status = $lzp_order instanceof WC_Order ? $lzp_order->get_status() : '';
$lzp_paid   = in_array( $lzp_status, array( 'processing', 'completed' ), true );

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound, WordPress.WP.I18n.TextDomainMismatch -- WooCommerce's own filter and sentence (translated by WooCommerce).
$lzp_message = apply_filters(
	'woocommerce_thankyou_order_received_text',
	esc_html( __( 'Thank you. Your order has been received.', 'woocommerce' ) ),
	$lzp_order
);
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound, WordPress.WP.I18n.TextDomainMismatch
?>
<section class="lzp-thanks<?php echo $lzp_paid || '' === $lzp_status ? ' is-paid' : ' is-waiting'; ?>" aria-labelledby="lzp-thanks-title">
	<span class="lzp-thanks__mark" aria-hidden="true">
		<svg viewBox="0 0 52 52" focusable="false">
			<circle class="lzp-thanks__ring" cx="26" cy="26" r="24" fill="none" />
			<path class="lzp-thanks__check" fill="none" d="M15 27l7.5 7.5L37.5 19" />
		</svg>
	</span>

	<?php if ( $lzp_order instanceof WC_Order ) : ?>
		<p class="lzp-thanks__eyebrow">
			<?php
			/* translators: %s: order number. */
			echo esc_html( sprintf( __( 'Order #%s', 'lenz-plus' ), $lzp_order->get_order_number() ) );
			?>
		</p>
	<?php endif; ?>

	<h2 id="lzp-thanks-title" class="lzp-thanks__title woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
		<?php if ( '' !== $lzp_thanks['title'] ) : ?>
			<?php echo esc_html( $lzp_thanks['title'] ); ?>
		<?php else : ?>
			<?php echo $lzp_message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above; filters may add markup, as in WooCommerce's template. ?>
		<?php endif; ?>
	</h2>

	<?php if ( '' !== $lzp_thanks['lead'] ) : ?>
		<p class="lzp-thanks__lead"><?php echo esc_html( $lzp_thanks['lead'] ); ?></p>
	<?php endif; ?>

	<?php if ( array() !== $lzp_thanks['courses'] ) : ?>
		<ul class="lzp-thanks__courses">
			<?php foreach ( $lzp_thanks['courses'] as $lzp_course ) : ?>
				<li class="lzp-thanks__course">
					<a class="lzp-thanks__course-title" href="<?php echo esc_url( $lzp_course['url'] ); ?>"><?php echo esc_html( $lzp_course['title'] ); ?></a>
					<?php if ( array() !== $lzp_course['meta'] ) : ?>
						<span class="lzp-thanks__course-meta">
							<?php foreach ( $lzp_course['meta'] as $lzp_meta ) : ?>
								<span><?php echo esc_html( $lzp_meta ); ?></span>
							<?php endforeach; ?>
						</span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<div class="lzp-thanks__actions">
		<?php foreach ( $lzp_thanks['actions'] as $lzp_action ) : ?>
			<a class="button lzp-thanks__btn<?php echo $lzp_action['primary'] ? ' alt' : ''; ?>" href="<?php echo esc_url( $lzp_action['url'] ); ?>"><?php echo esc_html( $lzp_action['label'] ); ?></a>
		<?php endforeach; ?>
	</div>
</section>
