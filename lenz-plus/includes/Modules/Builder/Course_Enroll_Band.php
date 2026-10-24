<?php
/**
 * Course pages print the request form band (the container whose HTML id
 * is `enroll`) only for courses that register through it. A course sold
 * through the checkout, coming soon, held or full has no use for the form,
 * and showing it beside the cart button offered two ways to register.
 *
 * One route template serves every course, so the band is kept out of
 * Elementor's element cache (it is rendered per request like a dynamic
 * widget); otherwise the first course viewed would decide for all.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Drops the `#enroll` container on courses that do not register by form.
 */
final class Course_Enroll_Band {

	/** Hooks Elementor's cache and render checks. */
	public function register(): void {
		add_filter( 'elementor/element/is_dynamic_content', array( $this, 'is_dynamic' ), 10, 2 );
		add_filter( 'elementor/frontend/container/should_render', array( $this, 'should_render' ), 10, 2 );
	}

	/**
	 * The band is rendered per request, never from the element cache.
	 *
	 * @param bool  $dynamic  Elementor's answer.
	 * @param array $raw_data The element's saved data.
	 */
	public function is_dynamic( $dynamic, $raw_data ): bool {
		return (bool) $dynamic || self::is_band( (array) $raw_data );
	}

	/**
	 * Whether a container is printed.
	 *
	 * @param bool                   $should_render Elementor's answer.
	 * @param \Elementor\Element_Base $element      The container.
	 */
	public function should_render( $should_render, $element ): bool {
		if ( ! $should_render || ! $element instanceof \Elementor\Element_Base || ! self::is_band( $element->get_raw_data() ) ) {
			return (bool) $should_render;
		}

		// The band stays editable in the editor whatever the sample course does.
		if ( Context::is_editor() ) {
			return true;
		}

		$id = Context::current_item( 'product' );
		if ( ! $id || ! Course_Data::is_course( $id ) ) {
			return true;
		}

		return '#' . Course_Data::ENROLL_ID === Course_Data::action( $id )['url'];
	}

	/**
	 * Whether saved element data is the request form band.
	 *
	 * @param array $raw_data Element data.
	 */
	private static function is_band( array $raw_data ): bool {
		return 'container' === ( $raw_data['elType'] ?? '' ) && Course_Data::ENROLL_ID === ( $raw_data['settings']['_element_id'] ?? '' );
	}
}
