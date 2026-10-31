<?php
/**
 * The text editor of articles, pages, projects and products in the panel.
 *
 * Which editor a content type gets is a setting (Schema::EDITORS): the
 * panel's own simple editor (a toolbar over a contenteditable box, with the
 * text area underneath as the real field, so the form still posts without
 * JavaScript), WordPress's classic editor, a plain text box, or WordPress's
 * own editor in wp-admin. Texts built with Elementor are never posted from
 * the panel: their content is Elementor's data, and a plain save would
 * flatten it.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel;

defined( 'ABSPATH' ) || exit;

/**
 * Chooses and prints the content editor of a post.
 */
final class Editor {

	/** Name of the posted content field. */
	public const FIELD = 'content';

	/** @var Module */
	private $module;

	/**
	 * @param Module $module Panel module (settings).
	 */
	public function __construct( Module $module ) {
		$this->module = $module;
	}

	/**
	 * The editor chosen for a post type (`product` also covers courses).
	 *
	 * @param string $post_type Post type.
	 */
	public function kind( string $post_type ): string {
		$editors = $this->module->settings()['editor'];

		return (string) ( $editors[ $post_type ] ?? 'simple' );
	}

	/**
	 * Whether a post is built with Elementor (its text belongs to Elementor).
	 *
	 * @param int $post_id Post ID (0 = new).
	 */
	public static function is_elementor( int $post_id ): bool {
		return $post_id > 0 && 'builder' === get_post_meta( $post_id, '_elementor_edit_mode', true );
	}

	/**
	 * Whether the panel posts the content of this post (else it is kept).
	 *
	 * @param string $post_type Post type.
	 * @param int    $post_id   Post ID (0 = new).
	 */
	public function posts_content( string $post_type, int $post_id ): bool {
		return 'WordPress' !== $this->kind( $post_type ) && ! self::is_elementor( $post_id );
	}

	/**
	 * Loads WordPress's editor scripts when the classic editor is chosen.
	 *
	 * @param string $post_type Post type.
	 */
	public function enqueue( string $post_type ): void {
		if ( 'classic' !== $this->kind( $post_type ) ) {
			return;
		}

		wp_enqueue_editor();
		if ( current_user_can( 'upload_files' ) ) {
			wp_enqueue_media();
		}
	}

	/**
	 * Prints the content field.
	 *
	 * @param string $post_type Post type.
	 * @param int    $post_id   Post ID (0 = new).
	 * @param string $content   Current content.
	 * @param string $label     Field label.
	 */
	public function render( string $post_type, int $post_id, string $content, string $label ): void {
		if ( self::is_elementor( $post_id ) ) {
			$this->handoff(
				__( 'This text is built with Elementor, so it is edited there. The other fields on this page are saved here as usual.', 'lenz-plus' ),
				admin_url( 'post.php?post=' . $post_id . '&action=elementor' ),
				__( 'Edit with Elementor', 'lenz-plus' )
			);
			return;
		}

		switch ( $this->kind( $post_type ) ) {
			case 'WordPress':
				$this->handoff(
					__( 'The text is written in WordPress\'s editor. Save this page first; the button opens the editor in a new tab.', 'lenz-plus' ),
					$post_id ? (string) get_edit_post_link( $post_id, 'url' ) : '',
					__( 'Open WordPress\'s editor', 'lenz-plus' )
				);
				return;

			case 'classic':
				echo '<div class="lzp-field lzp-classic-editor"><span class="lzp-field__label">' . esc_html( $label ) . '</span>';
				wp_editor(
					$content,
					'lzp_content',
					array(
						'textarea_name' => self::FIELD,
						'media_buttons' => current_user_can( 'upload_files' ),
						'editor_height' => 320,
					)
				);
				echo '</div>';
				return;

			case 'plain':
				Ui::textarea( self::FIELD, $label, $content, array( 'rows' => 12 ) );
				return;
		}

		$this->simple( $content, $label );
	}

