<?php
/**
 * Pages about a single service, in three looks built from the services
 * page's pieces: a photo hero with cards, samples and price cards; the
 * service on the ink band beside a booking form without the project step;
 * and a narrow centred page with a price list and the request form in the
 * ink frame.
 *
 * Every look takes a service key (Service_Content): the template library
 * shows the sample service, and Service_Pages builds one page per service
 * with that service's own copy. Images are left empty so the frames show
 * which photo goes where. The price band carries the HTML id `packages` and
 * the forms `book`, for the hero buttons.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Presets;

defined( 'ABSPATH' ) || exit;

/**
 * Single service page preset builders.
 */
final class Service {

	/**
	 * Photo hero, key numbers, what is included, samples, price cards, steps, reviews, questions and a call to action.
	 *
	 * @param string $key Service key (Service_Content).
	 */
	public static function classic( string $key = Service_Content::SAMPLE ): array {
		$c = Service_Content::get( $key );

		return array(
			self::hero( $c, 'split' ),
			self::stats( $c ),
			Blocks::band(
				array(
					Blocks::heading( 'در این سرویس چه می‌گیری؟', 'همه چیز از قبل در قرارداد نوشته می‌شود؛ بدون هزینه پنهان' ),
					El::w(
						'lzp-card-grid',
						array(
							'columns'        => '4',
							'columns_tablet' => '2',
							'columns_mobile' => '1',
							'items'          => Blocks::rows( array_map( array( self::class, 'card_row' ), $c['cards'] ) ),
						)
					),
				)
			),
			self::samples( $c, array() ),
			self::packages( $c ),
			self::steps(),
			self::testimonials( $c ),
			self::faq( $c, array() ),
			self::cta( $c ),
		);
	}

	/**
	 * Text hero, the service on the ink band, what is included beside a review, samples, prices, the booking form and questions.
	 *
	 * @param string $key Service key (Service_Content).
	 */
	public static function ink( string $key = Service_Content::SAMPLE ): array {
		$c = Service_Content::get( $key );

		return array(
			self::hero( $c, 'text' ),
			Blocks::band(
				array(
					El::w(
						'lzp-service-detail',
						array(
							'index'          => '',
							'title'          => $c['detail']['title'],
							'latin'          => $c['latin'],
							'text'           => $c['detail']['text'],
							'chips_title'    => 'مناسب برای',
							'chips'          => Blocks::rows( self::lines( $c['detail']['chips'] ) ),
							'primary_text'   => 'رزرو همین سرویس',
							'primary_link'   => Blocks::link( '#book' ),
							'secondary_text' => 'دیدن تعرفه‌ها',
							'secondary_link' => Blocks::link( '#packages' ),
							'media'          => 'pair',
							'label_1'        => $c['detail']['label_1'],
							'label_2'        => $c['detail']['label_2'],
						)
					),
				),
				array_merge(
					Blocks::pad_y( 88, 60, 40 ),
					array(
						'surface' => 'ink',
						'set'     => array( 'overflow' => 'hidden' ),
					)
				)
			),
			Blocks::band(
				array(
					Blocks::columns(
						array(
							Blocks::heading( 'در این سرویس چه می‌گیری؟' ),
							El::w( 'lzp-checklist', array( 'items' => Blocks::rows( self::lines( $c['included'] ) ) ) ),
						),
						array(
							Blocks::heading( 'از زبان مشتری' ),
							El::w( 'lzp-quote', self::testimonial_row( $c['testimonials'][0] ) ),
						),
						array( 'gap' => 56 )
					),
				)
			),
			self::samples( $c, array( 'surface' => 'soft' ) ),
			self::packages( $c ),
			Blocks::band(
				array(
					Blocks::heading( 'رزرو همین سرویس', 'تاریخ و جزئیات کار را بنویس؛ با پیشنهاد دقیق قیمت تماس می‌گیرم.', array( 'space_below' => El::px( 28 ) ) ),
					El::w(
						'lzp-booking-form',
						array(
							'look'        => 'split',
							'form_name'   => $c['name'],
							'panel_title' => $c['name'],
							'panel_text'  => 'سه قدم کوتاه؛ تا یک روز کاری برای قطعی کردن تاریخ و قیمت تماس می‌گیرم.',
							// No project step: the page is about one service already.
							'services'    => array(),
						)
					),
				),
				array(
					'surface' => 'soft',
					'set'     => array( '_element_id' => 'book' ),
				)
			),
			self::faq( $c, array() ),
		);
	}

