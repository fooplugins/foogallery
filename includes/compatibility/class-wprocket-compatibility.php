<?php
/**
 * Adds support for WPRocket in FooGallery
 * Created by brad.
 * Date: 11/06/2017
 *
 * @since 1.3.3
 */
if ( ! class_exists( 'FooGallery_WPRocket_Compatibility' ) ) {

	class FooGallery_WPRocket_Compatibility {

		function __construct() {
			add_action( 'plugins_loaded', array( $this, 'init_wprocket' ) );
		}

		/**
		 * Init hooks for WPRocket
		 */
		function init_wprocket() {
			//check that WPRocket is activated
			if ( defined( 'WP_ROCKET_VERSION' ) ) {
				//add_filter( 'foogallery_process_image_url', array( $this, 'force_images_to_use_rocket_cdn' ), 999 );
				add_filter( 'foogallery_attachment_html_image_attributes', array( $this, 'alter_image_attributes' ), 10, 3 );
				add_filter( 'rocket_excluded_inline_js_content', array( $this, 'ensure_foogallery_items_excluded' ) );
			}
		}

		function ensure_foogallery_items_excluded( $excluded_inline ) {
			$excluded_inline[] = 'window["foogallery-gallery-';
			return $excluded_inline;
		}

		/**
		 *
		 * @param $url
		 *
		 * @return mixed
		 */
		function force_images_to_use_rocket_cdn( $url ) {
			if ( function_exists( 'get_rocket_cdn_url' ) ) {
				return get_rocket_cdn_url( $url );
			}

			return $url;
		}

		/**
		 * Ensure that WPRocket lazyloading does not interfere with FooGallery's lazy loading
		 *
		 * @uses "foogallery_attachment_html_image_attributes" filter
		 *
		 * @param                             $attr
		 * @param                             $args
		 * @param object|FooGalleryAttachment $object
		 *
		 * @return array
		 */
		function alter_image_attributes( $attr, $args, $object ) {
			$attr['data-no-lazy'] = '1';
			return $attr;
		}
	}
}
