<?php
/**
 * Pages screen: the site's pages. Pages built with Elementor (most pages of
 * a Lenz site) keep their text in Elementor, so the panel edits their title,
 * visibility and cover and links to Elementor for the rest.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Modules\Panel\Editor;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * Pages list and editor.
 */
final class Pages extends Content_Screen {

	/** Screen id. */
	public function id(): string {
		return 'pages';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Pages', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'page';
	}

	/** Settings switch. */
	public function section(): string {
		return 'pages';
	}

	/** WordPress pages. */
	protected function post_type(): string {
		return 'page';
	}

	/** "New" label. */
	protected function new_label(): string {
		return __( 'New page', 'lenz-plus' );
	}

	/** Edit page title. */
	protected function edit_label(): string {
		return __( 'Edit page', 'lenz-plus' );
	}

	/** Empty list. */
	protected function empty_label(): string {
		return __( 'No pages yet.', 'lenz-plus' );
	}

	/**
	 * Pages built with Elementor say so in the list.
	 *
	 * @param \WP_Post $post Page.
	 */
	protected function row( \WP_Post $post ): array {
		$row = parent::row( $post );
		if ( Editor::is_elementor( (int) $post->ID ) ) {
			$row['meta'] .= ' · ' . __( 'Elementor', 'lenz-plus' );
		}

		if ( (int) get_option( 'page_on_front' ) === (int) $post->ID ) {
			$row['pill'] = Ui::pill( 'new', __( 'Home page', 'lenz-plus' ) );
		}

		return $row;
	}
}
