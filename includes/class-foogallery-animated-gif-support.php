<?php
/**
 * Animated GIF support in FooGallery
 * Date: 19/02/2017
 */
if ( ! class_exists( 'class-foogallery-animated-gif-support.php' ) ) {

	class FooGallery_Animated_Gif_Support {

		/**
		 * FooGallery_Animated_Gif_Support constructor.
		 */
		function __construct() {
			add_filter( 'foogallery_thumbnail_resize_args', array( $this, 'support_for_animations' ), 10, 3 );
		}

		/**
		 * Checks if the thumb we are using is an animated GIF. If so, return the original image so that the thumb is also animated
		 *
		 * @param array $args
		 * @param string $original_image_src
		 * @param FooGalleryAttachment $thumbnail_object
		 *
		 * @return array
		 */
		function support_for_animations( $args, $original_image_src, $thumbnail_object ) {
			$filetype = wp_check_filetype( $original_image_src );

			if ( is_array( $filetype ) && array_key_exists( 'ext', $filetype ) && 'gif' === $filetype['ext'] ) {

				if ( 'on' === foogallery_get_setting( 'animated_gif_use_original_image' ) ) {
					$args['force_use_original_image'] = true;
				}
			}

			return $args;
		}
	}
}