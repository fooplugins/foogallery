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

			$arg_defaults = array(
				'width'                   => 0,
				'height'                  => 0,
				'crop'                    => true,
				'jpeg_quality'            => foogallery_thumbnail_jpeg_quality(),
				'thumb_resize_animations' => foogallery_get_setting( 'thumb_resize_animations' )
			);

			$args = wp_parse_args( $args, $arg_defaults );

			if ( 'on' === foogallery_get_setting( 'use_original_thumbs' ) ) {
				$width  = (int)$args['width'];
				$height = (int)$args['height'];
				$crop   = (bool)$args['crop'];

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

			return wpthumb( $original_image_src, $args );
		}

		function run_tests() {
			$test_image_url = FOOGALLERY_URL . '/assets/test_thumb_1.jpg';
			$thumb_width = get_option( 'thumbnail_size_w' );
			$thumb_height = get_option( 'thumbnail_size_h' );

			$test1_args = array(
				'width'                   => 20,
				'height'                  => 20,
				'crop'                    => true,
				'jpeg_quality'            => foogallery_thumbnail_jpeg_quality()
			);
			$thumb1 = new WP_Thumb( $test_image_url, $test1_args );
			$results[] = array(
				'thumb' => $thumb1->returnImage(),
				'error' => $thumb1->error(),
			);

			$test2_args = array(
					'width'                   => 50,
					'height'                  => 80,
					'crop'                    => true,
					'crop_from_position'	  => 'right,center',
					'jpeg_quality'            => foogallery_thumbnail_jpeg_quality()
			);
			$thumb2 = new WP_Thumb( $test_image_url, $test2_args );
			$results[] = array(
					'thumb' => $thumb2->returnImage(),
					'error' => $thumb2->error(),
			);

			$test3_args = array(
				'width'                   => $thumb_width,
				'height'                  => $thumb_height,
				'crop'                    => true,
				'jpeg_quality'            => foogallery_thumbnail_jpeg_quality()
			);
			$thumb3 = new WP_Thumb( $test_image_url, $test3_args );
			$results[] = array(
				'thumb' => $thumb3->returnImage(),
				'error' => $thumb3->error(),
			);

			return $results;
		}
	}
}
