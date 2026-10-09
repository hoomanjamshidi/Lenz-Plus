<?php
/**
 * The portfolio list and the project page of the mockups
 * (Design/… - Portfolio.dc.html and Project.dc.html), as route templates:
 * they replace Lenz's portfolio archive (and categories) and its project
 * page when chosen in Page templates → Site pages.
 *
 * The list shows the archive's projects with category chips, the featured
 * projects and a call to action. The project pages read everything from the
 * project being viewed: Lenz's title, text, featured image and gallery, and
 * the plugin's project details (summary, facts, checklist, quote). There is
 * one per project type (Portfolio_Data::KINDS): the mockups' page for photo
 * projects, and two built on it for video projects (the film first, on the
 * ink band, then the other videos and stills) and photo and video projects
 * (the cover and photos first, then the videos on the ink band). Their media
 * bands hide themselves when the project has nothing for them.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Presets;

defined( 'ABSPATH' ) || exit;

/**
 * Portfolio list and project page preset builders.
 */
final class Portfolio {

	/** The portfolio list. */
	public static function archive(): array {
		return array(
			Blocks::bleed(
				array(
					El::w(
						'lzp-page-hero',
						array(
							'layout'       => 'text',
							'eyebrow'      => 'نمونه‌کارها',
							'title'        => 'کارهایی که تا امروز ثبت کرده‌ام',
							'lead'         => 'مجموعه‌ای از پرتره، عکاسی مذهبی، عکاسی محصول، پوشش رویداد، مستند و ویدیو. برای دیدن آرشیو کامل هر پروژه می‌توانی پیام بدهی.',
							'primary_text' => '',
							'watermark'    => 'SELECTED WORK',
						)
					),
				)
			),
			Blocks::band(
				array( El::w( 'lzp-portfolio-grid', array( 'source' => 'current' ) ) ),
				array(
					'pad'        => array( 24, 40, 64 ),
					'pad_tablet' => array( 20, 24, 44 ),
					'pad_mobile' => array( 14, 16, 30 ),
				)
			),
			Blocks::band(
				array(
					Blocks::heading( 'پروژه‌های شاخص', 'سه کاری که بیشترین زمان و انرژی را برده‌اند' ),
					El::w( 'lzp-featured-projects', array( 'link_text' => 'مشاهده پروژه' ) ),
				),
				array( 'surface' => 'soft' )
			),
			self::cta(
				'پروژه بعدی می‌تواند مال تو باشد',
				'بگو چه چیزی در ذهن داری تا با هم جلویش برویم.',
				array( 'رزرو وقت', Blocks::booking_url(), 'calendar' ),
				array( 'خدمات و تعرفه‌ها', Blocks::design_page_url( 'services' ) ),
				'عکس پشت صحنه'
			),
		);
	}

	/** The project page of photo projects (the mockups' page). */
	public static function single(): array {
		return array(
			self::breadcrumb(),
			Blocks::bleed( array( El::w( 'lzp-project-header' ) ) ),
			Blocks::band(
				array(
					El::w(
						'lzp-project-gallery',
						array(
							'source' => 'cover',
							'ratio'  => '16/9',
						)
					),
				),
				self::below_header()
			),
			self::story( 'صورت مسئله' ),
			Blocks::band( array( self::gallery( 'image', 0, 4, '4/5', array( 4, 2, 2 ) ) ), self::tight() ),
			Blocks::band( array( self::gallery( 'image', 4, 2, '3/2', array( 2, 2, 1 ) ) ), self::tight() ),
			self::quote(),
			self::related(),
			self::cta(
				'محصولی داری که باید دیده شود؟',
				'تعداد آیتم‌ها را بگو تا برآورد زمان و هزینه بفرستم.',
				array( 'درخواست برآورد', Blocks::booking_url(), 'receipt' ),
				array( 'تعرفه عکاسی محصول', Blocks::design_page_url( 'services' ) . '#packages' ),
				'عکس چیدمان محصول'
			),
		);
	}

