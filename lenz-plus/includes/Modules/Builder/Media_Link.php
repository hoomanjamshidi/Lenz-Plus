<?php
/**
 * Photos and videos kept outside the site's own uploads: an Aparat video
 * (its page link, its iframe code or its script embed code) or a direct file
 * link on a download host. Studios use them so big films do not fill the
 * main host.
 *
 * Links are stored as plain URLs in Lenz's `_external_links` (Lenz's gallery
 * box, the studio panel). normalize() turns whatever was pasted into the URL
 * to store: every Aparat form becomes the video's page link, so Lenz's
 * `sanitize_url()` keeps it and both editors show the same value. The
 * renderers ask embed_url() for the player: Aparat links play in Aparat's
 * iframe, every other link is a video file for `<video>`.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Static helpers for external media links.
 */
final class Media_Link {

	/** Aparat's video page; `%s` is the video's hash. */
	private const APARAT_PAGE = 'https://www.aparat.com/v/%s';

	/** Aparat's iframe player; `%s` is the video's hash. */
	private const APARAT_EMBED = 'https://www.aparat.com/video/video/embed/videohash/%s/vt/frame';

	/** File extensions read as photos; any other link is a video. */
	private const IMAGE_EXTENSIONS = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg' );

	/**
	 * The URL to store for a pasted link or embed code, '' when it holds none.
	 *
	 * @param string $raw A URL, an `<iframe>` or Aparat's `<script>` embed code.
	 */
	public static function normalize( string $raw ): string {
		$raw = trim( html_entity_decode( $raw, ENT_QUOTES, 'UTF-8' ) );

		// Embed codes: the player's address is in their src attribute.
		if ( preg_match( '/<(?:iframe|script)\b[^>]*\bsrc\s*=\s*["\']?([^"\'\s>]+)/iu', $raw, $match ) ) {
			$raw = $match[1];
		}

		if ( 0 === strpos( $raw, '//' ) ) {
			$raw = 'https:' . $raw;
		} elseif ( preg_match( '#^(?:www\.)?[a-z0-9-]+(?:\.[a-z0-9-]+)+/#iu', $raw ) ) {
			// Typed without a scheme (aparat.com/v/…).
			$raw = 'https://' . $raw;
		}

		$aparat = self::aparat_id( $raw );
		if ( '' !== $aparat ) {
			return sprintf( self::APARAT_PAGE, $aparat );
		}

		$url = esc_url_raw( $raw, array( 'http', 'https' ) );

		return '' !== $url && '' !== (string) wp_parse_url( $url, PHP_URL_HOST ) ? $url : '';
	}

	/**
	 * The hash of an Aparat video link, '' for other links.
	 *
	 * Covers the page (`/v/<hash>`), the iframe player
	 * (`/video/video/embed/videohash/<hash>/vt/frame`) and the script embed
	 * (`/embed/<hash>?data[rnddiv]=…`).
	 *
	 * @param string $url Link.
	 */
	public static function aparat_id( string $url ): string {
		if ( preg_match( '#^(?:https?:)?//(?:www\.)?aparat\.com/(?:v|embed|video/video/embed/videohash)/([A-Za-z0-9]+)#u', trim( $url ), $match ) ) {
			return $match[1];
		}

		return '';
	}

	/**
	 * Address of the iframe player for a link, '' when the link is a file
	 * that plays in `<video>`.
	 *
	 * @param string $url Stored link.
	 */
	public static function embed_url( string $url ): string {
		$aparat = self::aparat_id( $url );

		return '' !== $aparat ? sprintf( self::APARAT_EMBED, $aparat ) : '';
	}

	/**
	 * What a link shows: `image` for photo files, else `video`.
	 *
	 * @param string $url Stored link.
	 */
	public static function type_of( string $url ): string {
		$extension = strtolower( pathinfo( (string) wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );

		return in_array( $extension, self::IMAGE_EXTENSIONS, true ) ? 'image' : 'video';
	}

	/**
	 * Short name of a link for lists: «آپارات» or the host.
	 *
	 * @param string $url Stored link.
	 */
	public static function label( string $url ): string {
		if ( '' !== self::aparat_id( $url ) ) {
			return __( 'Aparat', 'lenz-plus' );
		}

		$host = (string) wp_parse_url( $url, PHP_URL_HOST );

		return '' !== $host ? preg_replace( '/^www\./u', '', $host ) : $url;
	}
}
