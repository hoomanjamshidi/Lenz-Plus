<?php
/**
 * Project booking pages in four styles, built around the «Project booking»
 * widget (the mockups have no booking page):
 *
 * - `steps`: a text hero, the form in numbered steps on a card, what
 *   happens after booking, and questions.
 * - `split`: the form beside its ink panel (steps and a live summary), then
 *   questions.
 * - `focus`: one large question at a time on the soft band, nothing else.
 * - `simple`: a text hero and an ordinary one-page form beside the ways to
 *   get in touch.
 *
 * The form's project types and fields are the widget's defaults (shown in
 * the site's language); bookings land in Lenz+ → Bookings.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Presets;

defined( 'ABSPATH' ) || exit;

/**
 * Booking page preset builders.
 */
final class Booking {

	/** Hero, the stepped form on a card, what happens next and questions. */
	public static function steps(): array {
		return array(
			self::hero( 'پروژه‌ات را در چهار قدم ثبت کن', 'نوع پروژه، جزئیات و راه تماس را بنویس؛ تا یک روز کاری برای هماهنگی تاریخ و قیمت تماس می‌گیرم.' ),
			Blocks::band(
				array( self::form( 'steps' ) ),
				array(
					'boxed'      => 960,
					'pad'        => array( 8, 40, 56 ),
					'pad_tablet' => array( 8, 24, 40 ),
					'pad_mobile' => array( 4, 16, 28 ),
				)
			),
			Blocks::band(
				array(
					Blocks::heading( 'بعد از ثبت درخواست', '', array( 'space_below' => El::px( 28 ) ) ),
					El::w( 'lzp-process-steps', array( 'items' => About::step_items() ) ),
				),
				array( 'surface' => 'soft' )
			),
			self::faq(),
		);
	}

	/** The form beside its ink panel, then questions. */
	public static function split(): array {
		return array(
			Blocks::band(
				array(
					El::w( 'lzp-breadcrumb', array( 'home_label' => 'خانه' ) ),
					El::w(
						'lzp-heading',
						array(
							'title'       => 'رزرو پروژه',
							'tag'         => 'h1',
							'space_below' => El::px( 24 ),
							'_margin'     => El::dims( array( 14, 0, 0, 0 ) ),
						)
					),
					self::form( 'split' ),
				),
				array(
					'pad'        => array( 36, 40, 60 ),
					'pad_tablet' => array( 28, 24, 44 ),
					'pad_mobile' => array( 20, 16, 30 ),
				)
			),
			self::faq(),
		);
	}

	/** One large question at a time on the soft band. */
	public static function focus(): array {
		return array(
			Blocks::band(
				array(
					El::w(
						'lzp-heading',
						array(
							'latin'       => 'BOOK A PROJECT',
							'title'       => 'رزرو پروژه',
							'tag'         => 'h1',
							'bar'         => '',
							'align'       => 'center',
							'space_below' => El::px( 34 ),
						)
					),
					self::form( 'focus' ),
				),
				array(
					'surface'    => 'soft',
					'min_h'      => 640,
					'pad'        => array( 64, 40, 80 ),
					'pad_tablet' => array( 48, 24, 60 ),
					'pad_mobile' => array( 32, 16, 40 ),
				)
			),
		);
	}

	/** Hero and an ordinary one-page form beside the ways to get in touch. */
	public static function simple(): array {
		return array(
			self::hero( 'فرم رزرو پروژه', 'همه چیز در یک صفحه: نوع پروژه، جزئیات و شماره تماس. پاسخ تا یک روز کاری.' ),
			Blocks::band(
				array(
					El::box(
						array(
							'dir'        => 'row',
							'dir_tablet' => 'column',
							'align'      => 'flex-start',
							'gap'        => 32,
							'gap_tablet' => 28,
						),
						array(
							El::box(
								array(
									'width'        => 66,
									'width_tablet' => 100,
									'width_mobile' => 100,
									'fill'         => true,
								),
								array( self::form( 'plain' ) )
							),
							El::box(
								array(
									'width'         => 34,
									'width_tablet'  => 100,
									'width_mobile'  => 100,
									'fill'          => true,
									'sticky'        => 'desktop',
									'sticky_offset' => 90,
									'tag'           => 'aside',
								),
								array(
									El::w(
										'lzp-contact-cards',
										array(
											'list_title' => 'یا مستقیم تماس بگیر',
											'look'       => 'tiles',
											'columns'    => '1',
											'items'      => Blocks::rows(
												array(
													array(
														'icon'  => 'phone',
														'title' => 'تلفن و واتساپ',
														'value' => '0912 000 0000',
														'note'  => 'شنبه تا چهارشنبه، ۱۰ تا ۱۹',
													),
													array(
														'icon'  => 'mail',
														'title' => 'ایمیل',
														'value' => 'hello@example.com',
														'note'  => 'برای ارسال بریف و فایل‌ها',
													),
												)
											),
										)
									),
								)
							),
						)
					),
				),
				array(
					'pad'        => array( 8, 40, 64 ),
					'pad_tablet' => array( 8, 24, 44 ),
					'pad_mobile' => array( 4, 16, 30 ),
				)
			),
		);
	}

	/**
	 * Text-only page hero.
	 *
	 * @param string $title Title.
	 * @param string $lead  Lead.
	 */
	private static function hero( string $title, string $lead ): array {
		return Blocks::bleed(
			array(
				El::w(
					'lzp-page-hero',
					array(
						'layout'       => 'text',
						'eyebrow'      => 'رزرو پروژه',
						'title'        => $title,
						'lead'         => $lead,
						'primary_text' => '',
						'watermark'    => 'BOOKING',
					)
				),
			)
		);
	}

	/**
	 * The booking widget in a look.
	 *
	 * @param string $look Look.
	 */
	private static function form( string $look ): array {
		return El::w(
			'lzp-booking-form',
			array(
				'look'      => $look,
				'form_name' => 'رزرو پروژه',
			)
		);
	}

	/** Questions about booking. */
	private static function faq(): array {
		$qa = static function ( string $question, string $answer ): array {
			return array(
				'question' => $question,
				'answer'   => '<p>' . $answer . '</p>',
			);
		};

		return Blocks::band(
			array(
				Blocks::heading( 'سؤال‌های رایج درباره رزرو', '', array( 'space_below' => El::px( 28 ) ) ),
				El::w(
					'lzp-faq',
					array(
						'items' => Blocks::rows(
							array(
								$qa( 'ثبت درخواست به معنی قطعی شدن رزرو است؟', 'نه؛ بعد از بررسی جزئیات تماس می‌گیرم و با توافق روی تاریخ و قیمت و پرداخت بیعانه، رزرو قطعی می‌شود.' ),
								$qa( 'کد پیگیری به چه کاری می‌آید؟', 'اگر بخواهی وضعیت درخواستت را بپرسی یا چیزی به آن اضافه کنی، با گفتن کد پیگیری سریع پیدایش می‌کنم.' ),
								$qa( 'برای چه بازه‌ای می‌شود رزرو کرد؟', 'معمولاً تا دو ماه آینده؛ برای مراسم و رویدادهای بزرگ زودتر هم می‌شود هماهنگ کرد.' ),
								$qa( 'هزینه رفت‌وآمد خارج از شهر چطور حساب می‌شود؟', 'هزینه سفر و اقامت جدا در پیشنهاد قیمت نوشته می‌شود و پیش از شروع کار توافق می‌کنیم.' ),
							)
						),
					)
				),
			)
		);
	}
}
