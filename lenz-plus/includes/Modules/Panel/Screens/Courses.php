<?php
/**
 * Courses screen: products flagged as courses, with the course details of
 * the Builder's course pages (status, format, schedule, seats, registration,
 * instructor, lists and curriculum).
 *
 * Details go through Course_Meta_Box::clean(), the same cleaning as the box
 * on WooCommerce's product editor; a new course is a virtual simple product
 * with the course flag.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Core\Persian;
use LenzPlus\Modules\Builder\Course_Data;
use LenzPlus\Modules\Builder\Course_Meta_Box;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * Courses list and editor.
 */
final class Courses extends Products {

	/** Screen id. */
	public function id(): string {
		return 'courses';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Courses', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'graduation';
	}

	/** Settings switch. */
	public function section(): string {
		return 'courses';
	}

	/** "New" label. */
	protected function new_label(): string {
		return __( 'New course', 'lenz-plus' );
	}

	/** Edit page title. */
	protected function edit_label(): string {
		return __( 'Edit course', 'lenz-plus' );
	}

	/** Empty list. */
	protected function empty_label(): string {
		return __( 'No courses yet. Add one with the + button.', 'lenz-plus' );
	}

	/** Main text label. */
	protected function content_label(): string {
		return __( 'About the course', 'lenz-plus' );
	}

	/** Lists courses. */
	protected function lists_courses(): bool {
		return true;
	}

	/**
	 * Only courses can be opened here (other products have their own screen).
	 */
	protected function edited_post(): ?\WP_Post {
		$post = parent::edited_post();

		return $post && Course_Data::is_course( (int) $post->ID ) ? $post : null;
	}

	/**
	 * Rows show the course status.
	 *
	 * @param \WP_Post $post Course.
	 */
	protected function row( \WP_Post $post ): array {
		$row    = parent::row( $post );
		$status = Course_Data::details( (int) $post->ID )['status'];
		if ( 'publish' === $post->post_status ) {
			$row['pill'] = Ui::pill( 'open' === $status ? 'publish' : ( 'soon' === $status ? 'pending' : 'draft' ), self::statuses()[ $status ] ?? '' );
		}

		return $row;
	}

	/**
	 * Course status → label.
	 *
	 * @return array<string, string>
	 */
	private static function statuses(): array {
		return array(
			'open'    => __( 'Registration open', 'lenz-plus' ),
			'soon'    => __( 'Coming soon (waitlist)', 'lenz-plus' ),
			'archive' => __( 'Held (archive)', 'lenz-plus' ),
		);
	}

