<?php
/**
 * Admin shell: top bar, sidebar with every module, and the current panel.
 *
 * @var \LenzPlus\Admin\Admin                 $admin
 * @var array<string, \LenzPlus\Core\Module> $modules
 * @var \LenzPlus\Core\Module|null            $module  Current module (null on the dashboard).
 *
 * @package LenzPlus
 */

use LenzPlus\Core\Theme_Bridge;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included from a class method, so variables are local.
?>
<div class="lzp-admin" id="lzp-admin">
	<header class="lzp-topbar">
		<a class="lzp-brand" href="<?php echo esc_url( $admin->page_url() ); ?>">
			<span class="lzp-brand__mark" aria-hidden="true"><?php echo $admin->icon( 'camera' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG. ?></span>
			<span class="lzp-brand__text">
				<strong><?php esc_html_e( 'Lenz Plus', 'lenz-plus' ); ?></strong>
				<span><?php esc_html_e( 'Extra features for the Lenz theme', 'lenz-plus' ); ?> · v<?php echo esc_html( LENZ_PLUS_VERSION ); ?></span>
			</span>
		</a>

		<?php if ( $module ) : ?>
			<div class="lzp-topbar__actions">
				<span class="lzp-dirty" data-lzp-dirty hidden><?php esc_html_e( 'Unsaved changes', 'lenz-plus' ); ?></span>
				<button type="button" class="lzp-btn lzp-btn--ghost" data-lzp-reset>
					<?php esc_html_e( 'Reset', 'lenz-plus' ); ?>
				</button>
				<button type="button" class="lzp-btn lzp-btn--primary" data-lzp-save disabled>
					<span class="lzp-btn__spinner" aria-hidden="true"></span>
					<span data-lzp-save-label><?php esc_html_e( 'Save changes', 'lenz-plus' ); ?></span>
				</button>
			</div>
		<?php endif; ?>
	</header>

	<div class="lzp-shell">
		<aside class="lzp-sidebar" aria-label="<?php esc_attr_e( 'Features', 'lenz-plus' ); ?>">
			<nav class="lzp-sidenav">
				<a class="lzp-sidenav__link<?php echo $module ? '' : ' is-current'; ?>" href="<?php echo esc_url( $admin->page_url() ); ?>"<?php echo $module ? '' : ' aria-current="page"'; ?>>
					<?php echo $admin->icon( 'grid' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php esc_html_e( 'Dashboard', 'lenz-plus' ); ?></span>
				</a>

				<span class="lzp-sidenav__heading"><?php esc_html_e( 'Features', 'lenz-plus' ); ?></span>

				<?php foreach ( $modules as $item ) : ?>
					<?php $is_current = $module && $module->id() === $item->id(); ?>
					<a class="lzp-sidenav__link<?php echo $is_current ? ' is-current' : ''; ?>" href="<?php echo esc_url( $admin->page_url( $item ) ); ?>"<?php echo $is_current ? ' aria-current="page"' : ''; ?>>
						<?php echo $admin->icon( $item->icon() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_html( $item->title() ); ?></span>
						<i class="lzp-status-dot<?php echo $item->is_enabled() ? ' is-on' : ''; ?>" data-lzp-status="<?php echo esc_attr( $item->id() ); ?>" aria-hidden="true"></i>
					</a>
				<?php endforeach; ?>
			</nav>

			<div class="lzp-sidebar__foot">
				<span class="lzp-theme-chip<?php echo Theme_Bridge::is_active() ? ' is-ok' : ''; ?>">
					<i aria-hidden="true"></i>
					<?php
					echo Theme_Bridge::is_active()
						? esc_html__( 'Lenz theme detected', 'lenz-plus' )
						: esc_html__( 'Lenz theme not active', 'lenz-plus' );
					?>
				</span>
			</div>
		</aside>

		<main class="lzp-main">
			<?php
			if ( $module ) {
				$module->render_admin();
			} else {
				require __DIR__ . '/dashboard.php';
			}
			?>
		</main>
	</div>

	<div class="lzp-toasts" aria-live="polite" aria-atomic="false"></div>
</div>
