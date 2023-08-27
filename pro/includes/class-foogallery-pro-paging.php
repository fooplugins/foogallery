<?php
/**
 * FooGallery Paging Class that registers the extension.
 */

if ( ! class_exists('FooGallery_Pro_Paging') ) {

	class FooGallery_Pro_Paging {

		/**
		 * FooGallery_Pro_Paging constructor.
		 */
		function __construct() {
			add_filter( 'foogallery_available_extensions', array( $this, 'register_extension' ) );
		}

		/**
		 * Register the Pagination extension
		 *
		 * @param $extensions_list
		 *
		 * @return array
		 */
		function register_extension( $extensions_list ) {
			$extensions_list[] = array(
				'slug' => 'foogallery-paging',
				'class' => 'FooGallery_Pro_Paging_Extension',
				'categories' => array( 'Premium' ),
				'title' => __( 'Pagination', 'foogallery' ),
				'description' => __( 'Add advanced pagination options: numbered, infinite scroll, and load more.', 'foogallery' ),
				'author' => 'FooPlugins',
				'author_url' => 'https://fooplugins.com',
				'thumbnail' => 'https://i.pinimg.com/474x/62/f9/e0/62f9e07f0df68e37a5a1d3e9e77b8c83.jpg',
				'tags' => array( 'premium' ),
				'source' => 'bundled'
			);

			return $extensions_list;
		}
	}
}
