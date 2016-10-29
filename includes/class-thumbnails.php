<?php
/*
 * FooGallery Thumbnail Resizing class
 */

if ( !class_exists( 'FooGallery_Thumbnails' ) ) {

	class FooGallery_Thumbnails {

		function __construct() {
			//generate thumbs using WPThumb
			add_filter( 'foogallery_attachment_resize_thumbnail', array( $this, 'resize' ), 10, 3 );
		}

		function resize( $original_image_src, $args, $thumbnail_object ) {

		    global $foogallery_last_generated_thumb_url;

			$arg_defaults = array(
				'width'                   => 0,
				'height'                  => 0,
				'crop'                    => true,
				'jpeg_quality'            => foogallery_thumbnail_jpeg_quality(),
				'thumb_resize_animations' => foogallery_get_setting( 'thumb_resize_animations' )
			);

			$args = wp_parse_args( $args, $arg_defaults );

			$width  = (int)$args['width'];
			$height = (int)$args['height'];
			$crop   = (bool)$args['crop'];

			//we can force the use of the original WP icon by passing through args individually
			$force_use_original_thumb = isset( $args['force_use_original_thumb'] ) && true === $args['force_use_original_thumb'];

			if ( $force_use_original_thumb ) {
				$thumbnail_icon = wp_get_attachment_image_src( $thumbnail_object->ID, array( $width, $height ) );

				return $thumbnail_icon[0];
			}

			//we can force the use of original WP thumbs by passing through args individually, or by saved settings
			$use_original_thumbs = ( isset( $args['use_original_thumbs'] ) && true === $args['use_original_thumbs'] ) || 'on' === foogallery_get_setting( 'use_original_thumbs' );

			if ( $use_original_thumbs ) {
				//check if we are trying to get back the default thumbnail that we already have
				if ( $thumbnail_object->ID > 0 && $width == get_option( 'thumbnail_size_w' ) && $height == get_option( 'thumbnail_size_h' ) && $crop == get_option( 'thumbnail_crop' ) ) {
					$thumbnail_attributes = wp_get_attachment_image_src( $thumbnail_object->ID );

					return $thumbnail_attributes[0];
				}
			}

			if ( $thumbnail_object->ID > 0 ) {
				$crop_from_position = get_post_meta( $thumbnail_object->ID, 'wpthumb_crop_pos', true );

				if ( !empty( $crop_from_position ) ) {
					$args['crop_from_position'] = $crop_from_position;
				}
			}

			//save the generated thumb url to a global so that we can use it later if needed
            $foogallery_last_generated_thumb_url = wpthumb( $original_image_src, $args );

            return $foogallery_last_generated_thumb_url;
		}

		function run_thumbnail_generation_tests() {
            $test_image_url = foogallery_test_thumb_url();

            //first, clear any previous cached files
            $thumb = new WP_Thumb( $test_image_url );
            wpthumb_rmdir_recursive( $thumb->getCacheFileDirectory() );

            //next, generate a thumbnail
			$test_args = array(
				'width'                   => 20,
				'height'                  => 20,
				'crop'                    => true,
				'jpeg_quality'            => foogallery_thumbnail_jpeg_quality()
			);
			$test_thumb = new WP_Thumb( $test_image_url, $test_args );
            $generated_thumb = $test_thumb->returnImage();
            $success = $test_image_url !== $generated_thumb;

			$test_results = array(
			    'success' => $success,
				'thumb' => $generated_thumb,
				'error' => $test_thumb->errored() ? $test_thumb->error : '',
			);

            do_action( 'foogallery_thumbnail_generation_test', $test_results );

            return $test_results;
		}
	}
}
