<?php
/**
 * The services page of the mockups (Design/… - Services.dc.html): hero,
 * jump tiles to the four services, the four services (the second one on
 * the ink band with its areas of work, photos and honours), price cards
 * with add-ons, the project steps, FAQ and the closing call to action.
 *
 * The Persian copy is the mockups' sample content, meant to be replaced by
 * the site owner in Elementor; images are left empty so the frames show
 * which photo goes where. Each service band has an HTML id (photo,
 * religious, content, event, packages) so the jump tiles and hero buttons
 * scroll to it.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Presets;

defined( 'ABSPATH' ) || exit;

/**
 * Services page preset builder.
 */
final class Services {

	/** Every section of the page, in order. */
	public static function page(): array {
		return array(
			self::hero(),
			self::tiles(),
			self::service_photo(),
			self::service_religious(),
			self::service_content(),
			self::service_event(),
			self::packages(),
			self::steps(),
			self::faq(),
			self::cta(),
		);
	}

	/** Hero: four services, one standard; prices and portfolio buttons; a 4:3 studio photo. */
	private static function hero(): array {
		return Blocks::bleed(
			array(
				El::w(
					'lzp-page-hero',
					array(
						'eyebrow'        => 'خدمات',
						'title'          => 'چهار سرویس، یک استاندارد ثابت',
						'lead'           => 'عکاسی حرفه‌ای، عکاسی آیینی و مذهبی، تولید محتوای تصویری و پوشش رویداد. هر پروژه با قرارداد شروع می‌شود، تعداد فریم و زمان تحویل از اول مشخص است و تا رسیدن به نتیجه مورد رضایتت بازنگری داریم.',
						'primary_text'   => 'دیدن تعرفه‌ها',
						'primary_link'   => Blocks::link( '#packages' ),
						'secondary_text' => 'نمونه‌کارها',
						'secondary_link' => Blocks::link( Blocks::portfolio_url() ),
						'ratio'          => '4/3',
						'image_label'    => 'عکس چیدمان نور در آتلیه',
						'watermark'      => 'LIGHT & FRAME',
					)
				),
			)
		);
	}

	/** Four tiles that jump to the services below. */
	private static function tiles(): array {
		$tile = static function ( string $icon, string $title, string $anchor ): array {
			return array(
				'icon'  => $icon,
				'title' => $title,
				'link'  => Blocks::link( '#' . $anchor ),
			);
		};

		return Blocks::band(
			array(
				El::w(
					'lzp-icon-features',
					array(
						'size'           => 'lg',
						'columns'        => '4',
						'columns_tablet' => '2',
						'columns_mobile' => '1',
						'items'          => Blocks::rows(
							array(
								$tile( 'camera', 'عکاسی حرفه‌ای', 'photo' ),
								$tile( 'mosque', 'عکاسی آیینی و مذهبی', 'religious' ),
								$tile( 'video', 'تولید محتوای تصویری', 'content' ),
								$tile( 'stadium', 'پوشش رویداد و مستندسازی', 'event' ),
							)
						),
					)
				),
			),
			Blocks::pad_y( 48, 36, 24 )
		);
	}

	/**
	 * Chip rows of a service.
	 *
	 * @param string[] $chips Texts.
	 */
	private static function chips( array $chips ): array {
		return Blocks::rows(
			array_map(
				static function ( string $text ): array {
					return array( 'text' => $text );
				},
				$chips
			)
		);
	}

	/**
	 * Band options with an HTML id, for the jump links.
	 *
	 * @param string $anchor Id.
	 * @param array  $more   More options.
	 */
	private static function anchored( string $anchor, array $more = array() ): array {
		return array_merge( array( 'set' => array( '_element_id' => $anchor ) ), $more );
	}

	/** 01 Professional photography with two staggered portraits. */
	private static function service_photo(): array {
		return Blocks::band(
			array(
				El::w(
					'lzp-service-detail',
					array(
						'index'   => '۰۱',
						'title'   => 'عکاسی حرفه‌ای',
						'latin'   => 'PROFESSIONAL PHOTOGRAPHY',
						'text'    => 'عکاسی پرتره، تبلیغاتی، صنعتی، رویداد و ثبت لحظه‌ها؛ با تمرکز بر نور، ترکیب‌بندی و خلق تصاویری که هویت سوژه را به‌درستی منتقل کنند.',
						'chips'   => self::chips( array( 'پرتره شخصی و سازمانی', 'محصولات', 'برندها', 'رویدادها', 'پروژه‌های عکاسی' ) ),
						'media'   => 'pair',
						'label_1' => 'پرتره',
						'label_2' => 'صنعتی',
					)
				),
			),
			self::anchored( 'photo' )
		);
	}