	/**
	 * Course details before the price and gallery.
	 *
	 * @param \WP_Post|null $post Course, null for a new one.
	 */
	protected function render_fields( ?\WP_Post $post ): void {
		$d = Course_Data::details( $post ? (int) $post->ID : 0 );

		$pairs = static function ( array $rows ): string {
			return implode(
				"\n",
				array_map(
					static function ( $row ) {
						return implode( ' | ', (array) $row );
					},
					$rows
				)
			);
		};
		?>
		<section class="lzp-card">
			<h2 class="lzp-card__title"><?php esc_html_e( 'Course', 'lenz-plus' ); ?></h2>
			<?php Ui::choices( 'lzp_course[status]', __( 'Status', 'lenz-plus' ), self::statuses(), $d['status'] ); ?>
			<div class="lzp-fields-2">
				<?php
				Ui::select(
					'lzp_course[format]',
					__( 'Format', 'lenz-plus' ),
					array(
						'offline' => __( 'In person', 'lenz-plus' ),
						'online'  => __( 'Online', 'lenz-plus' ),
					),
					$d['format']
				);
				Ui::input( 'lzp_course[place]', __( 'City or venue', 'lenz-plus' ), $d['place'] );
				Ui::input( 'lzp_course[schedule]', __( 'Date and time', 'lenz-plus' ), $d['schedule'], array( 'placeholder' => __( 'e.g. Friday 25 Mehr 1405 · 9 to 13', 'lenz-plus' ) ) );
				Ui::input( 'lzp_course[duration]', __( 'Duration', 'lenz-plus' ), $d['duration'], array( 'placeholder' => __( 'e.g. 3 to 4 hours', 'lenz-plus' ) ) );
				Ui::input( 'lzp_course[capacity]', __( 'Capacity', 'lenz-plus' ), $d['capacity'] ? (string) $d['capacity'] : '', array( 'inputmode' => 'numeric' ) );
				Ui::input(
					'lzp_course[seats_left]',
					__( 'Seats left', 'lenz-plus' ),
					(string) $d['seats_left'],
					array(
						'inputmode' => 'numeric',
						'help'      => __( 'When the stock is counted, the stock is the seats left.', 'lenz-plus' ),
					)
				);
				?>
			</div>
			<?php
			Ui::choices(
				'lzp_course[registration]',
				__( 'Registration', 'lenz-plus' ),
				array(
					'cart' => __( 'Pay online (cart and checkout)', 'lenz-plus' ),
					'form' => __( 'Request form on the page (#enroll)', 'lenz-plus' ),
				),
				$d['registration']
			);
			Ui::select( 'lzp_course[expert]', __( 'Instructor', 'lenz-plus' ), self::experts(), (string) $d['expert'] );
			?>
			<details class="lzp-more">
				<summary><?php esc_html_e( 'More course details', 'lenz-plus' ); ?></summary>
				<div class="lzp-fields-2">
					<?php
					Ui::input( 'lzp_course[kind]', __( 'Type badge', 'lenz-plus' ), $d['kind'], array( 'placeholder' => __( 'e.g. Hands-on workshop', 'lenz-plus' ) ) );
					Ui::input( 'lzp_course[topic]', __( 'Topic', 'lenz-plus' ), $d['topic'] );
					Ui::input( 'lzp_course[level]', __( 'Level', 'lenz-plus' ), $d['level'], array( 'placeholder' => __( 'e.g. Beginner', 'lenz-plus' ) ) );
					Ui::input( 'lzp_course[tools]', __( 'Tools', 'lenz-plus' ), $d['tools'] );
					Ui::input( 'lzp_course[year]', __( 'Year (archive)', 'lenz-plus' ), $d['year'] );
					Ui::input(
						'lzp_course[video]',
						__( 'Intro video', 'lenz-plus' ),
						$d['video'],
						array(
							'type'        => 'url',
							'dir'         => 'ltr',
							'placeholder' => 'https://…/intro.mp4',
						)
					);
					?>
				</div>
				<?php
				Ui::textarea( 'lzp_course[learn]', __( 'What you will learn', 'lenz-plus' ), implode( "\n", $d['learn'] ), array( 'help' => __( 'One per line.', 'lenz-plus' ) ) );
				Ui::textarea( 'lzp_course[audience]', __( 'Who it is for', 'lenz-plus' ), implode( "\n", $d['audience'] ), array( 'help' => __( 'One per line.', 'lenz-plus' ) ) );
				Ui::input( 'lzp_course[audience_note]', __( 'Note under "Who it is for"', 'lenz-plus' ), $d['audience_note'] );
				Ui::textarea( 'lzp_course[curriculum]', __( 'Curriculum', 'lenz-plus' ), $pairs( $d['curriculum'] ), array( 'help' => __( 'One session per line, as "Title | what it covers".', 'lenz-plus' ) ) );
				Ui::textarea( 'lzp_course[outcome]', __( 'Outcome', 'lenz-plus' ), $d['outcome'], array( 'rows' => 3 ) );
				Ui::textarea( 'lzp_course[specs]', __( 'More specs', 'lenz-plus' ), $pairs( $d['specs'] ), array( 'help' => __( 'One per line, as "Label | value" (e.g. Photo critique | In the last part).', 'lenz-plus' ) ) );
				Ui::textarea( 'lzp_course[faq]', __( 'Questions', 'lenz-plus' ), $pairs( $d['faq'] ), array( 'help' => __( 'One per line, as "Question | answer".', 'lenz-plus' ) ) );
				?>
			</details>
		</section>
		<?php
		parent::render_fields( $post );
	}

	/**
	 * Courses count seats, not shipping: no "nothing to ship" switch.
	 *
	 * @param \WC_Product|null $product Course, null for a new one.
	 */
	protected function render_stock_fields( $product ): void {
		Ui::toggle( 'manage_stock', __( 'Count the seats as stock', 'lenz-plus' ), $product && $product->get_manage_stock() );
		Ui::input( 'stock', __( 'Seats in stock', 'lenz-plus' ), $product && null !== $product->get_stock_quantity() ? (string) $product->get_stock_quantity() : '', array( 'inputmode' => 'numeric' ) );
		echo '<input type="hidden" name="virtual" value="1">';
	}

	/**
	 * Lenz's experts for the instructor list.
	 *
	 * @return array<string, string> ID => name.
	 */
	private static function experts(): array {
		$experts = array( '0' => __( '— None —', 'lenz-plus' ) );
		if ( ! post_type_exists( 'expert' ) ) {
			return $experts;
		}

		$posts = get_posts(
			array(
				'post_type'      => 'expert',
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		foreach ( $posts as $expert ) {
			$experts[ (string) $expert->ID ] = get_the_title( $expert );
		}

		return $experts;
	}

	/**
	 * Courses ship nothing (the checkout's short form applies).
	 *
	 * @param \WC_Product $product Course.
	 */
	protected function prepare_product( $product ): void {
		$product->set_virtual( true );
	}

	/**
	 * Saves the course flag and details, then the gallery.
	 *
	 * @param int $id Course ID.
	 */
	protected function save_fields( int $id ): void {
		update_post_meta( $id, Course_Data::META_FLAG, '1' );
		$raw = self::posted_array( 'lzp_course' );
		foreach ( array( 'capacity', 'seats_left' ) as $number ) {
			// Seat counts typed with a Persian keyboard.
			if ( isset( $raw[ $number ] ) && is_string( $raw[ $number ] ) ) {
				$raw[ $number ] = Persian::latin_digits( $raw[ $number ] );
			}
		}

		update_post_meta( $id, Course_Data::META, Course_Meta_Box::clean( $raw ) );

		parent::save_fields( $id );
	}
}
