<?php
/**
 * The home page of the mockups (Design/… - Home.dc.html): the film strip
 * hero, services, about me, key numbers, the portfolio with category chips,
 * a call to action, the framed «بگو سیـب», courses, the blog, client
 * testimonials, the booking form in the ink frame (#booking) and the FAQ.
 *
 * Section ids (services, about, portfolio, courses, blog, testimonials,
 * booking, faq) are kept so menu links such as `/#booking` keep working.
 * The Persian copy is the mockups' sample content; images are left empty.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Presets;

defined( 'ABSPATH' ) || exit;

/**
 * Home page preset builder.
 */
final class Home {

	/** Every section of the page, in order. */
	public static function page(): array {
		return array(
			self::hero(),
			self::services(),
			self::about(),
			self::stats(),
			self::portfolio(),
			self::cta(),
			self::smile(),
			self::courses(),
			self::blog(),
			self::testimonials(),
			self::booking(),
			self::faq(),
		);
	}

	/** Film strip, who I am, booking and portfolio buttons, the proof row and the showreel frame. */
	private static function hero(): array {
		return Blocks::bleed(
			array(
				El::w(
					'lzp-page-hero',
					array(
						'layout'         => 'filmstrip',
						'eyebrow_strong' => 'Amirmahdi Asadi',
						'eyebrow'        => 'Photography',
						'title'          => 'قصه‌ات را با نور می‌نویسم',
						'lead'           => 'عکاسی حرفه‌ای، عکاسی آیینی و مذهبی، تولید محتوای تصویری و پوشش رویداد — از اولین تماس تا تحویل فایل‌ها، کنارت هستم.',
						'primary_text'   => 'رزرو وقت',
						'primary_link'   => Blocks::link( '#booking' ),
						'secondary_text' => 'نمونه‌کارها',
						'secondary_link' => Blocks::link( '#portfolio' ),
						'strip_text'     => 'PHOTOGRAPHY STUDIO',
						'proof_top'      => 'از سال ۱۳۹۹ تا امروز',
						'proof_bottom'   => 'کنار شما بوده‌ام',
						'ratio'          => '4/5',
						'image_label'    => 'عکس/ویدیوی معرفی',
						'watermark'      => 'AMIRMAHDI PHOTO',
					)
				),
			)
		);
	}

	/** «خدمات من»: four service cards linking to the services page sections. */
	private static function services(): array {
		$card = static function ( string $icon, string $title, string $latin, string $text, string $anchor ): array {
			return array(
				'icon'  => $icon,
				'title' => $title,
				'latin' => $latin,
				'text'  => $text,
				'link'  => Blocks::link( Blocks::design_page_url( 'services' ) . '#' . $anchor ),
			);
		};

		return Blocks::band(
			array(
				Blocks::heading( 'خدمات من', '', array( 'space_below' => El::px( 28 ) ) ),
				El::w(
					'lzp-service-cards',
					array(
						'items' => Blocks::rows(
							array(
								$card( 'camera', 'عکاسی حرفه‌ای', 'Professional Photography', 'عکاسی پرتره، تبلیغاتی، صنعتی، رویداد و ثبت لحظه‌ها؛ با تمرکز بر نور، ترکیب‌بندی و خلق تصاویری که هویت سوژه را به‌درستی منتقل کنند.', 'photo' ),
								$card( 'mosque', 'عکاسی آیینی و مذهبی', 'Religious & Ritual Photography', 'ثبت مراسم عزاداری، هیئت‌ها، جشن‌های مذهبی و اماکن متبرکه با نگاهی مستند و محترمانه؛ بدون صحنه‌سازی.', 'religious' ),
								$card( 'video', 'تولید محتوای تصویری', 'Visual Content Creation', 'طراحی و تولید محتوای تصویری برای شبکه‌های اجتماعی و رسانه‌های دیجیتال؛ با هدف ساخت محتوایی منسجم، جذاب و متناسب با شخصیت و نیاز هر برند.', 'content' ),
								$card( 'stadium', 'پوشش رویداد و مستندسازی', 'Event Coverage & Visual Storytelling', 'روایت تصویری رویدادها، مسابقات و اتفاقات مهم؛ با ثبت جزئیات، آدم‌ها، فضا و لحظه‌هایی که یک تجربه را به خاطره‌ای ماندگار تبدیل می‌کنند.', 'event' ),
							)
						),
					)
				),
			),
			array( 'set' => array( '_element_id' => 'services' ) )
		);
	}

