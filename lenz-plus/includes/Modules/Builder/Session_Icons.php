<?php
/**
 * An icon per curriculum session, chosen in each course's own details box.
 *
 * The icons are stored in the course details as `curriculum_icons`: one icon
 * key (from the plugin's icon library) per session, by position, '' = no own
 * icon (the Curriculum widget then shows its number or its icon). They live
 * apart from the `curriculum` rows so the «Title | what it covers» text and
 * every place that reads it stay unchanged.
 *
 * The box (and the studio panel's course form) prints one native <select> per session, which posts without
 * JavaScript; course-icons-admin.js turns each into a button that opens a
 * grid of the icons and keeps the rows in step with the curriculum text.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

use LenzPlus\Core\Asset;
use LenzPlus\Core\Icon_Library;
use LenzPlus\Modules\Builder\Elementor\Integration;

defined( 'ABSPATH' ) || exit;

/**
 * Picker markup, cleaning and the editor script.
 */
final class Session_Icons {

	/** Details key. */
	public const KEY = 'curriculum_icons';

	/** Script and style handle. */
	private const HANDLE = 'lzp-course-icons-admin';

	/** Hooks the script on the product editor. */
	public function register(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/** Loads the picker on the product editor. */
	public function enqueue(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && 'post' === $screen->base && 'product' === $screen->post_type ) {
			self::enqueue_assets();
		}
	}

	/** The picker's script and style (also queued by the studio panel's course form). */
	public static function enqueue_assets(): void {
		wp_enqueue_style( self::HANDLE, Asset::url( 'assets/modules/builder/css/course-icons-admin.css' ), array(), LENZ_PLUS_VERSION );
		wp_enqueue_script( self::HANDLE, Asset::url( 'assets/modules/builder/js/course-icons-admin.js' ), array(), LENZ_PLUS_VERSION, true );
		wp_add_inline_script(
			self::HANDLE,
			'window.lzpCourseIcons = ' . wp_json_encode(
				array(
					'none'    => __( 'No icon', 'lenz-plus' ),
					'choose'  => __( 'Choose an icon', 'lenz-plus' ),
					/* translators: %s: session number. */
					'session' => __( 'Session %s', 'lenz-plus' ),
				)
			) . ';',
			'before'
		);
	}

	/**
	 * Valid icon keys from a posted list ('' kept: it holds a session's place).
	 *
	 * @param mixed $raw Posted list.
	 * @return string[]
	 */
	public static function clean( $raw ): array {
		if ( ! is_array( $raw ) ) {
			return array();
		}

		$keys  = Icon_Library::icon_keys();
		$clean = array();
		foreach ( array_values( $raw ) as $value ) {
			$value   = is_string( $value ) ? sanitize_key( $value ) : '';
			$clean[] = in_array( $value, $keys, true ) ? $value : '';
		}

		// Trailing empty places carry nothing.
		while ( $clean && '' === end( $clean ) ) {
			array_pop( $clean );
		}

		return $clean;
	}

	/**
	 * The course box row: the label and the picker.
	 *
	 * @param array    $sessions [title, description] rows.
	 * @param string[] $icons    Saved keys, by position.
	 */
	public static function row( array $sessions, array $icons ): void {
		echo '<tr><th scope="row">' . esc_html__( 'Session icons', 'lenz-plus' ) . '</th><td>';
		self::picker( $sessions, $icons );
		echo '</td></tr>';
	}

	/**
	 * One picker per session, with the help line and the script's templates.
	 *
	 * @param array    $sessions [title, description] rows.
	 * @param string[] $icons    Saved keys, by position.
	 */
	public static function picker( array $sessions, array $icons ): void {
		$catalog = Icon_Library::catalog()['icons'];
		$pack    = self::pack();
		?>
		<div class="lzp-cicons" data-lzp-cicons>
			<ol class="lzp-cicons__list" data-lzp-cicons-list>
				<?php
				foreach ( array_values( $sessions ) as $index => $session ) {
					self::item( (string) $session[0], $icons[ $index ] ?? '', $catalog );
				}
				?>
			</ol>
			<p class="description"><?php esc_html_e( 'Optional: give any session its own icon; sessions left on "No icon" keep what the Curriculum widget shows (number or icon). The rows follow the curriculum above.', 'lenz-plus' ); ?></p>
			<template data-lzp-cicons-row><?php self::item( '', '', $catalog ); ?></template>
			<template data-lzp-cicons-grid>
				<?php
				foreach ( $catalog as $icon ) {
					printf(
						'<button type="button" class="lzp-cicons__pick" data-key="%1$s" title="%2$s" aria-label="%2$s">%3$s</button>',
						esc_attr( $icon['key'] ),
						esc_attr( $icon['label'] ),
						Icon_Library::svg( $pack, $icon['key'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled icon markup.
					);
				}
				?>
			</template>
		</div>
		<?php
	}

	/**
	 * One session's picker.
	 *
	 * @param string $title   Session title.
	 * @param string $current Chosen key.
	 * @param array  $catalog Icon catalog rows.
	 */
	private static function item( string $title, string $current, array $catalog ): void {
		echo '<li class="lzp-cicons__item"><span class="lzp-cicons__title">' . esc_html( $title ) . '</span>';
		echo '<select name="lzp_course[' . esc_attr( self::KEY ) . '][]" class="lzp-cicons__select" aria-label="' . esc_attr( '' !== $title ? $title : __( 'Session icon', 'lenz-plus' ) ) . '">';
		echo '<option value="">' . esc_html__( 'No icon', 'lenz-plus' ) . '</option>';
		foreach ( $catalog as $icon ) {
			printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $icon['key'] ), selected( $current, $icon['key'], false ), esc_html( $icon['label'] ) );
		}
		echo '</select></li>';
	}

	/** SVG pack for the previews (the Builder's choice; the Lenz font falls back to SVG). */
	private static function pack(): string {
		$module = Integration::module();

		return $module ? (string) $module->settings()['options']['icon_pack'] : 'lucide';
	}
}
