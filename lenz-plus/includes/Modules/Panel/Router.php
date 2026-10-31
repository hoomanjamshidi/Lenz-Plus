<?php
/**
 * Address of the studio panel and the request that opens it.
 *
 * The panel lives at site.com/<slug>/<screen>/ (rewrite rule) or, with plain
 * permalinks, at ?lzp_panel=<screen>. Sub-pages are query arguments
 * (`?edit=12`, `?status=draft`), so every link is a real URL that works
 * without JavaScript and survives a reload. The panel is its own document:
 * the theme's header, footer and styles are never loaded on it.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel;

defined( 'ABSPATH' ) || exit;

/**
 * Rewrite rule, URLs and the dispatch of panel requests.
 */
final class Router {

	/** Query variable holding the screen id. */
	public const QUERY_VAR = 'lzp_panel';

	/** Screen shown at the panel's root. */
	public const HOME = 'home';

	/** Stores the slug the rewrite rules were last flushed for. */
	private const RULES_OPTION = 'lenz_plus_panel_rules';

	/** @var string Slug of the panel, set on register(). */
	private static $slug = Schema::DEFAULT_SLUG;

	/** @var App */
	private $app;

	/**
	 * @param string $slug Panel slug from the settings.
	 * @param App    $app  Renders and runs the screens.
	 */
	public function __construct( string $slug, App $app ) {
		self::$slug = '' !== $slug ? $slug : Schema::DEFAULT_SLUG;
		$this->app  = $app;
	}

	/** Adds the rewrite rule, the query variable and the dispatcher. */
	public function register(): void {
		$slug = preg_quote( self::$slug, '#' );
		add_rewrite_rule( '^' . $slug . '/?$', 'index.php?' . self::QUERY_VAR . '=' . self::HOME, 'top' );
		add_rewrite_rule( '^' . $slug . '/([a-z0-9-]+)/?$', 'index.php?' . self::QUERY_VAR . '=$matches[1]', 'top' );

		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'template_redirect', array( $this, 'dispatch' ), 0 );
		add_action( 'wp_loaded', array( $this, 'maybe_flush' ) );
	}

	/**
	 * @param string[] $vars Public query variables.
	 * @return string[]
	 */
	public function query_vars( $vars ) {
		$vars[] = self::QUERY_VAR;

		return $vars;
	}

	/**
	 * Flushes the rewrite rules once after the slug changes (or the first
	 * time), so the new address works without visiting Settings → Permalinks.
	 */
	public function maybe_flush(): void {
		if ( get_option( self::RULES_OPTION ) !== self::$slug ) {
			flush_rewrite_rules( false );
			update_option( self::RULES_OPTION, self::$slug, false );
		}
	}

	/** Forgets the flushed slug, so the next request flushes again. */
	public static function forget_rules(): void {
		delete_option( self::RULES_OPTION );
	}

	/**
	 * A panel URL.
	 *
	 * @param string               $screen Screen id.
	 * @param array<string, mixed> $args   Query arguments.
	 */
	public static function url( string $screen = self::HOME, array $args = array() ): string {
		if ( get_option( 'permalink_structure' ) ) {
			$path = self::HOME === $screen ? self::$slug . '/' : self::$slug . '/' . $screen . '/';
			$url  = home_url( user_trailingslashit( $path ) );
		} else {
			$url = add_query_arg( self::QUERY_VAR, $screen, home_url( '/' ) );
		}

		return $args ? add_query_arg( array_map( 'rawurlencode', array_map( 'strval', $args ) ), $url ) : $url;
	}

	/** Screen id asked for, or '' when the request is not for the panel. */
	public static function requested_screen(): string {
		return sanitize_key( (string) get_query_var( self::QUERY_VAR ) );
	}

	/** Runs the panel instead of the theme's template. */
	public function dispatch(): void {
		$screen = self::requested_screen();
		if ( '' === $screen ) {
			return;
		}

		nocache_headers();
		header( 'X-Robots-Tag: noindex, nofollow', true );
		header( 'X-Frame-Options: SAMEORIGIN', true );

		$this->app->run( $screen );
		exit;
	}
}
