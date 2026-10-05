<?php
/**
 * Dashboard: one card per module with an on/off switch.
 *
 * @var \LenzPlus\Admin\Admin                 $admin
 * @var array<string, \LenzPlus\Core\Module> $modules
 *
 * @package LenzPlus
 */

use LenzPlus\Core\Theme_Bridge;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included from a class method, so variables are local.
?>
<section class="lzp-hero">
	<div class="lzp-hero__text">
		<h1><?php esc_html_e( 'Welcome to Lenz Plus', 'lenz-plus' ); ?></h1>
		<p><?php esc_html_e( 'Turn features on or off, then fine-tune each one. Everything follows your Lenz colours and fonts by default.', 'lenz-plus' ); ?></p>
	</div>
	<div class="lzp-hero__art" aria-hidden="true"><?php echo $admin->icon( 'camera' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
</section>

<?php if ( ! Theme_Bridge::is_active() ) : ?>
	<div class="lzp-notice lzp-notice--warning">
		<?php echo $admin->icon( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<p><?php esc_html_e( 'The Lenz theme is not active. Features still work, but they fall back to neutral colours and cannot use Lenz-specific integrations (its mobile menu, icon font and portfolio items).', 'lenz-plus' ); ?></p>
	</div>
<?php endif; ?>

<div class="lzp-module-grid">
	<?php foreach ( $modules as $item ) : ?>
		<article class="lzp-module-card">
			<div class="lzp-module-card__head">
				<span class="lzp-module-card__icon" aria-hidden="true"><?php echo $admin->icon( $item->icon() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<label class="lzp-switch" title="<?php esc_attr_e( 'Enable', 'lenz-plus' ); ?>">
					<input type="checkbox" data-lzp-module-toggle="<?php echo esc_attr( $item->id() ); ?>" <?php checked( $item->is_enabled() ); ?> aria-label="<?php echo esc_attr( sprintf( /* translators: %s: feature name. */ __( 'Enable %s', 'lenz-plus' ), $item->title() ) ); ?>">
					<span class="lzp-switch__track" aria-hidden="true"></span>
				</label>
			</div>
			<h2><?php echo esc_html( $item->title() ); ?></h2>
			<p><?php echo esc_html( $item->description() ); ?></p>
			<a class="lzp-btn lzp-btn--soft" href="<?php echo esc_url( $admin->page_url( $item ) ); ?>">
				<?php esc_html_e( 'Customize', 'lenz-plus' ); ?>
				<?php echo $admin->icon( 'settings' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</article>
	<?php endforeach; ?>

	<article class="lzp-module-card lzp-module-card--soon" aria-disabled="true">
		<div class="lzp-module-card__head">
			<span class="lzp-module-card__icon" aria-hidden="true"><?php echo $admin->icon( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</div>
		<h2><?php esc_html_e( 'More features are on the way', 'lenz-plus' ); ?></h2>
		<p><?php esc_html_e( 'New tools will appear here as they are added to the plugin.', 'lenz-plus' ); ?></p>
	</article>
</div>
