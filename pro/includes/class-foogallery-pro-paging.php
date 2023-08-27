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
				'thumbnail' => 'https://lh3.googleusercontent.com/bip/APOwr82TUK2u7YlC6c2nONPFqhue2JOcXqoVFIF4HMhVNop1eSwVyum2ujEpe31AvDBUW_n4bDaUjrcemrZMkY1AaH25NSyDe4mQf5VbMudhT-qI14IQs2Kzz8xJwsS13Mce79L-ubXalcn8HHIo=w250-h200-p',
				'tags' => array( 'premium' ),
				'source' => 'bundled'
			);

			return $extensions_list;
		}
	}
}