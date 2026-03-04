<?php
/**
 * Plugin Name: VeloWP
 * Description: Secure WebP generation and delivery for WordPress.
 * Version: 0.1.0
 * Author: VeloWP
 * Text Domain: velowp
 * Domain Path: /languages
 * Requires at least: 5.5
 * Requires PHP: 7.4
 *
 * @package VeloWP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VELOWP_VERSION', '0.1.0' );
define( 'VELOWP_FILE', __FILE__ );
define( 'VELOWP_DIR', plugin_dir_path( __FILE__ ) );
define( 'VELOWP_URL', plugin_dir_url( __FILE__ ) );

require_once VELOWP_DIR . 'src/Core/Autoloader.php';
\VeloWP\Core\Autoloader::register();

register_activation_hook( VELOWP_FILE, array( '\\VeloWP\\Lifecycle\\Activate', 'run' ) );
register_deactivation_hook( VELOWP_FILE, array( '\\VeloWP\\Lifecycle\\Deactivate', 'run' ) );

add_action(
	'plugins_loaded',
	static function() {
		load_plugin_textdomain( 'velowp', false, dirname( plugin_basename( VELOWP_FILE ) ) . '/languages' );
		\VeloWP\Core\Plugin::boot();
	}
);
