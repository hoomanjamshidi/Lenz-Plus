<?php
/**
 * Cache for panel figures built from many orders (reports, guest buyers,
 * course students, the home screen's sales).
 *
 * Such a figure is rebuilt when the orders change (Report::bump() raises
 * the version), but not more often than once per MIN_AGE: on a busy store
 * every checkout changes the version, and rebuilding from thousands of
 * orders on every panel visit is what costs the server. One transient per
 * figure (the version is stored inside, not in the name), so old versions
 * do not pile up in the options table.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Shop;

defined( 'ABSPATH' ) || exit;

/**
 * Version-aware, rate-limited transient cache.
 */
final class Order_Cache {

	/** Seconds a figure is reused even after the orders changed. */
	private const MIN_AGE = 60;

	/**
	 * The cached figure, or a fresh one from `$build`.
	 *
	 * @param string   $name  Transient name (172 characters at most).
	 * @param int      $ttl   Seconds the figure may be kept while nothing changes.
	 * @param callable $build Builds the figure.
	 * @return mixed
	 */
	public static function remember( string $name, int $ttl, callable $build ) {
		$version = (int) get_option( Report::VERSION_OPTION, 0 );
		$entry   = get_transient( $name );

		if ( is_array( $entry ) && array_key_exists( 'data', $entry ) && ( $version === $entry['version'] || time() - (int) $entry['time'] < self::MIN_AGE ) ) {
			return $entry['data'];
		}

		$data = $build();
		set_transient(
			$name,
			array(
				'version' => $version,
				'time'    => time(),
				'data'    => $data,
			),
			$ttl
		);

		return $data;
	}
}
