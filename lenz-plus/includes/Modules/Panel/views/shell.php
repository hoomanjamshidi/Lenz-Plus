<?php
/**
 * The panel's page: sidebar (wide screens), top bar, the screen's content
 * and the tab bar (phones). Sub pages (editing, an order) hide the tab bar
 * and show a back button, like a phone app.
 *
 * @var \LenzPlus\Modules\Panel\App                 $app
 * @var \LenzPlus\Modules\Panel\Module              $module
 * @var \LenzPlus\Modules\Panel\Screens\Screen      $screen
 * @var string[]                                    $message [text, tone] or empty.
 * @var string                                      $content The screen's printed page.
 *
 * @package LenzPlus
 */

use LenzPlus\Modules\Panel\Router;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included from a class method, so variables are local.

$back       = $screen->back_url();
$top_action = $screen->primary_action();
$user       = wp_get_current_user();
$doc_title  = $screen->page_title();
$body_class = 'lzp-p lzp-p--' . $screen->id() . ( '' !== $back ? ' is-sub' : '' );
$logo       = $app->logo_html();

require __DIR__ . '/head.php';
?>
<a class="lzp-skip" href="#lzp-main"><?php esc_html_e( 'Skip to content', 'lenz-plus' ); ?></a>
<div class="lzp-app">
	<aside class="lzp-side">
		<a class="lzp-brand" href="<?php echo esc_url( Router::url() ); ?>">
			<?php if ( '' !== $logo ) : ?>
				<span class="lzp-brand__logo"><?php echo wp_kses_post( $logo ); ?></span>
			<?php else : ?>
				<span class="lzp-brand__name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
			<?php endif; ?>
			<span class="lzp-brand__panel"><?php echo esc_html( $app->title() ); ?></span>
		</a>
		<nav class="lzp-nav" aria-label="<?php esc_attr_e( 'Panel menu', 'lenz-plus' ); ?>">
			<ul>
				<?php foreach ( $app->menu_screens() as $item ) : ?>
					<?php $badge = $item->badge(); ?>
					<li>
						<a class="lzp-nav__link" href="<?php echo esc_url( $item->url() ); ?>"<?php echo $item->id() === $screen->id() ? ' aria-current="page"' : ''; ?>>
							<?php Ui::the_icon( $item->icon() ); ?>
							<span><?php echo esc_html( $item->title() ); ?></span>
							<?php if ( $badge ) : ?>
								<b class="lzp-badge"><?php echo esc_html( Ui::number( $badge ) ); ?></b>
							<?php endif; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<div class="lzp-side__foot">
			<a class="lzp-nav__link" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php Ui::the_icon( 'globe' ); ?><span><?php esc_html_e( 'View the site', 'lenz-plus' ); ?></span></a>
			<a class="lzp-nav__link" href="<?php echo esc_url( wp_logout_url( Router::url() ) ); ?>"><?php Ui::the_icon( 'logout' ); ?><span><?php esc_html_e( 'Log out', 'lenz-plus' ); ?></span></a>
		</div>
	</aside>

	<div class="lzp-main">
		<header class="lzp-top">
			<?php if ( '' !== $back ) : ?>
				<a class="lzp-icon-btn" href="<?php echo esc_url( $back ); ?>">
					<?php Ui::the_icon( 'back', 'lzp-ico--start' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Back', 'lenz-plus' ); ?></span>
				</a>
			<?php endif; ?>
			<h1 class="lzp-top__title"><?php echo esc_html( $doc_title ); ?></h1>
			<?php if ( $top_action ) : ?>
				<a class="lzp-btn lzp-btn--primary lzp-top__action" href="<?php echo esc_url( $top_action[0] ); ?>"><?php Ui::the_icon( $top_action[2] ); ?><span><?php echo esc_html( $top_action[1] ); ?></span></a>
			<?php endif; ?>
			<a class="lzp-top__me" href="<?php echo esc_url( Router::url( 'account' ) ); ?>"<?php echo 'account' === $screen->id() ? ' aria-current="page"' : ''; ?>>
				<?php echo get_avatar( $user->ID, 36, '', '', array( 'class' => 'lzp-avatar' ) ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'My account', 'lenz-plus' ); ?></span>
			</a>
		</header>

		<main id="lzp-main" class="lzp-content" tabindex="-1">
			<?php if ( $message ) : ?>
				<div class="lzp-toast lzp-toast--<?php echo esc_attr( $message[1] ); ?>" role="status" data-lzp-toast>
					<?php Ui::the_icon( 'ok' === $message[1] ? 'check' : 'info' ); ?>
					<span><?php echo esc_html( $message[0] ); ?></span>
				</div>
			<?php endif; ?>
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- printed by the screen, escaped there. ?>
		</main>
	</div>

	<nav class="lzp-tabbar" aria-label="<?php esc_attr_e( 'Main sections', 'lenz-plus' ); ?>">
		<?php foreach ( $app->tabs() as $tab_item ) : ?>
			<a class="lzp-tabbar__item<?php echo 'create' === $tab_item->id() ? ' lzp-tabbar__item--add' : ''; ?>" href="<?php echo esc_url( $tab_item->url() ); ?>"<?php echo $tab_item->id() === $screen->id() ? ' aria-current="page"' : ''; ?>>
				<span class="lzp-tabbar__icon"><?php Ui::the_icon( $tab_item->icon() ); ?></span>
				<span class="lzp-tabbar__label"><?php echo esc_html( $tab_item->title() ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>
</div>
<?php
require __DIR__ . '/foot.php';
