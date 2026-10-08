<?php
/**
 * Shared pieces of the post and project widgets: the content with anchors
 * on its headings (shared by the content and the table of contents),
 * dates, reading time, share links, author pictures and what a post list
 * page is about.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor;

use LenzPlus\Core\Persian;
use LenzPlus\Core\Site;
use LenzPlus\Modules\Builder\Context;
use LenzPlus\Modules\Builder\Elementor\Widgets\Base;

defined( 'ABSPATH' ) || exit;

/**
 * Static helpers shared by the post widgets.
 */
final class Post_Parts {

	/** Reading speed used for the reading time (words per minute). */
	private const WORDS_PER_MINUTE = 200;

	/** @var array<int, array{html:string, headings:array}> Filtered content per post, for this request. */
	private static $content = array();

	/**
	 * The post's content after WordPress's content filters, with an `id` on
	 * every H2 and H3 and tables in scrolling boxes, and the list of those
	 * headings. Filtered once per
	 * request, so the table of contents and the content never disagree and
	 * content filters (shortcodes, embeds) do not run twice.
	 *
	 * @param \WP_Post $post Post.
	 * @return array{html:string, headings: array<int, array{level:int, id:string, text:string}>}
	 */
	public static function content( \WP_Post $post ): array {
		if ( isset( self::$content[ $post->ID ] ) ) {
			return self::$content[ $post->ID ];
		}

		$html = (string) Context::run_post(
			$post,
			static function () {
				// get_the_content() reads the current page of a post split with <!--nextpage-->.
				return str_replace( ']]>', ']]&gt;', (string) apply_filters( 'the_content', get_the_content() ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core hook.
			}
		);

		self::$content[ $post->ID ] = self::anchor_headings( self::wrap_tables( $html ) );

		return self::$content[ $post->ID ];
	}

	/**
	 * Puts each table in a box that scrolls sideways, so a wide table never
	 * widens the page on phones while narrow ones still fill the column.
	 *
	 * @param string $html Content markup.
	 */
	private static function wrap_tables( string $html ): string {
		$html = (string) preg_replace( '#<table\b#i', '<div class="lzp-prose__table"><table', $html );

		return (string) preg_replace( '#</table>#i', '</table></div>', $html );
	}

	/**
	 * Adds an id to headings that have none and collects H2/H3 for the table
	 * of contents. IDs keep the heading's own words (Persian included), so
	 * shared links such as #why-it-matters stay readable.
	 *
	 * @param string $html Content markup.
	 * @return array{html:string, headings: array<int, array{level:int, id:string, text:string}>}
	 */
	private static function anchor_headings( string $html ): array {
		$headings = array();
		$used     = array();

		$html = (string) preg_replace_callback(
			'#<h([23])(\s[^>]*)?>(.*?)</h\1>#is',
			static function ( array $m ) use ( &$headings, &$used ): string {
				$attrs = $m[2] ?? '';
				$text  = trim( html_entity_decode( wp_strip_all_tags( $m[3] ), ENT_QUOTES, 'UTF-8' ) );

				if ( preg_match( '/\sid=(["\'])(.*?)\1/i', $attrs, $found ) ) {
					$id = $found[2];
				} else {
					$id     = self::unique_slug( $text, $used );
					$attrs .= ' id="' . esc_attr( $id ) . '"';
				}
				$used[ $id ] = true;

				if ( '' !== $text ) {
					$headings[] = array(
						'level' => (int) $m[1],
						'id'    => $id,
						'text'  => $text,
					);
				}

				return '<h' . $m[1] . $attrs . '>' . $m[3] . '</h' . $m[1] . '>';
			},
			$html
		);

		return array(
			'html'     => $html,
			'headings' => $headings,
		);
	}

	/**
	 * A readable, unique id for a heading.
	 *
	 * @param string              $text Heading text.
	 * @param array<string, bool> $used IDs already taken.
	 */
	private static function unique_slug( string $text, array $used ): string {
		$slug = trim( (string) preg_replace( '/[^\p{L}\p{N}]+/u', '-', $text ), '-' );
		$slug = function_exists( 'mb_strtolower' ) ? mb_strtolower( mb_substr( $slug, 0, 60 ) ) : strtolower( substr( $slug, 0, 60 ) );
		$slug = '' !== $slug ? $slug : 'section';

		$id = $slug;
		for ( $i = 2; isset( $used[ $id ] ); $i++ ) {
			$id = $slug . '-' . $i;
		}

		return $id;
	}

	/**
	 * Estimated reading time in minutes (at least 1).
	 *
	 * @param \WP_Post $post Post.
	 */
	public static function reading_minutes( \WP_Post $post ): int {
		$text  = wp_strip_all_tags( strip_shortcodes( (string) $post->post_content ) );
		$words = preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );

		return max( 1, (int) ceil( count( (array) $words ) / self::WORDS_PER_MINUTE ) );
	}

	/**
	 * «۶ دقیقه», with the site's digits.
	 *
	 * @param \WP_Post $post Post.
	 * @param bool     $long «۹ دقیقه مطالعه» (the article header) instead of «۹ دقیقه».
	 */
	public static function reading_label( \WP_Post $post, bool $long = false ): string {
		$minutes = Base::num( self::reading_minutes( $post ) );

		/* translators: %s: number of minutes. */
		return $long ? sprintf( __( '%s min read', 'lenz-plus' ), $minutes ) : sprintf( __( '%s min', 'lenz-plus' ), $minutes );
	}

	/**
	 * Publish date: Jalali on Persian sites, the site's date format elsewhere.
	 *
	 * @param \WP_Post $post Post.
	 */
	public static function date( \WP_Post $post ): string {
		$date = get_post_datetime( $post );

		if ( $date && Persian::is_site_persian() ) {
			return Base::digits( Persian::jalali_date( $date ) );
		}

		return (string) get_the_date( '', $post );
	}

	/**
	 * `<time>` element for the publish date.
	 *
	 * @param \WP_Post $post Post.
	 */
	public static function time_html( \WP_Post $post ): string {
		$date = get_post_datetime( $post );

		return '<time datetime="' . esc_attr( $date ? $date->format( DATE_W3C ) : '' ) . '">' . esc_html( self::date( $post ) ) . '</time>';
	}

	/**
	 * Categories of a post, the default "Uncategorized" left out.
	 *
	 * @param \WP_Post $post Post.
	 * @return \WP_Term[]
	 */
	public static function categories( \WP_Post $post ): array {
		$terms   = get_the_terms( $post, 'category' );
		$default = (int) get_option( 'default_category' );

		return array_values(
			array_filter(
				( $terms && ! is_wp_error( $terms ) ) ? $terms : array(),
				static function ( $term ) use ( $default ) {
					return (int) $term->term_id !== $default;
				}
			)
		);
	}

	/**
	 * Author picture, or an icon when avatars are switched off.
	 *
	 * @param int    $user_id    Author ID.
	 * @param int    $size       Size in pixels.
	 * @param string $class_name Class for the image.
	 */
	public static function avatar( int $user_id, int $size, string $class_name ): string {
		$avatar = get_option( 'show_avatars' ) ? get_avatar(
			$user_id,
			$size,
			'',
			'',
			array(
				'class'         => $class_name,
				'loading'       => 'lazy',
				'force_display' => false,
			)
		) : '';

		return $avatar ? $avatar : '<span class="' . esc_attr( $class_name ) . ' lzp-avatar--empty" aria-hidden="true">' . Base::icon( 'user' ) . '</span>';
	}

	/**
	 * Share networks → label, in their default order.
	 *
	 * @return array<string, string>
	 */
	public static function share_networks(): array {
		return array(
			'telegram' => __( 'Telegram', 'lenz-plus' ),
			'whatsapp' => __( 'WhatsApp', 'lenz-plus' ),
			'x'        => __( 'X (Twitter)', 'lenz-plus' ),
			'linkedin' => __( 'LinkedIn', 'lenz-plus' ),
			'email'    => __( 'Email', 'lenz-plus' ),
		);
	}

	/**
	 * Share link for a network.
	 *
	 * @param string   $network Network key.
	 * @param \WP_Post $post    Post.
	 */
	public static function share_url( string $network, \WP_Post $post ): string {
		$url   = rawurlencode( (string) get_permalink( $post ) );
		$title = rawurlencode( html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ) );

		switch ( $network ) {
			case 'telegram':
				return 'https://t.me/share/url?url=' . $url . '&text=' . $title;
			case 'whatsapp':
				return 'https://wa.me/?text=' . $title . '%20' . $url;
			case 'x':
				return 'https://x.com/intent/post?url=' . $url . '&text=' . $title;
			case 'linkedin':
				return 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url;
			case 'email':
				return 'mailto:?subject=' . $title . '&body=' . $url;
		}

		return '';
	}

