<?php
/*
 * FooGallery Public class
 */

if ( ! class_exists( 'FooGallery_Public' ) ) {

	class FooGallery_Public {

		function __construct() {
			new FooGallery_Shortcodes();
		}

	}

}
