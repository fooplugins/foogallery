<?php
/*
 * FooGallery Thumbnail Resizing class
 */

if ( !class_exists( 'FooGallery_Thumbnails' ) ) {

	class FooGallery_Thumbnails {

		function __construct() {
			add_filter( 'foogallery_attachment_resize_thumbnail', array( $this, 'resize' ), 10, 3 );

			add_filter( 'foogallery_test_thumb_url', array( $this, 'override_test_thumb_url' ) );

			add_filter( 'foogallery_thumbnail_resize_args', array( $this, 'check_for_force_original_thumb') );
		}

		function check_for_force_original_thumb( $args ){
			global $current_foogallery;

			if ( isset( $current_foogallery ) ) {
				$args['force_use_original_thumb'] = $current_foogallery->force_use_original_thumbs;
			}

			return $args;
		}

		function resize( $original_image_src, $args, $thumbnail_object ) {
			global $current_foogallery;
		    global $foogallery_last_generated_thumb_url;

			$arg_defaults = array(
				'width'                   => 0,
				'height'                  => 0,
				'crop'                    => true,
				'jpeg_quality'            => foogallery_thumbnail_jpeg_quality(),
				'thumb_resize_animations' => true,
				'foogallery_attachment_id'=> $thumbnail_object->ID
			);

			if ( isset( $current_foogallery ) ) {
				$arg_defaults['foogallery_id'] = $current_foogallery->ID;
			}

			$args = wp_parse_args( $args, $arg_defaults );

			//allow for plugins to change the thumbnail creation args
			$args = apply_filters( 'foogallery_thumbnail_resize_args', $args, $original_image_src, $thumbnail_object );

			//check the current arguments passed in by the shortcode
			global $current_foogallery_arguments;
			if ( isset( $current_foogallery_arguments ) && isset( $current_foogallery_arguments['template'] ) ) {
				$thumbnail_args = apply_filters( 'foogallery_calculate_thumbnail_dimensions-' . $current_foogallery_arguments['template'], $args, $current_foogallery_arguments );
				$args = wp_parse_args( $thumbnail_args, $args );
			}

			//allow for plugins to change the thumbnail creation args one final time
			$args = apply_filters( 'foogallery_thumbnail_resize_args_final', $args, $original_image_src, $thumbnail_object );

			$width  = (int)$args['width'];
			$height = (int)$args['height'];
			$crop   = (bool)$args['crop'];

			if ( 0 === $width && 0 === $height ) {
				return $original_image_src;
			}

			//we can force the use of the originally uploaded full-size image
			$force_use_original_image = isset( $args['force_use_original_image'] ) && true === $args['force_use_original_image'];

			if ( $thumbnail_object->ID > 0 && $force_use_original_image ) {
				$fullsize = wp_get_attachment_image_src( $thumbnail_object->ID, 'fullsize' );

				return $fullsize[0];
			}

			//we can force the use of the original WP icon or WP-generated thumb by passing through args individually
			$force_use_original_thumb = isset( $args['force_use_original_thumb'] ) && true === $args['force_use_original_thumb'];

			if ( $thumbnail_object->ID > 0 && $force_use_original_thumb ) {
				$thumbnail_icon = wp_get_attachment_image_src( $thumbnail_object->ID, array( $width, $height ) );

				return $thumbnail_icon[0];
			}

			//we can force the use of original WP thumbs by passing through args individually, or by saved settings
			$use_original_thumbs = ( isset( $args['use_original_thumbs'] ) && true === $args['use_original_thumbs'] ) || 'on' === foogallery_get_setting( 'use_original_thumbs' );

			if ( $use_original_thumbs ) {

				$option_thumbnail_size_w = get_option( 'thumbnail_size_w' );
				$option_thumbnail_size_h = get_option( 'thumbnail_size_h' );
				$option_thumbnail_crop = get_option( 'thumbnail_crop' );

				//check if we are trying to get back the default thumbnail that we already have
				if ( $thumbnail_object->ID > 0 && $width == $option_thumbnail_size_w && $height == $option_thumbnail_size_h && $crop == $option_thumbnail_crop ) {
					$thumbnail_attributes = wp_get_attachment_image_src( $thumbnail_object->ID );

					return $thumbnail_attributes[0];
				}
			}

			//remove invalid resize args
			if ( array_key_exists( 'height', $args ) && 0 === $args['height'] ) {
				unset( $args['height'] );
			}

			$force_resize = false;

			//only worry about upscaling if we have supplied both a width and height for cropping
			if ( array_key_exists( 'height', $args ) && $args['height'] > 0 &&
			     array_key_exists( 'width', $args ) && $args['width'] > 0 ) {
				//check if we must upscale smaller images
				if ( 'on' === foogallery_get_setting( 'thumb_resize_upscale_small' ) ) {
					$force_resize = true;
					$color = foogallery_get_setting( 'thumb_resize_upscale_small_color', '' );
					if ( $color !== 'auto' && $color !== 'transparent' ) {
						$colors = foogallery_rgb_to_color_array( $color );
						$color  = sprintf( "%03d%03d%03d000", $colors[0], $colors[1], $colors[2] );
					}
					$args['background_fill'] = $color;
				}
			}

			//do some checks to see if the image is smaller
			if ( $force_resize || $this->should_resize( $thumbnail_object, $args ) ) {
				//save the generated thumb url to a global so that we can use it later if needed
				$foogallery_last_generated_thumb_url = foogallery_thumb( $original_image_src, $args );
			} else {
				$foogallery_last_generated_thumb_url = apply_filters('foogallery_thumbnail_resize_small_image', $original_image_src, $args );
			}

            return $foogallery_last_generated_thumb_url;
		}

		function should_resize($thumbnail_object, $args) {
			$original_width = $thumbnail_object->width;
			$original_height = $thumbnail_object->height;

			if ( $original_width === $original_height && $original_height === 0 ) {
				//we do not have the original dimensions, so assume we must resize!
				return true;
			}

			$new_width = isset( $args['width'] ) ? $args['width'] : 0;
			$new_height = isset( $args['height'] ) ? $args['height'] : 0;

			if ( $new_width > 0 && $new_height > 0 ) {
				return $original_width > $new_width || $original_height > $new_height;
			} else if ( $new_width > 0 ) {
				return $original_width > $new_width;
			}
			return $original_height > $new_height;
		}

		function run_thumbnail_generation_tests() {
			if ( !foogallery_thumb_active_engine()->requires_thumbnail_generation_tests() ) {
				return array(
					'success' => true
				);
			}

            $test_image_url = foogallery_test_thumb_url();

			//next, generate a thumbnail
			$test_args = array(
				'width'                   => 20,
				'height'                  => 20,
				'crop'                    => true,
				'jpeg_quality'            => foogallery_thumbnail_jpeg_quality()
			);

			//first, clear any previous cached files
			$engine = foogallery_thumb_active_engine();
			$engine->clear_local_cache_for_file( $test_image_url );

            $generated_thumb = $engine->generate( $test_image_url, $test_args );

            $success = $test_image_url !== $generated_thumb;
			$file_info = wp_check_filetype( $test_image_url );

			$test_results = array(
			    'success' => $success,
				'thumb' => $generated_thumb,
				'error' => $engine->get_last_error(),
				'file_info' => $file_info
			);

            do_action( 'foogallery_thumbnail_generation_test', $test_results );

            return $test_results;
		}

		function override_test_thumb_url( $test_thumb_url ) {
			if ( 'on' !== foogallery_get_setting( 'override_thumb_test', false ) ) {
				$image_url = $this->find_first_image_in_media_library();

				if ( $image_url !== false ) {
					return $image_url;
				}
			}

			//if we get here, then either, we have set the override_thumb_test setting,
			//or there are no good images to use from the media library
			return $test_thumb_url;
		}

		static function find_first_image_in_media_library() {
			//try the first 10 attachments from the media library
			$args         = array(
				'post_type'        => 'attachment',
				'post_mime_type'   => 'image',
				'post_status'      => 'any',
				'numberposts'      => 10,
				'orderby'          => 'date',
				'order'            => 'ASC'
			);
			$query_images = new WP_Query( $args );
			foreach ( $query_images->posts as $image ) {
				$image_url = wp_get_attachment_url( $image->ID );

				if ( !empty( $image_url ) ) {
                    if ( self::image_file_exists( $image_url ) || self::image_file_exists( $image_url, true ) ) {
                        return $image_url;
                    }
				}
			}

			return false;
		}

		/**
		 * Check if a remote image file exists.
		 *
		 * @param  string $url The url to the remote image.
         * @param  bool   $force_https Whether to force the url to be https.
         *
		 * @return bool        Whether the remote image exists.
		 */
		static function image_file_exists( $url, $force_https = false ) {
            if ( $force_https ) {
                $url = str_replace( 'http://', 'https://', $url );
            }
			$response = wp_remote_head( $url );
			return 200 === wp_remote_retrieve_response_code( $response );
		}
	}
}
