<?php
/**
 * Page for logged-in users whose role does not open the panel.
 *
 * @var \LenzPlus\Modules\Panel\App $app
 *
 * @package LenzPlus
 */

use LenzPlus\Modules\Panel\Router;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included from a class method, so variables are local.

$doc_title  = __( 'No access', 'lenz-plus' );
$body_class = 'lzp-p lzp-p--denied';

require __DIR__ . '/head.php';
?>
<main class="lzp-gate">
	<div class="lzp-gate__card">
		<?php Ui::the_icon( 'lock', 'lzp-gate__icon' ); ?>
		<h1 class="lzp-gate__title"><?php esc_html_e( 'This account cannot open the panel', 'lenz-plus' ); ?></h1>
		<p class="lzp-gate__text"><?php esc_html_e( 'Ask the site\'s administrator to give your account the «Studio manager» role, or log in with another account.', 'lenz-plus' ); ?></p>
		<div class="lzp-actions lzp-actions--stack">
			<a class="lzp-btn lzp-btn--primary lzp-btn--block" href="<?php echo esc_url( wp_logout_url( Router::url() ) ); ?>"><?php esc_html_e( 'Log in with another account', 'lenz-plus' ); ?></a>
			<a class="lzp-btn lzp-btn--ghost lzp-btn--block" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to the site', 'lenz-plus' ); ?></a>
		</div>
	</div>
</main>
<?php
require __DIR__ . '/foot.php';
