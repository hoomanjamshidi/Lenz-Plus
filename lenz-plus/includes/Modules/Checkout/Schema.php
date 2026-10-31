<?php
/**
 * Settings schema and defaults for the checkout form module.
 *
 * Field modes are keyed by WooCommerce's billing field name without the
 * `billing_` prefix, plus `notes` (the order notes), so admin selects bind by
 * path (`fields.phone`, `quick_fields.city`).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Checkout;

defined( 'ABSPATH' ) || exit;

/**
 * Field list, modes, settings shape and defaults.
 */
final class Schema {

	/** Billing fields the settings control, in WooCommerce's form order, plus the order notes. */
	public const FIELDS = array( 'first_name', 'last_name', 'company', 'country', 'state', 'city', 'address_1', 'address_2', 'postcode', 'phone', 'email', 'notes' );

	/** default: as WooCommerce and other plugins set it; required / optional; hidden: left out of the form. */
	public const MODES = array( 'default', 'required', 'optional', 'hidden' );

	/**
	 * The short form of orders with nothing to ship (downloads, virtual
	 * products, courses): who is buying and how to reach them, no address.
	 */
	public const QUICK_DEFAULTS = array(
		'first_name' => 'required',
		'last_name'  => 'required',
		'company'    => 'hidden',
		'country'    => 'hidden',
		'state'      => 'hidden',
		'city'       => 'hidden',
		'address_1'  => 'hidden',
		'address_2'  => 'hidden',
		'postcode'   => 'hidden',
		'phone'      => 'required',
		'email'      => 'required',
		'notes'      => 'hidden',
	);

	/** Scenarios of the order-received page whose text can be changed (Builder\Shop_Pages::default_thanks_texts()). */
	public const THANKS_TEXTS = array( 'title', 'course', 'download', 'paid', 'on_hold', 'pending', 'email' );

	/**
	 * Every setting's default. General fields start on "default", so switching
	 * the module on changes nothing in the form until a field is set.
	 */
	public static function defaults(): array {
		return array(
			'enabled'      => true,
			'fields'       => array_fill_keys( self::FIELDS, 'default' ),
			'quick'        => true,
			'quick_fields' => self::QUICK_DEFAULTS,
			'latin_digits' => true,
			// Empty = the built-in text of that scenario.
			'thanks'       => array_fill_keys( self::THANKS_TEXTS, '' ),
		);
	}

	/**
	 * Sanitizer schema with the same shape as defaults().
	 */
	public static function fields(): array {
		$mode = array(
			'type'    => 'enum',
			'options' => self::MODES,
		);

		return array(
			'enabled'      => array( 'type' => 'bool' ),
			'fields'       => array(
				'type'   => 'group',
				'fields' => array_fill_keys( self::FIELDS, $mode ),
			),
			'quick'        => array( 'type' => 'bool' ),
			'quick_fields' => array(
				'type'   => 'group',
				'fields' => array_fill_keys( self::FIELDS, $mode ),
			),
			'latin_digits' => array( 'type' => 'bool' ),
			'thanks'       => array(
				'type'   => 'group',
				'fields' => array_fill_keys(
					self::THANKS_TEXTS,
					array(
						'type'       => 'text',
						'max_length' => 400,
					)
				),
			),
		);
	}
}