	/**
	 * What the post list being viewed is about: a category or tag, an
	 * author, a date or a search, or '' title for the posts page itself.
	 *
	 * @return array{eyebrow:string, title:string, text:string}
	 */
	public static function archive(): array {
		$info = array(
			'eyebrow' => '',
			'title'   => '',
			'text'    => '',
		);

		if ( is_category() || is_tag() || is_tax() ) {
			$term     = get_queried_object();
			$taxonomy = $term instanceof \WP_Term ? get_taxonomy( $term->taxonomy ) : null;

			$info['eyebrow'] = $taxonomy ? (string) $taxonomy->labels->singular_name : '';
			$info['title']   = single_term_title( '', false );
			$info['text']    = wp_strip_all_tags( term_description() );
		} elseif ( is_author() ) {
			$author = get_queried_object();

			$info['eyebrow'] = __( 'Author', 'lenz-plus' );
			$info['title']   = $author instanceof \WP_User ? $author->display_name : '';
			$info['text']    = $author instanceof \WP_User ? (string) get_the_author_meta( 'description', $author->ID ) : '';
		} elseif ( is_date() ) {
			add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
			$info['eyebrow'] = __( 'Archive', 'lenz-plus' );
			$info['title']   = Base::digits( wp_strip_all_tags( get_the_archive_title() ) );
			remove_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
		} elseif ( is_search() ) {
			$info['eyebrow'] = __( 'Search results', 'lenz-plus' );
			// Persian guillemets on right-to-left sites, curly quotes elsewhere.
			$info['title'] = sprintf( Site::is_rtl() ? '«%s»' : '“%s”', get_search_query( false ) );
		}

		return $info;
	}
}
