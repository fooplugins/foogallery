<?php

/**
 * FooGallery PRO includes
 */
require_once( FOOGALLERY_PATH . 'pro/functions.php' );
require_once( FOOGALLERY_PATH . 'pro/class-foogallery-pro-presets.php' );

/**
 * FooGallery PRO Main Class
 */
if ( ! class_exists( 'FooGallery_Pro' ) ) {

	class FooGallery_Pro {

		function __construct() {
			new FooGallery_Pro_Hover_Presets();
		}
	}
}