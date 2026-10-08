<?php
/**
 * Course cards of the courses page, in the three states of the mockups:
 * open courses (16:10 cover, «duration · format» chip, a seats or status
 * badge, title, summary, price and a button), a coming-soon course on ink
 * with a waitlist form, and held courses with a dashed border and a "let me
 * know" button. Chips above filter by status and format: links that filter
 * on the server (`?lzp_course=online`), filtered in place by filter.js.
 *
 * The waitlist form posts to the sign-up list (Newsletter) with the course
 * title as its topic; the list name comes from this widget's settings.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Assets;
use LenzPlus\Modules\Builder\Course_Data;
use LenzPlus\Modules\Builder\Elementor\Picture;
use LenzPlus\Modules\Builder\Newsletter;

defined( 'ABSPATH' ) || exit;

/**
 * The «Course grid (Lenz+)» widget.
 */
final class Course_Grid extends Course_Base {

	/** Query argument of the server-side filter. */
	private const FILTER_ARG = 'lzp_course';

	/** Elementor widget id (Newsletter reads its waitlist settings). */
	public function get_name(): string {
		return 'lzp-course-grid';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Course grid', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-posts-grid';
	}

	/** Filter, waitlist and video scripts. */
	public function get_script_depends(): array {
		return array( Assets::FILTER_HANDLE, Assets::FORMS_HANDLE );
	}

	/** Course and form stylesheets. */
	public function get_style_depends(): array {
		return array_merge( parent::get_style_depends(), array( Assets::FORMS_HANDLE ) );
	}

