<?php

namespace VeloWP\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Multisite {
	public static function is_network() {
		return is_multisite();
	}
}
