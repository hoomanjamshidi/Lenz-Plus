<?php
/**
 * Bottom sheet (search, cart, menu or custom content).
 *
 * @var \LenzPlus\Modules\Bottom_Nav\Renderer $renderer
 * @var array                                    $item  Item view model.
 * @var array                                    $sheet Sheet definition.
 *
 * @package LenzPlus
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- included from a class method, so variables are local.

$sheet_id = $renderer->sheet_id( $item );
?>
<div class="<?php echo esc_attr( $renderer->sheet_classes( $sheet ) ); ?>" id="<?php echo esc_attr( $sheet_id ); ?>"<?php echo $renderer->sheet_uses_history( $sheet ) ? ' data-lzp-history' : ''; ?> hidden>
	<div class="lzp-sheet__backdrop" data-lzp-close></div>
	<div class="lzp-sheet__panel" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $sheet_id ); ?>-title" tabindex="-1">
		<div class="lzp-sheet__grip" data-lzp-drag aria-hidden="true"><span></span></div>
		<div class="lzp-sheet__header" data-lzp-drag>
			<h2 class="lzp-sheet__title" id="<?php echo esc_attr( $sheet_id ); ?>-title"><?php echo esc_html( $sheet['title'] ); ?></h2>
			<button type="button" class="lzp-sheet__close" data-lzp-close aria-label="<?php esc_attr_e( 'Close', 'lenz-plus' ); ?>">
				<?php echo $renderer->close_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG. ?>
			</button>
		</div>
		<div class="lzp-sheet__body">
			<?php $renderer->sheet_body( $sheet ); ?>
		</div>
	</div>
</div>
