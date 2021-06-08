<?php
/**
 * Base class for the image editor helper
 */

if ( ! class_exists( 'FooGallery_Image_Editor_Helper_Base' ) ) {

	abstract class FooGallery_Image_Editor_Helper_Base {
		/**
		 * Merge 2 images together taking into account transparency of the foreground image
		 *
		 * @param $background_image
		 * @param $foreground_image
		 * @param $dst_x
		 * @param $dst_y
		 * @param $src_x
		 * @param $src_y
		 * @param $src_w
		 * @param $src_h
		 * @param $pct
		 *
		 * @return mixed
		 */
		abstract function merge_images( $background_image, $foreground_image, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct );

		/**
		 * Release any memory for an image
		 *
		 * @param $image
		 *
		 * @return mixed
		 */
		abstract function cleanup( &$image );

		/**
		 * Return the base64 encoded string representation of the image
		 *
		 * @param $image
		 *
		 * @return mixed
		 */
		abstract function get_image_base64( $image );
	}
}