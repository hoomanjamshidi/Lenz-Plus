<?php
/**
 * Live search results, returned by Live_Search and placed under the search
 * field of the search sheet.
 *
 * @var \LenzPlus\Modules\Bottom_Nav\Renderer $renderer
 * @var array                                    $view View data:
 *      `results` (title_html, url, thumb, icon, type, price, date),
 *      `term`, `count` (localized) and `all_url`.
 *
 * @package LenzPlus
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included from a class method, so variables are local.

$title_tags = array( 'mark' => array( 'class' => true ) );
?>
<?php if ( ! $view['results'] ) : ?>
	<div class="lzp-results-state">
		<span class="lzp-results-state__icon" aria-hidden="true"><?php echo $renderer->icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG. ?></span>
		<p class="lzp-results-state__title">
			<?php
			/* translators: %s: search term. */
			echo esc_html( sprintf( __( 'Nothing found for “%s”.', 'lenz-plus' ), $view['term'] ) );
			?>
		</p>
		<p class="lzp-results-state__text"><?php esc_html_e( 'Check the spelling or try a shorter word.', 'lenz-plus' ); ?></p>
	</div>
<?php else : ?>
	<div class="lzp-results__head">
		<span class="lzp-results__heading"><?php esc_html_e( 'Results', 'lenz-plus' ); ?></span>
		<span class="lzp-results__count"><?php echo esc_html( $view['count'] ); ?></span>
	</div>
	<ul class="lzp-results">
		<?php foreach ( $view['results'] as $index => $result ) : ?>
			<li class="lzp-results__item" style="--lzp-i:<?php echo (int) $index; ?>">
				<a class="lzp-results__link" href="<?php echo esc_url( $result['url'] ); ?>">
					<span class="lzp-results__media<?php echo '' === $result['thumb'] ? ' is-placeholder' : ''; ?>" aria-hidden="true">
						<?php echo '' !== $result['thumb'] ? $result['thumb'] : $renderer->icon( $result['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core thumbnail markup or bundled SVG. ?>
					</span>
					<span class="lzp-results__text">
						<span class="lzp-results__title"><?php echo wp_kses( $result['title_html'], $title_tags ); ?></span>
						<?php if ( '' !== $result['type'] || '' !== $result['price'] || '' !== $result['date'] ) : ?>
							<span class="lzp-results__meta">
								<?php if ( '' !== $result['type'] ) : ?>
									<span class="lzp-results__type"><?php echo esc_html( $result['type'] ); ?></span>
								<?php endif; ?>
								<?php if ( '' !== $result['date'] ) : ?>
									<span class="lzp-results__date"><?php echo esc_html( $result['date'] ); ?></span>
								<?php endif; ?>
								<?php if ( '' !== $result['price'] ) : ?>
									<span class="lzp-results__price"><?php echo wp_kses_post( $result['price'] ); ?></span>
								<?php endif; ?>
							</span>
						<?php endif; ?>
					</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<a class="lzp-results__all" href="<?php echo esc_url( $view['all_url'] ); ?>"><?php esc_html_e( 'See all results', 'lenz-plus' ); ?></a>
<?php endif; ?>
