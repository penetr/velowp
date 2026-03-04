<?php

namespace VeloWP\Core;

use VeloWP\Admin\Assets;
use VeloWP\Admin\SettingsPage;
use VeloWP\Delivery\Htaccess;
use VeloWP\Delivery\WpFilters;
use VeloWP\Queue\Worker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {
	public static function boot() {
		Settings::register();
		SettingsPage::register();
		Assets::register();
		Cron::register();
		Worker::register();
		WpFilters::register();
		Htaccess::maybe_apply();
	}
}
