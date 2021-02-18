<?php
/**
 * Class that managers all thumbnail generation within FooGallery
 */
if ( ! class_exists( 'FooGallery_Thumb_Manager' ) ) {

	class FooGallery_Thumb_Manager {

		function __construct() {
			add_action( 'plugins_loaded', array( $this, 'init_active_engine' ) );
		}

		/**
		 * Make sure the active thumb engine initializes
		 */
		function init_active_engine() {
			$engine = foogallery_thumb_active_engine();
			$engine->init();
		}
	}
}