	/** 02 Religious photography on the ink band: service, areas of work, photos and honours. */
	private static function service_religious(): array {
		$area = static function ( string $title, string $text ): array {
			return array(
				'title' => $title,
				'text'  => $text,
			);
		};

		$photo = static function ( string $caption ): array {
			return array( 'caption' => $caption );
		};

		$honour = static function ( string $label, string $text ): array {
			return array(
				'label' => $label,
				'text'  => $text,
			);
		};

		return Blocks::band(
			array(
				El::w(
					'lzp-service-detail',
					array(
						'index'          => '۰۲',
						'title'          => 'عکاسی آیینی و مذهبی',
						'latin'          => 'RELIGIOUS & RITUAL PHOTOGRAPHY',
						'text'           => 'ثبت آیین‌ها، مراسم و لحظه‌های معنوی با نگاهی مستند و محترمانه. در این کار حرمت فضا و آدم‌ها اولویت اول است؛ بدون صحنه‌سازی، با کمترین مزاحمت برای مراسم و با تمرکز بر حس واقعی لحظه‌ها.',
						'chips'          => self::chips( array( 'هیئت‌ها و مساجد', 'مؤسسات فرهنگی و مذهبی', 'آستان‌ها و موقوفات', 'رسانه‌ها و خبرگزاری‌ها', 'کتاب و نمایشگاه' ) ),
						'primary_text'   => 'هماهنگی برای پوشش مراسم',
						'primary_link'   => Blocks::link( Blocks::booking_url() ),
						'secondary_text' => 'نمونه‌کارهای مذهبی',
						'secondary_link' => Blocks::link( Blocks::portfolio_url() ),
						'media'          => 'single',
						'ratio'          => '4/3',
						'label_1'        => 'عکس شاخص از یک مراسم آیینی',
						'watermark'      => 'RITUAL & FAITH',
					)
				),
				El::w(
					'lzp-card-grid',
					array(
						'list_title' => 'حوزه‌های کار',
						'size'       => 'sm',
						'items'      => Blocks::rows(
							array(
								$area( 'مراسم عزاداری و هیئت‌ها', 'محرم، صفر، فاطمیه و مراسم هفتگی هیئت‌ها؛ از دسته‌ها و سینه‌زنی تا لحظه‌های خلوت.' ),
								$area( 'اعیاد و جشن‌های مذهبی', 'غدیر، نیمه شعبان، میلادها و مراسم ماه رمضان.' ),
								$area( 'اماکن متبرکه و زیارت', 'حرم رضوی، زیارتگاه‌ها و سفرهای زیارتی مانند پیاده‌روی اربعین.' ),
								$area( 'مستند آیینی', 'روایت تصویری بلندمدت از یک آیین، هیئت یا سنت محلی برای کتاب، نمایشگاه یا آرشیو.' ),
							)
						),
					)
				),
				El::w(
					'lzp-photo-grid',
					array(
						'items' => Blocks::rows(
							array(
								$photo( 'مراسم عزاداری' ),
								$photo( 'حرم رضوی' ),
								$photo( 'پیاده‌روی اربعین' ),
								$photo( 'جشن غدیر' ),
							)
						),
					)
				),
				El::w(
					'lzp-simple-list',
					array(
						'list_title'     => 'افتخارات در این حوزه',
						'layout'         => 'stacked',
						'columns'        => '3',
						'columns_tablet' => '3',
						'columns_mobile' => '1',
						'items'          => Blocks::rows(
							array(
								$honour( 'نفر اول', 'جشنواره بین‌المللی خورشید ولایت' ),
								$honour( 'نفر دوم', 'جشنواره ملی سوگواری هنر عاشورا — بجنورد' ),
								$honour( 'حضور در نمایشگاه', 'نمایشگاه عکس «عطر رمضان»' ),
							)
						),
					)
				),
			),
			self::anchored(
				'religious',
				array_merge(
					Blocks::pad_y( 88, 60, 40 ),
					array(
						'surface'    => 'ink',
						'gap'        => 48,
						'gap_tablet' => 36,
						'gap_mobile' => 28,
						'set'        => array(
							'_element_id' => 'religious',
							'overflow'    => 'hidden',
						),
					)
				)
			)
		);
	}