	/**
	 * A centred title, one wide photo, the text beside what is included, a price list, a framed line, questions and the request form.
	 *
	 * @param string $key Service key (Service_Content).
	 */
	public static function minimal( string $key = Service_Content::SAMPLE ): array {
		$c      = Service_Content::get( $key );
		$narrow = static function ( array $pad, array $pad_tablet, array $pad_mobile ): array {
			return array(
				'boxed'      => 1000,
				'pad'        => $pad,
				'pad_tablet' => $pad_tablet,
				'pad_mobile' => $pad_mobile,
			);
		};
		$middle = $narrow( array( 28, 40, 28 ), array( 22, 24, 22 ), array( 18, 16, 18 ) );

		return array(
			Blocks::band(
				array(
					El::w(
						'lzp-heading',
						array(
							'latin'       => $c['latin'],
							'bar'         => '',
							'title'       => $c['name'],
							'subtitle'    => $c['lead'],
							'tag'         => 'h1',
							'align'       => 'center',
							'space_below' => El::px( 30 ),
						)
					),
					El::w(
						'lzp-photo-frame',
						array(
							'ratio' => '21/9',
							'label' => $c['wide_photo'],
						)
					),
				),
				$narrow( array( 64, 40, 28 ), array( 48, 24, 22 ), array( 34, 16, 18 ) )
			),
			Blocks::band(
				array(
					Blocks::columns(
						array(
							Blocks::heading( 'درباره این سرویس' ),
							El::w( 'lzp-text', array( 'content' => '<p>' . implode( '</p><p>', $c['about'] ) . '</p>' ) ),
						),
						array(
							Blocks::heading( 'چه چیزهایی شامل است؟' ),
							El::w(
								'lzp-checklist-grid',
								array(
									'source' => 'custom',
									'look'   => 'list',
									'items'  => Blocks::rows( self::lines( $c['included'] ) ),
								)
							),
						),
						array( 'gap' => 48 )
					),
				),
				$middle
			),
			Blocks::band(
				array(
					Blocks::heading( 'تعرفه', $c['plans_note'] ),
					El::w(
						'lzp-simple-list',
						array(
							'layout'         => 'prices',
							'columns'        => '1',
							'columns_tablet' => '1',
							'columns_mobile' => '1',
							'items'          => Blocks::rows( self::price_rows( $c ) ),
						)
					),
				),
				array_merge( $middle, array( 'set' => array( '_element_id' => 'packages' ) ) )
			),
			Blocks::band(
				array(
					El::w(
						'lzp-framed-band',
						array(
							'label' => 'قول من',
							'text'  => $c['promise'],
						)
					),
				),
				$narrow( array( 40, 40, 40 ), array( 32, 24, 32 ), array( 24, 16, 24 ) )
			),
			self::faq( $c, $middle ),
			Blocks::band(
				array(
					Blocks::heading(
						'سفارش این سرویس',
						'',
						array(
							'align' => 'center',
							'bar'   => '',
						)
					),
					self::request_form( $c ),
				),
				array_merge( $narrow( array( 40, 40, 64 ), array( 32, 24, 44 ), array( 24, 16, 30 ) ), array( 'set' => array( '_element_id' => 'book' ) ) )
			),
		);
	}

	/**
	 * Page hero: with a photo and the booking button (`split`), or text only
	 * with the buttons that jump to the prices and the form on the page.
	 *
	 * @param array  $c      Service content.
	 * @param string $layout `split` or `text`.
	 */
	private static function hero( array $c, string $layout ): array {
		$split    = 'split' === $layout;
		$settings = array(
			'layout'         => $layout,
			'eyebrow'        => 'خدمات',
			'title'          => $c['name'],
			'lead'           => $c['lead'],
			'primary_text'   => $split ? 'رزرو وقت' : 'دیدن تعرفه‌ها',
			'primary_link'   => Blocks::link( $split ? Blocks::booking_url() : '#packages' ),
			'primary_icon'   => $split ? 'calendar' : '',
			'secondary_text' => $split ? 'دیدن تعرفه‌ها' : 'رزرو همین سرویس',
			'secondary_link' => Blocks::link( $split ? '#packages' : '#book' ),
			'watermark'      => $c['watermark'],
		);

		if ( $split ) {
			$settings['ratio']       = '4/3';
			$settings['image_label'] = $c['hero_photo'];
		}

		return Blocks::bleed( array( El::w( 'lzp-page-hero', $settings ) ) );
	}

