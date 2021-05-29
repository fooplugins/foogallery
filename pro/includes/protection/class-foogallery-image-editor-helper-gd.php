<?php

/**
 * class for the GD image editor helper
 */
if ( ! class_exists( 'FooGallery_Image_Editor_Helper_GD' ) ) {

	class FooGallery_Image_Editor_Helper_GD extends FooGallery_Image_Editor_Helper_Base {

		function merge_images( $background_image, $foreground_image, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct ) {
			//create a new true color image
			$new = imagecreatetruecolor( $src_w, $src_h );

			// copy relevant section from background image to the new resource
			imagecopy( $new, $background_image, 0, 0, $dst_x, $dst_y, $src_w, $src_h );

			// copy relevant section from foreground image to the new resource
			imagecopy( $new, $foreground_image, 0, 0, $src_x, $src_y, $src_w, $src_h );

			// insert $new resource onto the background image
			imagecopymerge( $background_image, $new, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct );

			//destroy the new image and free up the memory
			imagedestroy( $new );
		}

		function cleanup( &$image ) {
			imagedestroy( $image );
			$image = null;
		}

		function get_image_base64( $image ) {
			ob_start();
			imagepng( $image );
			$image_output = ob_get_clean();
			return base64_encode( $image_output );
		}
	}
}