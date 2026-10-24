<?php
/**
 * Contact pages in three styles, built from the designs' parts (the
 * mockups have no contact page):
 *
 * - `classic`: a text hero, four contact cards, the request form beside the
 *   map, working hours and social links, then the questions.
 * - `split`: the contact rows on the ink panel beside the form, the map in
 *   a wide band under them and a closing call to action.
 * - `minimal`: a short centred title, three large values (phone, email,
 *   address), a wide map and the form in the ink frame.
 *
 * Maps load on click (Map_Embed) and the form posts to the requests inbox
 * like every Lenz+ form. Contact details are the mockups' style of sample
 * copy: the owner replaces them in the editor.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Presets;

defined( 'ABSPATH' ) || exit;

/**
 * Contact page preset builders.
 */
final class Contact {

	/** Sample contact details shared by the three pages. */
	private const PHONE   = '0912 000 0000';
	private const EMAIL   = 'hello@example.com';
	private const ADDRESS = 'مشهد، بلوار سجاد، آتلیه عکاسی';

	/** Hero, cards, the form beside the map and hours, questions. */
	public static function classic(): array {
		return array(
			self::hero( 'بیا درباره پروژه‌ات حرف بزنیم', 'برای رزرو جلسه، مشاوره رایگان یا همکاری تجاری پیام بده؛ معمولاً در کمتر از یک روز کاری جواب می‌دهم.' ),
			Blocks::band(
				array(
					El::w(
						'lzp-contact-cards',
						array(
							'look'  => 'tiles',
							'items' => Blocks::rows( self::cards( true ) ),
						)
					),
				),
				Blocks::pad_y( 8, 8, 4 )
			),
			Blocks::band(
				array(
					Blocks::columns(
						array(
							Blocks::heading( 'فرم تماس', 'پیامت مستقیم به صندوق درخواست‌ها می‌رسد.', array( 'space_below' => El::px( 20 ) ) ),
							self::form( 'card' ),
						),
						array(
							self::map( '4/3' ),
							El::w(
								'lzp-simple-list',
								array(
									'list_title' => 'ساعت پاسخ‌گویی',
									'layout'     => 'prices',
									'items'      => Blocks::rows( self::hours() ),
									'_margin'    => El::dims( array( 26, 0, 0, 0 ) ),
								)
							),
							El::w( 'lzp-social-links', array( '_margin' => El::dims( array( 18, 0, 0, 0 ) ) ) ),
						),
						array( 'gap' => 44 )
					),
				)
			),
			self::faq(),
		);
	}

	/** The contact rows on the ink panel beside the form, a wide map and a call to action. */
	public static function split(): array {
		return array(
			Blocks::band(
				array(
					El::box(
						array(
							'dir'        => 'row',
							'dir_tablet' => 'column',
							'align'      => 'stretch',
							'gap'        => 28,
							'gap_tablet' => 24,
						),
						array(
							El::box(
								array(
									'width'        => 40,
									'width_tablet' => 100,
									'width_mobile' => 100,
									'fill'         => true,
									'surface'      => 'ink-card',
									'gap'          => 22,
									'pad'          => array( 36, 32 ),
									'pad_mobile'   => array( 26, 20 ),
								),
								array(
									Blocks::heading( 'راه‌های ارتباطی', 'از هر راهی که راحت‌تری؛ پاسخ‌گویی شنبه تا پنجشنبه.', array( 'space_below' => El::px( 6 ) ) ),
									El::w(
										'lzp-contact-cards',
										array(
											'look'  => 'list',
											'items' => Blocks::rows( self::cards( true ) ),
										)
									),
									El::w( 'lzp-social-links' ),
								)
							),
							El::box(
								array(
									'width'        => 60,
									'width_tablet' => 100,
									'width_mobile' => 100,
									'fill'         => true,
									'gap'          => 6,
									'pad'          => array( 8, 0 ),
								),
								array(
									El::w(
										'lzp-heading',
										array(
											'title'       => 'پیام بگذار',
											'subtitle'    => 'چند خط درباره پروژه، تاریخ و محل اجرا بنویس تا با پیشنهاد دقیق تماس بگیرم.',
											'tag'         => 'h1',
											'space_below' => El::px( 22 ),
										)
									),
									self::form( 'none' ),
								)
							),
						)
					),
				),
				array(
					'pad'        => array( 48, 40, 40 ),
					'pad_tablet' => array( 36, 24, 32 ),
					'pad_mobile' => array( 24, 16, 24 ),
				)
			),
			Blocks::band(
				array( self::map( '21/9' ) ),
				Blocks::pad_y( 0, 0, 0 )
			),
			Blocks::band(
				array(
					El::w(
						'lzp-cta-band',
						array(
							'title'          => 'ترجیح می‌دهی حضوری صحبت کنیم؟',
							'text'           => 'یک وقت مشاوره رزرو کن؛ اولین جلسه رایگان است.',
							'primary_text'   => 'رزرو وقت',
							'primary_link'   => Blocks::link( Blocks::booking_url() ),
							'primary_icon'   => 'calendar',
							'secondary_text' => '',
							'image_label'    => 'عکس آتلیه',
						)
					),
				),
				Blocks::pad_y( 52, 36, 24 )
			),
		);
	}

