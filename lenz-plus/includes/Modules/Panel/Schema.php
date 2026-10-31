<?php
/**
 * Settings schema and defaults for the studio panel.
 *
 * Roles are a group of switches keyed by role name, built from the roles the
 * site has when the settings are read, so roles added by other plugins show
 * up by themselves. Sections and editors are fixed groups keyed by id.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel;

defined( 'ABSPATH' ) || exit;

/**
 * Sections, editors, settings shape and defaults.
 */
final class Schema {

	/** Sections of the panel that can be switched off, in menu order. */
	public const SECTIONS = array( 'posts', 'portfolio', 'pages', 'courses', 'products', 'orders', 'bookings', 'requests', 'comments', 'media' );

	/** Content types whose text editor can be chosen. */
	public const EDITOR_TYPES = array( 'post', 'page', 'portfolio', 'product' );

	/**
	 * simple: the panel's own editor; classic: WordPress's TinyMCE inside the
	 * panel; plain: a text box (HTML allowed); WordPress: the text is edited in
	 * WordPress's own editor (wp-admin), the panel keeps the other fields.
	 */
	public const EDITORS = array( 'simple', 'classic', 'plain', 'wordpress' );

	/** Roles that can open the panel until the admin changes them. */
	public const DEFAULT_ROLES = array( 'administrator', Access::ROLE, 'shop_manager' );

	/** Default address of the panel: site.com/panel/. */
	public const DEFAULT_SLUG = 'panel';

	/** Every setting's default. */
	public static function defaults(): array {
		return array(
			'enabled'        => true,
			'slug'           => self::DEFAULT_SLUG,
			'title'          => '',
			'roles'          => self::role_defaults(),
			'sections'       => array_fill_keys( self::SECTIONS, true ),
			'editor'         => array_fill_keys( self::EDITOR_TYPES, 'simple' ),
			'hide_admin'     => false,
			'login_redirect' => false,
			'account_link'   => true,
			'admin_bar_link' => true,
			'per_page'       => 20,
		);
	}

	/**
	 * Sanitizer schema with the same shape as defaults().
	 */
	public static function fields(): array {
		$editor = array(
			'type'    => 'enum',
			'options' => self::EDITORS,
		);

		return array(
			'enabled'        => array( 'type' => 'bool' ),
			'slug'           => array( 'type' => 'key' ),
			'title'          => array(
				'type'       => 'text',
				'max_length' => 60,
			),
			'roles'          => array(
				'type'   => 'group',
				'fields' => array_fill_keys( array_keys( self::role_defaults() ), array( 'type' => 'bool' ) ),
			),
			'sections'       => array(
				'type'   => 'group',
				'fields' => array_fill_keys( self::SECTIONS, array( 'type' => 'bool' ) ),
			),
			'editor'         => array(
				'type'   => 'group',
				'fields' => array_fill_keys( self::EDITOR_TYPES, $editor ),
			),
			'hide_admin'     => array( 'type' => 'bool' ),
			'login_redirect' => array( 'type' => 'bool' ),
			'account_link'   => array( 'type' => 'bool' ),
			'admin_bar_link' => array( 'type' => 'bool' ),
			'per_page'       => array(
				'type' => 'int',
				'min'  => 5,
				'max'  => 60,
			),
		);
	}

	/**
	 * Every role of the site → whether it opens the panel by default.
	 *
	 * @return array<string, bool>
	 */
	public static function role_defaults(): array {
		$roles = array();
		foreach ( array_keys( wp_roles()->get_names() ) as $role ) {
			$roles[ $role ] = in_array( $role, self::DEFAULT_ROLES, true );
		}

		return $roles;
	}
}
