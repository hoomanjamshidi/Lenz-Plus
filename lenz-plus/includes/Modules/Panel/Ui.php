<?php
/**
 * Markup pieces shared by the panel's screens: icons, status pills, dates,
 * list rows, filters, pagination and form fields.
 *
 * Every screen builds its page from these, so the panel looks and behaves
 * the same everywhere (44px touch targets, visible labels, one way to show
 * an empty list) and escaping happens in one place.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel;

use LenzPlus\Core\Icon_Library;
use LenzPlus\Core\Persian;

defined( 'ABSPATH' ) || exit;

/**
 * Static markup helpers of the panel.
 */
final class Ui {

	/** Line icon pack of the panel (close to the designs' line icons). */
	private const PACK = 'lucide';

	/**
	 * Icons the bundled packs lack, from Lucide (ISC licence), drawn on its 24px grid.
	 */
	private const EXTRA = array(
		'pencil' => '<path d="M21.17 6.81a2.83 2.83 0 0 0-4-4L3.84 16.17a2 2 0 0 0-.5.83l-1.32 4.35a.5.5 0 0 0 .62.62l4.35-1.32a2 2 0 0 0 .83-.5z"/><path d="m15 5 4 4"/>',
		'upload' => '<path d="M12 3v12"/><path d="m17 8-5-5-5 5"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>',
		'back'   => '<path d="m9 18 6-6-6-6"/>',
		'inbox'  => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
		'page'   => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/>',
		'undo'   => '<path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/>',
	);

	/** Status → tone of its pill. */
	private const TONES = array(
		'publish'    => 'ok',
		'completed'  => 'ok',
		'confirmed'  => 'ok',
		'done'       => 'ok',
		'approved'   => 'ok',
		'processing' => 'info',
		'new'        => 'info',
		'future'     => 'info',
		'draft'      => 'muted',
		'private'    => 'muted',
		'pending'    => 'warn',
		'on-hold'    => 'warn',
		'hold'       => 'warn',
		'cancelled'  => 'bad',
		'failed'     => 'bad',
		'refunded'   => 'bad',
		'spam'       => 'bad',
		'trash'      => 'bad',
	);

	/**
	 * An inline line icon.
	 *
	 * @param string $key   Semantic icon key (catalog or EXTRA).
	 * @param string $extra Extra class.
	 */
	public static function icon( string $key, string $extra = '' ): string {
		$class = trim( 'lzp-ico ' . $extra );

		if ( isset( self::EXTRA[ $key ] ) ) {
			return '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . self::EXTRA[ $key ] . '</svg>';
		}

		return Icon_Library::svg( self::PACK, $key, false, $class );
	}

