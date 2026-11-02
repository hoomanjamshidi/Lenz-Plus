<?php
/**
 * CSV downloads of the panel (customers, students, reports, the activity
 * log): UTF-8 with a byte order mark (Excel needs it for Persian) and cells
 * that start like a formula made plain text (formula injection).
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Shop;

defined( 'ABSPATH' ) || exit;

/**
 * CSV streaming.
 */
final class Csv {

	/**
	 * Sends a whole file as a download.
	 *
	 * @param string   $name   File name prefix (the date is added).
	 * @param string[] $header Column names.
	 * @param iterable $rows   Rows of cells, in header order.
	 */
	public static function send( string $name, array $header, iterable $rows ): void {
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . sanitize_file_name( $name ) . '-' . gmdate( 'Y-m-d' ) . '.csv' );

		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- streaming a download.
		fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		self::put( $out, $header );
		foreach ( $rows as $row ) {
			self::put( $out, (array) $row );
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	}

	/**
	 * Writes one line. An explicit, empty escape character keeps RFC 4180
	 * quoting only (PHP 8.4 deprecates the default).
	 *
	 * @param resource $out   Stream.
	 * @param array    $cells Cells.
	 */
	private static function put( $out, array $cells ): void {
		foreach ( $cells as $i => $cell ) {
			$cell = (string) $cell;
			if ( '' !== $cell && ! is_numeric( $cell ) && preg_match( '/^[=+\-@\t\r]/u', $cell ) ) {
				$cell = "'" . $cell;
			}
			$cells[ $i ] = $cell;
		}
		fputcsv( $out, array_values( $cells ), ',', '"', '' );
	}
}
