<?php
/*
Plugin Name: WordPress Importer Exclude CDATA section
Plugin URI: https://github.com/ko31/wordpress-importer-exclude-cdata
Description: Addon WordPress Importer
Author: Gosign
Author URI: https://go-sign.info
Version: 0.1
Text Domain: wordpress-importer-exclude-cdata
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
	return;
}

class WPImporterExcludeCdata {

	function __construct() {
		add_filter( 'wp_import_post_data_processed', [ $this, 'wp_import_post_data_processed' ], 10, 2 );
	}

	function wp_import_post_data_processed( $postdata, $post ) {

		$post_types = apply_filters( 'wpiec_post_types', [ 'post' ] );

		if ( ! in_array( $postdata['post_type'], $post_types ) ) {
			return $postdata;
		}

		$exclude_fields = apply_filters( 'wpiec_exclude_fields', [ 'post_content', 'post_excerpt' ] );

		foreach ( $exclude_fields as $field ) {
			if ( ! isset( $postdata[ $field ] ) ) {
				continue;
			}

			$data = $postdata[ $field ];

			// Check format
			$data = trim( $data );
			if ( ! ( substr( $data, 0, 9 ) === '<![CDATA[' && substr( $data, - 3 ) === ']]>' ) ) {
				return $postdata;
			}

			// Enclose in a dummy element for XML parsing
			$xml = "<data>$data</data>";

			$new_data = simplexml_load_string( $xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS );
			if ( $new_data === false ) {
				continue;
			}
			$postdata[ $field ] = $new_data;
		}

		return $postdata;
	}

}

new WPImporterExcludeCdata();