	/** The project page of video projects: the film leads, then the story, other videos and stills. */
	public static function video(): array {
		return array(
			self::breadcrumb(),
			Blocks::bleed( array( El::w( 'lzp-project-header' ) ) ),
			self::media_band(
				array( self::gallery( 'video', 0, 1, '16/9', array( 1, 1, 1 ) ) ),
				array(
					'surface'    => 'ink',
					'pad'        => array( 40, 40 ),
					'pad_tablet' => array( 30, 24 ),
					'pad_mobile' => array( 16, 0 ),
				)
			),
			self::story( 'درباره پروژه' ),
			self::media_band( array( self::gallery( 'video', 1, 0, '16/9', array( 2, 2, 1 ), 'ویدیوهای دیگر این پروژه' ) ) ),
			self::media_band( array( self::gallery( 'image', 0, 6, '16/9', array( 3, 2, 2 ), 'فریم‌هایی از فیلم' ) ) ),
			self::quote(),
			self::related(),
			self::cta(
				'روایتی داری که باید دیده شود؟',
				'بگو ویدیو برای کجاست و چه حسی باید منتقل کند تا برآورد زمان و هزینه بفرستم.',
				array( 'درخواست برآورد', Blocks::booking_url(), 'receipt' ),
				array( 'خدمات تولید محتوا', Blocks::design_page_url( 'services' ) . '#content' ),
				'فریم پشت صحنه فیلم‌برداری'
			),
		);
	}

	/** The project page of photo and video projects: the cover and photos, then the videos on the ink band. */
	public static function mixed(): array {
		return array(
			self::breadcrumb(),
			Blocks::bleed( array( El::w( 'lzp-project-header' ) ) ),
			Blocks::band(
				array(
					El::w(
						'lzp-project-gallery',
						array(
							'source' => 'cover',
							'ratio'  => '16/9',
						)
					),
				),
				self::below_header()
			),
			self::story( 'صورت مسئله' ),
			self::media_band( array( self::gallery( 'image', 0, 4, '4/5', array( 4, 2, 2 ), 'عکس‌های پروژه' ) ) ),
			self::media_band( array( self::gallery( 'image', 4, 2, '3/2', array( 2, 2, 1 ) ) ) ),
			self::media_band(
				array(
					self::gallery( 'video', 0, 1, '16/9', array( 1, 1, 1 ), 'ویدیوهای پروژه' ),
					self::gallery( 'video', 1, 0, '16/9', array( 2, 2, 1 ) ),
				),
				array(
					'surface'    => 'ink',
					'gap'        => 18,
					'pad'        => array( 56, 40 ),
					'pad_tablet' => array( 40, 24 ),
					'pad_mobile' => array( 28, 16 ),
				)
			),
			self::quote(),
			self::related(),
			self::cta(
				'عکس و ویدیو را با هم برنامه‌ریزی کنیم',
				'یک جلسه، یک برنامه تصویربرداری و خروجی‌هایی که کنار هم یک‌دست دیده می‌شوند.',
				array( 'رزرو وقت', Blocks::booking_url(), 'calendar' ),
				array( 'پکیج‌ها و تعرفه‌ها', Blocks::design_page_url( 'services' ) . '#packages' ),
				'عکس پشت صحنه'
			),
		);
	}

	/** Breadcrumb band at the top of the project pages. */
	private static function breadcrumb(): array {
		return Blocks::band(
			array( El::w( 'lzp-breadcrumb', array( 'home_label' => 'خانه' ) ) ),
			array(
				'pad'        => array( 36, 40, 28 ),
				'pad_tablet' => array( 28, 24, 22 ),
				'pad_mobile' => array( 20, 16, 18 ),
			)
		);
	}

