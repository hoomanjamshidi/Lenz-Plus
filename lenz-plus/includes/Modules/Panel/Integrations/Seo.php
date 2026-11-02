<?php
/**
 * Rank Math and Yoast SEO in the panel's edit pages (articles, pages,
 * projects, products and courses).
 *
 * The card edits the same post meta the plugins' own boxes write, so the
 * plugins read the values exactly as if they had been typed in wp-admin:
 * title, meta description, focus keyword, canonical address and "hide from
 * search engines". Empty values delete the meta, so the plugins' own title
 * and description templates apply again. Without either plugin the card is
 * not printed. Ported from Daftar's Integrations\Seo.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Integrations;

use LenzPlus\Core\Persian;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * SEO bridge and its card.
 */
final class Seo {

	/** Posted field holding the card's values. */
	public const FIELD = 'seo';

	/** Lengths search engines show before they cut (characters). */
	private const TITLE_LENGTH = 60;

	private const DESC_LENGTH = 160;

	/** Meta keys per plugin. */
	private const KEYS = array(
		'rankmath' => array(
			'title'     => 'rank_math_title',
			'desc'      => 'rank_math_description',
			'keyword'   => 'rank_math_focus_keyword',
			'canonical' => 'rank_math_canonical_url',
			'score'     => 'rank_math_seo_score',
		),
		'yoast'    => array(
			'title'     => '_yoast_wpseo_title',
			'desc'      => '_yoast_wpseo_metadesc',
			'keyword'   => '_yoast_wpseo_focuskw',
			'canonical' => '_yoast_wpseo_canonical',
			'score'     => '_yoast_wpseo_linkdex',
		),
	);

	/** The active SEO plugin: `rankmath`, `yoast` or '' (Rank Math wins when both are on). */
	public static function provider(): string {
		if ( defined( 'RANK_MATH_VERSION' ) || class_exists( '\RankMath' ) ) {
			return 'rankmath';
		}

		return defined( 'WPSEO_VERSION' ) ? 'yoast' : '';
	}

	/** Name of the active plugin for the card's title. */
	private static function provider_name( string $provider ): string {
		return 'rankmath' === $provider ? 'Rank Math' : 'Yoast SEO';
	}

	/**
	 * A post's SEO fields, or null without an SEO plugin.
	 *
	 * @param int $post_id Post ID (0 for a new post).
	 */
	public static function get( int $post_id ): ?array {
		$provider = self::provider();
		if ( '' === $provider ) {
			return null;
		}

		$keys = self::KEYS[ $provider ];
		$out  = array( 'provider' => $provider );
		foreach ( array( 'title', 'desc', 'keyword', 'canonical' ) as $field ) {
			$out[ $field ] = $post_id ? (string) get_post_meta( $post_id, $keys[ $field ], true ) : '';
		}

		$score          = $post_id ? (string) get_post_meta( $post_id, $keys['score'], true ) : '';
		$out['score']   = '' === $score ? null : (int) $score;
		$out['noindex'] = $post_id && self::noindex( $post_id, $provider );

		return $out;
	}

	/**
	 * Whether the post is hidden from search engines.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $provider Provider.
	 */
	private static function noindex( int $post_id, string $provider ): bool {
		if ( 'rankmath' === $provider ) {
			return in_array( 'noindex', (array) get_post_meta( $post_id, 'rank_math_robots', true ), true );
		}

		return '1' === (string) get_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', true );
	}

