<?php
/**
 * Request context for the widgets: whether they render inside the Elementor
 * editor, where hints are shown and samples stand in for real content.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Static helpers about the current rendering context.
 */
final class Context {

	/** Whether we are inside the Elementor editor or its preview iframe. */
	public static function is_editor(): bool {
		if ( ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance ) ) {
			return false;
		}

		$elementor = \Elementor\Plugin::$instance;

		return ( isset( $elementor->editor ) && $elementor->editor->is_edit_mode() )
			|| ( isset( $elementor->preview ) && $elementor->preview->is_preview_mode() );
	}
}
