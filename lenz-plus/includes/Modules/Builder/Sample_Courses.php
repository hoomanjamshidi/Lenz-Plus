<?php
/**
 * Ready-made courses offered on Page templates → Pages: one click adds the
 * course as a WooCommerce product with every course detail filled in, a
 * cover picture and its page design, so the course landing page works at
 * once. The studio then sets the price, date and seats and publishes it.
 *
 * Like the page presets, the courses hold their Persian copy directly: it is
 * demo content written for the studio, not interface text. The cover is a
 * drawing bundled with the plugin (`assets/modules/builder/img/`), copied
 * into the media library so it can be swapped like any other photo.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

defined( 'ABSPATH' ) || exit;

/**
 * Catalogue and installer of ready-made courses.
 */
final class Sample_Courses {

	/** Product meta naming the ready-made course a product came from. */
	public const META = '_lzp_sample_course';

	/** The page design ready-made courses get when the site has none yet. */
	private const DESIGN = 'course-single';

	/**
	 * Ready-made courses by key: label and description for the admin card,
	 * product title, lead (short description), text, cover file and the
	 * course details in Course_Meta_Box's form shape (lists one per line,
	 * pairs as «label | value»), cleaned by Course_Meta_Box::clean().
	 *
	 * @return array<string, array{label:string, description:string, title:string, lead:string, text:string, cover:string, details:array}>
	 */
	public static function all(): array {
		return array(
			'mobile-photography' => array(
				'label'       => __( 'Mobile photography workshop', 'lenz-plus' ),
				'description' => __( 'A 3 to 4 hour hands-on workshop: seeing, light, composition and the phone camera, a city photo walk, choosing and editing, and a photo critique. Who it is for, what you learn, seven sessions, questions and the outcome are filled in.', 'lenz-plus' ),
				'title'       => 'از دیدن تا ثبت کردن',
				'lead'        => 'یک ورکشاپ عملی برای کسانی که می‌خواهند با موبایلشان بهتر ببینند، بهتر کادر ببندند و عکس‌های بهتری ثبت کنند.',
				'text'        => 'در این ورکشاپ از اصول پایه عکاسی شروع می‌کنیم و با نور، ترکیب‌بندی، نگاه عکاسانه و تکنیک‌های کاربردی عکاسی با موبایل آشنا می‌شویم؛ سپس آموخته‌ها را در یک تمرین عملی عکاسی می‌کنیم و عکس‌ها را بررسی می‌کنیم.',
				'cover'       => 'course-mobile-photography.jpg',
				'details'     => array(
					'status'        => 'open',
					'kind'          => 'ورکشاپ عملی',
					'topic'         => 'عکاسی با موبایل',
					'level'         => 'مقدماتی',
					'format'        => 'offline',
					'duration'      => '۳ تا ۴ ساعت',
					'tools'         => 'موبایل',
					'registration'  => 'cart',
					'specs'         => 'شیوه برگزاری | تئوری + تمرین عملی + نقد عکس',
					'learn'         => implode(
						"\n",
						array(
							'چطور بهتر ببینیم و سوژه پیدا کنیم',
							'شناخت نور و استفاده درست از آن',
							'اصول ساده و کاربردی ترکیب‌بندی',
							'انتخاب زاویه و کادر مناسب',
							'استفاده بهتر از امکانات دوربین موبایل',
							'عکاسی در فضای شهری',
							'انتخاب عکس بهتر',
							'اصول اولیه ادیت عکس',
							'نگاه کردن و نقد کردن عکس',
						)
					),
					'audience'      => implode(
						"\n",
						array(
							'با موبایل زیاد عکس می‌گیرند اما از نتیجه راضی نیستند.',
							'می‌خواهند عکاسی را اصولی‌تر یاد بگیرند.',
							'به عکاسی شهری، روزمره و سفر علاقه دارند.',
							'می‌خواهند بدون تجهیزات حرفه‌ای عکس‌های بهتری ثبت کنند.',
							'می‌خواهند نگاه و سلیقه عکاسانه خودشان را تقویت کنند.',
						)
					),
					'audience_note' => 'برای شرکت در دوره، داشتن موبایل خاصی لازم نیست. مهم این است که گوشی شما دوربین داشته باشد.',
					'not_for'       => implode(
						"\n",
						array(
							'دنبال آموزش دوربین‌های حرفه‌ای و تنظیمات دستی آن‌ها هستند.',
							'فقط تئوری می‌خواهند و وقتی برای تمرین در شهر ندارند.',
							'انتظار دارند بدون تمرین، از جلسه اول عکس‌های حرفه‌ای بگیرند.',
						)
					),
					'curriculum'    => implode(
						"\n",
						array(
							'نگاه عکاسانه | دیدن، انتخاب کردن و تصمیم گرفتن قبل از ثبت عکس',
							'شناخت دوربین موبایل | لنزها، فوکوس، نوردهی و امکانات کاربردی دوربین',
							'نور | شناخت نور و پیدا کردن نور مناسب',
							'ترکیب‌بندی | کادر، زاویه، خطوط، فضای منفی و پس‌زمینه',
							'عکاسی شهری | تمرین عملی و پیدا کردن سوژه در محیط واقعی',
							'انتخاب و ادیت | انتخاب عکس بهتر و اصلاحات پایه',
							'نقد عکس | بررسی عکس‌ها و پیدا کردن نقاط قوت و ضعف آن‌ها',
						)
					),
					'outcome'       => 'در پایان ورکشاپ، مخاطب با اصول پایه عکاسی با موبایل آشناست و می‌تواند آگاهانه‌تر سوژه پیدا کند، نور و کادر را انتخاب کند و عکس خود را ثبت و بررسی کند.',
					'faq'           => implode(
						"\n",
						array(
							'آیا باید عکاسی بلد باشم؟ | خیر. ورکشاپ از مفاهیم پایه شروع می‌شود.',
							'آیا نوع یا مدل موبایل مهم است؟ | خیر. مفاهیم اصلی دوره برای عکاسی با موبایل طراحی شده و به برند خاصی محدود نیست.',
							'آیا تمرین عملی داریم؟ | بله. بخشی از ورکشاپ به عکاسی در فضای واقعی اختصاص دارد.',
							'آیا عکس‌های خودمان بررسی می‌شوند؟ | بله، در بخش پایانی عکس‌ها بررسی و نقد می‌شوند.',
							'آیا ادیت عکس هم آموزش داده می‌شود؟ | بله، در حد اصول و اصلاحات کاربردی.',
							'آیا این دوره فقط درباره تنظیمات دوربین است؟ | خیر. تمرکز اصلی روی دیدن، نور، ترکیب‌بندی و تصمیم‌گیری برای ثبت عکس است.',
						)
					),
				),
			),
		);
	}

