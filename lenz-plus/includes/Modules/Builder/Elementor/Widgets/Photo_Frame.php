<?php
/**
 * One photo in the mockups' frame: cropped to a ratio, with the optional
 * side notch and the rule-of-thirds guide lines drawn around it (the home
 * page's «درباره من» photo). Without a photo it shows the striped
 * placeholder with a label, like every Lenz+ photo frame.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Elementor\Picture;

defined( 'ABSPATH' ) || exit;

/**
 * The «Photo frame (Lenz+)» widget.
 */
final class Photo_Frame extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-photo-frame';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'Photo frame', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-image';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'photo', 'image', 'frame', 'عکس', 'تصویر' ) );
	}

	/** Content (photo, shape, decoration) controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Photo', 'lenz-plus' ) );
		$this->add_photo_control( 'image', __( 'Photo', 'lenz-plus' ) );
		$this->add_ratio_control( 'ratio', '4/3' );

		$this->add_control(
			'label',
			array(
				'label'       => __( 'Placeholder text', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'Shown in the empty frame until a photo is chosen.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'thirds',
			array(
				'label'        => __( 'Rule-of-thirds lines', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => __( 'Also follows the global "Guide lines" option of Page templates.', 'lenz-plus' ),
			)
		);

		$this->add_control(
			'notch',
			array(
				'label'        => __( 'Corner notch', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();
	}

	/** Prints the frame. */
	protected function render(): void {
		$s     = $this->get_settings_for_display();
		$frame = Picture::frame(
			(array) $s['image'],
			array(
				'ratio' => (string) $s['ratio'],
				'label' => (string) $s['label'],
				'class' => 'lzp-photo',
			)
		);

		$classes = 'lzp-frame' . ( 'yes' === $s['thirds'] ? ' lzp-frame--thirds' : '' );
		$inner   = 'lzp-frame__photo' . ( 'yes' === $s['notch'] ? ' lzp-notch lzp-notch--side' : '' );

		printf(
			'<div class="%1$s">%2$s<div class="%3$s">%4$s</div></div>',
			esc_attr( $classes ),
			'yes' === $s['thirds'] ? '<span class="lzp-frame__guides" aria-hidden="true"></span>' : '',
			esc_attr( $inner ),
			$frame // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in Picture::frame().
		);
	}
}