	/** 03 Visual content with two tall reels frames at the start. */
	private static function service_content(): array {
		return Blocks::band(
			array(
				El::w(
					'lzp-service-detail',
					array(
						'index'      => '۰۳',
						'title'      => 'تولید محتوای تصویری',
						'latin'      => 'VISUAL CONTENT CREATION',
						'text'       => 'طراحی و تولید محتوای تصویری برای شبکه‌های اجتماعی و رسانه‌های دیجیتال؛ با هدف ساخت محتوایی منسجم، جذاب و متناسب با شخصیت و نیاز هر برند.',
						'chips'      => self::chips( array( 'محتوای اینستاگرام', 'عکاسی ویدیویی محصول', 'ریلز' ) ),
						'media'      => 'tall',
						'media_side' => 'start',
						'label_1'    => 'ریلز',
						'label_2'    => 'ویدیوی محصول',
					)
				),
			),
			self::anchored( 'content' )
		);
	}

	/** 04 Event coverage on the soft band. */
	private static function service_event(): array {
		return Blocks::band(
			array(
				El::w(
					'lzp-service-detail',
					array(
						'index'   => '۰۴',
						'title'   => 'پوشش رویداد و مستندسازی',
						'latin'   => 'EVENT COVERAGE & VISUAL STORYTELLING',
						'text'    => 'روایت تصویری رویدادها، مسابقات و اتفاقات مهم؛ با ثبت جزئیات، آدم‌ها، فضا و لحظه‌هایی که یک تجربه را به خاطره‌ای ماندگار تبدیل می‌کنند.',
						'chips'   => self::chips( array( 'رویدادهای ورزشی', 'مسابقات سوارکاری', 'همایش‌ها', 'نمایشگاه‌ها', 'پروژه‌های مستند تصویری' ) ),
						'media'   => 'single',
						'ratio'   => '4/3',
						'label_1' => 'عکس مسابقه سوارکاری',
					)
				),
			),
			self::anchored( 'event', array( 'surface' => 'soft' ) )
		);
	}

