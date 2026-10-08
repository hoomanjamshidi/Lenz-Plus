<?php
/**
 * The courses page and the course page of the mockups (Design/… -
 * Courses.dc.html and Course.dc.html).
 *
 * The courses page is a page design (Create page): title, course cards with
 * status and format chips, how classes are held, and the waitlist band
 * (#waitlist). The course page is a route template for WooCommerce products
 * marked as courses: everything comes from the course's details box. It
 * comes as the mockups' single column with the buy box after the content,
 * and as a variant with the buy box sticky beside the content.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Presets;

defined( 'ABSPATH' ) || exit;

/**
 * Courses page and course page preset builders.
 */
final class Courses {

	/** The courses page. */
	public static function page(): array {
		$card = static function ( string $icon, string $title, string $text ): array {
			return array(
				'icon'  => $icon,
				'title' => $title,
				'text'  => $text,
			);
		};

		return array(
			Blocks::bleed(
				array(
					El::w(
						'lzp-page-hero',
						array(
							'layout'       => 'text',
							'eyebrow'      => 'آرشیو دوره‌ها',
							'title'        => 'همه دوره‌ها و کارگاه‌ها، یک‌جا',
							'lead'         => 'دوره‌های در حال ثبت‌نام، کارگاه‌های حضوری و آرشیو دوره‌های تمام‌شده. هر دوره از دل کار روزمره‌ام بیرون آمده، نه از روی کتاب.',
							'primary_text' => '',
							'watermark'    => 'LEARN THE LIGHT',
						)
					),
				)
			),
			Blocks::band(
				array(
					El::w(
						'lzp-course-grid',
						array(
							'button_text'     => 'جزئیات و ثبت‌نام',
							'columns'         => '4',
							'columns_tablet'  => '2',
							'columns_mobile'  => '1',
							'waitlist_list'   => 'لیست انتظار دوره‌ها',
							'waitlist_button' => 'به لیست انتظار اضافه کن',
							'waitlist_done'   => 'ثبت شد؛ به‌محض باز شدن ثبت‌نام خبرت می‌کنم.',
						)
					),
				),
				array(
					'pad'        => array( 24, 40, 64 ),
					'pad_tablet' => array( 20, 24, 44 ),
					'pad_mobile' => array( 14, 16, 30 ),
					'set'        => array( '_element_id' => 'list' ),
				)
			),
			Blocks::band(
				array(
					Blocks::heading( 'کلاس‌ها چطور برگزار می‌شوند', 'قالب همه دوره‌ها یکسان است؛ فقط موضوع فرق می‌کند' ),
					El::w(
						'lzp-card-grid',
						array(
							'items' => Blocks::rows(
								array(
									$card( 'tv', 'جلسه زنده هفتگی', 'هر جلسه حدود دو ساعت، با پرسش و پاسخ در انتها. ضبط جلسه بعد از کلاس در اختیارت است.' ),
									$card( 'review', 'تمرین با بازخورد', 'بعد از هر جلسه تمرین می‌دهم و عکس‌هایت را یکی‌یکی نقد می‌کنم.' ),
									$card( 'chat', 'گروه هم‌دوره‌ای‌ها', 'یک گروه کوچک برای پرسیدن سؤال بین جلسات که تا یک ماه بعد از دوره باز می‌ماند.' ),
									$card( 'award', 'گواهی پایان دوره', 'برای کسانی که تمرین‌ها را کامل تحویل داده باشند صادر می‌شود.' ),
								)
							),
						)
					),
				),
				array( 'surface' => 'soft' )
			),
			Blocks::band(
				array( self::waitlist() ),
				array_merge( Blocks::pad_y( 52, 36, 24 ), array( 'set' => array( '_element_id' => 'waitlist' ) ) )
			),
		);
	}

	/** The course page as in the mockups: one column, the buy box after the content. */
	public static function single(): array {
		return array(
			self::hero(),
			Blocks::band(
				self::content(),
				array(
					'pad'        => array( 56, 40 ),
					'pad_tablet' => array( 40, 24 ),
					'pad_mobile' => array( 24, 16 ),
					'gap'        => 48,
					'gap_tablet' => 36,
					'gap_mobile' => 28,
				)
			),
			Blocks::band(
				array( El::w( 'lzp-course-buy-box', array( 'label' => 'مشخصات دوره' ) ) ),
				array(
					'pad'        => array( 20, 40, 52 ),
					'pad_tablet' => array( 16, 24, 40 ),
					'pad_mobile' => array( 10, 16, 24 ),
				)
			),
			self::enroll(),
			self::buy_bar(),
		);
	}

	/** The course page with the buy box sticky beside the content. */
	public static function single_sidebar(): array {
		return array(
			self::hero(),
			Blocks::band(
				array(
					El::box(
						array(
							'dir'        => 'row',
							'dir_tablet' => 'column',
							'align'      => 'flex-start',
							'gap'        => 40,
							'gap_tablet' => 32,
						),
						array(
							El::box(
								array(
									'width'        => 64,
									'width_tablet' => 100,
									'width_mobile' => 100,
									'fill'         => true,
									'gap'          => 48,
									'gap_mobile'   => 28,
								),
								self::content()
							),
							El::box(
								array(
									'width'         => 36,
									'width_tablet'  => 100,
									'width_mobile'  => 100,
									'fill'          => true,
									'sticky'        => 'desktop',
									'sticky_offset' => 90,
									'tag'           => 'aside',
								),
								array(
									El::w(
										'lzp-course-buy-box',
										array(
											'layout' => 'narrow',
											'label'  => 'مشخصات دوره',
										)
									),
								)
							),
						)
					),
				),
				array(
					'pad'        => array( 56, 40 ),
					'pad_tablet' => array( 40, 24 ),
					'pad_mobile' => array( 24, 16 ),
				)
			),
			self::enroll(),
			self::buy_bar(),
		);
	}

