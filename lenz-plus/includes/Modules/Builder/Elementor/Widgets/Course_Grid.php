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
 * The «poster» look is the home page's: a title row with «most popular /
 * newest» tabs and an «all courses» button, tall cover cards with the
 * title, number of sessions and students, then the price and a small green
 * pill. The tabs are links (`?lzp_course_sort=new`) that courses.js turns
 * into in-place sorting.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Assets;
use LenzPlus\Modules\Builder\Course_Data;
use LenzPlus\Modules\Builder\Elementor\Picture;
use LenzPlus\Modules\Builder\Newsletter;
use LenzPlus\Modules\Builder\Presets\Blocks;

defined( 'ABSPATH' ) || exit;

/**
 * The «Course grid (Lenz+)» widget.
 */
final class Course_Grid extends Course_Base {

	/** Query argument of the server-side filter. */
	private const FILTER_ARG = 'lzp_course';

	/** Icon of the poster look's green pill, by what it shows. */
	private const PILL_ICONS = array(
		'duration' => 'clock',
		'format'   => 'map-pin',
		'badge'    => 'users',
		'custom'   => 'support',
	);

	/** Query argument of the poster look's sort tabs. */
	private const SORT_ARG = 'lzp_course_sort';

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
		return array( Assets::FILTER_HANDLE, Assets::FORMS_HANDLE, Assets::COURSES_HANDLE );
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
			'look',
			array(
				'label'   => __( 'Look', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'cards',
				'options' => array(
					'cards'  => __( 'Cards (courses page)', 'lenz-plus' ),
					'poster' => __( 'Posters with tabs (home page)', 'lenz-plus' ),
				),
			)
		);

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
				'condition'    => array( 'look' => 'cards' ),
			)
		);

		$this->add_poster_controls();

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
		$this->add_photo_tone_control();
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Cards', 'lenz-plus' ) );
		$this->add_gap_control( '.lzp-courses' );
		$this->add_text_style( 'title', '.lzp-course-card__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Controls of the poster look (title row, tabs, card parts). */
	private function add_poster_controls(): void {
		$poster = array( 'look' => 'poster' );

		$this->add_control(
			'poster_heading',
			array(
				'label'     => __( 'Posters', 'lenz-plus' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => $poster,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Courses', 'lenz-plus' ),
				'label_block' => true,
				'condition'   => $poster,
			)
		);

		$this->add_control(
			'title_muted',
			array(
				'label'       => __( 'Words in the muted colour after the title', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'condition'   => $poster,
			)
		);

		$this->add_control(
			'sort_tabs',
			array(
				'label'        => __( '«Most popular / Newest» tabs', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => $poster,
			)
		);

		$this->add_control(
			'all_text',
			array(
				'label'       => __( '«All courses» button', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'All courses', 'lenz-plus' ),
				'description' => __( 'Empty: no button.', 'lenz-plus' ),
				'condition'   => $poster,
			)
		);

		$this->add_control(
			'all_link',
			array(
				'label'       => __( 'Button link', 'lenz-plus' ),
				'type'        => Controls_Manager::URL,
				'default'     => array( 'url' => '' ),
				'description' => __( 'Empty: the page made from the courses design, else the shop.', 'lenz-plus' ),
				'condition'   => $poster,
			)
		);

		$this->add_control(
			'poster_ratio',
			array(
				'label'     => __( 'Cover shape', 'lenz-plus' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '4/5',
				'options'   => Picture::ratio_options(),
				'condition' => $poster,
			)
		);

		$this->add_control(
			'show_sessions',
			array(
				'label'        => __( 'Number of sessions', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => $poster,
			)
		);

		$this->add_control(
			'show_students',
			array(
				'label'        => __( 'Number of students', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => __( 'Sales counted by WooCommerce.', 'lenz-plus' ),
				'condition'    => $poster,
			)
		);

		$this->add_control(
			'pill',
			array(
				'label'     => __( 'Green pill', 'lenz-plus' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'duration',
				'options'   => array(
					'duration' => __( 'Duration of the course', 'lenz-plus' ),
					'format'   => __( 'Online or in person', 'lenz-plus' ),
					'badge'    => __( 'Seats or registration status', 'lenz-plus' ),
					'custom'   => __( 'Same text on every card', 'lenz-plus' ),
					'none'     => __( 'None', 'lenz-plus' ),
				),
				'condition' => $poster,
			)
		);

		$this->add_control(
			'pill_text',
			array(
				'label'       => __( 'Pill text', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => __( '3 months of support', 'lenz-plus' ),
				'condition'   => array(
					'look' => 'poster',
					'pill' => 'custom',
				),
			)
		);
	}

	/** Prints the chips and the cards. */
	protected function render(): void {
		$s   = $this->get_settings_for_display();
		$ids = Course_Data::course_ids( max( 1, (int) $s['count'] ), 'open' === $s['show'] ? array( 'open' ) : array() );

		if ( ! $ids ) {
			$this->editor_hint( __( 'No courses yet. Mark WooCommerce products as courses (Course details box on the product editor).', 'lenz-plus' ) );
			return;
		}

		if ( 'poster' === ( $s['look'] ?? 'cards' ) ) {
			$this->render_posters( $ids, $s );
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

	/**
	 * The poster look: title row with tabs and the button, then the cards.
	 *
	 * @param int[] $ids Course IDs, newest first.
	 * @param array $s   Settings.
	 */
	private function render_posters( array $ids, array $s ): void {
		$sales = array();
		foreach ( $ids as $id ) {
			$product      = wc_get_product( $id );
			$sales[ $id ] = $product ? (int) $product->get_total_sales() : 0;
		}

		$tabs  = 'yes' === ( $s['sort_tabs'] ?? '' );
		$sort  = $tabs && isset( $_GET[ self::SORT_ARG ] ) && 'new' === sanitize_key( wp_unslash( $_GET[ self::SORT_ARG ] ) ) ? 'new' : ( $tabs ? 'popular' : 'new' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a read-only sort.
		$order = $ids;
		if ( 'popular' === $sort ) {
			// Stable: equal sales keep the newest first.
			usort(
				$order,
				static function ( int $a, int $b ) use ( $sales, $ids ): int {
					return array( $sales[ $b ], array_search( $a, $ids, true ) ) <=> array( $sales[ $a ], array_search( $b, $ids, true ) );
				}
			);
		}

		echo '<div class="lzp-course-list lzp-course-list--poster" data-lzp-course-sort>';
		$end = ( $tabs ? $this->sort_tabs_html( $sort ) : '' ) . $this->all_button_html( $s );
		if ( '' !== trim( (string) $s['title'] ) || '' !== $end ) {
			$heading = Heading::markup(
				array(
					'title' => (string) $s['title'],
					'muted' => (string) ( $s['title_muted'] ?? '' ),
				),
				'' !== $end ? '<div class="lzp-cposter-head__end">' . $end . '</div>' : ''
			);
			echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in markup(), the tabs and the button.
		}

		echo '<ul class="lzp-courses lzp-grid">';
		foreach ( $order as $id ) {
			printf(
				'<li class="lzp-course-card lzp-course-card--poster" data-sales="%1$d" data-new="%2$d">%3$s</li>',
				(int) $sales[ $id ],
				(int) array_search( $id, $ids, true ),
				$this->poster_card( $id, $sales[ $id ], $s ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building.
			);
		}
		echo '</ul></div>';
	}

	/**
	 * The «most popular / newest» tabs: links that sort on the server,
	 * sorted in place by courses.js.
	 *
	 * @param string $current `popular` or `new`.
	 */
	private function sort_tabs_html( string $current ): string {
		$base = remove_query_arg( self::SORT_ARG );
		$tabs = array(
			'popular' => array( __( 'Most popular courses', 'lenz-plus' ), $base ),
			'new'     => array( __( 'Newest courses', 'lenz-plus' ), add_query_arg( self::SORT_ARG, 'new', $base ) ),
		);

		$html = '<nav class="lzp-cposter-tabs" aria-label="' . esc_attr__( 'Sort courses', 'lenz-plus' ) . '">';
		foreach ( $tabs as $key => $tab ) {
			$html .= sprintf(
				'<a class="lzp-cposter-tabs__tab" href="%1$s" data-lzp-sort="%2$s"%3$s>%4$s</a>',
				esc_url( $tab[1] ),
				esc_attr( $key ),
				$key === $current ? ' aria-current="true"' : '',
				esc_html( $tab[0] )
			);
		}

		return $html . '</nav>';
	}

	/**
	 * The «all courses» button ('' without text).
	 *
	 * @param array $s Settings.
	 */
	private function all_button_html( array $s ): string {
		$text = trim( (string) ( $s['all_text'] ?? '' ) );
		if ( '' === $text ) {
			return '';
		}

		$link = (array) ( $s['all_link'] ?? array() );
		if ( empty( $link['url'] ) ) {
			$page = Blocks::design_page_url( 'courses' );
			$link = array( 'url' => '' !== $page ? $page : Blocks::courses_url() );
		}

		return Button::markup(
			array(
				'text'    => $text,
				'variant' => 'primary',
				'size'    => 'sm',
				'attrs'   => $this->link_attrs( 'all', $link ),
			)
		);
	}

	/**
	 * One poster card.
	 *
	 * @param int   $id    Course ID.
	 * @param int   $sales Number of sales (students).
	 * @param array $s     Settings.
	 */
	private function poster_card( int $id, int $sales, array $s ): string {
		$details = Course_Data::details( $id );
		$url     = (string) get_permalink( $id );
		$facts   = array();

		if ( 'yes' === ( $s['show_sessions'] ?? '' ) && $details['curriculum'] ) {
			/* translators: %s: number of sessions. */
			$facts[] = array( 'video', sprintf( __( '%s sessions', 'lenz-plus' ), self::num( count( $details['curriculum'] ) ) ) );
		}
		if ( 'yes' === ( $s['show_students'] ?? '' ) && $sales > 0 ) {
			/* translators: %s: number of students. */
			$facts[] = array( 'users', sprintf( __( '%s students', 'lenz-plus' ), self::num( $sales ) ) );
		}

		$facts_html = '';
		foreach ( $facts as $fact ) {
			$facts_html .= '<li>' . self::icon( $fact[0] ) . '<span>' . esc_html( $fact[1] ) . '</span></li>';
		}

		$price = 'open' === $details['status']
			? '<span class="lzp-cposter__price">' . Course_Data::price_html( $id ) . '</span>'
			: '<span class="lzp-cposter__state">' . esc_html( self::digits( Course_Data::badge( $id ) ) ) . '</span>';
		$pill  = self::poster_pill( $id, $details, $s );

		return sprintf(
			'<article class="lzp-course-card__box lzp-cposter"><div class="lzp-cposter__media">%1$s</div><div class="lzp-cposter__body"><h3 class="lzp-course-card__title"><a href="%2$s">%3$s</a></h3>%4$s<div class="lzp-cposter__foot">%5$s%6$s</div></div></article>',
			Picture::frame(
				array( 'id' => (int) get_post_thumbnail_id( $id ) ),
				array(
					'ratio' => in_array( $s['poster_ratio'] ?? '', Picture::RATIOS, true ) ? $s['poster_ratio'] : '4/5',
					'alt'   => '',
					'class' => 'lzp-photo',
					'sizes' => '(max-width: 767px) 100vw, 25vw',
				)
			),
			esc_url( $url ),
			esc_html( get_the_title( $id ) ),
			'' !== $facts_html ? '<ul class="lzp-cposter__facts">' . $facts_html . '</ul>' : '',
			$price,
			'' !== $pill ? '<span class="lzp-cposter__pill">' . self::icon( self::PILL_ICONS[ $s['pill'] ] ?? 'support' ) . '<span>' . esc_html( self::digits( $pill ) ) . '</span></span>' : ''
		);
	}

	/**
	 * Text of a poster's green pill ('' for none).
	 *
	 * @param int   $id      Course ID.
	 * @param array $details Course details.
	 * @param array $s       Settings.
	 */
	private static function poster_pill( int $id, array $details, array $s ): string {
		switch ( $s['pill'] ?? 'duration' ) {
			case 'duration':
				return (string) $details['duration'];
			case 'format':
				return Course_Data::format_label( $details );
			case 'badge':
				return 'open' === $details['status'] ? Course_Data::badge( $id ) : '';
			case 'custom':
				return trim( (string) ( $s['pill_text'] ?? '' ) );
			default:
				return '';
		}
	}
}
