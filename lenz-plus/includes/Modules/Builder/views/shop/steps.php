<?php
/**
 * Step bar of the order flow: cart → details and payment → order complete.
 *
 * Required by Shop_Pages::add_steps() with `$steps` (label, url), `$current`
 * (index of the page being viewed) and `$linked` (whether finished steps
 * link back). Numbers come from a CSS counter, Persian on Persian pages.
 *
 * @package LenzPlus
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included from a class method, so variables are local.
?>
<nav class="lzp-steps" aria-label="<?php esc_attr_e( 'Order steps', 'lenz-plus' ); ?>">
	<ol class="lzp-steps__list">
		<?php foreach ( $steps as $lzp_index => $lzp_step ) : ?>
			<?php
			$lzp_state = $lzp_index < $current ? 'done' : ( $lzp_index === $current ? 'current' : 'next' );
			$lzp_link  = 'done' === $lzp_state && $linked && '' !== $lzp_step['url'];
			?>
			<li class="lzp-steps__item is-<?php echo esc_attr( $lzp_state ); ?>"<?php echo 'current' === $lzp_state ? ' aria-current="step"' : ''; ?>>
				<?php if ( $lzp_link ) : ?>
					<a class="lzp-steps__link" href="<?php echo esc_url( $lzp_step['url'] ); ?>">
				<?php else : ?>
					<span class="lzp-steps__link">
				<?php endif; ?>
					<span class="lzp-steps__num" aria-hidden="true"></span>
					<span class="lzp-steps__label"><?php echo esc_html( $lzp_step['label'] ); ?></span>
					<?php if ( 'done' === $lzp_state ) : ?>
						<span class="screen-reader-text"><?php esc_html_e( '(done)', 'lenz-plus' ); ?></span>
					<?php endif; ?>
				<?php echo $lzp_link ? '</a>' : '</span>'; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