	/** «درباره من»: two paragraphs, a link to the about page and four promises beside a photo. */
	private static function about(): array {
		$promise = static function ( string $icon, string $title ): array {
			return array(
				'icon'  => $icon,
				'title' => $title,
				'note'  => '',
			);
		};

		return Blocks::band(
			array(
				Blocks::columns(
					array(
						Blocks::heading( 'درباره من' ),
						El::w(
							'lzp-text',
							array(
								'content' => '<p>من امیرمهدی اسدی هستم؛ عکاس و فیلم‌بردار و فعال در حوزه تولید محتوای تصویری.</p><p>داستان من با تصویر، خیلی زودتر از آن‌چه به یک مسیر حرفه‌ای تبدیل شود آغاز شد؛ از ۶ سالگی، زمانی که در کنار پدرم عکاسی را تجربه کردم. همان تجربه‌های ابتدایی، به‌مرور به علاقه‌ای جدی تبدیل شدند و از سال ۱۳۹۹، فعالیت حرفه‌ای خودم را در عرصه عکاسی آغاز کردم.</p>',
							)
						),
						El::w(
							'lzp-button',
							array(
								'text'    => 'ادامه داستان',
								'link'    => Blocks::link( Blocks::design_page_url( 'about' ) ),
								'variant' => 'link',
							)
						),
						El::w(
							'lzp-icon-features',
							array(
								'items'          => Blocks::rows(
									array(
										$promise( 'smile', 'رضایت مخاطب در اولویت' ),
										$promise( 'clock', 'تعهد به زمان‌بندی' ),
										$promise( 'bolt', 'تحویل سریع فایل‌ها' ),
										$promise( 'receipt', 'قیمت شفاف و بدون هزینه پنهان' ),
									)
								),
								'look'           => 'boxed',
								'columns'        => '2',
								'columns_tablet' => '2',
								'columns_mobile' => '1',
								'_margin'        => array(
									'unit'     => 'px',
									'top'      => '12',
									'right'    => '0',
									'bottom'   => '0',
									'left'     => '0',
									'isLinked' => '',
								),
							)
						),
					),
					array(
						El::w(
							'lzp-photo-frame',
							array(
								'ratio' => '4/3',
								'label' => 'عکس امیرمهدی سر کار',
							)
						),
					),
					array( 'align' => 'center' )
				),
			),
			array( 'set' => array( '_element_id' => 'about' ) )
		);
	}

	/** Four key numbers on tiles. */
	private static function stats(): array {
		return Blocks::band(
			array(
				El::w(
					'lzp-stats',
					array(
						'items' => Blocks::rows(
							array(
								array(
									'icon'  => 'camera',
									'value' => '۶',
									'label' => 'سال تجربه حرفه‌ای',
								),
								array(
									'icon'  => 'images',
									'value' => '۲۰۰+',
									'label' => 'پروژه',
								),
								array(
									'icon'  => 'landmark',
									'value' => '۱۲',
									'label' => 'نمایشگاه گروهی',
								),
								array(
									'icon'  => 'trophy',
									'value' => '۶',
									'label' => 'برگزیده جشنواره',
								),
							)
						),
					)
				),
			),
			Blocks::pad_y( 48, 36, 24 )
		);
	}

	/** «نمونه‌کارها»: the newest projects with the category chips in the title row. */
	private static function portfolio(): array {
		return Blocks::band(
			array(
				El::w(
					'lzp-portfolio-grid',
					array(
						'title'        => 'نمونه‌کارها',
						'source'       => 'latest',
						'count'        => 8,
						'show_count'   => '',
						'all_label'    => 'همه',
						'column_width' => El::px( 230 ),
					)
				),
			),
			array( 'set' => array( '_element_id' => 'portfolio' ) )
		);
	}