	/**
	 * The project text beside the "what was done" checklist.
	 *
	 * @param string $title Title of the text column.
	 */
	private static function story( string $title ): array {
		return Blocks::band(
			array(
				Blocks::columns(
					array(
						Blocks::heading( $title, '', array( 'space_below' => El::px( 20 ) ) ),
						El::w( 'lzp-post-content' ),
					),
					array(
						Blocks::heading( 'آنچه انجام شد', '', array( 'space_below' => El::px( 20 ) ) ),
						El::w( 'lzp-checklist', array( 'source' => 'project' ) ),
					)
				),
			),
			self::tight()
		);
	}

	/**
	 * A slice of the project's gallery.
	 *
	 * @param string $media   `image` or `video`.
	 * @param int    $offset  Items of that type skipped (shown above).
	 * @param int    $count   Items shown, 0 for all the rest.
	 * @param string $ratio   Frame ratio.
	 * @param int[]  $columns Columns on desktop, tablet and phone.
	 * @param string $title   Title shown with the items.
	 */
	private static function gallery( string $media, int $offset, int $count, string $ratio, array $columns, string $title = '' ): array {
		return El::w(
			'lzp-project-gallery',
			array(
				'media'          => $media,
				'offset'         => $offset,
				'count'          => $count,
				'ratio'          => $ratio,
				'title'          => $title,
				'columns'        => (string) $columns[0],
				'columns_tablet' => (string) $columns[1],
				'columns_mobile' => (string) $columns[2],
			)
		);
	}

	/**
	 * A band of gallery slices that hides itself when the project has none
	 * of their items (portfolio.css, `.lzp-hide-empty`).
	 *
	 * @param array $children Gallery widgets.
	 * @param array $o        Band options (default: the tight bands).
	 */
	private static function media_band( array $children, array $o = array() ): array {
		$o        = array_merge( self::tight(), $o );
		$o['set'] = array( 'css_classes' => 'lzp-hide-empty' );

		return Blocks::band( $children, $o );
	}

	/** The client's quote. */
	private static function quote(): array {
		return Blocks::band(
			array( El::w( 'lzp-quote', array( 'source' => 'project' ) ) ),
			array(
				'pad'        => array( 48, 40 ),
				'pad_tablet' => array( 36, 24 ),
				'pad_mobile' => array( 24, 16 ),
			)
		);
	}

	/** Related projects. */
	private static function related(): array {
		return Blocks::band(
			array(
				El::w(
					'lzp-related-items',
					array(
						'title'       => 'پروژه‌های مرتبط',
						'button_text' => 'همه نمونه‌کارها',
					)
				),
			),
			self::tight()
		);
	}

	/** The cover's band, right under the project header. */
	private static function below_header(): array {
		return array(
			'pad'        => array( 0, 40, 44 ),
			'pad_tablet' => array( 0, 24, 32 ),
			'pad_mobile' => array( 0, 16, 24 ),
		);
	}

	/** The project page's shorter bands (20–40px). */
	private static function tight(): array {
		return array(
			'pad'        => array( 40, 40 ),
			'pad_tablet' => array( 30, 24 ),
			'pad_mobile' => array( 20, 16 ),
		);
	}

	/**
	 * Closing call to action.
	 *
	 * @param string $title     Title.
	 * @param string $text      Text.
	 * @param array  $primary   [text, url, icon].
	 * @param array  $secondary [text, url].
	 * @param string $photo     Placeholder text of the photo.
	 */
	private static function cta( string $title, string $text, array $primary, array $secondary, string $photo ): array {
		return Blocks::band(
			array(
				El::w(
					'lzp-cta-band',
					array(
						'title'          => $title,
						'text'           => $text,
						'primary_text'   => $primary[0],
						'primary_link'   => Blocks::link( $primary[1] ),
						'primary_icon'   => $primary[2],
						'secondary_text' => $secondary[0],
						'secondary_link' => Blocks::link( $secondary[1] ),
						'image_label'    => $photo,
					)
				),
			),
			Blocks::pad_y( 52, 36, 24 )
		);
	}
}
