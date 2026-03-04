<?php

namespace VeloWP\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Lock {
	const KEY = 'velowp_worker_lock';

	public static function acquire( $ttl ) {
		$now    = time();
		$locked = (int) get_option( self::KEY, 0 );
		if ( $locked > $now ) {
			return false;
		}
		update_option( self::KEY, $now + (int) $ttl );
		return true;
	}

	public static function release() {
		delete_option( self::KEY );
	}
}
