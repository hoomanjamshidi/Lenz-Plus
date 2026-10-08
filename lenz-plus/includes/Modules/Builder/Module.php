<?php
/**
 * Page templates module (Builder): Elementor templates and widgets that
 * rebuild the site designs (home, about, services, courses, portfolio, blog)
 * with the brand palette of the mockups, plus a template library with
 * ready-made designs that pages are created from.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

use LenzPlus\Admin\Admin;
use LenzPlus\Core\Module as Base_Module;
use LenzPlus\Core\Sanitizer;
use LenzPlus\Modules\Builder\Elementor\Integration;

defined( 'ABSPATH' ) || exit;

/**
 * Settings, admin panel and boot of the page templates.
 */
final class Module extends Base_Module {

	/** @var Resolver|null */
	private $resolver = null;

	/** Module id: the option name suffix and the admin page slug. */
	public function id(): string {
		return 'builder';
	}

	/** Feature name in the admin menu and on the dashboard. */
	public function title(): string {
		return __( 'Page templates', 'lenz-plus' );
	}

	/** One-line summary on the dashboard card. */
	public function description(): string {
		return __( 'Ready-made Elementor designs of your site\'s pages, with the brand palette, photo tone and guide lines of the designs.', 'lenz-plus' );
	}

	/** Semantic icon key for the admin menu and the dashboard card. */
	public function icon(): string {
		return 'palette';
	}

	/**
	 * Template kind → translated label.
	 *
	 * @return array<string, string>
	 */
	public static function type_labels(): array {
		return array(
			'header'            => __( 'Header', 'lenz-plus' ),
			'footer'            => __( 'Footer', 'lenz-plus' ),
			'home'              => __( 'Home page', 'lenz-plus' ),
			'about'             => __( 'About page', 'lenz-plus' ),
			'services'          => __( 'Services page', 'lenz-plus' ),
			'courses'           => __( 'Courses page', 'lenz-plus' ),
			'portfolio_archive' => __( 'Portfolio list', 'lenz-plus' ),
			'portfolio'         => __( 'Project page', 'lenz-plus' ),
			'blog'              => __( 'Post list', 'lenz-plus' ),
			'post'              => __( 'Article', 'lenz-plus' ),
			'course'            => __( 'Course page', 'lenz-plus' ),
		);
	}

	/** Default settings (see Schema). */
	public function defaults(): array {
		return Schema::defaults();
	}

	/**
	 * Everything is registered even while the module is off: templates stay
	 * editable, widgets keep working on normal Elementor pages and admins can
	 * preview designs before switching them on. The `enabled` flag only
	 * controls what replaces parts of the theme for visitors.
	 */
	public function register(): void {
		( new Template_Post_Type( $this ) )->register();
		( new Assets( $this ) )->register();
		( new Header_Footer( $this ) )->register();
		( new Page_Routes( $this ) )->register();
		( new Contact_Messages() )->register();
		( new Newsletter() )->register();
		( new Course_Shop() )->register();

		if ( did_action( 'elementor/loaded' ) ) {
			( new Integration( $this ) )->register();
		}

		if ( is_admin() ) {
			( new Contact_Inbox() )->register();
			( new Project_Meta_Box() )->register();
			( new Course_Meta_Box() )->register();
			add_action( 'admin_init', array( Library::class, 'maybe_upgrade' ) );
			( new Library_Ajax( $this ) )->register();
		}

		parent::register();
	}

	/** Nothing extra: see register(). */
	protected function boot(): void {}

	/** Template choice and preview links for this request. */
	public function resolver(): Resolver {
		if ( null === $this->resolver ) {
			$this->resolver = new Resolver( $this );
		}

		return $this->resolver;
	}

	/** Cleans posted settings with the schema. */
	public function sanitize( array $input ): array {
		return Sanitizer::apply( Schema::fields(), $input, Schema::defaults() );
	}

	/** Prints the settings panel. */
	public function render_admin(): void {
		$module = $this;
		require __DIR__ . '/views/admin.php';
	}

	/** Loads the panel's stylesheet and script. */
	public function enqueue_admin_assets(): void {
		wp_enqueue_style( 'lzp-builder-admin', LENZ_PLUS_URL . 'assets/modules/builder/css/builder-admin.css', array( 'lzp-admin' ), LENZ_PLUS_VERSION );
		wp_enqueue_script( 'lzp-builder-admin', LENZ_PLUS_URL . 'assets/modules/builder/js/builder-admin.js', array( 'lzp-admin' ), LENZ_PLUS_VERSION, true );
	}

