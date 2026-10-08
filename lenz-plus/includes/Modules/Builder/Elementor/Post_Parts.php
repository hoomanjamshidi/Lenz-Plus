<?php
/**
 * Shared pieces of the post and project widgets: the content with anchors
 * on its headings (shared by the content and the table of contents).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor;

use LenzPlus\Modules\Builder\Context;

defined( 'ABSPATH' ) || exit;

/**
 * Static helpers shared by the post widgets.
 */
final class Post_Parts {

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
}
