<?php

namespace VeloWP\Queue;

use VeloWP\Converter\Processor;
use VeloWP\Core\Cron;
use VeloWP\Core\Lock;
use VeloWP\Core\Settings;
use VeloWP\Diagnostics\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Worker {
	public static function register() {
		add_action( Cron::HOOK, array( __CLASS__, 'tick' ) );
		add_action( 'wp_generate_attachment_metadata', array( __CLASS__, 'enqueue_attachment_sizes' ), 10, 2 );
	}

	public static function tick() {
		if ( Settings::get( 'stop_requested', 0 ) ) {
			return;
		}

		$ttl = (int) Settings::get( 'lock_ttl', 60 );
		if ( ! Lock::acquire( $ttl ) ) {
			return;
		}

		QueueRepository::reset_stale_processing( (int) Settings::get( 'processing_ttl', 300 ) );
		$tasks = QueueRepository::take_batch( (int) Settings::get( 'batch_size', 10 ) );

		foreach ( $tasks as $task ) {
			if ( 'convert' !== $task['task_type'] || empty( $task['original_path'] ) ) {
				QueueRepository::mark_skipped( $task['id'], 'Unsupported task type' );
				continue;
			}
			$result = Processor::convert_relative( $task['original_path'] );
			if ( true === $result ) {
				QueueRepository::mark_done( $task['id'] );
				continue;
			}
			if ( is_wp_error( $result ) && 'SKIPPED_MIME' === $result->get_error_code() ) {
				QueueRepository::mark_skipped( $task['id'], $result->get_error_message() );
				continue;
			}
			$error = is_wp_error( $result ) ? $result->get_error_code() . ': ' . $result->get_error_message() : 'Unknown conversion error';
			QueueRepository::mark_error( $task['id'], $error );
			Logger::error( 'CONVERT', $task['original_path'], $error );
		}

		Lock::release();

		$counts = QueueRepository::counts();
		if ( ! empty( $counts['pending'] ) || ! empty( $counts['processing'] ) ) {
			Cron::schedule_next();
		}
	}

	public static function enqueue_attachment_sizes( $metadata, $attachment_id ) {
		if ( ! Settings::get( 'auto_convert_media', 1 ) ) {
			return $metadata;
		}
		$file = get_attached_file( $attachment_id );
		if ( ! $file ) {
			return $metadata;
		}
		$rel = \VeloWP\Storage\PathMapper::to_relative( $file );
		if ( $rel ) {
			QueueRepository::enqueue_convert( $rel );
		}
		return $metadata;
	}
}
