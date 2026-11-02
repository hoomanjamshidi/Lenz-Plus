<?php
/**
 * The activity log: who changed what, from the studio panel, wp-admin or
 * the site.
 *
 * Entries live in their own table (`{prefix}lzp_panel_log`): one row per
 * change with the person, where it happened, the object and a JSON list of
 * changed fields ({ field, from, to }). Only changes made by a signed-in
 * person are kept. Rows older than the kept number of days are removed by a
 * daily cron job. The «Activity» section switch turns logging off too.
 * Ported from Daftar's Activity\Log.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Activity;

defined( 'ABSPATH' ) || exit;

/**
 * Log storage and queries.
 */
final class Log {

	/** Schema version of the table. */
	private const DB_VERSION = '1';

	/** Stores the schema version the table was built for (removed on uninstall). */
	private const DB_OPTION = 'lenz_plus_panel_log_db';

	/** Daily pruning job. */
	public const CRON = 'lenz_plus_panel_prune_log';

	/** @var bool Whether the current request posts a panel form. */
	private static $in_panel = false;

	/** @var bool Whether logging is on (settings). */
	private static $enabled = false;

	/** @var int Days entries are kept. */
	private static $days = 90;

	/** Table name. */
	public static function table(): string {
		global $wpdb;

		return $wpdb->prefix . 'lzp_panel_log';
	}

	/**
	 * Applies the settings, builds the table once per schema version and
	 * schedules the pruning.
	 *
	 * @param bool $enabled Whether logging is on.
	 * @param int  $days    Days entries are kept.
	 */
	public static function register( bool $enabled, int $days ): void {
		self::$enabled = $enabled;
		self::$days    = max( 1, $days );

		add_action( self::CRON, array( self::class, 'prune' ) );
		if ( ! $enabled ) {
			return;
		}

		self::install();
		Watcher::register();
	}

	/** Creates or upgrades the table and schedules the pruning. */
	private static function install(): void {
		if ( get_option( self::DB_OPTION ) !== self::DB_VERSION ) {
			global $wpdb;
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$table   = self::table();
			$collate = $wpdb->get_charset_collate();
			dbDelta(
				"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				created_at datetime NOT NULL,
				user_id bigint(20) unsigned NOT NULL DEFAULT 0,
				source varchar(10) NOT NULL DEFAULT '',
				action varchar(40) NOT NULL,
				object_type varchar(20) NOT NULL DEFAULT '',
				object_id bigint(20) unsigned NOT NULL DEFAULT 0,
				object_label varchar(200) NOT NULL DEFAULT '',
				details longtext NULL,
				PRIMARY KEY  (id),
				KEY object (object_type, object_id),
				KEY user_id (user_id),
				KEY created_at (created_at)
				) {$collate};"
			);
			// Autoloaded: it is read on every request while logging is on.
			update_option( self::DB_OPTION, self::DB_VERSION, true );
		}

		if ( ! wp_next_scheduled( self::CRON ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON );
		}
	}

	/** Marks the request as a panel form (App, before the screen runs it). */
	public static function from_panel(): void {
		self::$in_panel = true;
	}

	/** Where the current change comes from: `panel`, `admin` or `site`. */
	public static function source(): string {
		if ( self::$in_panel ) {
			return 'panel';
		}

		return is_admin() ? 'admin' : 'site';
	}

	/**
	 * Adds an entry (only for signed-in people, while logging is on).
	 *
	 * @param string $action      Action key (status, updated, created, refunded…).
	 * @param string $object_type order, product, coupon, post, page, portfolio, comment, customer, wallet, booking.
	 * @param int    $object_id   Object ID.
	 * @param string $label       Human label of the object («#590», a product name).
	 * @param array  $changes     List of [ field, from, to ] (Watcher::change()).
	 */
	public static function add( string $action, string $object_type, int $object_id, string $label, array $changes = array() ): void {
		$user = get_current_user_id();
		if ( ! $user || ! self::$enabled ) {
			return;
		}

		global $wpdb;
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- our own table.
			self::table(),
			array(
				'created_at'   => gmdate( 'Y-m-d H:i:s' ),
				'user_id'      => $user,
				'source'       => self::source(),
				'action'       => sanitize_key( $action ),
				'object_type'  => sanitize_key( $object_type ),
				'object_id'    => $object_id,
				'object_label' => mb_substr( wp_strip_all_tags( $label ), 0, 200 ),
				'details'      => $changes ? wp_json_encode( $changes, JSON_UNESCAPED_UNICODE ) : null,
			),
			array( '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s' )
		);
	}

	/**
	 * Entries, newest first.
	 *
	 * @param array $filter   `user`, `object_type`, `object_id`, `source`.
	 * @param int   $page     Page.
	 * @param int   $per_page Page size.
	 * @return array{0: array[], 1: int} Rows and the total.
	 */
	public static function query( array $filter, int $page, int $per_page ): array {
		global $wpdb;
		$where  = array( '1=1' );
		$params = array();

		foreach ( array(
			'user'      => 'user_id',
			'object_id' => 'object_id',
		) as $key => $column ) {
			if ( ! empty( $filter[ $key ] ) ) {
				$where[]  = "{$column} = %d";
				$params[] = (int) $filter[ $key ];
			}
		}
		foreach ( array( 'object_type', 'source' ) as $column ) {
			if ( ! empty( $filter[ $column ] ) ) {
				$where[]  = "{$column} = %s";
				$params[] = sanitize_key( (string) $filter[ $column ] );
			}
		}

		$table = self::table();
		$sql   = implode( ' AND ', $where );
		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- our own table; values are prepared, the column names are fixed above.
		$total = (int) $wpdb->get_var( $params ? $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$sql}", $params ) : "SELECT COUNT(*) FROM {$table} WHERE {$sql}" );
		$rows  = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE {$sql} ORDER BY id DESC LIMIT %d OFFSET %d", array_merge( $params, array( $per_page, ( $page - 1 ) * $per_page ) ) ),
			ARRAY_A
		);
		// phpcs:enable

		return array( array_map( array( self::class, 'row' ), (array) $rows ), $total );
	}

	/**
	 * A database row with its details decoded.
	 *
	 * @param array $row Database row.
	 */
	private static function row( array $row ): array {
		$details = $row['details'] ? json_decode( $row['details'], true ) : array();

		return array(
			'time'        => (int) strtotime( $row['created_at'] . ' UTC' ),
			'user_id'     => (int) $row['user_id'],
			'source'      => (string) $row['source'],
			'action'      => (string) $row['action'],
			'object_type' => (string) $row['object_type'],
			'object_id'   => (int) $row['object_id'],
			'label'       => (string) $row['object_label'],
			'changes'     => is_array( $details ) ? $details : array(),
		);
	}

	/**
	 * People who appear in the log (for the filter), id => name.
	 *
	 * @return array<int, string>
	 */
	public static function people(): array {
		global $wpdb;
		$table = self::table();
		$ids   = $wpdb->get_col( "SELECT DISTINCT user_id FROM {$table} ORDER BY user_id" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- our own table, no input.
		$out   = array();
		foreach ( $ids as $id ) {
			$user = get_userdata( (int) $id );
			if ( $user ) {
				$out[ $user->ID ] = $user->display_name;
			}
		}

		return $out;
	}

	/** Removes entries older than the kept number of days (daily cron). */
	public static function prune(): void {
		global $wpdb;
		$table = self::table();
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE created_at < %s", gmdate( 'Y-m-d H:i:s', time() - self::$days * DAY_IN_SECONDS ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- our own table.
	}
}