	/**
	 * The ready-made courses for the admin card: key, label, description,
	 * cover URL and the products already made from each.
	 *
	 * @return array<int, array{key:string, label:string, description:string, cover:string, made:array}>
	 */
	public static function for_admin(): array {
		$list = array();
		foreach ( self::all() as $key => $course ) {
			$list[] = array(
				'key'         => $key,
				'label'       => $course['label'],
				'description' => $course['description'],
				'cover'       => LENZ_PLUS_URL . 'assets/modules/builder/img/' . $course['cover'],
				'made'        => self::made_from( $key ),
			);
		}

		return $list;
	}

	/**
	 * Adds a ready-made course as a draft product. Returns its ID, 0 when
	 * WooCommerce is missing or the key is unknown.
	 *
	 * @param string $key     Course key.
	 * @param bool   $publish Publish at once instead of keeping a draft.
	 */
	public static function create( string $key, bool $publish = false ): int {
		$course = self::all()[ $key ] ?? null;
		if ( ! $course || ! class_exists( 'WC_Product_Simple' ) ) {
			return 0;
		}

		$product = new \WC_Product_Simple();
		$product->set_name( $course['title'] );
		$product->set_short_description( $course['lead'] );
		$product->set_description( $course['text'] );
		$product->set_status( $publish ? 'publish' : 'draft' );
		// A seat, not a parcel: no shipping, the checkout's short form.
		$product->set_virtual( true );
		$product->set_sold_individually( true );

		$cover = self::import_cover( $course['cover'], $course['title'] );
		if ( $cover ) {
			$product->set_image_id( $cover );
		}

		$id = (int) $product->save();
		if ( ! $id ) {
			return 0;
		}

		if ( $cover ) {
			wp_update_post(
				array(
					'ID'          => $cover,
					'post_parent' => $id,
				)
			);
		}

		update_post_meta( $id, Course_Data::META_FLAG, '1' );
		update_post_meta( $id, Course_Data::META, Course_Meta_Box::clean( $course['details'] ) );
		update_post_meta( $id, self::META, $key );

		return $id;
	}

	/**
	 * Makes sure course pages have a design: when the Course page still
	 * follows the theme, the ready-made «Course» design is used. Returns
	 * whether the setting was changed.
	 *
	 * @param Module $module Builder module.
	 */
	public static function ensure_design( Module $module ): bool {
		$settings = $module->settings();
		if ( 'theme' !== ( $settings['routes']['course'] ?? 'theme' ) ) {
			return false;
		}

		$template = Library::preset_template( self::DESIGN );
		if ( ! $template ) {
			return false;
		}

		$settings['routes']['course'] = (string) $template;
		$module->save( $settings );

		return true;
	}

	/**
	 * Products made from a ready-made course: ID, title, status and links.
	 *
	 * @param string $key Course key.
	 * @return array<int, array{id:int, title:string, status:string, editUrl:string, viewUrl:string}>
	 */
	private static function made_from( string $key ): array {
		$ids = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'posts_per_page' => 20,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => self::META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- admin card, a handful of products.
				'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		return array_map(
			static function ( int $id ): array {
				return array(
					'id'      => $id,
					'title'   => get_the_title( $id ),
					'status'  => (string) get_post_status( $id ),
					'editUrl' => (string) get_edit_post_link( $id, 'raw' ),
					'viewUrl' => (string) get_permalink( $id ),
				);
			},
			array_map( 'intval', $ids )
		);
	}

	/**
	 * Copies a bundled cover into the media library; returns the attachment ID (0 on failure).
	 *
	 * @param string $file  File name under assets/modules/builder/img/.
	 * @param string $title Attachment title and alt text.
	 */
	private static function import_cover( string $file, string $title ): int {
		$source = LENZ_PLUS_DIR . 'assets/modules/builder/img/' . $file;
		if ( ! is_readable( $source ) ) {
			return 0;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// media_handle_sideload() moves the file it gets, so it gets a copy.
		$copy = wp_tempnam( $file );
		if ( ! $copy || ! copy( $source, $copy ) ) {
			return 0;
		}

		$id = media_handle_sideload(
			array(
				'name'     => $file,
				'tmp_name' => $copy,
			),
			0,
			$title
		);

		if ( is_wp_error( $id ) ) {
			wp_delete_file( $copy );
			return 0;
		}

		update_post_meta( (int) $id, '_wp_attachment_image_alt', $title );

		return (int) $id;
	}
}
