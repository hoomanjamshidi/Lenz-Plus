<?php
/**
 * The about page of the mockups (Design/… - About.dc.html): hero with a
 * portrait, key numbers, story and career path, a framed motto, working
 * steps, equipment and certificates, awards, collaborations and group
 * exhibitions, and the closing call to action.
 *
 * The Persian copy is the mockups' sample content, meant to be replaced by
 * the site owner in Elementor; images are left empty so the frames show
 * which photo goes where.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Presets;

defined( 'ABSPATH' ) || exit;

/**
 * About page preset builder.
 */
final class About {

	/** Every section of the page, in order. */
	public static function page(): array {
		return array(
			self::hero(),
			self::stats(),
			self::story(),
			self::motto(),
			self::steps(),
			self::gear(),
			self::awards(),
			self::collaborations(),
			self::cta(),
		);
	}

	/** Hero: who I am, three facts, booking and portfolio buttons, a 4:5 portrait. */
	private static function hero(): array {
		return Blocks::bleed(
			array(
				El::w(
					'lzp-page-hero',
					array(
						'eyebrow'        => 'درباره من',
						'title'          => 'مسیری که با یک دوربین کنار پدرم شروع شد',
						'lead'           => 'من امیرمهدی اسدی هستم؛ عکاس و فیلم‌بردار و فعال در حوزه تولید محتوای تصویری.',
						'chips'          => Blocks::rows(
							array(
								array( 'text' => 'متولد ۱۳۸۰' ),
								array( 'text' => 'مشهد، خراسان رضوی' ),
								array( 'text' => 'فعالیت حرفه‌ای از ۱۳۹۹' ),
							)
						),
						'primary_text'   => 'رزرو وقت',
						'primary_link'   => Blocks::link( Blocks::booking_url() ),
						'secondary_text' => 'نمونه‌کارها',
						'secondary_link' => Blocks::link( Blocks::portfolio_url() ),
						'ratio'          => '4/5',
						'image_label'    => 'عکس پرتره امیرمهدی',
						'watermark'      => 'BEHIND THE CAMERA',
					)
				),
			)
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

	/** Story paragraphs beside the career timeline. */
	private static function story(): array {
		$story = array(
			'داستان من با تصویر، خیلی زودتر از آن‌چه به یک مسیر حرفه‌ای تبدیل شود آغاز شد؛ از ۶ سالگی، زمانی که در کنار پدرم عکاسی را تجربه کردم. همان تجربه‌های ابتدایی، به‌مرور به علاقه‌ای جدی تبدیل شدند و از سال ۱۳۹۹، فعالیت حرفه‌ای خودم را در عرصه عکاسی آغاز کردم.',
			'در میان شاخه‌های مختلف عکاسی، عکاسی مستند برای من جایگاه ویژه‌ای دارد؛ چرا که باور دارم گاهی واقعی‌ترین لحظه‌ها، بدون نیاز به ساختن یا اغراق، بیشترین قدرت را برای روایت کردن دارند.',
			'امروز در کنار عکاسی و تصویربرداری، در حوزه تولید محتوا نیز فعالیت می‌کنم و تلاش می‌کنم هر پروژه را با نگاهی دقیق، خلاقانه و متناسب با نیاز مخاطب پیش ببرم. برای من، یک همکاری موفق فقط به ثبت تصاویر باکیفیت محدود نمی‌شود؛ رضایت مخاطب، تعهد به زمان‌بندی و تحویل سریع فایل‌ها بخش مهمی از تجربه‌ای است که می‌خواهم برای هر پروژه خلق کنم.',
			'این وب‌سایت، مجموعه‌ای از نمونه‌کارها، پروژه‌ها، تجربه‌ها و روایت‌های تصویری من است؛ بخشی از مسیری که با یک دوربین در کنار پدرم شروع شد و همچنان ادامه دارد.',
		);

		return Blocks::band(
			array(
				Blocks::columns(
					array(
						Blocks::heading( 'داستان من' ),
						El::w( 'lzp-text', array( 'content' => '<p>' . implode( '</p><p>', $story ) . '</p>' ) ),
					),
					array(
						Blocks::heading( 'مسیر کاری' ),
						El::w(
							'lzp-timeline',
							array(
								'items' => Blocks::rows(
									array(
										array(
											'date'  => '۱۳۸۶',
											'title' => 'کوچک‌ترین عکاس ایران',
											'text'  => 'در ۶ سالگی و در کنار پدرم، در جشنواره عکس دریا به‌عنوان کوچک‌ترین عکاس ایران انتخاب شدم.',
										),
										array(
											'date'  => 'آموزش',
											'title' => 'دو مدرک عکاسی',
											'text'  => 'مدرک عکاسی از سازمان فنی و حرفه‌ای و جهاد دانشگاهی.',
										),
										array(
											'date'  => '۱۳۹۹',
											'title' => 'شروع فعالیت حرفه‌ای',
											'text'  => 'ورود جدی به عکاسی، با علاقه ویژه به عکاسی مستند.',
										),
										array(
											'date'  => 'انجمن‌ها',
											'title' => 'عضویت در انجمن‌های عکاسی',
											'text'  => 'موسسه عکاسان خراسان رضوی، کانون عکس سینمای جوان مشهد و خانه عکس جهاد دانشگاهی مشهد.',
										),
										array(
											'date'  => 'امروز',
											'title' => 'عکاس و تصویربردار',
											'text'  => 'عکاس خبرگزاری مهر، عکاس و تصویربردار شرکت فرس و شرکت پارسینو، تصویربردار شرکت‌های تبلیغاتی و فعال در تولید محتوای تصویری.',
										),
									)
								),
							)
						),
					)
				),
			)
		);
	}

	/** The framed motto between dashed rules. */
	private static function motto(): array {
		return Blocks::band(
			array(
				El::w(
					'lzp-framed-band',
					array(
						'label' => 'زندگی از نگاه من',
						'text'  => 'زندگی من آن‌چیزی است که برایش می‌جنگم',
					)
				),
			),
			Blocks::pad_y( 56, 40, 28 )
		);
	}

	/** Four working steps on the soft band. */
	private static function steps(): array {
		return Blocks::band(
			array(
				Blocks::heading( 'طرز کار من', 'چهار قدم ثابت، از اولین تماس تا تحویل فایل‌ها' ),
				El::w( 'lzp-process-steps', array( 'items' => self::step_items() ) ),
			),
			array( 'surface' => 'soft' )
		);
	}

	/**
	 * The four steps, shared with the services page.
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function step_items(): array {
		return Blocks::rows(
			array(
				array(
					'title' => 'گفت‌وگوی اول',
					'text'  => 'می‌پرسم عکس‌ها کجا قرار است استفاده شوند و چه حسی باید داشته باشند.',
				),
				array(
					'title' => 'برنامه و قرارداد',
					'text'  => 'لوکیشن، زمان، تعداد فریم و هزینه شفاف نوشته می‌شود و بعد کار شروع می‌شود.',
				),
				array(
					'title' => 'روز عکاسی',
					'text'  => 'بدون عجله؛ اول کمی حرف می‌زنیم تا جلوی دوربین راحت باشی.',
				),
				array(
					'title' => 'انتخاب و تحویل',
					'text'  => 'گالری انتخاب می‌فرستم، روتوش نهایی انجام می‌شود و فایل‌ها سر وقت تحویل می‌شود.',
				),
			)
		);
	}

	/** Equipment beside certificates and memberships. */
	private static function gear(): array {
		$row = static function ( string $icon, string $title, string $note ): array {
			return array(
				'icon'  => $icon,
				'title' => $title,
				'note'  => $note,
			);
		};

		return Blocks::band(
			array(
				Blocks::columns(
					array(
						Blocks::heading( 'تجهیزاتی که با آن کار می‌کنم' ),
						El::w(
							'lzp-icon-features',
							array(
								'items' => Blocks::rows(
									array(
										$row( 'camera', 'بدنه Sony a7 IV', 'عکس و ویدیو' ),
										$row( 'camera', 'لنزهای ۱۴-۲۴، ۲۴-۷۰ و ۷۰-۲۰۰', 'واید تا تله' ),
										$row( 'bolt', 'فلاش استودیویی', 'آتلیه و محصول' ),
										$row( 'lightbulb', 'نور ثابت و نور RGB', 'عکس و ویدیو' ),
										$row( 'rotate-360', 'دوربین Insta360', 'ویدیوی ۳۶۰ درجه' ),
										$row( 'mic', 'گیمبال و رکوردر یقه‌ای', 'تصویربرداری و صدا' ),
									)
								),
							)
						),
					),
					array(
						Blocks::heading( 'مدارک و عضویت‌ها' ),
						El::w(
							'lzp-icon-features',
							array(
								'items' => Blocks::rows(
									array(
										$row( 'graduation', 'مدرک عکاسی سازمان فنی و حرفه‌ای', 'مدرک' ),
										$row( 'graduation', 'مدرک عکاسی جهاد دانشگاهی', 'مدرک' ),
										$row( 'users', 'موسسه عکاسان خراسان رضوی', 'عضو' ),
										$row( 'users', 'کانون عکس سینمای جوان مشهد', 'عضو' ),
										$row( 'users', 'خانه عکس جهاد دانشگاهی مشهد', 'عضو' ),
									)
								),
							)
						),
					)
				),
			)
		);
	}

	/** Award cards and the list of other honours, on the soft band. */
	private static function awards(): array {
		$award = static function ( string $tag, string $title, string $text ): array {
			return array(
				'icon'  => 'trophy',
				'tag'   => $tag,
				'title' => $title,
				'text'  => $text,
			);
		};

		$line = static function ( string $label, string $text ): array {
			return array(
				'label' => $label,
				'text'  => $text,
			);
		};

		return Blocks::band(
			array(
				Blocks::heading( 'افتخارات و جوایز', 'برگزیده‌ها و آثار پذیرفته‌شده در جشنواره‌های عکس' ),
				El::w(
					'lzp-card-grid',
					array(
						'items' => Blocks::rows(
							array(
								$award( 'بین‌المللی', 'نفر اول', 'جشنواره بین‌المللی خورشید ولایت' ),
								$award( 'ملی', 'نفر دوم', 'جشنواره ملی سوگواری هنر عاشورا — بجنورد' ),
								$award( 'ملی', 'نفر دوم', 'جشنواره برق و رسانه — وزارت برق' ),
								$award( '۱۳۸۶', 'کوچک‌ترین عکاس ایران', 'جشنواره عکس دریا' ),
							)
						),
					)
				),
				El::w(
					'lzp-simple-list',
					array(
						'items'   => Blocks::rows(
							array(
								$line( 'شایسته تقدیر', 'پذیرش سه عکس و عنوان شایسته تقدیر — جشنواره انرژی تجدیدپذیر' ),
								$line( 'پذیرش', 'ششمین جشنواره روستایی و عشایر «آسمان هشتم»' ),
								$line( 'پذیرش', 'جشنواره عکس نور' ),
								$line( 'پذیرش', 'پذیرش چندین عکس در سایت 35AWARDS' ),
								$line( 'پذیرش', 'جشنواره روایت همدلی' ),
							)
						),
						'_margin' => El::dims( array( 26, 0, 0, 0 ) ),
					)
				),
			),
			array( 'surface' => 'soft' )
		);
	}

	/** Collaboration cards and the numbered list of group exhibitions. */
	private static function collaborations(): array {
		$client = static function ( string $title, string $text ): array {
			return array(
				'title' => $title,
				'text'  => $text,
			);
		};

		$shows = array(
			'نمایشگاه گروهی «آن‌کادر» — مشهد',
			'نمایشگاه «عطر رمضان»',
			'پنجمین نمایشگاه سالانه موسسه عکاسان خراسان رضوی',
			'ششمین نمایشگاه سالانه موسسه عکاسان خراسان رضوی',
			'چهارمین نمایشگاه دوسالانه موسسه عکاسان خراسان رضوی',
			'نمایشگاه دوسالانه کانون عکس انجمن سینمای جوان',
		);

		return Blocks::band(
			array(
				Blocks::heading( 'سوابق همکاری' ),
				El::w(
					'lzp-card-grid',
					array(
						'size'    => 'sm',
						'items'   => Blocks::rows(
							array(
								$client( 'خبرگزاری مهر', 'عکاس' ),
								$client( 'شرکت فرس', 'عکاس و تصویربردار' ),
								$client( 'شرکت پارسینو', 'عکاس و تصویربردار' ),
								$client( 'شرکت‌های تبلیغاتی', 'تصویربردار' ),
							)
						),
						'_margin' => El::dims( array( 0, 0, 56, 0 ) ),
					)
				),
				Blocks::heading( 'نمایشگاه‌های گروهی' ),
				El::w(
					'lzp-simple-list',
					array(
						'numbered'       => 'yes',
						'columns'        => '3',
						'columns_tablet' => '2',
						'columns_mobile' => '1',
						'items'          => Blocks::rows(
							array_map(
								static function ( string $text ): array {
									return array( 'text' => $text );
								},
								$shows
							)
						),
					)
				),
			)
		);
	}

	/** Closing call to action with a studio photo. */
	private static function cta(): array {
		return Blocks::band(
			array(
				El::w(
					'lzp-cta-band',
					array(
						'title'          => 'بیا درباره پروژه‌ات حرف بزنیم',
						'text'           => 'اولین جلسه مشاوره رایگان است.',
						'primary_text'   => 'رزرو وقت',
						'primary_link'   => Blocks::link( Blocks::booking_url() ),
						'primary_icon'   => 'calendar',
						'secondary_text' => 'دیدن دوره‌ها',
						'secondary_link' => Blocks::link( Blocks::courses_url() ),
						'image_label'    => 'عکس آتلیه',
					)
				),
			),
			Blocks::pad_y( 52, 36, 24 )
		);
	}
}
