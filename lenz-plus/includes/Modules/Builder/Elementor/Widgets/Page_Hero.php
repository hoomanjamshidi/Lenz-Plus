<?php
/**
 * Top section of the pages: a small label, the page title, a lead
 * paragraph, optional dashed fact chips and two buttons, with a photo at the
 * end (split layout: about, services), text only (portfolio, blog,
 * courses), or the home page's film strip layout: an ink film strip with a
 * vertical word, the text with a "since 1399" proof row, and a 4:5 photo
 * whose play button opens the showreel in a dialog (video.js). Behind it
 * sit the mockups' dashed guide lines and a huge outline word (AMIRMAHDI
 * PHOTO, BEHIND THE CAMERA, LEARN THE LIGHT…).
 *
 * The widget spans the whole width and boxes its own content, so the guide
 * lines and the outline word reach the edges of the screen as in the
 * mockups: place it in a full-width container without padding.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use LenzPlus\Modules\Builder\Assets;
use LenzPlus\Modules\Builder\Elementor\Picture;
use LenzPlus\Modules\Builder\Elementor\Post_Parts;

defined( 'ABSPATH' ) || exit;

/**
 * The «Page hero (Lenz+)» widget.
 */
final class Page_Hero extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-page-hero';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Page hero', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-header';
	}

	/** Archive titles depend on the page being viewed. */
	protected function is_dynamic_content(): bool {
		return true;
	}

	/** The play button's video dialog. */
	public function get_script_depends(): array {
		return array( Assets::VIDEO_HANDLE );
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'hero', 'banner', 'title', 'هیرو', 'بنر' ) );
	}

	/** Content (texts, chips, buttons, photo, decoration) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Texts', 'lenz-plus' ) );

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'split',
				'options' => array(
					'split'     => __( 'Text and photo', 'lenz-plus' ),
					'text'      => __( 'Text only', 'lenz-plus' ),
					'filmstrip' => __( 'Film strip, text and photo (home page)', 'lenz-plus' ),
				),
			)
		);

		$this->add_control(
			'title_source',
			array(
				'label'       => __( 'On category, tag, author and search pages', 'lenz-plus' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'custom',
				'options'     => array(
					'custom'  => __( 'Keep these texts', 'lenz-plus' ),
					'archive' => __( 'Show what the page lists', 'lenz-plus' ),
				),
				'description' => __( 'For list templates: a category page then says which category it shows.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'eyebrow',
			array(
				'label'   => __( 'Label above the title', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'About me', 'lenz-plus' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'eyebrow_strong',
			array(
				'label'       => __( 'Bold words before the label', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'For example your name, in front of the label in bold.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => __( 'A story told with light', 'lenz-plus' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'tag',
			array(
				'label'   => __( 'HTML tag', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h1',
				'options' => array(
					'h1' => 'H1',
					'h2' => 'H2',
				),
			)
		);

		$this->add_control(
			'lead',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 4,
				'default' => __( 'A short paragraph that introduces this page.', 'lenz-plus' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'text',
			array(
				'label'   => __( 'Text', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'A short fact', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'chips',
			array(
				'label'       => __( 'Facts (chips)', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(),
				'title_field' => '{{{ text }}}',
			)
		);

		$this->add_buttons_controls(
			array(
				'primary_text' => __( 'Book a session', 'lenz-plus' ),
				'primary_url'  => '#',
			)
		);

		$this->end_controls_section();

		$this->start_content_section(
			'section_strip',
			__( 'Film strip and proof', 'lenz-plus' ),
			array( 'condition' => array( 'layout' => 'filmstrip' ) )
		);

		$this->add_control(
			'strip_text',
			array(
				'label'   => __( 'Word on the film strip', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'PHOTOGRAPHY STUDIO',
			)
		);

		$this->add_control(
			'proof_faces',
			array(
				'label'        => __( 'Faces', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'proof_images',
			array(
				'label'       => __( 'Photos of clients', 'lenz-plus' ),
				'type'        => Controls_Manager::GALLERY,
				'default'     => array(),
				'description' => __( 'The first three are shown as small circles.', 'lenz-plus' ),
				'condition'   => array( 'proof_faces' => 'yes' ),
			)
		);

		$this->add_control(
			'proof_top',
			array(
				'label'   => __( 'Small line', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Since 2020', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'proof_bottom',
			array(
				'label'   => __( 'Bold line', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'By your side', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();

		$this->start_content_section(
			'section_photo',
			__( 'Photo', 'lenz-plus' ),
			array( 'condition' => array( 'layout!' => 'text' ) )
		);
		$this->add_photo_control( 'image', __( 'Photo', 'lenz-plus' ) );
		$this->add_ratio_control( 'ratio', '4/5' );

		$this->add_control(
			'image_label',
			array(
				'label'       => __( 'Placeholder text', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'Shown in the empty frame until a photo is chosen.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'video',
			array(
				'label'       => __( 'Video link', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'placeholder' => 'https://',
				'description' => __( 'Adds a play button on the photo. An embed link (YouTube, Aparat…) or a video file; it plays over the page.', 'lenz-plus' ),
				'condition'   => array( 'layout' => 'filmstrip' ),
			)
		);

		$this->add_control(
			'notch',
			array(
				'label'        => __( 'Corner notch', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_content_section( 'section_decor', __( 'Decoration', 'lenz-plus' ) );

		$this->add_control(
			'watermark',
			array(
				'label'       => __( 'Outline word', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'BEHIND THE CAMERA',
				'label_block' => true,
				'description' => __( 'The large outlined word behind the section. Leave empty to hide it.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'guides',
			array(
				'label'        => __( 'Guide lines', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => __( 'Also follows the global "Guide lines" option of Page templates.', 'lenz-plus' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Texts', 'lenz-plus' ) );
		$this->add_text_style( 'eyebrow', '.lzp-hero__eyebrow', array( 'label' => __( 'Label', 'lenz-plus' ) ) );
		$this->add_text_style( 'title', '.lzp-hero__title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_text_style( 'lead', '.lzp-hero__lead', array( 'label' => __( 'Text', 'lenz-plus' ) ) );

		$this->add_responsive_control(
			'pad_y',
			array(
				'label'      => __( 'Space above and below', 'lenz-plus' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'separator'  => 'before',
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 160,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .lzp-hero' => 'padding-block: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	/** Prints the hero. */
	protected function render(): void {
		$s = $this->get_settings_for_display();

		if ( 'archive' === $s['title_source'] ) {
			$archive = Post_Parts::archive();
			if ( '' !== $archive['title'] ) {
				$s['eyebrow']        = $archive['eyebrow'];
				$s['eyebrow_strong'] = '';
				$s['title']          = $archive['title'];
				$s['lead']           = $archive['text'];
			}
		}

		$layout = in_array( $s['layout'], array( 'text', 'filmstrip' ), true ) ? $s['layout'] : 'split';
		$tag    = 'h2' === $s['tag'] ? 'h2' : 'h1';

		$text = '';
		if ( '' !== (string) $s['eyebrow'] || '' !== (string) $s['eyebrow_strong'] ) {
			$strong = '' !== (string) $s['eyebrow_strong'] ? '<b>' . esc_html( $s['eyebrow_strong'] ) . '</b> ' : '';
			$text  .= '<span class="lzp-hero__eyebrow">' . $strong . esc_html( $s['eyebrow'] ) . '</span>';
		}
		$text .= sprintf( '<%1$s class="lzp-hero__title">%2$s</%1$s>', $tag, esc_html( (string) $s['title'] ) );
		if ( '' !== (string) $s['lead'] ) {
			$text .= '<p class="lzp-hero__lead">' . esc_html( $s['lead'] ) . '</p>';
		}

		if ( ! empty( $s['chips'] ) ) {
			$text .= '<ul class="lzp-hero__chips">';
			foreach ( $s['chips'] as $chip ) {
				$text .= '<li class="lzp-chip lzp-chip--dashed">' . esc_html( self::digits( (string) $chip['text'] ) ) . '</li>';
			}
			$text .= '</ul>';
		}

		$text .= $this->buttons_html( $s, array( 'primary', 'secondary' ) );

		$strip = '';
		if ( 'filmstrip' === $layout ) {
			$text .= self::proof_html( $s );
			$strip = '<div class="lzp-hero__strip" aria-hidden="true"><span class="lzp-hero__holes"></span><span class="lzp-hero__strip-text">' . esc_html( (string) $s['strip_text'] ) . '</span><span class="lzp-hero__holes"></span></div>';
		}

		$media = 'text' !== $layout ? self::media_html( $s, 'filmstrip' === $layout ) : '';

		$decor = '';
		if ( 'yes' === $s['guides'] ) {
			$decor .= '<span class="lzp-hero__guides" aria-hidden="true"></span>';
		}
		if ( '' !== (string) $s['watermark'] ) {
			$decor .= '<span class="lzp-watermark lzp-hero__watermark" aria-hidden="true">' . esc_html( $s['watermark'] ) . '</span>';
		}

		printf(
			'<div class="lzp-hero lzp-hero--%1$s">%2$s<div class="lzp-hero__inner">%3$s<div class="lzp-hero__text">%4$s</div>%5$s</div></div>',
			esc_attr( $layout ),
			$decor, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			$strip, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			$text, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			$media // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in media_html().
		);
	}

	/**
	 * The photo with its notch and, on the film strip layout, the play button.
	 *
	 * @param array $s         Widget settings.
	 * @param bool  $with_play Whether the layout has a play button.
	 */
	private static function media_html( array $s, bool $with_play ): string {
		$html = Picture::frame(
			(array) $s['image'],
			array(
				'ratio'   => (string) $s['ratio'],
				'label'   => (string) $s['image_label'],
				'alt'     => (string) $s['title'],
				'loading' => 'high',
				'class'   => 'lzp-photo',
			)
		);

		$video = $with_play ? trim( (string) $s['video'] ) : '';
		if ( '' !== $video ) {
			$html .= sprintf(
				'<a class="lzp-play" href="%1$s" data-lzp-video="%1$s" data-title="%2$s" target="_blank" rel="noopener"><span class="screen-reader-text">%3$s</span>%4$s</a>',
				esc_url( $video ),
				esc_attr( (string) $s['title'] ),
				esc_html__( 'Play the video', 'lenz-plus' ),
				self::icon( 'play' )
			);
		}

		return '<div class="lzp-hero__media' . ( 'yes' === $s['notch'] ? ' lzp-notch lzp-notch--side' : '' ) . '">' . $html . '</div>';
	}

	/**
	 * The proof row under the buttons: overlapping client photos (striped
	 * circles until photos are chosen) and two short lines.
	 *
	 * @param array $s Widget settings.
	 */
	private static function proof_html( array $s ): string {
		if ( '' === (string) $s['proof_top'] && '' === (string) $s['proof_bottom'] ) {
			return '';
		}

		$faces = '';
		if ( 'yes' === $s['proof_faces'] ) {
			$images = array_slice( (array) $s['proof_images'], 0, 3 );
			foreach ( $images ? $images : array( array(), array(), array() ) as $image ) {
				$id     = (int) ( $image['id'] ?? 0 );
				$faces .= $id ? wp_get_attachment_image( $id, 'thumbnail', false, array( 'class' => 'lzp-hero__face' ) ) : '<span class="lzp-hero__face lzp-hero__face--empty"></span>';
			}
			$faces = '<span class="lzp-hero__faces" aria-hidden="true">' . $faces . '</span>';
		}

		return '<div class="lzp-hero__proof">' . $faces . '<span class="lzp-hero__proof-text">'
			. ( '' !== (string) $s['proof_top'] ? '<span class="lzp-hero__proof-top">' . esc_html( self::digits( (string) $s['proof_top'] ) ) . '</span>' : '' )
			. ( '' !== (string) $s['proof_bottom'] ? '<span class="lzp-hero__proof-bottom">' . esc_html( self::digits( (string) $s['proof_bottom'] ) ) . '</span>' : '' )
			. '</span></div>';
	}
}
