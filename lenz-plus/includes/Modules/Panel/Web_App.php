<?php
/**
 * The panel as a phone app: the web app manifest and the head tags that let
 * staff add the panel to their home screen and open it full screen, without
 * the browser's address bar, like an installed app.
 *
 * The manifest is served at the panel's own address (`<slug>/manifest/`)
 * to anyone, since browsers fetch it without cookies; it holds only the
 * panel's name, colours and icons. Icons are the site icon (Settings →
 * General) when one is set, else the bundled camera icons in the brand ink.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel;

use LenzPlus\Core\Site;

defined( 'ABSPATH' ) || exit;

/**
 * Manifest and home-screen head tags.
 */
final class Web_App {

	/** Screen id the manifest is served under. */
	public const MANIFEST = 'manifest';

	/** Bundled icons, used without a site icon. */
	private const ICON_DIR = 'assets/modules/panel/img/';

	/** @var App */
	private $app;

	/**
	 * @param App $app Panel (title, colours).
	 */
	public function __construct( App $app ) {
		$this->app = $app;
	}

	/** Prints the manifest as JSON. */
	public function send_manifest(): void {
		header( 'Content-Type: application/manifest+json; charset=utf-8' );
		header( 'Cache-Control: public, max-age=3600' );

		$start = Router::url();
		$scope = get_option( 'permalink_structure' ) ? $start : home_url( '/' );

		echo wp_json_encode(
			array(
				'name'             => $this->app->title() . ' – ' . get_bloginfo( 'name' ),
				'short_name'       => $this->app->title(),
				'start_url'        => $start,
				'scope'            => $scope,
				'id'               => $start,
				'display'          => 'standalone',
				'background_color' => '#f5f8fa',
				'theme_color'      => $this->app->theme_color(),
				'dir'              => Site::direction(),
				'lang'             => str_replace( '_', '-', get_locale() ),
				'icons'            => $this->icons(),
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		);
	}

	/**
	 * Manifest icons: the site icon's sizes, else the bundled ones.
	 *
	 * @return array<int, array<string, string>>
	 */
	private function icons(): array {
		if ( has_site_icon() ) {
			return array(
				array(
					'src'   => get_site_icon_url( 192 ),
					'sizes' => '192x192',
				),
				array(
					'src'   => get_site_icon_url( 512 ),
					'sizes' => '512x512',
				),
			);
		}

		$icons = array();
		foreach ( array( 192, 512 ) as $size ) {
			$icons[] = array(
				'src'     => LENZ_PLUS_URL . self::ICON_DIR . 'icon-' . $size . '.png',
				'sizes'   => $size . 'x' . $size,
				'type'    => 'image/png',
				'purpose' => 'any maskable',
			);
		}

		return $icons;
	}

	/** Prints the manifest link and the home-screen tags (inside <head>). */
	public function head_tags(): void {
		printf( '<link rel="manifest" href="%s">' . "\n", esc_url( Router::url( self::MANIFEST ) ) );
		echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
		echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
		echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . "\n";
		printf( '<meta name="apple-mobile-web-app-title" content="%s">' . "\n", esc_attr( $this->app->title() ) );

		// wp_site_icon() prints the touch icon when the site has one.
		if ( ! has_site_icon() ) {
			printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( LENZ_PLUS_URL . self::ICON_DIR . 'apple-touch-icon.png' ) );
		}
	}
}