	/** The phone buy bar (courses.js moves it to the bottom of the screen). */
	private static function buy_bar(): array {
		return El::box(
			array(
				'pad'        => 0,
				'pad_tablet' => 0,
				'pad_mobile' => 0,
			),
			array( El::w( 'lzp-mobile-buy-bar', array( 'button_text' => 'ثبت‌نام' ) ) )
		);
	}

	/** Course hero with the breadcrumb. */
	private static function hero(): array {
		return Blocks::bleed( array( El::w( 'lzp-course-hero', array( 'home_label' => 'خانه' ) ) ) );
	}

	/**
	 * The course's content: introduction, what you learn, curriculum, who it
	 * is for, outcome, instructor and questions.
	 *
	 * @return array<int, array>
	 */
	private static function content(): array {
		return array(
			El::w( 'lzp-post-content', array( 'boxed' => 'yes' ) ),
			self::part( 'در این ورکشاپ چه چیزهایی یاد می‌گیری؟', El::w( 'lzp-checklist-grid', array( 'source' => 'learn' ) ) ),
			self::part( 'سرفصل‌های ورکشاپ', El::w( 'lzp-curriculum' ) ),
			self::part(
				'این ورکشاپ برای چه کسانی است؟',
				El::w(
					'lzp-checklist-grid',
					array(
						'source' => 'audience',
						'intro'  => 'برای کسانی که:',
					)
				)
			),
			El::w( 'lzp-course-outcome', array( 'label' => 'خروجی ورکشاپ' ) ),
			El::w(
				'lzp-instructor-box',
				array(
					'label'        => 'مدرس ورکشاپ',
					'primary_text' => 'درباره من بیشتر بخوان',
					'primary_link' => Blocks::link( Blocks::design_page_url( 'about' ) ),
				)
			),
			self::part(
				'سوالات متداول',
				El::w(
					'lzp-faq',
					array(
						'source'    => 'course',
						'max_width' => array(
							'unit'  => '%',
							'size'  => 100,
							'sizes' => array(),
						),
					)
				)
			),
		);
	}

	/**
	 * A heading and the widget under it, as one block.
	 *
	 * @param string $title  Heading.
	 * @param array  $widget Widget.
	 */
	private static function part( string $title, array $widget ): array {
		return El::box( array(), array( Blocks::heading( $title ), $widget ) );
	}

	/** «ثبت‌نام ورکشاپ»: the request form the "Register" button scrolls to (#enroll). */
	private static function enroll(): array {
		$field = static function ( string $type, string $label, bool $required, string $options = '' ): array {
			return array(
				'type'     => $type,
				'label'    => $label,
				'required' => $required ? 'yes' : '',
				'options'  => $options,
			);
		};

		return Blocks::band(
			array(
				Blocks::heading( 'ثبت‌نام ورکشاپ', 'فرم را پر کن؛ زمان، مکان و جزئیات ورکشاپ را برایت می‌فرستم.', array( 'space_below' => El::px( 22 ) ) ),
				El::w(
					'lzp-request-form',
					array(
						'frame'        => 'card',
						'field_width'  => El::px( 210 ),
						'form_name'    => 'ثبت‌نام ورکشاپ',
						'fields'       => Blocks::rows(
							array(
								$field( 'name', 'نام و نام خانوادگی', true ),
								$field( 'phone', 'شماره موبایل', true ),
								$field( 'select', 'سطح فعلی‌ات را انتخاب کن', true, "تازه شروع کرده‌ام\nمدتی است عکس می‌گیرم\nبا موبایل زیاد عکس می‌گیرم" ),
								$field( 'text', 'مدل گوشی (اختیاری)', false ),
							)
						),
						'note'         => 'ظرفیت ورکشاپ محدود است و به ترتیب ثبت‌نام پر می‌شود.',
						'button_text'  => 'ثبت‌نام می‌کنم',
						'success_text' => 'ثبت شد. زمان، مکان و جزئیات ورکشاپ را برایت می‌فرستم.',
					)
				),
			),
			array(
				'pad'        => array( 0, 40, 52 ),
				'pad_tablet' => array( 0, 24, 40 ),
				'pad_mobile' => array( 0, 16, 24 ),
				'set'        => array( '_element_id' => 'enroll' ),
			)
		);
	}

	/** «دوره‌ی بعدی را از دست نده»: the waitlist band. */
	private static function waitlist(): array {
		return El::w(
			'lzp-newsletter-form',
			array(
				'title'             => 'دوره‌ی بعدی را از دست نده',
				'text'              => 'شماره یا ایمیلت را بگذار؛ فقط وقتی ثبت‌نام دوره جدیدی باز شود پیام می‌دهم.',
				'contact'           => 'email_phone',
				'placeholder'       => 'ایمیل یا شماره موبایل',
				'topics'            => "عکاسی با موبایل\nنورپردازی پرتره\nعکاسی محصول\nتدوین و اصلاح رنگ",
				'topic_placeholder' => 'کدام موضوع برایت مهم است؟',
				'button_text'       => 'خبرم کن',
				'list_name'         => 'لیست انتظار دوره‌ها',
				'success_text'      => 'ثبت شد؛ خبر دوره بعدی را برایت می‌فرستم.',
			)
		);
	}
}
