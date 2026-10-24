<?php
/**
 * Registry of the ready-made designs (presets).
 *
 * Each entry names its template kind, admin label and description, and the
 * builder method that returns its Elementor data (written with El::box() /
 * El::w()). Library installs them as editable templates and refreshes the
 * unedited ones when the plugin version changes.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Presets;

defined( 'ABSPATH' ) || exit;

/**
 * Preset keys → kind, label, description and builder.
 */
final class Catalog {

	/**
	 * Every preset, in admin order.
	 *
	 * @return array<string, array{type:string, label:string, description:string, build:callable, project?:string}>
	 */
	public static function all(): array {
		return array(
			'header'              => array(
				'type'        => 'header',
				'label'       => __( 'Header', 'lenz-plus' ),
				'description' => __( 'Logo and name, the menu, a booking button and the phone, on a translucent bar; a compact bar with the menu button on phones.', 'lenz-plus' ),
				'build'       => array( Header::class, 'main' ),
			),
			'footer'              => array(
				'type'        => 'footer',
				'label'       => __( 'Footer', 'lenz-plus' ),
				'description' => __( 'Brand box with a description and social buttons, both footer menus and the contact card, on the ink band.', 'lenz-plus' ),
				'build'       => array( Footer::class, 'full' ),
			),
			'footer-compact'      => array(
				'type'        => 'footer',
				'label'       => __( 'Compact footer', 'lenz-plus' ),
				'description' => __( 'Brand box, quick links and the contact card, as on the about and project pages.', 'lenz-plus' ),
				'build'       => array( Footer::class, 'compact' ),
			),
			'footer-store'        => array(
				'type'        => 'footer',
				'label'       => __( 'Footer with trust badges', 'lenz-plus' ),
				'description' => __( 'The full footer with a row of trust badges (eNamad, Samandehi, Zarinpal) and a line about safe payment above the copyright.', 'lenz-plus' ),
				'build'       => array( Footer::class, 'store' ),
			),
			'home'                => array(
				'type'        => 'home',
				'label'       => __( 'Home page', 'lenz-plus' ),
				'description' => __( 'Film strip hero with a showreel, services, about me, key numbers, the portfolio, courses, the blog, testimonials, the booking form and questions.', 'lenz-plus' ),
				'build'       => array( Home::class, 'page' ),
			),
			'about'               => array(
				'type'        => 'about',
				'label'       => __( 'About me', 'lenz-plus' ),
				'description' => __( 'Portrait hero, key numbers, story and career path, a framed motto, working steps, equipment, awards, collaborations and a call to action.', 'lenz-plus' ),
				'build'       => array( About::class, 'page' ),
			),
			'services'            => array(
				'type'        => 'services',
				'label'       => __( 'Services', 'lenz-plus' ),
				'description' => __( 'Hero, jump tiles, four services with photos (one on the ink band), price cards with add-ons, project steps, FAQ and a call to action.', 'lenz-plus' ),
				'build'       => array( Services::class, 'page' ),
			),
			'contact'             => array(
				'type'        => 'contact',
				'label'       => __( 'Contact: classic', 'lenz-plus' ),
				'description' => __( 'Text hero, four contact cards, the contact form beside the map, working hours and social links, then a few questions.', 'lenz-plus' ),
				'build'       => array( Contact::class, 'classic' ),
			),
			'contact-split'       => array(
				'type'        => 'contact',
				'label'       => __( 'Contact: ink panel', 'lenz-plus' ),
				'description' => __( 'Contact rows and social links on the ink panel beside the form, a wide map and a call to action.', 'lenz-plus' ),
				'build'       => array( Contact::class, 'split' ),
			),
			'contact-minimal'     => array(
				'type'        => 'contact',
				'label'       => __( 'Contact: minimal', 'lenz-plus' ),
				'description' => __( 'A centred title, phone, email and address in large type, a wide map and the form in the ink frame.', 'lenz-plus' ),
				'build'       => array( Contact::class, 'minimal' ),
			),
			'portfolio-archive'   => array(
				'type'        => 'portfolio_archive',
				'label'       => __( 'Portfolio', 'lenz-plus' ),
				'description' => __( 'Title, category chips and a masonry of the projects with page numbers, the featured projects and a call to action.', 'lenz-plus' ),
				'build'       => array( Portfolio::class, 'archive' ),
			),
			'portfolio-single'    => array(
				'type'        => 'portfolio',
				'project'     => 'photo',
				'label'       => __( 'Photo project', 'lenz-plus' ),
				'description' => __( 'Breadcrumb, title with facts, the cover, the project text beside a checklist, the gallery photos, the client\'s quote, related projects and a call to action.', 'lenz-plus' ),
				'build'       => array( Portfolio::class, 'single' ),
			),
			'portfolio-video'     => array(
				'type'        => 'portfolio',
				'project'     => 'video',
				'label'       => __( 'Video project', 'lenz-plus' ),
				'description' => __( 'Title with facts, the film in a wide player on the ink band, the project text beside a checklist, the other videos, stills, the client\'s quote, related projects and a call to action.', 'lenz-plus' ),
				'build'       => array( Portfolio::class, 'video' ),
			),
			'portfolio-mixed'     => array(
				'type'        => 'portfolio',
				'project'     => 'mixed',
				'label'       => __( 'Photo and video project', 'lenz-plus' ),
				'description' => __( 'Title with facts, the cover, the project text beside a checklist, the photos, the videos on the ink band, the client\'s quote, related projects and a call to action.', 'lenz-plus' ),
				'build'       => array( Portfolio::class, 'mixed' ),
			),
			'blog-archive'        => array(
				'type'        => 'blog',
				'label'       => __( 'Blog', 'lenz-plus' ),
				'description' => __( 'A title that follows the page, the featured article, posts with category chips beside a sidebar (search, most read, courses) and the newsletter band.', 'lenz-plus' ),
				'build'       => array( Blog::class, 'archive' ),
			),
			'blog-single'         => array(
				'type'        => 'post',
				'label'       => __( 'Article', 'lenz-plus' ),
				'description' => __( 'Reading progress, breadcrumb, header with author and share buttons, the featured image, the text beside a table of contents, comments, related articles and the newsletter band.', 'lenz-plus' ),
				'build'       => array( Blog::class, 'single' ),
			),
			'blog-single-cover'   => array(
				'type'        => 'post',
				'label'       => __( 'Article with a cover', 'lenz-plus' ),
				'description' => __( 'The title on the full-width featured image, a centred reading column with a folding table of contents, the author box, previous and next articles, comments and related articles.', 'lenz-plus' ),
				'build'       => array( Blog::class, 'cover' ),
			),
			'blog-single-split'   => array(
				'type'        => 'post',
				'label'       => __( 'Article with the image beside the title', 'lenz-plus' ),
				'description' => __( 'The title beside the featured image, the text beside a sticky sidebar (contents, most read, booking), the author box, previous and next articles, comments and related articles.', 'lenz-plus' ),
				'build'       => array( Blog::class, 'split' ),
			),
			'blog-single-minimal' => array(
				'type'        => 'post',
				'label'       => __( 'Minimal article', 'lenz-plus' ),
				'description' => __( 'A narrow reading column without a sidebar: centred header, the featured image, the text, the author box, previous and next articles, comments and the newsletter band.', 'lenz-plus' ),
				'build'       => array( Blog::class, 'minimal' ),
			),
			'courses'             => array(
				'type'        => 'courses',
				'label'       => __( 'Courses', 'lenz-plus' ),
				'description' => __( 'Title, course cards with status and format chips (open, coming soon with a waitlist, held), how classes are held and the waitlist band.', 'lenz-plus' ),
				'build'       => array( Courses::class, 'page' ),
			),
			'course-single'       => array(
				'type'        => 'course',
				'label'       => __( 'Course', 'lenz-plus' ),
				'description' => __( 'Hero with facts and the intro video, what you learn, curriculum, who it is for, outcome, instructor, questions, the buy box, the registration form and the phone buy bar.', 'lenz-plus' ),
				'build'       => array( Courses::class, 'single' ),
			),
			'course-sidebar'      => array(
				'type'        => 'course',
				'label'       => __( 'Course with a sticky buy box', 'lenz-plus' ),
				'description' => __( 'The same course page with the buy box beside the content, following the visitor while scrolling.', 'lenz-plus' ),
				'build'       => array( Courses::class, 'single_sidebar' ),
			),
		);
	}

	/**
	 * Every preset key.
	 *
	 * @return string[]
	 */
	public static function keys(): array {
		return array_keys( self::all() );
	}

	/**
	 * One preset, or null for an unknown key.
	 *
	 * @param string $key Preset key.
	 */
	public static function get( string $key ): ?array {
		return self::all()[ $key ] ?? null;
	}

	/**
	 * Project type (Portfolio_Data::KINDS) a project page preset is made
	 * for, '' for other presets and custom templates.
	 *
	 * @param string $key Preset key.
	 */
	public static function project_kind( string $key ): string {
		return (string) ( self::all()[ $key ]['project'] ?? '' );
	}

	/**
	 * Elementor data for a preset.
	 *
	 * @param string $key Preset key.
	 */
	public static function build( string $key ): array {
		$preset = self::get( $key );

		return $preset ? El::finalize( call_user_func( $preset['build'] ) ) : array();
	}
}
