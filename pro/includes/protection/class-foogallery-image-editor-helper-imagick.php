<?php

/**
 * class for the Imagick image editor helper
 */
if ( ! class_exists( 'FooGallery_Image_Editor_Helper_Imagick' ) ) {

	class FooGallery_Image_Editor_Helper_Imagick extends FooGallery_Image_Editor_Helper_Base {

		function merge_images( $background_image, $foreground_image, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct ) {
			$foreground_image_to_use = $foreground_image;

			$destroy = false;

			if ( $pct < 100 ) {
				$foreground_image_to_use = clone $foreground_image;

				$destroy = true;

				// alpha channel exists?
				if ( $foreground_image_to_use->getImageAlphaChannel() > 0 ) {
					$foreground_image_to_use->evaluateImage( Imagick::EVALUATE_MULTIPLY, round( (float) ( $pct / 100 ), 2 ), Imagick::CHANNEL_ALPHA );
				} else {
					// no alpha channel.
					if ( version_compare( phpversion( 'imagick' ), '3.4.3', '>=' ) ) {
						$foreground_image_to_use->setImageAlpha( round( (float) ( $pct / 100 ), 2 ) );
					} else {
						$foreground_image_to_use->setImageOpacity( round( (float) ( $pct / 100 ), 2 ) );
					}
				}
			}

			$background_image->compositeImage( $foreground_image_to_use, Imagick::COMPOSITE_DEFAULT, $dst_x, $dst_y, Imagick::CHANNEL_ALL );

			if ( $destroy ) {
				$this->cleanup( $foreground_image_to_use );
			}
		}

		function cleanup( &$image ) {
			// clear image memory
			$image->clear();
			$image->destroy();
			$image = null;
		}

		function get_image_base64( $image ) {
			$imgBuff = $image->getimageblob();
			return base64_encode( $imgBuff );
		}
	}
}