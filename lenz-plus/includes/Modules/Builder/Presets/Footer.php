<?php
/**
 * The mockups' site footers, on the ink band.
 *
 * The full footer (home page) has the brand box with a description, both of
 * Lenz's footer menus and the contact card; the compact one (about and
 * project pages) drops the description and the first menu. The store
 * footer is the full one with a row of trust badges (eNamad, Zarinpal)
 * above the copyright line, for sites that sell courses. Columns are
 * 210px wide at least and share the rest, like the mockups'
 * `repeat(auto-fit, minmax(210px, 1fr))` grid.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Presets;

defined( 'ABSPATH' ) || exit;

/**
 * Footer preset builders.
 */
final class Footer {

	/** Latin line under the name, as in the mockups. */
	private const TAGLINE = 'PHOTO & VIDEO';

	/** Brand box with a description, two link columns and the contact card. */
	public static function full(): array {
		return self::footer( true );
	}

	/** Brand box, quick links and the contact card. */
	public static function compact(): array {
		return self::footer( false );
	}

	/** The full footer with the trust badges row. */
	public static function store(): array {
		return self::footer( true, true );
	}

	/**
	 * @param bool $full  Whether the description and the first menu are included.
	 * @param bool $trust Whether the trust badges row is included.
	 */
	private static function footer( bool $full, bool $trust = false ): array {
		$columns = array(
			self::column(
				El::w(
					'lzp-brand-box',
					array(
						'sub'        => self::TAGLINE,
						'show_about' => $full ? 'yes' : '',
					)
				)
			),
			$full ? self::column( El::w( 'lzp-link-column', array( 'source' => 'footer-1' ) ) ) : null,
			self::column( El::w( 'lzp-link-column', array( 'source' => 'footer-2' ) ) ),
			self::column( El::w( 'lzp-contact-box' ) ),
		);

		return array(
			El::box(
				array(
					'surface'    => 'ink',
					'tag'        => 'footer',
					'boxed'      => 1200,
					'gap'        => 32,
					'pad'        => array( 56, 40, 24 ),
					'pad_tablet' => array( 48, 24, 24 ),
					'pad_mobile' => array( 40, 16, 24 ),
				),
				array(
					El::box(
						array(
							'dir'        => 'row',
							'dir_mobile' => 'column',
							'wrap'       => 'wrap',
							'gap'        => array( 32, 40 ),
						),
						$columns
					),
					$trust ? self::trust_row() : null,
					El::w( 'lzp-copyright' ),
				)
			),
		);
	}

	/** Trust badges beside a line about safe payment, between hairlines. */
	private static function trust_row(): array {
		return El::box(
			array(
				'dir'        => 'row',
				'dir_mobile' => 'column',
				'align'      => 'center',
				'justify'    => 'space-between',
				'gap'        => 20,
				'pad'        => array( 22, 0 ),
				'border'     => array( array( 1, 0, 0, 0 ), '#1a4a6e' ),
			),
			array(
				El::w(
					'lzp-text',
					array(
						'content'   => '<p><strong>خرید امن و مطمئن</strong><br>پرداخت آنلاین دوره‌ها با درگاه امن زرین‌پال؛ نمادهای اعتماد را برای اطمینان بررسی کنید.</p>',
						'tone'      => 'muted',
						'max_width' => El::px( 460 ),
					),
					array( 'fill' => true )
				),
				El::w(
					'lzp-trust-badges',
					array(
						'title' => '',
						'items' => Blocks::rows(
							array(
								array( 'type' => 'enamad' ),
								array( 'type' => 'samandehi' ),
								array( 'type' => 'zarinpal' ),
							)
						),
					)
				),
			)
		);
	}

	/**
	 * A column that is 210px at least and grows to share the row.
	 *
	 * @param array $widget Widget.
	 */
	private static function column( array $widget ): array {
		return El::box(
			array(
				'width'        => '210px',
				'width_mobile' => 100,
				'fill'         => true,
			),
			array( $widget )
		);
	}
}