	/**
	 * Four key numbers about the service.
	 *
	 * @param array $c Service content.
	 */
	private static function stats( array $c ): array {
		$stat = static function ( array $row ): array {
			return array(
				'icon'  => $row[0],
				'value' => $row[1],
				'label' => $row[2],
			);
		};

		return Blocks::band(
			array( El::w( 'lzp-stats', array( 'items' => Blocks::rows( array_map( $stat, $c['stats'] ) ) ) ) ),
			Blocks::pad_y( 48, 36, 24 )
		);
	}

	/**
	 * A "what you get" card from its [icon, title, text] tuple.
	 *
	 * @param array $row Tuple.
	 */
	private static function card_row( array $row ): array {
		return array(
			'icon'  => $row[0],
			'title' => $row[1],
			'text'  => $row[2],
		);
	}

	/**
	 * A testimonial (or the quote widget's settings) from its [text, name, role] tuple.
	 *
	 * @param array $row Tuple.
	 */
	private static function testimonial_row( array $row ): array {
		return array(
			'text' => $row[0],
			'name' => $row[1],
			'role' => $row[2],
		);
	}

	/**
	 * Repeater rows of `text` lines.
	 *
	 * @param string[] $lines Texts.
	 */
	private static function lines( array $lines ): array {
		return array_map(
			static function ( string $text ): array {
				return array( 'text' => $text );
			},
			$lines
		);
	}

	/**
	 * Sample photos with a link to the portfolio.
	 *
	 * @param array $c Service content.
	 * @param array $o Band options.
	 */
	private static function samples( array $c, array $o ): array {
		$photo = static function ( string $caption ): array {
			return array( 'caption' => $caption );
		};

		return Blocks::band(
			array(
				Blocks::heading(
					'نمونه‌کارهای این سرویس',
					'چند فریم از سفارش‌های اخیر',
					array(
						'action_text' => 'همه نمونه‌کارها',
						'action_link' => Blocks::link( Blocks::portfolio_url() ),
						'space_below' => El::px( 28 ),
					)
				),
				El::w( 'lzp-photo-grid', array( 'items' => Blocks::rows( array_map( $photo, $c['samples'] ) ) ) ),
			),
			$o
		);
	}

	/**
	 * Three price cards for the service and the add-ons box.
	 *
	 * @param array $c Service content.
	 */
	private static function packages( array $c ): array {
		$booking = Blocks::link( Blocks::booking_url() );

		$plan = static function ( array $row ) use ( $booking ): array {
			list( $name, $price, $unit, $features, $featured ) = $row;
			$priced = '' !== $unit;

			return array_merge(
				array(
					'name'     => $name,
					'price'    => $price,
					'unit'     => $unit,
					'features' => implode( "\n", $features ),
					'button'   => $priced ? 'رزرو' : 'استعلام قیمت',
					'link'     => $booking,
				),
				$featured ? array(
					'badge'    => 'پرطرفدار',
					'featured' => 'yes',
				) : array()
			);
		};

		return Blocks::band(
			array(
				Blocks::heading( 'تعرفه‌ها', $c['plans_note'] ),
				El::w(
					'lzp-pricing-plans',
					array(
						'columns'        => '3',
						'columns_tablet' => '3',
						'columns_mobile' => '1',
						'items'          => Blocks::rows( array_map( $plan, $c['plans'] ) ),
					)
				),
				El::box(
					array(
						'surface'    => 'dashed',
						'pad'        => 26,
						'pad_tablet' => 22,
						'pad_mobile' => 18,
						'margin'     => array( 26, 0, 0, 0 ),
					),
					array(
						El::w(
							'lzp-simple-list',
							array(
								'list_title'     => 'قابل افزودن',
								'layout'         => 'prices',
								'columns'        => '3',
								'columns_tablet' => '1',
								'columns_mobile' => '1',
								'items'          => Blocks::rows( array_map( array( self::class, 'price_row' ), $c['addons'] ) ),
							)
						),
					)
				),
			),
			array_merge(
				Blocks::pad_y( 72, 52, 36 ),
				array( 'set' => array( '_element_id' => 'packages' ) )
			)
		);
	}

