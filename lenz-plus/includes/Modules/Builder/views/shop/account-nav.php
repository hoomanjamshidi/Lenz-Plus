<?php
/**
 * Account menu of WooCommerce's account pages (replaces `myaccount/navigation.php`
 * while the shop pages are styled, see Shop_Pages::account_navigation()).
 *
 * The same hooks and classes as WooCommerce's template, so Lenz's profile box
 * (printed on `woocommerce_before_account_navigation`) and plugins that add
 * menu items keep working; only Lenz's slider is left out. shop.css shows it
 * as a column on wide screens and a scrolling row on phones.
 *
 * @package LenzPlus
 */

use LenzPlus\Modules\Builder\Shop_Pages;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included by wc_get_template(), so variables are local to it.

do_action( 'woocommerce_before_account_navigation' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce's own hook.
?>
<nav class="woocommerce-MyAccount-navigation lzp-account-nav" aria-label="<?php esc_attr_e( 'Account pages', 'lenz-plus' ); ?>">
	<ul class="lzp-account-nav__list">
		<?php foreach ( wc_get_account_menu_items() as $lzp_endpoint => $lzp_label ) : ?>
			<?php $lzp_current = wc_is_current_account_menu_item( $lzp_endpoint ); ?>
			<li class="<?php echo esc_attr( wc_get_account_menu_item_classes( $lzp_endpoint ) ); ?>">
				<a class="lzp-account-nav__link" href="<?php echo esc_url( wc_get_account_endpoint_url( $lzp_endpoint ) ); ?>"<?php echo $lzp_current ? ' aria-current="page"' : ''; ?>>
					<?php echo Shop_Pages::account_icon( $lzp_endpoint ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- glyph markup or sanitized SVG. ?>
					<span><?php echo esc_html( $lzp_label ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
<?php
do_action( 'woocommerce_after_account_navigation' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce's own hook.