	/** «لحظه‌های خوبت موندگار می‌شه»: the ink call to action with a 16:9 photo. */
	private static function cta(): array {
		return Blocks::band(
			array(
				El::w(
					'lzp-cta-band',
					array(
						'title'          => 'لحظه‌های خوبت موندگار می‌شه',
						'text'           => 'اگه من رو انتخاب کنی : )',
						'primary_text'   => 'همین الان مشاوره بگیر',
						'primary_link'   => Blocks::link( '#booking' ),
						'primary_icon'   => 'phone',
						'secondary_text' => '',
						'image_label'    => 'عکس قاب‌گرفتن با دست',
					)
				),
			),
			Blocks::pad_y( 48, 36, 24 )
		);
	}

	/** «بگو سیـب»: one large line in the framed band. */
	private static function smile(): array {
		return Blocks::band(
			array(
				El::w(
					'lzp-framed-band',
					array(
						'label' => '',
						'text'  => 'بگو سیـب',
						'icon'  => 'camera',
						'size'  => 'lg',
						'ticks' => 'yes',
					)
				),
			),
			Blocks::pad_y( 56, 40, 28 )
		);
	}

	/** «دوره‌های عکاسی»: course cards on the soft band. */
	private static function courses(): array {
		return Blocks::band(
			array(
				Blocks::heading( 'دوره‌های عکاسی', 'آموزش‌هایی که از دل کار روزمره‌ام بیرون آمده‌اند', array( 'space_below' => El::px( 28 ) ) ),
				El::w(
					'lzp-course-grid',
					array(
						'count'           => 3,
						'filters'         => '',
						'button_text'     => 'جزئیات و ثبت‌نام',
						'columns'         => '3',
						'columns_tablet'  => '2',
						'columns_mobile'  => '1',
						'waitlist_list'   => 'لیست انتظار دوره‌ها',
						'waitlist_button' => 'خبرم کن',
						'waitlist_done'   => 'ثبت شد؛ به‌محض باز شدن ثبت‌نام خبرت می‌کنم.',
						'archive_link'    => Blocks::link( Blocks::design_page_url( 'courses' ) ),
					)
				),
			),
			array(
				'surface' => 'soft',
				'set'     => array( '_element_id' => 'courses' ),
			)
		);
	}

	/** «بلاگ»: the four newest articles with a "view all" button in the title row. */
	private static function blog(): array {
		return Blocks::band(
			array(
				Blocks::heading(
					'بلاگ',
					'گزارش برنامه‌ها، قصه‌های سفر و چیزهایی که در کار یاد گرفته‌ام',
					array(
						'action_text' => 'مشاهده همه',
						'action_link' => Blocks::link( Blocks::blog_url() ),
						'space_below' => El::px( 28 ),
					)
				),
				El::w(
					'lzp-post-grid',
					array(
						'source'         => 'latest',
						'count'          => 4,
						'look'           => 'dashed',
						'ratio'          => '3/2',
						'columns'        => '4',
						'columns_tablet' => '2',
						'columns_mobile' => '1',
					)
				),
			),
			array( 'set' => array( '_element_id' => 'blog' ) )
		);
	}

	/** «نظر مشتری‌ها»: three client testimonials on the soft band. */
	private static function testimonials(): array {
		$quote = static function ( string $text, string $name, string $role ): array {
			return array(
				'text' => $text,
				'name' => $name,
				'role' => $role,
			);
		};

		return Blocks::band(
			array(
				Blocks::heading( 'نظر مشتری‌ها', '', array( 'space_below' => El::px( 28 ) ) ),
				El::w(
					'lzp-testimonials',
					array(
						'items' => Blocks::rows(
							array(
								$quote( 'مسابقات سوارکاری باشگاه را پوشش داد. امیرمهدی از اول تا آخر آرام و منظم کار کرد و عکس‌ها دقیقاً همان حسی را دارند که آن روز داشتیم.', 'سارا محمدی', 'پوشش رویداد' ),
								$quote( 'برای فروشگاه اینترنتی‌مان صد و بیست محصول عکاسی شد. تحویل سر وقت بود و نرخ تبدیل صفحه محصول‌هایمان واقعاً بالا رفت.', 'شایان نقوی', 'عکاسی محصول' ),
								$quote( 'تیزر افتتاحیه کافه را ساخت؛ از فیلم‌نامه تا تدوین. نتیجه در اینستاگرام بیشترین بازدید صفحه‌مان را گرفت.', 'محمد صابری', 'تولید محتوای تصویری' ),
							)
						),
					)
				),
			),
			array(
				'surface' => 'soft',
				'set'     => array( '_element_id' => 'testimonials' ),
			)
		);
	}

