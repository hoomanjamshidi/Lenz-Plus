<?php
/**
 * Decides which template a request uses and builds preview links.
 *
 * Page designs need no decision: pages made from them carry their own
 * Elementor content. Headers and footers get one slot per device. Admins can
 * force any header or footer through nonce-protected preview parameters,
 * which is how the admin previews work before anything is saved. Later kinds
 * (portfolio, blog and course pages) add their rules here.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Template choice for the current request, and admin preview URLs.
 */
final class Resolver {

	public const PREVIEW_NONCE = 'lzp_builder_preview';

	/** @var Module */
	private $module;

	/** @var array<string, ?string>|null Memoized preview parameters, by area. */
	private $preview = null;

	/**
	 * @param Module $module Owning module.
	 */
	public function __construct( Module $module ) {
		$this->module = $module;
	}

	/**
	 * Resolved header/footer slots for this request.
	 *
	 * @param string $area `header` or `footer`.
	 * @return array{desktop: int|string, mobile: int|string} Template ID, `theme` or `none`.
	 */
	public function slots( string $area ): array {
		$preview = $this->preview()[ $area ];

		if ( null !== $preview ) {
			// A forced preview shows the same template at every width.
			$desktop = $preview;
			$mobile  = 'same';
		} elseif ( $this->module->is_enabled() ) {
			$settings = $this->module->settings()[ $area ];
			$desktop  = $settings['desktop'];
			$mobile   = $settings['mobile'];
		} else {
			$desktop = 'theme';
			$mobile  = 'same';
		}

		$desktop = $this->to_slot( $desktop, $area );

		return array(
			'desktop' => $desktop,
			'mobile'  => 'same' === $mobile ? $desktop : $this->to_slot( $mobile, $area ),
		);
	}

	/** Whether this request is an admin preview. */
	public function is_preview(): bool {
		return array() !== array_filter(
			$this->preview(),
			static function ( $value ) {
				return null !== $value;
			}
		);
	}

	/**
	 * Where the admin previews a template (or a keyword such as `theme`).
	 *
	 * @param string $type Template kind.
	 * @param string $ref  Template ID or keyword.
	 */
	public function preview_url( string $type, string $ref ): string {
		if ( in_array( $type, Schema::PAGE_TYPES, true ) ) {
			// A page design is a whole page: its own URL shows it between the site's header and footer.
			return is_numeric( $ref ) ? (string) get_permalink( (int) $ref ) : '';
		}

		if ( in_array( $type, Schema::AREAS, true ) ) {
			return add_query_arg(
				array(
					'lzp_preview_' . $type => $ref,
					'_lzpnonce'            => wp_create_nonce( self::PREVIEW_NONCE ),
				),
				home_url( '/' )
			);
		}

		return '';
	}

	/**
	 * @param string $ref  Stored reference.
	 * @param string $area `header` or `footer`.
	 * @return int|string Template ID, `theme` or `none`.
	 */
	private function to_slot( string $ref, string $area ) {
		if ( 'none' === $ref ) {
			return 'none';
		}

		$id = is_numeric( $ref ) ? (int) $ref : 0;

		// A deleted or unpublished template falls back to the theme, never to an empty page top.
		return Template_Post_Type::is_usable( $id, array( $area ) ) ? $id : 'theme';
	}

	/**
	 * Reads the preview parameters once. They only count for users who can
	 * edit templates and carry a valid nonce, so shared links do nothing.
	 *
	 * @return array<string, ?string> Area → forced reference, or null.
	 */
	private function preview(): array {
		if ( null !== $this->preview ) {
			return $this->preview;
		}

		$this->preview = array_fill_keys( Schema::AREAS, null );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- verified right below.
		$requested = array();
		foreach ( Schema::AREAS as $area ) {
			if ( isset( $_GET[ 'lzp_preview_' . $area ] ) ) {
				$requested[ $area ] = sanitize_key( wp_unslash( $_GET[ 'lzp_preview_' . $area ] ) );
			}
		}

		$nonce = isset( $_GET['_lzpnonce'] ) ? sanitize_key( wp_unslash( $_GET['_lzpnonce'] ) ) : '';
		// phpcs:enable

		if ( $requested && Module::user_can_manage() && wp_verify_nonce( $nonce, self::PREVIEW_NONCE ) ) {
			$this->preview = array_merge( $this->preview, $requested );
		}

		return $this->preview;
	}
}
