<?php

if ( ! class_exists( 'FooGallery_Admin_Settings_Image_Optimization' ) ) {

	define( 'FOOGALLERY_SETTINGS_IMAGE_OPTIMIZATION_ENDPOINT', 'https://fooplugins.com/foogallery-image-optimization.html' );

	/**
	 * Class FooGallery_Admin_Settings_Image_Optimization
	 */
	class FooGallery_Admin_Settings_Image_Optimization {

		function __construct() {
			add_filter( 'foogallery_admin_settings_override', array($this, 'add_image_optimization_info' ) );

			// Ajax calls for pulling in Image Optimization Info
			add_action( 'wp_ajax_foogallery_get_image_optimization_info', array( $this, 'ajax_get_html' ) );
		}

		function add_image_optimization_info( $settings ) {

			$image_optimization_html = '<input id="foogallery_setting_image_optimization-nonce" type="hidden" value="' .
			                           esc_attr( wp_create_nonce( 'foogallery_get_image_optimization_info' ) ) .
			                           '" /><div id="foogallery_settings_image_optimization_container">'.
			                           __( 'please wait...', 'foogallery' ) . '</div>';

			$settings['settings'][] = array(
				'id'      => 'image_optimization',
				'title'   => __( 'Image Optimization', 'foogallery' ),
				'type'    => 'html',
				'desc'    => $image_optimization_html,
				'tab'     => 'thumb'
			);

			return $settings;
		}

		function ajax_get_html() {
			if ( check_admin_referer( 'foogallery_get_image_optimization_info' ) ) {
				echo wp_remote_retrieve_body( wp_remote_get( FOOGALLERY_SETTINGS_IMAGE_OPTIMIZATION_ENDPOINT ) );
			}
			die();
		}
	}
}