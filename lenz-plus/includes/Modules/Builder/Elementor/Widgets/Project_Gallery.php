<?php
/**
 * Photos of a project page: the cover (featured image) as the large 16:9
 * frame, or a slice of the project's gallery (Lenz's gallery box, external
 * links included) in a row of equal frames. The mockups' project page uses
 * three of them: the cover, four 4:5 photos, then two 3:2 photos; "Skip"
 * and "How many" pick each slice, "Media" limits it to photos or videos
 * (the video and mixed project designs show them in separate sections).
 * Videos play in place: uploaded files and download-host links in
 * `<video>` (the project's first one, its film, gets the cover as its
 * poster, as on Lenz's own project page), Aparat links in Aparat's player
 * (Media_Link). Photos open in a full-screen preview (lightbox.js); they are
 * links to the full-size file, so without JavaScript the photo simply opens.
 *
 * An optional title is printed only with the items, and nothing at all is
 * printed when the slice is empty, so a project without videos shows no
 * empty "Videos" section (the presets' bands carry `lzp-hide-empty`).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Assets;
use LenzPlus\Modules\Builder\Elementor\Picture;
use LenzPlus\Modules\Builder\Media_Link;
use LenzPlus\Modules\Builder\Portfolio_Data;

defined( 'ABSPATH' ) || exit;

/**
 * The «Project photos (Lenz+)» widget.
 */
