<?php
/**
 * Courses: WooCommerce products flagged by the plugin's «Course details
 * (Lenz+)» box. Reads what the course widgets show (status, format,
 * schedule, capacity, the learning lists, curriculum, FAQ, instructor) and
 * decides what the buy buttons do.
 *
 * Statuses: `open` (registration open), `soon` (coming soon: a waitlist
 * instead of a price) and `archive` (already held). Registration is either
 * WooCommerce's cart (straight to the checkout) or the request form on the
 * page (`#enroll`). Seats left come from WooCommerce stock when the product
 * manages it, else from the box.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

use LenzPlus\Modules\Builder\Elementor\Widgets\Base;

defined( 'ABSPATH' ) || exit;

/**
 * Static readers of course products.
 */
final class Course_Data {

	/** Flag: '1' on products that are courses (a separate key so lists can query it). */
	public const META_FLAG = '_lzp_is_course';

	/** Course details (one array). */
	public const META = '_lzp_course';

	public const STATUSES = array( 'open', 'soon', 'archive' );

	public const FORMATS = array( 'offline', 'online' );

	public const REGISTRATIONS = array( 'cart', 'form' );

	/** Seats left at or below which cards say «۴ ظرفیت باقی‌مانده» instead of «در حال ثبت‌نام». */
	private const FEW_SEATS = 5;

	/** Whether WooCommerce is active. */
	public static function has_woo(): bool {
		return class_exists( '\WooCommerce' ) && function_exists( 'wc_get_product' );
	}

	/**
	 * Whether a product is a course.
	 *
	 * @param int $product_id Product ID.
	 */
	public static function is_course( int $product_id ): bool {
		return $product_id > 0 && 'product' === get_post_type( $product_id ) && '1' === get_post_meta( $product_id, self::META_FLAG, true );
	}

	/**
	 * Empty course details.
	 *
	 * @return array<string, mixed>
	 */
	public static function empty_details(): array {
		return array(
			'status'        => 'open',
			'kind'          => '',
			'topic'         => '',
			'level'         => '',
			'format'        => 'offline',
			'place'         => '',
			'duration'      => '',
			'schedule'      => '',
			'year'          => '',
			'capacity'      => 0,
			'seats_left'    => '',
			'tools'         => '',
			// [label, value] rows added to the specs.
			'specs'         => array(),
			'learn'         => array(),
			'audience'      => array(),
			'audience_note' => '',
			// [title, description] of each session.
			'curriculum'    => array(),
			'outcome'       => '',
			// Question and answer pairs.
			'faq'           => array(),
			'expert'        => 0,
			'registration'  => 'cart',
			'video'         => '',
		);
	}

	/**
	 * Course details of a product.
	 *
	 * @param int $product_id Product ID.
	 */
	public static function details( int $product_id ): array {
		$saved = get_post_meta( $product_id, self::META, true );

		return array_merge( self::empty_details(), is_array( $saved ) ? array_intersect_key( $saved, self::empty_details() ) : array() );
	}

	/**
	 * Course products, newest first.
	 *
	 * @param int      $count    How many (-1 for all).
	 * @param string[] $statuses Statuses to keep (empty = all).
	 * @return int[]
	 */
	public static function course_ids( int $count = -1, array $statuses = array() ): array {
		if ( ! self::has_woo() ) {
			return array();
		}

		$ids = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => $count,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => self::META_FLAG, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- the course flag; a handful of products.
				'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		if ( $statuses ) {
			$ids = array_values(
				array_filter(
					$ids,
					static function ( $id ) use ( $statuses ) {
						return in_array( self::details( (int) $id )['status'], $statuses, true );
					}
				)
			);
		}

