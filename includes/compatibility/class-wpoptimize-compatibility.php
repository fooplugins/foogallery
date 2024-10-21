<?php
/**
 * WP Optimize Compatibility Class
 *
 * @since   2.0.27
 *
 * @package foogallery
 */
namespace FooPlugins\FooGallery\Compatibility;

if ( ! class_exists( 'FooGallery_WPOptimize_Compatibility' ) ) {

	/**
	 * Class FooGallery_WPOptimize_Compatibility
	 */
	class FooGallery_WPOptimize_Compatibility {

		/**
		 * FooGallery_WPOptimize_Compatibility constructor.
		 */
		public function __construct() {
			// Add 'no-lazy' class onto all image tags generated by FooGallery.
			add_filter( 'foogallery_attachment_html_image_attributes', array( $this, 'add_lazy_image_attributes' ), 10, 3 );
		}

		/**
		 * Adds skip-lazy class to all img tags so that they get ignored by lazy loading plugins
		 *
		 * @param array                $attr                  the attributes.
		 * @param array                $args                  extra arguments.
		 * @param FooGalleryAttachment $foogallery_attachment the current attachment.
		 *
		 * @return mixed
		 */
		public function add_lazy_image_attributes( $attr, $args, $foogallery_attachment ) {
			if ( class_exists( 'WP_Optimize_Lazy_Load' ) ) {
				if ( array_key_exists( 'class', $attr ) ) {
					$attr['class'] .= ' no-lazy';
				} else {
					$attr['class'] = 'no-lazy';
				}
			}

			return $attr;
		}
	}
}
