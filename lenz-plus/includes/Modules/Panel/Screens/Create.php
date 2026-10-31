<?php
/**
 * "Add" screen behind the + button: big tiles for everything the user may
 * create (project, article, course, product, page, upload).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * Tiles to create content.
 */
final class Create extends Screen {

	/** Screen id. */
	public function id(): string {
		return 'create';
	}

	/** Title. */
	public function title(): string {
		return __( 'Add', 'lenz-plus' );
	}

	/** Icon. */
	public function icon(): string {
		return 'plus';
	}

	/** Every panel user (the tiles check their own capabilities). */
	public function capability(): string {
		return 'read';
	}

	/** Reached from the + button, not the menu. */
	public function in_menu(): bool {
		return false;
	}

	/** Prints the tiles. */
	public function render(): void {
		$tiles = $this->module->app()->create_actions();

		if ( ! $tiles ) {
			Ui::empty_state( __( 'There is nothing you can add.', 'lenz-plus' ) );
			return;
		}

		echo '<ul class="lzp-tiles">';
		foreach ( $tiles as $tile ) {
			printf(
				'<li><a class="lzp-tile" href="%1$s">%2$s<span>%3$s</span></a></li>',
				esc_url( $tile[0] ),
				Ui::icon( $tile[2] ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG.
				esc_html( $tile[1] )
			);
		}
		echo '</ul>';
	}
}
