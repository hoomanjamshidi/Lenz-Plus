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
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel;

defined( 'ABSPATH' ) || exit;

/**
 * Upload handling (form and AJAX) and the media field markup.
 */
final class Uploads {

	/** AJAX action and nonce action of background uploads. */
	public const AJAX_ACTION = 'lzp_panel_upload';

	/** MIME families the panel accepts. */
	private const FAMILIES = array( 'image', 'video' );

	/** Hooks the background upload. */
	public function register(): void {
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'ajax_upload' ) );
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
	 * The IDs a media field holds after a save: the kept ones (not marked for
	 * removal) in their order, then files sent with the form.
	 *
	 * @param string $name   Field name.
	 * @param int    $parent_id Post the new files belong to.
	 * @return int[]
	 */
	public static function posted_ids( string $name, int $parent_id = 0 ): array {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- callers verified the nonce.
		$kept    = isset( $_POST[ $name ] ) ? array_map( 'absint', (array) wp_unslash( $_POST[ $name ] ) ) : array();
		$removed = isset( $_POST[ $name . '_remove' ] ) ? array_map( 'absint', (array) wp_unslash( $_POST[ $name . '_remove' ] ) ) : array();
		// phpcs:enable

		$ids = array_values( array_diff( array_filter( $kept ), $removed ) );
		$ids = array_values(
			array_filter(
				$ids,
				static function ( int $id ): bool {
					return 'attachment' === get_post_type( $id );
				}
			)
		);

		return array_values( array_unique( array_merge( $ids, self::from_files( $name . '_files', $parent_id ) ) ) );
	}

	/**
	 * Prints a media field.
	 *
	 * @param string $name     Field name (IDs post as `name[]`).
	 * @param string $label    Visible label.
	 * @param int[]  $ids      Current attachment IDs.
	 * @param array  $args     `multiple` (bool), `accept` (MIME list), `help`.
	 */
	public static function field( string $name, string $label, array $ids, array $args = array() ): void {
		$multiple = ! empty( $args['multiple'] );
		$accept   = (string) ( $args['accept'] ?? 'image/*' );
		$can      = current_user_can( 'upload_files' );
		$input_id = 'lzp-media-' . sanitize_html_class( $name );
		?>
		<div class="lzp-field lzp-media" data-lzp-media="<?php echo esc_attr( $name ); ?>"<?php echo $multiple ? ' data-multiple' : ''; ?>>
			<span class="lzp-field__label" id="<?php echo esc_attr( $input_id ); ?>-label"><?php echo esc_html( $label ); ?></span>
			<ul class="lzp-media__list" aria-labelledby="<?php echo esc_attr( $input_id ); ?>-label">
				<?php foreach ( array_filter( $ids ) as $id ) : ?>
					<?php self::item( $name, self::attachment( (int) $id ) ); ?>
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