	/**
	 * Prints an icon (bundled SVG, safe as is).
	 *
	 * @param string $key   Icon key.
	 * @param string $extra Extra class.
	 */
	public static function the_icon( string $key, string $extra = '' ): void {
		echo self::icon( $key, $extra ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG.
	}

	/**
	 * A status pill.
	 *
	 * @param string $status Status key (decides the colour).
	 * @param string $label  Text.
	 */
	public static function pill( string $status, string $label ): string {
		$tone = self::TONES[ $status ] ?? 'muted';

		return '<span class="lzp-pill lzp-pill--' . esc_attr( $tone ) . '">' . esc_html( $label ) . '</span>';
	}

	/**
	 * A date as the site reads it: Jalali with Persian digits on Persian
	 * sites, else the site's date format.
	 *
	 * @param int  $timestamp Unix time (UTC).
	 * @param bool $time      Add the time of day.
	 */
	public static function date( int $timestamp, bool $time = false ): string {
		if ( $timestamp <= 0 ) {
			return '';
		}

		if ( Persian::is_site_persian() ) {
			$date = ( new \DateTimeImmutable( '@' . $timestamp ) )->setTimezone( wp_timezone() );
			$text = Persian::jalali_date( $date ) . ( $time ? ' · ' . $date->format( 'H:i' ) : '' );

			return Persian::digits( $text );
		}

		$format = get_option( 'date_format' ) . ( $time ? ' ' . get_option( 'time_format' ) : '' );

		return wp_date( $format, $timestamp );
	}

	/**
	 * A count in the site's digits.
	 *
	 * @param int|float $number Number.
	 */
	public static function number( $number ): string {
		return Persian::number( $number );
	}

	/**
	 * One tappable row of a list.
	 *
	 * @param array $row {
	 *     @type string $url    Where the row leads.
	 *     @type string $title  Main line.
	 *     @type string $meta   Second line (plain text).
	 *     @type string $thumb  Image URL, or '' for the icon.
	 *     @type string $icon   Icon key when there is no image.
	 *     @type string $pill   Status pill markup (from pill()).
	 *     @type string $aside  Short text at the end (e.g. a price).
	 * }
	 */
	public static function row( array $row ): void {
		$row += array(
			'url'   => '',
			'title' => '',
			'meta'  => '',
			'thumb' => '',
			'icon'  => 'page',
			'pill'  => '',
			'aside' => '',
		);
		?>
		<li class="lzp-row">
			<a class="lzp-row__link" href="<?php echo esc_url( $row['url'] ); ?>">
				<span class="lzp-row__thumb<?php echo '' === $row['thumb'] ? ' is-icon' : ''; ?>" aria-hidden="true">
					<?php if ( '' !== $row['thumb'] ) : ?>
						<img src="<?php echo esc_url( $row['thumb'] ); ?>" alt="" loading="lazy" decoding="async">
					<?php else : ?>
						<?php self::the_icon( $row['icon'] ); ?>
					<?php endif; ?>
				</span>
				<span class="lzp-row__body">
					<span class="lzp-row__title"><?php echo esc_html( $row['title'] ); ?></span>
					<?php if ( '' !== $row['meta'] ) : ?>
						<span class="lzp-row__meta"><?php echo esc_html( $row['meta'] ); ?></span>
					<?php endif; ?>
				</span>
				<span class="lzp-row__end">
					<?php echo $row['pill']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built by pill(). ?>
					<?php if ( '' !== $row['aside'] ) : ?>
						<span class="lzp-row__aside"><?php echo esc_html( $row['aside'] ); ?></span>
					<?php endif; ?>
				</span>
			</a>
		</li>
		<?php
	}

	/**
	 * A list of rows, or the empty message.
	 *
	 * @param array[] $rows       Rows for row().
	 * @param string  $empty_text Message when there are none.
	 */
	public static function rows( array $rows, string $empty_text ): void {
		if ( ! $rows ) {
			self::empty_state( $empty_text );
			return;
		}

		echo '<ul class="lzp-rows">';
		foreach ( $rows as $row ) {
			self::row( $row );
		}
		echo '</ul>';
	}

	/**
	 * The empty-list message.
	 *
	 * @param string $message Text.
	 * @param string $icon    Icon key.
	 */
	public static function empty_state( string $message, string $icon = 'inbox' ): void {
		?>
		<div class="lzp-empty">
			<?php self::the_icon( $icon ); ?>
			<p><?php echo esc_html( $message ); ?></p>
		</div>
		<?php
	}

	/**
	 * Filter chips: links that keep the other query arguments.
	 *
	 * @param string                $base    URL of the list.
	 * @param string                $arg     Query argument the chips set.
	 * @param array<string, string> $choices Value => label ('' = all).
	 * @param string                $current Current value.
	 */
	public static function chips( string $base, string $arg, array $choices, string $current ): void {
		?>
		<nav class="lzp-chips" aria-label="<?php esc_attr_e( 'Filter', 'lenz-plus' ); ?>">
			<?php foreach ( $choices as $value => $label ) : ?>
				<?php $url = '' === (string) $value ? remove_query_arg( array( $arg, 'paged' ), $base ) : add_query_arg( $arg, (string) $value, remove_query_arg( 'paged', $base ) ); ?>
				<a class="lzp-chip" href="<?php echo esc_url( $url ); ?>"<?php echo (string) $value === $current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>
		<?php
	}

	/**
	 * The search box of a list (GET, keeps the filter arguments).
	 *
	 * @param string   $action Form target (the list's URL without arguments).
	 * @param string   $value  Current search.
	 * @param string   $label  Placeholder and hidden label.
	 * @param string[] $keep   Query arguments carried over (name => value).
	 */
	public static function search( string $action, string $value, string $label, array $keep = array() ): void {
		?>
		<form class="lzp-search" role="search" method="get" action="<?php echo esc_url( $action ); ?>">
			<?php foreach ( $keep as $name => $kept ) : ?>
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $kept ); ?>">
			<?php endforeach; ?>
			<label class="screen-reader-text" for="lzp-search"><?php echo esc_html( $label ); ?></label>
			<?php self::the_icon( 'search' ); ?>
			<input type="search" id="lzp-search" name="s" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $label ); ?>" enterkeyhint="search">
		</form>
		<?php
	}

	/**
	 * Previous / next page links.
	 *
	 * @param string $base  URL of the list (with its filters).
	 * @param int    $page  Current page.
	 * @param int    $pages Number of pages.
	 */
	public static function pagination( string $base, int $page, int $pages ): void {
		if ( $pages <= 1 ) {
			return;
		}
		?>
		<nav class="lzp-pager" aria-label="<?php esc_attr_e( 'Pages', 'lenz-plus' ); ?>">
			<?php if ( $page > 1 ) : ?>
				<a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( add_query_arg( 'paged', $page - 1, $base ) ); ?>"><?php esc_html_e( 'Previous', 'lenz-plus' ); ?></a>
			<?php endif; ?>
			<span class="lzp-pager__now">
				<?php
				/* translators: 1: current page, 2: number of pages. */
				echo esc_html( sprintf( __( 'Page %1$s of %2$s', 'lenz-plus' ), self::number( $page ), self::number( $pages ) ) );
				?>
			</span>
			<?php if ( $page < $pages ) : ?>
				<a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( add_query_arg( 'paged', $page + 1, $base ) ); ?>"><?php esc_html_e( 'Next', 'lenz-plus' ); ?></a>
			<?php endif; ?>
		</nav>
		<?php
	}

	/**
	 * A labelled text input.
	 *
	 * @param string $name  Field name.
	 * @param string $label Visible label.
	 * @param string $value Value.
	 * @param array  $args  `type`, `placeholder`, `help`, `required`, `dir`, `inputmode`, `min`, `step`, `autocomplete`.
	 */
	public static function input( string $name, string $label, string $value, array $args = array() ): void {
		$id = self::field_id( $name );
		?>
		<div class="lzp-field">
			<label class="lzp-field__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			<input class="lzp-input" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" type="<?php echo esc_attr( $args['type'] ?? 'text' ); ?>"<?php self::attrs( $args, array( 'placeholder', 'dir', 'inputmode', 'min', 'step', 'autocomplete' ) ); ?><?php echo empty( $args['required'] ) ? '' : ' required'; ?>>
			<?php self::help( $args ); ?>
		</div>
		<?php
	}

	/**
	 * A labelled text area.
	 *
	 * @param string $name  Field name.
	 * @param string $label Visible label.
	 * @param string $value Value.
	 * @param array  $args  `rows`, `placeholder`, `help`.
	 */
	public static function textarea( string $name, string $label, string $value, array $args = array() ): void {
		$id = self::field_id( $name );
		?>
		<div class="lzp-field">
			<label class="lzp-field__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			<textarea class="lzp-input lzp-input--area" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="<?php echo esc_attr( (string) ( $args['rows'] ?? 4 ) ); ?>"<?php self::attrs( $args, array( 'placeholder' ) ); ?>><?php echo esc_textarea( $value ); ?></textarea>
			<?php self::help( $args ); ?>
		</div>
		<?php
	}

	/**
	 * A labelled select.
	 *
	 * @param string                $name    Field name.
	 * @param string                $label   Visible label.
	 * @param array<string, string> $options Value => label.
	 * @param string                $value   Selected value.
	 * @param array                 $args    `help`.
	 */
	public static function select( string $name, string $label, array $options, string $value, array $args = array() ): void {
		$id = self::field_id( $name );
		?>
		<div class="lzp-field">
			<label class="lzp-field__label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			<span class="lzp-select">
				<select class="lzp-input" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>">
					<?php foreach ( $options as $option => $option_label ) : ?>
						<option value="<?php echo esc_attr( (string) $option ); ?>"<?php selected( (string) $option, $value ); ?>><?php echo esc_html( $option_label ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php self::the_icon( 'chevron-down' ); ?>
			</span>
			<?php self::help( $args ); ?>
		</div>
		<?php
	}

	/**
	 * Choice buttons (radio inputs styled as a segmented control).
	 *
	 * @param string                $name    Field name.
	 * @param string                $label   Group label.
	 * @param array<string, string> $options Value => label.
	 * @param string                $value   Checked value.
	 * @param array                 $args    `help`.
	 */
	public static function choices( string $name, string $label, array $options, string $value, array $args = array() ): void {
		?>
		<fieldset class="lzp-field lzp-choices">
			<legend class="lzp-field__label"><?php echo esc_html( $label ); ?></legend>
			<div class="lzp-choices__row">
				<?php foreach ( $options as $option => $option_label ) : ?>
					<label class="lzp-choice">
						<input type="radio" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $option ); ?>"<?php checked( (string) $option, $value ); ?>>
						<span><?php echo esc_html( $option_label ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
			<?php self::help( $args ); ?>
		</fieldset>
		<?php
	}

	/**
	 * An on/off switch row.
	 *
	 * @param string $name    Field name (posts "1" when on).
	 * @param string $label   Label.
	 * @param bool   $checked Whether it is on.
	 * @param array  $args    `help`.
	 */
	public static function toggle( string $name, string $label, bool $checked, array $args = array() ): void {
		?>
		<label class="lzp-toggle">
			<span class="lzp-toggle__text">
				<span class="lzp-field__label"><?php echo esc_html( $label ); ?></span>
				<?php self::help( $args ); ?>
			</span>
			<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1"<?php checked( $checked ); ?>>
			<span class="lzp-toggle__track" aria-hidden="true"></span>
		</label>
		<?php
	}

	/**
	 * Terms as checkable chips, plus a box for new ones.
	 *
	 * @param string   $taxonomy Taxonomy.
	 * @param string   $label    Group label.
	 * @param int[]    $checked  Term IDs that are on.
	 * @param string[] $args     `new_label`: label of the new-term box ('' = none).
	 */
	public static function terms( string $taxonomy, string $label, array $checked, array $args = array() ): void {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'number'     => 200,
			)
		);
		$terms = is_array( $terms ) ? $terms : array();
		?>
		<fieldset class="lzp-field lzp-terms">
			<legend class="lzp-field__label"><?php echo esc_html( $label ); ?></legend>
			<?php if ( $terms ) : ?>
				<div class="lzp-terms__list">
					<?php foreach ( $terms as $term ) : ?>
						<label class="lzp-check-chip">
							<input type="checkbox" name="terms[<?php echo esc_attr( $taxonomy ); ?>][]" value="<?php echo esc_attr( (string) $term->term_id ); ?>"<?php checked( in_array( (int) $term->term_id, $checked, true ) ); ?>>
							<span><?php echo esc_html( $term->name ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $args['new_label'] ) && current_user_can( get_taxonomy( $taxonomy )->cap->manage_terms ) ) : ?>
				<?php $id = self::field_id( 'new_terms_' . $taxonomy ); ?>
				<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $args['new_label'] ); ?></label>
				<input class="lzp-input" id="<?php echo esc_attr( $id ); ?>" name="new_terms[<?php echo esc_attr( $taxonomy ); ?>]" placeholder="<?php echo esc_attr( $args['new_label'] ); ?>">
			<?php endif; ?>
		</fieldset>
		<?php
	}

	/**
	 * Optional attributes from the args.
	 *
	 * @param array    $args Field args.
	 * @param string[] $keys Attribute names to print.
	 */
	private static function attrs( array $args, array $keys ): void {
		foreach ( $keys as $key ) {
			if ( isset( $args[ $key ] ) && '' !== (string) $args[ $key ] ) {
				printf( ' %s="%s"', esc_attr( $key ), esc_attr( (string) $args[ $key ] ) );
			}
		}
	}

	/**
	 * The help line under a field.
	 *
	 * @param array $args Field args (`help`).
	 */
	private static function help( array $args ): void {
		if ( ! empty( $args['help'] ) ) {
			echo '<span class="lzp-field__help">' . esc_html( $args['help'] ) . '</span>';
		}
	}

	/**
	 * An element id from a field name (`lzp_project[year]` → `lzp-f-lzp_project-year`).
	 *
	 * @param string $name Field name.
	 */
	private static function field_id( string $name ): string {
		return 'lzp-f-' . trim( (string) preg_replace( '/[^a-z0-9_]+/i', '-', $name ), '-' );
	}
}