	/** «رزرو وقت»: the booking form in the ink frame. */
	private static function booking(): array {
		$field = static function ( string $type, string $label, bool $required, bool $wide = false, string $options = '' ): array {
			return array(
				'type'     => $type,
				'label'    => $label,
				'required' => $required ? 'yes' : '',
				'wide'     => $wide ? 'yes' : '',
				'options'  => $options,
			);
		};

		return Blocks::band(
			array(
				Blocks::heading( 'رزرو وقت', 'فرم را پر کن؛ حداکثر تا یک روز کاری برای هماهنگی تماس می‌گیرم.', array( 'space_below' => El::px( 26 ) ) ),
				El::w(
					'lzp-request-form',
					array(
						'frame'        => 'ink',
						'form_name'    => 'رزرو وقت',
						'fields'       => Blocks::rows(
							array(
								$field( 'name', 'نام و نام خانوادگی', true ),
								$field( 'select', 'موضوع موردنظرت را انتخاب کن', true, false, "عکاسی حرفه‌ای\nعکاسی آیینی و مذهبی\nعکاسی محصول و تبلیغاتی\nتصویربرداری و تیزر\nتولید محتوا و موبایل‌گرافی\nپوشش رویداد و مستندسازی\nدوره‌های آموزشی" ),
								$field( 'phone', 'شماره موبایل', true ),
								$field( 'text', 'شهر / محل اجرا', false ),
								$field( 'date', 'تاریخ و ساعت پیشنهادی (اختیاری)', false, true ),
								$field( 'textarea', 'جزئیات و توضیحات بیشتر (اختیاری)', false, true ),
							)
						),
						'note'         => 'اطلاعاتت فقط برای هماهنگی همین جلسه استفاده می‌شود.',
						'button_text'  => 'رزرو کن',
						'success_text' => 'درخواستت ثبت شد. تا یک روز کاری تماس می‌گیرم.',
					)
				),
			),
			array( 'set' => array( '_element_id' => 'booking' ) )
		);
	}

	/** «سؤالات متداول». */
	private static function faq(): array {
		$pair = static function ( string $question, string $answer ): array {
			return array(
				'question' => $question,
				'answer'   => '<p>' . $answer . '</p>',
			);
		};

		return Blocks::band(
			array(
				Blocks::heading( 'سؤالات متداول' ),
				El::w(
					'lzp-faq',
					array(
						'items' => Blocks::rows(
							array(
								$pair( 'هر جلسه عکاسی چقدر طول می‌کشد؟', 'جلسه پرتره معمولاً یک تا دو ساعت است، عکاسی محصول بسته به تعداد آیتم نیم روز تا یک روز و پوشش رویداد به برنامه خودت بستگی دارد.' ),
								$pair( 'عکس‌های نهایی کِی تحویل داده می‌شود؟', 'فایل‌های منتخب و روتوش‌شده تا ۱۰ روز کاری تحویل می‌شود. برای پروژه‌های ویدیویی زمان تدوین جداگانه در قرارداد نوشته می‌شود.' ),
								$pair( 'امکان عکاسی در محل ما هست؟', 'بله. تجهیزات نور سیار دارم و برای محل کار، خانه یا لوکیشن بیرونی هم می‌آیم. هزینه رفت‌وآمد خارج از شهر جداگانه محاسبه می‌شود.' ),
								$pair( 'روتوش شامل چه کارهایی است؟', 'اصلاح نور و رنگ، پاک‌سازی پس‌زمینه و روتوش پوستِ طبیعی. روتوش‌های سنگین‌تر در صورت درخواست و با هزینه جدا انجام می‌شود.' ),
								$pair( 'شرایط جابه‌جایی یا کنسل کردن وقت چیست؟', 'تا ۷۲ ساعت قبل از جلسه، جابه‌جایی تاریخ بدون هزینه است. برای کنسلی در فاصله کمتر، بخشی از بیعانه به‌عنوان هزینه رزرو زمان کسر می‌شود.' ),
							)
						),
					)
				),
			),
			array( 'set' => array( '_element_id' => 'faq' ) )
		);
	}
}
