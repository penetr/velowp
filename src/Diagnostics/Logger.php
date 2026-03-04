<?php

namespace VeloWP\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Logger {
	const OPTION = 'velowp_log';
	const LIMIT  = 200;

	public static function error( $category, $path, $message ) {
		self::write( 'error', $category, $path, $message );
	}

	public static function write( $level, $category, $path, $message ) {
		$rows = get_option( self::OPTION, array() );
		$rows[] = array(
			'time'     => current_time( 'mysql', 1 ),
			'level'    => sanitize_key( $level ),
			'category' => sanitize_key( $category ),
			'path'     => sanitize_text_field( $path ),
			'message'  => sanitize_text_field( $message ),
		);
		if ( count( $rows ) > self::LIMIT ) {
			$rows = array_slice( $rows, -1 * self::LIMIT );
		}
		update_option( self::OPTION, $rows, false );
	}

	public static function latest( $count = 20 ) {
		$rows = get_option( self::OPTION, array() );
		return array_reverse( array_slice( $rows, -1 * (int) $count ) );
	}
}
