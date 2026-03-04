<?php

namespace VeloWP\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cron {
	const HOOK = 'velowp_worker_tick';

	public static function register() {
		add_action( 'admin_init', array( __CLASS__, 'ensure_scheduled' ) );
	}

	public static function ensure_scheduled() {
		if ( wp_doing_ajax() ) {
			return;
		}
		if ( Settings::get( 'stop_requested', 0 ) ) {
			return;
		}
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_single_event( time() + 5, self::HOOK );
		}
	}

	public static function schedule_next() {
		if ( Settings::get( 'stop_requested', 0 ) ) {
			return;
		}
		wp_schedule_single_event( time() + 10, self::HOOK );
	}
}