final class Project_Gallery extends Portfolio_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-project-gallery';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Project photos', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-gallery-grid';
	}

	/** The photo preview. */
	public function get_script_depends(): array {
		return array( Assets::LIGHTBOX_HANDLE );
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Photos', 'lenz-plus' ) );

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Show', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'gallery',
				'options' => array(
					'cover'   => __( 'Cover (featured image)', 'lenz-plus' ),
					'gallery' => __( 'Gallery photos', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'media',
			array(
				'label'     => __( 'Media', 'lenz-plus' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''      => __( 'Photos and videos', 'lenz-plus' ),
					'image' => __( 'Photos only', 'lenz-plus' ),
					'video' => __( 'Videos only', 'lenz-plus' ),
				),
				'condition' => array( 'source' => 'gallery' ),
			)
		);

		$this->add_control(
			'offset',
			array(
				'label'       => __( 'Skip', 'lenz-plus' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'condition'   => array( 'source' => 'gallery' ),
				'description' => __( 'Gallery items shown by the widgets above this one.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'count',
			array(
				'label'       => __( 'How many', 'lenz-plus' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'condition'   => array( 'source' => 'gallery' ),
				'description' => __( '0 shows all the rest.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'description' => __( 'Shown above the items, and hidden with them when the project has none.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'lightbox',
			array(
				'label'        => __( 'Open photos in a preview', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => __( 'A tap shows the photo full screen; the arrows go through every photo of the project.', 'lenz-plus' ),
			)
		);

		$this->add_ratio_control( 'ratio', '4/5' );
		$this->add_columns_control( '.lzp-pgal', array( 4, 2, 1 ) );
		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Photos', 'lenz-plus' ) );
		$this->add_gap_control( '.lzp-pgal' );
		$this->end_controls_section();
	}

	/** Prints the photos. */
	protected function render(): void {
		$s       = $this->get_settings_for_display();
		$post_id = $this->current_project();
		if ( ! $post_id ) {
			return;
		}

		$items  = $this->items( $s, $post_id );
		$ratio  = (string) $s['ratio'];
		$videos = Portfolio_Data::media( $post_id, 'video' );
		$film   = $videos ? $videos[0]['url'] : '';
		$poster = $film ? (string) get_the_post_thumbnail_url( $post_id, 'large' ) : '';
		// Every gallery widget of the project shares one set, so the preview goes through all its photos.
		$group = 'yes' === $s['lightbox'] ? 'project-' . $post_id : '';

		if ( ! $items ) {
			if ( ! $this->in_editor() ) {
				return;
			}
			// Editing with a project that has no photos here yet: show the frames to fill.
			$items = array_fill( 0, 'cover' === $s['source'] ? 1 : max( 1, (int) $s['count'] ), null );
		}

		if ( '' !== (string) $s['title'] ) {
			echo Heading::markup( array( 'title' => (string) $s['title'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Heading::markup().
		}

		// The cover is one wide frame whatever the column setting says (inline beats Elementor's per-widget rule).
		echo '<ul class="lzp-pgal lzp-grid' . ( 'cover' === $s['source'] ? ' lzp-pgal--cover" style="--lzp-cols:1' : '' ) . '">';
		foreach ( $items as $item ) {
			$html = $this->item_html( $item, $ratio, get_the_title( $post_id ), null !== $item && $item['url'] === $film ? $poster : '' );
			echo '<li class="lzp-pgal__item">' . ( '' !== $group && null !== $item && 'image' === $item['type'] ? self::zoom_link( $item, $group, $html ) : $html ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in item_html() and zoom_link().
		}
		echo '</ul>';
	}

	/**
	 * The gallery slice (or the cover) to show.
	 *
	 * @param array $s       Settings.
	 * @param int   $post_id Item ID.
	 * @return array<int, array{id:int, url:string, type:string}>
	 */
	private function items( array $s, int $post_id ): array {
		if ( 'cover' === $s['source'] ) {
			$cover = (int) get_post_thumbnail_id( $post_id );

			return $cover ? array(
				array(
					'id'   => $cover,
					'url'  => '',
					'type' => 'image',
				),
			) : array();
		}

		$count = (int) $s['count'];

		return array_slice( Portfolio_Data::media( $post_id, (string) $s['media'] ), max( 0, (int) $s['offset'] ), $count > 0 ? $count : null );
	}

	/**
	 * A photo wrapped in a link to its full-size file, which lightbox.js
	 * opens in the preview.
	 *
	 * @param array  $item  Gallery item.
	 * @param string $group Preview set.
	 * @param string $frame Frame markup.
	 */
	private static function zoom_link( array $item, string $group, string $frame ): string {
		$full = $item['id'] ? (string) wp_get_attachment_image_url( $item['id'], 'full' ) : $item['url'];
		if ( '' === $full ) {
			return $frame;
		}

		$caption = $item['id'] ? (string) wp_get_attachment_caption( $item['id'] ) : '';

		return '<a class="lzp-pgal__zoom" href="' . esc_url( $full ) . '" data-lzp-lightbox="' . esc_attr( $group ) . '"'
			. ( '' !== $caption ? ' data-caption="' . esc_attr( $caption ) . '"' : '' )
			. '><span class="screen-reader-text">' . esc_html__( 'View the photo', 'lenz-plus' ) . '</span>' . $frame . '</a>';
	}

	/**
	 * One frame: an image, a video that plays in place, or an empty frame.
	 *
	 * @param array|null $item   Gallery item, null for an empty frame.
	 * @param string     $ratio  Frame ratio.
	 * @param string     $alt    Alternative text.
	 * @param string     $poster Poster of a video ('' for its first frame).
	 */
	private function item_html( ?array $item, string $ratio, string $alt, string $poster = '' ): string {
		if ( null !== $item && 'video' === $item['type'] ) {
			$style = in_array( $ratio, Picture::RATIOS, true ) ? ' style="--lzp-ratio:' . esc_attr( str_replace( '/', ' / ', $ratio ) ) . '"' : '';
			$embed = $item['id'] ? '' : Media_Link::embed_url( $item['url'] );
			if ( '' !== $embed ) {
				return '<div class="lzp-pic lzp-pgal__video lzp-pgal__embed"' . $style . '><iframe class="lzp-pic__img" src="' . esc_url( $embed ) . '" title="' . esc_attr( $alt ) . '"'
					. ' loading="lazy" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div>';
			}

			$src = $item['url'];
			if ( '' === $poster && false === strpos( $src, '#' ) ) {
				// iOS Safari paints no frame for preload="metadata" until a start time is given.
				$src .= '#t=0.1';
			}

			return '<div class="lzp-pic lzp-pgal__video"' . $style . '><video class="lzp-pic__img" src="' . esc_url( $src ) . '"'
				. ( '' !== $poster ? ' poster="' . esc_url( $poster ) . '"' : '' )
				. ' controls preload="metadata" playsinline aria-label="' . esc_attr( $alt ) . '"></video></div>';
		}

		return Picture::frame(
			null !== $item ? array(
				'id'  => $item['id'],
				'url' => $item['url'],
			) : array(),
			array(
				'ratio' => $ratio,
				'alt'   => $alt,
				'class' => 'lzp-photo',
				'sizes' => '(max-width: 767px) 100vw, 50vw',
			)
		);
	}
}
