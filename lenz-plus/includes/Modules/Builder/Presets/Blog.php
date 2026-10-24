<?php
/**
 * The blog page and the article page of the mockups (Design/… - Blog.dc.html
 * and Article.dc.html), as route templates: they replace Lenz's post lists
 * (posts page, categories, tags, authors, dates, blog searches) and its
 * article page when chosen in Page templates → Site pages.
 *
 * The list shows a title that follows the page (category name, search…),
 * the featured article, the posts with category chips beside a sidebar
 * (search, most read, a promo) and the newsletter band. The article reads
 * everything from the post being viewed: header, featured image, text with
 * a table of contents in the sidebar, comments and related articles.
 *
 * Three more article pages share those widgets: `cover` (the title on the
 * full-width featured image, a narrow centred text with a folding table of
 * contents), `split` (the title beside the featured image, the text beside
 * a sidebar with the contents, the most read and a promo) and `minimal` (a narrow reading column with nothing
 * beside it). All three end with the author box, the previous and next
 * articles, comments and related articles.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Presets;

defined( 'ABSPATH' ) || exit;

/**
 * Blog list and article preset builders.
 */
final class Blog {

	/** The post list. */
	public static function archive(): array {
		return array(
			Blocks::bleed(
				array(
					El::w(
						'lzp-page-hero',
						array(
							'layout'       => 'text',
							'title_source' => 'archive',
							'eyebrow'      => 'آرشیو مقالات',
							'title'        => 'یادداشت‌هایی از پشت دوربین',
							'lead'         => 'هر چه در پروژه‌ها یاد می‌گیرم اینجا می‌نویسم: نورپردازی، انتخاب تجهیزات، کار با مشتری و آماده شدن برای یک جلسه عکاسی.',
							'primary_text' => '',
							'watermark'    => 'NOTES ON LIGHT',
						)
					),
				)
			),
			Blocks::band(
				array(
					El::w(
						'lzp-featured-post',
						array(
							'badge'     => 'مقاله شاخص',
							'link_text' => 'خواندن مقاله',
						)
					),
				),
				array(
					'pad'        => array( 18, 40, 34 ),
					'pad_tablet' => array( 14, 24, 26 ),
					'pad_mobile' => array( 10, 16, 20 ),
				)
			),
			Blocks::band(
				array(
					self::with_sidebar(
						array(
							El::w(
								'lzp-post-grid',
								array(
									'source'         => 'current',
									'filters'        => 'yes',
									'columns'        => '3',
									'columns_tablet' => '2',
									'columns_mobile' => '1',
									'all_label'      => 'همه',
									'empty_text'     => 'در این دسته فعلاً مقاله‌ای منتشر نشده است.',
								)
							),
						),
						array(
							El::w(
								'lzp-post-search',
								array(
									'title'       => 'جست‌وجو در مقالات',
									'placeholder' => 'مثلاً نورپردازی',
								)
							),
							El::w( 'lzp-popular-posts', array( 'title' => 'پرخواننده‌ترین‌ها' ) ),
							El::w(
								'lzp-promo-box',
								array(
									'title'        => 'دوره‌ها',
									'text'         => 'اگر این یادداشت‌ها به کارت آمد، دوره‌های آموزشی مفصل‌ترش را هم ببین.',
									'primary_text' => 'آرشیو دوره‌ها',
									'primary_link' => Blocks::link( Blocks::design_page_url( 'courses' ) ),
								)
							),
						)
					),
				),
				array(
					'pad'        => array( 30, 40, 64 ),
					'pad_tablet' => array( 24, 24, 44 ),
					'pad_mobile' => array( 18, 16, 30 ),
				)
			),
			self::newsletter(),
		);
	}

