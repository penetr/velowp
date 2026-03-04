<?php

namespace VeloWP\Delivery;

use VeloWP\Core\Settings;
use VeloWP\Storage\PathMapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WpFilters {
	public static function register() {
		add_filter( 'the_content', array( __CLASS__, 'replace_in_content' ), 20 );
	}

	public static function replace_in_content( $content ) {
		$method = Settings::get( 'delivery_method', 'off' );
		if ( 'php_safe' !== $method && 'php_wide' !== $method ) {
			return $content;
		}
		$param = (string) Settings::get( 'bypass_query_param', 'no_webp' );
		if ( isset( $_GET[ $param ] ) && '1' === (string) sanitize_text_field( wp_unslash( $_GET[ $param ] ) ) ) {
			return $content;
		}
		if ( ! class_exists( 'DOMDocument' ) ) {
			return $content;
		}

		$dom = new \DOMDocument();
		@$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $content );
		$images = $dom->getElementsByTagName( 'img' );
		if ( 0 === $images->length ) {
			return $content;
		}

		$uploads = wp_upload_dir();
		$baseurl = trailingslashit( $uploads['baseurl'] );

		for ( $i = $images->length - 1; $i >= 0; --$i ) {
			$img = $images->item( $i );
			$src = $img->getAttribute( 'src' );
			if ( 0 !== strpos( $src, $baseurl ) ) {
				continue;
			}
			$rel   = ltrim( str_replace( $baseurl, '', $src ), '/' );
			$webp  = PathMapper::derivative_relative_from_original( $rel );
			$absp  = PathMapper::derivative_absolute_from_relative( $webp );
			if ( ! file_exists( $absp ) ) {
				continue;
			}

			$picture = $dom->createElement( 'picture' );
			$source  = $dom->createElement( 'source' );
			$source->setAttribute( 'type', 'image/webp' );
			$source->setAttribute( 'srcset', trailingslashit( $baseurl ) . $webp );
			$picture->appendChild( $source );
			$clone = $img->cloneNode( true );
			$picture->appendChild( $clone );
			$img->parentNode->replaceChild( $picture, $img );
		}

		$html = $dom->saveHTML();
		$html = preg_replace( '~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $html );
		$html = str_replace( '<?xml encoding="utf-8" ?>', '', $html );
		return $html;
	}
}
