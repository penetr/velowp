<?php

namespace VeloWP\Queue;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class QueueRepository {
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'velowp_queue';
	}

	public static function enqueue_convert( $original_path ) {
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"INSERT IGNORE INTO " . self::table() . " (task_type, original_path, status, attempts, updated_at) VALUES ('convert', %s, 'pending', 0, NOW())",
				$original_path
			)
		);
	}

	public static function take_batch( $size ) {
		global $wpdb;
		$size = (int) $size;
		$ids  = $wpdb->get_col( "SELECT id FROM " . self::table() . " WHERE status='pending' ORDER BY id ASC LIMIT {$size}" );
		if ( empty( $ids ) ) {
			return array();
		}
		$id_list = implode( ',', array_map( 'intval', $ids ) );
		$wpdb->query( "UPDATE " . self::table() . " SET status='processing', updated_at=NOW() WHERE id IN ({$id_list}) AND status='pending'" );
		return $wpdb->get_results( "SELECT * FROM " . self::table() . " WHERE id IN ({$id_list})", ARRAY_A );
	}

	public static function mark_done( $id ) {
		global $wpdb;
		$wpdb->update( self::table(), array( 'status' => 'done', 'updated_at' => current_time( 'mysql', 1 ) ), array( 'id' => (int) $id ) );
	}

	public static function mark_skipped( $id, $error = '' ) {
		global $wpdb;
		$wpdb->update( self::table(), array( 'status' => 'skipped', 'last_error' => $error, 'updated_at' => current_time( 'mysql', 1 ) ), array( 'id' => (int) $id ) );
	}

	public static function mark_error( $id, $error = '' ) {
		global $wpdb;
		$task = $wpdb->get_row( $wpdb->prepare( 'SELECT attempts FROM ' . self::table() . ' WHERE id=%d', (int) $id ), ARRAY_A );
		$next = isset( $task['attempts'] ) ? (int) $task['attempts'] + 1 : 1;
		$max  = (int) \VeloWP\Core\Settings::get( 'max_attempts', 3 );
		$status = $next >= $max ? 'error' : 'pending';
		$wpdb->update(
			self::table(),
			array(
				'status'     => $status,
				'attempts'   => $next,
				'last_error' => $error,
				'updated_at' => current_time( 'mysql', 1 ),
			),
			array( 'id' => (int) $id )
		);
	}

	public static function reset_stale_processing( $ttl ) {
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE " . self::table() . " SET status='pending', updated_at=NOW() WHERE status='processing' AND updated_at < (UTC_TIMESTAMP() - INTERVAL %d SECOND)",
				(int) $ttl
			)
		);
	}

	public static function counts() {
		global $wpdb;
		$rows = $wpdb->get_results( 'SELECT status, COUNT(*) as cnt FROM ' . self::table() . ' GROUP BY status', ARRAY_A );
		$out  = array( 'pending' => 0, 'processing' => 0, 'done' => 0, 'error' => 0, 'skipped' => 0 );
		foreach ( $rows as $row ) {
			$out[ $row['status'] ] = (int) $row['cnt'];
		}
		return $out;
	}
}
