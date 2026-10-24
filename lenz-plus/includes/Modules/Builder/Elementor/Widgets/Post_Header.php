<?php
/**
 * Top of an article: the category chip with the date and reading time, the
 * title, the excerpt, then a dashed rule over the author (picture, name and
 * role) and the share buttons with "copy link". Reads the post being viewed
 * (a sample post while the template is edited); each part can be hidden.
 *
 * Layouts: `text` (the mockups' header; the featured image is a separate
 * widget), `split` (the text beside the featured image) and `cover` (the
 * text on the featured image, full width). In the last two the image is the
 * page's largest paint, so it loads at once with a high priority.
 *
 * Share links work without JavaScript; blog.js adds "copy link" and the
 * phone's share sheet, which stay hidden until then.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Elementor\Picture;
use LenzPlus\Modules\Builder\Elementor\Post_Parts;

defined( 'ABSPATH' ) || exit;

/**
 * The «Article header (Lenz+)» widget.
 */
final class Post_Header extends Blog_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-post-header';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Article header', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-post-title';
	}

	/** Content and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Article header', 'lenz-plus' ) );

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'text',
				'options' => array(
					'text'  => __( 'Text only', 'lenz-plus' ),
					'split' => __( 'Text beside the featured image', 'lenz-plus' ),
					'cover' => __( 'Text on the featured image', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'align',
			array(
				'label'     => __( 'Alignment', 'lenz-plus' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'start',
				'options'   => array(
					'start'  => __( 'Start', 'lenz-plus' ),
					'center' => __( 'Centre', 'lenz-plus' ),
				),
				'condition' => array( 'layout!' => 'split' ),
			)
		);

		$this->add_ratio_control( 'ratio', '4/3', array( 'condition' => array( 'layout' => 'split' ) ) );

		$this->add_responsive_control(
			'cover_height',
			array(
				'label'      => __( 'Height', 'lenz-plus' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array(
						'min' => 300,
						'max' => 900,
					),
					'vh' => array(
						'min' => 30,
						'max' => 100,
					),
				),
				'condition'  => array( 'layout' => 'cover' ),
				'selectors'  => array( '{{WRAPPER}} .lzp-hero--cover' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);

		foreach ( array(
			'show_meta'    => __( 'Category, date and reading time', 'lenz-plus' ),
			'show_excerpt' => __( 'Excerpt', 'lenz-plus' ),
			'show_author'  => __( 'Author', 'lenz-plus' ),
		) as $key => $label ) {
			$this->add_control(
				$key,
				array(
					'label'        => $label,
					'type'         => Controls_Manager::SWITCHER,
					'default'      => 'yes',
					'return_value' => 'yes',
				)
			);
		}

		$this->add_control(
			'author_role',
			array(
				'label'       => __( 'Author role', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'Shown under the name. Empty: the first line of the author\'s biographical info.', 'lenz-plus' ),
				'condition'   => array( 'show_author' => 'yes' ),
			)
		);

		$this->add_control(
			'networks',
			array(
				'label'       => __( 'Share on', 'lenz-plus' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'default'     => array( 'telegram', 'whatsapp' ),
				'options'     => Post_Parts::share_networks(),
				'label_block' => true,
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'copy',
			array(
				'label'        => __( '"Copy link" button', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'guides',
			array(
				'label'        => __( 'Guide lines', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Texts', 'lenz-plus' ) );
		$this->add_text_style( 'title', '.lzp-hero__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'lead', '.lzp-hero__lead', array( 'label' => __( 'Excerpt', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** Prints the header. */
	protected function render(): void {
		$s = $this->get_settings_for_display();

		$this->with_post(
			static function ( \WP_Post $post ) use ( $s ) {
				$layout = in_array( $s['layout'], array( 'split', 'cover' ), true ) ? (string) $s['layout'] : 'text';
				$text   = 'yes' === $s['show_meta'] ? Post_Grid::meta_html( $post, true, true ) : '';

				$text .= '<h1 class="lzp-hero__title">' . esc_html( get_the_title( $post ) ) . '</h1>';

				$excerpt = has_excerpt( $post ) ? wp_strip_all_tags( get_the_excerpt( $post ) ) : '';
				if ( 'yes' === $s['show_excerpt'] && '' !== $excerpt ) {
					$text .= '<p class="lzp-hero__lead">' . esc_html( $excerpt ) . '</p>';
				}

				$byline = ( 'yes' === $s['show_author'] ? self::author_html( $post, (string) $s['author_role'] ) : '' )
					. self::share_html( $post, (array) $s['networks'], 'yes' === $s['copy'] );

				if ( '' !== $byline ) {
					$text .= '<div class="lzp-byline">' . $byline . '</div>';
				}

				$photo = 'text' === $layout ? '' : Picture::frame(
					array( 'id' => (int) get_post_thumbnail_id( $post ) ),
					array(
						'ratio'   => 'split' === $layout ? (string) $s['ratio'] : '',
						'alt'     => get_the_title( $post ),
						'loading' => 'high',
						'sizes'   => 'split' === $layout ? '(max-width: 767px) 100vw, 50vw' : '100vw',
						'class'   => 'lzp-photo lzp-hero__media',
						'tag'     => 'figure',
					)
				);

				$classes = 'lzp-hero lzp-hero--article lzp-hero--article-' . $layout
					. ( 'split' === $layout ? '' : ' lzp-hero--text' )
					. ( 'cover' === $layout ? ' lzp-hero--cover lzp-dark' : '' )
					. ( 'split' !== $layout && 'center' === $s['align'] ? ' lzp-hero--center' : '' );

				printf(
					'<div class="%1$s">%2$s%3$s<div class="lzp-hero__inner"><div class="lzp-hero__text">%4$s</div>%5$s</div></div>',
					esc_attr( $classes ),
					'yes' === $s['guides'] && 'cover' !== $layout ? '<span class="lzp-hero__guides" aria-hidden="true"></span>' : '',
					'cover' === $layout ? $photo : '', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Picture::frame() escapes.
					$text, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building.
					'split' === $layout ? $photo : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Picture::frame() escapes.
				);
			}
		);
	}

	/**
	 * Author picture, name and role.
	 *
	 * @param \WP_Post $post Post.
	 * @param string   $role Role text ('' = first line of the bio).
	 */
	private static function author_html( \WP_Post $post, string $role ): string {
		$author = (int) $post->post_author;
		if ( '' === $role ) {
			$bio  = trim( (string) get_the_author_meta( 'description', $author ) );
			$role = '' !== $bio ? (string) strtok( $bio, "\n" ) : '';
		}

		return '<div class="lzp-byline__author">'
			. Post_Parts::avatar( $author, 44, 'lzp-byline__avatar' )
			. '<span class="lzp-byline__who"><a class="lzp-byline__name" href="' . esc_url( get_author_posts_url( $author ) ) . '">' . esc_html( get_the_author_meta( 'display_name', $author ) ) . '</a>'
			. ( '' !== $role ? '<span class="lzp-byline__role">' . esc_html( $role ) . '</span>' : '' )
			. '</span></div>';
	}

	/**
	 * Share links, the phone's share sheet and "copy link".
	 *
	 * @param \WP_Post $post     Post.
	 * @param string[] $networks Networks to show.
	 * @param bool     $copy     Whether "copy link" shows.
	 */
	private static function share_html( \WP_Post $post, array $networks, bool $copy ): string {
		$labels = Post_Parts::share_networks();
		$icons  = array(
			'telegram' => 'telegram',
			'whatsapp' => 'whatsapp',
			'x'        => 'x',
			'linkedin' => 'linkedin',
			'email'    => 'mail',
		);

		$html = '';
		foreach ( $networks as $network ) {
			if ( ! isset( $labels[ $network ] ) ) {
				continue;
			}
			$html .= sprintf(
				'<a class="lzp-share__btn" href="%1$s" target="_blank" rel="noopener nofollow" title="%2$s" aria-label="%2$s">%3$s</a>',
				esc_url( Post_Parts::share_url( $network, $post ) ),
				/* translators: %s: network name. */
				esc_attr( sprintf( __( 'Share on %s', 'lenz-plus' ), $labels[ $network ] ) ),
				self::icon( $icons[ $network ] )
			);
		}

		$url = (string) get_permalink( $post );

		// Hidden until blog.js shows them: both need JavaScript.
		$html .= sprintf(
			'<button type="button" class="lzp-share__btn" data-lzp-share data-url="%1$s" data-title="%2$s" aria-label="%3$s" hidden>%4$s</button>',
			esc_url( $url ),
			esc_attr( html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ) ),
			esc_attr__( 'Share', 'lenz-plus' ),
			self::icon( 'share' )
		);

		if ( $copy ) {
			$html .= sprintf(
				'<button type="button" class="lzp-share__btn lzp-share__btn--copy" data-lzp-copy="%1$s" data-lzp-needs-js hidden><span class="lzp-share__label">%2$s</span><span class="lzp-share__done">%3$s</span>%4$s</button>',
				esc_url( $url ),
				esc_html__( 'Copy link', 'lenz-plus' ),
				esc_html__( 'Copied', 'lenz-plus' ),
				self::icon( 'link' )
			);
		}

		return '' !== $html ? '<div class="lzp-share">' . $html . '</div>' : '';
	}
}