	/**
	 * Prints the SEO card of an edit page (nothing without an SEO plugin).
	 *
	 * @param \WP_Post|null $post Post, null for a new one.
	 */
	public static function render_card( ?\WP_Post $post ): void {
		$seo = self::get( $post ? (int) $post->ID : 0 );
		if ( null === $seo ) {
			return;
		}

		$name  = self::FIELD;
		$title = $post ? html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES, 'UTF-8' ) : '';
		$link  = $post && 'publish' === $post->post_status ? (string) get_permalink( $post ) : home_url( '/' );
		?>
		<section class="lzp-card lzp-seo" data-lzp-seo>
			<div class="lzp-section__head">
				<h2 class="lzp-card__title">
					<?php
					/* translators: %s: Rank Math or Yoast SEO. */
					echo esc_html( sprintf( __( 'SEO (%s)', 'lenz-plus' ), self::provider_name( $seo['provider'] ) ) );
					?>
				</h2>
				<?php if ( null !== $seo['score'] ) : ?>
					<?php
					$tone = $seo['score'] >= 80 ? 'completed' : ( $seo['score'] >= 50 ? 'pending' : 'failed' );
					/* translators: %s: SEO score out of 100. */
					echo Ui::pill( $tone, sprintf( __( 'Score %s', 'lenz-plus' ), Ui::number( $seo['score'] ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built by Ui::pill().
					?>
				<?php endif; ?>
			</div>

			<div class="lzp-serp" aria-hidden="true">
				<span class="lzp-serp__url" dir="ltr"><?php echo esc_html( self::short_url( $link ) ); ?></span>
				<span class="lzp-serp__title" data-lzp-serp-title data-fallback="<?php echo esc_attr( $title ); ?>"><?php echo esc_html( '' !== $seo['title'] ? $seo['title'] : $title ); ?></span>
				<span class="lzp-serp__desc" data-lzp-serp-desc data-fallback="<?php esc_attr_e( 'The search engine picks a part of the text.', 'lenz-plus' ); ?>"><?php echo esc_html( '' !== $seo['desc'] ? $seo['desc'] : __( 'The search engine picks a part of the text.', 'lenz-plus' ) ); ?></span>
			</div>

			<?php
			Ui::input(
				$name . '[keyword]',
				__( 'Focus keyword', 'lenz-plus' ),
				$seo['keyword'],
				array( 'help' => 'rankmath' === $seo['provider'] ? __( 'The word people search for. Separate several with commas; the first one is the main keyword.', 'lenz-plus' ) : __( 'The word people search for.', 'lenz-plus' ) )
			);
			Ui::input(
				$name . '[title]',
				__( 'SEO title', 'lenz-plus' ),
				$seo['title'],
				array(
					'placeholder' => $title,
					'help'        => self::length_help( self::TITLE_LENGTH ),
				)
			);
			Ui::textarea(
				$name . '[desc]',
				__( 'Meta description', 'lenz-plus' ),
				$seo['desc'],
				array(
					'rows' => 3,
					'help' => self::length_help( self::DESC_LENGTH ),
				)
			);
			?>
			<details class="lzp-more"<?php echo '' !== $seo['canonical'] || $seo['noindex'] ? ' open' : ''; ?>>
				<summary><?php esc_html_e( 'Advanced', 'lenz-plus' ); ?></summary>
				<?php
				Ui::input(
					$name . '[canonical]',
					__( 'Canonical address', 'lenz-plus' ),
					$seo['canonical'],
					array(
						'type' => 'url',
						'dir'  => 'ltr',
						'help' => __( 'Only when the same content lives at another address. Empty: this page\'s own address.', 'lenz-plus' ),
					)
				);
				?>
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>[noindex]" value="0">
				<?php Ui::toggle( $name . '[noindex]', __( 'Hide from search engines', 'lenz-plus' ), $seo['noindex'], array( 'help' => __( 'Search engines are asked not to list this page (noindex).', 'lenz-plus' ) ) ); ?>
			</details>
		</section>
		<?php
	}

	/**
	 * The help line under a field with a recommended length («Up to 60 characters»).
	 *
	 * @param int $length Recommended length.
	 */
	private static function length_help( int $length ): string {
		/* translators: %s: number of characters. */
		return sprintf( __( 'Search engines show about %s characters.', 'lenz-plus' ), Ui::number( $length ) );
	}

	/**
	 * An address as search results print it (no scheme, no trailing slash).
	 *
	 * @param string $url Address.
	 */
	private static function short_url( string $url ): string {
		return rtrim( (string) preg_replace( '#^https?://#', '', rawurldecode( $url ) ), '/' );
	}

	/**
	 * Saves the posted SEO fields (only those present).
	 *
	 * @param int   $post_id Post ID.
	 * @param mixed $input   Posted `seo` array.
	 */
	public static function save( int $post_id, $input ): void {
		$provider = self::provider();
		if ( '' === $provider || ! is_array( $input ) || ! $input || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$keys = self::KEYS[ $provider ];
		foreach ( array( 'title', 'keyword' ) as $field ) {
			if ( isset( $input[ $field ] ) && is_string( $input[ $field ] ) ) {
				self::put( $post_id, $keys[ $field ], sanitize_text_field( $input[ $field ] ) );
			}
		}
		if ( isset( $input['desc'] ) && is_string( $input['desc'] ) ) {
			self::put( $post_id, $keys['desc'], sanitize_textarea_field( $input['desc'] ) );
		}
		if ( isset( $input['canonical'] ) && is_string( $input['canonical'] ) ) {
			self::put( $post_id, $keys['canonical'], esc_url_raw( trim( Persian::latin_digits( $input['canonical'] ) ) ) );
		}

		if ( isset( $input['noindex'] ) ) {
			$noindex = '1' === (string) $input['noindex'];
			if ( 'rankmath' === $provider ) {
				// Rank Math keeps all robots values in one list; only index/noindex change.
				$robots = array_diff( (array) get_post_meta( $post_id, 'rank_math_robots', true ), array( 'index', 'noindex', '' ) );
				array_unshift( $robots, $noindex ? 'noindex' : 'index' );
				update_post_meta( $post_id, 'rank_math_robots', array_values( $robots ) );
			} else {
				self::put( $post_id, '_yoast_wpseo_meta-robots-noindex', $noindex ? '1' : '' );
			}
		}

		if ( 'yoast' === $provider ) {
			// Yoast keeps an indexable row per post; ask it to rebuild so the change shows at once.
			do_action( 'wpseo_save_indexable_for_post', $post_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Yoast's hook.
			clean_post_cache( $post_id );
		}
	}

	/**
	 * Writes a meta value, deleting it when empty (the plugins then use their templates).
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @param string $value   Value.
	 */
	private static function put( int $post_id, string $key, string $value ): void {
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, wp_slash( $value ) );
		}
	}
}
