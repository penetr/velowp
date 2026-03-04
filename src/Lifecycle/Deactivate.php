<?php

namespace VeloWP\Lifecycle;

use VeloWP\Core\Cron;
use VeloWP\Core\Settings;
use VeloWP\Delivery\Htaccess;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Deactivate {
	public static function run() {
		wp_clear_scheduled_hook( Cron::HOOK );
		if ( Settings::get( 'deactivate_remove_htaccess', 1 ) ) {
			Htaccess::remove_rules();
		}
	}
}
