<?php

namespace VeloWP\Storage;

use VeloWP\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PathMapper {
	public static function uploads_base() {
		$uploads = wp_upload_dir();
		return realpath( $uploads['basedir'] );
	}

	public static function to_relative( $absolute ) {
		$base = self::uploads_base();
		$real = realpath( $absolute );
		if ( ! $base || ! $real || 0 !== strpos( $real, $base ) ) {
			return '';
		}
		return ltrim( substr( $real, strlen( $base ) ), '/\\' );
	}

	public static function derivative_relative_from_original( $original_relative ) {
		$root = trim( (string) Settings::get( 'derivatives_root', 'media-derivative/webp' ), '/\\' );
		return $root . '/' . $original_relative . '.webp';
	}

	public static function derivative_absolute_from_relative( $derivative_relative ) {
		$base = self::uploads_base();
		if ( ! $base ) {
			return '';
		}
		return $base . DIRECTORY_SEPARATOR . str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, $derivative_relative );
	}
}
