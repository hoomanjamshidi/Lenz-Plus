<?php
/**
 * The portfolio list and the project page of the mockups
 * (Design/… - Portfolio.dc.html and Project.dc.html), as route templates:
 * they replace Lenz's portfolio archive (and categories) and its project
 * page when chosen in Page templates → Site pages.
 *
 * The list shows the archive's projects with category chips, the featured
 * projects and a call to action. The project page reads everything from the
 * project being viewed: Lenz's title, text, featured image and gallery, and
 * the plugin's project details (summary, facts, checklist, quote).
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

	/** The project page. */
	public static function single(): array {
		return array(
			Blocks::band(
				array( El::w( 'lzp-breadcrumb', array( 'home_label' => 'خانه' ) ) ),
				array(
					'pad'        => array( 36, 40, 28 ),
					'pad_tablet' => array( 28, 24, 22 ),
					'pad_mobile' => array( 20, 16, 18 ),
				)
			),
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
				array(
					'pad'        => array( 0, 40, 44 ),
					'pad_tablet' => array( 0, 24, 32 ),
					'pad_mobile' => array( 0, 16, 24 ),
				)
			),
			Blocks::band(
				array(
					Blocks::columns(
						array(
							Blocks::heading( 'صورت مسئله', '', array( 'space_below' => El::px( 20 ) ) ),
							El::w( 'lzp-post-content' ),
						),
						array(
							Blocks::heading( 'آنچه انجام شد', '', array( 'space_below' => El::px( 20 ) ) ),
							El::w( 'lzp-checklist', array( 'source' => 'project' ) ),
						)
					),
				),
				self::tight()
			),
			Blocks::band(
				array(
					El::w(
						'lzp-project-gallery',
						array(
							'count'          => 4,
							'ratio'          => '4/5',
							'columns'        => '4',
							'columns_tablet' => '2',
							'columns_mobile' => '2',
						)
					),
				),
				self::tight()
			),
			Blocks::band(
				array(
					El::w(
						'lzp-project-gallery',
						array(
							'offset'         => 4,
							'count'          => 2,
							'ratio'          => '3/2',
							'columns'        => '2',
							'columns_tablet' => '2',
							'columns_mobile' => '1',
						)
					),
				),
				self::tight()
			),
			Blocks::band(
				array( El::w( 'lzp-quote', array( 'source' => 'project' ) ) ),
				array(
					'pad'        => array( 48, 40 ),
					'pad_tablet' => array( 36, 24 ),
					'pad_mobile' => array( 24, 16 ),
				)
			),
			Blocks::band(
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
			),
			self::cta(
				'محصولی داری که باید دیده شود؟',
				'تعداد آیتم‌ها را بگو تا برآورد زمان و هزینه بفرستم.',
				array( 'درخواست برآورد', Blocks::booking_url(), 'receipt' ),
				array( 'تعرفه عکاسی محصول', Blocks::design_page_url( 'services' ) . '#packages' ),
				'عکس چیدمان محصول'
			),
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
