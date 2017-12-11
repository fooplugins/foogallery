<?php

/**
 * FooGallery PRO includes
 */
require_once( FOOGALLERY_PATH . 'pro/functions.php' );
require_once( FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-presets.php' );
require_once( FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-loaded-effects.php' );
require_once( FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-paging.php' );
require_once( FOOGALLERY_PATH . 'pro/extensions/default-templates/class-foogallery-pro-default-templates.php' );
require_once( FOOGALLERY_PATH . 'pro/includes/class-foogallery-pro-gallery-override.php' );

/**
 * FooGallery PRO Main Class
 */
if ( ! class_exists( 'FooGallery_Pro' ) ) {

	class FooGallery_Pro {

		function __construct() {
			new FooGallery_Pro_Hover_Presets();
			new FooGallery_Pro_Loaded_Effects();
			new FooGallery_Pro_Paging();
			new FooGallery_Pro_Default_Templates();
			new FooGallery_Pro_Gallery_Shortcode_Override();
		}
	}
}