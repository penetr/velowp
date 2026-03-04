<?php
/**
 * Uninstall handler.
 *
 * @package VeloWP
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once __DIR__ . '/src/Core/Autoloader.php';
\VeloWP\Core\Autoloader::register();
\VeloWP\Lifecycle\Uninstall::run();