	/**
	 * A price list row from its [text, value] tuple.
	 *
	 * @param array $row Tuple.
	 */
	private static function price_row( array $row ): array {
		return array(
			'text'  => $row[0],
			'value' => $row[1],
		);
	}

	/**
	 * The minimal page's one price list: the plans, then the first add-on.
	 *
	 * @param array $c Service content.
	 */
	private static function price_rows( array $c ): array {
		$rows = array();
		foreach ( $c['plans'] as $plan ) {
			$rows[] = self::price_row( array( $plan[0] . ': ' . $plan[3][0], trim( $plan[1] . ' ' . $plan[2] ) ) );
		}
		$rows[] = self::price_row( $c['addons'][0] );

		return $rows;
	}

	/** The four project steps on the soft band (shared with the about page). */
	private static function steps(): array {
		return Blocks::band(
			array(
				Blocks::heading( 'مسیر سفارش', 'چهار قدم ثابت، از اولین تماس تا تحویل فایل‌ها' ),
				El::w( 'lzp-process-steps', array( 'items' => About::step_items() ) ),
			),
			array( 'surface' => 'soft' )
		);
	}

	/**
	 * Three client reviews of the service.
	 *
	 * @param array $c Service content.
	 */
	private static function testimonials( array $c ): array {
		return Blocks::band(
			array(
				Blocks::heading( 'نظر مشتری‌ها', '', array( 'space_below' => El::px( 28 ) ) ),
				El::w( 'lzp-testimonials', array( 'items' => Blocks::rows( array_map( array( self::class, 'testimonial_row' ), $c['testimonials'] ) ) ) ),
			)
		);
	}

	/**
	 * Questions about ordering the service.
	 *
	 * @param array $c Service content.
	 * @param array $o Band options.
	 */
	private static function faq( array $c, array $o ): array {
		$qa = static function ( array $row ): array {
			return array(
				'question' => $row[0],
				'answer'   => '<p>' . $row[1] . '</p>',
			);
		};

		return Blocks::band(
			array(
				Blocks::heading( 'سؤال‌های رایج', '', array( 'space_below' => El::px( 28 ) ) ),
				El::w( 'lzp-faq', array( 'items' => Blocks::rows( array_map( $qa, $c['faq'] ) ) ) ),
			),
			$o
		);
	}

	/**
	 * Closing call to action.
	 *
	 * @param array $c Service content.
	 */
	private static function cta( array $c ): array {
		return Blocks::band(
			array(
				El::w(
					'lzp-cta-band',
					array(
						'title'          => $c['cta'][0],
						'text'           => $c['cta'][1],
						'primary_text'   => 'رزرو وقت',
						'primary_link'   => Blocks::link( Blocks::booking_url() ),
						'primary_icon'   => 'calendar',
						'secondary_text' => 'همه خدمات',
						'secondary_link' => Blocks::link( Blocks::design_page_url( 'services' ) ),
						'image_label'    => $c['cta'][2],
					)
				),
			),
			Blocks::pad_y( 52, 36, 24 )
		);
	}

	/**
	 * The order form of the minimal page, in the ink frame: a choice of the
	 * service's plans and a line about the job.
	 *
	 * @param array $c Service content.
	 */
	private static function request_form( array $c ): array {
		$field = static function ( string $type, string $label, bool $wide = false, string $options = '' ): array {
			return array(
				'type'     => $type,
				'label'    => $label,
				'required' => 'yes',
				'wide'     => $wide ? 'yes' : '',
				'options'  => $options,
			);
		};

		$plans   = wp_list_pluck( $c['plans'], 0 );
		$plans[] = 'هنوز مطمئن نیستم';

		return El::w(
			'lzp-request-form',
			array(
				'frame'        => 'ink',
				'form_name'    => $c['name'],
				'fields'       => Blocks::rows(
					array(
						$field( 'name', 'نام و نام خانوادگی' ),
						$field( 'phone', 'شماره موبایل' ),
						$field( 'select', 'بسته', true, implode( "\n", $plans ) ),
						$field( 'textarea', $c['order_label'], true ),
					)
				),
				'note'         => 'اطلاعاتت فقط برای پاسخ به همین سفارش استفاده می‌شود.',
				'button_text'  => 'ثبت سفارش',
				'success_text' => 'سفارشت رسید؛ برای هماهنگی تماس می‌گیرم.',
			)
		);
	}
}
