<?php
/**
 * Photo and video uploads of the panel, and the media field that holds them.
 *
 * A media field is a list of attachment IDs (hidden inputs) with a file input
 * to add more. Without JavaScript the files travel with the form and are
 * added when it is saved, and the remove buttons are checkboxes; panel.js
 * uploads each file as soon as it is chosen (admin-ajax, with a preview and
 * progress), so a slow phone connection does not hold the whole form.
 *
 * With JavaScript a field can also take files already in WordPress's media
 * library («From the library», the `lzp_panel_library` answer), and a field
 * with `links` takes video links kept off the site's host (Aparat, or a file
 * on a download host; Media_Link): their hidden input holds the URL instead
 * of an ID, so one ordered list mixes both.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel;

use LenzPlus\Modules\Builder\Media_Link;

defined( 'ABSPATH' ) || exit;

/**
 * Upload handling (form and AJAX) and the media field markup.
 */
final class Uploads {

	/** AJAX action and nonce action of background uploads. */
	public const AJAX_ACTION = 'lzp_panel_upload';

	/** AJAX action of the media library picker (same nonce as uploads). */
	public const LIBRARY_ACTION = 'lzp_panel_library';

	/** Files per page of the library picker. */
	private const LIBRARY_PAGE = 24;

	/** MIME families the panel accepts. */
	private const FAMILIES = array( 'image', 'video' );

