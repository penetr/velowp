<?php

namespace VeloWP\Storage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FilesRepository {
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'velowp_files';
	}

	public static function upsert( $original_path, $derivative_path, $mtime_original ) {
		global $wpdb;
		$existing_id = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT id FROM ' . self::table() . ' WHERE original_path=%s AND derivative_format=%s LIMIT 1',
				$original_path,
				'webp'
			)
		);

		$data = array(
			'original_path'     => $original_path,
			'derivative_path'   => $derivative_path,
			'derivative_format' => 'webp',
			'mtime_original'    => (int) $mtime_original,
			'updated_at'        => current_time( 'mysql', 1 ),
		);

		if ( $existing_id ) {
			$wpdb->update( self::table(), $data, array( 'id' => (int) $existing_id ) );
			return;
		}

		$data['created_at'] = current_time( 'mysql', 1 );
		$wpdb->insert( self::table(), $data );
	}

	public static function all_derivatives() {
		global $wpdb;
		return $wpdb->get_col( 'SELECT derivative_path FROM ' . self::table() . " WHERE derivative_format='webp'" );
	}
}
