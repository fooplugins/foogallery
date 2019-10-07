<?php
/**
 * FooGallery Compatibility Class for including all 3rd party plugin compatibilities
 * Created by bradvin
 * Date: 23/03/2018
 */

require_once( FOOGALLERY_PATH . 'includes/compatibility/class-autoptimize-compatibility.php' );
require_once( FOOGALLERY_PATH . 'includes/compatibility/class-foobox-compatibility.php' );
require_once( FOOGALLERY_PATH . 'includes/compatibility/class-polylang-compatibility.php' );
require_once( FOOGALLERY_PATH . 'includes/compatibility/class-responsive-lightbox-dfactory-compatibility.php' );
require_once( FOOGALLERY_PATH . 'includes/compatibility/class-wprocket-compatibility.php' );
require_once( FOOGALLERY_PATH . 'includes/compatibility/class-foovideo-compatibility.php' );
require_once( FOOGALLERY_PATH . 'includes/compatibility/class-elasticpress-compatibility.php' );
require_once( FOOGALLERY_PATH . 'includes/compatibility/class-elementor-compatibility.php' );

if ( ! class_exists( 'FooGallery_Compatibility' ) ) {
	class FooGallery_Compatibility {
		function __construct() {
			new FooGallery_Autoptimize_Compatibility();
			new FooGallery_FooBox_Compatibility();
			new FooGallery_Polylang_Compatibility();
			new FooGallery_Responsive_Lightbox_dFactory_Compatibility();
			new FooGallery_FooVideo_Compatibility();
			new FooGallery_ElasticPress_Compatibility();
			new FooGallery_Elementor_Compatibility();
			//new FooGallery_WPRocket_Compatibility(); this has not been fully tested
		}
	}
}