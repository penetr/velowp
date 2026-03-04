<?php

namespace VeloWP\Converter;

use VeloWP\Core\Checker;
use VeloWP\Core\Settings;
use VeloWP\Storage\DerivativesStore;
use VeloWP\Storage\FilesRepository;
use VeloWP\Storage\PathMapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Processor {
	public static function convert_relative( $original_relative ) {
		if ( ! Checker::can_convert() ) {
			return new \WP_Error( 'CONVERT_DISABLED', __( 'Conversion is not available in current environment.', 'velowp' ) );
		}

		$base          = PathMapper::uploads_base();
		$original_path = $base . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $original_relative );
		if ( ! file_exists( $original_path ) ) {
			return new \WP_Error( 'FILE_NOT_FOUND', __( 'Original file does not exist.', 'velowp' ) );
		}

		$mime = wp_check_filetype_and_ext( $original_path, basename( $original_path ) );
		if ( ! in_array( $mime['type'], array( 'image/jpeg', 'image/png' ), true ) ) {
			return new \WP_Error( 'SKIPPED_MIME', __( 'Unsupported mime type.', 'velowp' ) );
		}

		$derivative_relative = PathMapper::derivative_relative_from_original( $original_relative );
		$derivative_path     = PathMapper::derivative_absolute_from_relative( $derivative_relative );
		$mtime_original      = filemtime( $original_path );

		if ( file_exists( $derivative_path ) && DerivativesStore::is_valid_webp( $derivative_path ) && filemtime( $derivative_path ) >= $mtime_original ) {
			return true;
		}

		DerivativesStore::ensure_dir_for( $derivative_path );
		$tmp = $derivative_path . '.tmp.' . wp_generate_password( 6, false );
		$ok  = self::write_webp( $original_path, $tmp, $mime['type'] );
		if ( ! $ok || ! DerivativesStore::is_valid_webp( $tmp ) ) {
			DerivativesStore::safe_delete( $tmp );
			return new \WP_Error( 'IMG_ENCODE', __( 'Failed to encode WebP.', 'velowp' ) );
		}

		if ( ! @rename( $tmp, $derivative_path ) ) {
			if ( ! @copy( $tmp, $derivative_path ) ) {
				DerivativesStore::safe_delete( $tmp );
				return new \WP_Error( 'FS_WRITE', __( 'Failed to move temporary derivative.', 'velowp' ) );
			}
			DerivativesStore::safe_delete( $tmp );
		}

		FilesRepository::upsert( $original_relative, $derivative_relative, $mtime_original );
		return true;
	}

	private static function write_webp( $source, $target, $mime ) {
		if ( class_exists( 'Imagick' ) ) {
			$ok = self::write_with_imagick( $source, $target );
			if ( $ok ) {
				return true;
			}
		}
		return self::write_with_gd( $source, $target, $mime );
	}

	private static function write_with_imagick( $source, $target ) {
		try {
			$img = new \Imagick( $source );
			$img->setImageFormat( 'WEBP' );
			$img->setImageCompressionQuality( (int) Settings::get( 'jpeg_quality', 75 ) );
			return (bool) $img->writeImage( $target );
		} catch ( \Exception $e ) {
			return false;
		}
	}

	private static function write_with_gd( $source, $target, $mime ) {
		if ( ! function_exists( 'imagewebp' ) ) {
			return false;
		}
		$image = 'image/jpeg' === $mime ? @imagecreatefromjpeg( $source ) : @imagecreatefrompng( $source );
		if ( ! $image ) {
			return false;
		}
		$quality = (int) Settings::get( 'jpeg_quality', 75 );
		$ok      = imagewebp( $image, $target, $quality );
		imagedestroy( $image );
		return (bool) $ok;
	}
}
