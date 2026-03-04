<?php

namespace VeloWP\Queue;

use VeloWP\Storage\PathMapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Scanner {
	public static function scan_uploads( $limit = 200 ) {
		$base = PathMapper::uploads_base();
		if ( ! $base ) {
			return 0;
		}

		$it    = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $base, \FilesystemIterator::SKIP_DOTS ) );
		$count = 0;
		foreach ( $it as $file ) {
			if ( $count >= $limit ) {
				break;
			}
			if ( ! $file->isFile() ) {
				continue;
			}
			$ext = strtolower( $file->getExtension() );
			if ( ! in_array( $ext, array( 'jpg', 'jpeg', 'png' ), true ) ) {
				continue;
			}
			$rel = PathMapper::to_relative( $file->getPathname() );
			if ( '' === $rel ) {
				continue;
			}
			QueueRepository::enqueue_convert( $rel );
			++$count;
		}
		return $count;
	}
}
