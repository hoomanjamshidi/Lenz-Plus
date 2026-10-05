<?php
/**
 * Bottom navigation bar.
 *
 * @var \LenzPlus\Modules\Bottom_Nav\Renderer $renderer
 * @var array<int, array>                        $items View models.
 *
 * @package LenzPlus
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included from a class method, so variables are local.
?>
<nav id="lzp-bottom-nav" class="<?php echo esc_attr( $renderer->nav_classes() ); ?>" style="<?php echo esc_attr( $renderer->nav_style() ); ?>" data-count="<?php echo count( $items ); ?>" aria-label="<?php esc_attr_e( 'Mobile navigation', 'lenz-plus' ); ?>">
	<div class="lzp-bn__bar" aria-hidden="true"><span class="lzp-bn__indicator"></span></div>
	<ul class="lzp-bn__list">
		<?php foreach ( $items as $index => $item ) : ?>
			<?php $item_tag = $renderer->item_tag( $item ); ?>
			<li class="<?php echo esc_attr( $renderer->item_classes( $item ) ); ?>" style="--lzp-bn-i:<?php echo (int) $index; ?>">
				<<?php echo tag_escape( $item_tag ); ?><?php echo $renderer->item_attributes( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped per attribute. ?>>
					<span class="lzp-bn__icon">
						<span class="lzp-bn__glyph lzp-bn__glyph--base"><?php echo $item['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted/sanitized icon markup. ?></span>
						<?php if ( '' !== $item['icon_active'] ) : ?>
							<span class="lzp-bn__glyph lzp-bn__glyph--active"><?php echo $item['icon_active']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php endif; ?>
						<?php echo $item['badge']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_html(). ?>
					</span>
					<span class="lzp-bn__label"><?php echo esc_html( $item['label'] ); ?></span>
				</<?php echo tag_escape( $item_tag ); ?>>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
<div class="lzp-bn-spacer" aria-hidden="true"></div>
