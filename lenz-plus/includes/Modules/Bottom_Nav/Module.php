<?php
/**
 * Mobile bottom navigation module.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Bottom_Nav;

use LenzPlus\Core\Icon_Library;
use LenzPlus\Core\Module as Base_Module;
use LenzPlus\Core\Sanitizer;
use LenzPlus\Core\Theme_Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * Settings, admin panel and boot of the bottom navigation.
 */
final class Module extends Base_Module {

	public function id(): string {
		return 'bottom_nav';
	}

	public function title(): string {
		return __( 'Mobile bottom navigation', 'lenz-plus' );
	}

	public function description(): string {
		return __( 'An app-like tab bar for phones with five styles, live cart count and fully custom buttons.', 'lenz-plus' );
	}

	public function icon(): string {
		return 'grid';
	}

	public function defaults(): array {
		return Schema::defaults();
	}

	public function sanitize( array $input ): array {
		$clean          = Sanitizer::apply( Schema::fields(), $input, Schema::defaults() );
		$clean['items'] = $this->sanitize_items( $clean['items'] );

		return $clean;
	}

	protected function boot(): void {
		( new Frontend( $this ) )->register();
		( new Live_Search( $this ) )->register();
	}

	/**
	 * Fills keys added in newer plugin versions into previously saved items
	 * and drops keys that no longer exist.
	 *
	 * @param array $settings Settings merged with defaults.
	 */
	protected function normalize( array $settings ): array {
		$item_defaults = Schema::item_defaults();

		$settings['items'] = array_map(
			static function ( $item ) use ( $item_defaults ) {
				return array_merge( $item_defaults, array_intersect_key( is_array( $item ) ? $item : array(), $item_defaults ) );
			},
			is_array( $settings['items'] ) ? $settings['items'] : array()
		);

		return $settings;
	}

	public function render_admin(): void {
		$module = $this;
		require __DIR__ . '/views/admin.php';
	}

	public function enqueue_admin_assets(): void {
		wp_enqueue_media();

		// The preview renders with the real front-end stylesheet.
		wp_enqueue_style( 'lzp-bottom-nav', LENZ_PLUS_URL . 'assets/modules/bottom-nav/css/bottom-nav.css', array(), LENZ_PLUS_VERSION );

		if ( Theme_Bridge::is_active() ) {
			// The theme's icon fonts, for the "Font Awesome" and "Lenz icons" packs in previews.
			wp_enqueue_style( 'lzp-theme-fontawesome', get_template_directory_uri() . '/assets/libs/fontawesome/css/fa.min.css', array(), LENZ_PLUS_VERSION );
			wp_enqueue_style( 'lzp-theme-icons', get_template_directory_uri() . '/assets/css/lenz-icons.min.css', array(), LENZ_PLUS_VERSION );

			// The font chosen in Lenz → Typography (sanitized: no dots or slashes), so labels look as on the site.
			$font = Theme_Bridge::font();
			if ( is_readable( get_template_directory() . '/assets/css/fonts/' . $font . '.min.css' ) ) {
				wp_enqueue_style( 'lzp-theme-font', get_template_directory_uri() . '/assets/css/fonts/' . rawurlencode( $font ) . '.min.css', array(), LENZ_PLUS_VERSION );
			}
		}

		wp_enqueue_script(
			'lzp-bottom-nav-admin',
			LENZ_PLUS_URL . 'assets/modules/bottom-nav/js/bottom-nav-admin.js',
			array( 'lzp-admin', 'jquery-ui-sortable' ),
			LENZ_PLUS_VERSION,
			true
		);
	}

	public function admin_script_data(): array {
		$settings = $this->settings();

		return array(
			'styles'       => Styles::all(),
			'itemTypes'    => $this->available_item_types(),
			'itemDefaults' => Schema::item_defaults(),
			'maxItems'     => Schema::MAX_ITEMS,
			'colorSlots'   => Schema::COLOR_SLOTS,
			'iconPack'     => array(
				// Preloaded so the first preview paint needs no request.
				'id'    => $settings['icon_pack'],
				'icons' => Icon_Library::is_font_pack( $settings['icon_pack'] ) ? array() : Icon_Library::pack( $settings['icon_pack'] ),
			),
			'iconPackUrl'  => LENZ_PLUS_URL . 'assets/icons/packs/',
			'catalog'      => Icon_Library::catalog(),
			'theme'        => array(
				'active'  => Theme_Bridge::is_active(),
				'palette' => Theme_Bridge::palette(),
				'font'    => Theme_Bridge::font(),
				'reserve' => Theme_Bridge::reserve_link(),
			),
			'hasWoo'       => class_exists( 'WooCommerce' ),
			'avatarUrl'    => (string) get_avatar_url( get_current_user_id(), array( 'size' => 64 ) ),
			'menus'        => $this->menu_choices(),
			'templates'    => $this->elementor_template_choices(),
			'postTypes'    => Search_Scope::choices(),
			'archiveTypes' => Archive_Types::choices(),
			'i18n'         => $this->admin_strings(),
		);
	}

