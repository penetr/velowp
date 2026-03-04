<?php

namespace VeloWP\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Checker {
	public static function environment_report() {
		$uploads = wp_upload_dir();
		$base    = isset( $uploads['basedir'] ) ? $uploads['basedir'] : '';
		$store   = trailingslashit( $base ) . Settings::get( 'derivatives_root', 'media-derivative/webp' );

		return array(
			'php_ok'              => version_compare( PHP_VERSION, '7.4', '>=' ),
			'wp_ok'               => version_compare( get_bloginfo( 'version' ), '5.5', '>=' ),
			'gd_webp'             => function_exists( 'imagewebp' ),
			'imagick_webp'        => class_exists( 'Imagick' ) && self::imagick_supports_webp(),
			'exif'                => function_exists( 'exif_read_data' ),
			'dom'                 => class_exists( 'DOMDocument' ),
			'uploads_basedir'     => $base,
			'derivatives_writable'=> self::writable_dir( $store ),
			'web_server'          => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
			'htaccess_writable'   => self::htaccess_writable( $base ),
		);
	}

	public static function can_convert() {
		$report = self::environment_report();
		return ( $report['gd_webp'] || $report['imagick_webp'] ) && $report['derivatives_writable'];
	}

	private static function imagick_supports_webp() {
		try {
			$imagick = new \Imagick();
			return in_array( 'WEBP', $imagick->queryFormats(), true );
		} catch ( \Exception $e ) {
			return false;
		}
	}

	private static function writable_dir( $path ) {
		if ( ! file_exists( $path ) ) {
			wp_mkdir_p( $path );
		}
		$test_file = trailingslashit( $path ) . '.__velowp_test';
		$written   = @file_put_contents( $test_file, '1' );
		if ( false === $written ) {
			return false;
		}
		@unlink( $test_file );
		return true;
	}

	private static function htaccess_writable( $base ) {
		$htaccess = trailingslashit( $base ) . '.htaccess';
		if ( file_exists( $htaccess ) ) {
			return is_writable( $htaccess );
		}
		return is_writable( $base );
	}
}
