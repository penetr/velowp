<?php

namespace VeloWP\Diagnostics;

use VeloWP\Core\Checker;
use VeloWP\Core\Settings;
use VeloWP\Queue\QueueRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Report {
	public static function download() {
		$report = array(
			'generated_at' => gmdate( 'c' ),
			'environment'  => Checker::environment_report(),
			'settings'     => Settings::all(),
			'queue_counts' => QueueRepository::counts(),
			'errors'       => Logger::latest( 20 ),
		);

		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="velowp-report.json"' );
		echo wp_json_encode( $report, JSON_PRETTY_PRINT );
		exit;
	}
}