	/** Data for the panel script (`window.lzpAdmin.moduleData`). */
	public function admin_script_data(): array {
		// First visit: install the ready-made designs so they can be picked right away.
		if ( Library::elementor_active() && false === get_option( Library::OPTION, false ) ) {
			Library::install_missing();
		}

		return array(
			'elementor'     => Library::elementor_status(),
			'typeLabels'    => self::type_labels(),
			'keywordThumbs' => array(
				'theme' => Thumbs::svg( 'theme' ),
				'none'  => Thumbs::svg( 'none' ),
				'same'  => Thumbs::svg( 'same' ),
			),
			'themePreview'  => $this->theme_previews(),
			'themeTitles'   => array(
				'portfolio_archive' => __( 'Lenz\'s portfolio list', 'lenz-plus' ),
				'portfolio'         => __( 'Lenz\'s project page', 'lenz-plus' ),
				'blog'              => __( 'Lenz\'s post list', 'lenz-plus' ),
				'post'              => __( 'Lenz\'s article page', 'lenz-plus' ),
				'course'            => __( 'Lenz\'s product page', 'lenz-plus' ),
			),
			'routes'        => Schema::ROUTE_TYPES,
			'templates'     => Library::all( $this ),
			'pages'         => Design_Pages::all(),
			'pageKinds'     => self::page_kinds(),
			'presets'       => Library::catalog_for_js(),
			'i18n'          => $this->admin_strings(),
		);
	}

	/**
	 * Preview link of the theme's own layout, per header/footer area and route.
	 *
	 * @return array<string, string>
	 */
	private function theme_previews(): array {
		$previews = array();
		foreach ( array_merge( Schema::AREAS, Schema::ROUTE_TYPES ) as $type ) {
			$previews[ $type ] = $this->resolver()->preview_url( $type, 'theme' );
		}

		return $previews;
	}

	/**
	 * Page design kinds for the Pages tab, in order: the title of their group
	 * and the name suggested for a new page.
	 *
	 * @return array<int, array{kind:string, title:string, name:string}>
	 */
	private static function page_kinds(): array {
		$titles = array(
			'home'     => __( 'Home pages', 'lenz-plus' ),
			'about'    => __( 'About pages', 'lenz-plus' ),
			'services' => __( 'Services pages', 'lenz-plus' ),
			'courses'  => __( 'Courses pages', 'lenz-plus' ),
		);

		$kinds = array();
		foreach ( Schema::PAGE_TYPES as $kind ) {
			$kinds[] = array(
				'kind'  => $kind,
				'title' => $titles[ $kind ],
				'name'  => Design_Pages::default_title( $kind ),
			);
		}

		return $kinds;
	}

	/** Admin URL of the template library tab. */
	public function library_url(): string {
		return add_query_arg(
			array(
				'page'    => $this->admin_slug(),
				'lzp_tab' => 'library',
			),
			admin_url( 'admin.php' )
		);
	}

	/** Whether the current user may manage templates. */
	public static function user_can_manage(): bool {
		return current_user_can( Admin::CAPABILITY );
	}

