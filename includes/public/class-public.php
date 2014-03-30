<?php
/*
 * FooGallery Public class
 */

if (!class_exists('FooGallery_Public')) {

	class FooGallery_Public {

		function __construct() {
			require_once( FOOGALLERY_PATH . 'includes/public/class-template-engine.php' );
			require_once( FOOGALLERY_PATH . 'includes/public/class-shortcodes.php' );

			//include built-in FooGallery templates
			require_once( FOOGALLERY_PATH . 'templates/default/default.php' );

			new FooGallery_Shortcodes();
		}

	}

}
