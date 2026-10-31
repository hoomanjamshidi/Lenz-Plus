<?php
/**
 * Checkout form module: WooCommerce's billing fields (shown, required or
 * hidden), a short form for orders with nothing to ship, Latin digits in phone
 * numbers and postcodes, and the texts of the order-received page.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Checkout;

use LenzPlus\Core\Asset;
use LenzPlus\Core\Module as Base_Module;
use LenzPlus\Core\Sanitizer;

defined( 'ABSPATH' ) || exit;

/**
 * Settings, admin panel and boot of the checkout form rules.
 */
final class Module extends Base_Module {

	/** Front-end script that turns digits Latin while the visitor types. */
	public const SCRIPT_HANDLE = 'lzp-checkout';

	/** Module id: the option name suffix and the admin page slug. */
	public function id(): string {
		return 'checkout';
	}

	/** Feature name in the admin menu and on the dashboard. */
	public function title(): string {
		return __( 'Checkout form', 'lenz-plus' );
	}

	/** One-line summary on the dashboard card. */
	public function description(): string {
		return __( 'Choose which billing fields the checkout asks for, give download, virtual and course orders a short form and accept phone numbers and postcodes typed in Persian digits.', 'lenz-plus' );
	}

	/** Semantic icon key for the admin menu and the dashboard card. */
	public function icon(): string {
		return 'receipt';
	}

	/** Default settings (see Schema). */
	public function defaults(): array {
		return Schema::defaults();
	}

	/** Cleans posted settings with the schema. */
	public function sanitize( array $input ): array {
		return Sanitizer::apply( Schema::fields(), $input, Schema::defaults() );
	}

	/** Field rules and digit fixes; nothing without WooCommerce. */
	protected function boot(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		( new Checkout_Form( $this ) )->register();
		add_filter( 'lenz_plus_thanks_texts', array( $this, 'thanks_texts' ) );

		if ( $this->settings()['latin_digits'] ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		}
	}

	/**
	 * The order-received texts set in the panel; empty ones keep the built-in
	 * text and a lone dash shows nothing.
	 *
	 * @param array<string, string> $texts Texts by scenario.
	 * @return array<string, string>
	 */
	public function thanks_texts( $texts ) {
		foreach ( $this->settings()['thanks'] as $key => $text ) {
			$text = trim( (string) $text );
			if ( '' !== $text ) {
				// A lone dash leaves that line out (an empty field means "built-in text").
				$texts[ $key ] = '-' === $text ? '' : $text;
			}
		}

		return $texts;
	}

	/** The typing helper on the checkout and account pages (classic forms). */
	public function enqueue(): void {
		if ( ! is_checkout() && ! is_account_page() ) {
			return;
		}

		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			Asset::url( 'assets/modules/checkout/js/checkout.js' ),
			array(),
			LENZ_PLUS_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	/** Prints the settings panel. */
	public function render_admin(): void {
		$module = $this;
		require __DIR__ . '/views/admin.php';
	}
}
