<?php

namespace VeloWP\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {
	const OPTION_KEY = 'velowp_options';

	public static function defaults() {
		return array(
			'delivery_method'             => 'off',
			'derivatives_root'            => 'media-derivative/webp',
			'jpeg_quality'                => 75,
			'png_mode'                    => 'lossless',
			'scan_chunk_size'             => 200,
			'batch_size'                  => 10,
			'max_attempts'                => 3,
			'lock_ttl'                    => 60,
			'processing_ttl'              => 300,
			'bypass_query_param'          => 'no_webp',
			'auto_convert_media'          => 1,
			'php_wide_enabled'            => 0,
			'deactivate_remove_htaccess'  => 1,
			'uninstall_remove_tables'     => 0,
			'uninstall_remove_generated'  => 0,
			'uninstall_remove_options'    => 1,
			'log_level'                   => 'error',
			'stop_requested'              => 0,
		);
	}

	public static function register() {
		if ( false === get_option( self::OPTION_KEY ) ) {
			add_option( self::OPTION_KEY, self::defaults() );
		}
	}

	public static function all() {
		return wp_parse_args( get_option( self::OPTION_KEY, array() ), self::defaults() );
	}

	public static function get( $key, $default = null ) {
		$options = self::all();
		return isset( $options[ $key ] ) ? $options[ $key ] : $default;
	}

	public static function update( array $options ) {
		$current = self::all();
		update_option( self::OPTION_KEY, wp_parse_args( $options, $current ) );
	}
}
