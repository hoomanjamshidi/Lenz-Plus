<?php
/**
 * Declarative form controls for module panels.
 *
 * Every control carries `data-lzp-bind="<dot.path>"`; admin.js reads the
 * module settings, fills the controls and writes changes back by path, so
 * module views only describe *which* settings exist — no per-field JS.
 *
 * Shared `$args`:
 *   - help    (string) helper text under the control
 *   - show_if (string) conditional display, e.g. `style=floating|pill`, `!behavior.haptic`
 *
 * @package LenzPlus
 */

namespace LenzPlus\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Static renderers for toggle, select, segmented, range, text and colour controls.
 */
final class Fields {

	/**
	 * On/off switch rendered as a full-width row.
	 *
	 * @param string $path  Settings path.
	 * @param string $label Field label.
	 * @param array  $args  Shared args, plus `chip` (string[]): a status chip after
	 *                      the label, as text and tone (`accent`, `warn` or '').
	 */
	public static function toggle( string $path, string $label, array $args = array() ): void {
		$id = self::id( $path );
		self::open( 'toggle', $args );
		?>
		<label class="lzp-switch-row" for="<?php echo esc_attr( $id ); ?>">
			<span class="lzp-switch-row__text">
				<span class="lzp-field__label">
					<?php echo esc_html( $label ); ?>
					<?php if ( ! empty( $args['chip'] ) ) : ?>
						<span class="lzp-chip<?php echo empty( $args['chip'][1] ) ? '' : esc_attr( ' lzp-chip--' . $args['chip'][1] ); ?>"><?php echo esc_html( $args['chip'][0] ); ?></span>
					<?php endif; ?>
				</span>
				<?php self::help( $args ); ?>
			</span>
			<span class="lzp-switch">
				<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" data-lzp-bind="<?php echo esc_attr( $path ); ?>">
				<span class="lzp-switch__track" aria-hidden="true"></span>
			</span>
		</label>
		<?php
		self::close();
	}

