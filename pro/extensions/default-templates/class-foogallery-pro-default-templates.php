<?php
/**
 * FooGallery Pro Default Templates Class
 */
if ( ! class_exists( 'FooGallery_Pro_Default_Templates' ) ) {

	define( 'FOOGALLERY_PRO_DEFAULT_TEMPLATES_URL', plugin_dir_url( __FILE__ ) );
	define( 'FOOGALLERY_PRO_DEFAULT_TEMPLATES_PATH', plugin_dir_path( __FILE__ ) );

	define( 'FOOGALLERY_PRO_DEFAULT_TEMPLATES_SHARED_URL', FOOGALLERY_PRO_DEFAULT_TEMPLATES_URL . 'shared/' );
	define( 'FOOGALLERY_PRO_DEFAULT_TEMPLATES_SHARED_PATH', FOOGALLERY_PRO_DEFAULT_TEMPLATES_PATH . 'shared/' );

	require_once( FOOGALLERY_PRO_DEFAULT_TEMPLATES_PATH . 'polaroid/class-polaroid-gallery-template.php' );

	class FooGallery_Pro_Default_Templates {

		function __construct() {
			new FooGallery_Polaroid_Gallery_Template();

			add_filter( 'foogallery_core_gallery_style', array( $this, 'pro_core_gallery_style' ) );
			add_filter( 'foogallery_core_gallery_script', array( $this, 'pro_core_gallery_script' ) );
		}

		/***
		 * Return the path to the PRO core gallery stylesheet
		 */
		function pro_core_gallery_style( $url ){
			$filename = foogallery_get_setting( 'enable_debugging', false ) ? '' : '.min';
			return FOOGALLERY_PRO_DEFAULT_TEMPLATES_SHARED_URL . 'css/foogallery' . $filename . '.css';
		}

		/***
		 * Return the path to the PRO core gallery script
		 */
		function pro_core_gallery_script( $url ){
			$filename = foogallery_get_setting( 'enable_debugging', false ) ? '' : '.min';
			return FOOGALLERY_DEFAULT_TEMPLATES_EXTENSION_SHARED_URL . 'js/foogallery' . $filename . '.js';
		}
	}
}
