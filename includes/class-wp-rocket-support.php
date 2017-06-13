<?php
/**
 * Adds support for WPRocket in FooGallery
 * Created by brad.
 * Date: 11/06/2017
 *
 * @since 1.3.3
 */
if ( ! class_exists( 'FooGallery_WPRocket_Support' ) ) {

	class FooGallery_WPRocket_Support {

		function __construct() {
			add_filter( 'foogallery_attachment_html_image_attributes', array( $this, 'alter_image_attributes' ), 999, 3 );
			add_filter( 'foogallery_attachment_html_link_attributes', array( $this, 'alter_link_attributes' ), 999, 3 );
		}

		/**
		 * Alters the image URL's to use WP Rocket's get_rocket_cdn_url function
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

			if ( isset( $attr[ 'src' ] ) ) {
				$attr[ 'src' ] = $this->replace_url( $attr[ 'src' ] );
			}

			return $attr;
		}

		/**
		 * Alters the image URL's to use WP Rocket's get_rocket_cdn_url function
		 *
		 * @uses "foogallery_attachment_html_link_attributes" filter
		 *
		 * @param                             $attr
		 * @param                             $args
		 * @param object|FooGalleryAttachment $object
		 *
		 * @return array
		 */
		function alter_link_attributes( $attr, $args, $object ) {

			if ( isset( $attr[ 'href' ] ) ) {
				$attr[ 'href' ] = $this->replace_url( $attr[ 'href' ] );
			}

			return $attr;
		}

		function replace_url( $url ) {
			if ( function_exists( 'get_rocket_cdn_url' ) ) {
				return get_rocket_cdn_url( $url );
			}

			return $url;
		}
	}
}
