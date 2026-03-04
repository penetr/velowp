<?php

namespace VeloWP\Admin;

use VeloWP\Core\Checker;
use VeloWP\Core\Cron;
use VeloWP\Core\Settings;
use VeloWP\Diagnostics\Logger;
use VeloWP\Diagnostics\Report;
use VeloWP\Queue\QueueRepository;
use VeloWP\Queue\Scanner;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SettingsPage {
	public static function register() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_velowp_save', array( __CLASS__, 'save' ) );
		add_action( 'admin_post_velowp_scan', array( __CLASS__, 'scan' ) );
		add_action( 'admin_post_velowp_pause', array( __CLASS__, 'pause' ) );
		add_action( 'admin_post_velowp_resume', array( __CLASS__, 'resume' ) );
		add_action( 'admin_post_velowp_report', array( __CLASS__, 'report' ) );
	}

	public static function menu() {
		add_menu_page( 'VeloWP', 'VeloWP', 'manage_options', 'velowp', array( __CLASS__, 'render' ), 'dashicons-format-image' );
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$report = Checker::environment_report();
		$options = Settings::all();
		$counts = QueueRepository::counts();
		$logs = Logger::latest( 20 );
		require VELOWP_DIR . 'templates/admin-page.php';
	}

	public static function save() {
		self::guard();
		Settings::update(
			array(
				'delivery_method'            => sanitize_key( wp_unslash( $_POST['delivery_method'] ?? 'off' ) ),
				'jpeg_quality'               => max( 1, min( 100, (int) ( $_POST['jpeg_quality'] ?? 75 ) ) ),
				'png_mode'                   => sanitize_key( wp_unslash( $_POST['png_mode'] ?? 'lossless' ) ),
				'bypass_query_param'         => sanitize_key( wp_unslash( $_POST['bypass_query_param'] ?? 'no_webp' ) ),
				'auto_convert_media'         => empty( $_POST['auto_convert_media'] ) ? 0 : 1,
				'php_wide_enabled'           => empty( $_POST['php_wide_enabled'] ) ? 0 : 1,
				'deactivate_remove_htaccess' => empty( $_POST['deactivate_remove_htaccess'] ) ? 0 : 1,
			)
		);
		wp_safe_redirect( admin_url( 'admin.php?page=velowp&saved=1' ) );
		exit;
	}

	public static function scan() {
		self::guard();
		$count = Scanner::scan_uploads( (int) Settings::get( 'scan_chunk_size', 200 ) );
		Cron::schedule_next();
		wp_safe_redirect( admin_url( 'admin.php?page=velowp&scan=' . (int) $count ) );
		exit;
	}

	public static function pause() {
		self::guard();
		Settings::update( array( 'stop_requested' => 1 ) );
		wp_safe_redirect( admin_url( 'admin.php?page=velowp&paused=1' ) );
		exit;
	}

	public static function resume() {
		self::guard();
		Settings::update( array( 'stop_requested' => 0 ) );
		Cron::schedule_next();
		wp_safe_redirect( admin_url( 'admin.php?page=velowp&resumed=1' ) );
		exit;
	}

	public static function report() {
		self::guard();
		Report::download();
	}

	private static function guard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access denied.', 'velowp' ) );
		}
		check_admin_referer( 'velowp_action' );
	}
}
