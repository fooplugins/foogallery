<?php
/**
 * Class used to force HTTPS for all FooGallery assets
 * @since 1.6.18
 */
if ( ! class_exists( 'FooGallery_ForceHttps' ) ) {

	class FooGallery_ForceHttps {

		function __construct() {
			add_filter( 'foogallery_admin_settings_override', array($this, 'add_settings' ) );
			add_action( 'plugins_loaded', array( $this, 'enable_force' ) );
		}

		/**
		 * Enables the force checks based on the setting. This is done here so that the setting is only checked once per page load, rather than multiple times
		 *
		 */
		function enable_force() {
			if ( 'on' === foogallery_get_setting( 'force_https' ) ) {
				add_filter( 'foogallery_process_image_url', array( $this, 'force_images_to_https' ), 99 );
				add_filter( 'foogallery_enqueue_style_src', array( $this, 'force_css_to_https'), 99, 2 );
				add_filter( 'foogallery_core_gallery_script', array( $this, 'force_js_to_https'), 99 );
			}
		}

		/**
		 * Add settings for Force Https
		 * @param $settings
		 *
		 * @return array
		 */
		function add_settings( $settings ) {
			$settings['settings'][] = array(
				'id'      => 'force_https',
				'title'   => __( 'Force HTTPS', 'foogallery' ),
				'desc'    => __( 'Force all assets (thumbnails, javascript, css) to load over the HTTPS protocol. This can help overcome some issues when moving your site across to HTTPS and you get mixed content errors.', 'foogallery' ),
				'type'    => 'checkbox',
				'tab'     => 'advanced'
			);
			return $settings;
		}

		/**
		 * Helper function that does the replacement and forces Https
		 *
		 * @param $url
		 *
		 * @return string
		 */
		function force_https( $url ) {
			return str_replace( 'http://', 'https://', $url );
		}

		/**
		 * Force an image URL to Https
		 *
		 * @param $url
		 *
		 * @return string
		 */
		function force_images_to_https( $url ) {
			return $this->force_https( $url );
		}

		/**
		 * Force any CSS loaded using the foogallery_enqueue_style function to Https
		 *
		 * @param $src
		 * @param $handle
		 *
		 * @return string
		 */
		function force_css_to_https( $src, $handle ) {
			$src = $this->force_https( $src );
			return $src;
		}

		/**
		 * Force the js loaded for the default gallery templates to Https
		 *
		 * @param $src
		 *
		 * @return string
		 */
		function force_js_to_https( $src ) {
			$src = $this->force_https( $src );
			return $src;
		}
	}
}