	/** Hooks the background upload and the library picker. */
	public function register(): void {
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'ajax_upload' ) );
		add_action( 'wp_ajax_' . self::LIBRARY_ACTION, array( $this, 'ajax_library' ) );
	}

	/**
	 * One page of the media library: every photo and video on the site, as
	 * WordPress's own media screen lists them to people who may upload.
	 */
	public function ajax_library(): void {
		check_ajax_referer( self::AJAX_ACTION, 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to use the media library.', 'lenz-plus' ) ), 403 );
		}

		$type   = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '';
		$search = isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '';
		$page   = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;

		$query = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => in_array( $type, self::FAMILIES, true ) ? $type : self::FAMILIES,
				's'              => $search,
				'posts_per_page' => self::LIBRARY_PAGE,
				'paged'          => $page,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => false,
			)
		);

		wp_send_json_success(
			array(
				'items' => array_map(
					static function ( \WP_Post $attachment ): array {
						return self::attachment( (int) $attachment->ID );
					},
					$query->posts
				),
				'more'  => $page < (int) $query->max_num_pages,
			)
		);
	}

	/** Stores one uploaded file and answers with its ID and preview. */
	public function ajax_upload(): void {
		check_ajax_referer( self::AJAX_ACTION, 'nonce' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to upload files.', 'lenz-plus' ) ), 403 );
		}

		$ids = self::from_files( 'file' );
		if ( ! $ids ) {
			wp_send_json_error( array( 'message' => __( 'The file could not be uploaded. Use a photo or a video.', 'lenz-plus' ) ), 400 );
		}

		wp_send_json_success( self::attachment( $ids[0] ) );
	}

	/**
	 * Stores the files posted under a field (one or several) as attachments.
	 *
	 * @param string $field   File input name.
	 * @param int    $parent_id Post the files belong to (0 = none).
	 * @return int[] New attachment IDs.
	 */
	public static function from_files( string $field, int $parent_id = 0 ): array {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- callers verified the nonce.
		if ( empty( $_FILES[ $field ]['name'] ) || ! current_user_can( 'upload_files' ) ) {
			return array();
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput -- file arrays go to media_handle_upload(), which checks them.
		$files = self::split( $_FILES[ $field ] );
		$ids   = array();

		foreach ( $files as $index => $file ) {
			$type = wp_check_filetype( (string) $file['name'] )['type'];
			if ( UPLOAD_ERR_OK !== (int) $file['error'] || ! $type || ! in_array( strtok( $type, '/' ), self::FAMILIES, true ) ) {
				continue;
			}

			$key            = 'lzp_upload_' . $index;
			$_FILES[ $key ] = $file;
			$id             = media_handle_upload( $key, $parent_id );
			unset( $_FILES[ $key ] );

			if ( ! is_wp_error( $id ) ) {
				$ids[] = (int) $id;
			}
		}

		return $ids;
	}

	/**
	 * One file array per file, whether the input took one or several.
	 *
	 * @param array $files A `$_FILES` entry.
	 * @return array[]
	 */
	private static function split( array $files ): array {
		if ( ! is_array( $files['name'] ) ) {
			return array( $files );
		}

		$split = array();
		foreach ( array_keys( $files['name'] ) as $i ) {
			$split[] = array(
				'name'     => $files['name'][ $i ],
				'type'     => $files['type'][ $i ],
				'tmp_name' => $files['tmp_name'][ $i ],
				'error'    => $files['error'][ $i ],
				'size'     => $files['size'][ $i ],
			);
		}

		return $split;
	}

	/**
	 * Preview data of an attachment.
	 *
	 * @param int $id Attachment ID.
	 * @return array{id:int, thumb:string, url:string, type:string, name:string}
	 */
	public static function attachment( int $id ): array {
		$is_video = 0 === strpos( (string) get_post_mime_type( $id ), 'video' );
		$thumb    = $is_video ? '' : (string) wp_get_attachment_image_url( $id, 'medium' );

		return array(
			'id'    => $id,
			'thumb' => $thumb,
			'url'   => (string) wp_get_attachment_url( $id ),
			'type'  => $is_video ? 'video' : 'image',
			'name'  => get_the_title( $id ),
		);
	}

	/**
	 * Preview data of a link (the `id` is the link itself).
	 *
	 * @param string $url Media_Link URL.
	 * @return array{id:string, thumb:string, url:string, type:string, name:string}
	 */
	public static function link( string $url ): array {
		$type = Media_Link::type_of( $url );

		return array(
			'id'    => $url,
			'thumb' => 'image' === $type ? $url : '',
			'url'   => $url,
			'type'  => $type,
			'name'  => Media_Link::label( $url ),
		);
	}

	/**
	 * The IDs a media field holds after a save: the kept ones (not marked for
	 * removal) in their order, then files sent with the form.
	 *
	 * @param string $name   Field name.
	 * @param int    $parent_id Post the new files belong to.
	 * @return int[]
	 */
	public static function posted_ids( string $name, int $parent_id = 0 ): array {
		return array_values( array_filter( self::posted_items( $name, $parent_id ), 'is_int' ) );
	}

	/**
	 * What a media field with links holds after a save, in order: attachment
	 * IDs (int) and Media_Link URLs (string). The link typed into the field's
	 * link box and files sent with the form come last.
	 *
	 * @param string $name   Field name.
	 * @param int    $parent_id Post the new files belong to.
	 * @return array<int|string>
	 */
	public static function posted_items( string $name, int $parent_id = 0 ): array {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- callers verified the nonce.
		$kept    = isset( $_POST[ $name ] ) ? array_map( array( self::class, 'clean_value' ), (array) wp_unslash( $_POST[ $name ] ) ) : array();
		$removed = isset( $_POST[ $name . '_remove' ] ) ? array_map( array( self::class, 'clean_value' ), (array) wp_unslash( $_POST[ $name . '_remove' ] ) ) : array();
		$typed   = isset( $_POST[ $name . '_link' ] ) && is_string( $_POST[ $name . '_link' ] ) ? self::clean_value( wp_unslash( $_POST[ $name . '_link' ] ) ) : '';
		// phpcs:enable

		$items = array();
		foreach ( array_merge( $kept, array( $typed ) ) as $item ) {
			if ( ( is_int( $item ) && 'attachment' !== get_post_type( $item ) ) || '' === $item || 0 === $item || in_array( $item, $removed, true ) || in_array( $item, $items, true ) ) {
				continue;
			}
			$items[] = $item;
		}

		return array_merge( $items, array_diff( self::from_files( $name . '_files', $parent_id ), $items ) );
	}

	/**
	 * A posted item: an attachment ID (digits) or a link, '' when neither.
	 *
	 * @param mixed $value Posted value.
	 * @return int|string
	 */
	private static function clean_value( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		return ctype_digit( $value ) ? (int) $value : Media_Link::normalize( $value );
	}

	/**
	 * Prints a media field.
	 *
	 * @param string            $name  Field name (items post as `name[]`).
	 * @param string            $label Visible label.
	 * @param array<int|string> $items Current attachment IDs, and links when `links` is on.
	 * @param array             $args  `multiple` (bool), `accept` (MIME list), `links` (bool), `help`.
	 */
	public static function field( string $name, string $label, array $items, array $args = array() ): void {
		$multiple = ! empty( $args['multiple'] );
		$links    = $multiple && ! empty( $args['links'] );
		$accept   = (string) ( $args['accept'] ?? 'image/*' );
		$can      = current_user_can( 'upload_files' );
		$input_id = 'lzp-media-' . sanitize_html_class( $name );
		?>
		<div class="lzp-field lzp-media" data-lzp-media="<?php echo esc_attr( $name ); ?>" data-accept="<?php echo esc_attr( $accept ); ?>"<?php echo $multiple ? ' data-multiple' : ''; ?>>
			<span class="lzp-field__label" id="<?php echo esc_attr( $input_id ); ?>-label"><?php echo esc_html( $label ); ?></span>
			<ul class="lzp-media__list" aria-labelledby="<?php echo esc_attr( $input_id ); ?>-label">
				<?php foreach ( array_filter( $items ) as $item ) : ?>
					<?php self::item( $name, is_string( $item ) ? self::link( $item ) : self::attachment( (int) $item ) ); ?>
				<?php endforeach; ?>
				<?php if ( $can ) : ?>
					<li class="lzp-media__add">
						<label for="<?php echo esc_attr( $input_id ); ?>">
							<?php Ui::the_icon( 'plus' ); ?>
							<span><?php echo $multiple ? esc_html__( 'Add photos', 'lenz-plus' ) : esc_html__( 'Choose a photo', 'lenz-plus' ); ?></span>
						</label>
						<input class="lzp-media__file" type="file" id="<?php echo esc_attr( $input_id ); ?>" name="<?php echo esc_attr( $name . '_files' . ( $multiple ? '[]' : '' ) ); ?>" accept="<?php echo esc_attr( $accept ); ?>"<?php echo $multiple ? ' multiple' : ''; ?>>
					</li>
				<?php endif; ?>
			</ul>
			<?php if ( $can ) : ?>
				<div class="lzp-media__more">
					<button type="button" class="lzp-btn lzp-btn--ghost" data-lzp-pick hidden><?php Ui::the_icon( 'images' ); ?><span><?php esc_html_e( 'From the library', 'lenz-plus' ); ?></span></button>
				</div>
			<?php endif; ?>
			<?php if ( $links ) : ?>
				<details class="lzp-more lzp-media__link">
					<summary><?php esc_html_e( 'Add a video link', 'lenz-plus' ); ?></summary>
					<label class="lzp-field__label" for="<?php echo esc_attr( $input_id ); ?>-link"><?php esc_html_e( 'Aparat link or embed code, or a direct link to the video file', 'lenz-plus' ); ?></label>
					<div class="lzp-media__link-row">
						<textarea class="lzp-input" id="<?php echo esc_attr( $input_id ); ?>-link" name="<?php echo esc_attr( $name . '_link' ); ?>" rows="2" dir="ltr" placeholder="https://www.aparat.com/v/…"></textarea>
						<button type="button" class="lzp-btn lzp-btn--ghost" data-lzp-add-link hidden><?php Ui::the_icon( 'plus' ); ?><span><?php esc_html_e( 'Add', 'lenz-plus' ); ?></span></button>
					</div>
					<span class="lzp-field__help"><?php esc_html_e( 'Big films can stay on Aparat or on a download host, so they do not fill the site\'s host. Aparat videos play in Aparat\'s player; other links play as a video file (MP4).', 'lenz-plus' ); ?></span>
				</details>
			<?php endif; ?>
			<?php if ( ! empty( $args['help'] ) ) : ?>
				<span class="lzp-field__help"><?php echo esc_html( $args['help'] ); ?></span>
			<?php endif; ?>
			<?php if ( $can ) : ?>
				<template>
					<?php
					self::item(
						$name,
						array(
							'id'    => 0,
							'thumb' => '',
							'url'   => '',
							'type'  => 'image',
							'name'  => '',
						)
					);
					?>
				</template>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * One kept file of a media field. field() also prints it inside a
	 * <template>, which panel.js copies for files uploaded in the background.
	 *
	 * @param string $name Field name.
	 * @param array  $item attachment() data.
	 */
	public static function item( string $name, array $item ): void {
		?>
		<li class="lzp-media__item" data-id="<?php echo esc_attr( (string) $item['id'] ); ?>">
			<input type="hidden" name="<?php echo esc_attr( $name ); ?>[]" value="<?php echo esc_attr( (string) $item['id'] ); ?>">
			<?php if ( '' !== $item['thumb'] ) : ?>
				<img src="<?php echo esc_url( $item['thumb'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy" decoding="async">
			<?php else : ?>
				<span class="lzp-media__video"><?php Ui::the_icon( 'video' ); ?><span><?php echo esc_html( $item['name'] ); ?></span></span>
			<?php endif; ?>
			<span class="lzp-media__tools">
				<button type="button" class="lzp-media__move" data-lzp-move="-1" hidden><span class="screen-reader-text"><?php esc_html_e( 'Move earlier', 'lenz-plus' ); ?></span><?php Ui::the_icon( 'back', 'lzp-ico--start' ); ?></button>
				<label class="lzp-media__remove">
					<input type="checkbox" name="<?php echo esc_attr( $name ); ?>_remove[]" value="<?php echo esc_attr( (string) $item['id'] ); ?>">
					<span class="screen-reader-text"><?php esc_html_e( 'Remove', 'lenz-plus' ); ?></span>
					<?php Ui::the_icon( 'close' ); ?>
				</label>
				<button type="button" class="lzp-media__move" data-lzp-move="1" hidden><span class="screen-reader-text"><?php esc_html_e( 'Move later', 'lenz-plus' ); ?></span><?php Ui::the_icon( 'back', 'lzp-ico--end' ); ?></button>
			</span>
		</li>
		<?php
	}
}
