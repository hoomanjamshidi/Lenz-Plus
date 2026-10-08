<?php
/**
 * Front-end assets shared by every template and widget, plus the brand
 * tokens (`--lzp-*` custom properties) they are styled with.
 *
 * Widget groups with their own stylesheet or script (sections, portfolio,
 * blog, courses) register further handles here as they are added, so a page
 * only loads what its widgets use.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

use LenzPlus\Core\Asset;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the builder's handles and prints the brand tokens inline.
 */
final class Assets {

	/** Shared stylesheet and script: design utilities, basic widgets, header and footer parts. */
	public const HANDLE = 'lzp-builder';

	/** Page sections (heroes, numbers, steps, cards, lists, pricing, FAQ, call-to-action bands). */
	public const SECTIONS_HANDLE = 'lzp-sections';

	/** Request and sign-up forms (stylesheet and script). */
	public const FORMS_HANDLE = 'lzp-forms';

	/** Portfolio grids, featured projects and project pages (stylesheet and the filter script). */
	public const PORTFOLIO_HANDLE = 'lzp-portfolio';

	/** Inline-only style: the brand tokens every Lenz+ stylesheet depends on. */
	public const TOKENS_HANDLE = 'lzp-tokens';

	/** @var Module */
	private $module;

	/** @var bool */
	private $registered = false;

	/**
	 * @param Module $module Owning module.
	 */
	public function __construct( Module $module ) {
		$this->module = $module;
	}

	/**
	 * Registers early on the front end and again when Elementor registers its
	 * own assets (the editor preview runs without `wp_enqueue_scripts` order guarantees).
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ), 1 );
		add_action( 'elementor/frontend/after_register_styles', array( $this, 'register_assets' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register_assets' ) );
	}

	/**
	 * Registers the handles once per request; widgets enqueue them through
	 * their style dependencies, so pages without Lenz+ widgets load nothing.
	 */
	public function register_assets(): void {
		if ( $this->registered ) {
			return;
		}
		$this->registered = true;

		// No file: the tokens are small and needed before any Lenz+ stylesheet, so they print inline.
		wp_register_style( self::TOKENS_HANDLE, false, array(), LENZ_PLUS_VERSION );
		wp_add_inline_style( self::TOKENS_HANDLE, $this->tokens_css() );

		wp_register_style( self::HANDLE, Asset::url( 'assets/modules/builder/css/builder.css' ), array( self::TOKENS_HANDLE ), LENZ_PLUS_VERSION );
		wp_register_style( self::SECTIONS_HANDLE, Asset::url( 'assets/modules/builder/css/sections.css' ), array( self::HANDLE ), LENZ_PLUS_VERSION );
		wp_register_style( self::PORTFOLIO_HANDLE, Asset::url( 'assets/modules/builder/css/portfolio.css' ), array( self::SECTIONS_HANDLE ), LENZ_PLUS_VERSION );
		wp_register_style( self::FORMS_HANDLE, Asset::url( 'assets/modules/builder/css/forms.css' ), array( self::SECTIONS_HANDLE ), LENZ_PLUS_VERSION );

		Asset::register_shared();
		wp_register_script(
			self::HANDLE,
			Asset::url( 'assets/modules/builder/js/builder.js' ),
			array( Asset::LENZ_MENU ),
			LENZ_PLUS_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		wp_add_inline_script( self::HANDLE, 'window.lzpBuilder = ' . wp_json_encode( $this->script_config() ) . ';', 'before' );

		wp_register_script(
			self::PORTFOLIO_HANDLE,
			Asset::url( 'assets/modules/builder/js/portfolio.js' ),
			array(),
			LENZ_PLUS_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		wp_register_script(
			self::FORMS_HANDLE,
			Asset::url( 'assets/modules/builder/js/forms.js' ),
			array(),
			LENZ_PLUS_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		wp_add_inline_script(
			self::FORMS_HANDLE,
			'window.lzpForms = ' . wp_json_encode(
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'i18n'    => Public_Form::error_messages(),
				)
			) . ';',
			'before'
		);
	}

	/** Settings and strings for builder.js (`window.lzpBuilder`). */
	private function script_config(): array {
		return array(
			'breakpoint' => (int) $this->module->settings()['breakpoint'],
			'i18n'       => array(
				'close' => __( 'Close', 'lenz-plus' ),
			),
		);
	}

	/**
	 * `:root` custom properties from the brand settings and the global look
	 * options, then the derived tokens (tokens.css).
	 */
	public function tokens_css(): string {
		$settings = $this->module->settings();
		$brand    = $settings['brand'];
		$vars     = array();

		foreach ( Schema::BRAND_DEFAULTS as $key => $fallback ) {
			$value  = '' !== $brand[ $key ] ? $brand[ $key ] : $fallback;
			$vars[] = '--lzp-' . str_replace( '_', '-', $key ) . ':' . $value;
		}

		$vars[] = '--lzp-radius:' . (int) $brand['radius'] . 'px';

		// The mockups' "photo tone" and "guides" props, applied to every design at once.
		$vars[] = '--lzp-photo-filter:' . ( 'grayscale' === $settings['options']['photo_tone'] ? 'grayscale(1)' : 'none' );
		$vars[] = '--lzp-guides:' . ( $settings['options']['guides'] ? 'block' : 'none' );

		return ':root{' . implode( ';', $vars ) . '}' . Asset::contents( 'assets/modules/builder/css/tokens.css' );
	}
}