	/** The article page. */
	public static function single(): array {
		return array(
			Blocks::band(
				array(
					El::w( 'lzp-reading-progress' ),
					El::w( 'lzp-breadcrumb', array( 'home_label' => 'خانه' ) ),
				),
				array(
					'pad'        => array( 36, 40, 22 ),
					'pad_tablet' => array( 28, 24, 18 ),
					'pad_mobile' => array( 20, 16, 14 ),
				)
			),
			Blocks::bleed( array( El::w( 'lzp-post-header' ) ) ),
			Blocks::band(
				array( El::w( 'lzp-post-image' ) ),
				array(
					'boxed'      => 1000,
					'pad'        => array( 0, 40, 40 ),
					'pad_tablet' => array( 0, 24, 32 ),
					'pad_mobile' => array( 0, 16, 24 ),
				)
			),
			Blocks::band(
				array(
					self::with_sidebar(
						array(
							El::w( 'lzp-post-content', array( 'size' => 'lg' ) ),
							El::w(
								'lzp-promo-box',
								array(
									'look'         => 'dashed',
									'label'        => 'مرتبط با این نوشته',
									'title'        => 'کارگاه نورپردازی پرتره در آتلیه',
									'text'         => 'دو روز کار عملی با فلاش و نور پیوسته روی سوژه واقعی.',
									'primary_text' => 'دیدن دوره',
									'primary_link' => Blocks::link( Blocks::design_page_url( 'courses' ) ),
									'_margin'      => El::dims( array( 30, 0, 0, 0 ) ),
								)
							),
							El::w( 'lzp-post-comments', array( '_margin' => El::dims( array( 40, 0, 0, 0 ) ) ) ),
						),
						array(
							El::w( 'lzp-post-toc', array( 'title' => 'در این مقاله' ) ),
							El::w(
								'lzp-promo-box',
								array(
									'title'        => 'جلسه پرتره',
									'text'         => 'اگر ترجیح می‌دهی خودت جلوی دوربین بنشینی، یک جلسه رزرو کن.',
									'primary_text' => 'رزرو وقت',
									'primary_link' => Blocks::link( Blocks::booking_url() ),
									'primary_icon' => 'calendar',
								)
							),
						),
						44
					),
				),
				array(
					'boxed'      => 1000,
					'pad'        => array( 0, 40, 60 ),
					'pad_tablet' => array( 0, 24, 44 ),
					'pad_mobile' => array( 0, 16, 30 ),
				)
			),
			self::related(),
			self::newsletter(),
		);
	}

	/** Title on the full-width featured image, a centred reading column with a folding table of contents. */
	public static function cover(): array {
		return array(
			El::box( array( 'pad' => 0 ), array( El::w( 'lzp-reading-progress' ) ) ),
			Blocks::bleed(
				array(
					El::w(
						'lzp-post-header',
						array(
							'layout' => 'cover',
							'align'  => 'center',
						)
					),
				)
			),
			Blocks::band(
				array(
					El::w( 'lzp-breadcrumb', array( 'home_label' => 'خانه' ) ),
					El::w(
						'lzp-post-toc',
						array(
							'look'    => 'inline',
							'title'   => 'در این مقاله',
							'_margin' => El::dims( array( 18, 0, 26, 0 ) ),
						)
					),
					El::w( 'lzp-post-content', array( 'size' => 'lg' ) ),
				),
				self::column_band( 780, array( 30, 0, 40 ) )
			),
			self::closing( 780 ),
			self::related(),
			self::newsletter(),
		);
	}

	/** Title beside the featured image, the text beside a sticky sidebar. */
	public static function split(): array {
		return array(
			El::box( array( 'pad' => 0 ), array( El::w( 'lzp-reading-progress' ) ) ),
			Blocks::band(
				array( El::w( 'lzp-breadcrumb', array( 'home_label' => 'خانه' ) ) ),
				array(
					'pad'        => array( 30, 40, 6 ),
					'pad_tablet' => array( 24, 24, 4 ),
					'pad_mobile' => array( 18, 16, 2 ),
				)
			),
			Blocks::bleed(
				array(
					El::w(
						'lzp-post-header',
						array(
							'layout' => 'split',
							'guides' => '',
						)
					),
				)
			),
			Blocks::band(
				array(
					self::with_sidebar(
						array(
							El::w( 'lzp-post-content', array( 'size' => 'lg' ) ),
							El::w( 'lzp-author-box', array( '_margin' => El::dims( array( 36, 0, 0, 0 ) ) ) ),
							El::w( 'lzp-post-nav', array( '_margin' => El::dims( array( 20, 0, 0, 0 ) ) ) ),
							El::w( 'lzp-post-comments', array( '_margin' => El::dims( array( 40, 0, 0, 0 ) ) ) ),
						),
						array(
							El::w( 'lzp-post-toc', array( 'title' => 'در این مقاله' ) ),
							El::w( 'lzp-popular-posts', array( 'title' => 'پرخواننده‌ترین‌ها' ) ),
							El::w(
								'lzp-promo-box',
								array(
									'title'        => 'جلسه پرتره',
									'text'         => 'اگر ترجیح می‌دهی خودت جلوی دوربین بنشینی، یک جلسه رزرو کن.',
									'primary_text' => 'رزرو وقت',
									'primary_link' => Blocks::link( Blocks::booking_url() ),
									'primary_icon' => 'calendar',
								)
							),
						),
						44
					),
				),
				array(
					'pad'        => array( 0, 40, 60 ),
					'pad_tablet' => array( 0, 24, 44 ),
					'pad_mobile' => array( 0, 16, 30 ),
				)
			),
			self::related(),
			self::newsletter(),
		);
	}