	/** Price cards and the add-on services box. */
	private static function packages(): array {
		$booking = Blocks::link( Blocks::booking_url() );

		$plan = static function ( string $name, string $price, string $unit, array $features, string $button, array $more = array() ) use ( $booking ): array {
			return array_merge(
				array(
					'name'     => $name,
					'price'    => $price,
					'unit'     => $unit,
					'features' => implode( "\n", $features ),
					'button'   => $button,
					'link'     => $booking,
				),
				$more
			);
		};

		$addon = static function ( string $text, string $value ): array {
			return array(
				'text'  => $text,
				'value' => $value,
			);
		};

		return Blocks::band(
			array(
				Blocks::heading( 'تعرفه‌ها', 'چهار پکیج جدا؛ قیمت‌ها پایه است و پس از گفت‌وگو درباره پروژه نهایی می‌شود' ),
				El::w(
					'lzp-pricing-plans',
					array(
						'items' => Blocks::rows(
							array(
								$plan(
									'عکاسی محصول و تبلیغاتی',
									'۸۵۰٫۰۰۰',
									'تومان / هر آیتم',
									array( '۵ فریم از هر محصول', 'پس‌زمینه ساده + یک فریم فضاسازی', 'خروجی آماده سایت و اینستاگرام', 'تخفیف پلکانی از ۲۰ آیتم به بالا' ),
									'استعلام قیمت',
									array(
										'badge'    => 'پرطرفدار',
										'featured' => 'yes',
									)
								),
								$plan( 'تصویربرداری و تیزر', 'از ۱۸٫۰۰۰٫۰۰۰', 'تومان', array( 'یک روز تصویربرداری', 'تیزر ۶۰ تا ۹۰ ثانیه', 'دو نسخه کوتاه برای ریلز', 'دو نوبت بازنگری' ), 'گفت‌وگو' ),
								$plan( 'پوشش رویداد', '۲٫۹۰۰٫۰۰۰', 'تومان / ساعت', array( 'حداقل ۳ ساعت', 'تحویل ۲۰ عکس منتخب تا ۲۴ ساعت', 'آرشیو کامل رویداد', 'امکان افزودن عکاس دوم' ), 'رزرو' ),
								$plan( 'موبایل‌گرافی', 'توافقی', '', array( 'عکاسی و فیلم‌برداری با موبایل', 'خروجی عمودی آماده ریلز و استوری', 'ادیت و اصلاح رنگ', 'مناسب تولید محتوای روزانه کسب‌وکار' ), 'استعلام قیمت' ),
							)
						),
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
								'list_title'     => 'خدمات قابل افزودن',
								'layout'         => 'prices',
								'columns'        => '4',
								'columns_tablet' => '2',
								'columns_mobile' => '1',
								'items'          => Blocks::rows(
									array(
										$addon( 'عکاس دوم', '۱٫۴۰۰٫۰۰۰ / ساعت' ),
										$addon( 'روتوش سنگین هر فریم', '۳۵۰٫۰۰۰' ),
										$addon( 'تحویل فوری ۴۸ ساعته', '۳۰٪ هزینه پروژه' ),
										$addon( 'چاپ و قاب آلبوم', 'بر اساس سفارش' ),
									)
								),
							)
						),
					)
				),
			),
			self::anchored( 'packages', Blocks::pad_y( 72, 52, 36 ) )
		);
	}

	/** The four project steps on the soft band (shared with the about page). */
	private static function steps(): array {
		return Blocks::band(
			array(
				Blocks::heading( 'مسیر هر پروژه', 'چهار قدم ثابت، از اولین تماس تا تحویل فایل‌ها' ),
				El::w( 'lzp-process-steps', array( 'items' => About::step_items() ) ),
			),
			array( 'surface' => 'soft' )
		);
	}

	/** Ordering questions. */
	private static function faq(): array {
		$qa = static function ( string $question, string $answer ): array {
			return array(
				'question' => $question,
				'answer'   => '<p>' . $answer . '</p>',
			);
		};

		return Blocks::band(
			array(
				Blocks::heading( 'سؤال‌های رایج درباره سفارش', '', array( 'space_below' => El::px( 28 ) ) ),
				El::w(
					'lzp-faq',
					array(
						'items' => Blocks::rows(
							array(
								$qa( 'برای رزرو چقدر بیعانه لازم است؟', '۳۰ درصد مبلغ پروژه هنگام امضای قرارداد و مابقی در زمان تحویل فایل‌ها.' ),
								$qa( 'هزینه رفت‌وآمد خارج از شهر چطور حساب می‌شود؟', 'تا شعاع ۵۰ کیلومتری مشهد رایگان است. فراتر از آن، هزینه سفر و اقامت پیش از شروع کار توافق و در قرارداد نوشته می‌شود.' ),
								$qa( 'حق استفاده از عکس‌ها با کیست؟', 'حق استفاده تجاری از تصاویر به شما داده می‌شود. من فقط برای نمونه‌کار و شبکه‌های اجتماعی‌ام استفاده می‌کنم، آن هم اگر موافق باشی.' ),
								$qa( 'چند نوبت بازنگری داریم؟', 'برای عکس، دو نوبت اصلاح روی فریم‌های منتخب و برای ویدیو دو نوبت بازنگری روی نسخه تدوین‌شده در قیمت لحاظ شده است.' ),
							)
						),
					)
				),
			)
		);
	}

	/** Closing call to action. */
	private static function cta(): array {
		return Blocks::band(
			array(
				El::w(
					'lzp-cta-band',
					array(
						'title'          => 'مطمئن نیستی کدام سرویس مناسب کارت است؟',
						'text'           => 'یک تماس کوتاه کافی است؛ اولین جلسه مشاوره رایگان است.',
						'primary_text'   => 'رزرو وقت',
						'primary_link'   => Blocks::link( Blocks::booking_url() ),
						'primary_icon'   => 'calendar',
						'secondary_text' => 'دیدن نمونه‌کارها',
						'secondary_link' => Blocks::link( Blocks::portfolio_url() ),
						'image_label'    => 'عکس پشت صحنه',
					)
				),
			),
			Blocks::pad_y( 52, 36, 24 )
		);
	}
}