	/** Strings used by builder-admin.js. */
	private function admin_strings(): array {
		return array(
			'themeHeader'     => __( 'Lenz\'s header', 'lenz-plus' ),
			'themeFooter'     => __( 'Lenz\'s footer', 'lenz-plus' ),
			'themeDesc'       => __( 'Keep the theme\'s own design.', 'lenz-plus' ),
			'none'            => __( 'Nothing', 'lenz-plus' ),
			'noneDesc'        => __( 'Hide it on this device.', 'lenz-plus' ),
			'same'            => __( 'Same as desktop', 'lenz-plus' ),
			'sameDesc'        => __( 'Use the desktop choice on phones too.', 'lenz-plus' ),
			'inUse'           => __( 'In use', 'lenz-plus' ),
			'preview'         => __( 'Preview', 'lenz-plus' ),
			'previewOf'       => __( 'Preview', 'lenz-plus' ),
			'noSample'        => __( 'Nothing to preview yet.', 'lenz-plus' ),
			'unsavedPreview'  => __( 'Previews show a design right away — nothing changes for visitors until you save.', 'lenz-plus' ),
			'custom'          => __( 'Custom template', 'lenz-plus' ),
			'edit'            => __( 'Edit with Elementor', 'lenz-plus' ),
			'duplicate'       => __( 'Duplicate', 'lenz-plus' ),
			'restore'         => __( 'Restore original', 'lenz-plus' ),
			'delete'          => __( 'Delete', 'lenz-plus' ),
			'rename'          => __( 'Rename', 'lenz-plus' ),
			'deleteTitle'     => __( 'Delete this template?', 'lenz-plus' ),
			/* translators: %s: template name. */
			'deleteMessage'   => __( '"%s" goes to the trash. Pages made from it keep their own copy.', 'lenz-plus' ),
			'restoreTitle'    => __( 'Restore the original design?', 'lenz-plus' ),
			'restoreMessage'  => __( 'Your Elementor changes to this template will be replaced by the original design. Duplicate it first if you want to keep your version.', 'lenz-plus' ),
			'restoreConfirm'  => __( 'Restore', 'lenz-plus' ),
			'created'         => __( 'Template created.', 'lenz-plus' ),
			'duplicated'      => __( 'Template duplicated.', 'lenz-plus' ),
			'restored'        => __( 'Original design restored.', 'lenz-plus' ),
			'deleted'         => __( 'Template deleted.', 'lenz-plus' ),
			'installed'       => __( 'Ready-made designs installed.', 'lenz-plus' ),
			'renamed'         => __( 'Template renamed.', 'lenz-plus' ),
			'desktop'         => __( 'Desktop', 'lenz-plus' ),
			'tablet'          => __( 'Tablet', 'lenz-plus' ),
			'mobile'          => __( 'Mobile', 'lenz-plus' ),
			'openTab'         => __( 'Open in new tab', 'lenz-plus' ),
			'close'           => __( 'Close', 'lenz-plus' ),
			'newTitle'        => __( 'New template', 'lenz-plus' ),
			'newName'         => __( 'Name', 'lenz-plus' ),
			'newType'         => __( 'Kind', 'lenz-plus' ),
			'newStart'        => __( 'Start from', 'lenz-plus' ),
			'blank'           => __( 'Blank canvas', 'lenz-plus' ),
			'create'          => __( 'Create & open Elementor', 'lenz-plus' ),
			'createOnly'      => __( 'Create', 'lenz-plus' ),
			'renamePrompt'    => __( 'New name', 'lenz-plus' ),
			'save'            => __( 'Save', 'lenz-plus' ),
			'cancel'          => __( 'Cancel', 'lenz-plus' ),
			'preset'          => __( 'Ready-made', 'lenz-plus' ),
			'modified'        => __( 'Edited', 'lenz-plus' ),
			'empty'           => __( 'No templates of this kind yet.', 'lenz-plus' ),
			'needsElementor'  => __( 'Elementor is required to edit templates.', 'lenz-plus' ),
			'synced'          => __( 'Brand colours added to Elementor\'s global colours.', 'lenz-plus' ),
			'containerOn'     => __( 'Flexbox containers activated.', 'lenz-plus' ),
			'createPage'      => __( 'Create page', 'lenz-plus' ),
			/* translators: %s: design name. */
			'createPageTitle' => __( 'New page from "%s"', 'lenz-plus' ),
			'pageName'        => __( 'Page name', 'lenz-plus' ),
			'publishNow'      => __( 'Publish now', 'lenz-plus' ),
			'publishHelp'     => __( 'Off: the page is saved as a draft that only you can see.', 'lenz-plus' ),
			'makeFront'       => __( 'Use it as the site\'s home page', 'lenz-plus' ),
			'makeFrontHelp'   => __( 'Visitors see it at the site address. The page is published.', 'lenz-plus' ),
			'pageCreated'     => __( 'Page created.', 'lenz-plus' ),
			'noDesigns'       => __( 'No ready-made designs of this kind yet. Create a template of this kind in the Templates tab to make pages from it.', 'lenz-plus' ),
			'noPages'         => __( 'No pages yet. Create one from a design above.', 'lenz-plus' ),
			'frontPage'       => __( 'Home page', 'lenz-plus' ),
			'draft'           => __( 'Draft', 'lenz-plus' ),
			'published'       => __( 'Published', 'lenz-plus' ),
			/* translators: %s: design name. */
			'madeFrom'        => __( 'From "%s"', 'lenz-plus' ),
			'view'            => __( 'View', 'lenz-plus' ),
			'setFront'        => __( 'Make it the home page', 'lenz-plus' ),
			'frontSet'        => __( 'This page is now the site\'s home page.', 'lenz-plus' ),
			'editDesign'      => __( 'Edit the design', 'lenz-plus' ),
		);
	}
}