	/** A centred title, three large values, a wide map and the form in the ink frame. */
	public static function minimal(): array {
		return array(
			Blocks::band(
				array(
					El::w(
						'lzp-heading',
						array(
							'latin'       => 'CONTACT',
							'bar'         => '',
							'title'       => 'سلام! از کجا شروع کنیم؟',
							'subtitle'    => 'یک تماس کوتاه یا یک ایمیل؛ بقیه‌اش با من.',
							'tag'         => 'h1',
							'align'       => 'center',
							'space_below' => El::px( 34 ),
						)
					),
					El::w(
						'lzp-contact-cards',
						array(
							'look'           => 'big',
							'columns'        => '3',
							'columns_tablet' => '3',
							'columns_mobile' => '1',
							'items'          => Blocks::rows( self::cards( false ) ),
						)
					),
				),
				array(
					'boxed'      => 1000,
					'pad'        => array( 64, 40, 36 ),
					'pad_tablet' => array( 48, 24, 28 ),
					'pad_mobile' => array( 34, 16, 20 ),
				)
			),
			Blocks::band(
				array( self::map( '21/9' ) ),
				array(
					'boxed'      => 1000,
					'pad'        => array( 0, 40, 20 ),
					'pad_tablet' => array( 0, 24, 16 ),
					'pad_mobile' => array( 0, 16, 12 ),
				)
			),
			Blocks::band(
				array(
					Blocks::heading(
						'یا همین‌جا پیام بده',
						'',
						array(
							'align' => 'center',
							'bar'   => '',
						)
					),
					self::form( 'ink' ),
				),
				array(
					'boxed'      => 1000,
					'pad'        => array( 40, 40, 64 ),
					'pad_tablet' => array( 32, 24, 44 ),
					'pad_mobile' => array( 24, 16, 30 ),
				)
			),
		);
	}

	/**
	 * Text-only page hero with the booking button.
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
						'layout'         => 'text',
						'eyebrow'        => 'تماس با ما',
						'title'          => $title,
						'lead'           => $lead,
						'primary_text'   => 'رزرو وقت',
						'primary_link'   => Blocks::link( Blocks::booking_url() ),
						'primary_icon'   => 'calendar',
						'secondary_text' => '',
						'watermark'      => 'SAY HELLO',
					)
				),
			)
		);
	}

	/**
	 * Contact card rows.
	 *
	 * @param bool $hours Whether the working hours card is included.
	 */
	private static function cards( bool $hours ): array {
		$cards = array(
			array(
				'icon'  => 'phone',
				'title' => 'تلفن و واتساپ',
				'value' => self::PHONE,
				'note'  => 'تماس یا پیام در ساعات کاری',
			),
			array(
				'icon'  => 'mail',
				'title' => 'ایمیل',
				'value' => self::EMAIL,
				'note'  => 'برای همکاری تجاری و ارسال بریف',
			),
			array(
				'icon'  => 'map-pin',
				'title' => 'آتلیه',
				'value' => self::ADDRESS,
				'note'  => 'مراجعه حضوری با هماهنگی قبلی',
			),
		);

		if ( $hours ) {
			$cards[] = array(
				'icon'  => 'clock',
				'title' => 'ساعات کاری',
				'value' => 'شنبه تا چهارشنبه، ۱۰ تا ۱۹',
				'note'  => 'پنجشنبه‌ها با هماهنگی قبلی',
			);
		}

		return $cards;
	}