	/**
	 * The panel's own editor.
	 *
	 * @param string $content Current content.
	 * @param string $label   Field label.
	 */
	private function simple( string $content, string $label ): void {
		$tools = array(
			array( 'h2', 'formatBlock', 'h2', __( 'Heading', 'lenz-plus' ), 'H2' ),
			array( 'h3', 'formatBlock', 'h3', __( 'Subheading', 'lenz-plus' ), 'H3' ),
			array( 'p', 'formatBlock', 'p', __( 'Paragraph', 'lenz-plus' ), '¶' ),
			array( 'bold', 'bold', '', __( 'Bold', 'lenz-plus' ), 'B' ),
			array( 'italic', 'italic', '', __( 'Italic', 'lenz-plus' ), 'I' ),
			array( 'ul', 'insertUnorderedList', '', __( 'Bulleted list', 'lenz-plus' ), '•' ),
			array( 'ol', 'insertOrderedList', '', __( 'Numbered list', 'lenz-plus' ), '1.' ),
			array( 'quote', 'formatBlock', 'blockquote', __( 'Quote', 'lenz-plus' ), '“' ),
			array( 'link', 'createLink', '', __( 'Link', 'lenz-plus' ), '' ),
		);
		?>
		<div class="lzp-field lzp-editor" data-lzp-editor>
			<label class="lzp-field__label" for="lzp-f-content"><?php echo esc_html( $label ); ?></label>
			<div class="lzp-editor__bar" role="toolbar" aria-label="<?php esc_attr_e( 'Text formatting', 'lenz-plus' ); ?>" hidden>
				<?php foreach ( $tools as $tool ) : ?>
					<button type="button" class="lzp-editor__tool lzp-editor__tool--<?php echo esc_attr( $tool[0] ); ?>" data-cmd="<?php echo esc_attr( $tool[1] ); ?>" data-arg="<?php echo esc_attr( $tool[2] ); ?>" title="<?php echo esc_attr( $tool[3] ); ?>" aria-label="<?php echo esc_attr( $tool[3] ); ?>">
						<?php if ( 'link' === $tool[0] ) : ?>
							<?php Ui::the_icon( 'link' ); ?>
						<?php else : ?>
							<span aria-hidden="true"><?php echo esc_html( $tool[4] ); ?></span>
						<?php endif; ?>
					</button>
				<?php endforeach; ?>
				<?php if ( current_user_can( 'upload_files' ) ) : ?>
					<label class="lzp-editor__tool" title="<?php esc_attr_e( 'Photo', 'lenz-plus' ); ?>">
						<span class="screen-reader-text"><?php esc_html_e( 'Photo', 'lenz-plus' ); ?></span>
						<?php Ui::the_icon( 'image' ); ?>
						<input type="file" accept="image/*" data-lzp-editor-image hidden>
					</label>
				<?php endif; ?>
				<button type="button" class="lzp-editor__tool" data-cmd="undo" data-arg="" title="<?php esc_attr_e( 'Undo', 'lenz-plus' ); ?>" aria-label="<?php esc_attr_e( 'Undo', 'lenz-plus' ); ?>"><?php Ui::the_icon( 'undo' ); ?></button>
			</div>
			<div class="lzp-editor__area lzp-prose" data-lzp-editor-area role="textbox" aria-multiline="true" aria-label="<?php echo esc_attr( $label ); ?>" hidden></div>
			<textarea class="lzp-input lzp-input--area lzp-editor__source" id="lzp-f-content" name="<?php echo esc_attr( self::FIELD ); ?>" rows="12"><?php echo esc_textarea( $content ); ?></textarea>
		</div>
		<?php
	}

	/**
	 * A note with a button to the editor that owns the text.
	 *
	 * @param string $message Note.
	 * @param string $url     Editor URL ('' = no button yet).
	 * @param string $label   Button text.
	 */
	private function handoff( string $message, string $url, string $label ): void {
		?>
		<div class="lzp-field lzp-handoff">
			<p><?php echo esc_html( $message ); ?></p>
			<?php if ( '' !== $url ) : ?>
				<a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php Ui::the_icon( 'pencil' ); ?><span><?php echo esc_html( $label ); ?></span></a>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * The posted content, cleaned for the current user (scripts and other
	 * unsafe markup removed unless the user may post unfiltered HTML).
	 */
	public static function posted(): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- App verified the nonce; cleaned below.
		$raw = isset( $_POST[ self::FIELD ] ) && is_string( $_POST[ self::FIELD ] ) ? (string) wp_unslash( $_POST[ self::FIELD ] ) : '';

		return current_user_can( 'unfiltered_html' ) ? $raw : wp_kses_post( $raw );
	}
}
