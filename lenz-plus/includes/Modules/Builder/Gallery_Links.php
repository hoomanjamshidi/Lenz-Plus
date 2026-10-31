<?php
/**
 * Video links in Lenz's own gallery box on WordPress's portfolio editor.
 *
 * Lenz's "External media links" rows already hold photos and videos that
 * live elsewhere; the plugin's project pages play Aparat links in Aparat's
 * player and other links as video files (Media_Link). This class makes the
 * box take what studios paste: Aparat's iframe or script code becomes the
 * video's link while typing (Lenz's field is `type="url"`, so the browser
 * would refuse the code), the row switches to "Video" by itself, and a hint
 * explains the choices. On save the stored links go through
 * Media_Link::normalize() again, for posts sent without the script.
 *
 * Lenz's box, its markup and its saving stay Lenz's: the script only edits
 * the values of its inputs.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

use LenzPlus\Core\Asset;

defined( 'ABSPATH' ) || exit;

/**
 * Editor script and save-time cleaning of Lenz's external links.
 */
final class Gallery_Links {

	/** Script handle. */
	private const HANDLE = 'lzp-gallery-links-admin';

	/** Hooks the script and the cleaning. */
	public function register(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		// After Lenz's own save (10), which writes the links.
		add_action( 'save_post_' . Portfolio_Data::POST_TYPE, array( $this, 'clean' ), 20 );
	}

	/** Loads the script on the portfolio editor. */
	public function enqueue(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'post' !== $screen->base || Portfolio_Data::POST_TYPE !== $screen->post_type ) {
			return;
		}

		Asset::register_shared();
		wp_enqueue_script( self::HANDLE, Asset::url( 'assets/modules/builder/js/gallery-links-admin.js' ), array( Asset::MEDIA_LINK ), LENZ_PLUS_VERSION, true );
		wp_add_inline_script(
			self::HANDLE,
			'window.lzpGalleryLinks = ' . wp_json_encode(
				array(
					'hint' => __( 'Lenz+ project pages play these videos: an Aparat link or its embed code (iframe), or a direct link to the video file on a download host (MP4). Big films can live there instead of filling the site\'s host.', 'lenz-plus' ),
				)
			) . ';',
			'before'
		);
	}

	/**
	 * Re-cleans the links Lenz saved: embed codes become links, empty ones go.
	 *
	 * @param int $post_id Project ID.
	 */
	public function clean( $post_id ): void {
		if ( wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}

		$links = get_post_meta( $post_id, '_external_links', true );
		if ( ! is_array( $links ) || ! $links ) {
			return;
		}

		$clean = array();
		foreach ( $links as $link ) {
			$url = is_array( $link ) ? Media_Link::normalize( (string) ( $link['url'] ?? '' ) ) : '';
			if ( '' === $url ) {
				continue;
			}

			$link['url'] = $url;
			if ( '' !== Media_Link::aparat_id( $url ) ) {
				$link['type'] = 'video';
			}
			$clean[] = $link;
		}

		if ( $clean !== $links ) {
			update_post_meta( $post_id, '_external_links', $clean );
		}
	}
}