		return array_map( 'intval', $ids );
	}

	/**
	 * Seats left, or null when the course does not count them.
	 *
	 * @param int $product_id Product ID.
	 */
	public static function seats_left( int $product_id ): ?int {
		$product = self::has_woo() ? wc_get_product( $product_id ) : null;
		if ( $product && $product->managing_stock() ) {
			return max( 0, (int) $product->get_stock_quantity() );
		}

		$left = self::details( $product_id )['seats_left'];

		return '' === $left || null === $left ? null : max( 0, (int) $left );
	}

	/**
	 * «حضوری، مشهد» / «آنلاین».
	 *
	 * @param array $details Course details.
	 */
	public static function format_label( array $details ): string {
		$format = 'online' === $details['format'] ? __( 'Online', 'lenz-plus' ) : __( 'In person', 'lenz-plus' );

		/* translators: 1: "In person", 2: city or venue. */
		return '' !== $details['place'] ? sprintf( __( '%1$s, %2$s', 'lenz-plus' ), $format, $details['place'] ) : $format;
	}

	/**
	 * The card's badge: seats left when few, else the status.
	 *
	 * @param int $product_id Product ID.
	 */
	public static function badge( int $product_id ): string {
		$details = self::details( $product_id );

		if ( 'soon' === $details['status'] ) {
			return __( 'Coming soon', 'lenz-plus' );
		}

		if ( 'archive' === $details['status'] ) {
			return '' !== $details['year'] ? $details['year'] : __( 'Held', 'lenz-plus' );
		}

		$left = self::seats_left( $product_id );
		if ( 0 === $left ) {
			return __( 'Full', 'lenz-plus' );
		}
		if ( null !== $left && $left <= self::FEW_SEATS ) {
			/* translators: %s: number of seats left. */
			return sprintf( __( '%s seats left', 'lenz-plus' ), Base::num( $left ) );
		}

		return __( 'Registration open', 'lenz-plus' );
	}

	/**
	 * Price markup with the site's digits ('' when there is no price).
	 *
	 * @param int $product_id Product ID.
	 */
	public static function price_html( int $product_id ): string {
		$product = self::has_woo() ? wc_get_product( $product_id ) : null;
		$html    = $product ? (string) $product->get_price_html() : '';

		return '' !== $html ? Base::digits_html( $html ) : '';
	}

	/**
	 * What the main button does.
	 *
	 * @param int $product_id Product ID.
	 * @return array{type:string, label:string, url:string, product:int} Type `cart` (add to cart and
	 *     go to the checkout), `link`, or `none` (a disabled button).
	 */
	public static function action( int $product_id ): array {
		$details = self::details( $product_id );
		$action  = array(
			'type'    => 'link',
			'label'   => '',
			'url'     => '',
			'product' => $product_id,
		);

		if ( 'open' !== $details['status'] ) {
			$action['label'] = __( 'Let me know', 'lenz-plus' );
			$action['url']   = '#waitlist';

			return $action;
		}

		if ( 0 === self::seats_left( $product_id ) ) {
			$action['type']  = 'none';
			$action['label'] = __( 'Fully booked', 'lenz-plus' );

			return $action;
		}

		if ( 'form' === $details['registration'] || ! self::has_woo() ) {
			$action['label'] = __( 'Register', 'lenz-plus' );
			$action['url']   = '#enroll';

			return $action;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			$action['type']  = 'none';
			$action['label'] = __( 'Fully booked', 'lenz-plus' );

			return $action;
		}

		if ( WC()->cart && self::in_cart( $product_id ) ) {
			$action['label'] = __( 'In your cart · Checkout', 'lenz-plus' );
			$action['url']   = wc_get_checkout_url();

			return $action;
		}

		$action['type']  = 'cart';
		$action['label'] = __( 'Register', 'lenz-plus' );
		$action['url']   = wc_get_checkout_url();

		return $action;
	}

	/**
	 * Whether the visitor's cart holds the course.
	 *
	 * @param int $product_id Product ID.
	 */
	private static function in_cart( int $product_id ): bool {
		foreach ( WC()->cart->get_cart() as $item ) {
			if ( (int) ( $item['product_id'] ?? 0 ) === $product_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * The main button's markup: a form that adds the course to the cart and
	 * opens the checkout, a link, or a disabled button.
	 *
	 * @param array  $action    From action().
	 * @param string $classes   Button classes.
	 * @param string $label     Label override ('' keeps the action's).
	 * @param string $icon_html Trusted icon markup after the label.
	 */
	public static function button_html( array $action, string $classes, string $label = '', string $icon_html = '' ): string {
		$text = '<span>' . esc_html( '' !== $label && 'cart' === $action['type'] ? $label : $action['label'] ) . '</span>' . $icon_html;

		if ( 'cart' === $action['type'] ) {
			// Posting add-to-cart to the checkout page adds the course and shows the checkout in one step.
			return sprintf(
				'<form class="lzp-course-buy" method="post" action="%1$s"><input type="hidden" name="add-to-cart" value="%2$d"><button type="submit" class="%3$s">%4$s</button></form>',
				esc_url( $action['url'] ),
				(int) $action['product'],
				esc_attr( $classes ),
				$text
			);
		}

		if ( 'none' === $action['type'] ) {
			return '<button type="button" class="' . esc_attr( $classes ) . '" disabled>' . $text . '</button>';
		}

		return '<a class="' . esc_attr( $classes ) . '" href="' . esc_url( $action['url'] ) . '">' . $text . '</a>';
	}

	/**
	 * Instructor of a course: Lenz's `expert` post (name, position, bio,
	 * photo), or null when none is chosen.
	 *
	 * @param int $product_id Product ID.
	 * @return array{name:string, role:string, bio:string, photo:int}|null
	 */
	public static function instructor( int $product_id ): ?array {
		$expert = get_post( (int) self::details( $product_id )['expert'] );
		if ( ! $expert || 'expert' !== $expert->post_type || 'publish' !== $expert->post_status ) {
			return null;
		}

		$bio = '' !== $expert->post_excerpt ? $expert->post_excerpt : wp_trim_words( wp_strip_all_tags( $expert->post_content ), 40 );

		return array(
			'name'  => get_the_title( $expert ),
			'role'  => (string) get_post_meta( $expert->ID, '_position', true ),
			'bio'   => $bio,
			'photo' => (int) get_post_thumbnail_id( $expert ),
		);
	}
}
