<?php
/**
 * Trust badges for the footer: eNamad (اینماد), Samandehi (ساماندهی), the
 * Zarinpal payment badge, any image with a link, or a badge's own code.
 *
 * eNamad and Samandehi check the visiting page through the referrer, so
 * their links and images carry `referrerpolicy="origin"` exactly as in the
 * codes they hand out; only the ID and code are typed here and the markup is
 * built from them. Pasted codes are reduced to links and images
 * (`wp_kses`): badges that print themselves with a script
 * (`document.write`) block the page and are not supported — their link and
 * image can be entered as an "Image with a link" instead.
 *
 * Badge images load lazily (the footer is below the fold) inside tiles of a
 * fixed height, so a slow badge server never shifts the page. A badge with
 * missing details prints nothing for visitors and a hint in the editor.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * The «Trust badges (Lenz+)» widget.
 */
final class Trust_Badges extends Base {

	/** Badge kinds. */
	private const TYPES = array( 'enamad', 'samandehi', 'zarinpal', 'image', 'code' );

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-trust-badges';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Trust badges', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-lock-user';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'trust', 'enamad', 'zarinpal', 'samandehi', 'badge', 'footer', 'اینماد', 'نماد', 'زرین پال', 'ساماندهی', 'اعتماد' ) );
	}

	/** Badges and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Trust badges', 'lenz-plus' ) );

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Trust badges', 'lenz-plus' ),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'type',
			array(
				'label'   => __( 'Badge', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'enamad',
				'options' => self::type_labels(),
			)
		);
		$repeater->add_control(
			'badge_id',
			array(
				'label'       => __( 'ID', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'The id=… value in the code you received.', 'lenz-plus' ),
				'condition'   => array( 'type' => array( 'enamad', 'samandehi' ) ),
			)
		);
		$repeater->add_control(
			'badge_code',
			array(
				'label'       => __( 'Code', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'eNamad: the Code=… value. Samandehi: the p=… value.', 'lenz-plus' ),
				'condition'   => array( 'type' => array( 'enamad', 'samandehi' ) ),
			)
		);
		$repeater->add_control(
			'image',
			array(
				'label'       => __( 'Image', 'lenz-plus' ),
				'type'        => Controls_Manager::MEDIA,
				'default'     => array(
					'url' => '',
					'id'  => '',
				),
				'description' => __( 'Zarinpal: optional, without it a «secure payment» badge is drawn.', 'lenz-plus' ),
				'condition'   => array( 'type' => array( 'zarinpal', 'image' ) ),
			)
		);
		$repeater->add_control(
			'link',
			array(
				'label'       => __( 'Link', 'lenz-plus' ),
				'type'        => Controls_Manager::URL,
				'description' => __( 'Zarinpal: empty = your trust page at zarinpal.com/trustPage/your-domain.', 'lenz-plus' ),
				'condition'   => array( 'type' => array( 'zarinpal', 'image' ) ),
			)
		);
		$repeater->add_control(
			'label',
			array(
				'label'     => __( 'Name', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '',
				'condition' => array( 'type' => array( 'zarinpal', 'image' ) ),
			)
		);
		$repeater->add_control(
			'code',
			array(
				'label'       => __( 'Badge code', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 5,
				'default'     => '',
				'description' => __( 'Only links and images are kept; scripts are removed.', 'lenz-plus' ),
				'condition'   => array( 'type' => 'code' ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Badges', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'type' => 'enamad' ),
					array( 'type' => 'zarinpal' ),
				),
				'title_field' => '{{{ type }}}',
			)
		);

		$this->add_control(
			'look',
			array(
				'label'   => __( 'Look', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'tiles',
				'options' => array(
					'tiles' => __( 'White tiles', 'lenz-plus' ),
					'plain' => __( 'Plain', 'lenz-plus' ),
				),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Trust badges', 'lenz-plus' ) );
		$this->add_responsive_control(
			'badge_height',
			array(
				'label'      => __( 'Badge height', 'lenz-plus' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 40,
						'max' => 160,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .lzp-trust' => '--lzp-badge-h: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_text_style( 'title', '.lzp-col-title', array( 'label' => __( 'Title', 'lenz-plus' ) ) );
		$this->add_box_style( 'tile', '.lzp-trust__badge', array( 'label' => __( 'Tiles', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Badge kind → label.
	 *
	 * @return array<string, string>
	 */
	private static function type_labels(): array {
		return array(
			'enamad'    => __( 'eNamad', 'lenz-plus' ),
			'samandehi' => __( 'Samandehi', 'lenz-plus' ),
			'zarinpal'  => __( 'Zarinpal', 'lenz-plus' ),
			'image'     => __( 'Image with a link', 'lenz-plus' ),
			'code'      => __( 'Badge code', 'lenz-plus' ),
		);
	}

	/** Prints the badges. */
	protected function render(): void {
		$s      = $this->get_settings_for_display();
		$badges = '';

		foreach ( (array) $s['items'] as $item ) {
			$html = self::badge_html( $item );
			if ( '' !== $html ) {
				$badges .= '<li class="lzp-trust__badge">' . $html . '</li>';
			} elseif ( $this->in_editor() ) {
				$type    = in_array( $item['type'] ?? '', self::TYPES, true ) ? $item['type'] : 'image';
				$badges .= '<li class="lzp-trust__badge lzp-trust__badge--todo">' . esc_html(
					/* translators: %s: badge name (eNamad, Zarinpal…). */
					sprintf( __( '%s: fill in the details', 'lenz-plus' ), self::type_labels()[ $type ] )
				) . '</li>';
			}
		}

		if ( '' === $badges ) {
			return;
		}

		printf(
			'<div class="lzp-trust lzp-trust--%1$s">%2$s<ul class="lzp-trust__list">%3$s</ul></div>',
			'plain' === $s['look'] ? 'plain' : 'tiles',
			'' !== (string) $s['title'] ? '<h3 class="lzp-col-title">' . esc_html( $s['title'] ) . '</h3>' : '',
			$badges // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in badge_html().
		);
	}

	/**
	 * One badge's markup, '' when its details are missing.
	 *
	 * @param array $item Repeater row.
	 */
	private static function badge_html( array $item ): string {
		$id   = rawurlencode( trim( (string) ( $item['badge_id'] ?? '' ) ) );
		$code = rawurlencode( trim( (string) ( $item['badge_code'] ?? '' ) ) );

		switch ( $item['type'] ?? '' ) {
			case 'enamad':
				return '' !== $id && '' !== $code
					? self::seal_html( "https://trustseal.enamad.ir/?id={$id}&Code={$code}", "https://trustseal.enamad.ir/logo.aspx?id={$id}&Code={$code}", __( 'eNamad', 'lenz-plus' ), ' code="' . esc_attr( rawurldecode( $code ) ) . '"' )
					: '';

			case 'samandehi':
				return '' !== $id && '' !== $code
					? self::seal_html( "https://logo.samandehi.ir/Verify.aspx?id={$id}&p={$code}", "https://logo.samandehi.ir/logo.aspx?id={$id}&p={$code}", __( 'Samandehi', 'lenz-plus' ) )
					: '';

			case 'zarinpal':
				return self::zarinpal_html( $item );

			case 'image':
				return self::image_html( $item );

			case 'code':
				return self::code_html( (string) ( $item['code'] ?? '' ) );
		}

		return '';
	}

	/**
	 * eNamad / Samandehi markup, as in the codes they hand out.
	 *
	 * @param string $href  Verification page.
	 * @param string $src   Badge image.
	 * @param string $name  Badge name (alt text).
	 * @param string $extra Extra escaped image attributes.
	 */
	private static function seal_html( string $href, string $src, string $name, string $extra = '' ): string {
		return sprintf(
			'<a class="lzp-trust__link" href="%1$s" target="_blank" rel="noopener" referrerpolicy="origin"><img class="lzp-trust__img" src="%2$s" alt="%3$s" referrerpolicy="origin" loading="lazy" decoding="async"%4$s></a>',
			esc_url( $href ),
			esc_url( $src ),
			esc_attr( $name ),
			$extra
		);
	}

	/**
	 * Zarinpal: the merchant's trust page, with an uploaded image or a drawn badge.
	 *
	 * @param array $item Repeater row.
	 */
	private static function zarinpal_html( array $item ): string {
		$url = (string) ( $item['link']['url'] ?? '' );
		if ( '' === $url ) {
			$url = 'https://www.zarinpal.com/trustPage/' . wp_parse_url( home_url(), PHP_URL_HOST );
		}

		$name  = '' !== (string) ( $item['label'] ?? '' ) ? (string) $item['label'] : __( 'Zarinpal', 'lenz-plus' );
		$image = self::media_img( (array) ( $item['image'] ?? array() ), $name );
		$inner = '' !== $image ? $image : '<span class="lzp-trust__drawn">' . self::icon( 'lock' ) . '<span class="lzp-trust__drawn-text"><strong>' . esc_html__( 'Secure payment', 'lenz-plus' ) . '</strong><span>' . esc_html( $name ) . '</span></span></span>';

		return '<a class="lzp-trust__link" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . $inner . '</a>';
	}

	/**
	 * Any image, linked when a link is set.
	 *
	 * @param array $item Repeater row.
	 */
	private static function image_html( array $item ): string {
		$image = self::media_img( (array) ( $item['image'] ?? array() ), (string) ( $item['label'] ?? '' ) );
		$url   = (string) ( $item['link']['url'] ?? '' );

		if ( '' === $image ) {
			return '';
		}

		return '' !== $url ? '<a class="lzp-trust__link" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . $image . '</a>' : $image;
	}

	/**
	 * A pasted badge code reduced to links and images.
	 *
	 * @param string $code Pasted code.
	 */
	private static function code_html( string $code ): string {
		// wp_kses() drops the tags but would keep a script's text.
		$code = (string) preg_replace( '#<(script|style|noscript)\b[^>]*>.*?</\1>#isu', '', $code );
		$html = trim(
			wp_kses(
				$code,
				array(
					'a'   => array(
						'href'           => true,
						'target'         => true,
						'rel'            => true,
						'title'          => true,
						'referrerpolicy' => true,
					),
					'img' => array(
						'src'            => true,
						'alt'            => true,
						'width'          => true,
						'height'         => true,
						'id'             => true,
						'code'           => true,
						'referrerpolicy' => true,
					),
				)
			)
		);

		if ( '' === $html || false === strpos( $html, '<img' ) ) {
			return '';
		}

		// Below the fold: let the badge load late.
		return (string) preg_replace( '/<img(?![^>]*\bloading=)/u', '<img class="lzp-trust__img" loading="lazy" decoding="async"', $html );
	}

	/**
	 * `<img>` of a MEDIA value, '' without one.
	 *
	 * @param array  $media MEDIA value.
	 * @param string $alt   Alternative text.
	 */
	private static function media_img( array $media, string $alt ): string {
		$id = (int) ( $media['id'] ?? 0 );
		if ( $id ) {
			$img = wp_get_attachment_image(
				$id,
				'medium',
				false,
				array(
					'class'    => 'lzp-trust__img',
					'alt'      => $alt,
					'loading'  => 'lazy',
					'decoding' => 'async',
					'sizes'    => '160px',
				)
			);
			if ( '' !== $img ) {
				return $img;
			}
		}

		return ! empty( $media['url'] ) ? '<img class="lzp-trust__img" src="' . esc_url( (string) $media['url'] ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" decoding="async">' : '';
	}
}
