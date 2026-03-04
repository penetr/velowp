<?php

namespace VeloWP\Delivery;

use VeloWP\Core\Checker;
use VeloWP\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Htaccess {
	const MARKER = 'VeloWP';

	public static function maybe_apply() {
		if ( 'apache' !== Settings::get( 'delivery_method', 'off' ) ) {
			self::remove_rules();
			return;
		}
		$report = Checker::environment_report();
		if ( ! $report['htaccess_writable'] ) {
			return;
		}
		self::insert_rules();
	}

	public static function insert_rules() {
		$uploads = wp_upload_dir();
		$file    = trailingslashit( $uploads['basedir'] ) . '.htaccess';
		$param   = preg_replace( '/[^a-zA-Z0-9_]/', '', (string) Settings::get( 'bypass_query_param', 'no_webp' ) );
		$rules   = array(
			'<IfModule mod_mime.c>',
			'AddType image/webp .webp',
			'</IfModule>',
			'<IfModule mod_rewrite.c>',
			'RewriteEngine On',
			'RewriteCond %{QUERY_STRING} !(^|&)' . $param . '=1(&|$)',
			'RewriteCond %{HTTP_ACCEPT} image/webp',
			'RewriteCond %{REQUEST_FILENAME}.webp -f',
			'RewriteRule (.+) $1.webp [T=image/webp,E=accept:1]',
			'</IfModule>',
			'<IfModule mod_headers.c>',
			'Header append Vary Accept env=REDIRECT_accept',
			'</IfModule>',
		);
		insert_with_markers( $file, self::MARKER, $rules );
	}

	public static function remove_rules() {
		$uploads = wp_upload_dir();
		$file    = trailingslashit( $uploads['basedir'] ) . '.htaccess';
		if ( file_exists( $file ) ) {
			insert_with_markers( $file, self::MARKER, array() );
		}
	}
}
