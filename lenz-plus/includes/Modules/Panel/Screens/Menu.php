<?php
/**
 * "More" screen of the phone's tab bar: every section the user can open,
 * as big tiles with their counts (the sidebar shows the same list on wide
 * screens).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Modules\Panel\Router;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * All sections as tiles.
 */
final class Menu extends Screen {

	/** Screen id. */
	public function id(): string {
		return 'menu';
	}

	/** Title. */
	public function title(): string {
		return __( 'More', 'lenz-plus' );
	}

	/** Icon. */
	public function icon(): string {
		return 'grid';
	}

	/** Every panel user. */
	public function capability(): string {
		return 'read';
	}

	/** Reached from the tab bar. */
	public function in_menu(): bool {
		return false;
	}

	/** Prints the tiles and the exit links. */
	public function render(): void {
		echo '<ul class="lzp-tiles">';
		foreach ( $this->module->app()->menu_screens() as $screen ) {
			$badge = $screen->badge();
			printf(
				'<li><a class="lzp-tile" href="%1$s">%2$s<span>%3$s</span>%4$s</a></li>',
				esc_url( $screen->url() ),
				Ui::icon( $screen->icon() ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG.
				esc_html( $screen->title() ),
				$badge ? '<b class="lzp-badge">' . esc_html( Ui::number( $badge ) ) . '</b>' : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped here.
			);
		}
		echo '</ul>';
		?>
		<div class="lzp-actions lzp-actions--stack lzp-menu-exit">
			<a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php Ui::the_icon( 'globe' ); ?><span><?php esc_html_e( 'View the site', 'lenz-plus' ); ?></span></a>
			<a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( wp_logout_url( Router::url() ) ); ?>"><?php Ui::the_icon( 'logout' ); ?><span><?php esc_html_e( 'Log out', 'lenz-plus' ); ?></span></a>
		</div>
		<?php
	}
}
