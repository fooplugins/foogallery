<?php

if ( ! class_exists( 'FooGallery_Admin_Settings_Image_Optimization' ) ) {

	define( 'FOOGALLERY_SETTINGS_IMAGE_OPTIMIZATION_ENDPOINT', 'https://fooplugins.com/foogallery-image-optimization.html' );

	/**
	 * Class FooGallery_Admin_Settings_Image_Optimization
	 */
	class FooGallery_Admin_Settings_Image_Optimization {

		function __construct() {
			add_filter( 'foogallery_admin_settings_override', array($this, 'add_image_optimization_info' ) );
		}

		function add_image_optimization_info( $settings ) {

			$image_optimization_html = sprintf( __('Try the %s, an easy-to-use, lightweight WordPress plugin that optimizes images & PDFs.', 'foogallery'),
				'<a href="https://shortpixel.com/homepage/affiliate/foowww" target="_blank">' . __('ShortPixel Image Optimizer' , 'foogallery') . '</a>' );

			$settings['settings'][] = array(
				'id'      => 'image_optimization',
				'title'   => __( 'Image Optimization', 'foogallery' ),
				'type'    => 'html',
				'desc'    => $image_optimization_html,
				'tab'     => 'thumb'
			);

			return $settings;
		}
	}
}