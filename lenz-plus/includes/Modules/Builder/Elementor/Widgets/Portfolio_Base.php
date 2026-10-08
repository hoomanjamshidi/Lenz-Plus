<?php
/**
 * Base for the portfolio widgets (grid, featured projects, project header
 * and gallery). They add the portfolio stylesheet; the grid adds the filter
 * script. Items are Lenz's `portfolio` posts, read through Portfolio_Data.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use LenzPlus\Modules\Builder\Assets;
use LenzPlus\Modules\Builder\Context;
use LenzPlus\Modules\Builder\Portfolio_Data;

defined( 'ABSPATH' ) || exit;

/**
 * Assets and helpers shared by the portfolio widgets.
 */
abstract class Portfolio_Base extends Section_Base {

	/** Section and portfolio stylesheets. */
	public function get_style_depends(): array {
		return array( Assets::HANDLE, Assets::SECTIONS_HANDLE, Assets::PORTFOLIO_HANDLE );
	}

	/** Content comes from portfolio posts: never cached by Elementor. */
	protected function is_dynamic_content(): bool {
		return true;
	}

	/** Panel search terms shared by the portfolio widgets. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'portfolio', 'project', 'نمونه کار', 'پروژه' ) );
	}

	/**
	 * The project a single-item widget shows (see Context::current_item()).
	 * Prints an editor hint and returns 0 when there is none.
	 */
	protected function current_project(): int {
		$id = Context::current_item( Portfolio_Data::POST_TYPE );
		if ( ! $id ) {
			$this->editor_hint( __( 'Shows the project being viewed. Add a portfolio item (Lenz → Portfolios) to see it here.', 'lenz-plus' ) );
		}

		return $id;
	}

	/**
	 * Category chips and the year of an item.
	 *
	 * @param int  $post_id Item ID.
	 * @param int  $limit   Most categories shown (0 = all).
	 * @param bool $year    Whether the year follows.
	 */
	protected static function meta_html( int $post_id, int $limit = 0, bool $year = true ): string {
		$terms = Portfolio_Data::terms( $post_id );
		if ( $limit ) {
			$terms = array_slice( $terms, 0, $limit );
		}

		$html = '';
		foreach ( $terms as $term ) {
			$html .= '<span class="lzp-chip">' . esc_html( $term->name ) . '</span>';
		}

		$the_year = $year ? Portfolio_Data::year( $post_id ) : '';
		if ( '' !== $the_year ) {
			$html .= '<span class="lzp-pmeta__year">' . esc_html( self::digits( $the_year ) ) . '</span>';
		}

		return '' !== $html ? '<div class="lzp-pmeta">' . $html . '</div>' : '';
	}
}
