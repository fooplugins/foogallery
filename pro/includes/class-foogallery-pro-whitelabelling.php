<?php
/**
 * FooGallery WhiteLabelling Class that registers the extension.
 */

if ( ! class_exists('FooGallery_Pro_Whitelabelling') ) {

	class FooGallery_Pro_Whitelabelling {

		/**
		 * FooGallery_Pro_Whitelabelling constructor.
		 */
		function __construct() {
			add_filter( 'foogallery_available_extensions', array( $this, 'register_extension' ) );
		}

		/**
		 * Register the White Labelling extension
		 *
		 * @param $extensions_list
		 *
		 * @return array
		 */
		function register_extension( $extensions_list ) {
			$extensions_list[] = array(
				'slug' => 'foogallery-whitelabelling',
				'class' => 'FooGallery_Pro_Whitelabelling_Extension',
				'categories' => array( 'Premium' ),
				'title' => __( 'White Labelling', 'foogallery' ),
				'description' => __( 'Rebrand FooGallery to whatever you like for your clients. Ideal for freelancers and agencies.', 'foogallery' ),
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