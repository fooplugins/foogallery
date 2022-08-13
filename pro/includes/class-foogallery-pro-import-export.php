<?php
/**
 * FooGallery WhiteLabelling Class that registers the extension.
 */

if ( ! class_exists('FooGallery_Pro_Import_Export') ) {

	class FooGallery_Pro_Import_Export {

		/**
		 * FooGallery_Pro_Import_Export constructor.
		 */
		function __construct() {
			add_filter( 'foogallery_available_extensions', array( $this, 'register_extension' ) );
		}

		/**
		 * Register the White Labeling extension
		 *
		 * @param $extensions_list
		 *
		 * @return array
		 */
		function register_extension( $extensions_list ) {
			$extensions_list[] = array(
				'slug' => 'foogallery-import-export',
				'class' => 'FooGallery_Pro_Import_Export_Extension',
				'categories' => array( 'Premium' ),
				'title' => __( 'Import / Export', 'foogallery' ),
				'description' => __( 'Import/Export Galleries to and from another WordPress install', 'foogallery' ),
				'author' => 'FooPlugins',
				'author_url' => 'https://fooplugins.com',
				'thumbnail' => 'https://foogallery.s3.amazonaws.com/extensions/white_labelling.png',
				'tags' => array( 'premium' ),
				'source' => 'bundled'
			);

			return $extensions_list;
		}
	}
}