	/** Strings used by bottom-nav-admin.js. */
	private function admin_strings(): array {
		return array(
			'needsWoo'      => __( 'Requires WooCommerce', 'lenz-plus' ),
			'needsUrl'      => __( 'Add a URL', 'lenz-plus' ),
			'needsMenu'     => __( 'Choose a menu', 'lenz-plus' ),
			'needsContent'  => __( 'Add content', 'lenz-plus' ),
			'needsReserve'  => __( 'Add a booking URL', 'lenz-plus' ),
			'needsArchive'  => __( 'Choose a content type', 'lenz-plus' ),
			'needsSelector' => __( 'Add a selector or URL', 'lenz-plus' ),
			'hiddenOnSite'  => __( 'This button stays hidden on the site until this is fixed.', 'lenz-plus' ),
			'guestsOnly'    => __( 'Guests only', 'lenz-plus' ),
			'membersOnly'   => __( 'Members only', 'lenz-plus' ),
			'login'         => __( 'Login', 'lenz-plus' ),
			'deleteTitle'   => __( 'Delete this button?', 'lenz-plus' ),
			/* translators: %s: button label. */
			'deleteMessage' => __( '"%s" will be removed from the bar.', 'lenz-plus' ),
			'delete'        => __( 'Delete', 'lenz-plus' ),
			/* translators: %d: maximum number of buttons. */
			'maxReached'    => sprintf( __( 'You can add up to %d buttons.', 'lenz-plus' ), Schema::MAX_ITEMS ),
			'chooseImage'   => __( 'Choose an icon image', 'lenz-plus' ),
			'useImage'      => __( 'Use this image', 'lenz-plus' ),
			'noIcons'       => __( 'No icons match your search.', 'lenz-plus' ),
			'noneOption'    => __( '— Select —', 'lenz-plus' ),
		);
	}

	/**
	 * Guarantees unique ids and a single featured item.
	 *
	 * @param array $items Sanitized items.
	 */
	private function sanitize_items( array $items ): array {
		$seen_ids     = array();
		$has_featured = false;

		foreach ( $items as &$item ) {
			if ( '' === $item['id'] || isset( $seen_ids[ $item['id'] ] ) ) {
				$item['id'] = Schema::new_item_id();
			}
			$seen_ids[ $item['id'] ] = true;

			if ( $item['featured'] ) {
				$item['featured'] = ! $has_featured;
				$has_featured     = true;
			}
		}
		unset( $item );

		return $items;
	}

	/** Item types whose dependencies are met, for the admin "add button" menu. */
	private function available_item_types(): array {
		$types = array();
		foreach ( Item_Types::all() as $id => $type ) {
			$type['available'] = Item_Types::is_available( $id );
			$types[ $id ]      = $type;
		}

		return $types;
	}

	/** @return array<int, array{id:int, name:string}> */
	private function menu_choices(): array {
		return array_map(
			static function ( $menu ) {
				return array(
					'id'   => (int) $menu->term_id,
					'name' => $menu->name,
				);
			},
			wp_get_nav_menus()
		);
	}

	/**
	 * Elementor library templates plus pages, for the "content sheet" type.
	 *
	 * @return array<int, array{id:int, name:string}>
	 */
	private function elementor_template_choices(): array {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return array();
		}

		$posts = get_posts(
			array(
				'post_type'      => array( 'elementor_library', 'page' ),
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'meta_key'       => '_elementor_edit_mode', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- admin only, bounded.
				'meta_value'     => 'builder', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		return array_map(
			static function ( $post ) {
				return array(
					'id'   => (int) $post->ID,
					'name' => get_the_title( $post ) . ( 'page' === $post->post_type ? ' — ' . __( 'page', 'lenz-plus' ) : '' ),
				);
			},
			$posts
		);
	}
}
