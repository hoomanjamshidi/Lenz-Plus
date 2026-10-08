<?php
/**
 * Photos of a project page: the cover (featured image) as the large 16:9
 * frame, or a slice of the project's gallery (Lenz's gallery box, external
 * links included) in a row of equal frames. The mockups' project page uses
 * three of them: the cover, four 4:5 photos, then two 3:2 photos; "Skip"
 * and "How many" pick each slice. Videos play in place.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Elementor\Picture;
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

		$items = $this->items( $s, $post_id );
		$ratio = (string) $s['ratio'];

		if ( ! $items ) {
			if ( ! $this->in_editor() ) {
				return;
			}
			// Editing with a project that has no photos here yet: show the frames to fill.
			$items = array_fill( 0, 'cover' === $s['source'] ? 1 : max( 1, (int) $s['count'] ), null );
		}

		// The cover is one wide frame whatever the column setting says (inline beats Elementor's per-widget rule).
		echo '<ul class="lzp-pgal lzp-grid' . ( 'cover' === $s['source'] ? ' lzp-pgal--cover" style="--lzp-cols:1' : '' ) . '">';
		foreach ( $items as $item ) {
			echo '<li class="lzp-pgal__item">' . $this->item_html( $item, $ratio, get_the_title( $post_id ) ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in item_html().
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

		return array_slice( Portfolio_Data::gallery( $post_id ), max( 0, (int) $s['offset'] ), $count > 0 ? $count : null );
	}

	/**
	 * One frame: an image, a video that plays in place, or an empty frame.
	 *
	 * @param array|null $item  Gallery item, null for an empty frame.
	 * @param string     $ratio Frame ratio.
	 * @param string     $alt   Alternative text.
	 */
	private function item_html( ?array $item, string $ratio, string $alt ): string {
		if ( null !== $item && 'video' === $item['type'] ) {
			$style = in_array( $ratio, Picture::RATIOS, true ) ? ' style="--lzp-ratio:' . esc_attr( str_replace( '/', ' / ', $ratio ) ) . '"' : '';

			return '<div class="lzp-pic lzp-pgal__video"' . $style . '><video class="lzp-pic__img" src="' . esc_url( $item['url'] ) . '" controls preload="metadata" playsinline></video></div>';
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