	/**
	 * @param string                $path    Settings path.
	 * @param string                $label   Field label.
	 * @param array<string, string> $options Value => label.
	 * @param array                 $args    Shared args.
	 */
	public static function select( string $path, string $label, array $options, array $args = array() ): void {
		$id = self::id( $path );
		self::open( 'select', $args );
		self::label( $id, $label );
		?>
		<div class="lzp-select">
			<select id="<?php echo esc_attr( $id ); ?>" data-lzp-bind="<?php echo esc_attr( $path ); ?>">
				<?php foreach ( $options as $value => $option_label ) : ?>
					<option value="<?php echo esc_attr( (string) $value ); ?>"><?php echo esc_html( $option_label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
		self::help( $args );
		self::close();
	}

	/**
	 * Segmented control (radio group) for a handful of options.
	 *
	 * @param string                $path    Settings path.
	 * @param string                $label   Field label.
	 * @param array<string, string> $options Value => label.
	 * @param array                 $args    Shared args.
	 */
	public static function segmented( string $path, string $label, array $options, array $args = array() ): void {
		$name = self::id( $path );
		self::open( 'segmented', $args );
		?>
		<span class="lzp-field__label" id="<?php echo esc_attr( $name ); ?>-label"><?php echo esc_html( $label ); ?></span>
		<div class="lzp-segmented" role="radiogroup" aria-labelledby="<?php echo esc_attr( $name ); ?>-label">
			<?php foreach ( $options as $value => $option_label ) : ?>
				<label class="lzp-segmented__option">
					<input type="radio" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" data-lzp-bind="<?php echo esc_attr( $path ); ?>">
					<span><?php echo esc_html( $option_label ); ?></span>
				</label>
			<?php endforeach; ?>
		</div>
		<?php
		self::help( $args );
		self::close();
	}

	/**
	 * Slider with a live value read-out.
	 *
	 * @param string    $path  Settings path.
	 * @param string    $label Field label.
	 * @param int|float $min   Minimum.
	 * @param int|float $max   Maximum.
	 * @param int|float $step  Step.
	 * @param string    $unit  Unit shown after the value (e.g. `px`).
	 * @param array     $args  Shared args.
	 */
	public static function range( string $path, string $label, $min, $max, $step = 1, string $unit = 'px', array $args = array() ): void {
		$id = self::id( $path );
		self::open( 'range', $args );
		?>
		<div class="lzp-range__head">
			<?php self::label( $id, $label ); ?>
			<output class="lzp-range__value" for="<?php echo esc_attr( $id ); ?>"><span data-lzp-output="<?php echo esc_attr( $path ); ?>"></span><?php echo esc_html( $unit ); ?></output>
		</div>
		<input type="range" class="lzp-range" id="<?php echo esc_attr( $id ); ?>" min="<?php echo esc_attr( (string) $min ); ?>" max="<?php echo esc_attr( (string) $max ); ?>" step="<?php echo esc_attr( (string) $step ); ?>" data-lzp-bind="<?php echo esc_attr( $path ); ?>" data-lzp-type="number">
		<?php
		self::help( $args );
		self::close();
	}

	/**
	 * Text input.
	 *
	 * @param string $path  Settings path.
	 * @param string $label Field label.
	 * @param array  $args  Shared args plus `placeholder`, `type` (text|number|url) and `dir` (ltr for codes/URLs).
	 */
	public static function text( string $path, string $label, array $args = array() ): void {
		$id   = self::id( $path );
		$type = $args['type'] ?? 'text';
		self::open( 'text', $args );
		self::label( $id, $label );
		?>
		<input
			type="<?php echo esc_attr( $type ); ?>"
			class="lzp-input"
			id="<?php echo esc_attr( $id ); ?>"
			data-lzp-bind="<?php echo esc_attr( $path ); ?>"
			<?php echo 'number' === $type ? 'data-lzp-type="number"' : ''; ?>
			<?php echo isset( $args['dir'] ) ? 'dir="' . esc_attr( $args['dir'] ) . '"' : ''; ?>
			placeholder="<?php echo esc_attr( $args['placeholder'] ?? '' ); ?>"
		>
		<?php
		self::help( $args );
		self::close();
	}

	/**
	 * Colour picker with a "use theme default" state. admin.js builds the
	 * widget inside the placeholder element.
	 *
	 * @param string $path     Settings path.
	 * @param string $label    Field label.
	 * @param string $fallback CSS colour shown when the value is empty (the theme default).
	 * @param array  $args     Shared args.
	 */
	public static function color( string $path, string $label, string $fallback, array $args = array() ): void {
		self::open( 'color', $args );
		?>
		<span class="lzp-field__label"><?php echo esc_html( $label ); ?></span>
		<div class="lzp-color" data-lzp-color="<?php echo esc_attr( $path ); ?>" data-fallback="<?php echo esc_attr( $fallback ); ?>"></div>
		<?php
		self::help( $args );
		self::close();
	}

	/**
	 * @param string $type Field type modifier.
	 * @param array  $args Shared args.
	 */
	private static function open( string $type, array $args ): void {
		printf(
			'<div class="lzp-field lzp-field--%s"%s>',
			esc_attr( $type ),
			empty( $args['show_if'] ) ? '' : ' data-lzp-show-if="' . esc_attr( $args['show_if'] ) . '"'
		);
	}

	private static function close(): void {
		echo '</div>';
	}

	/**
	 * @param string $id    Control id.
	 * @param string $label Label text.
	 */
	private static function label( string $id, string $label ): void {
		printf( '<label class="lzp-field__label" for="%s">%s</label>', esc_attr( $id ), esc_html( $label ) );
	}

	/**
	 * @param array $args Shared args.
	 */
	private static function help( array $args ): void {
		if ( ! empty( $args['help'] ) ) {
			printf( '<p class="lzp-field__help">%s</p>', esc_html( $args['help'] ) );
		}
	}

	/**
	 * @param string $path Settings path.
	 */
	private static function id( string $path ): string {
		return 'lzp-f-' . str_replace( '.', '-', $path );
	}
}
