<?php
/**
 * «Project details (Lenz+)» box on Lenz's portfolio editor: the project
 * type (which project page design it gets), the summary under the title, the year, fact boxes (client, duration, output…), the
 * "what was done" checklist, the client's quote and the "featured" flag.
 * Lenz's own gallery box stays as it is; the project widgets read both.
 *
 * Lists are typed one item per line («کارفرما: برند نُوا» for facts), which
 * keeps the box usable without JavaScript.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Meta box markup and saving.
 */
final class Project_Meta_Box {

	private const NONCE = 'lzp_project_details';

	/** Hooks the box and its saving. */
	public function register(): void {
		add_action( 'add_meta_boxes_' . Portfolio_Data::POST_TYPE, array( $this, 'add' ) );
		add_action( 'save_post_' . Portfolio_Data::POST_TYPE, array( $this, 'save' ), 10, 2 );
	}

	/** Adds the box below the editor. */
	public function add(): void {
		add_meta_box( 'lzp-project-details', __( 'Project details (Lenz+)', 'lenz-plus' ), array( $this, 'render' ), Portfolio_Data::POST_TYPE, 'normal', 'default' );
	}

	/**
	 * Prints the fields.
	 *
	 * @param \WP_Post $post Item being edited.
	 */
	public function render( $post ): void {
		$d     = Portfolio_Data::details( (int) $post->ID );
		$facts = array();
		foreach ( $d['facts'] as $fact ) {
			$facts[] = $fact[0] . ': ' . $fact[1];
		}

		$detected = '' === $d['kind'] ? Portfolio_Data::kind( (int) $post->ID ) : '';
		$kinds    = self::kind_labels();

		wp_nonce_field( self::NONCE, self::NONCE . '_nonce' );
		?>
		<p class="description"><?php esc_html_e( 'Shown by the Lenz+ project widgets (project header, checklist, quote, featured projects). Leave a field empty to hide its part.', 'lenz-plus' ); ?></p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="lzp-project-kind"><?php esc_html_e( 'Project type', 'lenz-plus' ); ?></label></th>
				<td><select id="lzp-project-kind" name="lzp_project[kind]">
					<option value=""><?php echo esc_html( '' !== $detected ? sprintf( /* translators: %s: detected project type. */ __( 'From the gallery (now: %s)', 'lenz-plus' ), $kinds[ $detected ] ) : __( 'From the gallery', 'lenz-plus' ) ); ?></option>
					<?php foreach ( $kinds as $kind => $label ) : ?>
						<option value="<?php echo esc_attr( $kind ); ?>" <?php selected( $d['kind'], $kind ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Each type can have its own project page design (Lenz+ → Page templates → Site pages). From the gallery: no video is a photo project, a gallery that starts with a video is a video project, photos followed by videos are a photo and video project.', 'lenz-plus' ); ?></p></td>
			</tr>
			<tr>
				<th scope="row"><label for="lzp-project-summary"><?php esc_html_e( 'Summary', 'lenz-plus' ); ?></label></th>
				<td><textarea class="large-text" rows="3" id="lzp-project-summary" name="lzp_project[summary]"><?php echo esc_textarea( $d['summary'] ); ?></textarea>
				<p class="description"><?php esc_html_e( 'One or two sentences under the title. Empty: the excerpt.', 'lenz-plus' ); ?></p></td>
			</tr>
			<tr>
				<th scope="row"><label for="lzp-project-year"><?php esc_html_e( 'Year', 'lenz-plus' ); ?></label></th>
				<td><input type="text" class="regular-text" id="lzp-project-year" name="lzp_project[year]" value="<?php echo esc_attr( $d['year'] ); ?>">
				<p class="description"><?php esc_html_e( 'Empty: the year it was published.', 'lenz-plus' ); ?></p></td>
			</tr>
			<tr>
				<th scope="row"><label for="lzp-project-facts"><?php esc_html_e( 'Facts', 'lenz-plus' ); ?></label></th>
				<td><textarea class="large-text" rows="4" id="lzp-project-facts" name="lzp_project[facts]"><?php echo esc_textarea( implode( "\n", $facts ) ); ?></textarea>
				<p class="description"><?php esc_html_e( 'One per line, as "Label: value" (e.g. Client: Nova). The first three also appear on featured project cards.', 'lenz-plus' ); ?></p></td>
			</tr>
			<tr>
				<th scope="row"><label for="lzp-project-done"><?php esc_html_e( 'What was done', 'lenz-plus' ); ?></label></th>
				<td><textarea class="large-text" rows="5" id="lzp-project-done" name="lzp_project[done]"><?php echo esc_textarea( implode( "\n", $d['done'] ) ); ?></textarea>
				<p class="description"><?php esc_html_e( 'One step per line, shown as a checklist.', 'lenz-plus' ); ?></p></td>
			</tr>
			<tr>
				<th scope="row"><label for="lzp-project-quote"><?php esc_html_e( 'Client\'s quote', 'lenz-plus' ); ?></label></th>
				<td><textarea class="large-text" rows="3" id="lzp-project-quote" name="lzp_project[quote]"><?php echo esc_textarea( $d['quote'] ); ?></textarea>
				<p>
					<input type="text" class="regular-text" name="lzp_project[quote_name]" value="<?php echo esc_attr( $d['quote_name'] ); ?>" placeholder="<?php esc_attr_e( 'Name', 'lenz-plus' ); ?>" aria-label="<?php esc_attr_e( 'Name', 'lenz-plus' ); ?>">
					<input type="text" class="regular-text" name="lzp_project[quote_role]" value="<?php echo esc_attr( $d['quote_role'] ); ?>" placeholder="<?php esc_attr_e( 'Role', 'lenz-plus' ); ?>" aria-label="<?php esc_attr_e( 'Role', 'lenz-plus' ); ?>">
				</p></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Featured', 'lenz-plus' ); ?></th>
				<td><label><input type="checkbox" name="lzp_project_featured" value="1" <?php checked( Portfolio_Data::is_featured( (int) $post->ID ) ); ?>> <?php esc_html_e( 'Show it in "Featured projects"', 'lenz-plus' ); ?></label></td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Saves the box (not on autosaves, and only for users who may edit the item).
	 *
	 * @param int      $post_id Item ID.
	 * @param \WP_Post $post    Item.
	 */
	public function save( $post_id, $post ): void {
		$nonce = isset( $_POST[ self::NONCE . '_nonce' ] ) ? sanitize_key( wp_unslash( $_POST[ self::NONCE . '_nonce' ] ) ) : '';

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! wp_verify_nonce( $nonce, self::NONCE ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post ) ) {
			return;
		}

		$raw = isset( $_POST['lzp_project'] ) && is_array( $_POST['lzp_project'] ) ? wp_unslash( $_POST['lzp_project'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each field is sanitized below.
		$get = static function ( string $key ) use ( $raw ): string {
			return isset( $raw[ $key ] ) && is_string( $raw[ $key ] ) ? $raw[ $key ] : '';
		};

		$facts = array();
		foreach ( self::lines( sanitize_textarea_field( $get( 'facts' ) ) ) as $line ) {
			$parts = array_map( 'trim', explode( ':', $line, 2 ) );
			// A line without a colon is a value with no label.
			$facts[] = 2 === count( $parts ) ? $parts : array( '', $parts[0] );
		}

		update_post_meta(
			$post_id,
			Portfolio_Data::META,
			array(
				'kind'       => in_array( $get( 'kind' ), Portfolio_Data::KINDS, true ) ? $get( 'kind' ) : '',
				'summary'    => trim( sanitize_textarea_field( $get( 'summary' ) ) ),
				'year'       => trim( sanitize_text_field( $get( 'year' ) ) ),
				'facts'      => $facts,
				'done'       => self::lines( sanitize_textarea_field( $get( 'done' ) ) ),
				'quote'      => trim( sanitize_textarea_field( $get( 'quote' ) ) ),
				'quote_name' => trim( sanitize_text_field( $get( 'quote_name' ) ) ),
				'quote_role' => trim( sanitize_text_field( $get( 'quote_role' ) ) ),
			)
		);

		if ( isset( $_POST['lzp_project_featured'] ) ) {
			update_post_meta( $post_id, Portfolio_Data::META_FEATURED, '1' );
		} else {
			delete_post_meta( $post_id, Portfolio_Data::META_FEATURED );
		}
	}

	/**
	 * Project type → label.
	 *
	 * @return array<string, string>
	 */
	public static function kind_labels(): array {
		return array(
			'photo' => __( 'Photo project', 'lenz-plus' ),
			'video' => __( 'Video project', 'lenz-plus' ),
			'mixed' => __( 'Photo and video project', 'lenz-plus' ),
		);
	}

	/**
	 * Non-empty trimmed lines.
	 *
	 * @param string $text Text.
	 * @return string[]
	 */
	private static function lines( string $text ): array {
		return array_values( array_filter( array_map( 'trim', preg_split( '/\R/u', $text ) ), 'strlen' ) );
	}
}
