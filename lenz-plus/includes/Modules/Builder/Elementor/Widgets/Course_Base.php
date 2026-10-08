<?php
/**
 * Base for the course widgets (grid, hero, checklists, curriculum, outcome,
 * instructor, buy box and buy bar). They add the courses stylesheet and
 * script, and read the course being viewed (a sample course while the
 * template is edited) through Course_Data.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use LenzPlus\Modules\Builder\Assets;
use LenzPlus\Modules\Builder\Context;
use LenzPlus\Modules\Builder\Course_Data;

defined( 'ABSPATH' ) || exit;

/**
 * Assets and the course context shared by the course widgets.
 */
abstract class Course_Base extends Section_Base {

	/** Section and course stylesheets. */
	public function get_style_depends(): array {
		return array( Assets::HANDLE, Assets::SECTIONS_HANDLE, Assets::COURSES_HANDLE );
	}

	/** Buy bar and video dialog scripts. */
	public function get_script_depends(): array {
		return array( Assets::COURSES_HANDLE, Assets::VIDEO_HANDLE );
	}

	/** Courses change (seats, cart): never cached by Elementor. */
	protected function is_dynamic_content(): bool {
		return true;
	}

	/** Panel search terms shared by the course widgets. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'course', 'workshop', 'دوره', 'ورکشاپ', 'کارگاه' ) );
	}

	/**
	 * The course a single-course widget shows; prints an editor hint and
	 * returns 0 when there is none.
	 */
	protected function current_course(): int {
		$id = Context::current_item( 'product' );
		if ( $id && Course_Data::is_course( $id ) ) {
			return $id;
		}

		$this->editor_hint( __( 'Shows the course being viewed. Mark a WooCommerce product as a course (Course details box) to see it here.', 'lenz-plus' ) );

		return 0;
	}
}
