<?php
/**
 * «Course details (Lenz+)» box on WooCommerce's product editor. Ticking
 * "This product is a course" turns the product into a course: the course
 * template shows it (Page templates → Site pages → Course page) and the
 * course grids list it. Everything else here fills the course widgets.
 *
 * Lists are typed one item per line, pairs as «label | value», which keeps
 * the box usable without JavaScript.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Meta box markup and saving.
 */
final class Course_Meta_Box {

	private const NONCE = 'lzp_course_details';

	/** Hooks the box and its saving. */
	public function register(): void {
		add_action( 'add_meta_boxes_product', array( $this, 'add' ) );
		add_action( 'save_post_product', array( $this, 'save' ), 10, 2 );
	}

	/** Adds the box below the product data. */
	public function add(): void {
		add_meta_box( 'lzp-course-details', __( 'Course details (Lenz+)', 'lenz-plus' ), array( $this, 'render' ), 'product', 'normal', 'default' );
	}

	/**
	 * Prints the fields.
	 *
	 * @param \WP_Post $post Product being edited.
	 */
	public function render( $post ): void {
		$id = (int) $post->ID;
		$d  = Course_Data::details( $id );

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

		$experts = get_posts(
			array(
				'post_type'      => 'expert',
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		wp_nonce_field( self::NONCE, self::NONCE . '_nonce' );
		?>
		<p><label><input type="checkbox" name="lzp_is_course" value="1" <?php checked( Course_Data::is_course( $id ) ); ?>> <strong><?php esc_html_e( 'This product is a course', 'lenz-plus' ); ?></strong></label></p>
		<p class="description"><?php esc_html_e( 'Courses use the Lenz+ course page and appear in the course grids. Other products keep Lenz\'s product page.', 'lenz-plus' ); ?></p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Status', 'lenz-plus' ); ?></th>
				<td>
					<?php
					$statuses = array(
						'open'    => __( 'Registration open', 'lenz-plus' ),
						'soon'    => __( 'Coming soon (waitlist)', 'lenz-plus' ),
						'archive' => __( 'Held (archive)', 'lenz-plus' ),
					);
					foreach ( $statuses as $value => $label ) {
						printf( '<label style="margin-inline-end:16px"><input type="radio" name="lzp_course[status]" value="%1$s" %2$s> %3$s</label>', esc_attr( $value ), checked( $d['status'], $value, false ), esc_html( $label ) );
					}
					?>
				</td>
			</tr>
			<?php
			$this->text_row( 'kind', __( 'Type badge', 'lenz-plus' ), $d['kind'], __( 'e.g. Hands-on workshop', 'lenz-plus' ) );
			$this->text_row( 'topic', __( 'Topic', 'lenz-plus' ), $d['topic'], __( 'e.g. Mobile photography', 'lenz-plus' ) );
			$this->text_row( 'level', __( 'Level', 'lenz-plus' ), $d['level'], __( 'e.g. Beginner', 'lenz-plus' ) );
			?>
			<tr>
				<th scope="row"><?php esc_html_e( 'Format', 'lenz-plus' ); ?></th>
				<td>
					<select name="lzp_course[format]">
						<option value="offline" <?php selected( $d['format'], 'offline' ); ?>><?php esc_html_e( 'In person', 'lenz-plus' ); ?></option>
						<option value="online" <?php selected( $d['format'], 'online' ); ?>><?php esc_html_e( 'Online', 'lenz-plus' ); ?></option>
					</select>
					<input type="text" name="lzp_course[place]" value="<?php echo esc_attr( $d['place'] ); ?>" placeholder="<?php esc_attr_e( 'City or venue', 'lenz-plus' ); ?>" aria-label="<?php esc_attr_e( 'City or venue', 'lenz-plus' ); ?>">
				</td>
			</tr>
			<?php
			$this->text_row( 'duration', __( 'Duration', 'lenz-plus' ), $d['duration'], __( 'e.g. 3 to 4 hours', 'lenz-plus' ) );
			$this->text_row( 'schedule', __( 'Date and time', 'lenz-plus' ), $d['schedule'], __( 'e.g. Friday 25 Mehr 1405 · 9 to 13', 'lenz-plus' ) );
			$this->text_row( 'year', __( 'Year (archive)', 'lenz-plus' ), $d['year'], __( 'Shown on held courses, e.g. Course of 1404', 'lenz-plus' ) );
			$this->text_row( 'tools', __( 'Tools', 'lenz-plus' ), $d['tools'], __( 'e.g. Mobile phone', 'lenz-plus' ) );
			?>
			<tr>
				<th scope="row"><label for="lzp-course-capacity"><?php esc_html_e( 'Capacity', 'lenz-plus' ); ?></label></th>
				<td>
					<input type="number" min="0" id="lzp-course-capacity" name="lzp_course[capacity]" value="<?php echo esc_attr( (string) $d['capacity'] ); ?>" style="width:90px">
					<label><?php esc_html_e( 'Seats left', 'lenz-plus' ); ?> <input type="number" min="0" name="lzp_course[seats_left]" value="<?php echo esc_attr( (string) $d['seats_left'] ); ?>" style="width:90px"></label>
					<p class="description"><?php esc_html_e( 'When the product manages stock (Inventory tab), its stock is the number of seats left.', 'lenz-plus' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Registration', 'lenz-plus' ); ?></th>
				<td>
					<label style="margin-inline-end:16px"><input type="radio" name="lzp_course[registration]" value="cart" <?php checked( $d['registration'], 'cart' ); ?>> <?php esc_html_e( 'Pay online (cart and checkout)', 'lenz-plus' ); ?></label>
					<label><input type="radio" name="lzp_course[registration]" value="form" <?php checked( $d['registration'], 'form' ); ?>> <?php esc_html_e( 'Request form on the page (#enroll)', 'lenz-plus' ); ?></label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="lzp-course-expert"><?php esc_html_e( 'Instructor', 'lenz-plus' ); ?></label></th>
				<td>
					<select id="lzp-course-expert" name="lzp_course[expert]">
						<option value="0"><?php esc_html_e( '— None —', 'lenz-plus' ); ?></option>
						<?php foreach ( $experts as $expert ) : ?>
							<option value="<?php echo esc_attr( (string) $expert->ID ); ?>" <?php selected( (int) $d['expert'], (int) $expert->ID ); ?>><?php echo esc_html( get_the_title( $expert ) ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'From Lenz\'s experts (Experts menu): name, position, photo and text.', 'lenz-plus' ); ?></p>
				</td>
			</tr>
			<?php
			$this->text_row( 'video', __( 'Intro video', 'lenz-plus' ), $d['video'], 'https://…/intro.mp4', 'url' );
			$this->area_row( 'specs', __( 'More specs', 'lenz-plus' ), $pairs( $d['specs'] ), __( 'One per line, as "Label | value" (e.g. Photo critique | In the last part).', 'lenz-plus' ) );
			$this->area_row( 'learn', __( 'What you will learn', 'lenz-plus' ), implode( "\n", $d['learn'] ), __( 'One per line.', 'lenz-plus' ) );
			$this->area_row( 'audience', __( 'Who it is for', 'lenz-plus' ), implode( "\n", $d['audience'] ), __( 'One per line.', 'lenz-plus' ) );
			$this->text_row( 'audience_note', __( 'Note under "Who it is for"', 'lenz-plus' ), $d['audience_note'], '' );
			$this->area_row( 'curriculum', __( 'Curriculum', 'lenz-plus' ), $pairs( $d['curriculum'] ), __( 'One session per line, as "Title | what it covers".', 'lenz-plus' ) );
			$this->area_row( 'outcome', __( 'Outcome', 'lenz-plus' ), $d['outcome'], __( 'What participants can do at the end.', 'lenz-plus' ) );
			$this->area_row( 'faq', __( 'Questions', 'lenz-plus' ), $pairs( $d['faq'] ), __( 'One per line, as "Question | answer".', 'lenz-plus' ) );
			?>
		</table>
		<?php
	}

	/**
	 * A one-line field row.
	 *
	 * @param string $key         Field key.
	 * @param string $label       Label.
	 * @param string $value       Value.
	 * @param string $placeholder Placeholder.
	 * @param string $type        Input type.
	 */
	private function text_row( string $key, string $label, string $value, string $placeholder, string $type = 'text' ): void {
		printf(
			'<tr><th scope="row"><label for="lzp-course-%1$s">%2$s</label></th><td><input type="%5$s" class="regular-text" id="lzp-course-%1$s" name="lzp_course[%1$s]" value="%3$s" placeholder="%4$s"></td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( $value ),
			esc_attr( $placeholder ),
			esc_attr( $type )
		);
	}

	/**
	 * A multi-line field row.
	 *
	 * @param string $key   Field key.
	 * @param string $label Label.
	 * @param string $value Value.
	 * @param string $help  Help text.
	 */
	private function area_row( string $key, string $label, string $value, string $help ): void {
		printf(
			'<tr><th scope="row"><label for="lzp-course-%1$s">%2$s</label></th><td><textarea class="large-text" rows="4" id="lzp-course-%1$s" name="lzp_course[%1$s]">%3$s</textarea><p class="description">%4$s</p></td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_textarea( $value ),
			esc_html( $help )
		);
	}

	/**
	 * Saves the box (not on autosaves, and only for users who may edit the product).
	 *
	 * @param int      $post_id Product ID.
	 * @param \WP_Post $post    Product.
	 */
	public function save( $post_id, $post ): void {
		$nonce = isset( $_POST[ self::NONCE . '_nonce' ] ) ? sanitize_key( wp_unslash( $_POST[ self::NONCE . '_nonce' ] ) ) : '';

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! wp_verify_nonce( $nonce, self::NONCE ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post ) ) {
			return;
		}

		$raw = isset( $_POST['lzp_course'] ) && is_array( $_POST['lzp_course'] ) ? wp_unslash( $_POST['lzp_course'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each field is sanitized in clean().

		update_post_meta( $post_id, Course_Data::META, self::clean( $raw ) );

		if ( isset( $_POST['lzp_is_course'] ) ) {
			update_post_meta( $post_id, Course_Data::META_FLAG, '1' );
		} else {
			delete_post_meta( $post_id, Course_Data::META_FLAG );
		}
	}

	/**
	 * Sanitized course details from the posted fields.
	 *
	 * @param array $raw Posted fields.
	 */
	public static function clean( array $raw ): array {
		$get = static function ( string $key ) use ( $raw ): string {
			return isset( $raw[ $key ] ) && is_scalar( $raw[ $key ] ) ? (string) $raw[ $key ] : '';
		};

		$text = static function ( string $key ) use ( $get ): string {
			return trim( sanitize_text_field( $get( $key ) ) );
		};

		$lines = static function ( string $key ) use ( $get ): array {
			return array_values( array_filter( array_map( 'trim', preg_split( '/\R/u', sanitize_textarea_field( $get( $key ) ) ) ), 'strlen' ) );
		};

		$pairs = static function ( string $key ) use ( $lines ): array {
			return array_map(
				static function ( string $line ): array {
					$parts = array_map( 'trim', explode( '|', $line, 2 ) );

					return array( $parts[0], $parts[1] ?? '' );
				},
				$lines( $key )
			);
		};

		$pick = static function ( string $value, array $allowed ): string {
			return in_array( $value, $allowed, true ) ? $value : $allowed[0];
		};

		$seats = $get( 'seats_left' );

		return array(
			'status'        => $pick( $get( 'status' ), Course_Data::STATUSES ),
			'kind'          => $text( 'kind' ),
			'topic'         => $text( 'topic' ),
			'level'         => $text( 'level' ),
			'format'        => $pick( $get( 'format' ), Course_Data::FORMATS ),
			'place'         => $text( 'place' ),
			'duration'      => $text( 'duration' ),
			'schedule'      => $text( 'schedule' ),
			'year'          => $text( 'year' ),
			'capacity'      => absint( $get( 'capacity' ) ),
			'seats_left'    => '' === trim( $seats ) ? '' : absint( $seats ),
			'tools'         => $text( 'tools' ),
			'specs'         => $pairs( 'specs' ),
			'learn'         => $lines( 'learn' ),
			'audience'      => $lines( 'audience' ),
			'audience_note' => $text( 'audience_note' ),
			'curriculum'    => $pairs( 'curriculum' ),
			'outcome'       => trim( sanitize_textarea_field( $get( 'outcome' ) ) ),
			'faq'           => $pairs( 'faq' ),
			'expert'        => absint( $get( 'expert' ) ),
			'registration'  => $pick( $get( 'registration' ), Course_Data::REGISTRATIONS ),
			'video'         => esc_url_raw( $get( 'video' ) ),
		);
	}
}