	/** A narrow reading column: centred header, image, text, author and comments. */
	public static function minimal(): array {
		return array(
			Blocks::band(
				array(
					El::w( 'lzp-reading-progress' ),
					El::w( 'lzp-breadcrumb', array( 'home_label' => 'خانه' ) ),
				),
				self::column_band( 700, array( 36, 0, 22 ) )
			),
			Blocks::bleed(
				array(
					El::w(
						'lzp-post-header',
						array(
							'align'        => 'center',
							'show_excerpt' => 'yes',
							'guides'       => '',
						)
					),
				)
			),
			Blocks::band(
				array( El::w( 'lzp-post-image' ) ),
				self::column_band( 900, array( 0, 0, 34 ) )
			),
			Blocks::band(
				array( El::w( 'lzp-post-content', array( 'size' => 'lg' ) ) ),
				self::column_band( 700, array( 0, 0, 36 ) )
			),
			self::closing( 700 ),
			self::newsletter(),
		);
	}

	/**
	 * A band boxed to a reading width.
	 *
	 * @param int   $width Box width.
	 * @param int[] $pad   Top, sides (ignored: the gutters apply) and bottom padding.
	 */
	private static function column_band( int $width, array $pad ): array {
		return array(
			'boxed'      => $width,
			'pad'        => array( $pad[0], 40, $pad[2] ),
			'pad_tablet' => array( (int) round( $pad[0] * 0.8 ), 24, (int) round( $pad[2] * 0.8 ) ),
			'pad_mobile' => array( (int) round( $pad[0] * 0.6 ), 16, (int) round( $pad[2] * 0.6 ) ),
		);
	}

	/**
	 * Author box, previous and next articles and comments, in a reading column.
	 *
	 * @param int $width Column width.
	 */
	private static function closing( int $width ): array {
		return Blocks::band(
			array(
				El::w( 'lzp-author-box' ),
				El::w( 'lzp-post-nav', array( '_margin' => El::dims( array( 20, 0, 0, 0 ) ) ) ),
				El::w( 'lzp-post-comments', array( '_margin' => El::dims( array( 40, 0, 0, 0 ) ) ) ),
			),
			self::column_band( $width, array( 0, 0, 60 ) )
		);
	}

	/** Related articles on the soft band. */
	private static function related(): array {
		return Blocks::band(
			array(
				El::w(
					'lzp-related-items',
					array(
						'post_type'      => 'post',
						'look'           => 'card',
						'title'          => 'مقالات مرتبط',
						'button_text'    => 'همه مقالات',
						'columns'        => '3',
						'columns_tablet' => '2',
						'columns_mobile' => '1',
					)
				),
			),
			array_merge( Blocks::pad_y( 56, 40, 28 ), array( 'surface' => 'soft' ) )
		);
	}

	/**
	 * A main column (two thirds) beside a sticky sidebar (one third) that
	 * drops under it on phones and tablets.
	 *
	 * @param array $main Main column content.
	 * @param array $side Sidebar content.
	 * @param int   $gap  Gap between the columns on wide screens.
	 */
	private static function with_sidebar( array $main, array $side, int $gap = 40 ): array {
		return El::box(
			array(
				'dir'        => 'row',
				'dir_tablet' => 'column',
				'align'      => 'flex-start',
				'gap'        => $gap,
				'gap_tablet' => 28,
				'gap_mobile' => 24,
			),
			array(
				El::box(
					array(
						'width'        => 66,
						'width_tablet' => 100,
						'width_mobile' => 100,
						'fill'         => true,
					),
					$main
				),
				El::box(
					array(
						'width'         => 34,
						'width_tablet'  => 100,
						'width_mobile'  => 100,
						'fill'          => true,
						'gap'           => 20,
						'gap_mobile'    => 14,
						'sticky'        => 'desktop',
						'sticky_offset' => 90,
						'tag'           => 'aside',
					),
					$side
				),
			)
		);
	}

	/** «هر ماه یک یادداشت، بدون تبلیغ» sign-up band. */
	private static function newsletter(): array {
		return Blocks::band(
			array(
				El::w(
					'lzp-newsletter-form',
					array(
						'title'        => 'هر ماه یک یادداشت، بدون تبلیغ',
						'text'         => 'ایمیلت را بگذار تا نوشته‌های تازه و تمرین‌های عکاسی را برایت بفرستم.',
						'placeholder'  => 'ایمیل شما',
						'button_text'  => 'عضویت در خبرنامه',
						'list_name'    => 'خبرنامه',
						'success_text' => 'ثبت شد؛ اولین یادداشت به‌زودی می‌رسد.',
					)
				),
			),
			Blocks::pad_y( 52, 36, 24 )
		);
	}
}
