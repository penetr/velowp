<?php

namespace VeloWP\Lifecycle;

use VeloWP\Core\Checker;
use VeloWP\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Activate {
	public static function run() {
		Settings::register();
		self::create_tables();
		$report = Checker::environment_report();
		if ( ! $report['php_ok'] || ! $report['wp_ok'] || ( ! $report['gd_webp'] && ! $report['imagick_webp'] ) ) {
			deactivate_plugins( plugin_basename( VELOWP_FILE ) );
			wp_die( esc_html__( 'VeloWP requirements failed: PHP/WP version or WebP support missing.', 'velowp' ) );
		}
	}

	private static function create_tables() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();
		$table_files = $wpdb->prefix . 'velowp_files';
		$table_queue = $wpdb->prefix . 'velowp_queue';

		$sql_files = "CREATE TABLE {$table_files} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			original_path TEXT NOT NULL,
			derivative_path TEXT NOT NULL,
			derivative_format VARCHAR(16) NOT NULL DEFAULT 'webp',
			mtime_original BIGINT UNSIGNED NOT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY original_path (original_path(191)),
			KEY derivative_path (derivative_path(191))
		) {$charset};";

		$sql_queue = "CREATE TABLE {$table_queue} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			task_type ENUM('convert','cleanup_orphans','purge_generated') NOT NULL DEFAULT 'convert',
			original_path TEXT NULL,
			status ENUM('pending','processing','done','error','skipped') NOT NULL DEFAULT 'pending',
			attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
			last_error TEXT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY status_updated (status, updated_at),
			UNIQUE KEY uniq_task (task_type, original_path(191))
		) {$charset};";

		dbDelta( $sql_files );
		dbDelta( $sql_queue );
	}
}