	/** Working hours rows (text and value). */
	private static function hours(): array {
		return array(
			array(
				'text'  => 'شنبه تا چهارشنبه',
				'value' => '۱۰ تا ۱۹',
			),
			array(
				'text'  => 'پنجشنبه',
				'value' => '۱۰ تا ۱۴',
			),
			array(
				'text'  => 'جمعه و تعطیلات',
				'value' => 'فقط پروژه‌های رزروشده',
			),
		);
	}

	/**
	 * The studio map, loaded on click.
	 *
	 * @param string $ratio Shape.
	 */
	private static function map( string $ratio ): array {
		return El::w(
			'lzp-map',
			array(
				'title'   => 'آتلیه',
				'address' => self::ADDRESS,
				'ratio'   => $ratio,
			)
		);
	}

	/**
	 * The contact form.
	 *
	 * @param string $frame `ink`, `card` or `none`.
	 */
	private static function form( string $frame ): array {
		$field = static function ( string $type, string $label, bool $required, bool $wide = false, string $options = '' ): array {
			return array(
				'type'     => $type,
				'label'    => $label,
				'required' => $required ? 'yes' : '',
				'wide'     => $wide ? 'yes' : '',
				'options'  => $options,
			);
		};

		return El::w(
			'lzp-request-form',
			array(
				'frame'        => $frame,
				'form_name'    => 'تماس با ما',
				'fields'       => Blocks::rows(
					array(
						$field( 'name', 'نام و نام خانوادگی', true ),
						$field( 'phone', 'شماره موبایل', true ),
						$field( 'select', 'موضوع پیام', true, true, "رزرو جلسه عکاسی\nپروژه تجاری و تبلیغاتی\nدوره‌های آموزشی\nسایر موارد" ),
						$field( 'textarea', 'پیام شما', true, true ),
					)
				),
				'note'         => 'اطلاعاتت فقط برای پاسخ به همین پیام استفاده می‌شود.',
				'button_text'  => 'ارسال پیام',
				'success_text' => 'پیامت رسید؛ به‌زودی جواب می‌دهم.',
			)
		);
	}

	/** «سؤالات متداول» about getting in touch. */
	private static function faq(): array {
		$pair = static function ( string $question, string $answer ): array {
			return array(
				'question' => $question,
				'answer'   => '<p>' . $answer . '</p>',
			);
		};

		return Blocks::band(
			array(
				Blocks::heading( 'پیش از تماس' ),
				El::w(
					'lzp-faq',
					array(
						'items' => Blocks::rows(
							array(
								$pair( 'چقدر طول می‌کشد تا جواب بگیرم؟', 'پیام‌های فرم و ایمیل در کمتر از یک روز کاری پاسخ داده می‌شوند؛ برای کارهای فوری تماس بگیر.' ),
								$pair( 'بدون وقت قبلی می‌شود به آتلیه آمد؟', 'آتلیه برای جلسات رزروشده باز است؛ لطفاً پیش از آمدن هماهنگ کن تا زمان کافی برای گفت‌وگو داشته باشیم.' ),
								$pair( 'برای پروژه‌های شهرهای دیگر هم می‌آیید؟', 'بله. هزینه رفت‌وآمد و اقامت جداگانه در پیشنهاد قیمت نوشته می‌شود.' ),
							)
						),
					)
				),
			),
			array( 'surface' => 'soft' )
		);
	}
}