	/**
	 * Chip filters: slug → label.
	 *
	 * @return array<string, string>
	 */
	private static function filters(): array {
		return array(
			'open'    => __( 'Registration open', 'lenz-plus' ),
			'online'  => __( 'Online', 'lenz-plus' ),
			'offline' => __( 'In person', 'lenz-plus' ),
			'soon'    => __( 'Coming soon', 'lenz-plus' ),
			'archive' => __( 'Archive', 'lenz-plus' ),
		);
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Courses', 'lenz-plus' ) );

		$this->add_control(
			'show',
			array(
				'label'   => __( 'Courses', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'all',
				'options' => array(
					'all'  => __( 'All', 'lenz-plus' ),
					'open' => __( 'Registration open only', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'count',
			array(
				'label'   => __( 'Number of courses', 'lenz-plus' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 12,
				'min'     => 1,
				'max'     => 48,
			)
		);

		$this->add_control(
			'filters',
			array(
				'label'        => __( 'Filter chips', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'     => __( 'Button text', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Details and registration', 'lenz-plus' ),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'waitlist_heading',
			array(
				'label'     => __( 'Coming soon courses', 'lenz-plus' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'waitlist_list',
			array(
				'label'       => __( 'Waitlist', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Course waitlist', 'lenz-plus' ),
				'description' => __( 'Sign-ups from the coming-soon cards join this list, with the course as their topic.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'waitlist_button',
			array(
				'label'   => __( 'Waitlist button', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Add me to the waitlist', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'waitlist_done',
			array(
				'label'   => __( 'Message after joining', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Done. I will let you know as soon as registration opens.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'archive_link',
			array(
				'label'       => __( '"Let me know" link of held courses', 'lenz-plus' ),
				'type'        => Controls_Manager::URL,
				'default'     => array( 'url' => '#waitlist' ),
				'description' => __( 'Usually the sign-up band further down the page.', 'lenz-plus' ),
			)
		);

		$this->add_columns_control( '.lzp-courses', array( 3, 2, 1 ) );
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Cards', 'lenz-plus' ) );
		$this->add_gap_control( '.lzp-courses' );
		$this->add_text_style( 'title', '.lzp-course-card__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the chips and the cards. */
	protected function render(): void {
		$s   = $this->get_settings_for_display();
		$ids = Course_Data::course_ids( max( 1, (int) $s['count'] ), 'open' === $s['show'] ? array( 'open' ) : array() );

		if ( ! $ids ) {
			$this->editor_hint( __( 'No courses yet. Mark WooCommerce products as courses (Course details box on the product editor).', 'lenz-plus' ) );
			return;
		}

		$filters = self::filters();
		$current = isset( $_GET[ self::FILTER_ARG ] ) ? sanitize_key( wp_unslash( $_GET[ self::FILTER_ARG ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a read-only list filter.
		$current = isset( $filters[ $current ] ) ? $current : '';

		echo '<div class="lzp-course-list" data-lzp-filter>';

		if ( 'yes' === $s['filters'] ) {
			$base  = remove_query_arg( self::FILTER_ARG );
			$chips = array();
			foreach ( $filters as $slug => $label ) {
				$chips[ $slug ] = array( $label, add_query_arg( self::FILTER_ARG, $slug, $base ) );
			}

			$bar = self::filter_bar_html(
				array(
					'chips'     => $chips,
					'all_label' => __( 'All', 'lenz-plus' ),
					'all_url'   => $base,
					'current'   => $current,
					'total'     => count( $ids ),
					/* translators: %s: number of courses. */
					'one'       => __( '%s course', 'lenz-plus' ),
					/* translators: %s: number of courses. */
					'many'      => __( '%s courses', 'lenz-plus' ),
					'label'     => __( 'Course filters', 'lenz-plus' ),
				)
			);
			echo $bar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in filter_bar_html().
		}

		echo '<ul class="lzp-courses lzp-grid">';
		foreach ( $ids as $id ) {
			$details = Course_Data::details( $id );
			$cats    = array( $details['status'], $details['format'] );
			$hidden  = '' !== $current && ! in_array( $current, $cats, true );

			printf(
				'<li class="lzp-course-card lzp-course-card--%1$s" data-lzp-cats="%2$s"%3$s>%4$s</li>',
				esc_attr( $details['status'] ),
				esc_attr( implode( ' ', $cats ) ),
				$hidden ? ' hidden' : '',
				'soon' === $details['status'] ? $this->soon_card( $id, $s ) : $this->card( $id, $details, $s ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building.
			);
		}
		echo '</ul></div>';
	}

	/**
	 * An open or held course.
	 *
	 * @param int   $id      Course ID.
	 * @param array $details Course details.
	 * @param array $s       Settings.
	 */
	private function card( int $id, array $details, array $s ): string {
		$title   = get_the_title( $id );
		$url     = (string) get_permalink( $id );
		$archive = 'archive' === $details['status'];
		$summary = wp_strip_all_tags( get_the_excerpt( $id ) );
		$facts   = implode( ' · ', array_filter( array( $details['duration'], Course_Data::format_label( $details ) ) ) );

		$badge = Course_Data::badge( $id );
		$top   = '<div class="lzp-course-card__tags"><span class="lzp-chip">' . esc_html( self::digits( $facts ) ) . '</span>'
			. ( $archive ? '<span class="lzp-course-card__year">' . esc_html( self::digits( $badge ) ) . '</span>' : '<span class="lzp-chip lzp-chip--line">' . esc_html( $badge ) . '</span>' )
			. '</div>';

		if ( $archive ) {
			$foot = '<span class="lzp-course-card__held">' . esc_html__( 'Held', 'lenz-plus' ) . '</span>'
				. Button::markup(
					array(
						'text'    => __( 'Let me know', 'lenz-plus' ),
						'variant' => 'secondary',
						'size'    => 'sm',
						'attrs'   => $this->link_attrs( 'archive-' . $id, $s['archive_link'] ?? array() ),
					)
				);
		} else {
			$price = Course_Data::price_html( $id );
			$foot  = '<span class="lzp-course-card__price">' . $price . '</span>'
				. Button::markup(
					array(
						'text'    => (string) $s['button_text'],
						'url'     => $url,
						'variant' => 'primary',
						'size'    => 'sm',
					)
				);
		}

		return sprintf(
			'<article class="lzp-course-card__box"><a class="lzp-course-card__media" href="%1$s" tabindex="-1" aria-hidden="true">%2$s</a><div class="lzp-course-card__body">%3$s<h3 class="lzp-course-card__title"><a href="%1$s">%4$s</a></h3>%5$s<div class="lzp-course-card__foot">%6$s</div></div></article>',
			esc_url( $url ),
			Picture::frame(
				array( 'id' => (int) get_post_thumbnail_id( $id ) ),
				array(
					'ratio' => '16/10',
					'alt'   => '',
					'class' => 'lzp-photo',
					'sizes' => '(max-width: 767px) 100vw, 33vw',
				)
			),
			$top,
			esc_html( $title ),
			'' !== $summary ? '<p class="lzp-course-card__text">' . esc_html( $summary ) . '</p>' : '',
			$foot
		);
	}

	/**
	 * A coming-soon course: ink card with the waitlist form.
	 *
	 * @param int   $id Course ID.
	 * @param array $s  Settings.
	 */
	private function soon_card( int $id, array $s ): string {
		$form_id = 'lzp-form-' . $this->get_id() . '-' . $id;
		$result  = Newsletter::plain_result( 'lzp_signup', $form_id );
		$summary = wp_strip_all_tags( get_the_excerpt( $id ) );
		$button  = str_replace(
			'<button type="button"',
			'<button type="submit"',
			Button::markup(
				array(
					'text'    => (string) $s['waitlist_button'],
					'variant' => 'primary',
					'size'    => 'md',
					'icon'    => 'arrow-forward',
				)
			)
		);

		$form = sprintf(
			'<form class="lzp-form lzp-form--stack lzp-course-card__waitlist" id="%1$s" action="%2$s" method="post" data-lzp-form%3$s>%4$s<input type="hidden" name="topic" value="%5$s"><div class="lzp-form__fields"><p class="lzp-form__field"><label class="screen-reader-text" for="%1$s-contact">%6$s</label><input class="lzp-form__input" id="%1$s-contact" name="contact" type="text" required maxlength="100" placeholder="%6$s"></p>%7$s</div>%8$s</form>',
			esc_attr( $form_id ),
			esc_url( Newsletter::form_url() ),
			'ok' === $result ? ' data-lzp-sent' : '',
			Newsletter::hidden_fields( Newsletter::ACTION, $this->get_id(), $form_id ),
			esc_attr( get_the_title( $id ) ),
			esc_attr__( 'Email or mobile number', 'lenz-plus' ),
			$button,
			self::form_result_html( $result, (string) $s['waitlist_done'] )
		);

		return sprintf(
			'<article class="lzp-course-card__box lzp-dark"><span class="lzp-chip lzp-course-card__soon">%1$s</span><h3 class="lzp-course-card__title"><a href="%2$s">%3$s</a></h3>%4$s%5$s</article>',
			esc_html( Course_Data::badge( $id ) ),
			esc_url( (string) get_permalink( $id ) ),
			esc_html( get_the_title( $id ) ),
			'' !== $summary ? '<p class="lzp-course-card__text">' . esc_html( $summary ) . '</p>' : '',
			$form
		);
	}
}
