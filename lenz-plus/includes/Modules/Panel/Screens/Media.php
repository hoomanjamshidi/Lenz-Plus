<?php
/**
 * Media screen: the uploaded photos and videos as a grid, with uploading
 * (several at once, straight from the phone's camera or gallery), copying a
 * file's link and deleting.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Modules\Panel\Ui;
use LenzPlus\Modules\Panel\Uploads;

defined( 'ABSPATH' ) || exit;

/**
 * Media grid, upload and delete.
 */
final class Media extends Screen {

	/** Screen id. */
	public function id(): string {
		return 'media';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Media', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'image';
	}

	/** Uploaders. */
	public function capability(): string {
		return 'upload_files';
	}

	/** Settings switch. */
	public function section(): string {
		return 'media';
	}

	/** Prints the upload box and the grid. */
	public function render(): void {
		$type  = self::arg( 'type' );
		$type  = in_array( $type, array( 'image', 'video' ), true ) ? $type : '';
		$query = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => '' !== $type ? $type : array( 'image', 'video' ),
				'posts_per_page' => 30,
				'paged'          => self::page_number(),
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$this->form_open( 'upload', 'lzp-card lzp-upload', true );
		?>
			<label class="lzp-upload__drop" for="lzp-upload-files">
				<?php Ui::the_icon( 'upload' ); ?>
				<strong><?php esc_html_e( 'Upload photos or videos', 'lenz-plus' ); ?></strong>
				<span class="lzp-card__note"><?php esc_html_e( 'Choose one or several files from your phone or computer.', 'lenz-plus' ); ?></span>
			</label>
			<input type="file" id="lzp-upload-files" name="files[]" accept="image/*,video/*" multiple data-lzp-autosubmit>
			<button type="submit" class="lzp-btn lzp-btn--primary" data-lzp-saving="<?php esc_attr_e( 'Uploading…', 'lenz-plus' ); ?>"><?php esc_html_e( 'Upload', 'lenz-plus' ); ?></button>
		</form>
		<?php
		Ui::chips(
			$this->url(),
			'type',
			array(
				''      => __( 'All', 'lenz-plus' ),
				'image' => __( 'Photos', 'lenz-plus' ),
				'video' => __( 'Videos', 'lenz-plus' ),
			),
			$type
		);

		if ( ! $query->posts ) {
			Ui::empty_state( __( 'Nothing uploaded yet.', 'lenz-plus' ), 'image' );
			return;
		}

		echo '<ul class="lzp-gallery">';
		foreach ( $query->posts as $attachment ) {
			$this->render_item( $attachment );
		}
		echo '</ul>';

		Ui::pagination( $this->url( array_filter( array( 'type' => $type ) ) ), self::page_number(), (int) $query->max_num_pages );
	}

	/**
	 * One file of the grid.
	 *
	 * @param \WP_Post $attachment Attachment.
	 */
	private function render_item( \WP_Post $attachment ): void {
		$item = Uploads::attachment( (int) $attachment->ID );
		?>
		<li class="lzp-gallery__item">
			<details class="lzp-gallery__details">
				<summary>
					<?php if ( '' !== $item['thumb'] ) : ?>
						<img src="<?php echo esc_url( $item['thumb'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy" decoding="async">
					<?php else : ?>
						<span class="lzp-media__video"><?php Ui::the_icon( 'video' ); ?><span><?php echo esc_html( $item['name'] ); ?></span></span>
					<?php endif; ?>
				</summary>
				<div class="lzp-gallery__tools">
					<a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="noopener"><?php Ui::the_icon( 'eye' ); ?><span><?php esc_html_e( 'Open', 'lenz-plus' ); ?></span></a>
					<button type="button" class="lzp-btn lzp-btn--ghost" data-lzp-copy="<?php echo esc_attr( $item['url'] ); ?>" hidden><?php Ui::the_icon( 'copy' ); ?><span><?php esc_html_e( 'Copy link', 'lenz-plus' ); ?></span></button>
					<?php
					if ( current_user_can( 'delete_post', $attachment->ID ) ) {
						$this->button_form( 'delete', array( 'attachment_id' => $attachment->ID ), __( 'Delete', 'lenz-plus' ), 'lzp-btn lzp-btn--ghost lzp-btn--danger-text', __( 'Delete this file for good? Pages that show it will lose it.', 'lenz-plus' ) );
					}
					?>
				</div>
			</details>
		</li>
		<?php
	}

	/**
	 * Uploads and deletes.
	 *
	 * @param string $action Action name.
	 */
	public function handle( string $action ): string {
		if ( 'upload' === $action ) {
			return $this->done( Uploads::from_files( 'files' ) ? 'uploaded' : 'error' );
		}

		if ( 'delete' === $action ) {
			$id = absint( self::posted( 'attachment_id' ) );
			if ( 'attachment' === get_post_type( $id ) && current_user_can( 'delete_post', $id ) && wp_delete_attachment( $id, true ) ) {
				return $this->done( 'deleted' );
			}

			return $this->done( 'error' );
		}

		return $this->url();
	}
}
