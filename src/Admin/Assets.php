<?php

namespace VeloWP\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {
	public static function register() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	public static function enqueue( $hook ) {
		if ( 'toplevel_page_velowp' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'velowp-admin', VELOWP_URL . 'assets/css/admin.css', array(), VELOWP_VERSION );
		wp_enqueue_script( 'velowp-admin', VELOWP_URL . 'assets/js/admin.js', array(), VELOWP_VERSION, true );
	}
}
