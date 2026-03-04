<?php

namespace VeloWP\Storage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DerivativesStore {
	public static function ensure_dir_for( $absolute_path ) {
		$dir = dirname( $absolute_path );
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}
	}

	public static function is_valid_webp( $path ) {
		if ( ! file_exists( $path ) || 0 === filesize( $path ) ) {
			return false;
		}
		$mime = wp_check_filetype_and_ext( $path, basename( $path ) );
		return isset( $mime['type'] ) && 'image/webp' === $mime['type'];
	}

	public static function safe_delete( $path ) {
		if ( file_exists( $path ) && is_writable( $path ) ) {
			@unlink( $path );
		}
	}
}
