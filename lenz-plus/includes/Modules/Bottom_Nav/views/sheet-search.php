<?php
/**
 * Search form inside the search sheet, plus the live results area when the
 * item has live results turned on (filled by bottom-nav.js).
 *
 * @var \LenzPlus\Modules\Bottom_Nav\Renderer $renderer
 * @var array                                    $sheet    Sheet definition (`item_id`, `live`, `post_type`).
 *
 * @package LenzPlus
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included from a class method, so variables are local.

$field_id   = 'lzp-search-' . wp_unique_id();
$results_id = $field_id . '-results';
?>
<form class="lzp-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>"<?php echo $sheet['live'] ? ' data-lzp-live-search="' . esc_attr( $sheet['item_id'] ) . '"' : ''; ?>>
	<label class="screen-reader-text" for="<?php echo esc_attr( $field_id ); ?>"><?php esc_html_e( 'Search for:', 'lenz-plus' ); ?></label>
	<div class="lzp-search__field">
		<span class="lzp-search__icon" aria-hidden="true"><?php echo $renderer->search_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG. ?></span>
		<input
			type="search"
			class="lzp-search__input"
			id="<?php echo esc_attr( $field_id ); ?>"
			name="s"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			placeholder="<?php esc_attr_e( 'What are you looking for?', 'lenz-plus' ); ?>"
			autocomplete="off"
			enterkeyhint="search"
			<?php echo $sheet['live'] ? 'aria-controls="' . esc_attr( $results_id ) . '"' : ''; ?>
			data-lzp-autofocus
		>
	</div>
	<?php if ( '' !== $sheet['post_type'] ) : ?>
		<input type="hidden" name="post_type" value="<?php echo esc_attr( $sheet['post_type'] ); ?>">
	<?php endif; ?>
	<button type="submit" class="lzp-search__submit"><?php esc_html_e( 'Search', 'lenz-plus' ); ?></button>
</form>
<?php if ( $sheet['live'] ) : ?>
	<div class="lzp-results-area" id="<?php echo esc_attr( $results_id ); ?>" data-lzp-results>
		<div class="lzp-results-state">
			<span class="lzp-results-state__icon" aria-hidden="true"><?php echo $renderer->icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG. ?></span>
			<p class="lzp-results-state__text"><?php esc_html_e( 'Results appear as you type.', 'lenz-plus' ); ?></p>
		</div>
	</div>
	<p class="screen-reader-text" role="status" data-lzp-results-status></p>
	<?php // Shown by bottom-nav.js while the first results load. ?>
	<template data-lzp-results-skeleton>
		<ul class="lzp-results lzp-results--skeleton" aria-hidden="true">
			<?php for ( $row = 0; $row < 3; $row++ ) : ?>
				<li class="lzp-results__item">
					<span class="lzp-results__link">
						<span class="lzp-results__media"></span>
						<span class="lzp-results__text"><span class="lzp-skeleton"></span><span class="lzp-skeleton lzp-skeleton--short"></span></span>
					</span>
				</li>
			<?php endfor; ?>
		</ul>
	</template>
<?php endif; ?>
