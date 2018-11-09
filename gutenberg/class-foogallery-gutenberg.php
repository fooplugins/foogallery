<?php
/**
 * FooGallery Gutenberg Functionality
 * Date: 28/10/2018
 */

require_once( FOOGALLERY_PATH . 'gutenberg/class-foogallery-blocks.php' );
require_once( FOOGALLERY_PATH . 'gutenberg/class-foogallery-rest-routes.php' );

if ( ! class_exists( 'FooGallery_Gutenberg' ) ) {

	class FooGallery_Gutenberg {

		function __construct() {
			new FooGallery_Blocks();
			new FooGallery_Rest_Routes();
		}
	}
}