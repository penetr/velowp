<?php

namespace VeloWP\Lifecycle;

use VeloWP\Core\Settings;
use VeloWP\Delivery\Htaccess;
use VeloWP\Storage\FilesRepository;
use VeloWP\Storage\PathMapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uninstall {
	public static function run() {
		$options = get_option( Settings::OPTION_KEY, Settings::defaults() );

		if ( ! empty( $options['uninstall_remove_generated'] ) ) {
			self::delete_generated_files();
		}
		if ( ! empty( $options['uninstall_remove_tables'] ) ) {
			self::drop_tables();
		}
		Htaccess::remove_rules();
		if ( ! empty( $options['uninstall_remove_options'] ) ) {
			delete_option( Settings::OPTION_KEY );
			delete_option( 'velowp_log' );
		}
	}

	private static function delete_generated_files() {
		$base = PathMapper::uploads_base();
		if ( ! $base ) {
			return;
		}
		$paths = FilesRepository::all_derivatives();
		foreach ( $paths as $rel ) {
			$abs = PathMapper::derivative_absolute_from_relative( $rel );
			if ( $abs && file_exists( $abs ) ) {
				@unlink( $abs );
			}
		}
	}

	private static function drop_tables() {
		global $wpdb;
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'velowp_files' );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'velowp_queue' );
	}
}
